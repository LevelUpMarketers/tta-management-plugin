# Partner Login Page Template

## Overview
The **Partner Login Page** template powers the partner-specific login pages
(created automatically as `"{company} Login"` during partner creation). It is
registered via `includes/frontend/class-partner-login-page.php` and can be
selected manually from the Page Attributes dropdown if needed.

## Behavior
- **Registration-only experience:** Displays the standard TTA registration form
  so invited partner users can create their WordPress accounts. The intro
  messaging references the specific partner name attached to the login page
  (from the partner row whose `signuppageid` matches the page).
- **Styling and scripts:** Reuses the login/register page assets for consistent
  layout, password toggles, and spinner/response messaging.
- **Auto-assignment:** The partner login page created during partner onboarding
  is automatically set to this template.
- **Partner-only signup flow:**
  - Uses `tta_partner_register` AJAX to enforce the same email/password checks
    as the general registration form.
  - Looks up the partner by the signup page ID and verifies the submitted email
    already exists in `tta_members.partner` for that partner’s unique
    identifier. If the email is missing, unlinked, or linked to another
    partner, the request is rejected with guidance to contact support.
  - Prevents creation when a WordPress account already exists for the email and
    returns a contact message with a `/contact` link.
  - On success, creates a subscriber WordPress user, logs them in, and updates
    the existing member row to set `wpuserid`, `membership_level = premium`,
    `subscription_status = active`, and refreshes `joined_at` before redirecting
    to `/events`.
