// お知らせページ作業のコラム不変チェック用（AC-02-0 / AC-05-5）。
// 使い方: node shoot.cjs <出力ディレクトリ>
// Playwright は @playwright/mcp の npx キャッシュ、ブラウザはローカルの Chrome（channel: 'chrome'）を使う。
// 演出を出し切った状態で撮るため reducedMotion: 'reduce'（scroll-reveal.js が最終状態を即表示する）にし、
// 遅延読み込みの画像を読ませるため一度最下部までスクロールしてから最上部に戻し、全画像の decode を待って撮る。
// 写真部分は Chrome の縮小処理で撮影ごとに数値 1〜数十の揺らぎが出ることがある（2 回撮影で確認済み）。
// 画素比較は compare.py（同ディレクトリ）でしきい値付きで行う。
const path = require('path');
const fs = require('fs');
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright');

const BASE = 'http://exterior-exone-renewal.local';
const PAGES = [
	['column', '/column/'],
	['column-page2', '/column/page/2/'],
	['column-carport-size-snow', '/column/carport-size-snow/'],
	['column-store-aomori', '/column/?store=aomori'],
];
const WIDTHS = [
	['pc', { width: 1920, height: 1080 }, 1],
	['sp', { width: 375, height: 812 }, 2],
];

(async () => {
	const out = path.resolve(process.argv[2] || '.');
	fs.mkdirSync(out, { recursive: true });
	const browser = await chromium.launch({ channel: 'chrome' });
	for (const [w, viewport, dpr] of WIDTHS) {
		const ctx = await browser.newContext({ viewport, deviceScaleFactor: dpr, reducedMotion: 'reduce' });
		const page = await ctx.newPage();
		for (const [name, url] of PAGES) {
			await page.goto(BASE + url, { waitUntil: 'load', timeout: 60000 });
			await page.evaluate(async () => {
				for (let y = 0; y < document.body.scrollHeight; y += 300) {
					window.scrollTo(0, y);
					await new Promise((r) => setTimeout(r, 150));
				}
				window.scrollTo(0, 0);
			});
			// 遅延読み込みの取りこぼしを防ぐため、全画像の読み込みと decode の完了まで待つ（loading 属性は sizes=auto の再選択を招くので触らない）
			await page.evaluate(async () => {
				const imgs = Array.from(document.images);
				for (let t = 0; t < 200 && imgs.some((i) => !(i.complete && i.naturalWidth > 0) && i.currentSrc !== ''); t++) {
					await new Promise((r) => setTimeout(r, 100));
				}
				await Promise.all(imgs.filter((i) => i.complete && i.naturalWidth > 0).map((i) => Promise.race([i.decode().catch(() => {}), new Promise((r) => setTimeout(r, 3000))])));
			});
			const notLoaded = await page.evaluate(() => Array.from(document.images).filter((i) => !(i.complete && i.naturalWidth > 0)).map((i) => i.currentSrc || i.src));
			if (notLoaded.length) console.log('  未読み込みの画像:', notLoaded);
			await page.waitForTimeout(1500);
			// 1 回目の全体撮影では画面外の画像が描画されないことがあるため、捨て撮りしてから本番を撮る
			await page.screenshot({ fullPage: true });
			await page.waitForTimeout(1000);
			await page.screenshot({ path: path.join(out, `${w}-${name}.png`), fullPage: true });
			const meta = await page.evaluate(() => ({
				bodyClass: document.body.className,
				css: Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map((l) => l.id),
				js: Array.from(document.querySelectorAll('script[src]')).map((s) => s.id || s.src),
			}));
			fs.writeFileSync(path.join(out, `${w}-${name}.assets.json`), JSON.stringify(meta, null, 2));
			console.log(w, name, 'ok');
		}
		await ctx.close();
	}
	await browser.close();
})();
