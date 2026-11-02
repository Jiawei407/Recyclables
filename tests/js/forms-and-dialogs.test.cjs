'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const { Element, environment } = require('./support/dom.cjs');

function passwordFixture() {
  const password = new Element();
  const confirm = new Element();
  const world = environment({
    singles: {
      '#new-password, #password-new, #password': password,
      '#confirm-password, #password-confirm': confirm,
    },
    multiples: { 'input[type="password"]': [password, confirm] },
  });
  world.run();
  return { password, confirm };
}

test('mismatched confirmation gives a useful validation error and correction clears it', () => {
  const { password, confirm } = passwordFixture();
  password.value = 'first password!';
  confirm.value = 'different password!';
  confirm.dispatch('input');
  assert.equal(confirm.validityMessage, 'The passwords do not match.');
  confirm.value = password.value;
  confirm.dispatch('input');
  assert.equal(confirm.validityMessage, '');
});

test('changing the first password revalidates an already-entered confirmation', () => {
  const { password, confirm } = passwordFixture();
  password.value = confirm.value = 'initial password!';
  password.dispatch('input');
  assert.equal(confirm.validityMessage, '');
  password.value = 'new password!';
  password.dispatch('input');
  assert.notEqual(confirm.validityMessage, '');
});

test('empty confirmation does not create a mismatch error before native required validation', () => {
  const { password, confirm } = passwordFixture();
  password.value = 'valid password!';
  confirm.value = '';
  confirm.dispatch('input');
  assert.equal(confirm.validityMessage, '');
});

for (const choice of [true, false]) {
  test(`destructive form ${choice ? 'continues after confirmation' : 'is cancelled when confirmation is declined'}`, () => {
    const form = new Element({ dataset: { confirm: 'Delete this account?' } });
    const world = environment({ multiples: { 'form[data-confirm]': [form] }, confirm: choice });
    world.run();
    const event = form.dispatch('submit');
    assert.deepEqual(world.confirmations, ['Delete this account?']);
    assert.equal(event.defaultPrevented, !choice);
  });
}

function dialogFixture({ empty = false, close = true } = {}) {
  const dialog = new Element();
  const first = new Element();
  const middle = new Element();
  const last = new Element();
  const closeButton = close ? new Element() : null;
  const world = environment({ singles: { '[role="dialog"]': dialog } });
  for (const control of [first, middle, last]) control.ownerDocument = world.document;
  dialog.selectors.set('a[href],button,input,select,textarea', empty ? [] : [first, middle, last]);
  if (closeButton) dialog.selectors.set('.close-button', closeButton);
  world.run();
  return { world, dialog, first, middle, last, closeButton };
}

test('dialog initially focuses its first control', () => {
  const { world, first } = dialogFixture();
  assert.equal(world.document.activeElement, first);
});

test('hidden CSRF and disabled fields cannot become the modal focus boundary', () => {
  const { dialog, first, last, world } = dialogFixture();
  dialog.selectors.set('a[href],button,input,select,textarea', [new Element({type:'hidden'}),first,last,new Element({disabled:true})]);
  last.focus();dialog.dispatch('keydown',{key:'Tab',shiftKey:false});assert.equal(world.document.activeElement,first);
  first.focus();dialog.dispatch('keydown',{key:'Tab',shiftKey:true});assert.equal(world.document.activeElement,last);
});

test('Tab on the final control wraps to the first and prevents focus escaping', () => {
  const { world, dialog, first, last } = dialogFixture();
  last.focus();
  const event = dialog.dispatch('keydown', { key: 'Tab', shiftKey: false });
  assert.equal(world.document.activeElement, first);
  assert.equal(event.defaultPrevented, true);
});

test('Shift+Tab on the first control wraps to the final control', () => {
  const { world, dialog, first, last } = dialogFixture();
  first.focus();
  const event = dialog.dispatch('keydown', { key: 'Tab', shiftKey: true });
  assert.equal(world.document.activeElement, last);
  assert.equal(event.defaultPrevented, true);
});

test('Tab inside the dialog leaves normal browser focus movement unblocked', () => {
  const { dialog, middle } = dialogFixture();
  middle.focus();
  assert.equal(dialog.dispatch('keydown', { key: 'Tab', shiftKey: false }).defaultPrevented, false);
  assert.equal(dialog.dispatch('keydown', { key: 'Tab', shiftKey: true }).defaultPrevented, false);
});

test('Escape activates the dialog close action', () => {
  const { dialog, closeButton } = dialogFixture();
  dialog.dispatch('keydown', { key: 'Escape' });
  assert.equal(closeButton.clicked, true);
});

test('an empty dialog or missing close action does not throw on keyboard input', () => {
  const { world, dialog } = dialogFixture({ empty: true, close: false });
  assert.equal(world.document.activeElement, null);
  assert.equal(dialog.dispatch('keydown', { key: 'Tab' }).defaultPrevented, false);
  assert.doesNotThrow(() => dialog.dispatch('keydown', { key: 'Escape' }));
});

test('script remains safe on a page without enhanced controls', () => {
  assert.doesNotThrow(() => environment().run());
});
