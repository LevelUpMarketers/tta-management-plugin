<?php
/**
 * Template Name: Partner Admin Page
 *
 * Provides the Partner Admin landing page with a login prompt for partner contacts.
 *
 * @package TTA
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$redirect_url = get_permalink();
$login_form   = wp_login_form(
    [
        'echo'     => false,
        'redirect' => esc_url_raw( $redirect_url ),
        'remember' => true,
    ]
);
$lost_pw_url  = wp_lostpassword_url( $redirect_url );
?>
<div class="tta-account-access tta-partner-admin-page">
  <div class="tta-account-access-inner">
    <?php if ( ! is_user_logged_in() ) : ?>
      <section class="tta-login-column">
        <h1 class="tta-section-title"><?php esc_html_e( 'Already Have an Account? Log In Below!', 'tta' ); ?></h1>
        <p class="tta-section-intro">
          <?php
          echo wp_kses(
              sprintf(
                  /* translators: %s: contact page URL */
                  __( 'Log in to access your partner admin page. Need help? <a class="tta-join-link" href="%s"><strong>Contact us.</strong></a>', 'tta' ),
                  esc_url( home_url( '/contact/' ) )
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
    <?php
    else :
        global $post, $wpdb;

        $page_id = isset( $post->ID ) ? intval( $post->ID ) : 0;
        $partner = TTA_Cache::remember(
            'partner_admin_page_' . $page_id,
            static function () use ( $wpdb, $page_id ) {
                if ( ! $page_id ) {
                    return null;
                }

                $table = $wpdb->prefix . 'tta_partners';

                return $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT id, wpuserid FROM {$table} WHERE adminpageid = %d LIMIT 1",
                        $page_id
                    ),
                    ARRAY_A
                );
            },
            MINUTE_IN_SECONDS * 5
        );

        $current_user_id = get_current_user_id();
        $is_admin        = current_user_can( 'manage_options' );
        $is_partner      = $partner && intval( $partner['wpuserid'] ) === $current_user_id;

        if ( ! $partner || ( ! $is_admin && ! $is_partner ) ) :
            ?>
          <section class="tta-partner-admin-placeholder">
            <h1 class="tta-section-title"><?php esc_html_e( 'Access Restricted', 'tta' ); ?></h1>
            <p class="tta-section-intro"><?php esc_html_e( 'You do not have access to this partner admin page.', 'tta' ); ?></p>
          </section>
        <?php else : ?>
          <section class="tta-partner-admin-placeholder">
            <h1 class="tta-section-title"><?php esc_html_e( 'Partner Admin', 'tta' ); ?></h1>
            <p class="tta-section-intro"><?php esc_html_e( 'You are logged in. Partner admin tools will appear here soon.', 'tta' ); ?></p>
          </section>
        <?php
        endif;
    endif;
    ?>
  </div>
</div>
<?php
get_footer();
