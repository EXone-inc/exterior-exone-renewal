// S3: 375 の支店モード（/contact/thanks/?store=sendai）でドロワーの開閉・支店名・リンクの store= 引き継ぎを確認する。
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
(async () => {
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	const ctx = await browser.newContext({ viewport: { width: 375, height: 812 }, hasTouch: true });
	const page = await ctx.newPage();
	const errors = [];
	page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
	page.on('pageerror', (e) => errors.push(String(e)));
	await page.goto('http://exterior-exone-renewal.local/contact/thanks/?store=sendai', { waitUntil: 'load' });
	const r = {};
	r.header = await page.evaluate(() => ({ storeMode: document.documentElement.hasAttribute('data-store-mode'), store: (document.querySelector('.p-header__store') || {}).textContent, scrollW: document.documentElement.scrollWidth }));
	await page.click('.p-header__toggle'); await page.waitForTimeout(800);
	r.open = await page.evaluate(() => ({ expanded: document.querySelector('.p-header__toggle').getAttribute('aria-expanded'), drawerStore: (document.querySelector('.p-drawer__store') || {}).textContent, drawerLinks: Array.from(document.querySelectorAll('[data-drawer] a[href^="http://exterior-exone-renewal.local"]')).map((a) => a.getAttribute('href')) }));
	const link = page.locator('[data-drawer] a[href*="/works/"]').first();
	await Promise.all([page.waitForURL('**/works/**', { timeout: 10000 }).catch(() => null), link.click()]);
	r.afterLink = page.url();
	await page.goto('http://exterior-exone-renewal.local/contact/thanks/?store=sendai', { waitUntil: 'load' });
	const card = page.locator('main a[href]').nth(1);
	await card.scrollIntoViewIfNeeded(); await page.waitForTimeout(1200);
	await Promise.all([page.waitForURL('**/plans/**', { timeout: 10000 }).catch(() => null), card.tap()]);
	r.afterCard = page.url();
	r.errors = errors;
	console.log(JSON.stringify(r, null, 1));
	await browser.close();
})();
