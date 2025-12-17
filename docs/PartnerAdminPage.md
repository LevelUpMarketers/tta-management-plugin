# Partner Admin Page Template

## Overview
The **Partner Admin Page** template provides the default layout for partner
admin pages generated when a new partner is created. It is located at
`includes/frontend/templates/partner-admin-page-template.php` and is
registered through `includes/frontend/class-partner-admin-page.php`, so it
shows up in the WordPress editor's **Page Attributes → Template** dropdown.

## Behavior
- **Login-first experience:** Visitors who are not logged in see the standard
  WordPress login form with the Trying To Adult styling and a link to reset
  their password. Successful logins redirect back to the same partner admin
  page so the visitor can continue immediately.
- **Logged-in placeholder:** Authenticated users see a simple placeholder
  message for now; partner-facing tools will be added in this area.
- **Auto-assignment:** When a partner is created via the admin UI, the
  `"{company} (admin)"` page is automatically set to use this template so the
  partner contact always lands on the login experience.

## Assets
The template reuses the login/register stylesheet to mirror existing
account-access styling. Assets are enqueued through `class-tta-assets.php`
whenever the template is active.
