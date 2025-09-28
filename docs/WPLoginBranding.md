# WordPress Login Branding

The plugin customizes the core WordPress login, password reset, and error screens so the experience matches the Trying To Adult RVA brand.

## What is customized?

- **Logo:** Replaces the default WordPress mark with the Trying To Adult RVA logo located at `/wp-content/uploads/2022/12/TTA2_Full-1.png`.
- **Alert colors:** Aligns error, message, and success notices with the primary brand color `#3a7096` while leaving password strength indicators unchanged.
- **Password reset email:** Sends a friendlier message with a subject of `Reset Your {Site Name} Password!` and clear instructions containing the reset link.

All styling overrides are isolated in `assets/css/frontend/wp-login.css` and loaded through `TTA_Login_Branding` (see `includes/login/class-tta-login-branding.php`). Email copy changes use standard WordPress filters so the functionality remains portable across environments.
