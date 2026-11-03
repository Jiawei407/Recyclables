/* Native-backed workspace controls. Form submission and PHP validation remain unchanged. */
(() => {
  'use strict';
  if (!document.documentElement.classList.contains('workspace-page') || !document.documentElement.classList.contains('clay-site')) return;

  const icon = paths => {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('class', 'clay-control-icon');
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('focusable', 'false');
    paths.forEach(d => {
      const path = document.createElementNS(svg.namespaceURI, 'path');
      path.setAttribute('d', d);
      svg.append(path);
    });
    return svg;
  };
  const fieldName = input => (input.labels?.[0]?.textContent.trim()) || input.getAttribute('aria-label') || input.name || 'value';

  document.querySelectorAll('input[type="number"]').forEach(input => {
    if (input.closest('.clay-number-control')) return;
    const wrapper = document.createElement('div');
    wrapper.className = 'clay-number-control';
    input.before(wrapper);
    wrapper.append(input);
    const lower = document.createElement('button'), higher = document.createElement('button');
    lower.type = higher.type = 'button';
    lower.className = higher.className = 'clay-step-button';
    lower.setAttribute('aria-label', 'Decrease ' + fieldName(input));
    higher.setAttribute('aria-label', 'Increase ' + fieldName(input));
    lower.append(icon(['m6 9 6 6 6-6']));
    higher.append(icon(['m6 15 6-6 6 6']));
    wrapper.append(lower, higher);
    const refresh = () => {
      const inactive = input.matches(':disabled') || input.readOnly || input.step === 'any';
      const value = input.valueAsNumber;
      lower.disabled = inactive || (input.value !== '' && input.min !== '' && value <= Number(input.min));
      higher.disabled = inactive || (input.value !== '' && input.max !== '' && value >= Number(input.max));
    };
    [[lower, 'stepDown'], [higher, 'stepUp']].forEach(([button, method]) => {
      button.addEventListener('click', () => {
        if (input.matches(':disabled') || input.readOnly || input.step === 'any') return;
        try {
          input[method]();
          input.dispatchEvent(new Event('input', {bubbles: true}));
          input.dispatchEvent(new Event('change', {bubbles: true}));
        } catch (_) {
          // The original field remains directly editable if native stepping is unavailable.
        }
        refresh();
      });
    });
    input.addEventListener('input', refresh);
    input.addEventListener('change', refresh);
    input.form?.addEventListener('reset', () => setTimeout(refresh));
    new MutationObserver(refresh).observe(input, {attributes: true, attributeFilter: ['disabled', 'readonly', 'min', 'max', 'step']});
    refresh();
  });

  document.querySelectorAll('input[type="date"],input[type="datetime-local"]').forEach(input => {
    if (input.closest('.clay-date-control')) return;
    const wrapper = document.createElement('div');
    wrapper.className = 'clay-date-control';
    input.before(wrapper);
    wrapper.append(input);
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'clay-calendar-button';
    button.setAttribute('aria-label', 'Choose ' + fieldName(input));
    button.append(icon(['M5 4h14a1 1 0 0 1 1 1v15H4V5a1 1 0 0 1 1-1Z', 'M8 2v4m8-4v4M4 10h16', 'M8 14h1m3 0h1m3 0h1M8 17h1m3 0h1']));
    wrapper.append(button);
    button.addEventListener('click', () => {
      if (input.matches(':disabled') || input.readOnly) return;
      try {
        if (typeof input.showPicker === 'function') input.showPicker();
        else input.focus();
      } catch (_) { input.focus(); }
    });
    const refresh = () => { button.disabled = input.matches(':disabled') || input.readOnly; };
    new MutationObserver(refresh).observe(input, {attributes: true, attributeFilter: ['disabled', 'readonly']});
    refresh();
  });
})();
