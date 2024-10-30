<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}
	
/**
* This class provides endpoints to access product data
**/

class IT_RST_API_Endpoint {
    
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
    
    public function regenerate_key() {
	    
	    global $it_rst_main_settings, $wpdb;
	    
	    $value2 	= isset( $it_rst_main_settings['api_id'] ) ? $it_rst_main_settings['api_id'] : '';
	    if ( $value2 ) {
		    $result = $wpdb->delete( $wpdb->prefix .'woocommerce_api_keys', array( 'key_id' => $value2 ) );			  
		    $it_rst_main_settings['api_id'] = 0;
		    update_option( 'it_rooster_main_settings', $it_rst_main_settings );	
		    
	    	return true;
	    }
	    else {
		    return false;
	    }
    }
    
    // Regenerate key json endpoint
    public function regenerate_key_json() {
	    
	    $result = $this->regenerate_key();
	    return new IT_REST_Response( array( 'result' => $result, 'data' => array() )  , 200 );  
    }
}