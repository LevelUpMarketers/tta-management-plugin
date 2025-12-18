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
                  'fetchNonce'  => wp_create_nonce( 'tta_partner_fetch_action' ),
                  'pageId'      => $page_id,
                  'noFile'      => __( 'Please select a CSV file to upload.', 'tta' ),
                  'emptyFile'   => __( 'The selected file appears to be empty.', 'tta' ),
                  'badHeaders'  => __( 'Missing required headers: First Name, Last Name, Email.', 'tta' ),
                  'success'     => __( 'Upload complete.', 'tta' ),
                  'error'       => __( 'Upload failed.', 'tta' ),
                  'noResults'   => __( 'No partner members found.', 'tta' ),
                  'paginationLabel' => __( 'Page %1$d of %2$d', 'tta' ),
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
                      <img class="tta-admin-progress-spinner-svg" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                      <p id="tta-license-upload-response" class="tta-admin-progress-response-p" role="status" aria-live="polite"></p>
                    </div>

                    <div class="tta-license-single-add">
                      <h3><?php esc_html_e( 'Add an Individual', 'tta' ); ?></h3>
                      <div class="tta-license-single-fields">
                        <input type="text" id="tta-single-first" placeholder="<?php esc_attr_e( 'First Name', 'tta' ); ?>" />
                        <input type="text" id="tta-single-last" placeholder="<?php esc_attr_e( 'Last Name', 'tta' ); ?>" />
                        <input type="text" id="tta-single-email" placeholder="<?php esc_attr_e( 'Email', 'tta' ); ?>" />
                        <button type="button" class="tta-button tta-button-primary" id="tta-single-add-btn"><?php esc_html_e( 'Add Member', 'tta' ); ?></button>
                        <img class="tta-admin-progress-spinner-svg" id="tta-single-spinner" src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
                      </div>
                      <p id="tta-single-response" class="tta-admin-progress-response-p" role="status" aria-live="polite"></p>
                    </div>

                    <div class="tta-license-search">
                      <h3><?php esc_html_e( 'Search Existing Partner Members', 'tta' ); ?></h3>
                      <div class="tta-license-search-fields">
                        <input type="text" id="tta-search-first" placeholder="<?php esc_attr_e( 'First Name', 'tta' ); ?>" />
                        <input type="text" id="tta-search-last" placeholder="<?php esc_attr_e( 'Last Name', 'tta' ); ?>" />
                        <input type="text" id="tta-search-email" placeholder="<?php esc_attr_e( 'Email', 'tta' ); ?>" />
                        <button type="button" class="tta-button" id="tta-license-search-btn"><?php esc_html_e( 'Search', 'tta' ); ?></button>
                        <button type="button" class="tta-button tta-button-secondary" id="tta-license-reset-btn"><?php esc_html_e( 'Reset Search', 'tta' ); ?></button>
                      </div>
                    </div>

                    <div id="tta-license-results">
                      <p class="tta-license-empty"><?php esc_html_e( 'No partner members found yet.', 'tta' ); ?></p>
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
    var $resp = $('#tta-license-upload-response');
    var $spinner = $('.tta-license-upload .tta-admin-progress-spinner-svg');
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
    $singleSpinner.hide();

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
          fetchMembers(1);
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
        singleError(uploadCfg.emptyFile || 'Please provide first name, last name, and email.');
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
      html += '<div class="tta-license-header-row">';
      html += '<span class="tta-license-col tta-license-col-name"><?php echo esc_js( __( 'First Name', 'tta' ) ); ?></span>';
      html += '<span class="tta-license-col tta-license-col-name"><?php echo esc_js( __( 'Last Name', 'tta' ) ); ?></span>';
      html += '<span class="tta-license-col tta-license-col-email"><?php echo esc_js( __( 'Email', 'tta' ) ); ?></span>';
      html += '</div>';
      members.forEach(function(m){
        var name = ((m.first_name || '') + ' ' + (m.last_name || '')).trim();
        html += '<div class="tta-license-item">';
        html += '<button type="button" class="tta-license-toggle" aria-expanded="false">' +
                '<span class="tta-license-col tta-license-col-name">' + (m.first_name || '') + '</span>' +
                '<span class="tta-license-col tta-license-col-name">' + (m.last_name || '') + '</span>' +
                '<span class="tta-license-col tta-license-col-email">' + (m.email || '') + '</span>' +
                '</button>';
        html += '<div class="tta-license-panel" hidden>';
        html += '<p><strong><?php echo esc_js( __( 'First Name', 'tta' ) ); ?>:</strong> ' + (m.first_name || '') + '</p>';
        html += '<p><strong><?php echo esc_js( __( 'Last Name', 'tta' ) ); ?>:</strong> ' + (m.last_name || '') + '</p>';
        html += '<p><strong><?php echo esc_js( __( 'Email', 'tta' ) ); ?>:</strong> ' + (m.email || '') + '</p>';
        if (m.joined_at){
          var joined = new Date(m.joined_at.replace(' ', 'T'));
          var options = { month:'2-digit', day:'2-digit', year:'numeric', hour:'numeric', minute:'2-digit', hour12:true };
          var formatted = isNaN(joined.getTime()) ? m.joined_at : joined.toLocaleString([], options).replace(',', ' at');
          html += '<p><strong><?php echo esc_js( __( 'Added On', 'tta' ) ); ?>:</strong> ' + formatted + '</p>';
        }
        var status = (m.wpuserid && parseInt(m.wpuserid,10) !== 0) ? '<?php echo esc_js( __( 'Active', 'tta' ) ); ?>' : '<?php echo esc_js( __( 'Inactive', 'tta' ) ); ?>';
        html += '<p><strong><?php echo esc_js( __( 'Status', 'tta' ) ); ?>:</strong> ' + status + '</p>';
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
      var html = '<div class="tta-license-pager"><span>' + label + '</span>';
      if (page > 1){
        html += '<button type="button" class="tta-button tta-license-page" data-page="' + (page-1) + '">&laquo; <?php echo esc_js( __( 'Prev', 'tta' ) ); ?></button>';
      }
      if (page < pages){
        html += '<button type="button" class="tta-button tta-license-page" data-page="' + (page+1) + '"><?php echo esc_js( __( 'Next', 'tta' ) ); ?> &raquo;</button>';
      }
      html += '</div>';
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
  });
})(jQuery);
</script>
<?php
get_footer();
