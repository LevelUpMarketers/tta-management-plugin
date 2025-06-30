# Address Helper Functions

The plugin stores both event and member addresses as a single string using dashes as delimiters:

```
Street – Address 2 – City – State – ZIP
```

Earlier sample data used a normal hyphen (`-`) while newer forms output an en dash (`–`). To ensure older rows continue to load, the new helper `tta_parse_address()` splits on either character and trims whitespace. It returns an associative array with keys `street`, `address2`, `city`, `state` and `zip`.

Use this helper whenever an address needs to be broken into separate fields. `tta_format_address()` remains available for displaying a one line version on the front‑end.
`tta_format_address()` now outputs addresses separated with spaces rather than an en dash so emails and front‑end pages read more naturally.
