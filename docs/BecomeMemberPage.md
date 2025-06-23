# Become a Member Page

The **Become a Member** template introduces a front‑end landing page for membership information. Assign the template to a WordPress page whose URL ends in `/become-a-member/`.

## Overview
- Located at `includes/frontend/templates/become-member-page-template.php`.
- Registered by `TTA_Become_Member_Page`.
- Describes Basic vs. Premium benefits in a simple table.
- Shows pricing ($5 Basic, $10 Premium) in a dedicated row.
- Includes signup buttons embedded within the table. Clicking a button sends an AJAX request that stores the selected membership level in the session and redirects visitors to the cart page.

## Processing
Membership purchases are handled separately from one‑off ticket sales. The JavaScript on the page calls the `tta_add_membership` AJAX action which stores the chosen level in the visitor's session. Checkout can then call `TTA_AuthorizeNet_API::create_subscription()` to create a recurring subscription with Authorize.Net.
