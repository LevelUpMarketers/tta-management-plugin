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

$header_shortcode = '[vc_row full_width="stretch_row_content_no_spaces" css=".vc_custom_1670382516702{background-image: url(https://trying-to-adult-rva-2025.local/wp-content/uploads/2022/12/IMG-4418.png?id=70) !important;background-position: center !important;background-repeat: no-repeat !important;background-size: cover !important;}"][vc_column][vc_empty_space height="300px" el_id="jre-header-title-empty"][vc_column_text css_animation="slideInLeft" el_id="jre-homepage-id-1" css=".vc_custom_1671885403487{margin-left: 50px !important;padding-left: 50px !important;}"]<p id="jre-homepage-id-3">PARTNER ADMIN</p>[/vc_column_text][/vc_column][/vc_row]';
echo do_shortcode( $header_shortcode );
?>
<style>
  .page-template-partner-admin-page-template .vc_custom_1670382516702 {
    background-color: #000;
  }

  .page-template-partner-admin-page-template #jre-homepage-id-1 {
    margin-left: 50px !important;
    padding-left: 50px !important;
  }

  .page-template-partner-admin-page-template #single-blocks > div > div > div.vc_row.wpb_row.vc_row-fluid.vc_custom_1670382516702.wpex-vc-full-width-row.wpex-vc-full-width-row--no-padding.wpex-relative.wpex-vc_row-has-fill.wpex-vc-reset-negative-margin > div {
    background-color: #000;
  }

  .page-template-partner-admin-page-template #jre-homepage-id-3 {
    bottom: 65px;
  }

  @media (max-width: 960px) {
    .page-template-partner-admin-page-template #jre-homepage-id-1 {
      margin-left: 0 !important;
      padding-left: 0 !important;
    }

    .page-template-partner-admin-page-template #jre-homepage-id-3 {
      bottom: 0 !important;
    }

    .page-template-partner-admin-page-template #single-blocks > div > div > div.vc_row.wpb_row.vc_row-fluid.vc_custom_1670382516702.wpex-vc-full-width-row.wpex-vc-full-width-row--no-padding.wpex-relative.wpex-vc_row-has-fill.wpex-vc-reset-negative-margin > div {
      height: 245px;
    }
  }

  @media (max-width: 530px) {
    .page-template-partner-admin-page-template #jre-homepage-id-1 #jre-homepage-id-3 {
      font-size: 50px;
      padding: 0 15px;
    }

    .page-template-partner-admin-page-template #jre-homepage-id-3 {
      bottom: 20px !important;
    }
  }
</style>
<?php

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
                        "SELECT id, company_name, contact_first_name, contact_last_name, contact_phone, contact_email, licenses, wpuserid, uniquecompanyidentifier FROM {$table} WHERE adminpageid = %d LIMIT 1",
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
              $members_table = $wpdb->prefix . 'tta_members';
              $identifier    = $partner['uniquecompanyidentifier'] ?? '';
              $counts        = TTA_Cache::remember(
                  'partner_license_counts_' . $partner['id'],
                  static function () use ( $wpdb, $members_table, $identifier ) {
                      if ( empty( $identifier ) ) {
                          return [
                              'total'    => 0,
                              'active'   => 0,
                              'inactive' => 0,
                          ];
                      }
                      $total      = (int) $wpdb->get_var(
                          $wpdb->prepare(
                              "SELECT COUNT(*) FROM {$members_table} WHERE partner = %s",
                              $identifier
                          )
                      );
                      $active     = (int) $wpdb->get_var(
                          $wpdb->prepare(
                              "SELECT COUNT(*) FROM {$members_table} WHERE partner = %s AND wpuserid != 0",
                              $identifier
                          )
                      );
                      $inactive = max( 0, $total - $active );
                      return [
                          'total'    => $total,
                          'active'   => $active,
                          'inactive' => $inactive,
                      ];
                  },
                  MINUTE_IN_SECONDS * 5
              );

              $license_limit = intval( $partner['licenses'] );
              $remaining     = ( $license_limit > 0 && $identifier ) ? max( 0, $license_limit - $counts['total'] ) : ( $license_limit > 0 ? $license_limit : null );
              ?>
              <?php
              $license_config = [
                  'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
                  'nonce'       => wp_create_nonce( 'tta_partner_upload_action' ),
                  'fetchNonce'  => wp_create_nonce( 'tta_partner_fetch_action' ),
                  'updateNonce' => wp_create_nonce( 'tta_partner_member_action' ),
                  'pageId'      => $page_id,
                  'noFile'      => __( 'Please select a CSV file to upload.', 'tta' ),
                  'emptyFile'   => __( 'The selected file appears to be empty.', 'tta' ),
                  'badHeaders'  => __( 'Missing required headers: First Name, Last Name, Email.', 'tta' ),
                  'success'     => __( 'Upload complete.', 'tta' ),
                  'error'       => __( 'Upload failed.', 'tta' ),
                  'noResults'   => __( 'No partner members found.', 'tta' ),
                  'paginationLabel' => __( 'Page %1$d of %2$d', 'tta' ),
                  'employmentSuccess' => __( 'Member marked as no longer employed.', 'tta' ),
                  'employmentError'   => __( 'Unable to update the member. Please try again.', 'tta' ),
                  'employmentConfirm' => __( "Are you sure you want to remove this person? They'll lose their Membership and will need to sign up for their own paid Membership if they want to keep Membership benefits.", 'tta' ),
                  'singleMissing' => __( "Whoops - looks like some info is missing! Please make sure you've provided a first name, last name, and email address.", 'tta' ),
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
                    <div class="tta-license-section tta-license-summary">
                      <h3><?php esc_html_e( 'Member License Info', 'tta' ); ?></h3>
                      <p class="tta-section-intro">
                        <?php esc_html_e( 'Below is the info about your Licenses. One License is assigned to each Member. Active Licenses represent the amount of Members that have utilized their Membership benefits and created an account.', 'tta' ); ?>
                      </p>
                      <ul>
                        <li><strong><?php esc_html_e( 'License Limit:', 'tta' ); ?></strong> <?php echo $license_limit > 0 ? esc_html( number_format_i18n( $license_limit ) ) : esc_html__( 'Unlimited', 'tta' ); ?></li>
                        <li><strong><?php esc_html_e( 'Used Licenses:', 'tta' ); ?></strong> <?php echo esc_html( number_format_i18n( $counts['total'] ) ); ?></li>
                        <li><strong><?php esc_html_e( 'Active Licenses:', 'tta' ); ?></strong> <?php echo esc_html( number_format_i18n( $counts['active'] ) ); ?></li>
                        <li><strong><?php esc_html_e( 'Inactive Licenses:', 'tta' ); ?></strong> <?php echo esc_html( number_format_i18n( $counts['inactive'] ) ); ?></li>
                        <li><strong><?php esc_html_e( 'Remaining Licenses:', 'tta' ); ?></strong> <?php echo ( $license_limit > 0 ) ? esc_html( number_format_i18n( $remaining ) ) : esc_html__( 'Unlimited', 'tta' ); ?></li>
                      </ul>
                    </div>

                    <div class="tta-bulk-upload-license-section">
                      <h3><?php esc_html_e( 'Bulk Member License Upload', 'tta' ); ?></h3>
                      <p class="tta-section-intro">
                        <?php
                        printf(
                            wp_kses(
                                /* translators: %s: sample CSV URL */
                                __( 'Upload a CSV file with First Name, Last Name, and Email to add individuals. <a href="%s" download>Click here for a sample CSV file.</a>', 'tta' ),
                                [
                                    'a' => [
                                        'href'    => [],
                                        'download'=> [],
                                    ],
                                ]
                            ),
                            esc_url( TTA_PLUGIN_URL . 'assets/samples/partner-licenses-sample.csv' )
                        );
                        ?>
                      </p>
                      <div class="tta-license-upload">
                        <input type="file" id="tta-license-file" accept=".csv,text/csv,text/plain" />
                        <button type="button" class="tta-button tta-button-primary" id="tta-license-upload-btn"><?php esc_html_e( 'Upload Licenses', 'tta' ); ?></button>
                        <div class="tta-license-upload-progress-holder">
                          <div class="tta-license-upload-spinner-div">
                            <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                          </div>
                          <div class="tta-admin-progress-response-p-message-holder">
                            <p id="tta-license-upload-response-message" class="tta-admin-progress-response-p-message" role="status" aria-live="polite"></p>
                          </div>
                        </div>
                        <div class="tta-progress-wrap" aria-live="polite">
                          <div class="tta-progress-bar" id="tta-upload-progress" style="width:0%" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
                          <img class="tta-admin-progress-spinner-svg tta-inline-spinner" id="tta-upload-progress-spinner" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                        </div>
                      </div>
                    </div>

                    <div class="tta-license-single-add">
                      <h3><?php esc_html_e( 'Add an Individual Member', 'tta' ); ?></h3>
                      <p class="tta-section-intro">
                        <?php esc_html_e( 'Provide a First Name, Last Name, and Email address below to add members individually.', 'tta' ); ?>
                      </p>
                      <div class="tta-license-single-fields">
                        <input type="text" id="tta-single-first" placeholder="<?php esc_attr_e( 'First Name', 'tta' ); ?>" />
                        <input type="text" id="tta-single-last" placeholder="<?php esc_attr_e( 'Last Name', 'tta' ); ?>" />
                        <input type="text" id="tta-single-email" placeholder="<?php esc_attr_e( 'Email', 'tta' ); ?>" />
                        <button type="button" class="tta-button" id="tta-single-add-btn"><?php esc_html_e( 'Add Member', 'tta' ); ?></button>
                        <img class="tta-admin-progress-spinner-svg" id="tta-single-spinner" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                      </div>
                      <p id="tta-single-response" class="tta-admin-progress-response-p" role="status" aria-live="polite"></p>
                    </div>

                    <div class="tta-license-search">
                      <h3><?php esc_html_e( 'All Members', 'tta' ); ?></h3>
                      <p class="tta-section-intro">
                        <?php
                        echo wp_kses(
                            __( 'Below are the Members you&#039;ve uploaded or added individually. Search by First Name, Last Name, or Email address. If a Member&#039;s Status reads as "Active", that means they&#039;ve taken advantage of their Membership benefits and created an account! If a Member is no longer eligible for their Membership benefits, simply search for that member and click the "No Longer Employed" button.', 'tta' ),
                            []
                        );
                        ?>
                      </p>
                      <div class="tta-license-search-fields">
                        <input type="text" id="tta-search-first" placeholder="<?php esc_attr_e( 'First Name', 'tta' ); ?>" />
                        <input type="text" id="tta-search-last" placeholder="<?php esc_attr_e( 'Last Name', 'tta' ); ?>" />
                        <input type="text" id="tta-search-email" placeholder="<?php esc_attr_e( 'Email', 'tta' ); ?>" />
                        <button type="button" class="tta-button" id="tta-license-search-btn"><?php esc_html_e( 'Search', 'tta' ); ?></button>
                        <button type="button" class="tta-button tta-button-secondary" id="tta-license-reset-btn"><?php esc_html_e( 'Reset Search', 'tta' ); ?></button>
                      </div>
                      <div id="tta-license-results">
                        <p class="tta-license-empty"><?php esc_html_e( 'No partner members found yet.', 'tta' ); ?></p>
                      </div>
                    </div>

                    <div class="tta-license-pagination" aria-live="polite"></div>
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
    var $respMsg = $('#tta-license-upload-response-message');
    var $spinner = $('.tta-license-upload .tta-admin-progress-spinner-svg');
    var $progressBar = $('#tta-license-upload-response');
    var $progressSpinner = $('#tta-upload-progress-spinner');
    var $progressHolderSpinner = $('.tta-license-upload-progress-holder .tta-admin-progress-spinner-svg');
    var currentJob = null;
    var pollTimer = null;
    var $singleBtn = $('#tta-single-add-btn');
    var $singleSpinner = $('#tta-single-spinner');
    var $singleResp = $('#tta-single-response');
    var $singleFirst = $('#tta-single-first');
    var $singleLast = $('#tta-single-last');
    var $singleEmail = $('#tta-single-email');
    var $results = $('#tta-license-results');
    var $pagination = $('.tta-license-pagination');
    var $searchBtn = $('#tta-license-search-btn');
    var $resetBtn = $('#tta-license-reset-btn');
    var $searchFirst = $('#tta-search-first');
    var $searchLast = $('#tta-search-last');
    var $searchEmail = $('#tta-search-email');
    var perPage = 20;

    $spinner.hide();
    $progressSpinner.hide();
    $progressHolderSpinner.css({ display: 'none', opacity: 0 });
    $singleSpinner.hide();

    function capitalizeWords(value) {
      if (!value) {
        return '';
      }
      return value.toString().toLowerCase().replace(/\b\w/g, function(letter){
        return letter.toUpperCase();
      });
    }

    function resetState() {
      $respMsg.removeClass('error updated').text('');
    }

    function showError(msg) {
      $respMsg.removeClass('updated').addClass('error').text(msg);
    }

    function showSuccess(msg) {
      $respMsg.removeClass('error').addClass('updated').text(msg);
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
      $progressSpinner.show();
      $progressHolderSpinner.stop(true, true).css({ display: 'inline-block' }).fadeTo(200, 1);
      updateProgress(0);
      $resp.css('opacity', 1);

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
        // Keep progress spinners visible until job completes; hide only on fail or completion.
        if (res && res.success) {
          showSuccess(res.data && res.data.message ? res.data.message : (uploadCfg.success || 'Import started. Please remain on this page while we process the file.'));
          currentJob = res.data && res.data.job_id ? res.data.job_id : null;
          if (currentJob) {
            startPolling();
          }
          fetchMembers(1);
        } else {
          var msg = res && res.data && res.data.message ? res.data.message : (uploadCfg.error || 'Upload failed.');
          showError(msg);
          updateProgress(0);
          $progressSpinner.hide();
        }
      }).fail(function(){
        $btn.prop('disabled', false);
        $spinner.hide();
        $progressSpinner.hide();
        $progressHolderSpinner.fadeOut(200);
        showError(uploadCfg.error || 'Upload failed.');
      });
    });

    function resetSingle() {
      $singleResp.removeClass('error updated').text('');
    }

    function singleError(msg) {
      $singleResp.removeClass('updated').addClass('error').text(msg);
    }

    function singleSuccess(msg) {
      $singleResp.removeClass('error').addClass('updated').text(msg);
    }

    $singleBtn.on('click', function(){
      resetSingle();
      var first = $singleFirst.val();
      var last = $singleLast.val();
      var email = $singleEmail.val();
      if (!first || !last || !email) {
        singleError(uploadCfg.singleMissing || 'Please provide first name, last name, and email.');
        return;
      }
      $singleBtn.prop('disabled', true);
      $singleSpinner.show();
      $.post(uploadCfg.ajaxUrl, {
        action: 'tta_add_partner_member',
        nonce: uploadCfg.nonce,
        page_id: uploadCfg.pageId,
        first_name: first,
        last_name: last,
        email: email
      }, null, 'json').done(function(res){
        $singleBtn.prop('disabled', false);
        $singleSpinner.hide();
        if (res && res.success) {
          singleSuccess(res.data && res.data.message ? res.data.message : (uploadCfg.success || 'Member added successfully.'));
          $singleFirst.val('');
          $singleLast.val('');
          $singleEmail.val('');
          fetchMembers(1);
        } else {
          var msg = res && res.data && res.data.message ? res.data.message : (uploadCfg.error || 'Request failed.');
          singleError(msg);
        }
      }).fail(function(){
        $singleBtn.prop('disabled', false);
        $singleSpinner.hide();
        singleError(uploadCfg.error || 'Request failed.');
      });
    });

    function renderMembers(members){
      if (!members || !members.length){
        $results.html('<p class="tta-license-empty">' + (uploadCfg.noResults || 'No partner members found.') + '</p>');
        return;
      }
      var html = '<div class="tta-license-accordion">';
      
      members.forEach(function(m){
        var firstName = capitalizeWords(m.first_name || '');
        var lastName = capitalizeWords(m.last_name || '');
        var name = (firstName + ' ' + lastName).trim();
        html += '<div class="tta-license-item">';
        html += '<button type="button" class="tta-license-toggle" aria-expanded="false">' +
                '<span class="tta-license-col tta-license-col-name tta-license-col-firstname">' + firstName + '</span>' +
                '<span class="tta-license-col tta-license-col-name tta-license-col-lastname">' + lastName + ' - </span>' +
                '<span class="tta-license-col tta-license-col-email">' + (m.email || '') + '</span>' +
                '</button>';
        html += '<div class="tta-license-panel" hidden>';
        html += '<p><strong><?php echo esc_js( __( 'First Name', 'tta' ) ); ?>:</strong> ' + firstName + '</p>';
        html += '<p><strong><?php echo esc_js( __( 'Last Name', 'tta' ) ); ?>:</strong> ' + lastName + '</p>';
        html += '<p><strong><?php echo esc_js( __( 'Email', 'tta' ) ); ?>:</strong> ' + (m.email || '') + '</p>';
        if (m.joined_at){
          var joined = new Date(m.joined_at.replace(' ', 'T'));
          var options = { month:'2-digit', day:'2-digit', year:'numeric', hour:'numeric', minute:'2-digit', hour12:true };
          var formatted = isNaN(joined.getTime()) ? m.joined_at : joined.toLocaleString([], options).replace(',', ' at');
          html += '<p><strong><?php echo esc_js( __( 'Added On', 'tta' ) ); ?>:</strong> ' + formatted + '</p>';
        }
        var status = (m.wpuserid && parseInt(m.wpuserid,10) !== 0) ? '<?php echo esc_js( __( 'Active', 'tta' ) ); ?>' : '<?php echo esc_js( __( 'Inactive', 'tta' ) ); ?>';
        html += '<p><strong><?php echo esc_js( __( 'Status', 'tta' ) ); ?>:</strong> ' + status + '</p>';
        html += '<button type="button" class="tta-button tta-button-secondary tta-license-employment-btn" data-member-id="' + (m.id || '') + '"><?php echo esc_js( __( 'No Longer Employed', 'tta' ) ); ?></button>';
        html += '<p class="tta-license-employment-response" role="status" aria-live="polite"></p>';
        html += '</div></div>';
      });
      html += '</div>';
      $results.html(html);
    }

    function renderPagination(page, pages){
      if (!pages || pages <= 1){
        $pagination.empty();
        return;
      }
      var label = (uploadCfg.paginationLabel || 'Page %1$d of %2$d').replace('%1$d', page).replace('%2$d', pages);
      var html = '<div class="tta-license-pager">';
      if (page > 1){
        html += '<button type="button" class="tta-button tta-license-page tta-license-page-prev-button" data-page="' + (page-1) + '">&laquo; <?php echo esc_js( __( 'Prev', 'tta' ) ); ?></button>';
      }
      if (page < pages){
        html += '<button type="button" class="tta-button tta-license-page" data-page="' + (page+1) + '"><?php echo esc_js( __( 'Next', 'tta' ) ); ?> &raquo;</button>';
      }
      html += '<span class="tta-pages-span">' + label + '</span></div>';
      $pagination.html(html);
    }

    function fetchMembers(page){
      page = page || 1;
      $pagination.empty();
      $results.html('<p class="tta-license-empty"><?php echo esc_js( __( 'Loading…', 'tta' ) ); ?></p>');
      $.post(uploadCfg.ajaxUrl, {
        action: 'tta_fetch_partner_members',
        nonce: uploadCfg.fetchNonce,
        page_id: uploadCfg.pageId,
        page: page,
        per_page: perPage,
        first_name: $searchFirst.val(),
        last_name: $searchLast.val(),
        email: $searchEmail.val()
      }, null, 'json').done(function(res){
        if (res && res.success){
          renderMembers(res.data.members || []);
          renderPagination(res.data.page, res.data.pages);
        } else {
          var msg = res && res.data && res.data.message ? res.data.message : (uploadCfg.error || 'Request failed.');
          $results.html('<p class="tta-license-empty error">' + msg + '</p>');
        }
      }).fail(function(){
        $results.html('<p class="tta-license-empty error">' + (uploadCfg.error || 'Request failed.') + '</p>');
      });
    }

    $results.on('click', '.tta-license-toggle', function(){
      var $btn = $(this);
      var $panel = $btn.next('.tta-license-panel');
      var expanded = $btn.attr('aria-expanded') === 'true';
      $btn.attr('aria-expanded', !expanded);
      $panel.attr('hidden', expanded);
    });

    $results.on('click', '.tta-license-employment-btn', function(){
      var $btn = $(this);
      var memberId = parseInt($btn.data('member-id'), 10);
      if (!memberId) {
        return;
      }
      var confirmMessage = uploadCfg.employmentConfirm || 'Are you sure you want to remove this person?';
      if (!window.confirm(confirmMessage)) {
        return;
      }
      var $panel = $btn.closest('.tta-license-panel');
      var $message = $panel.find('.tta-license-employment-response');
      $message.removeClass('error updated').text('');
      $btn.prop('disabled', true);
      $.post(uploadCfg.ajaxUrl, {
        action: 'tta_partner_end_employment',
        nonce: uploadCfg.updateNonce,
        page_id: uploadCfg.pageId,
        member_id: memberId
      }, null, 'json').done(function(res){
        $btn.prop('disabled', false);
        if (res && res.success) {
          $message.removeClass('error').addClass('updated').text(res.data && res.data.message ? res.data.message : (uploadCfg.employmentSuccess || 'Member updated.'));
          fetchMembers(1);
        } else {
          var msg = res && res.data && res.data.message ? res.data.message : (uploadCfg.employmentError || 'Request failed.');
          $message.removeClass('updated').addClass('error').text(msg);
        }
      }).fail(function(){
        $btn.prop('disabled', false);
        $message.removeClass('updated').addClass('error').text(uploadCfg.employmentError || 'Request failed.');
      });
    });

    $pagination.on('click', '.tta-license-page', function(){
      var page = parseInt($(this).data('page'), 10) || 1;
      fetchMembers(page);
    });

    $searchBtn.on('click', function(){
      fetchMembers(1);
    });

    $resetBtn.on('click', function(){
      $searchFirst.val('');
      $searchLast.val('');
      $searchEmail.val('');
      fetchMembers(1);
    });

    fetchMembers(1);

    function startPolling(){
      if (!currentJob) return;
      if (pollTimer) clearInterval(pollTimer);
      pollTimer = setInterval(function(){
        $.post(uploadCfg.ajaxUrl, {
          action: 'tta_partner_import_status',
          nonce: uploadCfg.fetchNonce,
          job_id: currentJob
        }, null, 'json').done(function(res){
          if (!res || !res.success) return;
          var added = res.data && res.data.added ? parseInt(res.data.added,10) : 0;
          var total = res.data && res.data.total ? parseInt(res.data.total,10) : 0;
          var percent = total ? Math.min(100, Math.round((added/total)*100)) : 0;
          updateProgress(percent);
          if (percent > 0) {
            showSuccess((percent + '% ' + (uploadCfg.processing || 'processed')).trim());
          }
          if (res.data && res.data.message){
            showSuccess(res.data.message);
          }
          if (res.data && res.data.status && (res.data.status === 'completed' || res.data.status === 'failed')) {
            clearInterval(pollTimer);
            pollTimer = null;
            $progressSpinner.hide();
            $progressHolderSpinner.fadeOut(200);
            fetchMembers(1);
          }
        });
      }, 5000);
    }

    function updateProgress(percent){
      if (!$progressBar.length) return;
      percent = Math.max(0, Math.min(100, percent || 0));
      $progressBar.css('width', percent + '%').attr('aria-valuenow', percent);
    }
  });
})(jQuery);
</script>
<?php
get_footer();
