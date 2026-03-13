<?php
/**
 * Uninstall BookIt for Cal.com.
 *
 * Runs when the plugin is deleted from the WordPress admin.
 * Removes all options and transients created by the plugin.
 *
 * @package BookIt_For_CalCom
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Delete main settings option.
delete_option( 'bookit_settings' );

// Delete transients.
delete_transient( 'bookit_event_types' );
delete_transient( 'bookit_cal_username' );

// If multisite, clean up each site.
if ( is_multisite() ) {
	$sites = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		delete_option( 'bookit_settings' );
		delete_transient( 'bookit_event_types' );
		delete_transient( 'bookit_cal_username' );
		restore_current_blog();
	}
}
