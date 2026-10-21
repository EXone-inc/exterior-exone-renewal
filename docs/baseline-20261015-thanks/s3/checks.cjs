// S3 AC-03-5（SP・横スクロール）/ AC-03-6（支店モード）/ AC-03-7（コンソール）。使い方: node checks.cjs > checks.json
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
const BASE = 'http://exterior-exone-renewal.local';
const STORES = ['sendai', 'totsuka', 'morioka', 'aomori', 'hachinohe', 'hirosaki'];
const THANKS = ['/contact/thanks/', '/recruit/thanks/'];
(async () => {
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	const out = { sp: {}, overflow: {}, store: {}, console: {} };
	const mk = async (viewport) => {
		const ctx = await browser.newContext({ viewport, hasTouch: viewport.width <= 768, isMobile: false });
		const page = await ctx.newPage();
		const errors = [];
		page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
		page.on('pageerror', (e) => errors.push('pageerror: ' + String(e)));
		return { ctx, page, errors };
	};
	// AC-03-5: SP 375 ドロワー・ドロワー内リンク・Explore カード
	for (const url of THANKS) {
		const r = {};
		const { ctx, page, errors } = await mk({ width: 375, height: 812 });
		await page.goto(BASE + url, { waitUntil: 'load' });
		await page.waitForTimeout(800);
		const state = () => page.evaluate(() => ({ expanded: document.querySelector('.p-header__toggle').getAttribute('aria-expanded'), drawerVisible: (() => { const d = document.querySelector('[data-drawer]'); const s = getComputedStyle(d); const b = d.getBoundingClientRect(); return s.visibility !== 'hidden' && s.display !== 'none' && +s.opacity > 0 && b.width > 0 && b.right > 0 && b.left < innerWidth; })() }));
		r.initial = await state();
		await page.click('.p-header__toggle'); await page.waitForTimeout(800);
		r.opened = await state();
		await page.click('[data-drawer-close]'); await page.waitForTimeout(800);
		r.closedByX = await state();
		await page.click('.p-header__toggle'); await page.waitForTimeout(800);
		await page.keyboard.press('Escape'); await page.waitForTimeout(800);
		r.closedByEsc = await state();
		await page.click('.p-header__toggle'); await page.waitForTimeout(800);
		const link = page.locator('[data-drawer] a[href*="/works/"]').first();
		r.drawerLinkHref = await link.getAttribute('href');
		await Promise.all([page.waitForURL('**/works/**', { timeout: 10000 }).catch(() => null), link.click()]);
		r.drawerLinkUrl = page.url();
		// Explore カードのタップ
		r.explore = [];
		for (const [i, slug] of [[0, 'works'], [1, 'plans'], [2, 'dx']]) {
			await page.goto(BASE + url, { waitUntil: 'load' });
			const card = page.locator('main a[href]').nth(i);
			await card.scrollIntoViewIfNeeded(); await page.waitForTimeout(1500);
			const href = await card.getAttribute('href');
			const resp = page.waitForNavigation({ timeout: 10000 }).catch(() => null);
			await card.tap();
			const nav = await resp;
			r.explore.push({ href, url: page.url(), status: nav ? nav.status() : null, ok: page.url().includes('/' + slug + '/') });
		}
		r.errors = errors;
		out.sp[url] = r;
		await ctx.close();
	}
	// AC-03-5: 横スクロール
	for (const w of [375, 768, 1024, 1025, 1280, 1920]) {
		const { ctx, page } = await mk({ width: w, height: 900 });
		for (const url of THANKS) {
			await page.goto(BASE + url, { waitUntil: 'load' });
			await page.waitForTimeout(500);
			out.overflow[w + ' ' + url] = await page.evaluate(() => ({ scrollWidth: document.documentElement.scrollWidth, innerWidth: innerWidth, ok: document.documentElement.scrollWidth <= innerWidth }));
		}
		await ctx.close();
	}
	// AC-03-6: 支店モード
	{
		const { ctx, page, errors } = await mk({ width: 1920, height: 1080 });
		for (const s of [...STORES, 'unknown']) {
			await page.goto(BASE + '/contact/thanks/?store=' + s, { waitUntil: 'load' });
			out.store[s] = await page.evaluate(() => ({
				storeMode: document.documentElement.getAttribute('data-store-mode'),
				headerStore: document.querySelector('.p-header__store') ? { text: document.querySelector('.p-header__store').innerText.trim(), href: document.querySelector('.p-header__store').getAttribute('href') } : null,
				explore: Array.from(document.querySelectorAll('main a[href]')).map((a) => a.getAttribute('href')),
				linksWithStore: Array.from(document.querySelectorAll('a[href*="store="]')).length,
			}));
		}
		out.store.errors = errors;
		await ctx.close();
	}
	// AC-03-7: コンソール
	const URLS = ['/', '/contact/', '/contact/?store=sendai', '/contact/thanks/', '/contact/thanks/?store=sendai', '/recruit/', '/recruit/entry/', '/recruit/thanks/', '/works/', '/plans/', '/dx/'];
	for (const [w, vp] of [['1920', { width: 1920, height: 1080 }], ['375', { width: 375, height: 812 }]]) {
		const { ctx, page, errors } = await mk(vp);
		for (const url of URLS) {
			errors.length = 0;
			const resp = await page.goto(BASE + url, { waitUntil: 'load', timeout: 60000 });
			await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 400) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 80)); } });
			await page.waitForTimeout(1500);
			out.console[w + ' ' + url] = { status: resp.status(), errors: errors.slice() };
		}
		await ctx.close();
	}
	await browser.close();
	console.log(JSON.stringify(out, null, 1));
})();
