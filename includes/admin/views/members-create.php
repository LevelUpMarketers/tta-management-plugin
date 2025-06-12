<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="tta-members-create">
    <form id="tta-member-form" method="post">
        <?php wp_nonce_field( 'tta_member_save_action', 'tta_member_save_nonce' ); ?>

        <table class="form-table">
            <tbody>
                <!-- First Name -->
                <tr>
                    <th>
                        <label for="first_name">First Name</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Enter the member’s first name."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="text" name="first_name" id="first_name" class="regular-text" required>
                    </td>
                </tr>

                <!-- Last Name -->
                <tr>
                    <th>
                        <label for="last_name">Last Name</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Enter the member’s last name."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="text" name="last_name" id="last_name" class="regular-text" required>
                    </td>
                </tr>

                <!-- Email Address -->
                <tr>
                    <th>
                        <label for="email">Email Address</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Enter a valid email address; a verification will follow."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="email" name="email" id="email" class="regular-text" required>
                    </td>
                </tr>

                <!-- Verify Email Address -->
                <tr>
                    <th>
                        <label for="email_verify">Verify Email Address</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Re-enter the same email address to confirm."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="email" name="email_verify" id="email_verify" class="regular-text" required>
                    </td>
                </tr>

                <!-- Street Address -->
                <tr>
                    <th>
                        <label for="street_address">Street Address</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Enter the primary street address."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="text" name="street_address" id="street_address" class="regular-text">
                    </td>
                </tr>

                <!-- Address 2 -->
                <tr>
                    <th>
                        <label for="address_2">Address 2</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Apt, Suite, PO Box, etc. (optional)."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="text" name="address_2" id="address_2" class="regular-text">
                    </td>
                </tr>

                <!-- City -->
                <tr>
                    <th>
                        <label for="city">City</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Enter the city name."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="text" name="city" id="city" class="regular-text">
                    </td>
                </tr>

                <!-- State -->
                <tr>
                    <th>
                        <label for="state">State</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Select the state."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <select name="state" id="state">
                            <?php
                            // You can customize this list if you already have a states schema.
                            $states = [
                                '' => '— Select State —',
                                'AL' => 'Alabama',
                                'AK' => 'Alaska',
                                'AZ' => 'Arizona',
                                // … (add all 50 states as needed) …
                                'VA' => 'Virginia',
                                'WV' => 'West Virginia',
                                'WI' => 'Wisconsin',
                                'WY' => 'Wyoming',
                            ];
                            foreach ( $states as $abbr => $label ) {
                                printf(
                                    '<option value="%s">%s</option>',
                                    esc_attr( $abbr ),
                                    esc_html( $label )
                                );
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <!-- ZIP -->
                <tr>
                    <th>
                        <label for="zip">ZIP</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Enter the 5-digit ZIP code."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="text" name="zip" id="zip" class="regular-text" maxlength="10">
                    </td>
                </tr>

                <!-- Phone Number -->
                <tr>
                    <th>
                        <label for="phone">Phone Number</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Enter a 10-digit phone number; it will format automatically."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="tel" name="phone" id="phone" class="regular-text" placeholder="e.g. (555) 123-4567">
                    </td>
                </tr>

                <!-- Date of Birth -->
                <tr>
                    <th>
                        <label for="dob">Date of Birth</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Enter the member’s birth date (YYYY-MM-DD)."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="date" name="dob" id="dob">
                    </td>
                </tr>

                <!-- Member Type -->
                <tr>
                    <th>
                        <label for="member_type">Member Type</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Select the user role for this member."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <select name="member_type" id="member_type">
                            <option value="member">Member</option>
                            <option value="volunteer">Volunteer</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </td>
                </tr>

                <!-- Membership Level -->
                <tr>
                    <th>
                        <label for="membership_level">Membership Level</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Choose the membership tier: Free, Basic, or Premium."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <select name="membership_level" id="membership_level">
                            <option value="free">Free</option>
                            <option value="basic">Basic</option>
                            <option value="premium">Premium</option>
                        </select>
                    </td>
                </tr>

                <!-- Facebook URL -->
                <tr>
                    <th>
                        <label for="facebook">Facebook URL</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Full URL to the member’s Facebook profile."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="url" name="facebook" id="facebook" class="regular-text" placeholder="https://facebook.com/username">
                    </td>
                </tr>

                <!-- LinkedIn URL -->
                <tr>
                    <th>
                        <label for="linkedin">LinkedIn URL</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Full URL to the member’s LinkedIn profile."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="url" name="linkedin" id="linkedin" class="regular-text" placeholder="https://linkedin.com/in/username">
                    </td>
                </tr>

                <!-- Instagram URL -->
                <tr>
                    <th>
                        <label for="instagram">Instagram URL</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Full URL to the member’s Instagram profile."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="url" name="instagram" id="instagram" class="regular-text" placeholder="https://instagram.com/username">
                    </td>
                </tr>

                <!-- X/Twitter URL -->
                <tr>
                    <th>
                        <label for="twitter">X/Twitter URL</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Full URL to the member’s Twitter (X) profile."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <input type="url" name="twitter" id="twitter" class="regular-text" placeholder="https://twitter.com/username">
                    </td>
                </tr>

                <!-- Interests -->
                <tr>
                    <th>
                        <label for="interests">Interests</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Add one or more interests; click “+ Add Another Interest” to add more."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <div id="interests-container">
                            <div id="interests-item" style="margin-bottom:8px; display:flex; align-items:center;">
                                <input type="text" name="interests[]" class="regular-text interest-field" placeholder="Interest #1">
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
                        </div>
                        <button type="button" class="button" id="add-interest" style="margin-top:8px;">
                            + Add Another Interest
                        </button>
                    </td>
                </tr>
                <tr style="width:10000%;"></tr>
                
                <!-- Biography -->
                <tr>
                    <th>
                        <label for="biography">Biography</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="A short introduction or bio for this member."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <textarea name="biography" id="biography" rows="5" class="large-text" placeholder="Tell us about yourself…"></textarea>
                    </td>
                </tr>

                <!-- Admin Notes -->
                <tr>
                    <th>
                        <label for="notes">Admin Notes</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Private notes about this member (not visible to the member)."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <textarea name="notes" id="notes" rows="4" class="large-text" placeholder="Confidential notes…"></textarea>
                    </td>
                </tr>

                <!-- Profile Image -->
                <tr>
                    <th>
                        <label>Profile Image</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Select or upload a profile picture for the member."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <div class="tta-profile-image-wrapper">
                            <button type="button" class="button tta-member-upload-single" data-target="#profileimgid">
                                Select Profile Image
                            </button>
                            <input type="hidden" id="profileimgid" name="profileimgid" value="">
                            <div id="profileimage-preview" style="margin-top:10px;">
                                <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/placeholder-profile.svg' ); ?>"
                                     alt="Placeholder Profile" style="max-width:150px;"/>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Opt-In Preferences -->
                <tr>
                    <th>
                        <label>Opt-In Preferences</label>
                        <span class="tta-tooltip-icon"
                              data-tooltip="Select which communications this member wants to receive."
                              style="margin-left:4px;">
                            <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                 alt="Help">
                        </span>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="opt_in_marketing_email" value="1">
                                Marketing Emails
                                <span class="tta-tooltip-icon"
                                      data-tooltip="Send promotional emails and newsletters."
                                      style="margin-left:4px;">
                                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                         alt="Help">
                                </span>
                            </label><br>
                            <label>
                                <input type="checkbox" name="opt_in_marketing_sms" value="1">
                                Marketing Texts/SMS
                                <span class="tta-tooltip-icon"
                                      data-tooltip="Send promotional text messages."
                                      style="margin-left:4px;">
                                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                         alt="Help">
                                </span>
                            </label><br>
                            <label>
                                <input type="checkbox" name="opt_in_event_update_email" value="1">
                                Event Update Emails
                                <span class="tta-tooltip-icon"
                                      data-tooltip="Send event announcements via email."
                                      style="margin-left:4px;">
                                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                         alt="Help">
                                </span>
                            </label><br>
                            <label>
                                <input type="checkbox" name="opt_in_event_update_sms" value="1">
                                Event Update Texts/SMS
                                <span class="tta-tooltip-icon"
                                      data-tooltip="Send event announcements via text message."
                                      style="margin-left:4px;">
                                    <img src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/question.svg' ); ?>"
                                         alt="Help">
                                </span>
                            </label>
                        </fieldset>
                    </td>
                </tr>

            </tbody>
        </table>

        <p class="submit" style="margin-top:20px;">
            <button type="submit" name="tta_member_save" class="button button-primary">
                Create Member
            </button>
            <!-- Spinner & Response -->
            <div class="tta-admin-progress-spinner-div">
                <img
                    class="tta-admin-progress-spinner-svg"
                    src="<?php echo esc_url( TTA_PLUGIN_URL . 'assets/images/admin/loading.svg' ); ?>"
                    alt="Loading…"
                />
            </div>
            <div class="tta-admin-progress-response-div">
                <p class="tta-admin-progress-response-p"></p>
            </div>
        </p>
    </form>
</div>
