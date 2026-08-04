<?php
/**
 * Server-side renderer.
 *
 * The whole figure is printed as HTML, with every region a real anchor. Search
 * engines see the links, visitors see the diagram before any script has run,
 * and the front-end script only has to add hover and tooltip behaviour.
 *
 * @package Interactive_Body_Map
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders a diagram.
 */
final class BodyMap_Render {

	/**
	 * Counter used to give each diagram on a page a unique id.
	 *
	 * @var int
	 */
	private static $sequence = 0;

	/**
	 * Colour settings mapped to the CSS custom properties they drive.
	 *
	 * @var array<string, string>
	 */
	private static $colour_vars = array(
		'fill'         => '--bodymap-fill',
		'hover'        => '--bodymap-fill-hover',
		'active'       => '--bodymap-fill-active',
		'disabled'     => '--bodymap-fill-disabled',
		'stroke'       => '--bodymap-stroke',
		'detail'       => '--bodymap-detail',
		'tooltip_bg'   => '--bodymap-tooltip-bg',
		'tooltip_text' => '--bodymap-tooltip-color',
	);

	/**
	 * Builds the markup for one diagram.
	 *
	 * @param array<string, mixed> $args Merged settings and shortcode/block attributes.
	 * @return string
	 */
	public static function diagram( $args ) {
		$args = wp_parse_args( $args, BodyMap_Settings::defaults() );
		$mode = BodyMap_Model::sanitize_mode( isset( $args['mode'] ) ? $args['mode'] : BodyMap_Model::MODE_SIMPLE );

		wp_enqueue_style( 'interactive-body-map' );
		wp_enqueue_script( 'interactive-body-map' );

		self::$sequence++;
		$uid   = 'bodymap-' . self::$sequence;
		$links = isset( $args['links'] ) && is_array( $args['links'] ) ? $args['links'] : array();

		$classes = array( 'bodymap' );

		if ( ! empty( $args['selectable'] ) ) {
			$classes[] = 'bodymap--selectable';
		}
		if ( ! empty( $args['outline'] ) ) {
			$classes[] = 'bodymap--outline';
		}
		if ( ! empty( $args['align'] ) && in_array( $args['align'], array( 'left', 'right' ), true ) ) {
			$classes[] = 'bodymap--align-' . $args['align'];
		}
		if ( ! empty( $args['class'] ) ) {
			$classes = array_merge( $classes, preg_split( '/\s+/', trim( $args['class'] ) ) );
		}

		$style = self::inline_style( $args );

		$html  = sprintf(
			'<div class="%1$s" id="%2$s" data-bodymap-mode="%3$s"%4$s%5$s%6$s%7$s>',
			esc_attr( implode( ' ', array_filter( $classes ) ) ),
			esc_attr( $uid ),
			esc_attr( $mode ),
			$style ? ' style="' . esc_attr( $style ) . '"' : '',
			empty( $args['tooltip'] ) ? ' data-tooltip="false"' : '',
			empty( $args['selectable'] ) ? '' : ' data-selectable="true"',
			' data-body-map'
		);
		$html .= self::svg( $uid, $mode, $links, $args );
		$html .= '</div>';

		if ( ! empty( $args['show_list'] ) ) {
			$html .= self::link_list( $mode, $links, $args );
		}

		/**
		 * Filters the finished markup for one diagram.
		 *
		 * @param string               $html Diagram markup.
		 * @param array<string, mixed> $args Settings used to build it.
		 */
		return apply_filters( 'bodymap_diagram_html', $html, $args );
	}

	/**
	 * Builds the inline custom properties for the wrapper.
	 *
	 * @param array<string, mixed> $args Settings.
	 * @return string
	 */
	private static function inline_style( $args ) {
		$rules = array();

		if ( ! empty( $args['max_width'] ) ) {
			$rules[] = 'max-width:' . self::css_length( $args['max_width'] );
		}

		foreach ( self::$colour_vars as $key => $var ) {
			if ( ! empty( $args['colors'][ $key ] ) ) {
				$colour = sanitize_hex_color( $args['colors'][ $key ] );
				if ( $colour ) {
					$rules[] = $var . ':' . $colour;
				}
			}
		}

		return implode( ';', $rules );
	}

	/**
	 * Accepts a bare number as pixels, otherwise keeps a valid CSS length.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function css_length( $value ) {
		$value = trim( (string) $value );

		if ( is_numeric( $value ) ) {
			return $value . 'px';
		}

		return preg_match( '/^\d+(\.\d+)?(px|em|rem|vh|vw|%|ch)$/', $value ) ? $value : '';
	}

	/**
	 * Builds the SVG itself.
	 *
	 * @param string                               $uid   Unique id for this diagram.
	 * @param string                               $mode  Region mode.
	 * @param array<string, array<string, string>> $links Per-region settings.
	 * @param array<string, mixed>                 $args  Settings.
	 * @return string
	 */
	private static function svg( $uid, $mode, $links, $args ) {
		$title = ! empty( $args['title'] )
			? $args['title']
			: __( 'Interactive body map', 'interactive-body-map' );

		$svg  = sprintf(
			'<svg class="bodymap__svg" viewBox="%1$s" xmlns="http://www.w3.org/2000/svg" role="group" aria-labelledby="%2$s-title" preserveAspectRatio="xMidYMid meet" focusable="false">',
			esc_attr( BodyMap_Geometry::VIEW_BOX ),
			esc_attr( $uid )
		);
		$svg .= sprintf( '<title id="%s-title">%s</title>', esc_attr( $uid ), esc_html( $title ) );

		$svg .= '<g class="bodymap__regions">';

		foreach ( BodyMap_Model::regions( $mode ) as $region ) {
			$svg .= self::region( $region, $links, $args );
		}

		$svg .= '</g>';
		$svg .= self::details();
		$svg .= '</svg>';

		return $svg;
	}

	/**
	 * Builds one clickable region.
	 *
	 * @param array<string, mixed>                 $region Region description.
	 * @param array<string, array<string, string>> $links  Per-region settings.
	 * @param array<string, mixed>                 $args   Settings.
	 * @return string
	 */
	private static function region( $region, $links, $args ) {
		$config = BodyMap_Model::resolve( $links, $region['id'], $region['group'] );
		$url    = isset( $config['url'] ) ? trim( $config['url'] ) : '';
		$label  = ! empty( $config['label'] ) ? $config['label'] : $region['label'];

		if ( '' === $url && empty( $args['show_disabled'] ) ) {
			return '';
		}

		$shapes = '';
		foreach ( $region['shapes'] as $shape ) {
			$shapes .= sprintf(
				'<path class="bodymap__shape" d="%s"%s/>',
				esc_attr( $shape['d'] ),
				$shape['mirror'] ? ' transform="' . esc_attr( BodyMap_Geometry::MIRROR ) . '"' : ''
			);
		}

		$common = sprintf(
			' data-bodymap-part="%s" data-bodymap-group="%s" aria-label="%s"',
			esc_attr( $region['id'] ),
			esc_attr( $region['group'] ),
			esc_attr( $label )
		);

		if ( '' === $url ) {
			return '<g class="bodymap__region bodymap__region--disabled"' . $common . '>' . $shapes . '</g>';
		}

		$target = ! empty( $config['target'] ) ? $config['target'] : ( isset( $args['target'] ) ? $args['target'] : '' );
		$rel    = '_blank' === $target ? 'noopener' : '';

		return sprintf(
			'<a class="bodymap__region" href="%1$s"%2$s%3$s%4$s>%5$s</a>',
			esc_url( $url ),
			$common,
			'_blank' === $target ? ' target="_blank"' : '',
			$rel ? ' rel="' . esc_attr( $rel ) . '"' : '',
			$shapes
		);
	}

	/**
	 * Builds the non-interactive contour layer.
	 *
	 * @return string
	 */
	private static function details() {
		$svg = '<g class="bodymap__details" aria-hidden="true">';

		foreach ( BodyMap_Geometry::details() as $d ) {
			$svg .= '<path d="' . esc_attr( $d ) . '"/>';
		}

		foreach ( BodyMap_Geometry::details_paired() as $d ) {
			$svg .= '<path d="' . esc_attr( $d ) . '"/>';
			$svg .= '<path d="' . esc_attr( $d ) . '" transform="' . esc_attr( BodyMap_Geometry::MIRROR ) . '"/>';
		}

		return $svg . '</g>';
	}

	/**
	 * Builds a plain list of the same links, for visitors who prefer text.
	 *
	 * @param string                               $mode  Region mode.
	 * @param array<string, array<string, string>> $links Per-region settings.
	 * @param array<string, mixed>                 $args  Settings.
	 * @return string
	 */
	private static function link_list( $mode, $links, $args ) {
		$items = '';

		foreach ( BodyMap_Model::regions( $mode ) as $region ) {
			$config = BodyMap_Model::resolve( $links, $region['id'], $region['group'] );

			if ( empty( $config['url'] ) ) {
				continue;
			}

			$target = ! empty( $config['target'] ) ? $config['target'] : ( isset( $args['target'] ) ? $args['target'] : '' );

			$items .= sprintf(
				'<li><a href="%1$s"%2$s>%3$s</a></li>',
				esc_url( $config['url'] ),
				'_blank' === $target ? ' target="_blank" rel="noopener"' : '',
				esc_html( ! empty( $config['label'] ) ? $config['label'] : $region['label'] )
			);
		}

		return $items ? '<ul class="bodymap-list">' . $items . '</ul>' : '';
	}
}
