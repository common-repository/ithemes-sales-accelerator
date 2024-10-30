/**
* Handles generic backend plugin scripting on plugin pages
**/

jQuery( document ).ready( function( $ ) {
	
	jQuery( '.add_option' ).click( function() {
		jQuery( '.' + jQuery( this ).get( 0 ).id + ' .selectize-input' ).click();
	} );

	jQuery( 'svg.updated' ).click( function() {
		jQuery( '.updated #circle' ).css( 'fill', '#4DC2F0' );
		jQuery( '.creation #circle' ).css( 'fill', '#FFFFFF' );
		jQuery( '.it_rooster_updated_date' ).prop( 'checked', true );
		jQuery( '.it_rooster_creation_date' ).prop( 'checked', false );
	} );

	jQuery( 'svg.creation' ).click( function() {
		jQuery( '.creation #circle').css( 'fill', '#4DC2F0' );
		jQuery( '.updated #circle').css( 'fill', '#FFFFFF' );
		jQuery( '.it_rooster_creation_date').prop( 'checked', true );
		jQuery( '.it_rooster_updated_date').prop( 'checked', false );
	} );

	jQuery( 'svg.gross').click(function() {
		jQuery( '.gross #circle' ).css( 'fill','#4DC2F0' );
		jQuery( '.net #circle' ).css( 'fill','#FFFFFF' );
		jQuery( '.total_sales_gross' ).prop( 'checked',true );
		jQuery( '.total_sales_net' ).prop( 'checked',false );
	}) ;
	
	jQuery( 'svg.net').click(function() {
		jQuery( '.gross #circle' ).css( 'fill','#FFFFFF' );
		jQuery( '.net #circle' ).css( 'fill','#4DC2F0' );
		jQuery( '.total_sales_gross' ).prop( 'checked', false );
		jQuery( '.total_sales_net' ).prop( 'checked', true );
	} );
	
	jQuery( '.svg_circle').click(function() {
		jQuery( '.svg_circle #circle' ).css( 'fill','#FFFFFF' );
		jQuery( '.net #circle' ).css( 'fill','#4DC2F0' );
		jQuery( '.total_sales_gross' ).prop( 'checked',false );
		jQuery( '.total_sales_net' ).prop( 'checked',true );
	}) ;
	
	jQuery( '.it_rst_svg_features_circle' ).click(function() {
		var element_key = jQuery( this ).data( 'element-key' );
		var checkbox = jQuery( '#it_rooster_reporting_features_' + element_key + '_live' );
				
		if ( checkbox.is( ':checked') ) {
			jQuery( checkbox ).prop( 'checked', false );
			jQuery( '.it_rst_svg_features_circle #circle_' + element_key + '_live' ).css( 'fill','#FFFFFF' );
		}
		else{
			jQuery( checkbox ).prop( 'checked', true );
			jQuery( '.it_rst_svg_features_circle #circle_' + element_key + '_live' ).css( 'fill','#4DC2F0' );
		}		
	} );
	
	jQuery( '.notifications_desc').width(jQuery( '.form-table tr' ).width() );
	
	jQuery( window ).on( 'resize', function() {
	    jQuery( '.notifications_desc' ).width( jQuery( '.form-table tr' ).width() );
	} );
	
	jQuery( '.nice-select').niceSelect();
	
	setTimeout(function(){jQuery( '#setting-error-settings_updated.updated').fadeOut( 'slow'); },2000);
	
	if( jQuery( '.it_settings_free_selectize' ).length ) {
		jQuery( '.it_settings_free_selectize' ).selectize( {
		    plugins: ['remove_button'],
            create: function(input) {
		        return {
		            value: input,
		            text: input
		        }
		    },
		    hideSelected: true
		} );
	}
	
	if( jQuery( '.it_settings_table_selectize' ).length ) {
		
		var maxItems = 1;
		if( jQuery( '.it_settings_table_selectize' ).hasClass( 'it_no_limit_selectize' ) ) {
			maxItems = null;
		}
		
		jQuery( '.it_settings_table_selectize' ).selectize( {
		    maxItems: maxItems,
		    plugins: ['remove_button'],
		    valueField: 'key',
		    labelField: 'value',
		    searchField: 'key',
		    sortField: 'value',
		    options: it_admin_settings.table,
		    create: false,
		    hideSelected: true
		} );
	}
	
	if( jQuery( '.it_settings_charts_selectize' ).length ) {
		
		var maxItems = 2;
		if( jQuery( '.it_settings_charts_selectize' ).hasClass( 'it_no_limit_selectize' ) ) {
			maxItems = null;
		}
		
		jQuery( '.it_settings_charts_selectize' ).selectize( {
		    maxItems: maxItems,
		    plugins: ['remove_button','drag_drop'],
		    valueField: 'key',
		    labelField: 'value',
		    searchField: 'key',
		    sortField: 'value',
		    options: it_admin_settings.charts,
		    create: false,
		    hideSelected: true
		} );
	}
	
	if( jQuery( '.it_settings_overview_selectize' ).length ) {
		
		var maxItems = 9;
		if( jQuery( '.it_settings_overview_selectize' ).hasClass( 'it_no_limit_selectize' ) ) {
			maxItems = null;
		}
		
		jQuery( '.it_settings_overview_selectize' ).selectize( {
		    maxItems: maxItems,
		    plugins: ['remove_button','drag_drop'],
		    valueField: 'key',
		    labelField: 'value',
		    searchField: 'key',
		    sortField: 'value',
		    options: it_admin_settings.overview,
		    create: false,
		    hideSelected: true
		} );
	}

	if( jQuery( '.it_settings_fastfacts_selectize' ).length ) {
		jQuery( '.it_settings_fastfacts_selectize' ).selectize( {
		    maxItems: 4,
		    plugins: ['remove_button','drag_drop'],
		    valueField: 'key',
		    labelField: 'value',
		    searchField: 'key',
		    sortField: 'value',
		    options: it_admin_settings.fastfacts,
		    create: false,
		    hideSelected: true
		} );
	}
	
	if( jQuery( '.it_settings_roles_selectize' ).length ) {
		var roles_selectize = jQuery( '.it_settings_roles_selectize' ).selectize( {
		    maxItems: null,
		    plugins: ['remove_button'],
		    valueField: 'slug',
		    labelField: 'name',
		    searchField: 'slug',
		    options: it_admin_settings.roles,
		    create: false
		} );
	}
		
	if( jQuery( '.it_settings_status_selectize' ).length ) {
		jQuery( '.it_settings_status_selectize' ).selectize( {
		    maxItems: null,
		    plugins: ['remove_button'],
		    valueField: 'key',
		    labelField: 'value',
		    searchField: 'key',
		    options: it_admin_settings.order_status,
		    create: false
		} );
	}
	
	if( jQuery( '.it_settings_total_net_selectize' ).length ) {
		jQuery( '.it_settings_total_net_selectize' ).selectize( {
		    maxItems: null,
		    plugins: ['remove_button'],
		    valueField: 'key',
		    labelField: 'value',
		    searchField: 'key',
		    options: it_admin_settings.total_net,
		    create: false
		} );
	}
	
	if( jQuery( '.it_settings_roles_exceptions_selectize' ).length ) {
		var source 	   = jQuery( '.it_settings_roles_exceptions_selectize' ).data( 'settings' );
		
		var exceptions = it_admin_settings.role_exceptions;
		switch ( source ) {
			case 'reporting_exceptions':
				exceptions = it_admin_settings.reporting_exceptions;
			break;
			case 'warehouses_exceptions':
				exceptions = it_admin_settings.warehouses_exceptions;
			break;
			case 'omnichannel_ebay_exceptions':
				exceptions = it_admin_settings.omnichannel_ebay_exceptions;
			break;
			case 'omnichannel_amazon_exceptions':
				exceptions = it_admin_settings.omnichannel_amazon_exceptions;
			break;
			case 'omnichannel_google_exceptions':
				exceptions = it_admin_settings.omnichannel_google_exceptions;
			break;
			case 'omnichannel_facebook_exceptions':
				exceptions = it_admin_settings.omnichannel_facebook_exceptions;
			break;
		}
								
		jQuery( '.it_settings_roles_exceptions_selectize' ).selectize( {
			maxItems: null,
			plugins: ['remove_button'],
		    valueField: 'ID',
		    labelField: 'display_name',
		    searchField: 'display_name',
		    options: exceptions,
		    create: false,
		    render: {
		        option: function(item, escape) {
			        return '<div>' + escape(item.display_name) +
			        	'</div>';
		        }
		    },
		    load: function( query, callback ) {
			    var roles = jQuery( '.it_settings_roles_selectize' ).val();
		        if (!query.length) return callback();
		        jQuery.ajax( {
		            url: rst_ajax_settings.root + 'it-sales-acc/v1/users?search=' + encodeURIComponent(query) + '&role=' + encodeURIComponent(roles),
		            type: 'GET',
		            beforeSend: function( xhr ){
			        	xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );		    	
					},
		            error: function() {
		                callback();
		            },
		            success: function(res) {
		                callback(res);
		            }
		        } );
	    	}
		} );
	}
		
	function update_active_modules( id, val ) {
			  
	  jQuery( '.it_rooster_settings_checkbox' ).prop( 'disabled', true );
	  jQuery( '.slider-m.round-m' ).addClass( 'disabled' );
	  jQuery( '.spinner-wrapper' ).css( 'opacity','1' );
	  
      jQuery.ajax( {
        url: rst_ajax_settings.root + 'it-sales-acc/v1/modules/activate',
        method: 'PUT',
        data: {
            'module_id': id,
            'module_val': val 
        },
        beforeSend: function( xhr ){
	        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );		    	
		},
        success:function( response ) {
	        jQuery( '.it_rooster_settings_checkbox' ).prop( 'disabled', false );
	        jQuery( '.slider-m.round-m' ).removeClass( 'disabled' );
	        jQuery( '.spinner-wrapper' ).css( 'opacity','0' );
	        	        
	        if( !val ) {
		        try {
			        var func_name = eval( 'it_rst_' + id + '_just_deactivated_js');
			        if($.isFunction(func_name)) {
				        func_name();
				    }
			    } catch (e) {
				}
	        }
	        location.reload();
        },
        error: function( errorThrown ){
	        var checked;
	        if( val ){
		        checked = false;
	        }
	        else{
		        checked = true;
	        }
	        
	        jQuery( '#' + id ).prop( 'checked', checked );
	        jQuery( '.it_rooster_settings_checkbox' ).prop( 'disabled', false );
	        jQuery( '.slider-m.round-m' ).removeClass( 'disabled' );
	        jQuery( '.spinner-wrapper' ).css( 'opacity','0' );
	        
	        location.reload();
        }
      } );
    }
	    
    jQuery( '.it_rooster_settings_checkbox' ).change( function() {
	    var cb_id = this.id;
	    if ( jQuery(this).is( ':checked') ) {
		    var cb_val = 1;
	    }
	    else {
		    var cb_val = 0;
	    }
	    	    
	    update_active_modules( cb_id, cb_val, false )
    } );
    
    jQuery( '#it_rooster_activate_reporting_module_a' ).click( function() {
	    
	    update_active_modules( 'rooster_reporting', 1, true );
    } );
	  	
	jQuery( '.modules .last_btts' ).click( function() {
		if ( jQuery(this).hasClass( 'it_link_external' ) ) {
			return;
		}
		var id = jQuery( this ).parent().parent().attr( 'id' );
		jQuery( 'html, body' ).animate( { scrollTop: 0 }, 500 );
		
		if ( id != undefined ) {
			jQuery( '.laterals:nth-child(3)' ).attr( 'style','opacity: 1;' );
			jQuery( '#' + id + ' .modal-modules' ).css( 'display','flex' );
			jQuery( 'body' ).css( 'overflow', 'hidden' );
			
		} else {
			jQuery(this).siblings( '.modal-modules' ).css( 'display', 'flex' );
			jQuery( 'body' ).css( 'overflow', 'hidden' );
		}
	} );
	
	jQuery( '.openModal_Reports' ).click( function() {
		jQuery( 'html, body' ).animate( { scrollTop: 0 }, 500 );
		jQuery( '.openModal_Reports .modal-modules' ).css( 'display', 'flex' );
		jQuery( 'body' ).css( 'overflow', 'hidden' );
	} );
	
	jQuery( '.openModal_Timer' ).click( function() {
		jQuery( 'html, body' ).animate( { scrollTop: 0 }, 500 );
		jQuery( '.openModal_Timer .modal-modules' ).css( 'display','flex');
		jQuery( 'body' ).css( 'overflow', 'hidden' );
	});
	
	jQuery( '.closeModal' ).on( 'click', function( e ) {
    	e.stopPropagation();
		jQuery( '.openModal_Reports .modal-modules' ).css( 'display', 'none' );
		jQuery( '.openModal_Timer .modal-modules' ).css( 'display', 'none' );
		jQuery( 'body' ).attr( 'style','overflow: inherit;' );
	});
	
	jQuery( '.close' ).click(function() {
		var width = jQuery(window).width();
		if (width <= 450)
			jQuery( '.laterals:nth-child(3)' ).attr( 'style','opacity: 0;' );
		
		jQuery( '.modal-modules').css( 'display', 'none' );
		jQuery( 'body').attr( 'style', 'overflow: inherit;' );
	} );
	
	function it_rst_regenerate_key() {
		jQuery.ajax( {
	        url: rst_ajax_settings.root + 'it-sales-acc/v1/api/regenerate',
	        method: 'GET',
	        beforeSend: function( xhr ){
		        xhr.setRequestHeader( 'X-WP-Nonce', rst_ajax_settings.nonce );		    	
			},
	        success:function( response ) {
		        alert( 'Key regeneration was successful!');
		        location.reload();
	        },
	        error: function( errorThrown ) {
		        alert( 'Key regeneration failed!');
	        }
	    } );
    }
} );