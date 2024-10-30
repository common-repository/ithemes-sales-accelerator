<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles dashboard grid management endpoints
**/

class IT_RST_RP_Grid {
    
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
    
    // Load Grid Layout
    public function loadgrid() {
	    
	    return 'LOAD GRID';
    }
   
    // Load Grid JSON Endpoint
    public function loadgrid_json_endpoint( $request ) {
	    
	    $data = $this->loadgrid();
	    return new IT_REST_Response( array( 'result' => true, 'data' => $data )  , 200 );
    }
    
    public function resetgrid() {
	    
		$user_id = get_current_user_id();
		if ( $user_id ) {
			delete_user_meta( $user_id, 'it_rooster_box_coord' );
			delete_option( 'it_rooster_dashboard_settings' );
			return true;
		}
		else {
			return false;
		}
    }
    
    // Reset Grid JSON Endpoint
    public function resetgrid_json_endpoint( $request ) {
	    
	     $result = $this->resetgrid();
	     return new IT_REST_Response( array( 'result' => $result, 'data' => array() )  , 200 );
    }
}