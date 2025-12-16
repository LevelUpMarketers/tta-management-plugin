# TTA Partners Admin

The **TTA Partners** dashboard entry lives at `/wp-admin/admin.php?page=tta-partners`. It introduces two tabs:

- **Add New Partner:** Displays a form with fields matching the `tta_partners` table columns (`company_name`, `contact_first_name`, `contact_last_name`, `contact_phone`, `contact_email`, and `licenses`). On save, the request follows the same spinner/response pattern as other admin forms, creates a partner row (with a unique identifier), provisions a Subscriber WordPress user for the contact email using the password `d3v50$VdMICfo^s4AWIbJhG5`, and generates two pages titled `"{company} (admin)"` and `"{company} Login"`. The new page IDs are saved back to the partner row as `adminpageid` and `signuppageid`. The success notice echoes the password for the admin to share with the partner contact.
- **Manage Partners:** Reserved for upcoming management tools. Currently shows a placeholder message.

Use the page title action or the **Add New Partner** tab to begin entering partner details.
