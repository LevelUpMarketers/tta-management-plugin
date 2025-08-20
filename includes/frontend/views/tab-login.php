<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$tab_slug = isset( $tab_slug ) ? $tab_slug : 'profile';
$form_id  = 'loginform-' . $tab_slug;
$form_html = wp_login_form(
    [
        'echo'     => false,
        'redirect' => home_url( '/member-dashboard/?tab=' . $tab_slug ),
        'form_id'  => $form_id,
    ]
);
$lost_pw_url = wp_lostpassword_url( home_url( '/member-dashboard/?tab=' . $tab_slug ) );
?>
<div id="tab-<?php echo esc_attr( $tab_slug ); ?>" class="tta-dashboard-section">
  <section class="tta-message-center tta-login-accordion tta-login-message">
    <h2><?php esc_html_e( 'Log in or Register Here', 'tta' ); ?></h2>
    <div class="tta-accordion">
        <p>
          <?php
          printf(
              /* translators: 1: action buttons */
              esc_html__( 'Join today to create your profile, view your upcoming & past events, and more! Create a free account below or become a Member today!%1$s', 'tta' ),
              '<div><a href="#" class="tta-button tta-button-primary tta-show-register">' . esc_html__( 'Create Account', 'tta' ) . '</a><a href="/become-a-member" class="tta-button tta-button-primary">' . esc_html__( 'Become a Member', 'tta' ) . '</a></div>'
          );
          ?>
        </p>
      <div class="tta-accordion-content expanded">
        <div class="tta-login-wrap">
          <?php echo $form_html; ?>
          <p class="login-lost-password"><a href="<?php echo esc_url( $lost_pw_url ); ?>"><?php esc_html_e( 'Forgot your password?', 'tta' ); ?></a></p>
        </div>
        <form class="tta-register-form" style="display:none;">
          <p>
            <label><?php esc_html_e( 'First Name', 'tta' ); ?><br />
              <input type="text" name="first_name" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Last Name', 'tta' ); ?><br />
              <input type="text" name="last_name" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Email', 'tta' ); ?><br />
              <input type="email" name="email" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Verify Email', 'tta' ); ?><br />
              <input type="email" name="email_verify" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Password', 'tta' ); ?><br />
              <input type="password" name="password" required />
            </label>
          </p>
          <p>
            <label><?php esc_html_e( 'Verify Password', 'tta' ); ?><br />
              <input type="password" name="password_verify" required />
            </label>
          </p>
          <p>
            <button type="submit" class="tta-button tta-button-primary"><?php esc_html_e( 'Create Account', 'tta' ); ?></button>
            <a href="#" class="tta-button-link tta-cancel-register"><?php esc_html_e( 'Cancel Account Creation', 'tta' ); ?></a>
            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
          </p>
          <span class="tta-register-response tta-admin-progress-response-p"></span>
        </form>
      </div>
    </div>
  </section>
</div>
