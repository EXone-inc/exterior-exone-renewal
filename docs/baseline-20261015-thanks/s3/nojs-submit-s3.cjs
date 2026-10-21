// S3 AC-02-12 再確認: nojs-submit.cjs と同じ操作。読み込み直後のレイアウト移動で click が "not stable" になるため、送信ボタンを force で押す版（JS 無効の文脈では Playwright の安定待ちが "not stable" のまま終わらないことがあるため。操作内容は同じ）。
const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
const BASE = 'http://exterior-exone-renewal.local';
const count = async () => (await (await fetch('http://127.0.0.1:10010/api/v1/messages?limit=1')).json()).total;
(async () => {
	const browser = await chromium.launch({ channel: 'chrome', headless: true });
	const ctx = await browser.newContext({ viewport: { width: 1920, height: 1080 }, javaScriptEnabled: false });
	const page = await ctx.newPage();
	const res = [];
	const run = async (label, url, fill) => {
		const before = await count();
		await page.goto(BASE + url, { waitUntil: 'networkidle' }); await page.waitForTimeout(1500);
		let note = '';
		try {
			await fill(page);
		} catch (e) { note = "入力不可: " + String(e).slice(0, 600); }
		await page.waitForTimeout(3000);
		const after = await count();
		const out = await page.evaluate(() => Array.from(document.querySelectorAll('.wpcf7-response-output')).map((o) => o.textContent.trim()).filter(Boolean));
		const tips = await page.evaluate(() => Array.from(document.querySelectorAll('.wpcf7-not-valid-tip')).map((o) => o.textContent.trim()));
		res.push({ label, url: page.url(), responseOutput: out, notValidTips: tips, mailpitDelta: after - before, note });
	};
	if (!process.argv.includes('--recruit-only')) {
	await run('contact-personal', '/contact/', async (p) => {
		const f = p.locator('form').nth(0);
		await f.locator('[name="your-name"]').fill('テスト 太郎（JS無効）');
		await f.locator('[name="your-email"]').fill('test@example.com');
		await f.locator('[name="your-zip"]').fill('9800021');
		await f.locator('[name="your-address"]').fill('宮城県仙台市青葉区中央1-1');
		await f.locator('[name="your-message"]').fill('JS 無効時のベースライン送信テスト');
		await Promise.all([p.waitForLoadState('load'), f.locator('[type="submit"]').click({ timeout: 15000, force: true })]);
	});
	await run('contact-corporate', '/contact/', async (p) => {
		const f = p.locator('form').nth(1);
		await f.locator('[name="your-name"]').fill('テスト 花子（JS無効）');
		await f.locator('[name="your-company"]').fill('テスト株式会社');
		await f.locator('[name="your-zip"]').fill('9800021');
		await f.locator('[name="your-address"]').fill('宮城県仙台市青葉区中央1-1');
		await f.locator('[name="your-tel"]').fill('0120000000');
		await f.locator('[name="your-email"]').fill('test@example.com');
		await f.locator('[name="your-message"]').fill('JS 無効時のベースライン送信テスト');
		await Promise.all([p.waitForLoadState('load'), f.locator('[type="submit"]').click({ timeout: 15000, force: true })]);
	});
	}
	await run('recruit-entry', '/recruit/entry/', async (p) => {
		// チェックボックスは見た目を差し替えているため force で操作する
		await p.locator('input[value="施工管理職"]').check({ timeout: 5000, force: true });
		await p.locator('input[value="無回答"]').check({ timeout: 5000, force: true });
		await p.locator('[name="your-name-last"]').fill('テスト');
		await p.locator('[name="your-name-first"]').fill('太郎');
		await p.locator('[name="your-email"]').fill('test@example.com');
		await p.locator('[name="your-tel"]').fill('0120000000');
		await p.locator('[name="your-postcode"]').fill('105-0001');
		await p.locator('[name="your-address"]').fill('東京都港区芝公園');
		await p.locator('[name="your-extended-address"]').fill('3丁目1-22');
		await p.locator('[name="your-contact"]').fill('JS 無効時のベースライン送信テスト');
		await Promise.all([p.waitForLoadState('load'), p.locator('form [type="submit"]').click({ timeout: 15000, force: true })]);
	});
	console.log(JSON.stringify(res, null, 2));
	await browser.close();
})();
