<?php
/**
 * Template Name: Become a Member
 *
 * Displays membership options and benefits.
 *
 * @package TTA
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>
<div class="tta-become-member-wrap">
  <h1><?php esc_html_e( 'Become a Trying to Adult Member', 'tta' ); ?></h1>
  <p><?php esc_html_e( 'Join our community and unlock special perks at local events.', 'tta' ); ?></p>

  <table class="tta-membership-table">
    <thead>
      <tr>
        <th><?php esc_html_e( 'Benefits', 'tta' ); ?></th>
        <th><?php esc_html_e( 'Basic', 'tta' ); ?></th>
        <th><?php esc_html_e( 'Premium', 'tta' ); ?></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?php esc_html_e( 'Access to free events every month', 'tta' ); ?></td>
        <td class="check">&#10003;</td>
        <td class="check">&#10003;</td>
      </tr>
      <tr>
        <td><?php esc_html_e( 'Early access to new events', 'tta' ); ?></td>
        <td></td>
        <td class="check">&#10003;</td>
      </tr>
      <tr>
        <td><?php esc_html_e( 'Discounts on paid events', 'tta' ); ?></td>
        <td></td>
        <td class="check">&#10003;</td>
      </tr>
      <tr>
        <td><?php esc_html_e( 'Local discounts (coming soon)', 'tta' ); ?></td>
        <td></td>
        <td class="check">&#10003;</td>
      </tr>
      <tr class="tta-pricing-row">
        <td><strong><?php esc_html_e( 'Price per month', 'tta' ); ?></strong></td>
        <td>$5</td>
        <td>$10</td>
      </tr>
      <tr class="tta-membership-actions">
        <td></td>
        <td>
          <button type="button" id="tta-basic-signup" class="tta-button tta-button-primary">
            <?php esc_html_e( 'Sign Up', 'tta' ); ?>
          </button>
        </td>
        <td>
          <button type="button" id="tta-premium-signup" class="tta-button tta-button-primary">
            <?php esc_html_e( 'Sign Up', 'tta' ); ?>
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
<?php
get_footer();
