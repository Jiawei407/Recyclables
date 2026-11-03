'use strict';
const test=require('node:test'),assert=require('node:assert/strict');
const {Element,environment}=require('./support/dom.cjs');
for(const value of ['user','faculty','coordinator','admin'])test('browser identity rules match university policy for '+value,()=>{
 const role=new Element({value}),email=new Element(),identity=new Element();
 const world=environment({singles:{'#designated-role':role,'#email':email,'#university-id':identity}});world.run();
 const pattern=new RegExp('^(?:'+email.pattern+')$');
 assert.equal(pattern.test('account@mail.apu.edu.my'),true);
 assert.equal(pattern.test('account@MAIL.APU.EDU.MY'),true);
 assert.equal(pattern.test('account@example.com'),false);
 assert.equal(pattern.test('account@mail.apu.edu.my.evil.test'),false);
 if(value==='user')assert.equal(new RegExp('^(?:'+identity.pattern+')$').test('tp123456'),true);
});
