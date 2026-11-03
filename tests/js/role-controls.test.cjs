'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

// Isolated native-control doubles; actual popup/focus behavior is tested in Chrome.
function fixture({rolePage = true, numbers = [], dates = []} = {}) {
  class Element {
    constructor(tagName = 'div') { this.tagName = tagName; this.children = []; this.attributes = {}; this.listeners = {}; this.className = ''; }
    append(...children) { for (const child of children) { if (child.parentElement) child.parentElement.children.splice(child.parentElement.children.indexOf(child), 1); child.parentElement = this; this.children.push(child); } }
    before(element) { const parent = this.parentElement; element.parentElement = parent; parent.children.splice(parent.children.indexOf(this), 0, element); }
    closest(selector) { for (let node = this.parentElement; node; node = node.parentElement) if (node.className === selector.slice(1)) return node; return null; }
    setAttribute(name, value) { this.attributes[name] = value; }
    getAttribute(name) { return this.attributes[name] ?? null; }
    matches() { return !!this.disabled; }
    addEventListener(type, handler) { (this.listeners[type] ||= []).push(handler); }
    dispatchEvent(event) { (this.listeners[event.type] || []).forEach(handler => handler(event)); return !event.defaultPrevented; }
    focus() { this.focused = true; }
  }
  const form = new Element('form'); form.id = 'backend-form';
  const inputs = [...numbers.map(properties => ({type: 'number', ...properties})), ...dates.map(properties => ({type: 'date', ...properties}))].map(properties => {
    const input = Object.assign(new Element('input'), {min: '', max: '', step: '', value: '', readOnly: false, disabled: false, name: 'quantity', labels: [{textContent: 'Quantity'}]}, properties);
    input.form = form;
    Object.defineProperty(input, 'valueAsNumber', {get() { return Number(this.value); }});
    input.stepCalls = [];
    input.stepUp = () => { input.stepCalls.push('up'); input.value = String(Math.min(input.max === '' ? Infinity : Number(input.max), Number(input.value) + Number(input.step || 1))); };
    input.stepDown = () => { input.stepCalls.push('down'); input.value = String(Math.max(input.min === '' ? -Infinity : Number(input.min), Number(input.value) - Number(input.step || 1))); };
    form.append(input); return input;
  });
  const observers = [], documentEvents = [];
  const document = {
    documentElement: {classList: {contains: () => rolePage}},
    querySelectorAll: selector => inputs.filter(input => selector.includes('"' + input.type + '"')),
    createElement: tag => new Element(tag), createElementNS: (_, tag) => new Element(tag),
    addEventListener: type => documentEvents.push(type),
  };
  const context = vm.createContext({document, Event: class { constructor(type, options) { this.type = type; this.bubbles = options?.bubbles; this.defaultPrevented = false; } preventDefault() { this.defaultPrevented = true; } }, MutationObserver: class { constructor(callback) { this.callback = callback; } observe(target) { observers.push({target, callback: this.callback}); } }, setTimeout: callback => callback()});
  vm.runInContext(fs.readFileSync(path.join(__dirname, '../../scripts/role-controls.js'), 'utf8'), context);
  return {inputs, form, documentEvents, refresh: input => observers.filter(observer => observer.target === input).forEach(observer => observer.callback())};
}

test('quantity buttons honor native bounds/step and retain the original form field', () => {
  const {inputs: [input], form, documentEvents} = fixture({numbers: [{value: '2', min: '2', max: '6', step: '2', id: 'quantity', name: 'quantity', required: true}]});
  const wrapper = input.parentElement, lower = wrapper.children[1], higher = wrapper.children[2];
  assert.equal(wrapper.className, 'clay-number-control');
  assert.equal(lower.disabled, true); assert.equal(higher.disabled, false);
  assert.equal(lower.type, 'button'); assert.equal(higher.type, 'button');
  higher.dispatchEvent({type: 'click'}); assert.equal(input.value, '4');
  higher.dispatchEvent({type: 'click'}); assert.equal(input.value, '6'); assert.equal(higher.disabled, true);
  lower.dispatchEvent({type: 'click'}); assert.equal(input.value, '4');
  assert.equal(input.form, form); assert.equal(input.id, 'quantity'); assert.equal(input.name, 'quantity'); assert.equal(input.required, true);
  assert.equal(input.min, '2'); assert.equal(input.max, '6'); assert.equal(input.step, '2');
  assert.deepEqual(documentEvents, []); // Real form submission is never intercepted.
});

test('read-only, disabled and step-any fields cannot be stepped', () => {
  const world = fixture({numbers: [{value: '3', min: '1', max: '9'}]});
  const input = world.inputs[0], [lower, higher] = input.parentElement.children.slice(1);
  for (const property of ['readOnly', 'disabled']) {
    input[property] = true; world.refresh(input); assert.equal(lower.disabled, true); assert.equal(higher.disabled, true);
    higher.dispatchEvent({type: 'click'}); assert.equal(input.value, '3'); input[property] = false;
  }
  input.step = 'any'; world.refresh(input); assert.equal(higher.disabled, true);
  higher.dispatchEvent({type: 'click'}); assert.deepEqual(input.stepCalls, []);
});

test('calendar wrapper keeps editable native date/time fields and falls back to focus', () => {
  const world = fixture({dates: [{type: 'datetime-local', id: 'activity-date', name: 'activity-date', value: '2026-11-01T14:35', min: '2026-11-01T08:00', required: true}]});
  const input = world.inputs[0], trigger = input.parentElement.children[1];
  assert.equal(input.parentElement.className, 'clay-date-control'); assert.equal(trigger.type, 'button');
  trigger.dispatchEvent({type: 'click'}); assert.equal(input.focused, true);
  input.focused = false; let pickerCalls = 0; input.showPicker = () => { pickerCalls++; };
  trigger.dispatchEvent({type: 'click'}); assert.equal(pickerCalls, 1);
  input.readOnly = true; world.refresh(input); assert.equal(trigger.disabled, true);
  trigger.dispatchEvent({type: 'click'}); assert.equal(pickerCalls, 1);
  assert.equal(input.value, '2026-11-01T14:35'); assert.equal(input.min, '2026-11-01T08:00'); assert.equal(input.form, world.form); assert.equal(input.required, true);
});

test('public pages do not receive workspace control wrappers', () => {
  const world = fixture({rolePage: false, numbers: [{value: '1'}]});
  assert.equal(world.inputs[0].parentElement, world.form); assert.equal(world.form.children.length, 1);
});
