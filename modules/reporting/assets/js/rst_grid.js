/**
* Handles dashboard grid
**/

var startDate, endDate, boxContentAjax, tableContentAjax, chartContentAjax;	
var chartFunctions = [];
var datatable_settings = [];

jQuery( document ).ready(function() {   
	
	var getUrlParameter = function getUrlParameter(sParam) {
	    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;
	
	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');
	
	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : sParameterName[1];
	        }
	    }
	};
	
	jQuery.xhrPool = []; // array of uncompleted requests
		jQuery.xhrPool.abortAll = function() { // our abort function
		    jQuery( this ).each( function( idx, xhr ) { 
		        xhr.abort();
		    } );
		    jQuery.xhrPool.length = 0
	};

	/*
	 * Function to update the box content
	*/
    updateBoxContent = function( boxID, name, time, chart, borderGraph ) {
	    
	    if (startDate && endDate) {
	    	paramsDate = '&dateStart=' + startDate + '&dateEnd=' + endDate;
	    }
	    else {
	    	paramsDate = '';
	    }
	    
	    var id = '';
	    if ( getUrlParameter( 'customer_id' ) !== undefined ) {
	    	id = '&customer_id=' + getUrlParameter( 'customer_id' );
	    }
	    else if ( getUrlParameter( 'product_id' ) !== undefined ) {
	    	id = '&product_id=' + getUrlParameter( 'product_id' );
	    }
	    	    
	    boxContentAjax = jQuery.ajax({
	        url: rst_ajax_settings.root + 'it-sales-acc/v1/reporting/boxcontent?renderHtml=1&slug=' + name + paramsDate + id, 
		    method: 'GET',
	        beforeSend: function( xhr ) {
		        
		        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );
		        
		        // Activate animation
		        jQuery( '.box .' + boxID + ' .spinner-wrapper' ).css( 'display', 'flex' );
		        
		    	// Clear interval to not repeat while the data is not loaded
		    	intervalManager( false, chartFunctions[name] );
		    	
		   	},
	        success: function( response ) { 
		        
		        // Replace the updated data on box
		        jQuery( '.box .' + boxID + ' .grid-stack-item-content .content' ).html( response.data.html );
		        
		        // Deactivate animation
		        jQuery( '.box .' + boxID + ' .spinner-wrapper' ).css( 'display', 'none' );
		        jQuery( '.box .' + boxID + ' .spinner-wrapper2' ).css( 'display', 'none' );
		        
		        // Execute again the interval
				intervalManager( true, chartFunctions[name], time );
				if ( chart ) {
					createChart( name, response.data.values, borderGraph );
				}
	        }
	    } );
    }
    
     /*
	 * Function to update the table content
	*/
    updateTableContent = function( tableID, name, time ) {
	    	    
	    if ( startDate && endDate ) {
	    	paramsDate = '&dateStart=' + startDate + '&dateEnd=' + endDate;
	    }
	    else {
	    	paramsDate = '';
	    }
	    
	    var id = '';
	    if ( getUrlParameter( 'customer_id' ) !== undefined ) {
	    	id = '&customer_id=' + getUrlParameter( 'customer_id' );
	    }
	    else if ( getUrlParameter( 'product_id' ) !== undefined ) {
	    	id = '&product_id=' + getUrlParameter( 'product_id' );
	    }
	    	    
		tableContentAjax = jQuery.ajax( {
	        url: rst_ajax_settings.root + 'it-sales-acc/v1/reporting/tablecontent?renderHtml=1&slug=' + name + paramsDate + '&body=0' + id,
		    method: 'GET',
	        beforeSend: function( xhr ){
		        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );
		        
		        // Activate animation
		        jQuery( '.table-content .' + tableID + ' .spinner-wrapper' ).css( 'display', 'flex' );
		        jQuery( '.table-content .' + tableID + ' .content.yes' ).css( 'opacity', '.2' );
		        
		    	// Clear interval to not repeat while the data is not loaded
		    	intervalManager(false, chartFunctions[name]);
		   	},
	        success: function( response ) { 
		        
		        // Replace the updated data on box
		        jQuery( '.table-content .' + tableID + ' .grid-stack-item-content .content' ).html( response.data.html );
		        
		        // Deactivate animation
		        jQuery( '.table-content .' + tableID + ' .spinner-wrapper' ).css( 'display', 'none' );
		        jQuery( '.table-content .' + tableID + ' .spinner-wrapper2' ).css( 'display', 'none' );
		        jQuery( '.table-content .' + tableID + ' .content.yes' ).css( 'opacity', '1' );
		        
		        var sorting = true;
		        
		        // Execute again the interval
				intervalManager( true, chartFunctions[name], time );
				
				if ( jQuery( '#table-' + name ).hasClass( 'it_rst_no_sorting' ) ) {
					var sorting = false;
				}
				
				var table = jQuery( '#table-' + name + ' .it_rst_dataTable' ).DataTable( {
			        'scrollY'			: '300px',
			        'processing'		: true,
			        'serverSide'		: true,
			        'ajax': {
					    'url': rst_ajax_settings.root + 'it-sales-acc/v1/reporting/tablecontent?renderHtml=1&slug=' + name + paramsDate + id,
					    'type': 'GET',
					    'beforeSend': function ( xhr ) {
					        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );
					    },
					},
					"preDrawCallback": function( settings ) {
					    jQuery( '.table-content .' + tableID + ' .spinner-wrapper' ).css( 'display', 'flex' );
				        jQuery( '.table-content .' + tableID + ' .content.yes' ).css( 'opacity', '.2' );
				        datatable_settings = settings.oAjaxData;
					},
					'fnDrawCallback' : function( oSettings ) {
					  jQuery( '.table-content .' + tableID + ' .spinner-wrapper' ).css( 'display', 'none' );
					  jQuery( '.table-content .' + tableID + ' .spinner-wrapper2' ).css( 'display', 'none' );
				      jQuery( '.table-content .' + tableID + ' .content.yes' ).css( 'opacity', '1' );
				    },
			        'scrollCollapse'	: false,
			        'info'				: false, 
			        'searching'			: true,
			        'bLengthChange'		: false,
			        'responsive'		: false,
			        'autoWidth'			: true,
			        "ordering"			: sorting,
			        'language': {
				      'emptyTable': 'No records found.'
				    },
				    'order': [[ response.data.order, response.data.order_by ]]
				    
			    } );
			    			    
			    var search = jQuery( '#it_table_' + name + '_search' ).val();
			    table.search( search ).draw();

			    jQuery( '.dataTables_scrollBody .it_rst_dataTable_head' ).css( 'display', 'none' );
			    jQuery( '.dataTables_scrollHead .dataTables_scrollHeadInner' ).css( 'width', 'auto' );
			    jQuery( '.dataTables_scrollHead .it_rst_dataTable_head.dataTable' ).css( 'width', 'auto' );
			    
	        }
	    } );
    }
    
    /*
	 * Function to update the chart content
	*/
    updateChartContent = function(tableID,name,time,type) {
	    
	    if ( startDate && endDate ) {
	    	paramsDate = '&dateStart=' + startDate + '&dateEnd=' + endDate;
	    }
	    else {
	    	paramsDate = '';
	    }
	    
	    var id = '';
	    if ( getUrlParameter( 'customer_id' ) !== undefined ) {
	    	id = '&customer_id=' + getUrlParameter( 'customer_id' );
	    }
	    else if ( getUrlParameter( 'product_id' ) !== undefined ) {
	    	id = '&product_id=' + getUrlParameter( 'product_id' );
	    }
	    
		chartContentAjax = jQuery.ajax( {
	        url: rst_ajax_settings.root + 'it-sales-acc/v1/reporting/chartcontent?renderHtml=1&slug=' + name + paramsDate + id,
		    method: 'GET',
	        beforeSend: function( xhr ) {
		        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );
		        
		        // Activate animation
		        jQuery( '.chart .' + tableID + ' .spinner-wrapper' ).css( 'display', 'flex' );
		        jQuery( '.chart .' + tableID + ' .content' ).css( 'opacity', '.2' );
		        
		    	// Clear interval to not repeat while the data is not loaded
		    	intervalManager( false, chartFunctions[name] );
		   	},
	        success: function( response ) { 
		        
		        // Replace the updated data on box
		        jQuery( '.chart .' + tableID + ' .grid-stack-item-content .content' ).html( response.data.html );
		        
		        // Deactivate animation
		        jQuery( '.chart .' + tableID + ' .spinner-wrapper' ).css( 'display', 'none' );
		        jQuery( '.chart .' + tableID + ' .content' ).css( 'opacity', '1' );
		        
		        // Execute again the interval
				intervalManager( true, chartFunctions[name], time );
				if ( type == 'donut' ) {
					createChartDonut( name, response.data.values );
					jQuery( '.chart .chart-donut .selected_date' ).html( jQuery( '.selected_date' ).html() );
				}
				else {
					createChartAxis( name, response.data.values );
					jQuery( '.chart .chart-lines .selected_date' ).html( jQuery( '.selected_date' ).html() );
				}
	        }
	    } );
    }
    
    /*
	 * Function to build the main Grid
	*/
    buildGrid = function () {	
	    			
		var options = {
	        verticalMargin: 20,
	        verticalMarginUnit: 'px',
	        animate: true,
	    };
		    
	    jQuery( '.grid-stack' ).gridstack( options ); 
	    
	    // Force the margin of this two grids   
		jQuery( '#8 .grid-stack-item-content' ).css( 'padding', '0' );
		jQuery( '#14 .grid-stack-item-content' ).css( 'padding', '0' );
		
	}
	buildGrid();
	
	/*
	 * Function to save the positions of the Grid
	*/
	var isSending = false;
	
    saveGrid = function ( save ) {}
	
	/*
	 * Function to run the Functions of Grid
	*/
	runFunctions = function( dateFilter ) {
		
		var chartFunctions = [];
		var intervalID = [];
		
		var items = jQuery( '.grid-stack' ).find( '.grid-stack-item' ); 
        jQuery.each( items, function ( key, gridItem ) {
	        if ( gridItem != undefined ) {
				var contentName 	= jQuery( gridItem ).attr( 'data-gs-name' );
				var contentFunction = jQuery( gridItem ).attr( 'data-gs-function' );
				var contentTimer 	= jQuery( gridItem ).attr( 'data-gs-timer' );
				var contentType 	= jQuery( gridItem ).attr( 'data-gs-type' );
				var contentChart 	= jQuery( gridItem ).attr( 'data-gs-chart' );
				var update;
				var contentLiveUpdate  = jQuery( gridItem ).attr( 'data-gs-liveupdate' );
				var contentBorderGraph = jQuery( gridItem ).attr( 'data-gs-borderGraph' );
				var contentTypeChart   = jQuery( gridItem ).attr( 'data-gs-typeChart' );
				
				if ( contentName && contentFunction ) {
					
					if ( contentType == 'box' ) {
						if ( contentChart == '1' ) {
							update = function() { updateBoxContent( contentName, contentFunction, contentTimer, true, contentBorderGraph ); };
						} else {
							update = function() { updateBoxContent( contentName, contentFunction, contentTimer ); };
						}
					} else if ( contentType == 'table' ) {
						update = function() { updateTableContent( contentName, contentFunction, contentTimer ); };
					} else {
						update = function() { updateChartContent( contentName, contentFunction, contentTimer, contentTypeChart ); };
					}
					
					update();
					
					if ( contentLiveUpdate == '1' ) {
						chartFunctions[contentFunction] = update;
						intervalManager( true, chartFunctions[contentFunction], contentTimer );
					}
				} 		            	       
	        }
        }, this );
	}

	/*
	 * Function that occurs on Load Grid
	*/
	loadGridInit = function( status ) {

		if ( typeof serializedData !== 'undefined' ) {
			var serializedArr = JSON.parse( serializedData );			
			
	        var items = jQuery( '.grid-stack' ).find( '.grid-stack-item' ); 
	        jQuery.each( items, function ( key, gridItem ) {
		        if (gridItem != undefined) {

			        // move boxes
			        jQuery( gridItem ).attr( 'data-gs-x', serializedArr[key].x );
		            jQuery( gridItem ).attr( 'data-gs-y', serializedArr[key].y );
		            jQuery( gridItem ).attr( 'data-gs-width', serializedArr[key].width );
		            jQuery( gridItem ).attr( 'data-gs-height', serializedArr[key].height );
		        }

	        }, this );
	        
	        if ( status == true ) {
			    runFunctions(false);
			}
		}
		resizeGrid();
	}
	if ( typeof serializedData !== 'undefined' ) {
		loadGridInit( true );
	} else {
		runFunctions( false );
        saveGrid( true );
	}
    
});