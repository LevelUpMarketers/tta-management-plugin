/**
 * Handle top bar logout link.
 */
document.addEventListener('DOMContentLoaded', function () {
    if ( typeof TTALogout === 'undefined' ) {
        return;
    }
    var link = document.querySelector('.tta-header-logout-div a');
    if ( link ) {
        link.setAttribute('href', TTALogout.url);
    }
});
