<?php
	
if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class provides endpoints to access product data
**/

class IT_RST_WooCommerce_Shipping {
    
    // Singleton design pattern
    protected static $instance = NULL;
    private $server;
    private $namespace = 'wc/v1';
    private $rest_base = 'shipping';
    
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
    public function get_shipping_classes( $request ) {
	    
	    $shipping_obj 	  = new WC_Shipping();
	    $shipping_classes = $shipping_obj->get_shipping_classes();
	    
	    return $shipping_classes;
	}

}	