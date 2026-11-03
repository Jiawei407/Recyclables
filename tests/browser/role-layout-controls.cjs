'use strict';
const fs=require('fs'),path=require('path'),assert=require('node:assert/strict');
const {chromium}=require(process.env.RECYCLABLES_PLAYWRIGHT_MODULE||'playwright');
const base=process.env.RECYCLABLES_BROWSER_URL;
if(!/^http:\/\/127\.0\.0\.1:\d+$/.test(base))throw Error('Use an isolated loopback test server only.');
const password=process.env.RECYCLABLES_BROWSER_PASSWORD;
if(!password)throw Error('Set the disposable fixture password.');
const report={checks:[],scope:'Live themed forms on an explicitly seeded disposable browser database'};
const record=(name)=>{report.checks.push({name,passed:true});console.log('PASS '+name)};
function uniquePng(){
 const png=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=','base64');
 const text=Buffer.from('Comment\0Browser fixture '+require('node:crypto').randomUUID()),type=Buffer.from('tEXt'),length=Buffer.alloc(4),crc=Buffer.alloc(4);length.writeUInt32BE(text.length);
 let value=0xffffffff;for(const byte of Buffer.concat([type,text])){value^=byte;for(let i=0;i<8;i++)value=(value>>>1)^((value&1)?0xedb88320:0);}crc.writeUInt32BE((value^0xffffffff)>>>0);
 return Buffer.concat([png.subarray(0,-12),length,type,text,crc,png.subarray(-12)]);
}
(async()=>{
 const health=await (await fetch(base+'/__test_health')).json();
 assert.match(health.database,/^recyclables_test_[a-z0-9_]+$/);
 assert.ok(process.env.RECYCLABLES_TEST_SERVER_TOKEN,'Set the isolated server handshake token.');
 assert.equal(health.token,process.env.RECYCLABLES_TEST_SERVER_TOKEN,'Wrong test server; no mutations allowed.');
 const browser=await chromium.launch({...(process.env.RECYCLABLES_CHROME?{executablePath:process.env.RECYCLABLES_CHROME}:{}),headless:true});try{
 const contexts={},pages={},errors=[];
 for(const role of ['user','faculty','coordinator','admin']){
  contexts[role]=await browser.newContext({viewport:{width:390,height:1000}});const page=pages[role]=await contexts[role].newPage();
  page.on('pageerror',e=>errors.push(e.message));page.on('dialog',d=>d.accept());
  await page.goto(base+'/login.php');await page.locator('input[name=email]').fill(role==='user'?'tp810001@mail.apu.edu.my':role+'@mail.apu.edu.my');
  await page.locator('input[type=password]').fill(password);await page.getByRole('button',{name:'Log in',exact:true}).click();await page.waitForURL('**/'+role+'/home.php');
  assert.equal(await page.locator('[data-role-switch]').count(),0);assert.equal(await page.locator('.clay-header').count(),1);
 }
 record('Four real role logins keep their authorized navigation');
 const user=pages.user;await user.goto(base+'/user/submit-evidence-new-submission.php');
 await user.locator('.selection-challenge').click();await user.locator('input[name="challenge-id"]').first().check();
 await user.getByRole('button',{name:/Increase Number of items/}).click();assert.equal(await user.locator('#quantity').inputValue(),'2');
 await user.locator('input[type=file]').setInputFiles({name:'browser-flow.png',mimeType:'image/png',buffer:uniquePng()});
 await user.locator('#evidence-description').fill('Live layout browser evidence');
 assert.ok(await user.locator('#backend-form input[name=csrf]').inputValue());
 await user.getByRole('button',{name:'Submit evidence',exact:true}).click();await user.waitForURL('**/user/submit-evidence-submission-status.php');
 const detail=await user.locator('a[href*="submit-evidence-submission-details.php?id="]').first().getAttribute('href');assert.ok(detail);
 const id=new URL(detail,base+'/user/submit-evidence-submission-status.php').searchParams.get('id');
 record('Themed challenge, number and photo controls submit through real CSRF-protected PHP');
 const coordinator=pages.coordinator;await coordinator.goto(base+'/coordinator/submissions-review-submission.php?id='+id);
 for(const name of ['review-matches','review-count','review-duplicate'])await coordinator.locator('[name="'+name+'"]').check();
 await coordinator.getByRole('button',{name:'Approve',exact:true}).click();await coordinator.waitForURL('**/coordinator/submissions-approved.php');
 await user.goto(base+'/user/submit-evidence-submission-details.php?id='+id);assert.match(await user.locator('main').textContent(),/approved/i);
 record('Coordinator review posts a real decision and participant sees the stored result');
 const faculty=pages.faculty;await faculty.goto(base+'/faculty/announcements-create.php');
 await faculty.locator('#announcement-title').fill('Browser layout announcement');await faculty.locator('#announcement-message').fill('Announcement submitted through themed controls.');
 await faculty.locator('#announcement-date').fill('2026-11-01');await faculty.locator('#announcement-date').locator('..').locator('.clay-calendar-button').click();
 await faculty.locator('.selection-calendar:not([hidden]) [data-date="2026-11-02"]').click();
 const audience=faculty.locator('#announcement-audience');await audience.locator('..').locator('.selection-trigger').click();await faculty.getByRole('option',{name:'My faculty',exact:true}).click();
 await faculty.getByRole('button',{name:'Publish',exact:true}).click();await faculty.waitForURL('**/faculty/announcements-announcements.php');
 assert.match(await faculty.locator('main').textContent(),/Browser layout announcement/);record('Themed calendar and audience selector publish a real faculty announcement');
 const admin=pages.admin;await admin.goto(base+'/admin/users-edit-user.php?id=1');
 await admin.locator('#designated-role').locator('..').locator('.selection-trigger').click();await admin.getByRole('option',{name:'Faculty Representative',exact:true}).click();
 assert.equal(await admin.locator('#designated-role').inputValue(),'faculty');assert.equal(await admin.locator('#designated-role').evaluate(e=>new FormData(e.form).get(e.name)),'faculty');
 await admin.locator('#email').fill('staff@example.com');assert.equal(await admin.locator('#email').evaluate(e=>e.validity.patternMismatch),true);
 record('Administrator themed role selection preserves form payload and university-email validation');
 assert.deepEqual(errors,[]);record('No browser JavaScript errors');report.passed=true;
}catch(error){report.passed=false;report.failure=error.stack||String(error);throw error}finally{fs.mkdirSync(path.resolve('tests/.runtime'),{recursive:true});fs.writeFileSync(path.resolve('tests/.runtime/layout-control-browser.json'),JSON.stringify(report,null,2));await browser.close()}})().catch(error=>{console.error(error);process.exitCode=1});
