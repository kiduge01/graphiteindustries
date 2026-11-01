// Optional browser smoke check. Requires Node 22+ and Chrome or Edge.
const fs = require('node:fs');
const path = require('node:path');
const {spawn} = require('node:child_process');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '..');
const browserPath = process.env.GRAPHITE_BROWSER || (fs.existsSync('C:/Program Files (x86)/Google/Chrome/Application/chrome.exe') ? 'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe' : 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe');
const profile = fs.mkdtempSync(path.join(root, 'storage', 'browser-'));
const child = spawn(browserPath, ['--headless=new', '--disable-gpu', '--no-sandbox', '--disable-dev-shm-usage', '--no-first-run', '--no-default-browser-check', '--remote-debugging-port=0', '--user-data-dir='+profile, 'about:blank'], {windowsHide:true, stdio:'ignore'});
const pause = ms => new Promise(resolve=>setTimeout(resolve,ms));
let socket, sequence=0, checks=0;
const waiting=new Map(), exceptions=[];
async function call(method,params={}){
 const id=++sequence;
 const response=new Promise((resolve,reject)=>{
  const timer=setTimeout(()=>{waiting.delete(id);reject(Error('CDP timeout: '+method))},method==='Page.navigate'?60000:30000);
  waiting.set(id,{resolve:value=>{clearTimeout(timer);resolve(value)},reject:error=>{clearTimeout(timer);reject(error)}});
 });
 socket.send(JSON.stringify({id,method,params}));
 return response;
}
async function evaluate(expression){
 const result=await call('Runtime.evaluate',{expression,awaitPromise:true,returnByValue:true});
 if(result.exceptionDetails)throw Error(result.exceptionDetails.text+': '+result.exceptionDetails.exception?.description);
 return result.result.value;
}
async function until(expression){
 for(let i=0;i<300;i++){
  try{if(await evaluate(expression)){checks++;return}}catch{}
  await pause(100);
 }
 throw Error('Timed out: '+expression);
}
async function navigate(url,condition){
 console.log('Checking',url.replace(/.*\/public\//,'/'));
 await call('Page.navigate',{url});
 await until('document.readyState !== "loading" && ('+condition+')');
}
async function capture(name,full=false){
 const clip=full?(await call('Page.getLayoutMetrics')).cssContentSize:null;
 const result=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:full,...(clip?{clip:{x:0,y:0,width:clip.width,height:Math.min(clip.height,16000),scale:1}}:{})});
 fs.writeFileSync(path.join(root,'storage',name+'.png'),Buffer.from(result.data,'base64'));
}
(async()=>{
 try{
  let port;
  for(let i=0;i<600&&!port;i++){
   try{const value=fs.readFileSync(path.join(profile,'DevToolsActivePort'),'utf8').split('\n')[0];if(/^\d+$/.test(value))port=value}catch{}
   if(!port)await pause(100);
  }
  if(!port)throw Error('Browser did not provide a usable debugging port within 60 seconds.');
  const tab=await(await fetch(`http://127.0.0.1:${port}/json/new?about:blank`,{method:'PUT'})).json();
  socket=new WebSocket(tab.webSocketDebuggerUrl); await new Promise((resolve,reject)=>{socket.onopen=resolve;socket.onerror=reject});
  socket.onmessage=event=>{
   const message=JSON.parse(event.data);
   if(message.id){const job=waiting.get(message.id);if(!job)return;waiting.delete(message.id);message.error?job.reject(Error(message.error.message)):job.resolve(message.result)}
   if(message.method==='Runtime.exceptionThrown')exceptions.push(message.params.exceptionDetails.exception?.description||message.params.exceptionDetails.text);
  };
  await call('Page.enable');await call('Runtime.enable');
  await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
  const base=process.env.GRAPHITE_BROWSER_URL||'http://localhost/graphite/public/';
  await navigate(base,'document.querySelector(".hero") && document.querySelector("#footer .footer-top")');
  if(process.argv.includes('--capture-contact')){
   await evaluate('document.querySelector(".consent [data-value=no]")?.click()');
   await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
   await navigate(base+'contact?category=Products','document.querySelector("#contact-form")');
   await capture('review-contact-products-mobile',true);
   console.log('Product enquiry visual review capture saved.');return;
  }
  if(process.argv.includes('--capture-only')){
   await evaluate('document.querySelector(".consent [data-value=no]")?.click()');
   for(const route of ['','about','project','products','sustainability','investors','contact','news']){
    if(route)await navigate(base+route,'document.querySelector("#footer .footer-top")');
    await capture('review-'+(route||'home')+'-desktop',true);
   }
   await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
   for(const route of ['','contact']){
    await navigate(base+route,'document.querySelector("#footer .footer-top")');
    await capture('review-'+(route||'home')+'-mobile',true);
   }
   console.log('Full-page visual review captures saved.');return;
  }
  // Homepage: the short original layout and the server-rendered navigation.
  await until('document.querySelectorAll("#site-nav a").length >= 8');
  assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Desktop homepage should fit the viewport');checks++;
  assert.equal(await evaluate('document.querySelector("#main").children.length'),3,'Three focused homepage sections');checks++;
  assert.equal(await evaluate('document.querySelector(".hero .btn").getAttribute("href")'),'project');checks++;
  assert(await evaluate('document.querySelector(".responsibility-tile").getAttribute("href") === "sustainability"'));checks++;
  assert(await evaluate(`!!document.querySelector("#updates-form") && !document.querySelector('#footer a[href="admin.php"]')`),'Footer has the updates sign-up and no CMS link');checks++;
  const words=await evaluate('document.querySelector("#main").innerText.trim().split(/\\s+/).length');
  assert(words<=220,'Concise homepage editorial budget: '+words);checks++;
  console.log('Homepage words:',words);
  fs.writeFileSync(path.join(root,'storage','website-desktop.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
  // Each designed page: layout present, icons resolved, fits the viewport.
  const designed={
   about:['.vp-purpose .vp-card',2],
   project:['.vp-flow > li',6],
   products:['.vp-app-grid article',5],
   sustainability:['.sv-chips li',11],
   investors:['#vp-seek .sv-chips li',6],
   contact:['.vp-contact',4],
  };
  for(const route of ['about','project','products','sustainability','investors','news','contact','privacy','terms']){
   await navigate(base+route,'document.querySelector("#main h1") && document.querySelector("#footer .footer-top")');
   assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Desktop route should fit: '+route);checks++;
   assert.equal(await evaluate('document.querySelector("#site-nav .active")?.getAttribute("href") ?? null'),['privacy','terms'].includes(route)?null:route,'Active navigation item: '+route);checks++;
   if(designed[route]){
    assert.equal(await evaluate(`document.querySelectorAll(${JSON.stringify(designed[route][0])}).length`),designed[route][1],'Layout blocks on '+route);checks++;
    assert(await evaluate('[...document.querySelectorAll("#main [data-icon]")].every(el=>getComputedStyle(el,"::before").maskImage.startsWith("url"))'),'Every icon resolves on '+route);checks++;
   }
   if(route==='project'){
    assert.equal(await evaluate('document.querySelectorAll(".vp-timeline > li").length'),6,'Six separate proposed development stages');checks++;
    fs.writeFileSync(path.join(root,'storage','website-project.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
   }
   if(route==='about'){assert(await evaluate('!!document.querySelector("[data-leadership-spotlight]")'),'Leadership showcase');checks++}
   if(route==='news'){assert(await evaluate('!!document.querySelector("[data-layout=news]")'));checks++}
   if(route==='privacy'){assert(await evaluate('!!document.querySelector("#reset-consent")'),'Analytics preference control');checks++}
   if(route==='contact'){
    assert.equal(await evaluate('document.querySelector("[name=category]").options.length'),5);checks++;
    await evaluate('document.querySelector("#contact-form").requestSubmit()');
    assert(await evaluate('!document.querySelector("#contact-form").checkValidity()'),'Empty enquiry is rejected by native validation');checks++;
    assert(!await evaluate('document.querySelector("#form-result").textContent.includes("Thank you")'));checks++;
   }
   checks++;
  }
  await navigate(base+'missing-page','document.querySelector("#main h1")');
  assert(await evaluate('document.querySelector("#main").textContent.includes("This page is not published")'),'Unavailable page message');checks++;
  await navigate(base+'?page=about','document.querySelector("[data-layout=about]")');
  assert(await evaluate('location.pathname.endsWith("/about") && !location.search'),'Old ?page= links move to clean URLs');checks++;
  // Mobile navigation.
  await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
  await navigate(base,'document.querySelector(".hero") && document.querySelector("#footer .footer-top")');
  await evaluate('document.querySelector(".mobile-toggle").click()');
  assert.equal(await evaluate('document.querySelector(".mobile-toggle").getAttribute("aria-expanded")'),'true');checks++;
  assert(await evaluate('document.querySelector(".site-wrap").inert'),'Background is inactive while navigation is open');checks++;
  assert.equal(await evaluate('document.activeElement.getAttribute("aria-label")'),'Close navigation');checks++;
  await evaluate('document.querySelector(".nav-close").click()');
  assert(await evaluate('document.querySelector(".sidebar").inert'),'Closed mobile navigation is outside the tab sequence');checks++;
  await pause(400);
  assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Mobile page should fit the viewport');checks++;
  fs.writeFileSync(path.join(root,'storage','website-mobile.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
  for(const route of ['about','project','products','sustainability','investors','news','contact','privacy','terms']){
   await navigate(base+route,'document.querySelector("#main h1") && document.querySelector("#footer .footer-top")');
   assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Mobile route should fit: '+route);checks++;
  }
  // Enquiry links carry their category and topic into the form.
  await navigate(base+'investors','document.querySelector(".vp-info .btn")');
  await evaluate('document.querySelector(".vp-info .btn").click()');
  await until('document.querySelector("#contact-form")');
  assert.equal(await evaluate('document.querySelector("[name=category]").value'),'Investment');checks++;
  assert(await evaluate('document.querySelector("[name=message]").value.startsWith("Please share the available information")'),'Topic pre-fills the message');checks++;
  await navigate(base+'products','document.querySelector("#graphite-concentrate .text-link")');
  await evaluate('document.querySelector("#graphite-concentrate .text-link").click()');
  await until('document.querySelector("#contact-form")');
  assert.equal(await evaluate('document.querySelector("[name=category]").value'),'Products');checks++;
  assert(!await evaluate('document.querySelector(".buyer-details").hidden'));checks++;
  assert.equal(await evaluate('document.querySelectorAll("[name^=buyer_]").length'),7);checks++;
  await evaluate('document.querySelector("[name=category]").value="General";document.querySelector("[name=category]").dispatchEvent(new Event("change"))');
  assert(await evaluate('document.querySelector(".buyer-details").hidden && [...document.querySelectorAll("[name^=buyer_]")].every(x=>x.disabled)'));checks++;
  await call('Emulation.setDeviceMetricsOverride',{width:768,height:1024,deviceScaleFactor:1,mobile:true});
  await navigate(base+'project','document.querySelector(".vp-flow") && document.querySelector("#footer .footer-top")');
  assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Tablet project should fit');checks++;
  fs.writeFileSync(path.join(root,'storage','website-tablet.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
  // Pages are complete without JavaScript, apart from the enquiry form.
  await call('Emulation.setScriptExecutionDisabled',{value:true});
  await navigate(base+'about','document.querySelector("#main h2")');
  assert(await evaluate('document.querySelector("#main").textContent.includes("OUR MISSION") && document.querySelectorAll("#site-nav a").length >= 8'),'Mission and navigation are present without JavaScript');checks++;
  await navigate(base+'contact','document.querySelector("#contact-form-slot")');
  assert(await evaluate('document.querySelector("#contact-form-slot").textContent.includes("Enable JavaScript")'),'Contact fallback without JavaScript');checks++;
  await call('Emulation.setScriptExecutionDisabled',{value:false});
  await call('Emulation.clearDeviceMetricsOverride');
  await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
  // CMS checks sign in with GRAPHITE_ADMIN_EMAIL / GRAPHITE_ADMIN_PASSWORD, or the
  // local credentials file. When neither works they are skipped, not failed.
  let email = process.env.GRAPHITE_ADMIN_EMAIL, password = process.env.GRAPHITE_ADMIN_PASSWORD;
  if (!email || !password) {
   try {
    const credentials = fs.readFileSync(path.join(root,'storage','local-admin.txt'),'utf8');
    email = credentials.match(/^Email: (.+)$/m)?.[1]?.trim(); password = credentials.match(/^Password: (.+)$/m)?.[1]?.trim();
   } catch {}
  }
  await navigate(base+'admin.php','document.querySelector("#login-form")');
  let adminChecked = false;
  if (email && password) {
   await evaluate(`document.querySelector('[name=email]').value=${JSON.stringify(email)};document.querySelector('[name=password]').value=${JSON.stringify(password)};document.querySelector('#login-form').requestSubmit()`);
   await until('document.querySelector("#breadcrumb")?.textContent === "Overview" || document.querySelector("#login-error")?.textContent.trim()');
   adminChecked = await evaluate('document.querySelector("#breadcrumb")?.textContent === "Overview"');
  }
  if (!adminChecked) console.log('SKIPPED: CMS dashboard checks. Set GRAPHITE_ADMIN_EMAIL and GRAPHITE_ADMIN_PASSWORD, or update storage/local-admin.txt.');
  if (adminChecked) {
   fs.writeFileSync(path.join(root,'storage','admin-desktop.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
   for(const section of ['pages','posts','media','enquiries','analytics','users','settings']){
    await evaluate(`document.querySelector('[data-tab=${section}]').click()`);
    await until(`document.querySelector('[data-tab=${section}]')?.classList.contains('active')`);
    if(['posts','media','enquiries','users'].includes(section))await until('document.querySelector("#collection-pager")');
   }
   await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
   await evaluate('document.querySelector(".mobile-admin").click()');
   assert.equal(await evaluate('document.querySelector(".mobile-admin").getAttribute("aria-expanded")'),'true');checks++;
   for(const section of ['overview','pages','posts','media','enquiries','analytics','users','settings']){
    await evaluate(`document.querySelector('[data-tab=${section}]').click()`);
    await until(`document.querySelector('[data-tab=${section}]')?.getAttribute('aria-current') === 'page'`);
    if(['posts','media','enquiries','users'].includes(section))await until('document.querySelector("#collection-pager")');
    assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Mobile workspace should fit: '+section);checks++;
    assert.equal(await evaluate('document.querySelector(".mobile-admin").getAttribute("aria-expanded")'),'false');checks++;
   }
   fs.writeFileSync(path.join(root,'storage','admin-mobile.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
   await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
   await evaluate('document.querySelector("[data-tab=pages]").click()');
   await until('document.querySelector(".edit-page")');
   await evaluate('document.querySelector(".edit-page").click()');
   await until('document.querySelector(".find-images")');
   await evaluate('document.querySelector(".find-images").click()');
   await until('document.querySelector(".image-count").textContent.includes("images")');
   await evaluate('document.querySelector("#preview-page").click()');
   await until('document.querySelector("#modal").open');
   await evaluate('document.querySelector(".dialog-close").click()');
   await evaluate('document.querySelector("#logout").click()');
   await until('document.querySelector("#login-form")');
  }
  assert.deepEqual(exceptions,[],'Browser runtime exceptions');
  console.log(`PASS: ${checks} browser checks; website routes, layouts, icons, forms, mobile navigation, no-JavaScript rendering${adminChecked ? ', CMS login, all dashboard sections, image search, preview and logout' : ' (CMS checks skipped)'}.`);
 }catch(error){
  console.error(error);
  if(exceptions.length)console.error('Browser exceptions:',exceptions);
  try{console.error(await evaluate('({url:location.href,ready:document.readyState,main:document.querySelector("#main")?.innerText.slice(0,350)})'))}catch{}
  process.exitCode=1;
 }
 finally{
  if(socket?.readyState===1){socket.send(JSON.stringify({id:++sequence,method:'Browser.close'}));socket.close()}
  child.kill();await pause(500);
  try{fs.rmSync(profile,{recursive:true,force:true})}catch{}
 }
})().catch(error=>{console.error(error);process.exitCode=1});
