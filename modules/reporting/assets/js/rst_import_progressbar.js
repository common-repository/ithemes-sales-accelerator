/**
* Handles import progressbar
**/

jQuery( document ).ready( function() {
	
	function it_rst_get_current_progress() {
	jQuery.ajax( {
        url: rst_ajax_settings.root + 'it-sales-acc/v1/reporting/importing',
        method: 'GET',
        beforeSend: function( xhr ) {
	        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );		    	
		},
        success:function( response ){
	        var response_data = response.data;
	        var time_string   = response_data.time_string;

	        var progress = response_data.progress;
			jQuery( '#it_rooster_reporting_import_progressbar_top' ).attr( 'aria-valuenow', progress ).css( 'width',progress + '%' ).html( progress + '%' );
			jQuery( '#it_rooster_reporting_import_progressbar_bottom' ).attr( 'aria-valuenow', progress ).css( 'width',progress + '%' ).html( progress + '%' );
			jQuery( '.txt_remaining' ).text( time_string );
			
	        if ( progress >= 100 ) {
		        var redirect = it_admin_settings.dashboard_url;
		        if ( redirect ) {
			        var refreshBar = setInterval( function() {
						window.location.replace(redirect);
					}, 5000 );
		        }
	        }
        },
        error: function( errorThrown ){}
      } );
    }
      
	if ( jQuery( '.it_rooster_reporting_progressbar' ).length ) {
		var progress = jQuery( '#it_rooster_reporting_import_progressbar_top' ).attr( 'aria-valuenow' );
		if( progress < 100 ) {
			var refreshBar = setInterval( function() {
				it_rst_get_current_progress();
			}, 20000 );
		}	
	}
} );