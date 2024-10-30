/**
 * Switch to Reporting Dashboard vs WordPress Dashboard
**/

jQuery( document ).ready(function() {
	jQuery( '#rst_switch' ).change( function() {
        if( jQuery( this ).is( ':checked' ) ) {
            window.location = it_rst_dashboard_url + '?rst_dashboard=1';
        } else {
	        window.location = it_rst_dashboard_url + '?rst_dashboard=0';
        }
    } );
} );