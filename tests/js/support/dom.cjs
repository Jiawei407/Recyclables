'use strict';

// Small event/DOM doubles for isolated script logic. Browser layout, actual focus
// order, native constraint validation and file decoding need browser tests too.
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

class Element {
  constructor(properties = {}) {
    this.listeners = new Map();
    this.selectors = new Map();
    this.dataset = {};
    this.style = {};
    this.children = [];
    this.files = [];
    this.value = '';
    this.textContent = '';
    this.hidden = false;
    this.validityMessage = '';
    this.clicked = false;
    Object.assign(this, properties);
  }

  addEventListener(type, callback) {
    if (!this.listeners.has(type)) this.listeners.set(type, []);
    this.listeners.get(type).push(callback);
  }

  dispatch(type, properties = {}) {
    const event = {
      type,
      defaultPrevented: false,
      preventDefault() { this.defaultPrevented = true; },
      ...properties,
    };
    for (const callback of this.listeners.get(type) || []) callback(event);
    return event;
  }

  querySelector(selector) { return this.selectors.get(selector) || null; }
  querySelectorAll(selector) { return this.selectors.get(selector) || []; }
  setCustomValidity(message) { this.validityMessage = message; }
  replaceChildren(...children) { this.children = children; }
  focus() { this.ownerDocument.activeElement = this; }
  click() { this.clicked = true; }
  set innerHTML(_) { throw new Error('Scripts must not inject untrusted text as HTML.'); }
}

function environment({ singles = {}, multiples = {}, fixedNow, confirm = true } = {}) {
  const alerts = [];
  const confirmations = [];
  const urls = { created: [], revoked: [] };
  const document = {
    activeElement: null,
    querySelector: selector => singles[selector] || null,
    querySelectorAll: selector => multiples[selector] || [],
    createElement: tagName => new Element({ tagName, ownerDocument: document }),
  };
  for (const element of [...Object.values(singles), ...Object.values(multiples).flat()]) {
    if (element) element.ownerDocument = document;
  }
  const context = vm.createContext({
    document,
    window: {
      alert: message => alerts.push(message),
      confirm: message => { confirmations.push(message); return confirm; },
    },
    URL: {
      createObjectURL: file => {
        urls.created.push(file);
        return 'blob:test-' + urls.created.length;
      },
      revokeObjectURL: url => urls.revoked.push(url),
    },
    Date: fixedNow === undefined ? Date : class extends Date {
      constructor(...args) { super(...(args.length ? args : [fixedNow])); }
      static now() { return fixedNow; }
    },
  });
  return {
    document, alerts, confirmations, urls,
    run(script = 'app.js') {
      const filename = path.join(__dirname, '..', '..', '..', 'scripts', script);
      vm.runInContext(fs.readFileSync(filename, 'utf8'), context, { filename });
    },
    evaluate(source) { return vm.runInContext(source, context); },
  };
}

module.exports = { Element, environment };
