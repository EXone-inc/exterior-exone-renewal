// S3 AC-03-11: F-02 の送信 → 遷移・戻る・エラー時・支店モード・採用の再確認（S2 evaluator の ev2-flow.cjs を流用。画像は s3/flow/）
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
const BASE = 'http://exterior-exone-renewal.local';
const SHOT = __dirname + '/flow/';
require('fs').mkdirSync(SHOT, { recursive: true });
const api = async (q) => (await fetch('http://127.0.0.1:10010/api/v1/messages?limit=' + q)).json();
const count = async () => (await api(1)).total;
const latest = async (n) => (await api(n)).messages.map((m) => ({ to: m.To.map((t) => t.Address).join(','), subject: m.Subject }));
const which = process.argv.slice(2);
const PC = { width: 1920, height: 1080 };
const SP = { width: 375, height: 812 };

(async () => {
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	const out = [];
	const mk = async (viewport) => {
		const ctx = await browser.newContext({ viewport });
		const page = await ctx.newPage();
		const errors = [];
		page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
		page.on('pageerror', (e) => errors.push('pageerror: ' + String(e)));
		return { ctx, page, errors };
	};

	const contact = async (label, url, type, viewport, opts = {}) => {
		const { ctx, page, errors } = await mk(viewport);
		const before = await count();
		await page.goto(BASE + url, { waitUntil: 'load' });
		const attr = await page.locator(`[data-contact-form="${type}"]`).getAttribute('data-contact-thanks-url');
		await page.click(`[data-contact-type="${type}"]`);
		await page.waitForTimeout(800);
		if (type === 'personal' && opts.store) {
			const s = page.locator(`[data-contact-store="${opts.store}"]`);
			if (await s.isVisible().catch(() => false)) { await s.click(); await page.waitForTimeout(700); }
		}
		const m = page.locator('button[data-contact-method="mail"]:visible');
		if (await m.count()) { await m.first().click(); await page.waitForTimeout(800); }
		const box = page.locator(`[data-contact-form="${type}"]`);
		const f = box.locator('form');
		if (opts.empty) {
			await f.locator('[type="submit"]').click();
			await page.waitForTimeout(1500);
			const tips = await box.locator('.wpcf7-not-valid-tip:visible, [class*="error"]:visible').count();
			await page.waitForTimeout(3500);
			await page.screenshot({ path: SHOT + label + '.png' });
			out.push({ label, attr, url: page.url(), tips, delta: (await count()) - before, errors });
			await ctx.close();
			return;
		}
		await f.locator('[name="your-name"]').fill('評価 花子（S3回帰）');
		if (type === 'corporate') {
			await f.locator('[name="your-company"]').fill('評価株式会社');
			await f.locator('[name="your-tel"]').fill('0221234567');
		}
		await f.locator('[name="your-zip"]').fill('9800021');
		await page.waitForTimeout(1200);
		await f.locator('[name="your-address"]').fill('宮城県仙台市青葉区中央2-2');
		await f.locator('[name="your-email"]').fill('eval@example.com');
		await f.locator('[name="your-message"]').fill('S3 回帰 遷移テスト ' + label);
		await f.locator('[type="submit"]').click();
		await page.waitForTimeout(900);
		const confirmVisible = await box.locator('[data-contact-form-view="confirm"]').isVisible();
		// watch done view visibility until navigation
		await page.evaluate((type) => {
			window.__doneSeen = false;
			const b = document.querySelector(`[data-contact-form="${type}"]`);
			const d = b.querySelector('[data-contact-form-view="done"]');
			const tick = () => { if (d && !d.hidden && d.offsetHeight > 0) window.__doneSeen = true; requestAnimationFrame(tick); };
			tick();
		}, type);
		let doneSeenBeforeNav = false;
		page.on('framenavigated', () => {});
		const t0 = Date.now();
		const navP = page.waitForURL((u) => u.pathname.includes('/thanks/'), { timeout: 10000 }).then(() => true).catch(() => false);
		// poll done flag until nav
		const poll = (async () => { while (true) { try { if (await page.evaluate(() => window.__doneSeen)) { doneSeenBeforeNav = true; } } catch (e) { return; } await new Promise((r) => setTimeout(r, 30)); if (Date.now() - t0 > 10500) return; } })();
		await box.locator('[data-contact-confirm-send]').click();
		const reached = await navP;
		const ms = Date.now() - t0;
		await page.waitForLoadState('load');
		const res = { label, attr, confirmVisible, url: page.url(), ms, reached, doneSeenBeforeNav };
		if (reached) {
			Object.assign(res, await page.evaluate(() => ({
				h1: document.querySelector('h1') && document.querySelector('h1').innerText.replace(/\s+/g, ' ').trim(),
				lead: (document.querySelector('main') || document.body).innerText.split('\n').filter((s) => s.includes('ありがとうございます。'))[0],
				storeMode: document.documentElement.getAttribute('data-store-mode'),
				storeLink: document.querySelector('.p-header__store') ? document.querySelector('.p-header__store').innerText.trim() : null,
				explore: Array.from(document.querySelectorAll('main a[href]')).map((a) => a.getAttribute('href')),
			})));
			await page.screenshot({ path: SHOT + label + '-thanks.png' });
		} else {
			res.doneText = await box.locator('[data-contact-form-view="done"]').innerText().catch(() => null);
			res.doneVisible = await box.locator('[data-contact-form-view="done"]').isVisible();
			await page.screenshot({ path: SHOT + label + '-nothanks.png' });
		}
		await page.waitForTimeout(3000);
		res.delta = (await count()) - before;
		res.mails = await latest(res.delta || 1);
		if (opts.back && reached) {
			const c0 = await count();
			await page.goBack({ waitUntil: 'load' });
			await page.waitForTimeout(5000);
			res.back = await page.evaluate((type) => {
				const b = document.querySelector(`[data-contact-form="${type}"]`);
				const vis = (sel) => { const e = b.querySelector(sel); return !!e && !e.hidden && e.offsetHeight > 0; };
				return {
					url: location.href,
					boxVisible: b.offsetHeight > 0,
					input: vis('[data-contact-form-view="input"]'),
					confirm: vis('[data-contact-form-view="confirm"]'),
					done: vis('[data-contact-form-view="done"]'),
					busy: b.getAttribute('aria-busy'),
					sendDisabled: b.querySelector('[data-contact-confirm-send]').disabled,
					submitDisabled: b.querySelector('form [type="submit"]').disabled,
					name: b.querySelector('[name="your-name"]').value,
				};
			}, type);
			await page.screenshot({ path: SHOT + label + '-back.png' });
			res.back.delta = (await count()) - c0;
		}
		res.errors = errors;
		out.push(res);
		await ctx.close();
	};

	const recruit = async (label, url, viewport, opts = {}) => {
		const { ctx, page, errors } = await mk(viewport);
		const before = await count();
		await page.goto(BASE + url, { waitUntil: 'load' });
		const attr = await page.locator('[data-recruit-entry]').getAttribute('data-recruit-entry-thanks-url');
		if (opts.empty) {
			await page.locator('form [type="submit"]').click();
			await page.waitForTimeout(4500);
			await page.screenshot({ path: SHOT + label + '.png' });
			out.push({ label, attr, url: page.url(), tips: await page.locator('.wpcf7-not-valid-tip:visible').count(), delta: (await count()) - before, errors });
			await ctx.close();
			return;
		}
		await page.locator('input[value="施工管理職"]').check({ force: true });
		await page.locator('input[value="無回答"]').check({ force: true });
		await page.locator('[name="your-name-last"]').fill('評価');
		await page.locator('[name="your-name-first"]').fill('花子');
		await page.locator('[name="your-email"]').fill('eval@example.com');
		await page.locator('[name="your-tel"]').fill('0221234567');
		await page.locator('[name="your-postcode"]').fill('980-0021');
		await page.waitForTimeout(1200);
		await page.locator('[name="your-address"]').fill('宮城県仙台市青葉区中央');
		await page.locator('[name="your-extended-address"]').fill('2-2');
		await page.locator('[name="your-contact"]').fill('S3 回帰 ' + label);
		const t0 = Date.now();
		const navP = page.waitForURL((u) => u.pathname.includes('/thanks/'), { timeout: 10000 }).then(() => true).catch(() => false);
		await page.locator('form [type="submit"]').click();
		const reached = await navP;
		await page.waitForLoadState('load');
		const res = { label, attr, url: page.url(), ms: Date.now() - t0, reached };
		if (reached) {
			Object.assign(res, await page.evaluate(() => ({
				lead: document.querySelector('main').innerText.split('\n').filter((s) => s.includes('ありがとうございます。'))[0],
				storeMode: document.documentElement.getAttribute('data-store-mode'),
				storeLink: !!document.querySelector('.p-header__store'),
			})));
			await page.screenshot({ path: SHOT + label + '-thanks.png' });
		} else {
			res.doneText = await page.locator('[data-recruit-entry-done]').innerText().catch(() => null);
			res.doneVisible = await page.locator('[data-recruit-entry-done]').isVisible().catch(() => null);
			res.formHidden = await page.locator('[data-recruit-entry] form').isHidden();
			await page.screenshot({ path: SHOT + label + '-nothanks.png' });
		}
		await page.waitForTimeout(3000);
		res.delta = (await count()) - before;
		res.mails = await latest(res.delta || 1);
		if (opts.back && reached) {
			const c0 = await count();
			await page.goBack({ waitUntil: 'load' });
			await page.waitForTimeout(5000);
			res.back = await page.evaluate(() => {
				const b = document.querySelector('[data-recruit-entry]');
				const f = b.querySelector('form');
				return { url: location.href, formHidden: f.hidden, busy: b.getAttribute('aria-busy'), submitDisabled: f.querySelector('.wpcf7-submit').disabled, name: f.querySelector('[name="your-name-last"]').value, status: f.getAttribute('data-status') };
			});
			await page.screenshot({ path: SHOT + label + '-back.png' });
			res.back.delta = (await count()) - c0;
		}
		res.errors = errors;
		out.push(res);
		await ctx.close();
	};

	const run = (k) => which.includes(k);
	if (run('personal')) await contact('personal-1920', '/contact/', 'personal', PC, { store: 'morioka', back: true });
	if (run('corporate')) await contact('corporate-375', '/contact/', 'corporate', SP, { back: true });
	if (run('sendai')) await contact('personal-sendai', '/contact/?store=sendai', 'personal', PC, { store: 'sendai' });
	if (run('recruit')) await recruit('recruit-1920', '/recruit/entry/', PC, { back: true });
	if (run('recruit-aomori')) await recruit('recruit-aomori-375', '/recruit/entry/?store=aomori', SP);
	if (run('empty')) { await contact('personal-empty', '/contact/', 'personal', PC, { empty: true, store: 'morioka' }); await recruit('recruit-empty', '/recruit/entry/', PC, { empty: true }); }
	if (run('draft-contact')) await contact('draft-personal', '/contact/', 'personal', PC, { store: 'morioka' });
	if (run('draft-recruit')) await recruit('draft-recruit', '/recruit/entry/', PC);
	console.log(JSON.stringify(out, null, 1));
	await browser.close();
})();
