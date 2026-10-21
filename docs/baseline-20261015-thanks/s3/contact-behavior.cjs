// S3 AC-03-3: /contact/ の既存挙動（種別の切り替え・支店・方法・入力チェック・確認・修正する・郵便番号）を 1920 / 375 で確認する。
// 送信はしない（Mailpit の件数が増えないことも確認）。使い方: node contact-behavior.cjs > contact-behavior.json
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
const count = async () => (await (await fetch('http://127.0.0.1:10010/api/v1/messages?limit=1')).json()).total;
(async () => {
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	const result = {};
	const before = await count();
	for (const [w, viewport] of [['1920', { width: 1920, height: 1080 }], ['375', { width: 375, height: 812 }]]) {
		const ctx = await browser.newContext({ viewport });
		const page = await ctx.newPage();
		const errors = [];
		page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
		page.on('pageerror', (e) => errors.push(String(e)));
		await page.goto('http://exterior-exone-renewal.local/contact/', { waitUntil: 'load' });
		await page.waitForTimeout(800);
		const steps = () => page.evaluate(() => Object.fromEntries(Array.from(document.querySelectorAll('[data-contact-step]')).map((s) => [s.getAttribute('data-contact-step'), !s.hidden])));
		const pressed = (sel) => page.evaluate((s) => Array.from(document.querySelectorAll(s)).filter((b) => b.getAttribute('aria-pressed') === 'true').map((b) => b.getAttribute('data-contact-type') || b.getAttribute('data-contact-store') || b.getAttribute('data-contact-method')), sel);
		const r = { initial: await steps() };
		await page.click('[data-contact-type="personal"]'); await page.waitForTimeout(500);
		r.personal = { steps: await steps(), pressed: await pressed('[data-contact-type]') };
		await page.click('[data-contact-store="sendai"]'); await page.waitForTimeout(500);
		r.store = await page.evaluate(() => ({
			steps: Object.fromEntries(Array.from(document.querySelectorAll('[data-contact-step]')).map((s) => [s.getAttribute('data-contact-step'), !s.hidden])),
			pressed: Array.from(document.querySelectorAll('[data-contact-store]')).filter((b) => b.getAttribute('aria-pressed') === 'true').map((b) => b.getAttribute('data-contact-store')),
			storeInput: (document.querySelector('[data-contact-step="form-personal"] input[name="store-slug"]') || {}).value,
			lineHref: document.querySelector('a[data-contact-method="line"]').getAttribute('href'),
			telEl: (() => { const t = document.querySelector('[data-contact-method="tel"]'); return { tag: t.tagName, href: t.getAttribute('href') }; })(),
			telNumber: document.querySelector('[data-contact-tel-slot="number"]').textContent.trim(),
		}));
		// 電話（PC は電話ブロック、SP は tel: 直リンクでブロックは出さない）
		if (w === '1920') {
			await page.click('button[data-contact-method="tel"]'); await page.waitForTimeout(500);
			r.tel = { steps: await steps(), pressed: await pressed('button[data-contact-method]') };
		} else {
			r.tel = { stepsBeforeClick: await steps(), note: 'SP は a[href^=tel:] のためクリックしない' };
		}
		await page.click('button[data-contact-method="mail"]'); await page.waitForTimeout(600);
		r.mail = { steps: await steps(), pressed: await pressed('button[data-contact-method]') };
		const box = page.locator('[data-contact-form="personal"]');
		// 空で確認へ → エラー
		await box.locator('form [type="submit"]').click(); await page.waitForTimeout(800);
		r.emptyErrors = await box.locator('.wpcf7-not-valid-tip').evaluateAll((els) => els.filter((e) => e.offsetParent).map((e) => e.textContent.trim()));
		r.emptyStillInput = await box.locator('[data-contact-form-view="input"]').isVisible();
		// 郵便番号 → 住所
		await box.locator('[name="your-zip"]').fill('9800021'); await page.waitForTimeout(2000);
		r.zipAddress = await box.locator('[name="your-address"]').inputValue();
		await box.locator('[name="your-name"]').fill('回帰 太郎（S3）');
		await box.locator('[name="your-email"]').fill('s3@example.com');
		await box.locator('[name="your-message"]').fill('S3 回帰確認（送信しない）');
		await box.locator('form [type="submit"]').click(); await page.waitForTimeout(800);
		r.confirm = await page.evaluate(() => {
			const b = document.querySelector('[data-contact-form="personal"]');
			const v = (n) => { const e = b.querySelector('[data-contact-form-view="' + n + '"]'); return !!e && !e.hidden && e.offsetHeight > 0; };
			return { input: v('input'), confirm: v('confirm'), list: b.querySelector('[data-contact-confirm-list]').innerText.replace(/\s+/g, ' ').trim().slice(0, 300) };
		});
		await page.screenshot({ path: __dirname + `/behavior-${w}-confirm.png` });
		await box.locator('[data-contact-confirm-back]').click(); await page.waitForTimeout(600);
		r.back = await page.evaluate(() => {
			const b = document.querySelector('[data-contact-form="personal"]');
			const v = (n) => { const e = b.querySelector('[data-contact-form-view="' + n + '"]'); return !!e && !e.hidden && e.offsetHeight > 0; };
			return { input: v('input'), confirm: v('confirm'), nameKept: b.querySelector('[name="your-name"]').value };
		});
		// 法人へ切り替え → 個人の入力は保持
		await page.click('[data-contact-type="corporate"]'); await page.waitForTimeout(600);
		r.corporate = { steps: await steps(), pressed: await pressed('[data-contact-type]') };
		await page.locator('[data-contact-form="corporate"] [name="your-zip"]').fill('0300801'); await page.waitForTimeout(2000);
		r.corporateZip = await page.locator('[data-contact-form="corporate"] [name="your-address"]').inputValue();
		await page.click('[data-contact-type="personal"]'); await page.waitForTimeout(600);
		r.backToPersonal = { steps: await steps(), nameKept: await box.locator('[name="your-name"]').inputValue(), store: await pressed('[data-contact-store]'), method: await pressed('button[data-contact-method]') };
		r.url = page.url();
		r.errors = errors;
		result[w] = r;
		await ctx.close();
	}
	await new Promise((res) => setTimeout(res, 2000));
	result.mailpitDelta = (await count()) - before;
	await browser.close();
	console.log(JSON.stringify(result, null, 1));
})();
