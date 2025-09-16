<?php
/**
 * Template Name: Login or Create Account Page
 *
 * Dedicated page template that presents login and registration forms
 * side-by-side for easy account access.
 *
 * @package TTA
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$header_shortcode = '[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">LOG IN</p>[/vc_column_text][/vc_column][/vc_row]';
echo do_shortcode( $header_shortcode );

$redirect_url = home_url( '/events' );
$login_form   = wp_login_form(
    [
        'echo'     => false,
        'redirect' => esc_url_raw( $redirect_url ),
        'remember' => true,
    ]
);
$lost_pw_url = wp_lostpassword_url( $redirect_url );
?>
<div class="tta-account-access">
  <div class="tta-account-access-inner">
    <div class="tta-login-register-grid">
      <section class="tta-login-column">
        <h1 class="tta-section-title"><?php esc_html_e( 'Already Have an Account? Log In Below!', 'tta' ); ?></h1>
        <p class="tta-section-intro"><?php esc_html_e( 'Log in to unlock member-only pricing, manage your upcoming events, and access your dashboard.', 'tta' ); ?></p>
        <div class="tta-login-form">
          <?php echo $login_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <p class="tta-login-help"><a href="<?php echo esc_url( $lost_pw_url ); ?>"><?php esc_html_e( 'Forgot your password?', 'tta' ); ?></a></p>
        </div>
      </section>
      <section class="tta-register-column">
        <h1 class="tta-section-title"><?php esc_html_e( 'Don’t Have an Account? Create One Below!', 'tta' ); ?></h1>
        <p class="tta-section-intro"><?php esc_html_e( 'Create an account to join events faster, save your preferences, and see pricing tailored to your membership level.', 'tta' ); ?></p>
        <form id="tta-register-form" class="tta-register-form">
          <p>
            <label><?php esc_html_e( 'First Name', 'tta' ); ?><br />
              <input type="text" name="first_name" autocomplete="given-name" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Last Name', 'tta' ); ?><br />
              <input type="text" name="last_name" autocomplete="family-name" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Email', 'tta' ); ?><br />
              <input type="email" name="email" autocomplete="email" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Verify Email', 'tta' ); ?><br />
              <input type="email" name="email_verify" autocomplete="email" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Password', 'tta' ); ?><br />
              <input type="password" name="password" autocomplete="new-password" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Verify Password', 'tta' ); ?><br />
              <input type="password" name="password_verify" autocomplete="new-password" required />
            </label>
          </p>
          <p class="tta-register-actions">
            <button type="submit" class="tta-button tta-button-primary"><?php esc_html_e( 'Create Account', 'tta' ); ?></button>
            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
          </p>
          <span id="tta-register-response" class="tta-admin-progress-response-p" role="status" aria-live="polite"></span>
        </form>
      </section>
    </div>
  </div>
</div>
<?php
get_footer();
