<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class provides endpoints to access modules status
**/

class IT_RST_Modules_Endpoint {
    
    // Singleton design pattern
    protected static $instance = NULL;
    private $server;
    private $namespace = 'wc/v1';
    private $rest_base = 'modules';
    
    // Method to return the singleton instance
    public static function get_instance() {
        
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
	    
	    $this->server = new WP_REST_Server();
    }    
    
    // Get all products/Search products by parameters
    public function activate_module( $request ) {
	    
	    $params = $request->get_params();
	    
	    if ( isset( $params['module_id'] ) && isset( $params['module_val'] ) ) {
		    $module_id  = $params['module_id'];
			$module_val = $params['module_val'];
			
		    $modules = get_option( 'it_rooster_modules_status' );
		    		    
		    if ( isset( $modules[ $module_id ] ) ) {
			    if ( !is_array( $modules[ $module_id ] ) ) {
			    	$modules[ $module_id ] = array();
			    }
			    $modules[ $module_id ]['active'] = $module_val;
			    update_option( 'it_rooster_modules_status', $modules );
			    return array( 'result' => true, 'data' => array() );
		    }
		    else {
			    global $rst_modules;
			    
			    foreach ( $rst_modules as $module ) {
				    if ( $module['slug'] == $module_id ) {
					    $modules[$module_id]['active'] = $module_val;
					    update_option( 'it_rooster_modules_status', $modules );
						return array( 'result' => true, 'data' => array() );
				    }
			    }			    
		    }
	    }	   
	    
	    ob_clean(); 
	    return array( 'result' => false, 'data' => array() );
	}
}