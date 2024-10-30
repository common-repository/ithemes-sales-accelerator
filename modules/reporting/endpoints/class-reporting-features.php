<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles feature management endpoints
**/

class IT_RST_RP_Features {
    
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

    // Process reporting timers form
    public function set_reporting_timers_json( $request ) {
	    
	    $params = $request->get_params();
	    
	    if ( isset( $params['form'] ) ) {
		    $form   = $params['form'];
		    $values = array();
			parse_str( $form, $values );
			
			if( isset( $values['it_rooster_reporting_features'] ) ) {
				$it_rooster_reporting_features = $values['it_rooster_reporting_features'];					
				update_option( 'it_rooster_reporting_features', $it_rooster_reporting_features );
				
			    return new IT_REST_Response( array( 'result' => true, 'data' => array() )  , 200 );	 
		    }
	    }   
	    
	    return new IT_REST_Response( array( 'result' => false, 'data' => array() )  , 200 );
    }
    
    public function set_reporting_timers() {
	    
	    return true;
    }    
}