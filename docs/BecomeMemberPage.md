# Become a Member Page

The **Become a Member** template introduces a front‑end landing page for membership information. Assign the template to a WordPress page whose URL ends in `/become-a-member/`.

## Overview
- Located at `includes/frontend/templates/become-member-page-template.php`.
- Registered by `TTA_Become_Member_Page`.
- Describes Non-member, Standard Member, and Premium Member benefits in a three-column table with a mobile-friendly card layout.
- Shows pricing ($0 Non-member, $10 Standard, $17 Premium) in a dedicated row just above the signup buttons.
- Displays an introductory row above the table with a rotating image gallery. Images come from the **TTA Settings → Slider Images** tab.
- Includes signup buttons for Standard and Premium tiers embedded within the table. Clicking a button sends an AJAX request that stores the selected membership level in the session and redirects visitors to the cart page.
- Signup buttons for Standard and Premium tiers use the `.tta-basic-signup` and `.tta-premium-signup` classes so both table and mobile views trigger the same AJAX handler.
- Offers a **Join Now** button in the Non-member column that reveals an inline registration form for creating a free account.
- The page enqueues `tta-cart.js` so the signup buttons behave like adding tickets to the cart and redirect immediately. The cart will show the chosen membership as a line item so visitors can check out normally.
- A dedicated stylesheet (`assets/css/frontend/become-member.css`) targets the page's intro layout, membership table, and mobile cards and is only loaded for this template. Responsive tweaks adjust padding, card borders, and center the signup buttons on screens narrower than 960px.
- Visitors can complete checkout with just a membership selected—no tickets are required.
- Only one membership can exist in the cart at a time. Logged-in users who already have a Standard membership cannot add another Standard plan. Premium members cannot add any membership product.
- When only a membership is present in the cart, the subtotal and total rows show the price "Per Month" and table columns remain aligned.
- When tickets are also in the cart, the total row shows the immediate charge followed by the monthly membership amount (e.g. `$15.00 today, $10 Per Month`).
 - A follow-up note beneath the total explains the upfront charge and when the monthly renewal will occur and now includes guidance for cancellations and refunds (e.g. "You will be billed $10.00 today to begin your membership, and a recurring $10.00 on the 15th of every month. If you wish to cancel your membership, you can do so at any time on your member profile. For questions about refunds, visit our Rules & Policies page.").

### Header
The template outputs a WPBakery hero row above the content using `do_shortcode()`. The banner displays **Become a Member** over a full-width background image.

## Processing
Membership purchases are handled separately from one‑off ticket sales. The JavaScript on the page calls the `tta_add_membership` AJAX action which stores the chosen level in the visitor's session. Checkout will display this membership in the cart summary and use `TTA_AuthorizeNet_API::create_subscription()` to create a recurring subscription with Authorize.Net.
The subscription ID returned by the API is stored on the member record for future cancellation and reporting.
Each subscription uses a consistent name and description depending on the level:
"Trying to Adult Standard Membership" or "Trying to Adult Premium Membership" with matching descriptions.
