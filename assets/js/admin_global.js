/**
* Handles generic backend plugin scripting on all backend pages
**/

jQuery( document ).ready( function( $ ) {
	
	jQuery( '.it_rst_upgrade_db' ).click( 'click', function() {
		return window.confirm( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?' );
	} );
	
	jQuery( '.it_rst_upgrade_db_reset' ).click( 'click', function() {
		return window.confirm( 'Is the upgrade taking longer than expected? Click to retry.' );
	} );
} );