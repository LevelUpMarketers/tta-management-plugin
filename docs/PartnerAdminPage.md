# Partner Admin Page Template

## Overview
The **Partner Admin Page** template powers the pages automatically created for
each partner (`"{company} (admin)"`). It is registered via
`includes/frontend/class-partner-admin-page.php` and can be selected in the
Page Attributes dropdown.

## Behavior
- **Login-first experience:** Visitors who are not logged in see the standard
  WordPress login form. Successful logins return to the same page.
- **Access control:** Only the partner contact stored on the related
  `tta_partners.wpuserid` row or site administrators (`manage_options`) can
  view partner content; others see an access-restricted notice.
- **Logged-in dashboard:** Authorized users see a dashboard styled like the
  Member Dashboard with **Profile Info** (company/contact details) and **Your
  Licenses**, which offers a CSV upload (plus downloadable sample) to bulk
  create partner-linked members. Uploaded rows populate first name, last name,
  email, and the partner’s `uniquecompanyidentifier` into `tta_members.partner`.
- **Member offboarding:** Each member row in the license accordion includes a
  **No Longer Employed** button that updates the matching `tta_members` record
  to set `membership_level` to `free` and clears `subscription_status`.
- **Auto-assignment:** Partner admin pages created via the admin UI are
  automatically set to this template.

## Assets
The template reuses member-dashboard styling and runs lightweight JavaScript
to manage tab switching and license uploads.
