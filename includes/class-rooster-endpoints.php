<?php

if ( !defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

/**
* This class manages executor and php endpoints
**/

class IT_RST_Endpoints {
    
    // Singleton design pattern
    protected static $instance = NULL;
    private $endpoints;
    private $exec_endpoints;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct() {
	    
	    $this->init_endpoints();
	    
	    // Add rewrite rule for executor endpoints
        add_action( 'init', 		 array( $this, 'init_internal' ) );
        add_filter( 'query_vars',    array( $this, 'add_query_vars' ) );
        add_action( 'parse_request', array( $this, 'parse_endpoint_request' ) );
    }    
    
    // Initiates Endpoint Filter
    public function init_endpoints(){
	    
	    $endpoints = array( 'test_core_endpoint' => array( 'class' => 'IT_RST_Sample_Endpoint', 'http_endpoint' => array() ) );
	    $endpoints = array( 'it_rooster_reset_api_key' => array( 'class' => 'IT_RST_API_Endpoint', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'api/regenerate', 'callback' => 'regenerate_key_json') ) ) );
	    $endpoints['it_rooster_change_active_modules'] = array( 'class' => 'IT_RST_Modules_Endpoint', 'http_endpoint' => array( array( 'method' => WP_REST_Server::EDITABLE, 'endpoint' => 'modules/activate', 'callback' => 'activate_module') ) );
	    
	    $endpoints['it_rooster_settings'] = array( 'class' => 'IT_RST_Settings_Endpoint', 'http_endpoint' => array( array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'settings', 'callback' => 'get_settings') ) );
	    
	    $endpoints['wc_products'] = array( 'class' => 'IT_RST_WooCommerce_Products', 'http_endpoint' => 
								    	array(
								    		array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'products', 'callback' => 'get_products' ),
								    		array( 'method' => WP_REST_Server::CREATABLE, 'endpoint' => 'products', 'callback' => 'add_product' ),
								    		array( 'method' => WP_REST_Server::CREATABLE, 'endpoint' => 'products/(?P<id>\d+)', 'callback' => 'add_product' ),
								    		array( 'method' => WP_REST_Server::DELETABLE, 'endpoint' => 'products/(?P<id>\d+)', 'callback' => 'delete_product' ),
								    		array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'products/(?P<id>\d+)', 'callback' => 'get_product' ),
								    		array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'products/categories', 'callback' => 'get_product_categories' ),
								    		array( 'method' => WP_REST_Server::CREATABLE, 'endpoint' => 'products/categories', 'callback' => 'add_product_category' ),
								    		array( 'method' => WP_REST_Server::DELETABLE, 'endpoint' => 'products/categories/(?P<id>\d+)', 'callback' => 'delete_product_category' ),
								    		)
								    );
	    $endpoints['wc_orders'] = array( 'class' => 'IT_RST_WooCommerce_Orders', 'http_endpoint' => 
	    								array(
											array( 'method' => WP_REST_Server::READABLE,  'endpoint' => 'orders', 'callback' => 'get_orders' ),
											array( 'method' => WP_REST_Server::READABLE,  'endpoint' => 'orders/(?P<id>\d+)', 'callback' => 'get_order' ),
											array( 'method' => WP_REST_Server::READABLE,  'endpoint' => 'orders/(?P<id>\d+)/notes/', 'callback' => 'get_order_notes' ),
											array( 'method' => WP_REST_Server::CREATABLE, 'endpoint' => 'orders/(?P<id>\d+)/notes/', 'callback' => 'create_order_note' ),
											array( 'method' => WP_REST_Server::DELETABLE, 'endpoint' => 'orders/(?P<id>\d+)/notes/(?P<noteID>\d+)', 'callback' => 'delete_order_note' ),
											array( 'method' => WP_REST_Server::EDITABLE,  'endpoint' => 'orders/(?P<id>\d+)', 'callback' => 'set_order_status' ),
										)
								  );
	    $endpoints['wc_customers'] = array( 'class' => 'IT_RST_WooCommerce_Customers', 'http_endpoint' =>
	    								array(
	    									array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'customers', 'callback' => 'get_customers' ), 
	    									array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'customers/(?P<id>\d+)', 'callback' => 'get_customer' ), 
	    								) 
									 );
		
		 $endpoints['wc_shipping'] = array( 'class' => 'IT_RST_WooCommerce_Shipping', 'http_endpoint' =>
	    								array(
	    									array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'shipping/classes', 'callback' => 'get_shipping_classes' ), 
	    								) 
									 );
		
		$endpoints['wp_media'] = array( 'class' => 'IT_RST_WordPress_Media', 'http_endpoint' =>
	    								array(
	    									array( 'method' => WP_REST_Server::CREATABLE, 'endpoint' => 'media', 'callback' => 'add_media_image' ), 
	    									array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'media', 'callback' => 'get_media_images' ), 
	    									array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'media/(?P<id>\d+)', 'callback' => 'get_media_images' ), 
	    									array( 'method' => WP_REST_Server::DELETABLE, 'endpoint' => 'media/(?P<id>\d+)', 'callback' => 'delete_media_image' ), 
	    									
	    								) 
									 );
									 
		$endpoints['wp_users'] = array( 'class' => 'IT_RST_WordPress_Users', 'http_endpoint' =>
	    								array(
	    									array( 'method' => WP_REST_Server::READABLE, 'endpoint' => 'users', 'callback' => 'get_users' ),
	    								) 
								 );
	    
	    $endpoints 	 	 = apply_filters( 'it_rst_available_endpoints', $endpoints );
	    $this->endpoints = $endpoints;
	    $exec_endpoints  = array( 'sales-acc_authentication_process' => array( 'class' => 'IT_RST_Authentication_Rest', 'var' => 'sales-acc_authentication_process' ) );
	    $exec_endpoints  = apply_filters( 'it_rst_available_executor_endpoints', $exec_endpoints );	    
	    
	    $this->exec_endpoints = $exec_endpoints;
    }
    
    // Adds rewrite rules
    public function init_internal() {
	    
	    foreach ( $this->exec_endpoints as $k => $endpoint ) {
		    $query_var = $endpoint['var'];
		    add_rewrite_rule( $k, "index.php?$query_var=1", "top" );		    
	    }	    
    }
    
   // Adds query vars
   public function add_query_vars( $query_vars ) {
	   
	   foreach ( $this->exec_endpoints as $k => $endpoint ) {
		    $query_var 	  = $endpoint['var'];
		    $query_vars[] = $query_var;
	   }
	   	   	   
	   return $query_vars;
   }
   
   // Processes endpoint requests
   public function parse_endpoint_request( $wp ) {
	   
	   foreach ( $this->exec_endpoints as $k => $endpoint ) {
		   $query_var = $endpoint['var'];
		   
		   if ( array_key_exists( $query_var, $wp->query_vars ) ) {
	            $class = $endpoint['class'];
	            ob_clean();
	            new $class();
	            exit();
	       }
        }        
   }
    
    // Gets all defined endpoints
    public function get_endpoints() {
	    
	    return $this->endpoints;
    }
    
    // Call a specific endpoint
    public function call_endpoint( $endpoint ) {
	    
	    if ( isset( $this->endpoints[ $endpoint ] ) ) {
		    $class 	  = $this->endpoints[ $endpoint ]['class'];
		    $instance = new $class;
		    $result   = call_user_func( array( $instance, $endpoint ) );
		    return $result;
	    }
    }
    
    // This makes a rest request via curl to access WooCommerce API
    public function make_rest_request ( $url, $type = 'GET', $content_json = array() ) {

		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) {
			$curl = curl_init();
			curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
			curl_setopt( $curl, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'] . ":" . $_SERVER['PHP_AUTH_PW'] );
			curl_setopt( $curl, CURLOPT_URL, $url );
			curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );	
			curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-type: application/json; charset=utf-8' ) );
			
			if ( $type !== 'GET' ) {
				curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $type );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $content_json );
			}
			
			$result = curl_exec( $curl );
			curl_close($curl);	    	 

			return json_decode( $result );
			
		} else {
			return array( 'error' => 'not allowed' );
		}	
	}
}