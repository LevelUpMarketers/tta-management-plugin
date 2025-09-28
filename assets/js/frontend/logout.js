/**
 * Handle top bar logout link.
 */
(function () {
    function updateLogoutLink() {
        if ( typeof TTALogout === 'undefined' ) {
            return;
        }

        var container = document.querySelector('.tta-header-logout-div');
        if ( ! container ) {
            return;
        }

        var link = container.querySelector('a');
        if ( ! link ) {
            return;
        }

        if ( TTALogout.loggedIn ) {
            link.setAttribute('href', TTALogout.url);
            if ( TTALogout.name ) {
                link.innerHTML = link.innerHTML.replace('[USER FIRST NAME]', TTALogout.name);
            }
        } else {
            link.setAttribute('href', TTALogout.loginUrl);
            link.textContent = TTALogout.loginLabel || 'Log In';
        }
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener('DOMContentLoaded', updateLogoutLink);
    } else {
        updateLogoutLink();
    }
})();
