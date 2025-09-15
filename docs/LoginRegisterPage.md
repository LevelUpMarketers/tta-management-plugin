# Login or Create Account Page

## Overview
The **Login or Create Account Page** template provides a dedicated landing
page where visitors can either log in with their existing WordPress account
or register for a new Trying To Adult account. The template is located at
`includes/frontend/templates/login-register-page-template.php` and can be
assigned to any WordPress page via the **Page Attributes → Template**
dropdown in the editor.

When the template is active the plugin enqueues a lightweight stylesheet
(`assets/css/frontend/login-register.css`) that presents the login and
registration forms in a responsive two-column layout (stacking on mobile),
as well as a small JavaScript controller
(`assets/js/frontend/login-register-page.js`) that drives the registration
flow.

## Features
- **WordPress login form** on the left-hand column. Users are redirected to
  the Events Listings page (`/events`) after a successful login. A “Forgot
your password?” link points to the standard WordPress password reset
screen.
- **Custom registration form** on the right-hand column that mirrors the
  validation rules used across the site (matching email/password pairs and
  strong password requirements). Registration happens via the existing
  `tta_register` AJAX endpoint.
- **Automatic redirect** to the Events Listings page after either a
  successful login or a successful account creation. The redirect
  destination can be changed with the
  `tta_login_register_redirect_url` filter if required.
- **Cart refresh on login** – as soon as a visitor logs in (either through
  the embedded form or elsewhere) the helper
  `tta_refresh_cart_session_for_user()` recalculates any cart items stored in
their current session so the prices reflect the user’s membership level.
  Existing cart contents are preserved, but cached notices and stale
  checkout keys are cleared to prevent mismatched pricing.

## Registration Flow
1. Users fill in the registration form and submit it.
2. `assets/js/frontend/login-register-page.js` validates the input client-
   side and posts the data to the `tta_register` AJAX handler.
3. Upon success the handler creates the WordPress user, logs them in, and
   triggers the `wp_login` action so the cart refresh runs immediately.
4. The browser redirects to `/events` (or the filtered URL) after displaying
   a brief success message.

Any registration errors are surfaced inline by the script, using the same
localized strings as other registration experiences within the plugin.

## Styling Notes
- The CSS targets the `.tta-account-access` wrapper so the styles apply only
  to this template.
- The spinner image (`assets/images/admin/loading.svg`) is reused from other
  admin-style progress indicators and is hidden by default until the form is
  submitting.

## Related Helpers
- `tta_refresh_cart_session_for_user( $user_id )` – recalculates the active
  cart session pricing based on the logged-in user’s membership level while
  preserving cart contents.
- `tta_login_redirect` – now respects the requested redirect URL when one is
  supplied, allowing the login form to send users directly to `/events`.

These helpers live in `includes/helpers.php` and are shared across any login
flow inside the plugin.
