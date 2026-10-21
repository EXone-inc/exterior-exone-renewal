const { chromium } = require('/Users/exone-se/.npm/_npx/9833c18b2d85bc59/node_modules/playwright-core');
(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const urls = ['http://exterior-exone-renewal.local/contact/thanks/', 'http://exterior-exone-renewal.local/recruit/thanks/'];
  const sizes = [[1920,1080],[375,667]];
  const modes = ['normal','reduced','nojs'];
  for (const url of urls) for (const [w,h] of sizes) for (const mode of modes) {
    const ctx = await browser.newContext({ viewport:{width:w,height:h}, reducedMotion: mode==='reduced'?'reduce':'no-preference', javaScriptEnabled: mode!=='nojs' });
    const page = await ctx.newPage();
    const errs = [];
    page.on('console', m => { if (m.type()==='error') errs.push(m.text()); });
    page.on('pageerror', e => errs.push(e.message));
    await page.goto(url, { waitUntil: 'load' });
    const sample = async () => page.evaluate(() => {
      const t = document.querySelector('.p-thanksmsg__text');
      const cs = getComputedStyle(t);
      const svg = document.querySelector('.p-thanksmsg__check');
      const tick = svg.querySelector('.p-thanksmsg__tick');
      return { op: cs.opacity, tf: cs.transform, anim: cs.animationName, delay: cs.animationDelay, svg: svg.getAttribute('class'), tickOff: getComputedStyle(tick).strokeDashoffset, reveal: t.hasAttribute('data-reveal') };
    });
    const early = mode==='normal' ? await sample() : null;
    await page.waitForTimeout(500);
    const mid = mode==='normal' ? await sample() : null;
    await page.waitForTimeout(1500);
    const s = await sample();
    const tops = await page.evaluate(() => {
      const r = s => { const e=document.querySelector(s); return e? Math.round((e.getBoundingClientRect().top+scrollY)*10)/10 : null; };
      return { check: r('.p-thanksmsg__check'), lead: r('.p-thanksmsg__lead'), body: r('.p-thanksmsg__body'), steps: r('.p-thankssteps .p-thankshead__en'), stepsReveal: (()=>{const e=document.querySelector('.p-thankssteps [data-reveal]');return e?getComputedStyle(e).opacity:'n/a'})(), scrollY };
    });
    console.log(url.replace('http://exterior-exone-renewal.local',''), w, mode, JSON.stringify({early, mid, final:s, tops, errs}));
    await ctx.close();
  }
  await browser.close();
})();
