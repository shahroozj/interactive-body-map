<?php
/**
 * Just enough WordPress to render a diagram outside WordPress.
 *
 * Used by test/render.php. These are deliberately thin: the point is to check
 * the plugin's own logic and escaping, not to reimplement WordPress.
 *
 * @package Interactive_Body_Map
 */

define( 'ABSPATH', __DIR__ );

$GLOBALS['bodymap_test_options']  = array();
$GLOBALS['bodymap_test_enqueued'] = array();

// phpcs:disable WordPress.NamingConventions, Squiz.Commenting -- test doubles.

function __( $text, $domain = '' ) { return $text; }
function esc_html__( $text, $domain = '' ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_attr__( $text, $domain = '' ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_html_e( $text, $domain = '' ) { echo esc_html__( $text ); }
function esc_attr_e( $text, $domain = '' ) { echo esc_attr__( $text ); }

function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }

function esc_url( $url, $protocols = null ) {
	$url = trim( (string) $url );

	// Mirrors the part of esc_url() this plugin depends on: block anything
	// that is not an allowed scheme, and escape for an attribute.
	if ( preg_match( '#^\s*[a-z0-9.+-]+:#i', $url ) ) {
		$allowed = $protocols ? $protocols : array( 'http', 'https', 'mailto', 'tel' );
		$scheme  = strtolower( substr( $url, 0, strpos( $url, ':' ) ) );

		if ( ! in_array( $scheme, $allowed, true ) ) {
			return '';
		}
	}

	return htmlspecialchars( $url, ENT_QUOTES );
}

function esc_url_raw( $url, $protocols = null ) {
	$url = trim( (string) $url );

	if ( preg_match( '#^\s*[a-z0-9.+-]+:#i', $url ) ) {
		$allowed = $protocols ? $protocols : array( 'http', 'https', 'mailto', 'tel' );
		$scheme  = strtolower( substr( $url, 0, strpos( $url, ':' ) ) );

		if ( ! in_array( $scheme, $allowed, true ) ) {
			return '';
		}
	}

	return $url;
}

function sanitize_text_field( $text ) {
	return trim( preg_replace( '/[\r\n\t]+/', ' ', wp_strip_all_tags( (string) $text ) ) );
}

function wp_strip_all_tags( $text ) { return strip_tags( (string) $text ); }

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}

function sanitize_hex_color( $color ) {
	$color = trim( (string) $color );
	return preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', $color ) ? $color : null;
}

function wp_parse_args( $args, $defaults = array() ) {
	return is_array( $args ) ? array_merge( $defaults, $args ) : $defaults;
}

function apply_filters( $tag, $value ) { return $value; }
function add_filter() {}
function add_action() {}
function add_shortcode() {}
function do_shortcode( $content ) { return $content; }

function wp_enqueue_style( $handle ) { $GLOBALS['bodymap_test_enqueued'][] = 'style:' . $handle; }
function wp_enqueue_script( $handle ) { $GLOBALS['bodymap_test_enqueued'][] = 'script:' . $handle; }

function get_option( $name, $default = false ) {
	return array_key_exists( $name, $GLOBALS['bodymap_test_options'] )
		? $GLOBALS['bodymap_test_options'][ $name ]
		: $default;
}

function update_option( $name, $value ) {
	$GLOBALS['bodymap_test_options'][ $name ] = $value;
	return true;
}

function admin_url( $path = '' ) { return 'https://example.test/wp-admin/' . $path; }
function register_setting() {}
function checked() {}
function selected() {}
function in_array_strict( $needle, $haystack ) { return in_array( $needle, $haystack, true ); }
