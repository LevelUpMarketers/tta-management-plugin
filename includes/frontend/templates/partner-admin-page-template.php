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
                        "SELECT id, company_name, contact_first_name, contact_last_name, contact_phone, contact_email, licenses, wpuserid FROM {$table} WHERE adminpageid = %d LIMIT 1",
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
            <div class="tta-member-dashboard-wrap notranslate" data-nosnippet>
              <h2><?php echo esc_html( $partner['company_name'] ); ?></h2>
              <p><?php echo esc_html( sprintf( /* translators: %s: partner contact first name */ __( 'Welcome, %s!', 'tta' ), $partner['contact_first_name'] ) ); ?></p>
              <?php
              $license_config = [
                  'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
                  'nonce'       => wp_create_nonce( 'tta_partner_upload_action' ),
                  'pageId'      => $page_id,
                  'noFile'      => __( 'Please select a CSV file to upload.', 'tta' ),
                  'emptyFile'   => __( 'The selected file appears to be empty.', 'tta' ),
                  'badHeaders'  => __( 'Missing required headers: First Name, Last Name, Email.', 'tta' ),
                  'success'     => __( 'Upload complete.', 'tta' ),
                  'error'       => __( 'Upload failed.', 'tta' ),
              ];
              ?>
              <script>
                window.TTA_Partner_Licenses = <?php echo wp_json_encode( $license_config ); ?>;
              </script>

              <div class="tta-member-dashboard notranslate" data-nosnippet>
                <div class="tta-dashboard-sidebar">
                  <ul class="tta-dashboard-tabs">
                    <li data-tab="profile" class="active"><?php esc_html_e( 'Profile Info', 'tta' ); ?></li>
                    <li data-tab="licenses"><?php esc_html_e( 'Your Licenses', 'tta' ); ?></li>
                  </ul>
                </div>

                <div class="tta-dashboard-content">
                  <div id="tab-profile" class="tta-dashboard-section notranslate" data-nosnippet style="display:block;">
                    <table class="form-table tta-partner-profile-table">
                      <tbody>
                        <tr>
                          <th><?php esc_html_e( 'Company Name', 'tta' ); ?></th>
                          <td><?php echo esc_html( $partner['company_name'] ); ?></td>
                        </tr>
                        <tr>
                          <th><?php esc_html_e( 'Contact First Name', 'tta' ); ?></th>
                          <td><?php echo esc_html( $partner['contact_first_name'] ); ?></td>
                        </tr>
                        <tr>
                          <th><?php esc_html_e( 'Contact Last Name', 'tta' ); ?></th>
                          <td><?php echo esc_html( $partner['contact_last_name'] ); ?></td>
                        </tr>
                        <tr>
                          <th><?php esc_html_e( 'Contact Phone', 'tta' ); ?></th>
                          <td><?php echo esc_html( $partner['contact_phone'] ); ?></td>
                        </tr>
                        <tr>
                          <th><?php esc_html_e( 'Contact Email', 'tta' ); ?></th>
                          <td><?php echo esc_html( $partner['contact_email'] ); ?></td>
                        </tr>
                        <tr>
                          <th><?php esc_html_e( 'Licenses', 'tta' ); ?></th>
                          <td><?php echo esc_html( number_format_i18n( intval( $partner['licenses'] ) ) ); ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <div id="tab-licenses" class="tta-dashboard-section notranslate" data-nosnippet style="display:none;">
                    <p class="tta-section-intro"><?php esc_html_e( 'Upload a CSV or Excel file (xlsx) with First Name, Last Name, and Email to add partner licenses.', 'tta' ); ?></p>
                    <div class="tta-license-upload">
                      <input type="file" id="tta-license-file" accept=".csv,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.xls,.xlsx" />
                      <button type="button" class="tta-button tta-button-primary" id="tta-license-upload-btn"><?php esc_html_e( 'Upload Licenses', 'tta' ); ?></button>
                      <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                      <p id="tta-license-upload-response" class="tta-admin-progress-response-p" role="status" aria-live="polite"></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        <?php
        endif;
    endif;
    ?>
  </div>
</div>
<script>
(function($){
  $(function(){
    $('.tta-dashboard-tabs li').on('click', function(){
      var tab = $(this).data('tab');
      $('.tta-dashboard-tabs li').removeClass('active');
      $(this).addClass('active');
      $('.tta-dashboard-section').hide();
      $('#tab-' + tab).show();
    });

    var uploadCfg = window.TTA_Partner_Licenses || {};
    var $file = $('#tta-license-file');
    var $btn = $('#tta-license-upload-btn');
    var $resp = $('#tta-license-upload-response');
    var $spinner = $('.tta-license-upload .tta-admin-progress-spinner-svg');

    $spinner.hide();

    function resetState() {
      $resp.removeClass('error updated').text('');
    }

    function showError(msg) {
      $resp.removeClass('updated').addClass('error').text(msg);
    }

    function showSuccess(msg) {
      $resp.removeClass('error').addClass('updated').text(msg);
    }

    $btn.on('click', function(){
      resetState();
      var file = $file[0].files[0];
      if (!file) {
        showError(uploadCfg.noFile || 'Please select a file to upload.');
        return;
      }

      var formData = new FormData();
      formData.append('action', 'tta_upload_partner_licenses');
      formData.append('nonce', uploadCfg.nonce);
      formData.append('page_id', uploadCfg.pageId);
      formData.append('license_file', file);

      $btn.prop('disabled', true);
      $spinner.show();

      $.ajax({
        url: uploadCfg.ajaxUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
      }).done(function(res){
        $btn.prop('disabled', false);
        $spinner.hide();
        if (res && res.success) {
          showSuccess(res.data && res.data.message ? res.data.message : (uploadCfg.success || 'Upload complete.'));
        } else {
          var msg = res && res.data && res.data.message ? res.data.message : (uploadCfg.error || 'Upload failed.');
          showError(msg);
        }
      }).fail(function(){
        $btn.prop('disabled', false);
        $spinner.hide();
        showError(uploadCfg.error || 'Upload failed.');
      });
    });
  });
})(jQuery);
</script>
<?php
get_footer();
