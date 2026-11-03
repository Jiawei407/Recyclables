// Small JavaScript enhancements; PHP still validates everything on the server.
document.querySelectorAll('input[type="file"]').forEach(function (input) {
  var previewUrl = null;
  input.addEventListener('change', function () {
    if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
    var label = document.querySelector('[data-file-name="' + input.id + '"]');
    if (label) label.textContent = input.files.length ? input.files[0].name : 'Choose file';
    var preview = document.querySelector('[data-photo-preview]');
    if (preview && input.files.length && (/^image\//.test(input.files[0].type) || /\.hei[cf]$/i.test(input.files[0].name))) {
      var image = document.createElement('img');
      previewUrl = URL.createObjectURL(input.files[0]);
      image.src = previewUrl; image.alt = 'Selected photo'; image.width = 400;
      image.onload = function () { URL.revokeObjectURL(image.src); if (previewUrl === image.src) previewUrl = null; };
      image.onerror = function () {
        if (preview.children[0] !== image) return;
        var link = document.createElement('a'); link.href = image.src; link.download = input.files[0].name;
        link.textContent = 'Preview unavailable in this browser. Download the selected photo to view it.';
        preview.replaceChildren(link);
      };
      preview.replaceChildren(image);
    } else if (preview) preview.replaceChildren();
  });
});
// Private media remains behind file.php authorization; browsers without HEIC decoding get a real download.
document.querySelectorAll('img[src*="file.php"]').forEach(function (image) {
  image.addEventListener('error', function () {
    var link = document.createElement('a'); link.href = image.src; link.className = 'text-link';
    link.textContent = 'Image preview unavailable. Open or download the uploaded image.';
    link.target = '_blank'; link.rel = 'noopener'; image.replaceWith(link);
  });
});
var passwords = document.querySelectorAll('input[type="password"]');
passwords.forEach(function (input) {
  input.addEventListener('input', function () {
    var password = document.querySelector('#new-password, #password-new, #password');
    var confirm = document.querySelector('#confirm-password, #password-confirm');
    if (password && confirm) confirm.setCustomValidity(confirm.value && password.value !== confirm.value ? 'The passwords do not match.' : '');
  });
});

// Student identities use the APU student domain and a six-digit TP number.
var accountRole = document.querySelector('#designated-role');
if (accountRole) {
  function updateStudentFields() {
    var student = accountRole.value === 'user';
    var email = document.querySelector('#email');
    var universityId = document.querySelector('#university-id');
    if (email) {
      email.pattern = '[^\\s@]+@[mM][aA][iI][lL]\\.[aA][pP][uU]\\.[eE][dD][uU]\\.[mM][yY]';
      email.title = 'Use your @mail.apu.edu.my university email.';
      email.placeholder = student ? 'tp090526@mail.apu.edu.my' : 'admin@mail.apu.edu.my';
    }
    if (universityId) {
      universityId.pattern = student ? '[Tt][Pp][0-9]{6}' : '.{1,20}';
      universityId.maxLength = student ? 8 : 20;
      universityId.title = student ? 'TP followed by six digits, for example TP090526.' : 'Enter the staff or university ID.';
      universityId.placeholder = student ? 'TP090526' : 'University ID';
    }
  }
  accountRole.addEventListener('change', updateStudentFields);
  updateStudentFields();
}
document.querySelectorAll('.toolbar').forEach(function (toolbar) {
  function filterRecords() {
    var search = toolbar.querySelector('input[type="search"]');
    var select = toolbar.querySelector('select');
    var term = search ? search.value.toLowerCase() : '';
    var choice = select ? select.value.toLowerCase() : '';
    var statusChoices = {'pending review':'pending','needs revision':'needs-revision','registered user':'user','faculty representative':'faculty','sustainability coordinator':'coordinator','administrator':'admin'};
    choice = statusChoices[choice] || choice;
    if (/^(all|choose|any)/.test(choice)) choice = '';
    document.querySelectorAll('[data-record]').forEach(function (record) {
      var text = (record.dataset.filterText !== undefined ? record.dataset.filterText : record.textContent).toLowerCase();
      var status=record.dataset.status || '';
      var matchesChoice=!choice || text.includes(choice) || status===choice;
      if (choice==='current month' || choice==='this month' || choice==='previous month' || choice==='last month') {
        var now=new Date();
        var month=new Date(now.getFullYear(),now.getMonth()-(choice==='previous month'||choice==='last month'?1:0),1);
        var period=month.getFullYear()+'-'+String(month.getMonth()+1).padStart(2,'0');
        matchesChoice=(record.dataset.date || '').startsWith(period);
      }
      record.hidden = !text.includes(term) || !matchesChoice;
      record.style.display = record.hidden ? 'none' : '';
    });
  }
  toolbar.addEventListener('input', filterRecords);
  toolbar.addEventListener('change', filterRecords);
});
document.querySelectorAll('form[data-confirm]').forEach(function (form) {
  form.addEventListener('submit', function (event) {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  });
});
var dialog = document.querySelector('[role="dialog"]');
if (dialog) {
  function dialogControls() {
    return Array.from(dialog.querySelectorAll('a[href],button,input,select,textarea')).filter(function (control) {
      return control.type !== 'hidden' && !control.disabled && !control.hidden && control.tabIndex !== -1 &&
        !(typeof control.closest === 'function' && control.closest('[hidden],[inert]')) &&
        !(typeof control.getClientRects === 'function' && !control.getClientRects().length);
    });
  }
  var focusables = dialogControls();
  if (focusables.length) focusables[0].focus();
  dialog.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') { var close=dialog.querySelector('.close-button'); if (close) close.click(); }
    focusables = dialogControls();
    if (event.key !== 'Tab' || !focusables.length) return;
    var first=focusables[0], last=focusables[focusables.length-1];
    if (event.shiftKey && document.activeElement === first) { last.focus(); event.preventDefault(); }
    if (!event.shiftKey && document.activeElement === last) { first.focus(); event.preventDefault(); }
  });
}
