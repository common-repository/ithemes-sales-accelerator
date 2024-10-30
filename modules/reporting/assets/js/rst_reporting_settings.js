/**
* Handles plugin Settings features
**/

function it_rst_rooster_reporting_just_deactivated_js() {
	jQuery( 'a[href="admin.php?page=ithemes-sales-acc-plugin-reporting-dashboard"]' ).remove();
}

jQuery( document ).ready( function() {
		
	function it_rst_reset_grid_positions() {
		jQuery.ajax( {
	        url: rst_ajax_settings.root + 'it-sales-acc/v1/reporting/grid/reset',
	        method: 'GET',
	        beforeSend: function( xhr ){
		        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );		    	
			},
	        success:function( response ){
		        alert( 'Reset was successfull!' );
	        },
	        error: function( errorThrown ){
		        alert( 'Reset failed!' );
	        }
	    } );
    }
    
    function it_rst_reset_import() {
		jQuery.ajax( {
	        url: rst_ajax_settings.root + 'it-sales-acc/v1/reporting/importing/reset',
	        method: 'GET',
	        beforeSend: function( xhr ){
		        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );		    	
			},
	        success:function( response ){
		        alert( 'Reset was successfull!' );
	        },
	        error: function( errorThrown ){
		        alert( 'Reset failed!' );
	        }
	    } );
    }
    
    function it_rst_start_import() {
		jQuery.ajax( {
	        url: rst_ajax_settings.root + 'it-sales-acc/v1/reporting/importing/reset',
	        method: 'GET',
	        beforeSend: function( xhr ){
		        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );		    	
			},
	        success:function( response ){
		        alert( 'Import started successfully!' );
	        },
	        error: function( errorThrown ){
		        alert( 'Import start failed!' );
	        }
	    } );
    }
    
    function it_rst_delete_data() {
		jQuery.ajax( {
	        url: rst_ajax_settings.root + 'it-sales-acc/v1/reporting/importing/delete',
	        method: 'GET',
	        beforeSend: function( xhr ){
		        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );		    	
			},
	        success:function( response ){
		        alert( 'Delete was successfull!' );
	        },
	        error: function( errorThrown ){
		        alert( 'Delete failed!' );
	        }
	    } );
    }
    
	jQuery( '#it_rst_reset_grid' ).click(function() {
		it_rst_reset_grid_positions();
		var refreshBar = setInterval( function() {
			window.location = it_rst_dashboard_url + '?rst_dashboard=1';
		}, 3000 );
	} );
	
	jQuery( '#it_rst_reset_import' ).click(function() {
		
		var r = confirm( 'Are you sure you want to re-import? This action is irreversible.' );
		
		if ( r == true ) {
			it_rst_reset_import();
			var refreshBar = setInterval( function() {
				window.location = it_rst_dashboard_url + '?rst_dashboard=1';
			}, 3000 );
		} else {}
	} );
	
	jQuery( 'span#it_rst_start_import' ).click(function() {
		
		var r = confirm( 'Do you want reporting to start its initial import process?' );
		
		if ( r == true ) {
			it_rst_start_import();
			var refreshBar = setInterval( function() {
				window.location = it_rst_dashboard_url + '?rst_dashboard=1';
			}, 3000 );
		} else {}
	} );
	
	jQuery( '#it_rst_delete_data' ).click(function() {
		
		var r = confirm( 'Are you sure you want to delete all data? This action is irreversible.' );
		
		if ( r == true ) {
			it_rst_delete_data();
			var refreshBar = setInterval( function() {
				window.location = it_rst_about_url;
			}, 3000 );
		} else {}
	} );
} );