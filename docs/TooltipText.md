# Tooltip Text Management

All tooltip strings are centralized in the `TTA_Tooltips` class. Each tooltip in the markup simply declares a `data-ttakey` attribute. A small JavaScript helper assigns the actual text at runtime based on the mapping passed via `wp_localize_script`.

To add or update tooltip copy, edit `includes/classes/class-tta-tooltips.php` and modify the array returned by `TTA_Tooltips::get_texts()`. No changes to templates are required as long as the same key is reused.
