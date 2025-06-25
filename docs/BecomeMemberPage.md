# Become a Member Page

The **Become a Member** template introduces a front‑end landing page for membership information. Assign the template to a WordPress page whose URL ends in `/become-a-member/`.

## Overview
- Located at `includes/frontend/templates/become-member-page-template.php`.
- Registered by `TTA_Become_Member_Page`.
- Describes Basic vs. Premium benefits in a simple table.
- Shows pricing ($5 Basic, $10 Premium) in a dedicated row just above the signup buttons.
- Includes signup buttons embedded within the table. Clicking a button sends an AJAX request that stores the selected membership level in the session and redirects visitors to the cart page.
- The page enqueues `tta-cart.js` so the signup buttons behave like adding tickets to the cart and redirect immediately. The cart will show the chosen membership as a line item so visitors can check out normally.
- Visitors can complete checkout with just a membership selected—no tickets are required.
- Only one membership can exist in the cart at a time. Logged-in users who already have a Basic membership cannot add another Basic plan.

## Processing
Membership purchases are handled separately from one‑off ticket sales. The JavaScript on the page calls the `tta_add_membership` AJAX action which stores the chosen level in the visitor's session. Checkout will display this membership in the cart summary and use `TTA_AuthorizeNet_API::create_subscription()` to create a recurring subscription with Authorize.Net.
