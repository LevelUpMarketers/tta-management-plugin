# Become a Member Page

The **Become a Member** template introduces a front‑end landing page for membership information. Assign the template to a WordPress page whose URL ends in `/become-a-member/`.

## Overview
- Located at `includes/frontend/templates/become-member-page-template.php`.
- Registered by `TTA_Become_Member_Page`.
- Describes Basic vs. Premium benefits in a simple table.
- Includes buttons that link to WooCommerce products to add the membership to the cart.

## Sign Up Links
The sign up buttons direct visitors to the following URLs which automatically add the product to the WooCommerce cart:

- Basic Membership: `/product/membership/?add-to-cart=7184`
- Premium Membership: `/product/premium-membership/?add-to-cart=378`

Adjust the product IDs if they differ on your installation.
