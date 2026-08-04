<?php
/**
 * Turns the raw geometry into the list of regions a diagram should draw.
 *
 * This mirrors the logic in js-version/js/body-map.js so that a figure
 * printed by PHP and a figure built by the script are identical.
 *
 * @package Interactive_Body_Map
 */

defined( 'ABSPATH' ) || exit;

/**
 * Region model.
 */
final class BodyMap_Model {

	const MODE_SIMPLE   = 'simple';
	const MODE_DETAILED = 'detailed';

	/**
	 * The two modes, for settings screens.
	 *
	 * @return array<string, string>
	 */
	public static function modes() {
		return array(
			self::MODE_SIMPLE   => __( 'Simple - 9 regions', 'interactive-body-map' ),
			self::MODE_DETAILED => __( 'Detailed - 21 regions', 'interactive-body-map' ),
		);
	}

	/**
	 * Normalises an arbitrary mode string.
	 *
	 * @param string $mode Requested mode.
	 * @return string
	 */
	public static function sanitize_mode( $mode ) {
		return self::MODE_DETAILED === $mode ? self::MODE_DETAILED : self::MODE_SIMPLE;
	}

	/**
	 * The regions drawn in a given mode.
	 *
	 * Each entry is an id, a label, a group, and the list of paths that make
	 * it up. In simple mode a region is a whole group, so an arm is one link
	 * covering four paths.
	 *
	 * @param string $mode Either 'simple' or 'detailed'.
	 * @return array<int, array<string, mixed>>
	 */
	public static function regions( $mode = self::MODE_SIMPLE ) {
		$mode     = self::sanitize_mode( $mode );
		$parts    = BodyMap_Geometry::parts();
		$labels   = BodyMap_Geometry::group_labels();
		$regions  = array();

		foreach ( BodyMap_Geometry::group_order() as $group ) {
			$shapes = array();

			foreach ( $parts as $part ) {
				if ( $part['group'] !== $group ) {
					continue;
				}

				if ( self::MODE_DETAILED === $mode ) {
					$regions[] = array(
						'id'     => $part['id'],
						'label'  => $part['label'],
						'group'  => $part['group'],
						'shapes' => array( $part ),
					);
				} else {
					$shapes[] = $part;
				}
			}

			if ( self::MODE_SIMPLE === $mode && $shapes ) {
				$regions[] = array(
					'id'     => $group,
					'label'  => isset( $labels[ $group ] ) ? $labels[ $group ] : $group,
					'group'  => $group,
					'shapes' => $shapes,
				);
			}
		}

		return $regions;
	}

	/**
	 * Setting keys that can supply a link for a region, best match first.
	 *
	 * `hand-right` is answered by `hand-right`, then by `arm-right`, then by
	 * `hands`, then by `arms` - so one entry can cover a whole limb.
	 *
	 * @param string $id    Region id.
	 * @param string $group Group the region belongs to.
	 * @return string[]
	 */
	public static function lookup_keys( $id, $group = '' ) {
		$keys    = array( $id );
		$plurals = BodyMap_Geometry::plurals();

		if ( $group && $group !== $id ) {
			$keys[] = $group;
		}

		foreach ( array_values( $keys ) as $key ) {
			if ( preg_match( '/^(.+)-(left|right)$/', $key, $m ) && isset( $plurals[ $m[1] ] ) ) {
				$keys[] = $plurals[ $m[1] ];
			}
		}

		return $keys;
	}

	/**
	 * Finds the configuration for a region.
	 *
	 * @param array<string, array<string, string>> $links Saved per-region settings.
	 * @param string                               $id    Region id.
	 * @param string                               $group Group the region belongs to.
	 * @return array<string, string>
	 */
	public static function resolve( $links, $id, $group = '' ) {
		foreach ( self::lookup_keys( $id, $group ) as $key ) {
			if ( ! empty( $links[ $key ] ) && is_array( $links[ $key ] ) && ! empty( $links[ $key ]['url'] ) ) {
				return $links[ $key ];
			}
		}

		// A label with no URL is still worth honouring: the region gets a
		// tooltip even though it is not clickable.
		foreach ( self::lookup_keys( $id, $group ) as $key ) {
			if ( ! empty( $links[ $key ] ) && is_array( $links[ $key ] ) ) {
				return $links[ $key ];
			}
		}

		return array();
	}

	/**
	 * Every region id a site owner can configure, grouped for the admin screen.
	 *
	 * @return array<string, array<int, array<string, string>>>
	 */
	public static function editable_regions() {
		$out    = array();
		$labels = BodyMap_Geometry::group_labels();

		foreach ( self::regions( self::MODE_DETAILED ) as $region ) {
			$group = $region['group'];

			if ( ! isset( $out[ $group ] ) ) {
				$out[ $group ] = array(
					'label' => isset( $labels[ $group ] ) ? $labels[ $group ] : $group,
					'items' => array(),
				);
			}

			$out[ $group ]['items'][] = array(
				'id'    => $region['id'],
				'label' => $region['label'],
			);
		}

		/* In simple mode a whole limb is one clickable region, so the group id
		 * itself is configurable too. It is listed first because it is what
		 * most sites will fill in. */
		foreach ( $out as $group => $data ) {
			if ( count( $data['items'] ) > 1 ) {
				array_unshift(
					$out[ $group ]['items'],
					array(
						'id'    => $group,
						'label' => sprintf(
							/* translators: %s: name of a body region, e.g. "Right arm". */
							__( '%s (whole limb)', 'interactive-body-map' ),
							$data['label']
						),
					)
				);
			}
		}

		return $out;
	}
}
