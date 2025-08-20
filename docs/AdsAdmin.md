# TTA Ads Admin Page

The **TTA Ads** screen is split into two tabs:

1. **Create Ad** – Upload an image (a thumbnail preview appears immediately), enter the target URL (schemes like `http://` are optional so long as a valid domain/TLD is present) and optional business details. The telephone field auto‑formats as you type. Existing ads can be edited from the **Manage Ads** tab. Images are stored as WordPress attachments; only the attachment ID and details are saved.
2. **Manage Ads** – Lists all saved ads with their image preview, business name and URL. Each ad expands into an inline edit form where you can update details or delete the ad without leaving the page.

All form labels include tooltip icons that display contextual help on hover.

On each page load, the Events List Page picks one ad at random to display in the right‑hand column. Any changes automatically clear the plugin cache so the rotation reflects updates right away.
