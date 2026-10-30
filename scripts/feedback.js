// A single reusable browser alert for PHP success and validation messages.
function showPopupMessage() {
  var notice = document.querySelector('[data-popup-message]');
  if (notice && notice.dataset.popupMessage && !notice.dataset.popupShown) {
    notice.dataset.popupShown = 'true';
    window.alert(notice.dataset.popupMessage);
  }
}
showPopupMessage();
