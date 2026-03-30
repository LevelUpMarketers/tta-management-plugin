# TTA Actual

This repository contains the full WordPress site for **Trying To Adult RVA**.

## Trying To Adult Management Plugin

Most development work happens inside the plugin located at:

`app/public/wp-content/plugins/tta-management-plugin`

Current plugin version: **1.0.10**

- Unless a task explicitly states otherwise, assume questions and changes refer to this plugin.
- The plugin must remain self-contained so it can be copied to another WordPress installation and function on its own.

## Development Notes

Run Composer and PHPUnit from the plugin directory:

```bash
cd app/public/wp-content/plugins/tta-management-plugin
composer install
vendor/bin/phpunit
```


## Handover Dev Note (March 30, 2026)

- This v1.0.10 release is the final update completed by **Jake Evans (Level Up Digital Marketing)** for Trying To Adult.
- As of this handoff, automatic Bcc injection via the plugin email customizer has been intentionally disabled so prior vendor inboxes are not copied on outgoing email.
- Website and plugin/repository handoff contacts:
  - **Juliette Oliva** — juliette@pixelstrikecreative.com
  - **Louise Strickland** — louise@pixelstrikecreative.com
  - **Pixelstrike Creative** — https://pixelstrikecreative.com/

