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
        <p class="tta-section-intro">
          <?php
          echo wp_kses(
              sprintf(
                  /* translators: %s: Become a Member page URL */
                  __( 'Log in to see your Standard or Premium Membership pricing on events. Don\'t have a Membership? <a class="tta-join-link" href="%s"><strong>Join Here!</strong></a>', 'tta' ),
                  esc_url( home_url( '/become-a-member/' ) )
              ),
              [
                  'a'      => [
                      'href'  => [],
                      'class' => [],
                  ],
                  'strong' => [],
              ]
          );
          ?>
        </p>
        <div class="tta-login-form">
          <?php echo $login_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <p class="tta-login-help"><a href="<?php echo esc_url( $lost_pw_url ); ?>"><?php esc_html_e( 'Forgot your password?', 'tta' ); ?></a></p>
        </div>
      </section>
      <section class="tta-register-column">
        <h1 class="tta-section-title"><?php esc_html_e( 'Don’t Have an Account? Create One Below!', 'tta' ); ?></h1>
        <p class="tta-section-intro"><?php esc_html_e( 'Create an account to join select events, update your profile info, and sign up for a membership.', 'tta' ); ?></p>
        <form id="tta-register-form" class="tta-register-form">
          <p>
            <label for="tta-register-first-name"><?php esc_html_e( 'First Name', 'tta' ); ?></label>
            <input id="tta-register-first-name" type="text" name="first_name" autocomplete="given-name" required />
          </p>
          <p>
            <label for="tta-register-last-name"><?php esc_html_e( 'Last Name', 'tta' ); ?></label>
            <input id="tta-register-last-name" type="text" name="last_name" autocomplete="family-name" required />
          </p>
          <p>
            <label for="tta-register-email"><?php esc_html_e( 'Email', 'tta' ); ?></label>
            <input id="tta-register-email" type="email" name="email" autocomplete="email" required />
          </p>
          <p>
            <label for="tta-register-email-verify"><?php esc_html_e( 'Verify Email', 'tta' ); ?></label>
            <input id="tta-register-email-verify" type="email" name="email_verify" autocomplete="email" required />
          </p>
          <p class="tta-password-field">
            <label for="tta-register-password"><?php esc_html_e( 'Password', 'tta' ); ?></label>
            <span class="tta-password-input">
              <input id="tta-register-password" type="password" name="password" autocomplete="new-password" required />
              <button type="button" class="tta-password-toggle" data-target="tta-register-password" aria-pressed="false">
                <span class="tta-visually-hidden"><?php esc_html_e( 'Show password', 'tta' ); ?></span>
                <svg class="tta-password-toggle-icon tta-password-toggle-icon--show" aria-hidden="true" viewBox="0 0 24 24" focusable="false" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2 12c2.2-4.5 6.4-7.5 10-7.5s7.8 3 10 7.5c-2.2 4.5-6.4 7.5-10 7.5S4.2 16.5 2 12Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" />
                  <circle cx="12" cy="12" r="1.5" fill="currentColor" />
                </svg>
                <svg class="tta-password-toggle-icon tta-password-toggle-icon--hide" aria-hidden="true" viewBox="0 0 24 24" focusable="false" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2 12c2.2-4.5 6.4-7.5 10-7.5s7.8 3 10 7.5c-2.2 4.5-6.4 7.5-10 7.5S4.2 16.5 2 12Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" />
                  <path d="M4 4l16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </span>
          </p>
          <p class="tta-password-field">
            <label for="tta-register-password-verify"><?php esc_html_e( 'Verify Password', 'tta' ); ?></label>
            <span class="tta-password-input">
              <input id="tta-register-password-verify" type="password" name="password_verify" autocomplete="new-password" required />
              <button type="button" class="tta-password-toggle" data-target="tta-register-password-verify" aria-pressed="false">
                <span class="tta-visually-hidden"><?php esc_html_e( 'Show password', 'tta' ); ?></span>
                <svg class="tta-password-toggle-icon tta-password-toggle-icon--show" aria-hidden="true" viewBox="0 0 24 24" focusable="false" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2 12c2.2-4.5 6.4-7.5 10-7.5s7.8 3 10 7.5c-2.2 4.5-6.4 7.5-10 7.5S4.2 16.5 2 12Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" />
                  <circle cx="12" cy="12" r="1.5" fill="currentColor" />
                </svg>
                <svg class="tta-password-toggle-icon tta-password-toggle-icon--hide" aria-hidden="true" viewBox="0 0 24 24" focusable="false" xmlns="http://www.w3.org/2000/svg">
                  <path d="M2 12c2.2-4.5 6.4-7.5 10-7.5s7.8 3 10 7.5c-2.2 4.5-6.4 7.5-10 7.5S4.2 16.5 2 12Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" />
                  <path d="M4 4l16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </span>
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
