<?php
/**
 * Removes the plugin's stored data when it is deleted from the Plugins screen.
 *
 * Deactivating the plugin leaves everything in place; only an explicit delete
 * reaches this file.
 *
 * @package Interactive_Body_Map
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$bodymap_option = 'bodymap_settings';

if ( is_multisite() ) {
	$bodymap_sites = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);

	foreach ( $bodymap_sites as $bodymap_site_id ) {
		switch_to_blog( $bodymap_site_id );
		delete_option( $bodymap_option );
		restore_current_blog();
	}
} else {
	delete_option( $bodymap_option );
}
