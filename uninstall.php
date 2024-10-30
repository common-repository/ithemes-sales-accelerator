<?php
	
// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit ();
}

global $wpdb;

$prefix   = $wpdb->prefix . 'rst_';
if ( $prefix ) {
	$tables_i = $wpdb->get_results( "Show tables like '$prefix%'", ARRAY_N );
	
	if ( $tables_i && is_array( $tables_i ) ) {
		foreach ( $tables_i as $tables ) {
			foreach ( $tables as $table ) {
				$wpdb->query( "DROP TABLE IF EXISTS $table" );
			}
		}
	}
}

// Delete orphan meta
$wpdb->query( "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '%it_rooster_%' OR meta_key LIKE '%it_rst_%'" );

// Delete options
$wpdb->query( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '%it_rooster_%'" );