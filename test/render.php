<?php
/**
 * Renders diagrams outside WordPress and prints the results as JSON for
 * test/render.test.mjs to assert against.
 *
 * @package Interactive_Body_Map
 */

require __DIR__ . '/wp-stubs.php';

$plugin = dirname( __DIR__ ) . '/wordpress-plugin/interactive-body-map/';

require $plugin . 'includes/class-bodymap-geometry.php';
require $plugin . 'includes/class-bodymap-model.php';
require $plugin . 'includes/class-bodymap-settings.php';
require $plugin . 'includes/class-bodymap-render.php';
require $plugin . 'includes/class-bodymap-shortcode.php';

/**
 * Reports whether a fragment parses as well-formed XML.
 *
 * @param string $html Rendered markup.
 * @return string|true True, or the first parser error.
 */
function bodymap_test_wellformed( $html ) {
	$svg = null;

	if ( preg_match( '#<svg\b.*</svg>#s', $html, $m ) ) {
		$svg = $m[0];
	}

	if ( null === $svg ) {
		return 'no <svg> element found';
	}

	libxml_use_internal_errors( true );
	libxml_clear_errors();

	$doc = new DOMDocument();
	$doc->loadXML( $svg );
	$errors = libxml_get_errors();
	libxml_clear_errors();

	return $errors ? trim( $errors[0]->message ) : true;
}

update_option(
	'bodymap_settings',
	array(
		'mode'          => 'simple',
		'max_width'     => '340px',
		'align'         => 'center',
		'target'        => '',
		'tooltip'       => 1,
		'show_disabled' => 1,
		'colors'        => array( 'hover' => '#0ea5e9' ),
		'links'         => array(
			'head'    => array(
				'url'    => '/anatomy/head',
				'label'  => 'Head & face',
				'target' => '',
			),
			'chest'   => array(
				'url'    => 'https://example.com/chest',
				'label'  => '',
				'target' => '_blank',
			),
			// One key that has to cover both arms and all eight arm segments.
			'arms'    => array(
				'url'   => '/anatomy/arms',
				'label' => '',
			),
			// More specific than "arms", so it must win for the left hand.
			'hand-left' => array(
				'url'   => '/anatomy/left-hand',
				'label' => '',
			),
			// A label with no URL: labelled but not clickable.
			'pelvis'  => array(
				'url'   => '',
				'label' => 'Pelvic region',
			),
			// Must never reach the page.
			'neck'    => array(
				'url'   => 'javascript:alert(1)',
				'label' => '',
			),
			'abdomen' => array(
				'url'   => '/abdomen"><script>alert(1)</script>',
				'label' => '<b>bold</b> & "quoted"',
			),
		),
	)
);

$result = array();

foreach ( array( 'simple', 'detailed' ) as $mode ) {
	$settings         = BodyMap_Settings::get();
	$settings['mode'] = $mode;
	$html             = BodyMap_Render::diagram( $settings );

	preg_match_all( '/data-bodymap-part="([^"]+)"/', $html, $ids );
	preg_match_all( '/<a class="bodymap__region" href="([^"]*)"[^>]*data-bodymap-part="([^"]+)"/', $html, $links, PREG_SET_ORDER );
	preg_match_all( '/<path class="bodymap__shape" d="([^"]+)"/', $html, $paths );

	$resolved = array();
	foreach ( $links as $link ) {
		$resolved[ $link[2] ] = $link[1];
	}

	$result[ $mode ] = array(
		'html'       => $html,
		'ids'        => $ids[1],
		'links'      => $resolved,
		'paths'      => array_values( array_unique( $paths[1] ) ),
		'wellformed' => bodymap_test_wellformed( $html ),
	);
}

// Shortcode overrides, including a per-region link supplied inline.
$result['shortcode'] = BodyMap_Shortcode::render(
	array(
		'mode'      => 'detailed',
		'max_width' => '260px',
		'hover'     => '#ff0000',
		'tooltip'   => 'no',
		'head'      => '/from-shortcode',
		'legs'      => '/lower-limb',
	)
);

$result['lookup_keys'] = array(
	'hand-right' => BodyMap_Model::lookup_keys( 'hand-right', 'arm-right' ),
	'head'       => BodyMap_Model::lookup_keys( 'head', 'head' ),
	'foot-left'  => BodyMap_Model::lookup_keys( 'foot-left', 'leg-left' ),
);

$result['viewBox']  = BodyMap_Geometry::VIEW_BOX;
$result['mirror']   = BodyMap_Geometry::MIRROR;
$result['enqueued'] = array_values( array_unique( $GLOBALS['bodymap_test_enqueued'] ) );

echo wp_json_encode_fallback( $result );

/**
 * json_encode with the flags this test wants.
 *
 * @param mixed $data Anything encodable.
 * @return string
 */
function wp_json_encode_fallback( $data ) {
	return json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}
