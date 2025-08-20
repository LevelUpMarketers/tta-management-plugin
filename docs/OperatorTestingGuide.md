# Operator Testing Guide

This document provides a repeatable process for ChatGPT Operator to validate every feature of the Trying To Adult Management Plugin on a live site.

## Preparation

1. Ensure the plugin is installed and activated.
2. Reference [TestingInformation.md](TestingInformation.md) for test account credentials. Each membership level and user role is represented there.
3. The debug log is available from **TTA Settings → Logging** at `/wp-admin/admin.php?page=tta-settings&tab=logging`.
4. Do **not** test the `/become-a-member/` page or other links in the site's header or footer.

## Testing Checklist

### 1. Authentication Scenarios
- Log in as every account listed in `TestingInformation.md`.
- For each account, visit the Member Dashboard (`/member-dashboard/`) and verify the expected tabs and information appear.
- Log out and verify that restricted pages prompt for authentication.

### 2. Event Pages
For each of the sample events (`/dinner-at-crawleys/`, `/roller-skating/`, `/buffet-besties-at-kings-korner/`):
1. Visit the page while logged out. Note the message under **Get Your Tickets Now** and confirm ticket controls behave as documented in [MembershipBenefits.md](MembershipBenefits.md).
2. Log in as each member type (Free, Basic, Premium) and repeat the visit.
3. Confirm that the attendee gallery, event type labels, and call-to-action buttons render correctly for hosts, volunteers, and regular attendees.
4. If ticket controls are disabled, hover to see the membership requirement tooltip.
5. Add tickets to the cart when allowed and proceed to the cart page to verify pricing.
6. Complete checkout and then revisit the event page to confirm the new attendees appear in the Attendees section.

### 3. Admin Pages
- Navigate to the plugin's admin menus:
  - `/wp-admin/admin.php?page=tta-events`
  - `/wp-admin/admin.php?page=tta-events&tab=manage`
  - `/wp-admin/admin.php?page=tta-tickets`
- `/wp-admin/admin.php?page=tta-settings`
- `/wp-admin/admin.php?page=tta-comms`
- Verify each template on the **Email & SMS** page. Click a row to reveal its inline form, edit the fields, then click **Save Changes** and wait for the spinner to disappear. Refresh the page to ensure the updated values persist. Each template should operate independently.
- Confirm that the Email and SMS previews update after editing fields and that the SMS character count turns red when over 160 characters.
- Ensure lists load without errors and that cache clearing and log viewing actions work.

### 4. Checkout and Cart
- From an event page, add the maximum allowed tickets and complete the checkout flow using an Authorize.Net test card (see `TestingInformation.md`).
- Verify the order appears in the **TTA Transactions** log and that attendee records are created.
- Remove items from the cart and confirm cleanup logic runs as expected.

### 5. Logging and Error Handling
- Review the debug log after each major action. Clear it between scenarios so new entries are easy to spot.
- Note any warnings or notices that appear and include them in your report.

## Reporting Results
Record findings in Markdown format. If multiple passes are required, create separate files. Include steps taken, observed behavior, and any errors or unexpected results.

