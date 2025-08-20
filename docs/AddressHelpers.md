# Address Helper Functions

The plugin stores both event and member addresses as a single string using dashes as delimiters:

```
Street – Address 2 – City – State – ZIP
```

Earlier sample data used a normal hyphen (`-`) while newer forms output an en dash (`–`). Each delimiter is wrapped in spaces (for example `Street - City`). The helper `tta_parse_address()` splits on these spaced dashes so older rows continue to load and hyphenated street names remain intact. It returns an associative array with keys `street`, `address2`, `city`, `state` and `zip`.

Use this helper whenever an address needs to be broken into separate fields. `tta_format_address()` remains available for displaying a one line version on the front‑end.
`tta_format_address()` now outputs addresses separated with spaces rather than an en dash so emails and front‑end pages read more naturally.

`tta_get_google_maps_url()` takes the raw address string and returns a Google Maps
URL so templates can easily link to directions.
