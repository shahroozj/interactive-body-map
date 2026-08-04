<?php
/**
 * The "Body Map" block.
 *
 * Rendered server-side, so the block preview in the editor and the published
 * page come from exactly the same code as the shortcode.
 *
 * @package Interactive_Body_Map
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block registration.
 */
final class BodyMap_Block {

	const NAME = 'interactive-body-map/body-map';

	/**
	 * Registers the block, if the installed WordPress supports blocks.
	 *
	 * @return void
	 */
	public static function register() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		wp_register_script(
			'bodymap-block',
			BODYMAP_URL . 'admin/js/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
			BODYMAP_VERSION,
			true
		);

		$settings = BodyMap_Settings::get();

		wp_localize_script(
			'bodymap-block',
			'BODYMAP_BLOCK',
			array(
				'modes'       => BodyMap_Model::modes(),
				'defaultMode' => $settings['mode'],
				'settingsUrl' => admin_url( 'options-general.php?page=' . BodyMap_Settings::PAGE ),
			)
		);

		register_block_type(
			self::NAME,
			array(
				'api_version'     => 2,
				'title'           => __( 'Body Map', 'interactive-body-map' ),
				'category'        => 'widgets',
				'icon'            => 'universal-access',
				'editor_script'   => 'bodymap-block',
				'editor_style'    => 'interactive-body-map',
				'style'           => 'interactive-body-map',
				'render_callback' => array( __CLASS__, 'render' ),
				'attributes'      => array(
					'mode'         => array(
						'type'    => 'string',
						'default' => '',
					),
					'maxWidth'     => array(
						'type'    => 'string',
						'default' => '',
					),
					'align'        => array(
						'type'    => 'string',
						'default' => '',
					),
					'tooltip'      => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'selectable'   => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'showDisabled' => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showList'     => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'outline'      => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			)
		);
	}

	/**
	 * Renders the block.
	 *
	 * Empty attributes fall through to the saved settings, so a block dropped
	 * in with no configuration looks like the rest of the site.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string
	 */
	public static function render( $attributes ) {
		$settings = BodyMap_Settings::get();
		$map      = array(
			'mode'         => 'mode',
			'maxWidth'     => 'max_width',
			'align'        => 'align',
			'tooltip'      => 'tooltip',
			'selectable'   => 'selectable',
			'showDisabled' => 'show_disabled',
			'showList'     => 'show_list',
			'outline'      => 'outline',
		);

		foreach ( $map as $attribute => $key ) {
			if ( ! isset( $attributes[ $attribute ] ) ) {
				continue;
			}

			if ( is_bool( $attributes[ $attribute ] ) ) {
				$settings[ $key ] = $attributes[ $attribute ] ? 1 : 0;
			} elseif ( '' !== $attributes[ $attribute ] ) {
				$settings[ $key ] = sanitize_text_field( $attributes[ $attribute ] );
			}
		}

		return BodyMap_Render::diagram( $settings );
	}
}
