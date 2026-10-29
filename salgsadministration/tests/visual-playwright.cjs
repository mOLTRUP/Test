const path = require('path');
const fs = require('fs');
const { pathToFileURL } = require('url');
const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({headless: true, args: ['--no-sandbox']});
  const dir = path.resolve(__dirname, '../screenshots');
  fs.mkdirSync(dir, {recursive: true});
  const demo = pathToFileURL(path.resolve(__dirname, '../index.html')).href;
  const scenarios = [
    {id: 'seller', actor: '1', title: 'Min personlige salgsforside'},
    {id: 'office', actor: '8', title: 'Kontorets forside'},
    {id: 'manager', actor: '5', title: 'Landechefens forside'},
    {id: 'leadership', actor: '6', title: 'Ledelsens dashboard'},
    {id: 'admin', actor: '7', title: 'Administratorens kontrolpanel'}
  ];
  let checks = 0;
  try {
    for(const sc of scenarios) {
      const page = await browser.newPage({viewport:{width:1440,height:900}, deviceScaleFactor:1});
      const errors = [];
      page.on('pageerror', error => errors.push(error.message));
      await page.goto(demo, {waitUntil: 'load'});
      await page.waitForSelector('#sa-app .sa-layout');
      await page.locator('#sa-role').selectOption(sc.actor);
      await page.waitForTimeout(75);
      if(!(await page.locator('main').innerText()).includes(sc.title)) throw Error(sc.id+' missing title');
      await page.screenshot({path:path.join(dir,sc.id+'-desktop.png'),fullPage:true});
      const charts=await page.locator('.sa-curve').count();
      if(charts < 2) throw Error(sc.id+' must display at least two line curves');
      if(await page.locator('.sa-bar-track').count()) throw Error(sc.id+' must not show horizontal bar charts');
      const d=await page.locator('.sa-curve svg path').count(); if(d<1) throw Error(sc.id+' missing SVG curve paths');
      checks += 2;
      await page.setViewportSize({width:390,height:844});
      await page.waitForTimeout(75);
      const extent = await page.evaluate(()=>({
        width: document.documentElement.clientWidth,
        contentWidth:document.documentElement.scrollWidth,
        chartWidth:document.querySelector('.sa-dash-grid')?.getBoundingClientRect().width
      }));
      if(extent.contentWidth>extent.width+3) throw Error(sc.id+' horizontal page overflow at mobile: '+JSON.stringify(extent));
      if(extent.chartWidth<=0) throw Error(sc.id+' missing responsive grid');
      await page.screenshot({path:path.join(dir,sc.id+'-mobile.png'),fullPage:true});
      checks += 2;
      await page.close();
    }
    console.log('PASS visual browser smoke: '+checks+' role, graph and responsive layout checks; 10 screenshots');
  } finally {
    await browser.close();
  }
})().catch(error=>{console.error(error.stack||error);process.exitCode=1});
