<?php
/**
 * Meta Factory
 *
 * @version 1.1
 * @copyright WPExplorer.com 2020 - All Rights Reserved
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPEX_Meta_Factory' ) ) {

	class WPEX_Meta_Factory {

		/**
		 * Version.
		 *
		 * @var   array
		 * @since 1.0
		 */
		public $version = '1.1';

		/**
		 * Default metabox settings.
		 *
		 * @var   array
		 * @since 1.0
		 */
		protected $defaults = array(
			'id'       => '',
			'title'    => '',
			'screen'   => array( 'post' ),
			'context'  => 'normal',
			'priority' => 'default',
			'classes'  => array(),
			'fields'   => array(),
		);

		/**
		 * Array of custom metabox settings.
		 *
		 * @var   array
		 * @since 1.0
		 */
		protected $metabox = array();

		/**
		 * Field prefix.
		 *
		 * @var   string
		 * @since 1.0
		 */
		protected $prefix = 'wpex_mf_';

		/**
		 * Register this class with the WordPress API.
		 *
		 * @since 1.0
		 *
		 * @access public
		 * @param array $metabox Array of metabox settings|fields.
		 * @return void
		 */
		public function __construct( $metabox ) {

			// Parse metabox args
			$this->metabox = wp_parse_args( $metabox, $this->defaults );

			// Fields are required
			if ( empty( $this->metabox[ 'fields' ] ) ) {
				return;
			}

			// Add metaboxes
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

			// Save meta
			add_action( 'save_post', array( $this, 'save_meta_data' ) );

		}

		/**
		 * The function responsible for creating the actual meta boxes.
		 *
		 * @since 1.0
		 *
		 * @access public
		 * @return void
		 */
		public function add_meta_box() {

			add_meta_box(
				'wpex-mf-metabox--' . esc_attr( $this->metabox[ 'id' ] ),
				$this->metabox[ 'title' ],
				array( $this, 'display_meta_box' ),
				$this->metabox[ 'screen' ],
				$this->metabox[ 'context' ],
				$this->metabox[ 'priority' ]
			);

			if ( ! empty( $this->metabox[ 'classes' ] ) && is_array( $this->metabox[ 'screen' ] ) ) {
				foreach( $this->metabox[ 'screen' ] as $screen ) {
					add_filter( 'postbox_classes_' . $screen . '_' . $this->metabox[ 'id' ], array( $this, 'postbox_classes' ) );
				}
			}

		}

		/**
		 * Add custom classes to the metabox.
		 *
		 * @since 1.0
		 * @var $classes array
		 * @access public
		 * @return $classes
		 */
		public function postbox_classes( $classes ) {
			if ( is_array( $this->metabox[ 'classes' ] ) ) {
				foreach( $this->metabox[ 'classes' ] as $class ) {
					array_push( $classes, $class );
				}
			}
			return $classes;
		}

		/**
		 * Enqueue scripts and styles needed for the metaboxes.
		 *
		 * @since 1.0
		 *
		 * @access public
		 * @return void
		 */
		public function load_scripts() {

			$this->enqueue_metabox_scripts();

			$this->enqueue_metabox_styles();

		}

		/**
		 * Renders the content of the meta box.
		 *
		 * @since 1.0
		 *
		 * @access public
		 * @param obj $post Current post being shown in the admin.
		 * @return void
		 */
		public function display_meta_box( $post ) {

			// Add an nonce field so we can check for it later.
			wp_nonce_field(
				'wpex_metabox_factory_' . $this->metabox[ 'id' ],
				'wpex_meta_factory_nonce_' . $this->metabox[ 'id' ]
			);

			// Load metabox scripts
			$this->load_scripts();

			// Get metabox fields
			$fields = $this->metabox[ 'fields' ];

			?>

			<div class="wpex-mf-metabox">

				<table class="form-table">

					<?php
					// Loop through sections and store meta output
					foreach ( $fields as $key => $field ) {

						// Defaults
						$defaults = array(
							'name'    => '',
							'id'      => '',
							'type'    => '',
							'desc'    => '',
							'default' => '',
						);

						// Parse and extract
						$field = wp_parse_args( $field, $defaults );

						// Notice field
						if ( isset( $field[ 'type' ] ) && 'notice' == $field[ 'type' ] ) { ?>

							<tr><?php echo wp_kses_post( $field[ 'content' ] ); ?></tr>

						<?php }

						// Render singular field
						else {

							$custom_field_keys = get_post_custom_keys();

							if ( is_array( $custom_field_keys ) && in_array( $field[ 'id' ], $custom_field_keys ) ) {
								$value = get_post_meta( $post->ID, $field[ 'id' ], true );
							} else {
								$value = isset( $field[ 'default' ] ) ? $field[ 'default' ] : '';
							}

							$this->render_field( $field, $value );

						}

					} // end foreach ?>

				</table>

			</div>

		<?php }

		/**
		 * Renders a field.
		 *
		 * @since 1.0
		 */
		public function render_field( $field, $value = '' ) {

			$tr_id = $field[ 'id' ];
			if ( isset( $field['index'] ) ) {
				$tr_id = str_replace( '][', '-', $tr_id );
				$tr_id = str_replace( ']', '', $tr_id );
				$tr_id = str_replace( '[', '-', $tr_id );
			}
			$tr_id = 'wpex-mf-tr--' . esc_attr( $tr_id );

			?>

			<tr id="<?php echo esc_attr( $tr_id ); ?>">

				<?php if ( $field[ 'name' ] ) { ?>

					<th>

						<?php switch ( $field[ 'type'] ) {

							case 'multi_select':
							case 'group': ?>

								<span class="wpex-mf-label"><?php echo esc_html( $field[ 'name' ] ); ?></span>

								<?php break;

							default: ?>

								<label class="wpex-mf-label" for="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>"><?php echo esc_html( $field[ 'name' ] ); ?></label>

								<?php
								break;

						} ?>

						<?php if ( ! empty( $field[ 'desc' ] ) ) { ?>
							<p class="wpex-mf-description"><?php echo wp_kses_post( $field[ 'desc' ] ); ?></p>
						<?php } ?>

					</th>

				<?php } ?>

				<?php
				// Output field type
				$method = 'field_' . $field[ 'type' ];

				if ( method_exists( $this, $method ) ) {

					$td_colspan = empty( $field[ 'name' ] ) ? '2' : '';

					?>

						<td colspan="<?php echo esc_attr( $td_colspan ); ?>">

							<?php $this->$method( $field, $value ); ?>

							<?php if ( ! empty( $field[ 'after_hook' ] ) ) {
								echo '<div class="wpex-mf-after-hook">' . wp_kses_post( $field[ 'after_hook' ] ) . '</div>';
							} ?>

						</td>

					<?php

				}

				?>

			</tr>

		<?php

		}

		/**
		 * Render a group field type.
		 *
		 * @since 1.0
		 */
		public function field_group( $field, $value ) {

			if ( empty( $field['fields'] ) ) {
				return;
			}

			?>

			<div class="wpex-mf-group-set">

				<?php
				if ( empty( $value ) ) {
					$this->field_group_set( $field, $value, 0 );
				} elseif ( is_array( $value ) ) {

					$groups = $value;
					$groups_count = count( $groups );

					$index = 0;
					foreach( $groups as $group_k => $group_v ) {
						$this->field_group_set( $field, $value, $index );
						$index++;
					}

				} ?>

			</div>

			<?php
			// Get group button text
			$group_button = isset( $field['group_button'] ) ? $field['group_button'] : esc_html__( 'Add New', 'total-theme-core' ); ;?>

			<button type="button" class="wpex-mf-clone-group button-primary">&#65291; <?php esc_html_e( $group_button ); ?></button>

			<?php

		}

		/**
		 * Render singular field group
		 *
		 * @since 1.0
		 */
		public function field_group_set( $field, $value, $index ) {

			$index_escaped = absint( $index );
			$group_title   = isset( $field['group_title'] ) ? $field['group_title'] : esc_html__( 'Entry', 'total-theme-core' );

			?>

				<div class="wpex-mf-group">

					<div class="wpex-mf-group-header">
						<div>
							<?php esc_html_e( $group_title ); ?>
							<span class="wpex-mf-group-set-index"><?php echo absint( $index_escaped + 1 ); ?></span>
						</div>
						<div class="wpex-mf-group-header-actions">
							<button type="button" class="dashicons-before dashicons-trash wpex-mf-remove-group"><span class="screen-reader-text"><?php esc_html_e( 'Remove Group Set', 'total-theme-core' ); ?></span></button>
						</div>
					</div>

					<div class="wpex-mf-group-fields">
						<table>
							<?php
							foreach( $field['fields'] as $field_k => $field_v ) {

								// Get group single field value from index
								$field_value = isset( $value[ $index ][ $field_v[ 'id' ] ] ) ? $value[ $index ][ $field_v[ 'id' ] ] : '';

								// Group fields need to be setup as arrays
								$field_v[ 'id' ] = $field[ 'id' ] . '[' . $index_escaped . '][' . $field_v[ 'id' ] . ']';

								// Important, it lets everyone know it's a group item.
								$field_v[ 'index'] = $index_escaped;

								// Render singular group item field
								$this->render_field( $field_v, $field_value );

							} ?>
						</table>

					</div>

				</div>

			<?php
		}

		/**
		 * Render a text field type.
		 *
		 * @since 1.0
		 */
		public function field_text( $field, $value ) {

			$required    = isset( $field[ 'required' ] ) ? ' required' : '';
			$maxlength   = isset( $field[ 'maxlength' ] ) ? ' maxlength="' . floatval( $field[ 'maxlength' ] ) . '"' : '';
			$placeholder = ! empty( $field[ 'placeholder' ] ) ? ' placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"' : '';

			?>

				<input id="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" <?php echo $required . $maxlength . $placeholder; ?>>

			<?php

		}

		/**
		 * Render a URL field type.
		 *
		 * @since 1.0
		 */
		public function field_url( $field, $value ) {

			$required    = isset( $field[ 'required' ] ) ? ' required' : '';
			$maxlength   = isset( $field[ 'maxlength' ] ) ? ' maxlength="' . floatval( $field[ 'maxlength' ] ) . '"' : '';
			$placeholder = ! empty( $field[ 'placeholder' ] ) ? ' placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"' : '';

			?>

				<input id="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>" type="url" value="<?php echo esc_attr( $value ); ?>" <?php echo $required . $maxlength . $placeholder; ?>>

			<?php

		}

		/**
		 * Render a number field type.
		 *
		 * @since 1.0
		 */
		public function field_number( $field, $value ) {

			$step = isset( $field[ 'step' ] ) ? $field[ 'step' ] : 1;
			$min  = isset( $field[ 'min' ] ) ? $field[ 'min' ] : 1;
			$max  = isset( $field[ 'max' ] ) ? $field[ 'max' ] : 200;

			?>

				<input id="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>" type="number" value="<?php echo esc_attr( $value ); ?>" step="<?php echo absint( $step ); ?>" min="<?php echo floatval( $min ); ?>" max="<?php echo floatval( $max ); ?>">';

			<?php

		}

		/**
		 * Render a textare field type.
		 *
		 * @since 1.0
		 */
		public function field_textarea( $field, $value ) {

			$rows = isset( $field[ 'rows' ] ) ? $field[ 'rows' ] : 4;

			?>

			<textarea id="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>" rows="<?php echo absint( $rows ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>"><?php echo wp_kses_post( $value ); ?></textarea>

			<?php

		}

		/**
		 * Render a checkbox field type.
		 *
		 * @since 1.0
		 */
		public function field_checkbox( $field, $value ) {

			$value = $value ? true : false;

			?>

				<input id="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>" type="checkbox" <?php checked( $value, true, true ); ?>>

			<?php

		}

		/**
		 * Render a select field type.
		 *
		 * @since 1.0
		 */
		public function field_select( $field, $value ) {

			$choices = isset( $field[ 'choices' ] ) ? $field[ 'choices' ] : array();
			$autocomplete = ! empty( $field[ 'autocomplete' ] ) ? $field[ 'autocomplete' ] : array(); // @todo

			if ( empty( $choices ) ) {
				return;
			}

			?>

			<select id="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>">

				<?php foreach ( $choices as $choice_v => $name ) { ?>

					<option value="<?php echo esc_attr( $choice_v ); ?>" <?php selected( $value, $choice_v, true ); ?>><?php echo esc_html( $name ); ?></option>

				<?php } ?>

			</select>

			<?php

		}

		/**
		 * Render an icon select field type.
		 *
		 * @since 1.0
		 */
		public function field_icon_select( $field, $value ) {

			$choices = isset( $field[ 'choices' ] ) ? $field[ 'choices' ] : array();
			$autocomplete = ! empty( $field[ 'autocomplete' ] ) ? $field[ 'autocomplete' ] : array(); // @todo

			if ( empty( $choices ) ) {
				return;
			}

			?>

			<div class="wpex-mf-icon-select-wrap">

				<input type="text" id="<?php echo esc_attr( $this->sanitize_field_attr_id( $field[ 'id' ] ) ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>" value="<?php echo esc_attr( $value ); ?>">

				<button class="button-secondary" type="button"><?php esc_html_e( 'Select Icon', 'total-theme-core' ); ?></button>

				<div class="wpex-mf-icon-select-preview"><span class="<?php echo esc_attr( $value ); ?>" aria-hidden="true"></span></div>

				<div class="wpex-mf-icon-select-modal" style="display:none;">
					<div class="wpex-mf-icon-select-modal-inner">
						<span class="screen-reader-text"><label for="wpex-mf-icon-select-search-<?php echo esc_attr( $field[ 'id' ] ); ?>"><?php esc_html_e( 'Search for an icon', 'total-theme-core' ); ?></label></span>
						<input class="wpex-mf-icon-select-search" id="wpex-mf-icon-select-search-<?php echo esc_attr( $field[ 'id' ] ); ?>" type="search" placeholder="<?php esc_html_e( 'Search for an icon', 'total-theme-core' ); ?>&hellip;">
						<div class="wpex-mf-icon-select-modal-choices">
							<?php foreach ( $choices as $choice_v => $name ) {
								$a_title = empty( $choice_v ) ? esc_html__( 'None', 'total-theme-core' ) : $choice_v;
								?>
								<a href="#" title="<?php echo esc_html( $a_title ); ?>" data-value="<?php echo esc_attr( $choice_v ); ?>"><span class="<?php echo esc_html( $choice_v ); ?>"></span></a>
							<?php } ?>
						</div>
						<button class="button-primary wpex-mf-close"><?php esc_html_e( 'Close', 'total-theme-core' ); ?></button>
					</div>
				</div>

			</div>

			<?php

		}

		/**
		 * Render a multi_select field type.
		 *
		 * @since 1.0
		 */
		public function field_multi_select( $field, $value ) {

			$value   = is_array( $value ) ? $value : array();
			$choices = isset( $field[ 'choices' ] ) ? $field[ 'choices' ] : array();

			if ( empty( $choices ) ) {
				return;
			}

			?>

				<fieldset>

					<?php foreach ( $choices as $choice_v => $name ) {

						$field_id = $field[ 'id' ] . '_' . $choice_v;

						?>

						<input id="<?php echo $this->sanitize_field_attr_id( $field_id ); ?>" type="checkbox" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>[]" value="<?php echo esc_attr( $choice_v ); ?>" <?php checked( in_array( $choice_v, $value ), true, true ); ?>>

						<label for="<?php echo $this->sanitize_field_attr_id( $field_id ); ?>"><?php echo esc_html( $name ); ?></label>

						<br />

					<?php } ?>

				</fieldset>

			<?php

		}

		/**
		 * Render an upload field type.
		 *
		 * @since 1.0
		 */
		public function field_upload( $field, $value ) {

			wp_enqueue_media();

			$required    = isset( $field[ 'required' ] ) ? ' required' : '';
			$placeholder = ! empty( $field[ 'placeholder' ] ) ? ' placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"' : '';
			$return      = ! empty( $field['return'] ) ? $field['return'] : 'url';

			// ID based upload (displays preview)
			if ( 'id' == $return ) {

				?>

				<input id="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" data-selection="<?php echo esc_attr( $return ); ?>" <?php echo $required . $placeholder; ?>>

				<button class="wpex-mf-upload button-secondary" type="button"><?php esc_html_e( 'Upload', 'total-theme-core' ); ?></button>

				<div class="wpex-mf-upload-preview">
					<?php if ( $value ) {
						echo wp_get_attachment_image( $value, array( 50, 9999 ) );
					} ?>
				</div>

				<?php

			}

			// Standard upload
			else { ?>

				<input id="<?php echo $this->sanitize_field_attr_id( $field[ 'id' ] ); ?>" name="<?php echo esc_attr( $this->add_prefix( $field[ 'id' ] ) ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" data-selection="<?php echo esc_attr( $return ); ?>" <?php echo $required . $placeholder; ?>>

				<button class="wpex-mf-upload button-secondary" type="button"><?php esc_html_e( 'Upload', 'total-theme-core' ); ?></button>

			<?php

			}

		}

		/**
		 * Save metabox data.
		 *
		 * @since 1.0
		 */
		public function save_meta_data( $post_id ) {

			/*
			 * We need to verify this came from our screen and with proper authorization,
			 * because the save_post action can be triggered at other times.
			 */

			// Check if our nonce is set.
			if ( ! isset( $_POST[ 'wpex_meta_factory_nonce_' . $this->metabox[ 'id' ] ] ) ) {
				return;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce(
				$_POST[ 'wpex_meta_factory_nonce_' . $this->metabox[ 'id' ] ],
				'wpex_metabox_factory_' . $this->metabox[ 'id' ] )
			) {
				return;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( isset( $_POST[ 'post_type' ] ) && 'page' == $_POST[ 'post_type' ] ) {

				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return;
				}

			} else {

				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}

			}

			/* OK, it's safe for us to save the data now. Now we can loop through fields */

			// Get array of fields to save
			$fields = $this->metabox[ 'fields' ];

			// Return if fields are empty
			if ( empty( $fields ) ) {
				return;
			}

			// Loop through options and validate
			foreach ( $fields as $field ) {

				if ( isset( $field[ 'dont_save' ] ) || 'notice' == $field[ 'type' ] ) {
					continue;
				}

				$value    = '';
				$field_id = $field[ 'id' ];
				$prefixed_field_id = $this->prefix . $field_id;
				$new_value = isset( $_POST[$prefixed_field_id] ) ? $_POST[$prefixed_field_id] : '';

				if ( $field['type'] === 'group' ) {

					$group_fields = $field['fields'];

					if ( ! empty( $new_value ) && is_array( $new_value ) ) {

						// Loop through each group
						foreach ( $new_value as $new_value_k => $new_value_v ) {

							if ( empty( $new_value_v ) || ! is_array( $new_value_v ) ) {
								continue;
							}

							// Loop through each item in each group to sanitize the data
							foreach( $new_value_v as $new_group_value_k => $new_group_value_v ) {

								$new_value_field = array();

								foreach ( $group_fields as $group_field ) {
									if ( $new_group_value_k === $group_field['id'] ) {
										$new_value_field = $group_field;
										break;
									}
								}

								$new_group_value_v_escaped = $this->sanitize_value_for_db( $new_group_value_v, $new_value_field );

								$new_value[$new_value_k][$new_group_value_k] = $new_group_value_v_escaped;

							}

						}

						update_post_meta( $post_id, $field_id, $new_value );

					} else {
						delete_post_meta( $post_id, $field_id );
					}

				} else {

					// Make sure field exists and if so validate the data
					if ( $new_value ) {

						// Sanitize field before inserting into the database
						$new_val_escaped = $this->sanitize_value_for_db( $new_value, $field );

						// Update meta if value exists
						if ( $new_val_escaped ) {
							update_post_meta( $post_id, $field_id, $new_val_escaped );
						}

						// Delete if value is empty
						else {
							delete_post_meta( $post_id, $field_id );
						}

					} else {

						if ( 'checkbox' == $field[ 'type' ] && ! empty( $field[ 'default'] ) ) {
							update_post_meta( $post_id, $field_id, 0 );
						} else {
							delete_post_meta( $post_id, $field_id );
						}

					}

				}

			}

		}

		/**
		 * Sanitizes element ID.
		 *
		 * @since 1.0
		 */
		public function sanitize_field_attr_id( $id = '' ) {
			$id = str_replace( '][', '-', $id );
			$id = str_replace( '[', '-', $id );
			$id = str_replace( ']', '', $id );
			return 'wpex-mf-field--' . esc_attr( $id );

		}

		/**
		 * Sanitize input values before inserting into the database.
		 *
		 * @since 1.0
		 */
		public function sanitize_value_for_db( $input, $field ) {

			$type = $field[ 'type' ];

			switch ( $type ) {

				case 'text':
					return sanitize_text_field( $input );
					break;
				case 'url':
					return esc_url_raw( $input );
					break;
				case 'number':
					return absint( $input );
					break;
				case 'textarea':
					return sanitize_textarea_field( $input );
					break;
				case 'checkbox':
					return isset( $input ) ? 1 : 0;
					break;
				case 'select':
					if ( in_array( $input, $field[ 'choices' ] ) || array_key_exists( $input, $field[ 'choices' ] ) ) {
						return esc_attr( $input );
					}
					break;
				case 'multi_select':
					if ( ! is_array( $input ) ) {
						return isset( $field[ 'default' ] ) ? $field[ 'default' ] : array();
					}
					$checks = true;
					foreach( $input as $v ) {
						if ( ! in_array( $v, $field[ 'choices' ] ) && ! array_key_exists( $v, $field[ 'choices' ] ) ) {
							$checks = false;
							break;
						}
					}
					return $checks ? $input : array();
					break;
				case 'upload':
					$return = ! empty( $field['return'] ) ? $field['return'] : 'url';
					switch ( $return ) {
						case 'url':
							return esc_url_raw( $input );
							break;
						case 'id':
							return absint( $input );
							break;
						default:
							return sanitize_textarea_field( $input );
							break;
					}
					break;
				default:
					return wp_strip_all_tags( $input );
					break;

			}

		}

		/**
		 * Enqueues metabox scripts.
		 *
		 * @since 1.0
		 */
		public function enqueue_metabox_scripts() {

			wp_enqueue_script(
				'wpex-meta-factory',
				plugin_dir_url( __FILE__ ) . 'assets/wpex-meta-factory.min.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			wp_localize_script(
				'wpex-meta-factory',
				'wpexMetaFactoryL10n',
				array(
					'delete_group_confirm' => esc_html__( 'Please click ok to confirm.', 'total-theme-core' ),
				)
			);

			if ( isset( $this->metabox[ 'scripts' ] ) ) {

				foreach ( $this->metabox[ 'scripts' ] as $args ) {

					call_user_func_array( 'wp_enqueue_script', $args );

				}

			}

		}

		/**
		 * Enqueues metabox styles.
		 *
		 * @since 1.0
		 */
		public function enqueue_metabox_styles() {

			wp_enqueue_style(
				'wpex-meta-factory',
				plugin_dir_url( __FILE__ ) . 'assets/wpex-meta-factory.css',
				array(),
				$this->version
			);

			if ( isset( $this->metabox[ 'styles' ] ) ) {
				foreach ( $this->metabox[ 'styles' ] as $args ) {
					call_user_func_array( 'wp_enqueue_style', $args );
				}
			}
		}

		/**
		 * Adds prefix to string.
		 *
		 * @since 1.0
		 */
		public function add_prefix( $string = '' ) {
			return $this->prefix . $string;
		}

	}

}