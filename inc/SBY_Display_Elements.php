<?php

namespace SmashBalloon\YouTubeFeed;

use SmashBalloon\YouTubeFeed\Pro\SBY_Parse_Pro;
use \Smashballoon\Customizer\Feed_Builder;

class SBY_Display_Elements
{
	/**
	 * Images are hidden initially with the new/transition classes
	 * except if the js image loading is disabled using the plugin
	 * settings
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_item_classes( $settings ) {
		$classes = array( 'sby_new' );
		if ( empty( $settings['global_settings'] ) ) {
			$settings['global_settings'] = $settings;
		}

		if ( !$settings['global_settings']['disable_js_image_loading'] ) {
			if ( !sby_doing_customizer( $settings ) ) {
				$classes[] = 'sby_transition';
			}
		} else {
			$classes[] = 'sby_no_js';

			$classes[] = 'sby_no_resraise';
			$classes[] = 'sby_js_load_disabled';
		}

		return ' ' . implode( ' ', $classes );
	}

	/**
	 * Returns the list of CSS classes
	 *
	 * @param array $settings
	 * @param array $additional_classes
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function get_feed_container_css_classes( $settings, $additional_classes ) {
		$customizer = sby_doing_customizer( $settings );
		$classes = '';
		$classes_array = array(
			'sb_youtube',
			'sby_layout_' . esc_attr( $settings['layout'] ),
			'sby_col_' . esc_attr( self::get_cols( $settings ) ),
			'sby_mob_col_' . esc_attr( self::get_cols_mobile( $settings ) ),
			'sby_palette_' . esc_attr( $settings['colorpalette'] ),
		);

		if ( $customizer ) {
			return ' :class="$parent.getFeedContainerClasses()" ';
		} else {
			$classes = ' class="'. implode(" ", $classes_array ) .' ' . $additional_classes .'"';
		}

		return $classes;
	}

	/**
	 * Get feed container main attributes
	 *
	 * @since 2.0
	 */
	public static function get_feed_container_main_attributes( $settings ) {
		$customizer = sby_doing_customizer( $settings );

		$atts = self::print_element_attribute(
			$customizer,
			array(
				'attr'        	=> 'data-card-boxshadow',
				'vue_content' 	=> ' $parent.customizerFeedData.settings.enableboxshadow',
				'php_condition' => $settings['enableboxshadow'] == true,
				'php_content' 	=> $settings['enableboxshadow'],
			)
		);

		$atts .= self::print_element_attribute(
			$customizer,
			array(
				'attr'        	=> 'data-videostyle',
				'vue_content' 	=> ' $parent.customizerFeedData.settings.videocardstyle',
				'php_condition' => $settings['videocardstyle'] === 'boxed',
				'php_content' 	=> $settings['videocardstyle'],
			)
		);

		$atts .= self::print_element_attribute(
			$customizer,
			array(
				'attr'        	=> 'data-videocardlayout',
				'vue_content' 	=> '$parent.customizerFeedData.settings.videocardlayout',
				'php_condition' => isset($settings['videocardlayout']),
				'php_content' 	=> $settings['videocardlayout'],
			)
		);

		return $atts;
	}

	/**
	 * Get text header attributes
	 *
	 * @since 2.0
	 */
	public static function get_text_header_attributes( $settings ) {
		$customizer = sby_doing_customizer( $settings );

		$atts = self::print_element_attribute(
			$customizer,
			array(
				'attr'        	=> 'data-header-size',
				'vue_content' 	=> '$parent.customizerFeedData.settings.customheadersize',
				'php_condition' => isset($settings['customheadersize']),
				'php_content' 	=> $settings['customheadersize'],
			)
		);

		return $atts;
	}

	/**
	 * Print Element HTML Attribute
	 *
	 * @param bool $customizer
	 * @param array $args
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function print_element_attribute( $customizer, $args ) {
		if ( $customizer ) {
			return ' :' . $args['attr'] . '="' . $args['vue_content'] . '"';
		}
		if( ( isset( $args['php_condition'] ) && $args['php_condition'] ) || !isset( $args['php_condition'] ) ){
			if ( isset( $args['php_content'] ) ) {
				return ' ' . $args['attr'] . '="' . $args['php_content'] . '"';
			}
		}
	}
	/**
	 * Display header
	 *
	 * @since 2.0
	 */
	public static function display_header( $header_data, $settings ) {
		if ( sby_doing_customizer( $settings ) ) {
			include sby_get_feed_template_part( 'header', $settings );
			include sby_get_feed_template_part( 'header-text', $settings );
		} else {
			if ( $settings['headerstyle'] == 'standard' ) {
				include sby_get_feed_template_part( 'header', $settings );
			} else if ( $settings['headerstyle'] == 'text' ) {
				include sby_get_feed_template_part( 'header-text', $settings );
			}
		}
	}

	/**
	 * Overwritten in the Pro version.
	 *
	 * @param string $type key of the kind of icon needed
	 * @param string $icon_type svg or font
	 *
	 * @return string the complete html for the icon
	 *
	 * @since 1.0
	 */
	public static function get_icon( $type, $icon_type ) {
		return self::get_basic_icons( $type, $icon_type );
	}

	public static function get_cols( $settings ) {
		if( $settings['layout'] == 'list' ) {
			return 0;
		}
		if ( isset( $settings['cols'] ) ) {
			return $settings['cols'];
		}
		return 0;
	}

	public static function get_cols_mobile( $settings ) {
		if( $settings['layout'] == 'list' ) {
			return 0;
		}
		if ( isset( $settings['colsmobile'] ) ) {
			return $settings['colsmobile'];
		}
		return 0;
	}

	public static function get_style_att( $context, $settings, $pos = false ) {
		$style_settings = array();
		$item_spacing_setting = $settings['itemspacing'];
		if ( ! preg_match("/(px)|(%)/", $item_spacing_setting ) ) {
			$item_spacing_setting = $item_spacing_setting . $settings['itemspacingunit'];
		}
		if ( $context === 'player' ) {
			$style_settings['margin-bottom'] = $item_spacing_setting;
		} elseif ( $context === 'item' ) {
			if ( $settings['layout'] === 'list' ) {
				$style_settings['margin-bottom'] = $item_spacing_setting;
			}
		} elseif ( $context === 'items_wrap' || $context === 'player_outer_wrap' ) {
			if ( $settings['layout'] !== 'list' ) {
				$style_settings['padding'] = $item_spacing_setting;
			}
		} elseif ( $context === 'header' ) {
			if ( $settings['itemspacing'] > 0 ) {
				$style_settings['padding'] = $item_spacing_setting;
			}
			if ( $settings['itemspacing'] < 10 ) {
				$style_settings['margin-bottom'] = '10px';
			}
			$style_settings['padding-bottom'] = '0';
		}

		if ( $context === 'player_outer_wrap' ) {
			$style_settings['padding-bottom'] = 0;
		}

		$style_att = '';
		if ( ! empty( $style_settings ) ) {
			$style_att = ' style="';
			foreach ( $style_settings as $prop => $val ) {
				$style_att .= $prop . ': '. $val . ';';
			}
			$style_att .= '"';
		}

		return $style_att;
	}

	public static function get_display_avatar( $header_data, $settings ) {
		if ( SBY_GDPR_Integrations::doing_gdpr( $settings ) && $settings['global_settings']['disablecdn'] ) {
			return trailingslashit( SBY_PLUGIN_URL ) . 'img/placeholder.png';
		}
		return SBY_Parse::get_avatar( $header_data, $settings );
	}

	/**
	 * Get Text Header Attributes
	 *
	 * @since 2.0
	 */
	public static function get_header_text_attr( $settings ) {
		if ( sby_doing_customizer( $settings ) ) {
			return ' :data-header-subscriber="$parent.checkShouldShowSubscribers()"';
		}

		// If not doing customizer
		$subscribe_shown = $settings['showsubscribe'] == true ? 'shown' : '';
		return ' data-header-subscriber="'. $subscribe_shown .'"';
	}

	/**
	 * Palette class
	 *
	 * @param array $settings
	 * @param string $context
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function get_palette_class( $settings, $context = '' ) {
		$customizer = sby_doing_customizer( $settings );
		if ( $customizer ) {
			return ' $parent.getPaletteClass() ';
		} else {
			$feed_id_addition = ! empty( $settings['colorpalette'] ) && $settings['colorpalette'] === 'custom' ? '_' . $settings['feed'] : '';
			$palette_class    = ! empty( $settings['colorpalette'] ) && $settings['colorpalette'] !== 'inherit' ? ' sby' . $context . '_palette_' . $settings['colorpalette'] . $feed_id_addition : '';
			return $palette_class;
		}
	}

	/**
	 * Palette type
	 *
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function palette_type( $settings ) {
		return ! empty( $settings['colorpalette'] ) ? $settings['colorpalette'] : 'inherit';
	}
	/**
	 * Returns the best media url for an image based on settings.
	 * By default a white placeholder image is loaded and replaced
	 * with the most optimal image size based on the actual dimensions
	 * of the image element in the feed.
	 *
	 * @param array $post data for an individual post
	 * @param array $settings
	 * @param array $resized_images (optional) not yet used but
	 *  can pass in existing resized images to use in the source
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_optimum_media_url( $post, $settings, $resized_images = array() ) {
		$media_url = '';
		$optimum_res = $settings['imageres'];
		$account_type = isset( $post['images'] ) ? 'personal' : 'business';
		if ( empty( $settings['global_settings'] ) ) {
			$settings['global_settings'] = $settings;
		}

		// only use the placeholder if it will be replaced using JS
		if ( ! $settings['global_settings']['disable_js_image_loading'] || ($settings['global_settings']['disablecdn']  && $settings['global_settings']['gdpr'] === 'yes')) {
			return trailingslashit( SBY_PLUGIN_URL ) . 'img/placeholder.png';
		} else {
			$optimum_res = 'full';
			$settings['imageres'] = 'full';
		}

		$media_url = SBY_Parse::get_media_url( $post, 'lightbox' );

		return $media_url;
	}

	/**
	 * Creates a style attribute that contains all of the styles for
	 * the main feed div.
	 *
	 * @param $settings
	 *
	 * @return string
	 */
	public static function get_feed_style( $settings ) {

		$styles = '';
		$bg_color = str_replace( '#', '', $settings['background'] );

		if ( ! empty( $settings['imagepadding'] )
		     || ! empty( $bg_color )
		     || ! empty( $settings['width'] )
		     || ! empty( $settings['height'] ) ) {
			$styles = ' style="';
			if ( ! empty( $settings['imagepadding'] ) ) {
				$styles .= 'padding-bottom: ' . ((int)$settings['imagepadding'] * 2) . esc_attr( $settings['imagepaddingunit'] ) . ';';
			}
			if ( ! empty( $bg_color ) ) {
				$styles .= 'background-color: rgb(' . esc_attr( sby_hextorgb( $bg_color ) ). ');';
			}
			if ( ! empty( $settings['width'] ) ) {
				$styles .= 'width: ' . (int)$settings['width'] . esc_attr( $settings['widthunit'] ) . ';';
			}
			if ( ! empty( $settings['height'] ) ) {
				$styles .= 'height: ' . (int)$settings['height'] . esc_attr( $settings['heightunit'] ) . ';';
			}
			$styles .= '"';
		}
		return $styles;
	}

	/**
	 * Creates a style attribute for the sby_images div
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_items_wrap_style( $settings ) {
		if ( ! empty ( $settings['imagepadding'] ) ) {
			return 'style="padding: '.(int)$settings['imagepadding'] . esc_attr( $settings['imagepaddingunit'] ) . ';"';
		}
		return '';
	}

	/**
	 * Creates a style attribute for the header. Can be used in
	 * several places based on the header style
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_header_text_color_styles( $settings ) {
		$header_color = str_replace( '#', '', $settings['headercolor'] );

		if ( ! empty( $header_color ) ) {
			return 'style="color: rgb(' . esc_attr( sby_hextorgb( $header_color ) ). ');"';
		}
		return '';
	}

	/**
	 * Header icon and text size is styled using the class added here.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_header_size_class( $settings ) {
		$header_size_class = in_array( strtolower( $settings['headersize'] ), array( 'medium', 'large' ) ) ? ' sby_'.strtolower( $settings['headersize'] ) : '';
		return $header_size_class;
	}

	/**
	 * Creates a style attribute for the subscribe button. Can be in
	 * the feed footer or in a boxed header.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_subscribe_styles( $settings ) {
		$styles = '';
		$subscribe_color = str_replace( '#', '', $settings['subscribecolor'] );
		$subscribe_text_color = str_replace( '#', '', $settings['subscribetextcolor'] );

		if ( ! empty( $subscribe_color ) || ! empty( $subscribe_text_color ) ) {
			$styles = 'style="';
			if ( ! empty( $subscribe_color ) ) {
				$styles .= 'background: rgb(' . esc_attr( sby_hextorgb( $subscribe_color ) ) . ');';
			}
			if ( ! empty( $subscribe_text_color ) ) {
				$styles .= 'color: rgb(' . esc_attr( sby_hextorgb( $subscribe_text_color ) ). ');';
			}
			$styles .= '"';
		}
		return $styles;
	}

	/**
	 * Creates a style attribute for styling the load more button.
	 *
	 * @param $settings
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function get_load_button_styles( $settings ) {
		$styles = '';
		$button_color = str_replace( '#', '', $settings['buttoncolor'] );
		$button_text_color = str_replace( '#', '', $settings['buttontextcolor'] );

		if ( ! empty( $button_color ) || ! empty( $button_text_color ) ) {
			$styles = 'style="';
			if ( ! empty( $button_color ) ) {
				$styles .= 'background: rgb(' . esc_attr( sby_hextorgb( $button_color ) ) . ');';
			}
			if ( ! empty( $button_text_color ) ) {
				$styles .= 'color: rgb(' . esc_attr( sby_hextorgb( $button_text_color ) ). ');';
			}
			$styles .= '"';
		}
		return $styles;
	}

	/**
	 * Get load more button attributes
	 *
	 * @since 2.0
	 */
	public static function get_load_button_attribute( $settings ) {
		return self::should_print_element_vue( sby_doing_customizer( $settings ), '$parent.customizerFeedData.settings.buttontext' );
	}

	/**
	 * Get subscribe button attributes
	 *
	 * @since 2.0
	 */
	public static function get_subscribe_button_attribute( $settings ) {
		return self::should_print_element_vue( sby_doing_customizer( $settings ), '$parent.customizerFeedData.settings.subscribetext' );
	}

	/**
	 * Get subscribe button attributes
	 *
	 * @since 2.0
	 */
	public static function get_text_header_content( $settings ) {
		return self::should_print_element_vue( sby_doing_customizer( $settings ), '$parent.getCustomHeaderText()' );
	}

	/**
	 * Channel subscribers data attributes
	 *
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function get_subscribers_data_attributes( $settings ) {
		if ( ! sby_doing_customizer( $settings ) ) {
			return '';
		}
		return ' ' . self::display_vue_condition( 'showsubscribers' );
	}

	/**
	 * Channel description data attributes
	 *
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function get_description_data_attributes( $settings ) {
		if ( ! sby_doing_customizer( $settings ) ) {
			return '';
		}
		return ' ' . self::display_vue_condition( 'showdescription' );
	}

	/**
	 * Load button data attributes
	 *
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function get_button_data_attributes( $settings ) {
		if ( ! sby_doing_customizer( $settings ) ) {
			return '';
		}
		return ' ' . self::display_vue_condition( 'showbutton' );
	}

	/**
	 * Load button data attributes
	 *
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function get_subscribe_button_data_attributes( $settings ) {
		if ( ! sby_doing_customizer( $settings ) ) {
			return '';
		}
		return ' ' . self::display_vue_condition( 'showsubscribe' );
	}

	/**
	 * Customizer feed Header display conditions
	 *
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function get_header_display_condition( $settings ) {
		if ( ! sby_doing_customizer( $settings ) ) {
			return '';
		}
		return ' v-show="$parent.shouldShowStandardHeader()"';
	}

	/**
	 * Customizer feed text header data attributes
	 *
	 * @param array $settings
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function get_text_header_display_condition( $settings ) {
		if ( ! sby_doing_customizer( $settings ) ) {
			return '';
		}
		return ' v-show="$parent.shouldShowTextHeader()"';
	}

	/**
	 * Display vue condition
	 *
	 * @param array $setting_name
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public static function display_vue_condition( $setting_name, $custom_condition = '' ) {
		return self::create_condition_vue( true, '$parent.valueIsEnabled( $parent.customizerFeedData.settings.' . $setting_name . ' ) '. $custom_condition );
	}

	/**
	 * Should Print HTML
	 *
	 * @param bool $customizer
	 * @param string $condition
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function create_condition_vue( $customizer, $condition ) {
		if ( $customizer ) {
			return ' v-if="' . $condition . '" ';
		}
		return '';
	}

	/**
	 * A not very elegant but useful method to abstract out how the settings
	 * work for displaying certain elements in the feed.
	 *
	 * @param string $element specific key, view below for supported ones
	 * @param $settings
	 *
	 * @return bool
	 *
	 * @since 5.0
	 */
	public static function should_show_element( $element, $context, $settings ) {
		//user, views, date
		if ( $context === 'item-hover' ) {
			$include_array = is_array( $settings['hoverinclude'] ) ? $settings['hoverinclude'] : explode( ',', str_replace( ' ', '', $settings['hoverinclude'] ) );
		} else {
			$include_array = is_array( $settings['include'] ) ? $settings['include'] : explode( ',', str_replace( ' ', '', $settings['include'] ) );
		}

		if ( in_array( $element, $include_array, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the html for an icon based on the kind requested
	 *
	 * @param string $type kind of icon needed (ex "video" is a play button)
	 * @param string $icon_type svg or font
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	protected static function get_basic_icons( $type, $icon_type ) {
		if ( $type === 'carousel' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg class="svg-inline--fa fa-clone fa-w-16 sby_lightbox_carousel_icon" aria-hidden="true" data-fa-proƒcessed="" data-prefix="far" data-icon="clone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
	                <path fill="currentColor" d="M464 0H144c-26.51 0-48 21.49-48 48v48H48c-26.51 0-48 21.49-48 48v320c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h48c26.51 0 48-21.49 48-48V48c0-26.51-21.49-48-48-48zM362 464H54a6 6 0 0 1-6-6V150a6 6 0 0 1 6-6h42v224c0 26.51 21.49 48 48 48h224v42a6 6 0 0 1-6 6zm96-96H150a6 6 0 0 1-6-6V54a6 6 0 0 1 6-6h308a6 6 0 0 1 6 6v308a6 6 0 0 1-6 6z"></path>
	            </svg>';
			} else {
				return '<i class="fa fa-clone sby_carousel_icon" aria-hidden="true"></i>';
			}

		} elseif ( $type === 'video' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg style="color: rgba(255,255,255,1)" class="svg-inline--fa fa-play fa-w-14 sby_playbtn" aria-hidden="true" data-fa-processed="" data-prefix="fa" data-icon="play" role="presentation" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M424.4 214.7L72.4 6.6C43.8-10.3 0 6.1 0 47.9V464c0 37.5 40.7 60.1 72.4 41.3l352-208c31.4-18.5 31.5-64.1 0-82.6z"></path></svg>';
			} else {
				return '<i class="fa fa-play sby_playbtn" aria-hidden="true"></i>';
			}
		} elseif ( $type === 'youtube' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="youtube" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-youtube fa-w-18"><path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" class=""></path></svg>';
			} else {
				return '<i aria-hidden="true" role="img" class="sby_new_logo fab fa-youtube"></i>';
			}
		} elseif ( $type === 'newlogo' ) {
			if ( $icon_type === 'svg' ) {
				return '<svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="youtube" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="sby_new_logo svg-inline--fa fa-youtube fa-w-18"><path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" class=""></path></svg>';
			} else {
				return '<i aria-hidden="true" role="img" class="sby_new_logo fab fa-youtube"></i>';
			}
		} elseif ( $type === 'play') {
			if ( $icon_type === 'svg' ) {
				return '<svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="youtube" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-youtube fa-w-18"><path fill="currentColor" d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" class=""></path></svg>';
			} else {
				return '<i aria-hidden="true" role="img" class="fa fas fa-play"></i>';
			}
		} else {
			return '';
		}
	}

	public static function escaped_data_att_string( $atts ) {
		if ( empty( $atts ) ) {
			return '';
		}
		$string = '';
		foreach ( $atts as $key => $value ) {
			$string .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return $string;
	}

	/**
	 * Should Print HTML
	 *
	 * @param bool $customizer
	 * @param string $content
	 *
	 * @return string
	 *
	 * @since 2.0
	 */
	public static function should_print_element_vue( $customizer, $content ) {
		if ( $customizer ) {
			return ' v-html="' . $content . '" ';
		}
		return '';
	}

}