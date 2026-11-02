'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const { Element, environment } = require('./support/dom.cjs');

function uploadFixture({ label = true, preview = true } = {}) {
  const input = new Element({ id: 'photo' });
  const filename = label ? new Element() : null;
  const picture = preview ? new Element() : null;
  const singles = {};
  if (filename) singles['[data-file-name="photo"]'] = filename;
  if (picture) singles['[data-photo-preview]'] = picture;
  const world = environment({ singles, multiples: { 'input[type="file"]': [input] } });
  world.run();
  return { world, input, filename, picture };
}

test('a HEIC image without browser decoding retains a download fallback', () => {
  const { input, picture } = uploadFixture();
  input.files = [{ name: 'Phone.HEIC', type: '' }]; input.dispatch('change');
  picture.children[0].onerror();
  assert.equal(picture.children[0].tagName, 'a');
  assert.equal(picture.children[0].href, 'blob:test-1');
  assert.equal(picture.children[0].download, 'Phone.HEIC');
});

test('a replaced image cannot overwrite a newer preview after a late error', () => {
  const { input, picture } = uploadFixture();
  input.files = [{ name: 'first.heic', type: 'image/heic' }]; input.dispatch('change');
  const previous = picture.children[0];
  input.files = [{ name: 'second.png', type: 'image/png' }]; input.dispatch('change');
  const current = picture.children[0]; previous.onerror();
  assert.equal(picture.children[0], current);
});

test('selecting a photo displays its filename as plain text and creates a preview', () => {
  const { world, input, filename, picture } = uploadFixture();
  const file = { name: '<img onerror=alert(1)>.jpg', type: 'image/jpeg' };
  input.files = [file];
  input.dispatch('change');
  assert.equal(filename.textContent, file.name);
  assert.equal(picture.children.length, 1);
  assert.equal(picture.children[0].tagName, 'img');
  assert.equal(picture.children[0].alt, 'Selected photo');
  assert.equal(picture.children[0].src, 'blob:test-1');
  assert.deepEqual(world.urls.created, [file]);
  assert.deepEqual(world.alerts, []);
});

test('loaded image releases its temporary preview URL', () => {
  const { world, input, picture } = uploadFixture();
  input.files = [{ name: 'photo.png', type: 'image/png' }];
  input.dispatch('change');
  picture.children[0].onload();
  assert.deepEqual(world.urls.revoked, ['blob:test-1']);
});

test('a second selected image replaces the previous preview rather than appending', () => {
  const { input, filename, picture } = uploadFixture();
  input.files = [{ name: 'first.png', type: 'image/png' }];
  input.dispatch('change');
  const first = picture.children[0];
  input.files = [{ name: 'second.jpg', type: 'image/jpeg' }];
  input.dispatch('change');
  assert.equal(filename.textContent, 'second.jpg');
  assert.equal(picture.children.length, 1);
  assert.notEqual(picture.children[0], first);
});

test('clearing the native picker restores the filename prompt', () => {
  const { input, filename } = uploadFixture({ preview: false });
  input.files = [{ name: 'first.png', type: 'image/png' }];
  input.dispatch('change');
  input.files = [];
  input.dispatch('change');
  assert.equal(filename.textContent, 'Choose file');
});

test('clearing a selected photo removes its preview and releases its URL', () => {
  const { world, input, filename, picture } = uploadFixture();
  input.files = [{ name: 'first.png', type: 'image/png' }];
  input.dispatch('change');
  const image = picture.children[0];
  input.files = [];
  input.dispatch('change');
  assert.equal(filename.textContent, 'Choose file');
  assert.equal(picture.children.length, 0);
  assert.deepEqual(world.urls.revoked, ['blob:test-1']);
  image.onerror();
  assert.equal(picture.children.length, 0);
});

test('choosing PDF after a failed HEIC preview removes its old download link', () => {
  const { world, input, filename, picture } = uploadFixture();
  input.files = [{ name: 'Phone.HEIC', type: '' }];
  input.dispatch('change');
  picture.children[0].onerror();
  assert.equal(picture.children[0].tagName, 'a');
  input.files = [{ name: 'report.pdf', type: 'application/pdf' }];
  input.dispatch('change');
  assert.equal(filename.textContent, 'report.pdf');
  assert.equal(picture.children.length, 0);
  assert.deepEqual(world.urls.revoked, ['blob:test-1']);
  assert.equal(world.urls.created.length, 1);
});

test('a PDF filename is shown without pretending it is an image preview', () => {
  const { world, input, filename, picture } = uploadFixture();
  input.files = [{ name: 'report.pdf', type: 'application/pdf' }];
  input.dispatch('change');
  assert.equal(filename.textContent, 'report.pdf');
  assert.equal(picture.children.length, 0);
  assert.equal(world.urls.created.length, 0);
});

test('file selection tolerates pages without filename or preview elements', () => {
  const { input } = uploadFixture({ label: false, preview: false });
  input.files = [{ name: 'photo.jpg', type: 'image/jpeg' }];
  assert.doesNotThrow(() => input.dispatch('change'));
});

test('feedback displays the original message once as safe text', () => {
  const message = '<script>globalThis.unexpectedExecution = true</script> Saved & ready';
  const notice = new Element({ dataset: { popupMessage: message } });
  const world = environment({ singles: { '[data-popup-message]': notice } });
  world.run('feedback.js');
  world.evaluate('showPopupMessage(); showPopupMessage();');
  assert.deepEqual(world.alerts, [message]);
  assert.equal(notice.dataset.popupShown, 'true');
  assert.equal(world.evaluate('typeof unexpectedExecution'), 'undefined');
});

test('feedback does not redisplay a notice marked as already shown', () => {
  const notice = new Element({ dataset: { popupMessage: 'Saved', popupShown: 'true' } });
  const world = environment({ singles: { '[data-popup-message]': notice } });
  world.run('feedback.js');
  assert.deepEqual(world.alerts, []);
});

test('missing and empty feedback notices do not produce blank alerts', () => {
  const missing = environment();
  missing.run('feedback.js');
  assert.deepEqual(missing.alerts, []);
  const empty = environment({ singles: { '[data-popup-message]': new Element({ dataset: { popupMessage: '' } }) } });
  empty.run('feedback.js');
  assert.deepEqual(empty.alerts, []);
});
