/* Progressive selection controls. Original fields remain the source of values and validation. */
(() => {
  'use strict';
  if (!document.documentElement.classList.contains('clay-site') || document.documentElement.dataset.selectionControlsReady) return;
  document.documentElement.dataset.selectionControlsReady = 'true';
  let serial = 0;
  const open = [];
  const uid = name => 'selection-' + name + '-' + (++serial);
  const clean = text => text.replace(/\s+/g, ' ').trim();
  const icon = path => {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24'); svg.setAttribute('aria-hidden', 'true'); svg.setAttribute('class', 'selection-icon');
    const line = document.createElementNS(svg.namespaceURI, 'path'); line.setAttribute('d', path); svg.append(line); return svg;
  };
  const labelFor = field => clean(field.labels?.[0]?.textContent || field.getAttribute('aria-label') || field.name || 'Choose an option');
  const emit = field => { field.dispatchEvent(new Event('input', {bubbles: true})); field.dispatchEvent(new Event('change', {bubbles: true})); };
  function position(item) {
    if (!open.includes(item)) return;
    const r = item.anchor.getBoundingClientRect(), gutter = 8;
    const width = Math.min(item.width || Math.max(r.width, 200), window.innerWidth - gutter * 2);
    item.panel.style.width = width + 'px';
    const below = innerHeight - r.bottom - gutter - 6, above = r.top - gutter - 6;
    const up = below < Math.min(item.panel.scrollHeight, 260) && above > below;
    item.panel.style.maxHeight = Math.max(100, up ? above : below) + 'px';
    item.panel.style.left = Math.max(gutter, Math.min(r.left, innerWidth - width - gutter)) + 'px';
    const height = Math.min(item.panel.getBoundingClientRect().height, up ? above : below);
    item.panel.style.top = Math.max(gutter, up ? r.top - height - 6 : r.bottom + 6) + 'px';
  }
  function hide(item, returnFocus = false) {
    const index = open.indexOf(item); if (index < 0) return;
    for (const child of [...open].slice(index + 1).reverse()) hide(child);
    open.splice(open.indexOf(item), 1);
    if (typeof item.panel.hidePopover === 'function') { try { if (item.panel.matches(':popover-open')) item.panel.hidePopover(); } catch (_) {} }
    item.panel.hidden = true; item.anchor.setAttribute('aria-expanded', 'false');
    item.anchor.removeAttribute('aria-activedescendant');
    item.onClose?.();
    if (returnFocus && item.anchor.isConnected && !item.anchor.disabled) item.anchor.focus();
  }
  function show(item) {
    const parent = [...open].reverse().find(p => p.panel.contains(item.anchor));
    for (const previous of [...open].reverse()) if (previous !== parent) hide(previous);
    item.panel.hidden = false;
    if (typeof item.panel.showPopover === 'function') {
      try { item.panel.showPopover(); } catch (_) { /* Fixed-position fallback remains usable. */ }
    }
    open.push(item); item.anchor.setAttribute('aria-expanded', 'true'); position(item);
  }
  function popup(anchor, className, role, width, existing) {
    const panel = existing || document.createElement('div');
    panel.classList.add('selection-popup', className); panel.id ||= uid('popup'); panel.hidden = true;
    panel.setAttribute('role', role); if (!existing) panel.setAttribute('data-selection-ui', '');
    if (typeof panel.showPopover === 'function') panel.setAttribute('popover', 'manual');
    if (!existing) document.body.append(panel);
    anchor.setAttribute('aria-controls', panel.id); anchor.setAttribute('aria-expanded', 'false');
    anchor.setAttribute('aria-haspopup', role);
    return {anchor, panel, width};
  }
  document.addEventListener('pointerdown', event => {
    if (!open.some(p => p.panel.contains(event.target) || p.anchor.contains(event.target))) for (const item of [...open].reverse()) hide(item);
  });
  document.addEventListener('focusin', event => {
    if (!open.some(p => p.panel.contains(event.target) || p.anchor.contains(event.target))) for (const item of [...open].reverse()) hide(item);
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && open.length) { event.preventDefault(); event.stopPropagation(); hide(open[open.length - 1], true); }
  }, true);
  window.addEventListener('resize', () => open.forEach(position));
  window.addEventListener('scroll', () => open.forEach(position), true);

  function enhanceSelect(select) {
    if (select.multiple || select.size > 1 || select.dataset.selectionReady) return;
    const field = document.createElement('div'); field.className = 'selection-field';
    if (select.closest('.clay-header')) field.classList.add('selection-field--forest');
    if (select.hasAttribute('data-role-switch')) field.classList.add('selection-role');
    const trigger = document.createElement('button'); trigger.type = 'button'; trigger.className = 'selection-trigger';
    trigger.setAttribute('role', 'combobox'); trigger.setAttribute('aria-autocomplete', 'none');
    trigger.setAttribute('aria-label', labelFor(select)); trigger.setAttribute('data-selection-ui', '');
    const value = document.createElement('span'); value.className = 'selection-value';
    trigger.append(value, icon('m6 9 6 6 6-6'));
    select.before(field); field.append(select, trigger);
    const item = popup(trigger, 'selection-listbox', 'listbox'); item.panel.setAttribute('aria-label', labelFor(select));
    let active = -1, options = [], search = '', searchTimer;
    const resetSearch = () => { clearTimeout(searchTimer); search = ''; };
    item.onClose = resetSearch;
    const refresh = () => {
      value.textContent = select.selectedOptions[0]?.textContent || 'Choose an option';
      trigger.disabled = select.disabled; if (select.disabled) hide(item);
      trigger.setAttribute('aria-required', String(select.required));
      const described = select.getAttribute('aria-describedby');
      if (described) trigger.setAttribute('aria-describedby', described);
      trigger.setAttribute('aria-invalid', String(select.getAttribute('aria-invalid') === 'true' || (field.dataset.invalid === 'true' && !select.validity.valid)));
      if (open.includes(item)) options.forEach((element, index) => element.setAttribute('aria-selected', String(index === select.selectedIndex)));
    };
    const allowed = index => !select.disabled && index >= 0 && index < select.options.length && !select.options[index].disabled && !select.options[index].closest('optgroup[disabled]');
    const activate = index => {
      if (!allowed(index)) return;
      active = index;
      options.forEach((element, i) => element.classList.toggle('is-active', i === index));
      trigger.setAttribute('aria-activedescendant', options[index].id);
      options[index].scrollIntoView({block: 'nearest'});
    };
    const move = direction => {
      let index = active;
      for (let n = 0; n < select.options.length; n++) { index = (index + direction + select.options.length) % select.options.length; if (allowed(index)) { activate(index); break; } }
    };
    const choose = index => {
      if (!allowed(index)) return;
      select.selectedIndex = index; field.dataset.invalid = 'false'; refresh(); hide(item, true); emit(select);
    };
    const expand = (last = false) => {
      if (select.disabled) return;
      resetSearch();
      item.panel.replaceChildren(); options = [];
      let previousGroup;
      [...select.options].forEach((option, index) => {
        const group = option.parentElement.tagName === 'OPTGROUP' ? option.parentElement : null;
        if (group && group !== previousGroup) { const heading = document.createElement('div'); heading.className = 'selection-option-group'; heading.textContent = group.label; item.panel.append(heading); }
        previousGroup = group;
        const element = document.createElement('div'); element.className = 'selection-option'; element.id = uid('option');
        element.setAttribute('role', 'option'); element.setAttribute('aria-selected', String(option.selected));
        element.setAttribute('aria-disabled', String(!allowed(index))); element.textContent = option.textContent;
        element.addEventListener('pointermove', () => activate(index));
        element.addEventListener('pointerdown', event => event.preventDefault());
        element.addEventListener('click', () => choose(index)); options.push(element); item.panel.append(element);
      });
      show(item); active = -1;
      const selected = select.selectedIndex;
      if (allowed(selected)) activate(selected); else {
        const indices = [...select.options].map((_, index) => index).filter(allowed); activate(last ? indices.at(-1) : indices[0]);
      }
    };
    trigger.addEventListener('click', () => open.includes(item) ? hide(item, true) : expand());
    trigger.addEventListener('keydown', event => {
      if (['ArrowDown', 'ArrowUp', 'Home', 'End', 'Enter', ' '].includes(event.key)) {
        event.preventDefault();
        if (!open.includes(item)) { expand(event.key === 'ArrowUp' || event.key === 'End'); return; }
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') move(event.key === 'ArrowDown' ? 1 : -1);
        else if (event.key === 'Home' || event.key === 'End') {
          const indices = [...select.options].map((_, index) => index).filter(allowed); activate(event.key === 'Home' ? indices[0] : indices.at(-1));
        } else choose(active);
      } else if (event.key === 'Tab') hide(item);
      else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
        event.preventDefault(); if (!open.includes(item)) expand();
        clearTimeout(searchTimer); search += event.key.toLocaleLowerCase();
        searchTimer = setTimeout(() => { search = ''; }, 700);
        const repeated = [...search].every(letter => letter === search[0]);
        const term = repeated ? search[0] : search;
        const start = repeated ? active + 1 : 0;
        for (let n = 0; n < select.options.length; n++) { const index = (start + n) % select.options.length; if (allowed(index) && clean(select.options[index].textContent).toLocaleLowerCase().startsWith(term)) { activate(index); break; } }
      }
    });
    select.addEventListener('change', refresh); select.addEventListener('input', refresh);
    select.addEventListener('focus', () => trigger.focus());
    select.addEventListener('invalid', event => { event.preventDefault(); field.dataset.invalid = 'true'; refresh(); trigger.focus(); expand(); });
    [...(select.labels || [])].forEach(label => label.addEventListener('click', event => { if (!event.target.closest('a,button')) { event.preventDefault(); trigger.focus(); } }));
    select.form?.addEventListener('reset', () => setTimeout(() => { field.dataset.invalid = 'false'; hide(item); refresh(); }));
    new MutationObserver(refresh).observe(select, {subtree: true, childList: true, attributes: true, attributeFilter: ['disabled', 'required', 'selected', 'aria-invalid', 'aria-describedby']});
    select.classList.add('selection-native'); select.tabIndex = -1; select.setAttribute('aria-hidden', 'true'); select.dataset.selectionReady = 'true';
    refresh(); return {refresh, trigger};
  }
  document.querySelectorAll('select').forEach(enhanceSelect);

  // The challenge field keeps the original radios, labels, names, values and form binding.
  document.querySelectorAll('.challenge-choice-grid').forEach(grid => {
    const radios = [...grid.querySelectorAll('input[type=radio]')]; if (!radios.length) return;
    const legend = grid.closest('fieldset')?.querySelector('legend');
    const label = clean(legend?.textContent || 'Choose a challenge');
    const trigger = document.createElement('button'); trigger.type = 'button'; trigger.className = 'selection-trigger selection-challenge'; trigger.setAttribute('data-selection-ui', '');
    const value = document.createElement('span'); value.className = 'selection-value'; trigger.append(value, icon('m6 9 6 6 6-6')); grid.before(trigger);
    const item = popup(trigger, 'selection-radio-panel', 'dialog', null, grid); grid.setAttribute('aria-label', label); grid.setAttribute('aria-modal', 'false');
    const refresh = () => {
      const selected = radios.find(radio => radio.checked);
      value.textContent = selected ? clean(selected.labels?.[0]?.textContent || selected.value) : label;
      trigger.setAttribute('aria-label', label + ': ' + value.textContent); trigger.disabled = radios.every(radio => radio.disabled); if (trigger.disabled) hide(item);
      trigger.setAttribute('aria-invalid', String(radios.some(radio => radio.dataset.selectionInvalid === 'true' && !radio.validity.valid)));
    };
    const expand = () => { refresh(); show(item); (radios.find(radio => radio.checked && !radio.disabled) || radios.find(radio => !radio.disabled))?.focus(); };
    trigger.addEventListener('click', () => open.includes(item) ? hide(item, true) : expand());
    trigger.addEventListener('keydown', event => { if (event.key === 'ArrowDown' || event.key === 'ArrowUp') { event.preventDefault(); expand(); } });
    radios.forEach(radio => {
      radio.addEventListener('change', () => { radios.forEach(r => delete r.dataset.selectionInvalid); refresh(); hide(item, true); });
      radio.addEventListener('invalid', event => { event.preventDefault(); radio.dataset.selectionInvalid = 'true'; refresh(); trigger.focus(); expand(); });
    });
    radios.forEach(radio => new MutationObserver(refresh).observe(radio, {attributes: true, attributeFilter: ['disabled', 'required', 'checked']}));
    radios[0].form?.addEventListener('reset', () => setTimeout(() => { radios.forEach(radio => delete radio.dataset.selectionInvalid); hide(item); refresh(); })); refresh();
  });

  // Campaign inclusion is the only multi-choice checklist converted to a dropdown.
  // Permission and evidence-review checklists retain their original inline presentation.
  document.querySelectorAll('.check-list').forEach(list => {
    const boxes = () => [...list.querySelectorAll('input[type=checkbox][name="campaign-challenges[]"]')];
    if (!boxes().length) return;
    const label = clean(list.closest('fieldset')?.querySelector('legend')?.textContent || 'Included challenges');
    const trigger = document.createElement('button'); trigger.type = 'button'; trigger.className = 'selection-trigger selection-multi'; trigger.setAttribute('data-selection-ui', '');
    const value = document.createElement('span'); value.className = 'selection-value'; trigger.append(value, icon('m6 9 6 6 6-6')); list.before(trigger);
    const item = popup(trigger, 'selection-multi-panel', 'dialog', null, list); list.setAttribute('aria-label', label); list.setAttribute('aria-modal', 'false');
    const actions = document.createElement('div'); actions.className = 'selection-multi-actions'; actions.setAttribute('data-selection-ui', '');
    const done = document.createElement('button'); done.type = 'button'; done.className = 'selection-multi-done'; done.textContent = 'Done'; done.setAttribute('data-selection-ui', ''); actions.append(done); list.append(actions);
    const refresh = () => {
      const inputs = boxes(), selected = inputs.filter(box => box.checked);
      const names = selected.map(box => clean(box.labels?.[0]?.textContent || box.value));
      value.textContent = names.length === 0 ? 'Choose challenges' : names.length === 1 ? names[0] : names.length + ' challenges selected';
      trigger.setAttribute('aria-label', label + ': ' + value.textContent);
      trigger.title = names.join(', ');
      trigger.disabled = !inputs.length || inputs.every(box => box.disabled); if (trigger.disabled) hide(item);
      trigger.setAttribute('aria-invalid', String(inputs.some(box => box.dataset.selectionInvalid === 'true' && !box.validity.valid)));
    };
    const expand = () => { refresh(); if (trigger.disabled) return; show(item); (boxes().find(box => box.checked && !box.disabled) || boxes().find(box => !box.disabled))?.focus(); };
    trigger.addEventListener('click', () => open.includes(item) ? hide(item, true) : expand());
    trigger.addEventListener('keydown', event => { if (event.key === 'ArrowDown' || event.key === 'ArrowUp') { event.preventDefault(); expand(); } });
    list.addEventListener('change', event => {
      if (!boxes().includes(event.target)) return;
      boxes().forEach(box => delete box.dataset.selectionInvalid); refresh();
    });
    list.addEventListener('invalid', event => {
      if (!boxes().includes(event.target)) return;
      event.preventDefault(); event.target.dataset.selectionInvalid = 'true'; refresh(); expand();
    }, true);
    list.addEventListener('keydown', event => {
      if (!boxes().includes(event.target) || !['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
      event.preventDefault(); const available = boxes().filter(box => !box.disabled); let index = available.indexOf(event.target);
      if (event.key === 'Home') index = 0; else if (event.key === 'End') index = available.length - 1;
      else index = (index + (event.key === 'ArrowDown' ? 1 : -1) + available.length) % available.length;
      available[index]?.focus();
    });
    done.addEventListener('click', () => hide(item, true));
    boxes()[0].form?.addEventListener('reset', () => setTimeout(() => { boxes().forEach(box => delete box.dataset.selectionInvalid); hide(item); refresh(); }));
    new MutationObserver(refresh).observe(list, {subtree: true, childList: true, attributes: true, attributeFilter: ['disabled', 'required', 'checked']});
    refresh();
  });

  const DAY = 86400000;
  const makeDate = (year, month, day) => { const date = new Date(0); date.setUTCFullYear(year, month, day); date.setUTCHours(0, 0, 0, 0); return date; };
  const parseDate = value => {
    const match = /^(\d{4,})-(\d{2})-(\d{2})$/.exec(value || ''); if (!match) return null;
    const date = makeDate(+match[1], +match[2] - 1, +match[3]);
    return date.getUTCFullYear() === +match[1] && date.getUTCMonth() === +match[2] - 1 && date.getUTCDate() === +match[3] ? date : null;
  };
  const dateValue = date => String(date.getUTCFullYear()).padStart(4, '0') + '-' + String(date.getUTCMonth() + 1).padStart(2, '0') + '-' + String(date.getUTCDate()).padStart(2, '0');
  const today = () => { const date = new Date(); return makeDate(date.getFullYear(), date.getMonth(), date.getDate()); };
  const monthNames = Array.from({length: 12}, (_, index) => new Intl.DateTimeFormat('en', {month: 'long', timeZone: 'UTC'}).format(makeDate(2026, index, 1)));
  document.querySelectorAll('input[type=date],input[type=datetime-local]').forEach(input => {
    const dateTime = input.type === 'datetime-local';
    const inputDate = value => parseDate(dateTime ? (value || '').split('T')[0] : value);
    const candidateValue = date => dateValue(date) + (dateTime ? 'T' + (input.value.split('T')[1] || '00:00') : '');
    let wrapper = input.closest('.clay-date-control');
    if (!wrapper) { wrapper = document.createElement('div'); wrapper.className = 'clay-date-control'; input.before(wrapper); wrapper.append(input); }
    let trigger = wrapper.querySelector('.clay-calendar-button');
    if (!trigger) { trigger = document.createElement('button'); trigger.type = 'button'; trigger.className = 'clay-calendar-button'; trigger.append(icon('M5 4h14a1 1 0 0 1 1 1v15H4V5a1 1 0 0 1 1-1ZM8 2v4m8-4v4M4 10h16')); wrapper.append(trigger); }
    trigger.setAttribute('aria-label', 'Choose ' + labelFor(input)); trigger.setAttribute('data-selection-ui', '');
    const item = popup(trigger, 'selection-calendar', 'dialog', 340); item.panel.setAttribute('aria-label', 'Choose ' + labelFor(input)); item.panel.setAttribute('aria-modal', 'false');
    item.panel.innerHTML = '<div class="selection-calendar-head"><button type="button" class="selection-icon-button" data-previous aria-label="Previous month"></button><div class="selection-calendar-period"><select aria-label="Month" data-selection-ui></select><input type="number" inputmode="numeric" aria-label="Year" min="1" max="9999" data-selection-ui></div><button type="button" class="selection-icon-button" data-next aria-label="Next month"></button></div><div class="selection-weekdays" aria-hidden="true"><span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span></div><div class="selection-calendar-grid" role="grid"></div><div class="selection-calendar-footer"><button type="button" data-today>Today</button><button type="button" data-clear>Clear</button><button type="button" data-close>Close</button></div>';
    const previous = item.panel.querySelector('[data-previous]'), next = item.panel.querySelector('[data-next]');
    previous.append(icon('m14 6-6 6 6 6')); next.append(icon('m10 6 6 6-6 6'));
    const month = item.panel.querySelector('select'), year = item.panel.querySelector('input'), grid = item.panel.querySelector('[role=grid]');
    monthNames.forEach((name, index) => { const option = document.createElement('option'); option.value = index; option.textContent = name; month.append(option); });
    const monthControl = enhanceSelect(month);
    let cursor = inputDate(input.value) || today(), viewYear = cursor.getUTCFullYear(), viewMonth = cursor.getUTCMonth();
    const inBounds = date => {
      const min = inputDate(input.min), max = inputDate(input.max);
      return date.getUTCFullYear() >= 1 && date.getUTCFullYear() <= 9999 && (!min || date >= min) && (!max || date <= max);
    };
    const allowed = date => {
      if (!inBounds(date)) return false;
      if (dateTime) {
        const probe = document.createElement('input'); probe.type = 'datetime-local';
        for (const name of ['min', 'max', 'step', 'value']) if (input.hasAttribute(name)) probe.setAttribute(name, input.getAttribute(name));
        probe.value = candidateValue(date); return probe.validity.valid;
      }
      if (input.step === 'any') return true;
      const step = Number(input.step) > 0 ? Number(input.step) : 1;
      const base = parseDate(input.min) || parseDate(input.getAttribute('value')) || makeDate(1970, 0, 1);
      const quotient = (date - base) / (DAY * step); return Math.abs(quotient - Math.round(quotient)) < 1e-7;
    };
    const selectDate = date => {
      input.value = date ? candidateValue(date) : ''; emit(input); hide(item); input.focus();
    };
    function render(focus = false) {
      month.value = viewMonth; year.value = viewYear; monthControl.refresh();
      grid.replaceChildren(); grid.setAttribute('aria-label', monthNames[viewMonth] + ' ' + viewYear);
      const selected = dateTime ? input.value.split('T')[0] : input.value, currentDay = dateValue(today());
      const count = makeDate(viewYear, viewMonth + 1, 0).getUTCDate(), offset = makeDate(viewYear, viewMonth, 1).getUTCDay();
      const firstAllowed = Array.from({length: count}, (_, i) => makeDate(viewYear, viewMonth, i + 1)).find(allowed);
      if (!allowed(cursor) || cursor.getUTCMonth() !== viewMonth || cursor.getUTCFullYear() !== viewYear) cursor = firstAllowed || makeDate(viewYear, viewMonth, 1);
      for (let rowIndex = 0; rowIndex < Math.ceil((count + offset) / 7); rowIndex++) {
        const row = document.createElement('div'); row.setAttribute('role', 'row');
        for (let column = 0; column < 7; column++) {
          const day = rowIndex * 7 + column - offset + 1;
          if (day < 1 || day > count) { const blank = document.createElement('span'); blank.setAttribute('role', 'gridcell'); row.append(blank); continue; }
          const date = makeDate(viewYear, viewMonth, day), value = dateValue(date), button = document.createElement('button');
          button.type = 'button'; button.setAttribute('role', 'gridcell'); button.textContent = day; button.dataset.date = value;
          button.setAttribute('aria-label', new Intl.DateTimeFormat('en', {dateStyle: 'full', timeZone: 'UTC'}).format(date));
          button.setAttribute('aria-selected', String(selected === value)); if (value === currentDay) button.setAttribute('aria-current', 'date');
          button.disabled = !allowed(date); button.tabIndex = dateValue(cursor) === value && !button.disabled ? 0 : -1;
          button.addEventListener('click', () => selectDate(date)); row.append(button);
        }
        grid.append(row);
      }
      const min = inputDate(input.min), max = inputDate(input.max);
      previous.disabled = viewYear <= 1 && viewMonth === 0 || !!min && makeDate(viewYear, viewMonth, 0) < min;
      next.disabled = viewYear >= 9999 && viewMonth === 11 || !!max && makeDate(viewYear, viewMonth + 1, 1) > max;
      item.panel.querySelector('[data-today]').disabled = !allowed(today());
      if (open.includes(item)) { position(item); if (focus) grid.querySelector('button[tabindex="0"]')?.focus(); }
    }
    const changeMonth = amount => { const date = makeDate(viewYear, viewMonth + amount, 1); viewYear = date.getUTCFullYear(); viewMonth = date.getUTCMonth(); cursor = makeDate(viewYear, viewMonth, 1); render(); };
    previous.addEventListener('click', () => changeMonth(-1)); next.addEventListener('click', () => changeMonth(1));
    month.addEventListener('change', () => { viewMonth = Number(month.value); cursor = makeDate(viewYear, viewMonth, 1); render(); });
    year.addEventListener('change', () => { viewYear = Math.max(1, Math.min(9999, Math.trunc(Number(year.value) || viewYear))); cursor = makeDate(viewYear, viewMonth, 1); render(); });
    year.addEventListener('keydown', event => { if (event.key === 'Enter') { event.preventDefault(); year.dispatchEvent(new Event('change')); grid.querySelector('button[tabindex="0"]')?.focus(); } });
    item.panel.querySelector('[data-today]').addEventListener('click', () => selectDate(today()));
    item.panel.querySelector('[data-clear]').addEventListener('click', () => selectDate(null));
    item.panel.querySelector('[data-close]').addEventListener('click', () => hide(item, true));
    item.panel.addEventListener('keydown', event => {
      if (event.key !== 'Tab') return;
      const available = [...item.panel.querySelectorAll('button,input,select')].filter(element => !element.disabled && element.tabIndex >= 0 && !element.classList.contains('selection-native') && element.getClientRects().length);
      if (event.shiftKey && document.activeElement === available[0]) { event.preventDefault(); hide(item, true); }
      else if (!event.shiftKey && document.activeElement === available.at(-1)) {
        event.preventDefault(); hide(item);
        const sequence = [...document.querySelectorAll('a[href],button,input:not([type=hidden]),select,textarea,summary,[tabindex]')].filter(element => element.tabIndex >= 0 && !element.matches(':disabled') && !element.closest('[hidden],[inert],.selection-popup') && !element.classList.contains('selection-native') && element.getClientRects().length);
        (sequence[sequence.indexOf(trigger) + 1] || trigger).focus();
      }
    });
    grid.addEventListener('keydown', event => {
      if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown'].includes(event.key)) return;
      event.preventDefault(); const source = parseDate(document.activeElement.dataset.date) || cursor;
      let target;
      if (event.key === 'PageUp' || event.key === 'PageDown') {
        const amount = (event.key === 'PageUp' ? -1 : 1) * (event.shiftKey ? 12 : 1);
        const lastDay = makeDate(source.getUTCFullYear(), source.getUTCMonth() + amount + 1, 0).getUTCDate();
        target = makeDate(source.getUTCFullYear(), source.getUTCMonth() + amount, Math.min(source.getUTCDate(), lastDay));
      } else {
        const offsets = {ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7, Home: -source.getUTCDay(), End: 6 - source.getUTCDay()};
        target = new Date(+source + offsets[event.key] * DAY);
      }
      const direction = target < source ? -1 : 1;
      for (let attempts = 0; attempts < 3660 && inBounds(target) && !allowed(target); attempts++) target = new Date(+target + direction * DAY);
      if (!allowed(target)) return;
      cursor = target; viewYear = target.getUTCFullYear(); viewMonth = target.getUTCMonth(); render(true);
    });
    trigger.addEventListener('click', event => {
      event.preventDefault(); event.stopImmediatePropagation(); if (input.disabled || input.readOnly) return;
      if (open.includes(item)) { hide(item, true); return; }
      cursor = inputDate(input.value) || today();
      const min = inputDate(input.min), max = inputDate(input.max); if (min && cursor < min) cursor = min; if (max && cursor > max) cursor = max;
      viewYear = cursor.getUTCFullYear(); viewMonth = cursor.getUTCMonth(); render(); show(item); grid.querySelector('button[tabindex="0"]')?.focus();
    }, true);
    input.addEventListener('change', () => { if (open.includes(item)) render(); });
    input.form?.addEventListener('reset', () => setTimeout(() => hide(item)));
    const refreshDisabled = () => { trigger.disabled = input.disabled || input.readOnly; if (trigger.disabled) hide(item); };
    new MutationObserver(refreshDisabled).observe(input, {attributes: true, attributeFilter: ['disabled', 'readonly']}); refreshDisabled();
    render();
  });
})();
