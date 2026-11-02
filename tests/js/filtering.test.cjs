'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const { Element, environment } = require('./support/dom.cjs');

function filterFixture(records, { search = true, select = true } = {}) {
  const toolbar = new Element();
  const input = search ? new Element() : null;
  const dropdown = select ? new Element() : null;
  if (input) toolbar.selectors.set('input[type="search"]', input);
  if (dropdown) toolbar.selectors.set('select', dropdown);
  const world = environment({
    multiples: { '.toolbar': [toolbar], '[data-record]': records },
    // January exercises December/year-rollover selection independently of today.
    fixedNow: new Date(2026, 0, 15, 12).getTime(),
  });
  world.run();
  return { toolbar, input, dropdown };
}

test('mobile column labels are not searchable record data', () => {
  const row = new Element({ textContent: 'Status Alice active', dataset: { filterText: 'Alice active' } });
  const { input, toolbar } = filterFixture([row]);
  input.value = 'status'; toolbar.dispatch('input'); assert.equal(row.hidden, true);
  input.value = 'Alice'; toolbar.dispatch('input'); assert.equal(row.hidden, false);
});

test('search is case-insensitive and clearing it restores hidden records', () => {
  const bottle = new Element({ textContent: 'Recycle Plastic Bottles' });
  const cleanup = new Element({ textContent: 'Campus Cleanup' });
  const { input, toolbar } = filterFixture([bottle, cleanup]);
  input.value = 'pLASTIC';
  toolbar.dispatch('input');
  assert.equal(bottle.hidden, false);
  assert.equal(bottle.style.display, '');
  assert.equal(cleanup.hidden, true);
  assert.equal(cleanup.style.display, 'none');
  input.value = '';
  toolbar.dispatch('input');
  assert.equal(cleanup.hidden, false);
  assert.equal(cleanup.style.display, '');
});

test('search and selected category combine rather than overriding each other', () => {
  const bottle = new Element({ textContent: 'Bottle recycling' });
  const cup = new Element({ textContent: 'Bottle reuse' });
  const { input, dropdown, toolbar } = filterFixture([bottle, cup]);
  input.value = 'bottle';
  dropdown.value = 'Recycling';
  toolbar.dispatch('change');
  assert.equal(bottle.hidden, false);
  assert.equal(cup.hidden, true);
  input.value = 'no such activity';
  toolbar.dispatch('input');
  assert.equal(bottle.hidden, true);
  assert.equal(cup.hidden, true);
});

for (const [choice, status] of Object.entries({
  'Pending review': 'pending', 'Needs revision': 'needs-revision',
  'Registered user': 'user', 'Faculty representative': 'faculty',
  'Sustainability coordinator': 'coordinator', 'Administrator': 'admin',
})) {
  test(`filter maps "${choice}" to saved status "${status}"`, () => {
    const matching = new Element({ textContent: 'A record', dataset: { status } });
    const other = new Element({ textContent: 'Different record', dataset: { status: 'unrelated' } });
    const { dropdown, toolbar } = filterFixture([matching, other]);
    dropdown.value = choice;
    toolbar.dispatch('change');
    assert.equal(matching.hidden, false);
    assert.equal(other.hidden, true);
  });
}

for (const choice of ['All statuses', 'Choose category', 'Any status']) {
  test(`"${choice}" clears a previous filter`, () => {
    const record = new Element({ textContent: 'Unknown category' });
    const { dropdown, toolbar } = filterFixture([record]);
    dropdown.value = 'Cleanup';
    toolbar.dispatch('change');
    assert.equal(record.hidden, true);
    dropdown.value = choice;
    toolbar.dispatch('change');
    assert.equal(record.hidden, false);
    assert.equal(record.style.display, '');
  });
}

for (const choice of ['Current month', 'This month']) {
  test(`${choice} includes January records and excludes adjacent months and missing dates`, () => {
    const records = ['2026-01-01', '2026-01-31 23:59:59', '2025-12-31', '2026-02-01', ''].map(date =>
      new Element({ textContent: 'Activity', dataset: { date } }));
    const { dropdown, toolbar } = filterFixture(records);
    dropdown.value = choice;
    toolbar.dispatch('change');
    assert.deepEqual(records.map(record => record.hidden), [false, false, true, true, true]);
  });
}

for (const choice of ['Previous month', 'Last month']) {
  test(`${choice} crosses from January into December of the previous year`, () => {
    const records = ['2025-12-01', '2025-12-31', '2026-01-01', '2024-12-31'].map(date =>
      new Element({ textContent: 'Activity', dataset: { date } }));
    const { dropdown, toolbar } = filterFixture(records);
    dropdown.value = choice;
    toolbar.dispatch('change');
    assert.deepEqual(records.map(record => record.hidden), [false, false, true, true]);
  });
}

test('search-only and select-only toolbars operate without missing-control errors', () => {
  const searchRecord = new Element({ textContent: 'Cleanup' });
  const searchOnly = filterFixture([searchRecord], { select: false });
  searchOnly.input.value = 'recycling';
  searchOnly.toolbar.dispatch('input');
  assert.equal(searchRecord.hidden, true);
  const categoryRecord = new Element({ textContent: 'Recycling' });
  const selectOnly = filterFixture([categoryRecord], { search: false });
  selectOnly.dropdown.value = 'Recycling';
  selectOnly.toolbar.dispatch('change');
  assert.equal(categoryRecord.hidden, false);
});
