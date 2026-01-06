# Partner Login Page Template

## Overview
The **Partner Login Page** template powers the partner-specific login pages
(created automatically as `"{company} Login"` during partner creation). It is
registered via `includes/frontend/class-partner-login-page.php` and can be
selected manually from the Page Attributes dropdown if needed.

## Behavior
- **Registration-only experience:** Displays the standard TTA registration form
  so invited partner users can create their WordPress accounts.
- **Styling and scripts:** Reuses the login/register page assets for consistent
  layout, password toggles, and spinner/response messaging.
- **Auto-assignment:** The partner login page created during partner onboarding
  is automatically set to this template.

No additional access checks run here yet; future logic will validate partner
eligibility during submission.
