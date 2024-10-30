<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles initial import management endpoints
**/

class IT_RST_RP_Import_Rest {
    
    // Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct() {}
    
    // Get importing status
    public function importing_status_json() {
	    	    
	    $import_status = $this->reporting_is_importing();
		return new IT_REST_Response( array( 'result' => true, 'data' => $import_status ), 200 );
    }
    
    public function reporting_is_importing(){
	    $status     = get_option( 'it_rooster_reporting_import_status' );
	    $progress   = ( isset( $status['progress'] ) ) ? $status['progress'] : 0;
	    $importing  = ( $progress && $progress < 100 ) ? true : false;
	    
	    $period     	  = 60;
		$items_per_period = 400;
		$time_left 		  = 0;
		$time_string      = __( 'Import_completed', 'ithemes-sales-accelerator' );
		$tot_products 	  = ( isset( $status['total_products'] ) )    ? $status['total_products']    : 0;
		$tot_order    	  = ( isset( $status['total_orders'] ) )      ? $status['total_orders']      : 0;
		$imp_products 	  = ( isset( $status['imported_products'] ) ) ? $status['imported_products'] : 0;
		$imp_order    	  = ( isset( $status['imported_orders'] ) )   ? $status['imported_orders']   : 0;
		$items_left   	  = $tot_products + $tot_order - $imp_products - $imp_order;
		
		if ( $items_left > 0 ) {
			$time_left = ( $items_left / $items_per_period ) * $period;
			$time_left_mins = round( $time_left / 60, 0 );
			if ( !$time_left_mins ) {
				$time_left_mins = 1;
			}
			
			$mins_string = ( $time_left_mins == 1 ) ? __( 'minute remaining', 'ithemes-sales-accelerator' ) : __( 'minutes remaining', 'ithemes-sales-accelerator' );
			$time_string = '(' . __( 'approx.', 'ithemes-sales-accelerator' ). ' ' . $time_left_mins . ' ' . $mins_string . ')';
		}
		else if( $tot_order && $tot_products ) {
			$time_string = __( 'Completed', 'ithemes-sales-accelerator' );
		}
		else {
			$time_string = __( 'Import is starting', 'ithemes-sales-accelerator' );
		}
		
		$progress = round( $progress, 1 );
	    return array( 'importing' => $importing, 'progress' => $progress, 'time_string' => $time_string );
    }
    
    public function reporting_reset_importing() {
	    
	    try {
		    global $wpdb;
		    		    
		    // Delete reporting imported meta
			$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => 'it_rooster_reporting_imported' ) );
			
		    // Delete custom database information
		    $wpdb->query( 'TRUNCATE TABLE ' . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE );
		    $wpdb->query( 'TRUNCATE TABLE ' . IT_RST_REPORTING_PRODUCTS_DATABASE );
		    $wpdb->query( 'TRUNCATE TABLE ' . IT_RST_REPORTING_ORDERS_DATABASE );
	    	$wpdb->query( 'TRUNCATE TABLE ' . IT_RST_REPORTING_EVENTS_DATABASE );
				    
		    // Delete import option
		    delete_option( 'it_rooster_reporting_import_status' );
		    delete_option( 'it_rooster_reporting_is_importing' );
		    delete_option( 'it_rooster_reporting_db_upgrade_status' );
		    		    
	    	return true;
    	}
    	catch ( Exception $e ) {
	    	return false;
    	}
    }
    
    public function reporting_delete_import() {
	    
	    try {
		    
		    global $wpdb;
		    
		    // Delete reporting imported meta
			$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => 'it_rooster_reporting_imported' ) );
			
		    // Delete custom database information
		    $wpdb->query( 'TRUNCATE TABLE ' . IT_RST_REPORTING_ORDER_PRODUCTS_DATABASE );
		    $wpdb->query( 'TRUNCATE TABLE ' . IT_RST_REPORTING_PRODUCTS_DATABASE );
		    $wpdb->query( 'TRUNCATE TABLE ' . IT_RST_REPORTING_ORDERS_DATABASE );
	    	$wpdb->query( 'TRUNCATE TABLE ' . IT_RST_REPORTING_EVENTS_DATABASE );
				    
		    // Delete import option
		    $import_status = get_option( 'it_rooster_reporting_import_status' );
		    if ( is_array( $import_status ) ) {
			    $import_status['status'] = 'deleted';
			    update_option( 'it_rooster_reporting_import_status', $import_status );
		    }
		    delete_option( 'it_rooster_reporting_is_importing' );
		    delete_option( 'it_rooster_reporting_db_upgrade_status' );
		    		    
	    	return true;
    	}
    	catch ( Exception $e ) {
	    	return false;
    	}
    }
    
    // Start importing (REST)
    public function importing_start() {
	    
	    $result = $this->reporting_start_importing();
	    return new IT_REST_Response( array( 'result' => $result, 'data' => array() ), 200 );	    
    }
    
    // Re-Start importing (REST)
    public function importing_reset() {
	    
	    $result = $this->reporting_reset_importing();
	    return new IT_REST_Response( array( 'result' => $result, 'data' => array() ), 200 );	 
    }
    
    // Delete import data (REST)
    public function importing_delete() {
	    
	    $result = $this->reporting_delete_import();
	    return new IT_REST_Response( array( 'result' => $result, 'data' => array() ), 200 );	 
    }
    
    public function reporting_start_importing() {
	    
	    $ithemes_api = IT_iThemes_API::get_instance();
	    return $ithemes_api->requestPing( '', true );
    }
}