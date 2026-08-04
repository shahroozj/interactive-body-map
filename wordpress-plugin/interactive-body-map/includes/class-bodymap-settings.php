<?php
/**
 * Settings screen.
 *
 * Everything lives in one option so a site's whole configuration can be
 * exported, imported or reset in a single step.
 *
 * @package Interactive_Body_Map
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin settings.
 */
final class BodyMap_Settings {

	const OPTION = 'bodymap_settings';
	const PAGE   = 'interactive-body-map';
	const GROUP  = 'bodymap_settings_group';

	/**
	 * Registers the admin hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	/**
	 * The shipped configuration.
	 *
	 * Colours default to empty: an empty colour means "whatever the stylesheet
	 * says", which lets a theme restyle the diagram in CSS without fighting an
	 * inline style.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'mode'          => BodyMap_Model::MODE_SIMPLE,
			'max_width'     => '340px',
			'align'         => 'center',
			'target'        => '',
			'title'         => '',
			'tooltip'       => 1,
			'selectable'    => 0,
			'show_disabled' => 1,
			'show_list'     => 0,
			'outline'       => 0,
			'colors'        => array(
				'fill'         => '',
				'hover'        => '',
				'active'       => '',
				'disabled'     => '',
				'stroke'       => '',
				'detail'       => '',
				'tooltip_bg'   => '',
				'tooltip_text' => '',
			),
			'links'         => array(),
		);
	}

	/**
	 * The saved configuration, with defaults filled in.
	 *
	 * @return array<string, mixed>
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		$out   = wp_parse_args( is_array( $saved ) ? $saved : array(), self::defaults() );

		$out['colors'] = wp_parse_args(
			isset( $out['colors'] ) && is_array( $out['colors'] ) ? $out['colors'] : array(),
			self::defaults()['colors']
		);

		if ( ! is_array( $out['links'] ) ) {
			$out['links'] = array();
		}

		return $out;
	}

	/**
	 * Adds the menu entry.
	 *
	 * @return void
	 */
	public static function add_page() {
		add_options_page(
			__( 'Interactive Body Map', 'interactive-body-map' ),
			__( 'Body Map', 'interactive-body-map' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Registers the option.
	 *
	 * @return void
	 */
	public static function register() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	/**
	 * Cleans submitted settings.
	 *
	 * @param mixed $input Raw form data.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$out      = $defaults;

		if ( ! is_array( $input ) ) {
			return $out;
		}

		$out['mode']      = BodyMap_Model::sanitize_mode( isset( $input['mode'] ) ? $input['mode'] : '' );
		$out['max_width'] = isset( $input['max_width'] ) ? sanitize_text_field( $input['max_width'] ) : $defaults['max_width'];
		$out['title']     = isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : '';
		$out['align']     = in_array( isset( $input['align'] ) ? $input['align'] : '', array( 'left', 'center', 'right' ), true )
			? $input['align']
			: 'center';
		$out['target']    = ( isset( $input['target'] ) && '_blank' === $input['target'] ) ? '_blank' : '';

		foreach ( array( 'tooltip', 'selectable', 'show_disabled', 'show_list', 'outline' ) as $flag ) {
			$out[ $flag ] = empty( $input[ $flag ] ) ? 0 : 1;
		}

		foreach ( array_keys( $defaults['colors'] ) as $key ) {
			$value              = isset( $input['colors'][ $key ] ) ? trim( $input['colors'][ $key ] ) : '';
			$out['colors'][ $key ] = $value ? (string) sanitize_hex_color( $value ) : '';
		}

		$out['links'] = array();

		if ( isset( $input['links'] ) && is_array( $input['links'] ) ) {
			foreach ( $input['links'] as $id => $link ) {
				if ( ! is_array( $link ) ) {
					continue;
				}

				$url    = isset( $link['url'] ) ? trim( $link['url'] ) : '';
				$label  = isset( $link['label'] ) ? sanitize_text_field( $link['label'] ) : '';
				$target = ( isset( $link['target'] ) && '_blank' === $link['target'] ) ? '_blank' : '';

				if ( '' === $url && '' === $label ) {
					continue;
				}

				$out['links'][ sanitize_key( $id ) ] = array(
					// Relative paths such as /services/knee are common and valid.
					'url'    => $url ? esc_url_raw( $url, array( 'http', 'https', 'mailto', 'tel' ) ) : '',
					'label'  => $label,
					'target' => $target,
				);
			}
		}

		return $out;
	}

	/**
	 * Loads the admin stylesheet, the colour picker and the preview script.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public static function assets( $hook ) {
		if ( 'settings_page_' . self::PAGE !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'interactive-body-map' );
		wp_enqueue_style(
			'bodymap-admin',
			BODYMAP_URL . 'admin/css/admin.css',
			array( 'wp-color-picker' ),
			BODYMAP_VERSION
		);

		wp_enqueue_script( 'interactive-body-map' );
		wp_enqueue_script(
			'bodymap-admin',
			BODYMAP_URL . 'admin/js/admin.js',
			array( 'jquery', 'wp-color-picker', 'interactive-body-map' ),
			BODYMAP_VERSION,
			true
		);

		wp_localize_script(
			'bodymap-admin',
			'BODYMAP_ADMIN',
			array(
				'copied' => __( 'Copied', 'interactive-body-map' ),
				'copy'   => __( 'Copy shortcode', 'interactive-body-map' ),
			)
		);
	}

	/**
	 * Draws the settings screen.
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = self::get();
		$name     = self::OPTION;
		?>
		<div class="wrap bodymap-admin">
			<h1><?php esc_html_e( 'Interactive Body Map', 'interactive-body-map' ); ?></h1>
			<p class="bodymap-admin__intro">
				<?php esc_html_e( 'Point each part of the body at a page, then drop the shortcode wherever you want the diagram to appear.', 'interactive-body-map' ); ?>
			</p>

			<div class="bodymap-admin__layout">
				<form method="post" action="options.php" class="bodymap-admin__main">
					<?php settings_fields( self::GROUP ); ?>

					<h2><?php esc_html_e( 'Links', 'interactive-body-map' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Left and right are the figure\'s own left and right, so the right arm is the one drawn on your left. Fill in a "whole limb" row to cover every segment of that limb at once; a more specific row always wins.', 'interactive-body-map' ); ?>
					</p>

					<?php foreach ( BodyMap_Model::editable_regions() as $group_id => $group ) : ?>
						<table class="widefat striped bodymap-links">
							<thead>
								<tr>
									<th scope="col" class="bodymap-links__name"><?php echo esc_html( $group['label'] ); ?></th>
									<th scope="col"><?php esc_html_e( 'Link', 'interactive-body-map' ); ?></th>
									<th scope="col" class="bodymap-links__label"><?php esc_html_e( 'Tooltip', 'interactive-body-map' ); ?></th>
									<th scope="col" class="bodymap-links__target"><?php esc_html_e( 'New tab', 'interactive-body-map' ); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ( $group['items'] as $item ) : ?>
								<?php
								$id     = $item['id'];
								$link   = isset( $settings['links'][ $id ] ) ? $settings['links'][ $id ] : array();
								$url    = isset( $link['url'] ) ? $link['url'] : '';
								$label  = isset( $link['label'] ) ? $link['label'] : '';
								$target = isset( $link['target'] ) ? $link['target'] : '';
								?>
								<tr>
									<th scope="row">
										<label for="bodymap-url-<?php echo esc_attr( $id ); ?>">
											<?php echo esc_html( $item['label'] ); ?>
										</label>
										<code><?php echo esc_html( $id ); ?></code>
									</th>
									<td>
										<input type="text"
											id="bodymap-url-<?php echo esc_attr( $id ); ?>"
											class="regular-text code"
											name="<?php echo esc_attr( $name ); ?>[links][<?php echo esc_attr( $id ); ?>][url]"
											value="<?php echo esc_attr( $url ); ?>"
											placeholder="/example-page">
									</td>
									<td>
										<input type="text"
											class="regular-text"
											name="<?php echo esc_attr( $name ); ?>[links][<?php echo esc_attr( $id ); ?>][label]"
											value="<?php echo esc_attr( $label ); ?>"
											placeholder="<?php echo esc_attr( $item['label'] ); ?>">
									</td>
									<td class="bodymap-links__target">
										<input type="checkbox"
											name="<?php echo esc_attr( $name ); ?>[links][<?php echo esc_attr( $id ); ?>][target]"
											value="_blank"
											<?php checked( '_blank', $target ); ?>>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endforeach; ?>

					<h2><?php esc_html_e( 'Appearance', 'interactive-body-map' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="bodymap-mode"><?php esc_html_e( 'Regions', 'interactive-body-map' ); ?></label></th>
							<td>
								<select id="bodymap-mode" name="<?php echo esc_attr( $name ); ?>[mode]">
									<?php foreach ( BodyMap_Model::modes() as $value => $text ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['mode'], $value ); ?>>
											<?php echo esc_html( $text ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Simple treats each arm and leg as one link. Detailed splits them into shoulder, upper arm, forearm and hand, and thigh, knee, lower leg and foot.', 'interactive-body-map' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="bodymap-max-width"><?php esc_html_e( 'Maximum width', 'interactive-body-map' ); ?></label></th>
							<td>
								<input type="text" id="bodymap-max-width" class="small-text"
									name="<?php echo esc_attr( $name ); ?>[max_width]"
									value="<?php echo esc_attr( $settings['max_width'] ); ?>">
								<p class="description"><?php esc_html_e( 'Any CSS length, for example 340px or 100%. The figure scales to fit and never overflows its column.', 'interactive-body-map' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Alignment', 'interactive-body-map' ); ?></th>
							<td>
								<?php
								$aligns = array(
									'left'   => __( 'Left', 'interactive-body-map' ),
									'center' => __( 'Centre', 'interactive-body-map' ),
									'right'  => __( 'Right', 'interactive-body-map' ),
								);
								foreach ( $aligns as $value => $text ) :
									?>
									<label class="bodymap-admin__radio">
										<input type="radio" name="<?php echo esc_attr( $name ); ?>[align]"
											value="<?php echo esc_attr( $value ); ?>"
											<?php checked( $settings['align'], $value ); ?>>
										<?php echo esc_html( $text ); ?>
									</label>
								<?php endforeach; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Behaviour', 'interactive-body-map' ); ?></th>
							<td>
								<?php
								$flags = array(
									'tooltip'       => __( 'Show a tooltip with the region name', 'interactive-body-map' ),
									'selectable'    => __( 'Keep the clicked region highlighted', 'interactive-body-map' ),
									'show_disabled' => __( 'Draw regions that have no link', 'interactive-body-map' ),
									'show_list'     => __( 'Add a plain list of the same links below the figure', 'interactive-body-map' ),
									'outline'       => __( 'Outline style: fill regions only on hover', 'interactive-body-map' ),
								);
								foreach ( $flags as $flag => $text ) :
									?>
									<label class="bodymap-admin__check">
										<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $flag ); ?>]"
											value="1" <?php checked( 1, (int) $settings[ $flag ] ); ?>>
										<?php echo esc_html( $text ); ?>
									</label><br>
								<?php endforeach; ?>
								<label class="bodymap-admin__check">
									<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[target]"
										value="_blank" <?php checked( '_blank', $settings['target'] ); ?>>
									<?php esc_html_e( 'Open every link in a new tab', 'interactive-body-map' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Colours', 'interactive-body-map' ); ?></th>
							<td>
								<?php
								$colours = array(
									'fill'         => __( 'Body', 'interactive-body-map' ),
									'hover'        => __( 'Hover', 'interactive-body-map' ),
									'active'       => __( 'Selected', 'interactive-body-map' ),
									'disabled'     => __( 'Unlinked', 'interactive-body-map' ),
									'stroke'       => __( 'Outline', 'interactive-body-map' ),
									'detail'       => __( 'Contour lines', 'interactive-body-map' ),
									'tooltip_bg'   => __( 'Tooltip background', 'interactive-body-map' ),
									'tooltip_text' => __( 'Tooltip text', 'interactive-body-map' ),
								);
								foreach ( $colours as $key => $text ) :
									?>
									<p class="bodymap-admin__colour">
										<label for="bodymap-colour-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $text ); ?></label>
										<input type="text" id="bodymap-colour-<?php echo esc_attr( $key ); ?>"
											class="bodymap-colour-field"
											data-bodymap-var="<?php echo esc_attr( $key ); ?>"
											name="<?php echo esc_attr( $name ); ?>[colors][<?php echo esc_attr( $key ); ?>]"
											value="<?php echo esc_attr( $settings['colors'][ $key ] ); ?>">
									</p>
								<?php endforeach; ?>
								<p class="description"><?php esc_html_e( 'Leave a colour empty to inherit it from your theme stylesheet. The figure itself never paints a background, so it always sits on your page colour.', 'interactive-body-map' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="bodymap-title"><?php esc_html_e( 'Accessible name', 'interactive-body-map' ); ?></label></th>
							<td>
								<input type="text" id="bodymap-title" class="regular-text"
									name="<?php echo esc_attr( $name ); ?>[title]"
									value="<?php echo esc_attr( $settings['title'] ); ?>"
									placeholder="<?php esc_attr_e( 'Interactive body map', 'interactive-body-map' ); ?>">
								<p class="description"><?php esc_html_e( 'Read out by screen readers when the figure receives focus.', 'interactive-body-map' ); ?></p>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>

				<aside class="bodymap-admin__side">
					<div class="bodymap-admin__card">
						<h2><?php esc_html_e( 'Preview', 'interactive-body-map' ); ?></h2>
						<div class="bodymap-admin__preview" id="bodymap-preview"
							data-note="<?php esc_attr_e( 'Save to see the new region layout.', 'interactive-body-map' ); ?>">
							<?php
							$preview = $settings;
							// Links are irrelevant to a colour preview; light every region up.
							$preview['links']         = array();
							$preview['show_list']     = 0;
							$preview['show_disabled'] = 1;
							echo BodyMap_Render::diagram( $preview ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup is escaped as it is built.
							?>
						</div>
						<p class="description"><?php esc_html_e( 'Colour changes show here straight away. Save to apply them to your site.', 'interactive-body-map' ); ?></p>
					</div>

					<div class="bodymap-admin__card">
						<h2><?php esc_html_e( 'Shortcode', 'interactive-body-map' ); ?></h2>
						<p><?php esc_html_e( 'Paste this into any post, page or widget:', 'interactive-body-map' ); ?></p>
						<p><code class="bodymap-admin__shortcode">[body_map]</code>
							<button type="button" class="button button-small bodymap-copy" data-clipboard="[body_map]">
								<?php esc_html_e( 'Copy shortcode', 'interactive-body-map' ); ?>
							</button>
						</p>
						<p class="description">
							<?php esc_html_e( 'Every setting above can be overridden per shortcode, for example:', 'interactive-body-map' ); ?>
						</p>
						<p><code>[body_map mode="detailed" max_width="260px" hover="#0ea5e9"]</code></p>
						<p><code>[body_map head="/head" chest="/chest" arms="/arms"]</code></p>
						<p class="description">
							<?php esc_html_e( 'In the block editor, search for the "Body Map" block instead.', 'interactive-body-map' ); ?>
						</p>
					</div>
				</aside>
			</div>
		</div>
		<?php
	}
}
