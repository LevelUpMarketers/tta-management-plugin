# Top Bar Logout Link

The theme's top bar markup may include an empty logout container:

```html
<div class="tta-header-logout-div">
    <a href="">Hi [USER FIRST NAME] <span> - </span> Logout</a><span> - </span>
</div>
```

This plugin automatically handles that element:

- The entire `.tta-header-logout-div` is hidden for visitors who are not logged in.
- When a member is logged in, the plugin sets the anchor's `href` to a secure
  logout URL that redirects back to the site's homepage.
- If the link contains the placeholder `[USER FIRST NAME]`, it will be replaced
  with the logged-in member's first name.

No additional configuration is required—simply keep the markup as shown above
in the theme or Customizer and the plugin will manage visibility and logout
behavior.
