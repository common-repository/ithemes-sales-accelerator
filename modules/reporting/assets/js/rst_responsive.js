jQuery( document ).ready(function() {
	
	/*
	 * Initialize variables
	*/
	var width;
	

	/*
	 * Force Responsive Grid on Start and Resize
	*/
	resizeGrid = function() {
		
		if (main_dashboard == 1) {
		
			width = jQuery( window ).width();
	
			grid = jQuery( '.grid-stack.nested-grid' ).data( 'gridstack' );
			
			if ( width > 768 && width < 1300 ) {
				
				// Main Grid - Update position
				jQuery( jQuery( '#00' ) ).attr('data-gs-width', '12' );
				jQuery( jQuery( '#0' ) ).attr('data-gs-width', '12' );
	            jQuery( jQuery( '#8' ) ).attr('data-gs-width', '12' );
	            jQuery( jQuery( '#9' ) ).attr('data-gs-width', '12' );
	            grid.update( jQuery( '#8' ), null, null, 12, null );
				jQuery( '#7' ).css( 'opacity', '0' );
	
			} else if ( width > 1300 ) {
	
				// Main Grid - Update position
				jQuery(jQuery('#00')).attr('data-gs-width', '9');
				jQuery(jQuery('#00')).attr('data-gs-height', '2');
				jQuery(jQuery('#0')).attr('data-gs-width', '9');
				jQuery(jQuery('#0')).attr('data-gs-height', '5');
				grid.update(jQuery('#8'),null, null, 9, null);
				grid.update(jQuery('#9'),null, null, 9, null);
	            jQuery(jQuery('#8')).attr('data-gs-width', '9');
	            jQuery(jQuery('#9')).attr('data-gs-width', '9');
				jQuery(jQuery('#7')).attr('data-gs-y', '0');
				jQuery(jQuery('#7')).attr('data-gs-x', '9');
				jQuery('#7').css('opacity','1');
				jQuery('#7').css('display','block');
			}
			
			if ( width < 769 ) {
				jQuery( '#00' ).attr( 'data-gs-height', '5' );
				jQuery( '#00' ).attr( 'data-gs-max-height', '5' );
				jQuery( '#0' ).attr( 'data-gs-height', '13' );
				jQuery( '#0' ).attr( 'data-gs-max-height', '13' );
				jQuery( '#9' ).attr( 'data-gs-height', '8' );
				jQuery( '#9' ).attr( 'data-gs-max-height', '8' );
				jQuery( '#7' ).css( 'display', 'none' );
			} 
					
			if ( width < 490 ) {
				jQuery( '.calendar.right' ).attr( 'style', 'float: left !important' );		
			} else {
				jQuery( '.calendar.right' ).attr( 'style', 'float: right !important' );
			}
	
			// Hide calendar button on window resize
			if ( jQuery( '.daterangepicker' ).hasClass( 'show-calendar' ) == true ) {
				jQuery( '.cancelBtn' ).click();
			}
			
			// Hide overflow from boxes
			jQuery( '.grid-stack.nested-grid .grid-stack-item-content' ).css( 'overflow', 'hidden' );
			jQuery( '#00 .grid-stack-item-content' ).css('overflow', 'hidden' );
			jQuery( '#0 .grid-stack-item-content' ).css('overflow', 'hidden' );
			jQuery( '#9 .grid-stack-item-content' ).css('overflow', 'hidden' );
			jQuery( '#8 .grid-stack-item-content' ).css('overflow', 'hidden' );
			jQuery( '#8 .grid-stack-item-content' ).css('overflow', 'hidden' );
			jQuery( '#16 .grid-stack-item-content' ).css('overflow', 'hidden' );
			jQuery( '#15 .grid-stack-item-content' ).css('overflow', 'hidden' );
		} else {
			// Hide overflow from boxes
			jQuery( '.grid-stack.nested-grid .grid-stack-item-content' ).css( 'overflow', 'hidden' );
			jQuery( '#00 .grid-stack-item-content' ).css('overflow', 'hidden' );
			jQuery( '#0 .grid-stack-item-content' ).css('overflow', 'hidden' );
			jQuery( '#8 .grid-stack-item-content' ).css('overflow', 'hidden' );
			
			if ( width < 769 ) {
				jQuery( '#00' ).attr( 'data-gs-height', '5' );
				jQuery( '#00' ).attr( 'data-gs-max-height', '5' );
				jQuery( '#0' ).attr( 'data-gs-height', '5' );
				jQuery( '#0' ).attr( 'data-gs-max-height', '5' );
			}
		}
		
	}
	
	/*
	 * Actions on Window Resize
	*/
	jQuery( window ).resize(function() {
		resizeGrid();
	});
	
});