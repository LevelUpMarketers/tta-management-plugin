# Admin Bar Visibility

The plugin hides the WordPress admin toolbar for all logged-in members who do not have full administrator capabilities.

- Users lacking the `manage_options` capability never load the toolbar, preventing the extra markup from rendering.
- As a safeguard, a small inline CSS snippet hides `#wpadminbar` and removes the default top margin if the bar manages to load.
- Site administrators continue to see the admin bar normally.
