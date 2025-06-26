# Tooltip Text Management

All tooltip strings are centralized in the `TTA_Tooltips` class. Each tooltip in
the markup references a constant from this class and passes it to
`TTA_Tooltips::get()` for the actual text. Example:

```php
<span class="tta-tooltip-icon" data-tooltip="<?php echo esc_attr( TTA_Tooltips::get( TTA_Tooltips::EVENT_NAME ) ); ?>">
```

To add or update tooltip copy, edit `includes/classes/class-tta-tooltips.php` and
modify the array returned by `TTA_Tooltips::get_texts()`. Because templates rely
on constants, the markup does not need changes when text updates.
