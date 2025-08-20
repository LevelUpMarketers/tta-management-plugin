<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$bio = stripslashes( $member['biography'] );
$hide_attendance = intval( $member['hide_event_attendance'] );

// Assume $member, $street_address, $address_2, $city, $state, $zip are already defined above
?>
<div id="tab-profile" class="tta-dashboard-section">

  <form id="tta-member-dashboard-form"
        method="post"
        action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
        enctype="multipart/form-data">

    <?php wp_nonce_field( 'tta_member_front_update', 'tta_member_front_update_nonce' ); ?>
    <input type="hidden" name="action" value="tta_front_update_member">

    <table class="form-table">
      <tbody>

        <!-- First Name -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Enter your given first name as you’d like it displayed.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="first_name"><?php esc_html_e( 'First Name', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['first_name'] ); ?></span>
            <input class="edit-input regular-text"
                   type="text"
                   name="first_name"
                   id="first_name"
                   value="<?php echo esc_attr( $member['first_name'] ); ?>"
                   required>
          </td>
        </tr>

        <!-- Last Name -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Enter your family/last name.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="last_name"><?php esc_html_e( 'Last Name', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['last_name'] ); ?></span>
            <input class="edit-input regular-text"
                   type="text"
                   name="last_name"
                   id="last_name"
                   value="<?php echo esc_attr( $member['last_name'] ); ?>"
                   required>
          </td>
        </tr>

        <!-- Email Address -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Primary email where we’ll send confirmations & updates.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="email"><?php esc_html_e( 'Email Address', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['email'] ); ?></span>
            <input class="edit-input regular-text"
                   type="email"
                   name="email"
                   id="email"
                   value="<?php echo esc_attr( $member['email'] ); ?>"
                   required>
          </td>
        </tr>

        <!-- Verify Email Address (hidden until edit) -->
        <tr class="profile-row hide-until-edit">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Re-enter your email to confirm there are no typos.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="email_verify"><?php esc_html_e( 'Verify Email Address', 'tta' ); ?></label>
          </th>
          <td>
            <input class="edit-input regular-text"
                   type="email"
                   name="email_verify"
                   id="email_verify"
                   value="<?php echo esc_attr( $member['email'] ); ?>"
                   required>
          </td>
        </tr>

        <!-- Phone Number -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Your number for SMS/text notifications.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="phone"><?php esc_html_e( 'Phone Number', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['phone'] ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="tel"
                   name="phone"
                   id="phone"
                   value="<?php echo esc_attr( $member['phone'] ); ?>">
          </td>
        </tr>

        <!-- Date of Birth -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Used to celebrate member birthdays, verify age-restricted events, etc.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="dob"><?php esc_html_e( 'Date of Birth', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['dob'] ?: '—' ); ?></span>
            <input class="edit-input"
                   type="date"
                   name="dob"
                   id="dob"
                   value="<?php echo esc_attr( $member['dob'] ); ?>">
          </td>
        </tr>

        <!-- Facebook URL -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Link to your public Facebook profile.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="facebook"><?php esc_html_e( 'Facebook URL', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['facebook'] ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="url"
                   name="facebook"
                   id="facebook"
                   value="<?php echo esc_attr( $member['facebook'] ); ?>">
          </td>
        </tr>

        <!-- LinkedIn URL -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Link to your LinkedIn profile.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="linkedin"><?php esc_html_e( 'LinkedIn URL', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['linkedin'] ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="url"
                   name="linkedin"
                   id="linkedin"
                   value="<?php echo esc_attr( $member['linkedin'] ); ?>">
          </td>
        </tr>

        <!-- Instagram URL -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Link to your Instagram account.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="instagram"><?php esc_html_e( 'Instagram URL', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['instagram'] ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="url"
                   name="instagram"
                   id="instagram"
                   value="<?php echo esc_attr( $member['instagram'] ); ?>">
          </td>
        </tr>

        <!-- X/Twitter URL -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Link to your Twitter (X) handle.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="twitter"><?php esc_html_e( 'X/Twitter URL', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $member['twitter'] ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="url"
                   name="twitter"
                   id="twitter"
                   value="<?php echo esc_attr( $member['twitter'] ); ?>">
          </td>
        </tr>

        <!-- Street Address -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Your primary mailing address.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="street_address"><?php esc_html_e( 'Street Address', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $street_address ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="text"
                   name="street_address"
                   id="street_address"
                   value="<?php echo esc_attr( $street_address ); ?>">
          </td>
        </tr>

        <!-- Address Line 2 -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Apt, suite, unit, etc.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="address_2"><?php esc_html_e( 'Address Line 2', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $address_2 ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="text"
                   name="address_2"
                   id="address_2"
                   value="<?php echo esc_attr( $address_2 ); ?>">
          </td>
        </tr>

        <!-- City -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'City or town.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="city"><?php esc_html_e( 'City', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $city ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="text"
                   name="city"
                   id="city"
                   value="<?php echo esc_attr( $city ); ?>">
          </td>
        </tr>

        <!-- State -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Two-letter code (e.g. VA).', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="state_fd"><?php esc_html_e( 'State', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $state ?: '—' ); ?></span>
            <select class="edit-input regular-dropdown"
                    name="state"
                    id="state_fd">
              <?php
              $states = tta_get_us_states();
              foreach ( $states as $abbr => $label ) {
                printf(
                  '<option value="%1$s" %2$s>%3$s</option>',
                  esc_attr( $abbr ),
                  selected( $state, $abbr, false ),
                  esc_html( $label )
                );
              }
              ?>
            </select>
          </td>
        </tr>

        <!-- ZIP Code -->
        <tr class="profile-row">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( '
5-digit postal code.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="zip"><?php esc_html_e( 'ZIP Code', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value"><?php echo esc_html( $zip ?: '—' ); ?></span>
            <input class="edit-input regular-text"
                   type="text"
                   name="zip"
                   id="zip"
                   value="<?php echo esc_attr( $zip ); ?>">
          </td>
        </tr>

        <tr class="spacer-row"><td colspan="2"></td></tr>

        <!-- Biography -->
        <tr class="profile-row tta-wider-and-bigger-rows">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Tell us about yourself—background, interests, etc.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="biography"><?php esc_html_e( 'Biography', 'tta' ); ?></label>
          </th>
          <td>
            <p class="view-value"><?php echo esc_html( $bio ?: '—' ); ?></p>
            <textarea class="edit-input large-text"
                      name="biography"
                      id="biography"
                      placeholder="<?php esc_attr_e( 'Tell us about yourself…', 'tta' ); ?>"><?php echo esc_textarea( $bio ); ?></textarea>
          </td>
        </tr>

        <!-- Interests -->
        <tr class="profile-row tta-wider-and-bigger-rows">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'List your hobbies or areas of interest.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label for="interests_edit"><?php esc_html_e( 'Interests', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value">
              <?php
                $ints = array_filter( array_map( 'trim', explode( ',', $member['interests'] ) ) );
                echo $ints ? esc_html( implode( ', ', $ints ) ) : '—';
              ?>
            </span>
            <div class="edit-input" id="interests-container">
              <?php
              $interests = $ints ?: [''];
              foreach ( $interests as $i => $int ) :
                $count = $i + 1;
              ?>
                <div class="interest-item">
                  <input type="text"
                         name="interests[]"
                         class="regular-text interest-field"
                         value="<?php echo esc_attr( $int ); ?>"
                         placeholder="<?php echo esc_attr( "Interest #{$count}" ); ?>">
                  <button type="button"
                          class="delete-interest"
                          aria-label="<?php esc_attr_e( 'Remove this interest', 'tta' ); ?>">
                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/bin.svg' ); ?>" alt="×">
                  </button>
                </div>
              <?php endforeach; ?>
              <button type="button" id="add-interest-edit" class="button">
                + <?php esc_html_e( 'Add Another Interest', 'tta' ); ?>
              </button>
            </div>
          </td>
        </tr>

        <!-- Opt-In Preferences -->
        <tr class="profile-row tta-wider-and-bigger-rows">
          <th>
            <span class="tta-fake-label"><?php esc_html_e( 'Opt-In Preferences', 'tta' ); ?></span>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Select which communications you’d like to receive.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td>
            <?php
            $opt_email        = intval( $member['opt_in_marketing_email'] );
            $opt_sms          = intval( $member['opt_in_marketing_sms'] );
            $opt_update_email = intval( $member['opt_in_event_update_email'] );
            $opt_update_sms   = intval( $member['opt_in_event_update_sms'] );
            ?>
            <p class="view-value">
              <?php
                $opts = [];
                if ( $opt_email )        $opts[] = __( 'Marketing Emails', 'tta' );
                if ( $opt_sms )          $opts[] = __( 'Marketing SMS', 'tta' );
                if ( $opt_update_email ) $opts[] = __( 'Event Update Emails', 'tta' );
                if ( $opt_update_sms )   $opts[] = __( 'Event Update SMS', 'tta' );
                echo $opts ? esc_html( implode( ', ', $opts ) ) : '—';
              ?>
            </p>
            <fieldset class="edit-input">
              <label><input type="checkbox" name="opt_in_marketing_email"    value="1" <?php checked( $opt_email, 1 ); ?> /> <?php esc_html_e( 'Marketing Emails', 'tta' ); ?></label>
              <label><input type="checkbox" name="opt_in_marketing_sms"      value="1" <?php checked( $opt_sms, 1 ); ?> /> <?php esc_html_e( 'Marketing SMS', 'tta' ); ?></label>
              <label><input type="checkbox" name="opt_in_event_update_email" value="1" <?php checked( $opt_update_email, 1 ); ?> /> <?php esc_html_e( 'Event Update Emails', 'tta' ); ?></label>
              <label><input type="checkbox" name="opt_in_event_update_sms"   value="1" <?php checked( $opt_update_sms, 1 ); ?> /> <?php esc_html_e( 'Event Update SMS', 'tta' ); ?></label>
            </fieldset>
          </td>
        </tr>

        <!-- Privacy Options -->
        <tr class="profile-row tta-wider-and-bigger-rows">
          <th>
            <span class="tta-fake-label"><?php esc_html_e( 'Privacy Options', 'tta' ); ?></span>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Control what information is shown publicly.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
          </th>
          <td>
            <p class="view-value"><?php echo $hide_attendance ? esc_html__( 'Event attendance hidden', 'tta' ) : '—'; ?></p>
            <fieldset class="edit-input">
              <label><input type="checkbox" name="hide_event_attendance" value="1" <?php checked( $hide_attendance, 1 ); ?> /> <?php esc_html_e( 'Hide Event Attendance', 'tta' ); ?></label>
            </fieldset>
          </td>
        </tr>

        <!-- Profile Image -->
        <tr class="profile-row tta-wider-and-bigger-rows">
          <th>
            <span class="tta-tooltip-icon" data-tooltip="<?php esc_attr_e( 'Upload a picture for your public profile.', 'tta' ); ?>">
              <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/public/question.svg' ); ?>" alt="Help">
            </span>
            <label class="tta-special-vert-adjust"><?php esc_html_e( 'Profile Image', 'tta' ); ?></label>
          </th>
          <td>
            <span class="view-value">
              <?php
              if ( $member['profileimgid'] ) {
                $url = wp_get_attachment_url( $member['profileimgid'] );
                echo '<img src="' . esc_url( $url ) . '" class="attachment-thumbnail size-thumbnail" alt="' . esc_attr( $member['first_name'] ) . '">';
              } else {
                echo '<img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' ) . '" class="attachment-thumbnail size-thumbnail" alt="Placeholder">';
              }
              ?>
            </span>
            <div class="edit-input tta-profile-image-wrapper">
              <input type="file" id="profile-image-input" name="profile_image_file" accept="image/*">
              <input type="hidden" id="profileimgid" name="profileimgid" value="<?php echo esc_attr( $member['profileimgid'] ); ?>">
              <div id="profileimage-preview">
                <?php
                if ( $member['profileimgid'] ) {
                  echo '<img src="' . esc_url( $url ) . '" class="attachment-thumbnail size-thumbnail" alt="">';
                } else {
                  echo '<img src="' . esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' ) . '" class="attachment-thumbnail size-thumbnail" alt="">';
                }
                ?>
              </div>
              <button type="button" id="select-profile-image" class="button">
                <?php esc_html_e( 'Select Profile Image', 'tta' ); ?>
              </button>
            </div>
          </td>
        </tr>

      </tbody>
    </table>

    <div class="tta-submit-wrap">
      <button type="button" id="toggle-edit-mode" class="button">
        <?php esc_html_e( 'Edit Profile', 'tta' ); ?>
      </button>
      <button type="submit" class="button button-primary edit-input">
        <?php esc_html_e( 'Save Changes', 'tta' ); ?>
      </button>
      <span class="tta-progress-spinner">
        <img class="tta-admin-progress-spinner-svg"
             src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>"
             alt="<?php esc_attr_e( 'Loading…', 'tta' ); ?>" />
      </span>
      <span class="tta-admin-progress-response-p"></span>
    </div>

  </form>
</div>
