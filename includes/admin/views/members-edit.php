<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// We expect $_GET['member_id'] to be set by the AJAX handler before including this file:
$member_id = intval( $_GET['member_id'] );

global $wpdb;
$members_table = $wpdb->prefix . 'tta_members';
$member = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$members_table} WHERE id = %d", $member_id ), ARRAY_A );
if ( ! $member ) {
    echo '<p>Member not found.</p>';
    return;
}

// For user meta (profileimgid), if needed:
$wp_user_id   = intval( $member['wpuserid'] );
$profileimgid = $member['profileimgid'];
$hide_attendance = intval( $member['hide_event_attendance'] );

// Parse address components
$street_address = '';
$address_2      = '';
$city           = '';
$state          = '';
$zip            = '';
if ( ! empty( $member['address'] ) ) {
    $addr            = tta_parse_address( $member['address'] );
    $street_address  = $addr['street'];
    $address_2       = $addr['address2'];
    $city            = $addr['city'];
    $state           = $addr['state'];
    $zip             = $addr['zip'];
}

// Ensure media uploader can work:
wp_enqueue_media();
?>

<form method="post" id="tta-member-edit-form">
    <?php wp_nonce_field( 'tta_member_update_action', 'tta_member_update_nonce' ); ?>
    <input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">

    <table class="form-table">
        <tbody>
            <!-- First Name -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s first name.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="first_name_edit">First Name</label>
                </th>
                <td>
                    <input type="text"
                           name="first_name"
                           id="first_name_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['first_name'] ); ?>"
                           required>
                </td>
            </tr>

            <!-- Last Name -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s last name.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="last_name_edit">Last Name</label>
                </th>
                <td>
                    <input type="text"
                           name="last_name"
                           id="last_name_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['last_name'] ); ?>"
                           required>
                </td>
            </tr>

            <!-- Email Address -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s email address.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="email_edit">Email Address</label>
                </th>
                <td>
                    <input type="email"
                           name="email"
                           id="email_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['email'] ); ?>"
                           required>
                </td>
            </tr>

            <!-- Verify Email Address -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Re-enter the same email to confirm.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="email_verify_edit">Verify Email Address</label>
                </th>
                <td>
                    <input type="email"
                           name="email_verify"
                           id="email_verify_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['email'] ); ?>"
                           required>
                </td>
            </tr>

            <!-- Street Address -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s street address.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="street_address_edit">Street Address</label>
                </th>
                <td>
                    <input type="text"
                           name="street_address"
                           id="street_address_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $street_address ); ?>">
                </td>
            </tr>

            <!-- Address Line 2 -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s suite, apartment, etc.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="address_2_edit">Address 2</label>
                </th>
                <td>
                    <input type="text"
                           name="address_2"
                           id="address_2_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $address_2 ); ?>">
                </td>
            </tr>

            <!-- City -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s city.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="city_edit">City</label>
                </th>
                <td>
                    <input type="text"
                           name="city"
                           id="city_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $city ); ?>">
                </td>
            </tr>

            <!-- State -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s state.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="state_edit">State</label>
                </th>
                <td>
                    <select name="state" id="state_edit">
                        <?php
                        // Use helper function instead of hard-coded array
                        $states = tta_get_us_states();
                        foreach ( $states as $abbr => $name ) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr( $abbr ),
                                selected( $state, $abbr, false ),
                                esc_html( $name )
                            );
                        }
                        ?>
                    </select>
                </td>
            </tr>

            <!-- ZIP Code -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s ZIP code.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="zip_edit">ZIP</label>
                </th>
                <td>
                    <input type="text"
                           name="zip"
                           id="zip_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $zip ); ?>">
                </td>
            </tr>

            <!-- Phone -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the phone number.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="phone_edit">Phone Number</label>
                </th>
                <td>
                    <input type="tel"
                           name="phone"
                           id="phone_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['phone'] ); ?>">
                </td>
            </tr>

            <!-- Date of Birth -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s birth date.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="dob_edit">Date of Birth</label>
                </th>
                <td>
                    <input type="date"
                           name="dob"
                           id="dob_edit"
                           value="<?php echo esc_attr( $member['dob'] ); ?>">
                </td>
            </tr>

            <!-- Member Type -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Change the member’s role.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="member_type_edit">Member Type</label>
                </th>
                <td>
                    <select name="member_type" id="member_type_edit">
                        <option value="member"     <?php selected( $member['member_type'], 'member' ); ?>>Member</option>
                        <option value="volunteer"  <?php selected( $member['member_type'], 'volunteer' ); ?>>Volunteer</option>
                        <option value="admin"      <?php selected( $member['member_type'], 'admin' ); ?>>Admin</option>
                        <option value="super_admin"<?php selected( $member['member_type'], 'super_admin' ); ?>>Super Admin</option>
                    </select>
                </td>
            </tr>

            <!-- Membership Level -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Change subscription tier.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="membership_level_edit">Membership Level</label>
                </th>
                <td>
                    <select name="membership_level" id="membership_level_edit">
                        <option value="free"    <?php selected( $member['membership_level'], 'free' ); ?>>Free</option>
                        <option value="basic"   <?php selected( $member['membership_level'], 'basic' ); ?>>Basic</option>
                        <option value="premium" <?php selected( $member['membership_level'], 'premium' ); ?>>Premium</option>
                    </select>
                </td>
            </tr>

            <!-- Facebook URL -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the Facebook link.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="facebook_edit">Facebook URL</label>
                </th>
                <td>
                    <input type="url"
                           name="facebook"
                           id="facebook_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['facebook'] ); ?>">
                </td>
            </tr>

            <!-- LinkedIn URL -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the LinkedIn link.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="linkedin_edit">LinkedIn URL</label>
                </th>
                <td>
                    <input type="url"
                           name="linkedin"
                           id="linkedin_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['linkedin'] ); ?>">
                </td>
            </tr>

            <!-- Instagram URL -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the Instagram link.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="instagram_edit">Instagram URL</label>
                </th>
                <td>
                    <input type="url"
                           name="instagram"
                           id="instagram_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['instagram'] ); ?>">
                </td>
            </tr>

            <!-- Twitter URL -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the Twitter (X) link.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="twitter_edit">X/Twitter URL</label>
                </th>
                <td>
                    <input type="url"
                           name="twitter"
                           id="twitter_edit"
                           class="regular-text"
                           value="<?php echo esc_attr( $member['twitter'] ); ?>">
                </td>
            </tr>
            <!-- Interests -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Comma-separated interests.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="interests_edit">Interests</label>
                </th>
                <td>
                    <div id="interests-container">
                        <?php
                        $interests = array_filter( array_map( 'trim', explode( ',', $member['interests'] ) ) );
                        if ( empty( $interests ) ) {
                            $interests = [''];
                        }
                        foreach ( $interests as $i => $int ) :
                            $count = $i + 1;
                        ?>
                            <div class="interest-item" style="margin-bottom:8px; display:flex; align-items:center;">
                                <input
                                  type="text"
                                  name="interests[]"
                                  class="regular-text interest-field"
                                  value="<?php echo esc_attr( $int ); ?>"
                                  placeholder="<?php echo esc_attr( "Interest #{$count}" ); ?>"
                                  style="flex:1;"
                                />
                                <button
                                  type="button"
                                  class="delete-interest"
                                  aria-label="Remove this interest"
                                  style="background:none;border:none;cursor:pointer;margin-left:8px;"
                                >
                                    <img
                                      src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/bin.svg' ); ?>"
                                      alt="×"
                                      style="width:16px;height:16px;"
                                    />
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button
                      type="button"
                      class="button"
                      id="add-interest-edit"
                      style="margin-top:8px;"
                    >
                        + Add Another Interest
                    </button>
                </td>
            </tr>
            <tr style="width:10000%;"></tr>
            <!-- Biography -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit the member’s bio.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="biography_edit">Biography</label>
                </th>
                <td>
                    <textarea name="biography"
                              id="biography_edit"
                              rows="5"
                              class="large-text"
                              placeholder="Tell us about yourself…"><?php echo esc_textarea( $member['biography'] ); ?></textarea>
                </td>
            </tr>

            <!-- Admin Notes -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Edit confidential notes.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="notes_edit">Admin Notes</label>
                </th>
                <td>
                    <textarea name="notes"
                              id="notes_edit"
                              rows="4"
                              class="large-text"
                              placeholder="Confidential notes…"><?php echo esc_textarea( $member['notes'] ); ?></textarea>
                </td>
            </tr>

            <!-- Profile Image -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Change profile picture.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label>Profile Image</label>
                </th>
                <td>
                    <div class="tta-profile-image-wrapper">
                        <button type="button" class="button tta-member-upload-single" data-target="#profileimgid_edit">
                            Select Profile Image
                        </button>
                        <input type="hidden" id="profileimgid_edit" name="profileimgid" value="<?php echo esc_attr( $profileimgid ); ?>">
                        <div id="profileimage-preview_edit" style="margin-top:10px;">
                            <?php if ( $profileimgid ): ?>
                                <?php echo '<img src="' . esc_url( wp_get_attachment_image_url( $profileimgid, 'full' ) ) . '"/>' ?>
                            <?php else: ?>
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' ); ?>"
                                     alt="Placeholder Profile" style="max-width:150px;" />
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- Opt-In Preferences -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Toggle communications.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label>Opt-In Preferences</label>
                </th>
                <td>
                    <?php
                    $opt_marketing_email       = intval( $member['opt_in_marketing_email'] );
                    $opt_marketing_sms         = intval( $member['opt_in_marketing_sms'] );
                    $opt_event_update_email    = intval( $member['opt_in_event_update_email'] );
                    $opt_event_update_sms      = intval( $member['opt_in_event_update_sms'] );
                    ?>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="opt_in_marketing_email" value="1" <?php checked( $opt_marketing_email, 1 ); ?>>
                            <span class="tta-tooltip-icon" data-tooltip="Send promotional emails and newsletters.">
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                            </span>
                            <?php esc_html_e( 'Marketing Emails', 'tta' ); ?>
                        </label><br>

                        <label>
                            <input type="checkbox" name="opt_in_marketing_sms" value="1" <?php checked( $opt_marketing_sms, 1 ); ?>>
                            <span class="tta-tooltip-icon" data-tooltip="Send promotional text messages.">
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                            </span>
                            <?php esc_html_e( 'Marketing Texts/SMS', 'tta' ); ?>
                        </label><br>

                        <label>
                            <input type="checkbox" name="opt_in_event_update_email" value="1" <?php checked( $opt_event_update_email, 1 ); ?>>
                            <span class="tta-tooltip-icon" data-tooltip="Send event announcements via email.">
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                            </span>
                            <?php esc_html_e( 'Event Update Emails', 'tta' ); ?>
                        </label><br>

                        <label>
                            <input type="checkbox" name="opt_in_event_update_sms" value="1" <?php checked( $opt_event_update_sms, 1 ); ?>>
                            <span class="tta-tooltip-icon" data-tooltip="Send event announcements via text message.">
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                            </span>
                            <?php esc_html_e( 'Event Update Texts/SMS', 'tta' ); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>

            <!-- Privacy Options -->
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Control what information is shown publicly.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label>Privacy Options</label>
                </th>
                <td>
                    <fieldset>
                        <label>
                            <span class="tta-tooltip-icon" data-tooltip="Hide this member from public attendee lists.">
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                            </span>
                            <input type="checkbox" name="hide_event_attendance" value="1" <?php checked( $hide_attendance, 1 ); ?>>
                            Hide Event Attendance
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <th>
                    <span class="tta-tooltip-icon" data-tooltip="Prevent this member from making purchases.">
                        <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>" alt="Help" />
                    </span>
                    <label for="ban_status">Ban Status</label>
                </th>
                <td>
                    <?php
                    $ban_opt = 'none';
                    if ( $member['banned_until'] ) {
                        if ( TTA_BAN_UNTIL_REENTRY === $member['banned_until'] ) {
                            $ban_opt = 'reentry';
                        } elseif ( TTA_BAN_UNTIL_INDEFINITE === $member['banned_until'] ) {
                            $ban_opt = 'indefinite';
                        } else {
                            $diff = strtotime( $member['banned_until'] ) - time();
                            if ( $diff > 0 ) {
                                if ( $diff >= 2419200 ) {
                                    $ban_opt = '4week';
                                } elseif ( $diff >= 1814400 ) {
                                    $ban_opt = '3week';
                                } elseif ( $diff >= 1209600 ) {
                                    $ban_opt = '2week';
                                } elseif ( $diff >= 604800 ) {
                                    $ban_opt = '1week';
                                }
                            }
                        }
                    }
                    ?>
                    <select name="ban_status" id="ban_status">
                        <option value="none" <?php selected( $ban_opt, 'none' ); ?>>Not Banned</option>
                        <option value="indefinite" <?php selected( $ban_opt, 'indefinite' ); ?>>Banned Indefinitely</option>
                        <option value="reentry" <?php selected( $ban_opt, 'reentry' ); ?>>Banned Until Purchasing Re-Entry Product</option>
                        <option value="1week" <?php selected( $ban_opt, '1week' ); ?>>1-Week Ban</option>
                        <option value="2week" <?php selected( $ban_opt, '2week' ); ?>>2-Week Ban</option>
                        <option value="3week" <?php selected( $ban_opt, '3week' ); ?>>3-Week Ban</option>
                        <option value="4week" <?php selected( $ban_opt, '4week' ); ?>>4-Week Ban</option>
                    </select>
                </td>
            </tr>

        </tbody>
    </table>

    <p class="submit">
        <button type="submit" name="tta_member_update" class="button button-primary">
            Update Member
        </button>

        <!-- Spinner & Response (needed for JS) -->
        <div class="tta-admin-progress-spinner-div">
            <img
                class="tta-admin-progress-spinner-svg"
                src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>"
                alt="Loading…"
                style="display:none; opacity:0;"
            />
        </div>
        <div class="tta-admin-progress-response-div">
            <p class="tta-admin-progress-response-p"></p>
        </div>
    </p>
</form>
