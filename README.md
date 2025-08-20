# TTA Actual

This repository contains the full WordPress site for **Trying To Adult RVA**.

## Trying To Adult Management Plugin

Most development work happens inside the plugin located at:

`app/public/wp-content/plugins/tta-management-plugin`

- Unless a task explicitly states otherwise, assume questions and changes refer to this plugin.
- The plugin must remain self-contained so it can be copied to another WordPress installation and function on its own.

## Development Notes

Run Composer and PHPUnit from the plugin directory:

```bash
cd app/public/wp-content/plugins/tta-management-plugin
composer install
vendor/bin/phpunit
```

