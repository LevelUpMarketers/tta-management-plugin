# TTA Discount Codes Page

The **TTA Discount Codes** screen lets administrators create global discount codes that apply to any cart. These one-off codes are independent of individual events and stack with event-specific discounts.

The menu entry now uses the WordPress `dashicons-tag` icon to match other dashboard items.

Each code has five fields:

- **Discount Code** – the text users enter at checkout.
- **Discount Type** – choose a percentage off or a flat dollar amount.
- **Discount Amount** – numeric value for the discount.
- **One-Time Use?** – select **Yes** to mark the code as one-time use, otherwise **No**.
- **Date of Use** – read-only timestamp for when a one-time code was redeemed (defaults to `N/A`).

Use **Add Discount Code** to insert additional rows. Existing rows can be removed with the × button. Click **Save Discount Codes** when finished. Codes are stored in a dedicated table and cached for quick lookups.

The discount codes table also tracks one-time usage metadata:

- **onetime** – flag that marks a code as one-time use (`0`/`1`).
- **used** – timestamp populated when a one-time code is redeemed successfully.
