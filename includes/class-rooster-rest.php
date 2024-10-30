<?php

if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class manages rest requests and endpoints
**/

class IT_RST_Rest extends WP_REST_Controller {
    
    // Singleton design pattern
    protected static $instance 	= NULL;
    private $version   			= 'v1';
    private $base  	   			= 'it-sales-acc';
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct() {
	    
	    // Inits rest endpoints
		add_action( 'rest_api_init', array( $this, 'init_rest_endpoints' ) );
		
		// Fixes Datatable JS library warning
		add_action( 'init', array( $this, 'fix_datatable_structure' ) );
    }    
    
    public function fix_datatable_structure() {
	    $remove = false;
	    
	    if ( isset( $_GET['order'][0]['column'] ) ) {
		    $_GET['order']['column'] = $_GET['order'][0]['column'];
		    $remove = true;
	    }
	    if ( isset( $_GET['order'][0]['dir'] ) ) {
		    $_GET['order']['dir']    = $_GET['order'][0]['dir'];
		    $remove = true;
	    }
	    if ( $remove ) {
		    unset( $_GET['order'][0] );
	    }	    
    }
    
    // Initiates rest endpoints
    public function init_rest_endpoints() {
	    	    
	    $endpoints_class = IT_RST_Endpoints::get_instance();
	    $endpoints 		 = $endpoints_class->get_endpoints();
	    	    	    
	    foreach ( $endpoints as $key => $endpoint ) {
		    if ( isset( $endpoint['http_endpoint'] ) && !empty( $endpoint['http_endpoint'] ) ) {
			    
			    $http_endpoint = $endpoint['http_endpoint'];
			    
			    if ( !$this->is_assoc( $http_endpoint ) ) {
					foreach ( $http_endpoint as $the_endpoint ) {
					    $class 	  	 = $endpoint['class'];
					    $permissions = ( isset( $endpoint['allow_guests'] ) && $endpoint['allow_guests'] ) ? 'guest_permissions_check' : 'general_permissions_check';
					    					    
					    $instance 	 = new $class;
					    
					    // Register this route
						register_rest_route( $this->base . '/' . $this->version, '/' . $the_endpoint['endpoint'], array(
					            array(
					                'methods'         		=> $the_endpoint['method'],
					                'callback'        		=> array( $instance, $the_endpoint['callback'] ),
					                'permission_callback' 	=> array( $this, $permissions ),
					                'args'            		=> array(),
					            )
					        )
						);
					}
					
				} else {
				    $class 	  = $endpoint['class'];
				    $permissions = ( isset( $endpoint['allow_guests'] ) && $endpoint['allow_guests'] ) ? 'guest_permissions_check' : 'general_permissions_check';
				    $instance = new $class;
				    
				    
				    // Register this route
					register_rest_route( $this->base . '/' . $this->version, '/' . $http_endpoint['endpoint'], array(
				            array(
				                'methods'         		=> $http_endpoint['method'],
				                'callback'        		=> array( $instance, $http_endpoint['callback'] ),
				                'permission_callback' 	=> array( $this, $permissions ),
				                'args'            		=> array(),
				            )
				        )
					);
				}
		    }
	    }	
    }
    
    // Guests Permission check function
    public function guest_permissions_check( $request ) { 
	    
	    return true;
    }
    
    // Permission check function
    public function general_permissions_check( $request ) {
	    
	    if ( is_user_logged_in() ) {
		    $route = $request->get_route();
		    
		    $user = wp_get_current_user();
		    $roles = ( $user->roles ) ? $user->roles : array();
		    $permissions = IT_RST_Permissions::get_instance();
		    
		    // It is a reporting endpoint?
		    if ( strpos( $route, '/reporting/' ) !== false ) {
			    if ( $permissions->check_permission( 'reporting' ) ) {
				    return true;
			    }
		    }
		    // Else core
		    else {
			    if ( $permissions->check_permission( 'core' ) ) {
				    return true;
			    }
		    }		    
	    }
	    
	    else if ( is_ssl() ) {
		    // If request is made through HTTPS
		    return $this->ssl_permissions_check( $request );
	    }
	    else {
		    // If request is made through HTTP
		    return $this->non_ssl_permissions_check( $request );
	    }
    }
    
    // HTTPS permissions check
    public function ssl_permissions_check( $request ) {
	    
        try {
			return $this->perform_basic_authentication( $request );
        }
        catch( Exception $e ) {
          	return false;
        }
		return false;
    }
    
    public function perform_basic_authentication( $request ) {
	   
	    // If the above is not present, we will do full basic auth.
		if ( !empty( $_SERVER['PHP_AUTH_USER'] ) && !empty( $_SERVER['PHP_AUTH_PW'] ) ) {
			$key 	= $_SERVER['PHP_AUTH_USER'];
			$secret = $_SERVER['PHP_AUTH_PW'];
			
			// Stop if don't have any key.
			if ( !$key || ! $secret ) {
				return false;
			}
			
			// if the $_GET parameters are present, use those first
			if ( ! empty( $key ) && ! empty( $secret ) ) {
				$keys = $this->get_keys_by_consumer_key( $key );
				
				// Check if consumer secret is valid
				if ( ! $this->is_consumer_secret_valid( $keys['consumer_secret'], $secret ) ) {
					throw new Exception( __( 'Consumer Secret is invalid', 'woocommerce' ), 401 );
				}
	
				return true;
			}
				
			return false;
	    }
	    else {
		    return false;
	    }
	    
    }
    
    // Checks validity of consumer secret
    private function is_consumer_secret_valid( $keys_consumer_secret, $consumer_secret ) {
	    
		return hash_equals( $keys_consumer_secret, $consumer_secret );
	}
    
    // Get keys based on consumer secret
    private function get_keys_by_consumer_key( $consumer_key ) {
	    
  		global $wpdb;

  		$consumer_key = wc_api_hash( sanitize_text_field( $consumer_key ) );
  		$keys = $wpdb->get_row( $wpdb->prepare( "
  			SELECT key_id, user_id, permissions, consumer_key, consumer_secret, nonces
  			FROM {$wpdb->prefix}woocommerce_api_keys
  			WHERE consumer_key = '%s'
  		", $consumer_key ), ARRAY_A );
 
   		if ( empty( $keys ) ) {
  			throw new Exception( __( 'Consumer Key is invalid', 'woocommerce' ), 401 );
  		}

  		return $keys;
  	}
  	
  	
    // HTTP permissions check
    public function non_ssl_permissions_check( $request ) {
	    
        try {
			return $this->perform_oauth_authentication( $request );
        }
        catch( Exception $e ) {
          	return false;
        }
		return false;
    }
    
    // OAuth 1.0 Authentication
    private function perform_oauth_authentication( $request ) {
	    
	    if ( current_user_can( 'manage_woocommerce' ) ) {
		    return true;

	    } else {
	      	$params 	 = $request->get_params();
			$param_names =  array( 'oauth_consumer_key', 'oauth_signature', 'oauth_signature_method' );
			
			// Check for required OAuth parameters
			foreach ( $param_names as $param_name ) {
			
				if ( empty( $params[ $param_name ] ) ) {
					throw new Exception( sprintf( __( '%s parameter is missing', 'woocommerce' ), $param_name ), 404 );
				}
			}
			
			// Fetch WP user by consumer key
			$keys = $this->get_keys_by_consumer_key( $params['oauth_consumer_key'] );
			unset( $keys['nonces'] );
			
			// Perform OAuth validation
			return $this->check_oauth_signature( $keys, $params, $request );
		    
		}
	    
    }
    
    // Signature validation
    private function check_oauth_signature( $keys, $params, $request ) {
	    
  		$http_method = strtoupper( $request->get_method() );
  		$server_path = $request->get_route();

  		// if the requested URL has a trailingslash, make sure our base URL does as well
  		if ( isset( $_SERVER['REDIRECT_URL'] ) && '/' === substr( $_SERVER['REDIRECT_URL'], -1 ) ) {
  			$server_path .= '/';
  		}

  		$rooster_api_url  = get_home_url() . '/wp-json';
  		$base_request_uri = rawurlencode( untrailingslashit( $rooster_api_url ) . $server_path );

  		// Get the signature provided by the consumer and remove it from the parameters prior to checking the signature
  		$consumer_signature = rawurldecode( $params['oauth_signature'] );
  		unset( $params['oauth_signature'] );

  		// Sort parameters
  		if ( ! uksort( $params, 'strcmp' ) ) {
  			throw new Exception( __( 'Invalid Signature - failed to sort parameters', 'woocommerce' ), 401 );
  		}

  		// Normalize parameter key/values
  		$params = self::normalize_parameters( $params );
  		$query_parameters = array();
  		foreach ( $params as $param_key => $param_value ) {
  			if ( is_array( $param_value ) ) {
  				foreach ( $param_value as $param_key_inner => $param_value_inner ) {
  					$query_parameters[] = $param_key . '%255B' . $param_key_inner . '%255D%3D' . $param_value_inner;
  				}
  			} else {
  				$query_parameters[] = $param_key . '%3D' . $param_value; // join with equals sign
  			}
  		}
  		$query_string 	= implode( '%26', $query_parameters ); // join with ampersand
  		$string_to_sign = $http_method . '&' . $base_request_uri . '&' . $query_string;

  		if ( $params['oauth_signature_method'] !== 'HMAC-SHA1' && $params['oauth_signature_method'] !== 'HMAC-SHA256' ) {
  			throw new Exception( __( 'Invalid Signature - signature method is invalid', 'woocommerce' ), 401 );
  		}

  		$hash_algorithm = strtolower( str_replace( 'HMAC-', '', $params['oauth_signature_method'] ) );

  		$secret = $keys['consumer_secret'] . '&';
  		$signature = base64_encode( hash_hmac( $hash_algorithm, $string_to_sign, $secret, true ) );
  		
  		// Invalid signature
  		if ( ! hash_equals( $signature, $consumer_signature ) ) {
        	throw new Exception( __( 'Invalid Signature - provided signature does not match', 'woocommerce' ), 401 );
			return false;
  		}
  		
  		// Valid signature
	    else {
	        return true;
	    }
  	}
  	
  	// Normalize parameters taking into account RFC3986
    private static function normalize_parameters( $parameters ) {
	    
  		$keys = self::urlencode_rfc3986( array_keys( $parameters ) );
  		$values = self::urlencode_rfc3986( array_values( $parameters ) );
  		$parameters = array_combine( $keys, $values );
  		return $parameters;
  	}
  	
  	// URL encode according to RFC3986
    private static function urlencode_rfc3986( $value ) {
	    
  		if ( is_array( $value ) ) {
  			return array_map( array( self, 'urlencode_rfc3986' ), $value );
  		} else {
  			// Percent symbols (%) must be double-encoded
  			return str_replace( '%', '%25', rawurlencode( rawurldecode( $value ) ) );
  		}
  	}
  	
  	private function is_assoc($arr) {
	    return array_keys($arr) !== range(0, count($arr) - 1);
	}
}