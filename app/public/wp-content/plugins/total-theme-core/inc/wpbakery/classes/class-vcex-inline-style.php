<?php
/**
 * Parses inline styles
 *
 * @package Total WordPress Theme
 * @subpackage Framework
 * @version 1.2.4
 */

defined( 'ABSPATH' ) || exit;

class VCEX_Inline_Style {
	private $style;
	private $add_style;

	/**
	 * Class Constructor.
	 */
	public function __construct( $atts, $add_style ) {
		$this->style = array();
		$this->add_style = $add_style;

		// Loop through shortcode atts and run class methods
		foreach ( $atts as $key => $value ) {
			if ( ! empty( $value ) ) {
				$method = 'parse_' . $key;
				if ( method_exists( $this, $method ) ) {
					$this->$method( $value );
				}
			}
		}

	}

	/**
	 * Display.
	 */
	private function parse_display( $value ) {
		$this->style[] = 'display:' . esc_attr( $value ) . ';';
	}

	/**
	 * Float.
	 */
	private function parse_float( $value ) {
		if ( 'center' == $value ) {
			$this->style[] = 'margin-right:auto;margin-left:auto;float:none;';
		} else {
			$this->style[] = 'float:' . esc_attr( $value ) . ';';
		}
	}

	/**
	 * Width.
	 */
	private function parse_width( $value ) {
		$this->style[] = 'width:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Max-Width.
	 */
	private function parse_max_width( $value ) {
		$this->style[] = 'max-width:' . $this->sanitize_font_size( $value )  . ';';
	}

	/**
	 * Min-Width.
	 */
	private function parse_min_width( $value ) {
		$this->style[] = 'min-width:' . $this->sanitize_font_size( $value )  . ';';
	}

	/**
	 * Background.
	 */
	private function parse_background( $value ) {
		$this->style[] = 'background:' . esc_attr( $value ) . ';';
	}

	/**
	 * Background Image.
	 */
	private function parse_background_image( $value ) {
		$this->style[] = 'background-image:url(' . esc_attr( esc_url( $value ) ) . ');';
	}

	/**
	 * Background Position.
	 */
	private function parse_background_position( $value ) {
		$this->style[] = 'background-position:' . esc_attr( $value ) . ';';
	}

	/**
	 * Background Color.
	 */
	private function parse_background_color( $value ) {
		$value = 'none' == $value ? 'transparent' : $value;
		$this->style[] = 'background-color:' . esc_attr( $value ) . ';';
	}

	/**
	 * Border.
	 */
	private function parse_border( $value ) {
		$value = 'none' == $value ? '0' : $value;
		$this->style[] = 'border:' . esc_attr( $value ) . ';';
	}

	/**
	 * Border: Color.
	 */
	private function parse_border_color( $value ) {
		$value = 'none' == $value ? 'transparent' : $value;
		$this->style[] = 'border-color:' . esc_attr( $value ) . ';';
	}

	/**
	 * Border: Bottom Color.
	 */
	private function parse_border_bottom_color( $value ) {
		$value = 'none' == $value ? 'transparent' : $value;
		$this->style[] = 'border-bottom-color:' . esc_attr( $value ) . ';';
	}

	/**
	 * Border Width.
	 */
	private function parse_border_width( $value ) {
		$this->style[] = 'border-width:' . esc_attr( $value ) . ';';
	}

	/**
	 * Border Style.
	 */
	private function parse_border_style( $value ) {
		$this->style[] = 'border-style:' . esc_attr( $value ) . ';';
	}

	/**
	 * Border: Top Width.
	 */
	private function parse_border_top_width( $value ) {
		$this->style[] = 'border-top-width:' . $this->sanitize_px( $value )  . ';';
	}

	/**
	 * Border: Bottom Width.
	 */
	private function parse_border_bottom_width( $value ) {
		$this->style[] = 'border-bottom-width:' . $this->sanitize_px( $value )  . ';';
	}

	/**
	 * Margin.
	 */
	private function parse_margin( $value ) {

		if ( $this->parse_trbl_property( $value, 'margin' ) ) {
			return;
		}

		$value          = ( 'none' == $value ) ? '0' : $value;
		$value          = is_numeric( $value ) ? $value  . 'px' : $value;
		$this->style[]  = 'margin:' . esc_attr( $value ) . ';';

	}

	/**
	 * Margin: Right.
	 */
	private function parse_margin_right( $value ) {
		$this->style[] = 'margin-right:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Margin: Left.
	 */
	private function parse_margin_left( $value ) {
		$this->style[] = 'margin-left:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Margin: Top.
	 */
	private function parse_margin_top( $value ) {
		$this->style[] = 'margin-top:' . $this->sanitize_px( $value )  . ';';
	}

	/**
	 * Margin: Bottom.
	 */
	private function parse_margin_bottom( $value ) {
		$this->style[] = 'margin-bottom:' . $this->sanitize_px( $value )  . ';';
	}

	/**
	 * Padding.
	 */
	private function parse_padding( $value ) {

		if ( $this->parse_trbl_property( $value, 'padding' ) ) {
			return;
		}

		$value = 'none' == $value ? '0' : $value;
		$value = is_numeric( $value ) ? $value  . 'px' : $value;
		$this->style[] = 'padding:' . esc_attr( $value ) . ';';

	}

	/**
	 * Padding: Top.
	 */
	private function parse_padding_top( $value ) {
		$this->style[] = 'padding-top:' . $this->sanitize_px( $value )  . ';';
	}

	/**
	 * Padding: Bottom.
	 */
	private function parse_padding_bottom( $value ) {
		$this->style[] = 'padding-bottom:' .  $this->sanitize_px( $value )  . ';';
	}

	/**
	 * Padding: Left.
	 */
	private function parse_padding_left( $value ) {
		$this->style[] = 'padding-left:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Padding: Right.
	 */
	private function parse_padding_right( $value ) {
		$this->style[] = 'padding-right:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Font-Size.
	 */
	private function parse_font_size( $value ) {
		if ( $value && strpos( $value, '|' ) === false ) {
			if ( $value = $this->sanitize_font_size( $value ) ) {
				$this->style[] = 'font-size:' . esc_attr( $value )  . ';';
			}
		}
	}

	/**
	 * Font Weight.
	 */
	private function parse_font_weight( $value ) {
		switch ( $value ) {
			case 'normal':
				$value = '400';
				break;
			case 'medium' :
				$value = '500';
				break;
			case 'semibold':
				$value = '600';
				break;
			case 'bold':
				$value = '700';
				break;
			case 'bolder':
				$value = '900';
				break;
		}
		$this->style[] = 'font-weight:' . esc_attr( $value )  . ';';
	}

	/**
	 * Font Family (exclusive to Total theme)
	 */
	private function parse_font_family( $value ) {
		if ( function_exists( 'wpex_sanitize_font_family' ) ) {
			$value = wpex_sanitize_font_family( $value );
			if ( ! empty( $value ) ) {
				$value = str_replace( '"', "'", $value );
				$this->style[] = 'font-family:' . esc_attr( $value ) . ';';
			}
		}
	}

	/**
	 * Color.
	 */
	private function parse_color( $value ) {
		$this->style[] = 'color:' . esc_attr( $value )  . ';';
	}

	/**
	 * Opacity.
	 */
	private function parse_opacity( $value ) {
		$value = str_replace( '%', '', $value ); // allow % to be added.
		if ( ! is_numeric( $value ) ) {
			return;
		}
		if ( $value > 1 ) {
			$value = $value / 100;
		}
		if ( $value <= 1 ) {
			$this->style[] = 'opacity:' . esc_attr( $value ) . ';';
		}
	}

	/**
	 * Text Align.
	 */
	private function parse_text_align( $value ) {
		switch ( $value ) {
			case 'textcenter':
				$value = 'center';
				break;
			case 'textleft':
				$value = 'left';
				break;
			case 'textright':
				$value = 'right';
				break;
		}
		if ( $value ) {
			$this->style[] = 'text-align:' . esc_attr( $value ) . ';';
		}
	}

	/**
	 * Text Transform.
	 */
	private function parse_text_transform( $value ) {
		$allowed_values = array(
			'none',
			'capitalize',
			'uppercase',
			'lowercase',
			'initial',
			'inherit'
		);
		if ( ! in_array( $value, $allowed_values ) ) {
			return;
		}
		$this->style[] = 'text-transform:' .  esc_attr( $value ) . ';';
	}

	/**
	 * Letter Spacing.
	 */
	private function parse_letter_spacing( $value ) {
		if ( strpos( $value, 'px' ) || strpos( $value, 'em' ) || strpos( $value, 'vmin' ) || strpos( $value, 'vmax' ) ) {
			// do nothing
		} else {
			$value = absint( $value ) . 'px';
		}
		$this->style[] = 'letter-spacing:' . esc_attr( $value ) . ';';
	}

	/**
	 * Line-Height.
	 */
	private function parse_line_height( $value ) {
		$this->style[] = 'line-height:' . esc_attr( $value ) . ';';
	}

	/**
	 * Line-Height with px sanitize.
	 */
	private function parse_line_height_px( $value ) {
		$this->style[] = 'line-height:' . $this->sanitize_px( $value )  . ';';
	}

	/**
	 * Height.
	 */
	private function parse_height( $value ) {
		$this->style[] = 'height:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Height with px sanitize.
	 */
	private function parse_height_px( $value ) {
		$this->style[] = 'height:' . $this->sanitize_px( $value )  . ';';
	}

	/**
	 * Min-Height.
	 */
	private function parse_min_height( $value ) {
		$this->style[] = 'min-height:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Border Radius.
	 */
	private function parse_border_radius( $value ) {

		if ( 'none' == $value ) {
			$value = '0';
		} elseif ( strpos( $value, 'px' ) ) {
			$value = $value;
		} elseif ( strpos( $value, '%' ) ) {
			if ( '50%' == $value ) {
				$value = $value;
			} else {
				$value = str_replace( '%', 'px', $value );
			}
		} else {
			$value = intval( $value ) .'px';
		}

		$this->style[] = 'border-radius:' . esc_attr( $value )  . ';';
	}

	/**
	 * Position: Top.
	 */
	private function parse_top( $value ) {
		$this->style[] = 'top:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Position: Bottom.
	 */
	private function parse_bottom( $value ) {
		$this->style[] = 'bottom:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Position: Right.
	 */
	private function parse_right( $value ) {
		$this->style[] = 'right:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Position: Left.
	 */
	private function parse_left( $value ) {
		$this->style[] = 'left:' . $this->sanitize_px_pct( $value ) . ';';
	}

	/**
	 * Style.
	 */
	private function parse_font_style( $value ) {
		$this->style[] = 'font-style:' . esc_attr( $value )  . ';';
	}

	/**
	 * Text Decoration.
	 */
	private function parse_text_decoration( $value ) {
		$this->style[] = 'text-decoration:' . esc_attr( $value )  . ';';
	}

	/**
	 * Italic.
	 */
	private function parse_italic( $value ) {
		if ( 'true' ===  $value || 'yes' === $value || true === $value ) {
			$this->style[] = 'font-style:italic;';
		}
	}

	/**
	 * Animation delay.
	 */
	private function parse_animation_delay( $value ) {
		$this->style[] = 'animation-delay:' . esc_attr( floatval( $value ) ) . 's;';
	}

	/**
	 * Transition Speed.
	 */
	private function parse_transition_speed( $value ) {
		$this->style[] = 'transition-duration:' . esc_attr( floatval( $value ) ) . 's;';
	}

	/**
	 * Parse top/right/bottom/left fields.
	 */
	private function parse_trbl_property( $value, $property ) {

		if ( ! function_exists( 'vcex_parse_multi_attribute' ) ) {
			return;
		}

		if ( false !== strpos( $value, ':' ) && $values = vcex_parse_multi_attribute( $value ) ) {

			// All values are the same
			if ( isset( $values['top'] )
				&& count( $values ) == 4
				&& count( array_unique( $values ) ) <= 1
			) {
				$value          = $values['top'];
				$value          = ( 'none' == $value ) ? '0' : $value;
				$value          = is_numeric( $value ) ? $value  . 'px' : $value;
				$this->style[]  = esc_attr( trim( $property ) ) . ':' . esc_attr( $value ) . ';';
				return true;
			}

			// Values are different
			foreach ( $values as $k => $v ) {

				if ( 0 == $v ) {
					$v = '0px'; // 0px fix
				}

				if ( ! empty( $v ) ) {

					$method = 'parse_' . $property . '_' . $k;
					if ( method_exists( $this, $method ) ) {
						$this->$method( $v );
					}

				}

			}

			return true;

		}

	}

	/**
	 * Sanitize px-pct.
	 */
	private function sanitize_px_pct( $input ) {
		if ( 'none' == $input || '0px' == $input ) {
			return '0';
		} elseif ( strpos( $input, '%' ) ) {
			return esc_attr( $input );
		} elseif ( $input = floatval( $input ) ) {
			return esc_attr( $input ) . 'px';
		}
	}

	/**
	 * Sanitize font-size.
	 */
	private function sanitize_font_size( $input ) {
		if ( strpos( $input, 'px' ) || strpos( $input, 'em' ) || strpos( $input, 'vw' ) || strpos( $input, 'vmin' ) || strpos( $input, 'vmax' ) ) {
			$input = esc_attr( $input );
		} else {
			$input = absint( $input ) . 'px';
		}
		if ( $input && $input !== '0px' && $input !== '0em' ) {
			return esc_attr( $input );
		} else {
			return '';
		}
	}

	/**
	 * Sanitize px.
	 */
	private function sanitize_px( $input ) {
		if ( 'none' == $input ) {
			return '0';
		} else {
			return esc_attr( floatval( $input ) ) . 'px';
		}
	}

	/**
	 * Returns the styles.
	 */
	public function return_style() {
		if ( ! empty( $this->style ) ) {
			$this->style = implode( false, $this->style );
			if ( $this->add_style ) {
				return ' style="' . esc_attr( $this->style )  . '"';
			} else {
				return esc_attr( $this->style );
			}
		} else {
			return null;
		}
	}

} // End Class