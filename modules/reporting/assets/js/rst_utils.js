/**
* Handles reporting free util features
**/

jQuery( document ).ready(function() {
		
	/*
	 * Initialize variables
	*/
	var rst_dashboard, getUrlParameter;
	var intervalID = [];
	
	/*
	 * DataTable
	*/
	jQuery( '.it_table_search' ).on( 'keyup', function () {
		var table_name = jQuery(this).parent().attr('id');
		tableData 	   = jQuery( '#table-' + table_name + ' .it_rst_dataTable' ).DataTable();
	    tableData.search( this.value ).draw();
	    jQuery( '.dataTables_scrollBody thead' ).css( 'display', 'none' );
	    jQuery( '.dataTables_scrollBody tfoot' ).css( 'visibility', 'hidden' );
	} );
	
	jQuery( '.it_rst_dataTable_head' ).live( 'click', function() {
		jQuery( '.dataTables_scrollBody thead' ).css( 'display', 'none' );
	    jQuery( '.dataTables_scrollBody tfoot' ).css( 'visibility', 'hidden' );
	});
	
	
	/*
	 * Resize Window
	*/
	jQuery( window ).resize( function() {
		jQuery( '.dataTables_scrollHead table' ).css( 'width', '100%' );
		tableData = jQuery( '.it_rst_dataTable' ).DataTable();
	    jQuery( '.dataTables_scrollBody tfoot' ).css( 'visibility', 'hidden' );
	    setTimeout( function() { jQuery( '.dataTables_scrollBody .it_rst_dataTable_head' ).css( 'display', 'none' ); }, 10 );
	    
	    var colCount = 0;
	    jQuery( '.it_rst_dataTable_head th' ).each( function () {
	        if (jQuery( this ).attr( 'colspan' ) ) {
	            colCount += +jQuery( this ).attr( 'colspan' );
	        } else {
	            colCount++;
	        }
	    } );
	} );

	/*
	 * Function to return the Url Parameter
	*/
	getUrlParameter = function getUrlParameter( sParam ) {
	    var sPageURL = decodeURIComponent( window.location.search.substring( 1 ) ),
	        sURLVariables = sPageURL.split( '&' ),
	        sParameterName,
	        i;
	
	    for ( i = 0; i < sURLVariables.length; i++ ) {
	        sParameterName = sURLVariables[i].split( '=' );
	
	        if ( sParameterName[0] === sParam ) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};
	
	/*
	 * Prevent the Report Widget of being closed
	*/
	jQuery( '#rst-main-widget' ).click(function( e ) {
		if ( jQuery( '#rst-main-widget' ).hasClass( 'closed' ) == true ) {
			e.preventDefault();
			jQuery( this ).removeClass( 'closed' );
		}
	} );
	
	jQuery( '#rst-main-widget' ).removeClass( 'closed' );
		
	/*
	 * Check if variable exist and select Item 'Dashboard' on Menu
	*/
	rst_dashboard = getUrlParameter( 'rst_dashboard' );
	if ( rst_dashboard == '1' ) {
		jQuery( '.wp-first-item' ).addClass( 'current' );
	}
	
	/*
	 * Switch to Reporting Dashboard vs WordPress Dashboard
	*/
	jQuery( '#rst_switch').change( function() {
        if( jQuery( this ).is( ':checked' ) ) {
            window.location = admin_url + '?rst_dashboard=1';
        } else {
	        window.location = admin_url + '?rst_dashboard=0';
        }   
    } );
    
    /*
	 * Redirect to WooCommerce Orders Page
	*/
	jQuery( '.everything' ).click( function() {
		window.location = admin_url + 'edit.php?post_type=shop_order';
    } ); 
    
    /*
	 * Function to load the saved positions of the Grid
	*/
	intervalManager = function ( flag, animate, time ) {

		if( flag ) {
			intervalID[animate] = setInterval(animate, time);
		}
		else { 
			clearInterval(animate);
		}	
	}
	
	/*
	 * Hover on th element of table
	*/
	jQuery( ".sorting" ).hover(
	  function() {
	    jQuery( this ).addClass( "hover" );
	  }, function() {
	    jQuery( this ).removeClass( "hover" );
	  }
	);

});