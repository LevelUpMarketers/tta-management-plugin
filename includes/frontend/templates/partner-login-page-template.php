<?php
/**
 * Template Name: Partner Login Page
 *
 * Presents a registration form for partner invitees to create accounts.
 *
 * @package TTA
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post, $wpdb;

get_header();

$page_id = isset( $post->ID ) ? intval( $post->ID ) : 0;
$partner = TTA_Cache::remember(
    'partner_login_page_' . $page_id,
    static function () use ( $wpdb, $page_id ) {
        if ( ! $page_id ) {
            return null;
        }

        $table = $wpdb->prefix . 'tta_partners';

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT company_name FROM {$table} WHERE signuppageid = %d LIMIT 1",
                $page_id
            ),
            ARRAY_A
        );
    },
    MINUTE_IN_SECONDS * 5
);

$partner_name = $partner['company_name'] ?? __( 'our partner', 'tta' );
$redirect_url = home_url( '/events' );
?>
<div class="tta-account-access tta-partner-login-page">
  <div class="tta-account-access-inner">
    <section class="tta-register-column">
      <h1 class="tta-section-title"><?php esc_html_e( 'Don’t Have an Account yet? Create One Below!', 'tta' ); ?></h1>
      <p class="tta-section-intro">
        <?php
        printf(
            esc_html__( "As part of our partnership with %s, you're eligible for a FREE account! This grants you exclusive discount on events and additional membership perks. Create your account below!", 'tta' ),
            esc_html( $partner_name )
        );
        ?>
      </p>
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
<?php
get_footer();
