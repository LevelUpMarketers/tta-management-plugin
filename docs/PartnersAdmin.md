# TTA Partners Admin

The **TTA Partners** dashboard entry lives at `/wp-admin/admin.php?page=tta-partners`. It introduces two tabs:

- **Add New Partner:** Displays a form with fields matching the `tta_partners` table columns (`company_name`, `contact_first_name`, `contact_last_name`, `contact_phone`, `contact_email`, and `licenses`). Unique identifiers and page IDs are generated elsewhere and are not part of this form yet. The submit button is disabled until save logic is implemented.
- **Manage Partners:** Reserved for upcoming management tools. Currently shows a placeholder message.

Use the page title action or the **Add New Partner** tab to begin entering partner details. Saving and page generation will be wired up in a later iteration.
