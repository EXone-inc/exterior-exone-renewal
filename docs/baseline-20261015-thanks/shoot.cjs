// サンクスページ作業の回帰チェック用（AC-01-0 / AC-03-1 / AC-03-2）。
// 使い方: node shoot.cjs <出力ディレクトリ>
// baseline-20261014-news/shoot.cjs を流用。playwright-core + ローカル Chrome。reducedMotion: 'reduce' で演出を出し切った状態で撮る。
const path = require('path');
const fs = require('fs');
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');

const BASE = 'http://exterior-exone-renewal.local';
// [名前, URL, 操作]
const PAGES = [
	['contact', '/contact/', null],
	['contact-personal-form', '/contact/', async (p) => {
		await p.click('[data-contact-type="personal"]');
		await p.waitForTimeout(600);
		await p.click('[data-contact-store="sendai"]');
		await p.waitForTimeout(600);
		await p.click('button[data-contact-method="mail"]');
		await p.waitForTimeout(800);
	}],
	['contact-corporate-form', '/contact/', async (p) => {
		// 法人は種別を選ぶとフォームが直接出る（方法の段は無い）
		await p.click('[data-contact-type="corporate"]');
		await p.waitForTimeout(1200);
	}],
	['contact-store-sendai', '/contact/?store=sendai', null],
	['recruit-entry', '/recruit/entry/', null],
	['recruit', '/recruit/', null],
];
const WIDTHS = [
	['pc', { width: 1920, height: 1080 }, 1],
	['sp', { width: 375, height: 812 }, 2],
];

(async () => {
	const out = path.resolve(process.argv[2] || '.');
	fs.mkdirSync(out, { recursive: true });
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	for (const [w, viewport, dpr] of WIDTHS) {
		const ctx = await browser.newContext({ viewport, deviceScaleFactor: dpr, reducedMotion: 'reduce' });
		const page = await ctx.newPage();
		const errors = [];
		page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
		page.on('pageerror', (e) => errors.push(String(e)));
		for (const [name, url, action] of PAGES) {
			errors.length = 0;
			await page.goto(BASE + url, { waitUntil: 'load', timeout: 60000 });
			await page.waitForTimeout(1000);
			if (action) await action(page);
			await page.evaluate(async () => {
				for (let y = 0; y < document.body.scrollHeight; y += 300) {
					window.scrollTo(0, y);
					await new Promise((r) => setTimeout(r, 150));
				}
				window.scrollTo(0, 0);
			});
			await page.evaluate(async () => {
				const imgs = Array.from(document.images);
				for (let t = 0; t < 200 && imgs.some((i) => !(i.complete && i.naturalWidth > 0) && i.currentSrc !== ''); t++) {
					await new Promise((r) => setTimeout(r, 100));
				}
				await Promise.all(imgs.filter((i) => i.complete && i.naturalWidth > 0).map((i) => Promise.race([i.decode().catch(() => {}), new Promise((r) => setTimeout(r, 3000))])));
			});
			await page.waitForTimeout(2000);
			await page.screenshot({ fullPage: true });
			await page.waitForTimeout(1000);
			await page.screenshot({ path: path.join(out, `${w}-${name}.png`), fullPage: true });
			fs.writeFileSync(path.join(out, `${w}-${name}.html`), await page.content());
			const meta = await page.evaluate(() => ({
				url: location.href,
				bodyClass: document.body.className,
				robots: (document.querySelector('meta[name="robots"]') || {}).content || null,
				css: Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map((l) => l.id),
				js: Array.from(document.querySelectorAll('script[src]')).map((s) => s.id || s.src),
			}));
			meta.consoleErrors = errors.slice();
			fs.writeFileSync(path.join(out, `${w}-${name}.assets.json`), JSON.stringify(meta, null, 2));
			console.log(w, name, 'ok', errors.length ? errors : '');
		}
		await ctx.close();
	}
	await browser.close();
})();
