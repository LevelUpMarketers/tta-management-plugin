# Top Bar Logout Link

The theme's top bar markup may include an empty logout container:

```html
<div class="tta-header-logout-div">
    <a href="">Logout</a><span> - </span>
</div>
```

This plugin automatically handles that element:

- The entire `.tta-header-logout-div` is hidden for visitors who are not logged in.
- When a member is logged in, the plugin sets the anchor's `href` to a secure
  logout URL that redirects back to the site's homepage.

No additional configuration is required—simply keep the markup as shown above
in the theme or Customizer and the plugin will manage visibility and logout
behavior.
