// Optional browser smoke check. Requires Node 22+ and Chrome or Edge.
const fs = require('node:fs');
const path = require('node:path');
const {spawn} = require('node:child_process');
const assert = require('node:assert/strict');
const root = path.resolve(__dirname, '..');
const browserPath = process.env.GRAPHITE_BROWSER || 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const profile = fs.mkdtempSync(path.join(root, 'storage', 'browser-'));
const child = spawn(browserPath, ['--headless=new', '--disable-gpu', '--no-sandbox', '--disable-dev-shm-usage', '--no-first-run', '--no-default-browser-check', '--remote-debugging-port=0', '--user-data-dir='+profile, 'about:blank'], {windowsHide:true, stdio:'ignore'});
const pause = ms => new Promise(resolve=>setTimeout(resolve,ms));
let socket, sequence=0, checks=0;
const waiting=new Map(), exceptions=[];
async function call(method,params={}){
 const id=++sequence;
 const response=new Promise((resolve,reject)=>{
  const timer=setTimeout(()=>{waiting.delete(id);reject(Error('CDP timeout: '+method))},10000);
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
 for(let i=0;i<100;i++){
  try{if(await evaluate(expression)){checks++;return}}catch{}
  await pause(100);
 }
 throw Error('Timed out: '+expression);
}
async function navigate(url,condition){
 await call('Page.navigate',{url});
 await until('document.readyState === "complete" && ('+condition+')');
}
(async()=>{
 try{
  for(let i=0;i<100&&!fs.existsSync(path.join(profile,'DevToolsActivePort'));i++)await pause(100);
  const port=fs.readFileSync(path.join(profile,'DevToolsActivePort'),'utf8').split('\n')[0];
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
  await navigate(base,'document.querySelector(".hero")');
  await until('document.querySelectorAll("#site-nav a").length >= 8');
  assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Desktop homepage should fit the viewport');checks++;
  fs.writeFileSync(path.join(root,'storage','website-desktop.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
  for(const route of ['about','project','products','sustainability','investors','news','contact','privacy','terms']){
   await navigate(base+'?page='+route,'document.querySelector("#main h1") && document.querySelector("#footer .footer-top")');
   assert(!await evaluate('document.querySelector("#main").textContent.includes("Setup required")'));
   assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Desktop route should fit: '+route);checks++;
   if(route==='project'){
    await evaluate('document.querySelectorAll(".development-list summary")[1].click()');
    assert(await evaluate('document.querySelectorAll(".development-list details")[1].open'),'Project workstream should expand');checks++;
    fs.writeFileSync(path.join(root,'storage','website-project.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
   }
   checks++;
  }
  await call('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
  await navigate(base,'document.querySelector(".hero")');
  await evaluate('document.querySelector(".mobile-toggle").click()');
  assert.equal(await evaluate('document.querySelector(".mobile-toggle").getAttribute("aria-expanded")'),'true');checks++;
  await evaluate('document.querySelector(".mobile-toggle").click()');
  await pause(400);
  assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Mobile page should fit the viewport');checks++;
  fs.writeFileSync(path.join(root,'storage','website-mobile.png'),Buffer.from((await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false})).data,'base64'));
  for(const route of ['about','project','products','sustainability','investors','news','contact','privacy','terms']){
   await navigate(base+'?page='+route,'document.querySelector("#main h1") && document.querySelector("#footer .footer-top")');
   assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'),'Mobile route should fit: '+route);checks++;
  }
  await navigate(base+'?page=investors','document.querySelector(".partner-grid")');
  await evaluate('document.querySelectorAll(".partner-grid a")[2].click()');
  await until('document.querySelector("#contact-form")');
  assert.equal(await evaluate('document.querySelector("[name=category]").value'),'Product information');checks++;
  await call('Emulation.clearDeviceMetricsOverride');
  await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
  const credentials=fs.readFileSync(path.join(root,'storage','local-admin.txt'),'utf8');
  const email=credentials.match(/^Email: (.+)$/m)[1], password=credentials.match(/^Password: (.+)$/m)[1];
  await navigate(base+'admin.php','document.querySelector("#login-form")');
  await evaluate(`document.querySelector('[name=email]').value=${JSON.stringify(email)};document.querySelector('[name=password]').value=${JSON.stringify(password)};document.querySelector('#login-form').requestSubmit()`);
  await until('document.querySelector("#breadcrumb")?.textContent === "Overview"');
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
  assert.deepEqual(exceptions,[],'Browser runtime exceptions');
  console.log(`PASS: ${checks} browser checks; website routes, mobile navigation, CMS login, all dashboard sections, image search, preview and logout.`);
 }catch(error){console.error(error);process.exitCode=1}
 finally{
  if(socket?.readyState===1){socket.send(JSON.stringify({id:++sequence,method:'Browser.close'}));socket.close()}
  child.kill();await pause(500);
  try{fs.rmSync(profile,{recursive:true,force:true})}catch{}
 }
})().catch(error=>{console.error(error);process.exitCode=1});
