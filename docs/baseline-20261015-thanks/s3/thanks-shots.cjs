// S3 AC-03-9 用: サンクスページ 2 つ（+ 支店モード）の 1920 / 375 フルページ。使い方: node thanks-shots.cjs
const path = require('path');
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
const PAGES = [['contact-thanks', '/contact/thanks/'], ['recruit-thanks', '/recruit/thanks/'], ['contact-thanks-sendai', '/contact/thanks/?store=sendai']];
(async () => {
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	const res = {};
	for (const [w, viewport, dpr] of [['pc', { width: 1920, height: 1080 }, 1], ['sp', { width: 375, height: 812 }, 2]]) {
		const ctx = await browser.newContext({ viewport, deviceScaleFactor: dpr });
		const page = await ctx.newPage();
		for (const [name, url] of PAGES) {
			await page.goto('http://exterior-exone-renewal.local' + url, { waitUntil: 'load' });
			await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 200) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 120)); } window.scrollTo(0, 0); });
			await page.waitForTimeout(2500);
			await page.screenshot({ path: path.join(__dirname, `thanks-${w}-${name}.png`), fullPage: true });
			res[w + ' ' + name] = await page.evaluate(() => ({ h: document.documentElement.scrollHeight, footerTop: Math.round(document.querySelector('footer').getBoundingClientRect().top + scrollY), storeMode: document.documentElement.hasAttribute('data-store-mode'), headerStore: !!document.querySelector('.p-header__store, .p-drawer__store') }));
		}
		await ctx.close();
	}
	await browser.close();
	console.log(JSON.stringify(res));
})();
