// S3: /recruit/ の FV 粒子（recruit.js の Math.random）による撮影ごとの揺らぎを記録する。
// shoot.cjs と同じ条件で /recruit/ を N 回撮り、canvas の矩形を JSON に残す。
// 使い方: node jitter.cjs <出力ディレクトリ> <回数>
const path = require('path');
const fs = require('fs');
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
(async () => {
	const out = path.resolve(process.argv[2]);
	const n = parseInt(process.argv[3] || '3', 10);
	fs.mkdirSync(out, { recursive: true });
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	const rects = {};
	for (const [w, viewport, dpr] of [['pc', { width: 1920, height: 1080 }, 1], ['sp', { width: 375, height: 812 }, 2]]) {
		for (let i = 1; i <= n; i++) {
			const ctx = await browser.newContext({ viewport, deviceScaleFactor: dpr, reducedMotion: 'reduce' });
			const page = await ctx.newPage();
			await page.goto('http://exterior-exone-renewal.local/recruit/', { waitUntil: 'load' });
			await page.waitForTimeout(1000);
			await page.evaluate(async () => {
				for (let y = 0; y < document.body.scrollHeight; y += 300) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 150)); }
				window.scrollTo(0, 0);
			});
			await page.waitForTimeout(3000);
			await page.screenshot({ path: path.join(out, `${w}-recruit-${i}.png`), fullPage: true });
			rects[w] = await page.evaluate(() => { const r = document.querySelector('canvas[data-recruit-particles]').getBoundingClientRect(); return [r.left, r.top + scrollY, r.right, r.bottom + scrollY]; });
			await ctx.close();
		}
	}
	fs.writeFileSync(path.join(out, 'canvas-rect.json'), JSON.stringify(rects, null, 2));
	await browser.close();
})();
