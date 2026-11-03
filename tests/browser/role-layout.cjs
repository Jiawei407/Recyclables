/* Read-only authenticated presentation smoke test. Seed a separate disposable schema first. */
const fs=require('fs'),path=require('path'),assert=require('node:assert/strict');
const {chromium}=require(process.env.RECYCLABLES_PLAYWRIGHT_MODULE || 'playwright');
const root=path.resolve(__dirname,'../..'),base=process.env.RECYCLABLES_BROWSER_URL;
if(!/^http:\/\/127\.0\.0\.1:\d+$/.test(base||''))throw new Error('Set RECYCLABLES_BROWSER_URL to an isolated loopback test server.');
const password=process.env.RECYCLABLES_BROWSER_PASSWORD;if(!password)throw new Error('Set the disposable fixture password.');
const out=path.join(root,'tests/.runtime/role-layout-browser');fs.mkdirSync(out,{recursive:true});
const onlyRoutes=new Set((process.env.RECYCLABLES_BROWSER_ROUTES||'').split(',').filter(Boolean));
const axePath=process.env.RECYCLABLES_AXE_PATH;
const axe=axePath?fs.readFileSync(axePath,'utf8'):null;
const selected=/^(challenges-view-challenge|submit-evidence-submission-details|achievements-recycling-helper|participation-campus-cleanup|promote-challenges-feature-challenge|faculty-activities-faculty-cleanup|submit-report-activity-report|announcements-edit|campaigns-edit|campaigns-performance|challenges-edit|challenges-edit-upload-photo|submissions-review-submission|achievements-edit|achievements-edit-upload-badge|audit-log-audit-details|challenge-balancing|challenge-balancing-delete-challenge|challenge-balancing-upload-challenge-photo|leaderboard-review-user-points|reports-faculty-report|users-delete-user|users-edit-user|users-suspend-user)$/;
const accessibility=new Set(['user/home.php','faculty/home.php','coordinator/home.php','admin/home.php','user/submit-evidence-new-submission.php','faculty/announcements-create.php','coordinator/announcements-create.php','coordinator/challenges-create.php','coordinator/submissions-review-submission.php','admin/users-edit-user.php','admin/reports-faculty-report.php']);
(async()=>{const browser=await chromium.launch({...(process.env.RECYCLABLES_CHROME?{executablePath:process.env.RECYCLABLES_CHROME}:{}),headless:true});const previous=onlyRoutes.size&&fs.existsSync(path.join(out,'report.json'))?JSON.parse(fs.readFileSync(path.join(out,'report.json'),'utf8')):null;const report={date:new Date().toISOString(),base,routes:previous?previous.routes.filter(r=>!onlyRoutes.has(r.route)):[],errors:[]};if(onlyRoutes.size)report.targetedUpdate=[...onlyRoutes];try{
 for(const role of ['user','faculty','coordinator','admin']){
  if(onlyRoutes.size&&![...onlyRoutes].some(route=>route.startsWith(role+'/')))continue;
  const context=await browser.newContext(),page=await context.newPage();let errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto(base+'/login.php');await page.locator('#email').fill(role==='user'?'tp810001@mail.apu.edu.my':role+'@mail.apu.edu.my');await page.locator('#password').fill(password);
  await Promise.all([page.waitForURL('**/'+role+'/home.php'),page.getByRole('button',{name:'Log in',exact:true}).click()]);
  for(const file of fs.readdirSync(path.join(root,role)).filter(x=>x.endsWith('.php')&&(!onlyRoutes.size||onlyRoutes.has(role+'/'+x)))){const route=role+'/'+file,stem=file.slice(0,-4),record={route,viewports:[]};
   for(const width of [320,1440]){
    errors=[];await page.setViewportSize({width,height:1000});const response=await page.goto(base+'/'+route+(selected.test(stem)?'?id=1':''));
    await page.evaluate(async()=>{await document.fonts.ready;await Promise.all([...document.images].map(i=>{i.loading='eager';return i.decode().catch(()=>{})}))});
    const item=await page.evaluate(()=>({width:innerWidth,pageWidth:document.documentElement.scrollWidth,roleHeader:!!document.querySelector('.clay-header'),prototypeControls:!!document.querySelector('[data-role-switch],[data-prototype-form],.preview-note,.prototype-feedback'),htmlLinks:[...document.querySelectorAll('a[href]')].filter(a=>/\.html(?:[?#]|$)/.test(a.getAttribute('href'))).map(a=>a.getAttribute('href')),missingCsrf:[...document.forms].filter(f=>f.method.toLowerCase()==='post'&&f.querySelectorAll('input[name=csrf]').length!==1).map(f=>f.id),brokenImages:[...document.images].filter(i=>!i.naturalWidth).map(i=>i.getAttribute('src'))}));
    item.status=response.status();item.url=page.url();item.jsErrors=[...errors];
    if(axe&&accessibility.has(route)){await page.addScriptTag({content:axe});item.axe=await page.evaluate(async()=> (await axe.run(document,{runOnly:{type:'tag',values:['wcag2a','wcag2aa','wcag21aa','wcag22aa']}})).violations.map(v=>({id:v.id,nodes:v.nodes.map(n=>({target:n.target,summary:n.failureSummary}))})));}
    if(accessibility.has(route))await page.screenshot({path:path.join(out,role+'--'+stem+'--'+width+'.png')});
    record.viewports.push(item);
   }
   report.routes.push(record);fs.writeFileSync(path.join(out,'report.json'),JSON.stringify(report,null,2));console.log(route+': '+record.viewports.map(v=>v.width+'px status='+v.status+' overflow='+(v.pageWidth-v.width)+' axe='+(v.axe?.length??'-')).join(' / '));
  }await context.close();
 }
 report.failures=report.routes.flatMap(r=>r.viewports.filter(v=>v.status!==200||v.pageWidth>v.width||!v.roleHeader||v.prototypeControls||v.htmlLinks.length||v.missingCsrf.length||v.brokenImages.length||v.jsErrors.length||v.axe?.length).map(v=>({route:r.route,...v})));
 report.passed=report.routes.length===82&&!report.failures.length;
}catch(error){report.errors.push(error.stack||String(error));report.passed=false;}finally{fs.writeFileSync(path.join(out,'report.json'),JSON.stringify(report,null,2));await browser.close();}
console.log(JSON.stringify({routes:report.routes.length,passed:report.passed,failures:report.failures,errors:report.errors},null,2));if(!report.passed)process.exitCode=1;
})().catch(error=>{console.error(error);process.exitCode=1});
