<?php
/**
 * The [body_map] shortcode.
 *
 * @package Interactive_Body_Map
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode handler.
 */
final class BodyMap_Shortcode {

	const TAG = 'body_map';

	/**
	 * Attributes that configure the diagram rather than a link.
	 *
	 * @var string[]
	 */
	private static $flags = array( 'tooltip', 'selectable', 'show_disabled', 'show_list', 'outline' );

	/**
	 * Registers the shortcode.
	 *
	 * @return void
	 */
	public static function register() {
		add_shortcode( self::TAG, array( __CLASS__, 'render' ) );
	}

	/**
	 * Every key that can carry a link, including the side-less aliases.
	 *
	 * @return string[]
	 */
	public static function link_keys() {
		static $keys = null;

		if ( null !== $keys ) {
			return $keys;
		}

		$keys = BodyMap_Geometry::group_order();

		foreach ( BodyMap_Geometry::parts() as $part ) {
			$keys[] = $part['id'];
		}

		foreach ( BodyMap_Geometry::plurals() as $plural ) {
			$keys[] = $plural;
		}

		$keys = array_values( array_unique( $keys ) );

		return $keys;
	}

	/**
	 * Renders the shortcode.
	 *
	 * @param array<string, string>|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts     = is_array( $atts ) ? $atts : array();
		$settings = BodyMap_Settings::get();

		foreach ( array( 'mode', 'max_width', 'align', 'target', 'title', 'class' ) as $key ) {
			if ( isset( $atts[ $key ] ) ) {
				$settings[ $key ] = sanitize_text_field( $atts[ $key ] );
			}
		}

		foreach ( self::$flags as $flag ) {
			if ( isset( $atts[ $flag ] ) ) {
				$settings[ $flag ] = self::boolean( $atts[ $flag ] ) ? 1 : 0;
			}
		}

		foreach ( array_keys( $settings['colors'] ) as $key ) {
			if ( isset( $atts[ $key ] ) ) {
				$settings['colors'][ $key ] = (string) sanitize_hex_color( trim( $atts[ $key ] ) );
			}
		}

		/* A link given in the shortcode replaces the saved one for that region
		 * only, so one page can point the diagram somewhere else without
		 * disturbing the site-wide configuration. */
		foreach ( self::link_keys() as $key ) {
			if ( ! isset( $atts[ $key ] ) ) {
				continue;
			}

			$url = trim( $atts[ $key ] );

			$settings['links'][ $key ] = array(
				'url'    => $url ? esc_url_raw( $url, array( 'http', 'https', 'mailto', 'tel' ) ) : '',
				'label'  => isset( $atts[ $key . '_label' ] ) ? sanitize_text_field( $atts[ $key . '_label' ] ) : '',
				'target' => isset( $settings['target'] ) ? $settings['target'] : '',
			);
		}

		return BodyMap_Render::diagram( $settings );
	}

	/**
	 * Reads the usual ways of writing yes and no in a shortcode.
	 *
	 * @param string $value Raw attribute value.
	 * @return bool
	 */
	private static function boolean( $value ) {
		return in_array( strtolower( trim( (string) $value ) ), array( '1', 'true', 'yes', 'on' ), true );
	}
}
