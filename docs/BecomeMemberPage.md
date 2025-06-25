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
- Only one membership can exist in the cart at a time. Logged-in users who already have a Basic membership cannot add another Basic plan. Premium members cannot add any membership product.
- When only a membership is present in the cart, the subtotal and total rows show the price "Per Month" and table columns remain aligned.
- When tickets are also in the cart, the total row shows the immediate charge followed by the monthly membership amount (e.g. `$15.00 today, $5 Per Month`).

## Processing
Membership purchases are handled separately from one‑off ticket sales. The JavaScript on the page calls the `tta_add_membership` AJAX action which stores the chosen level in the visitor's session. Checkout will display this membership in the cart summary and use `TTA_AuthorizeNet_API::create_subscription()` to create a recurring subscription with Authorize.Net.
The subscription ID returned by the API is stored on the member record for future cancellation and reporting.
Each subscription uses a consistent name and description depending on the level:
"Trying to Adult Basic Membership" or "Trying to Adult Premium Membership" with matching descriptions.
