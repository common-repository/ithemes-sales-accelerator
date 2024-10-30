<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class manages connections with iThemes API
**/

class IT_iThemes_API {
    
    // Singleton design pattern
    protected static $instance 	 = NULL;
    protected static $api_url    = 'https://hellosales-api.ithemes.com';
    protected $wordpress_version;
    protected $site_url;
    protected $email;
    protected $site_id;
    protected $site_token;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct() {
	    
	    $this->wordpress_version = get_bloginfo( 'version' );
	    $this->site_url			 = get_site_url();
	    $this->email             = get_option( 'admin_email' );
	    $site_info 				 = get_option( 'it_rooster_ithemes_api_data' );
	    
	    // Already has website token and id?
	    if ( isset( $site_info['id'] ) && isset( $site_info['token'] ) && $site_info['id'] && $site_info['token'] ) {
		    $this->site_id 	  = $site_info['id'];
			$this->site_token = $site_info['token'];			
	    }
	    // Missing website token and id
	    else {
		    $this->connect();
	    }
	}
	
	// Makes the first connection with API to access id and token
	public function connect() {
		
		//  build the query string       
	    $action     = 'site/connect';
	    $params     = array( 'wp'        => $this->wordpress_version,
	                         'site'      => $this->site_url,
	                         'email'     => $this->email,
	                         'timestamp' => time(),
	                  );
	                          
	    $endpoint   = self::$api_url . '/' . $action;
	    
	    //  send the request
		$result = $this->postRequest( $endpoint, $params );
		
		if ( isset( $result['success'] ) && $result['success'] && isset( $result['site']['id'] ) && $result['site']['id'] && isset( $result['site']['token'] ) && $result['site']['token'] ) {
			$site_id 	= $result['site']['id'];
			$site_token = $result['site']['token'];
			$site_array = array( 'id' => $site_id, 'token' => $site_token );
			
			update_option( 'it_rooster_ithemes_api_data', $site_array );
			
			$this->site_id 	  = $site_id;
			$this->site_token = $site_token;
		}
	}
	
	// Requests ping to a given url
	public function requestPing( $path, $active = true ) {
		
		$action     = 'site/' . $this->site_id . '/ping';
	    $params     = array( 'wp'        => $this->wordpress_version,
	                         'site'      => $this->site_url,
	                         'timestamp' => time(), 
	                  );        
	        
	    $endpoint   = self::$api_url . '/' . $action;
		
		// Request parameters
	    $params['active']  = $active;
	    $params['path']    = $path;
	    
	    //sign the request
	    $params['signature'] = $this->getSignature( $this->site_token, $action, $params );  
	    	    
	    //  send the request        
	    $result = $this->postRequest( $endpoint, $params );
	    	    	    	    	    
	    if ( isset( $result['success'] ) && $result['success'] ) {    
			return true;
		}
		else {
			return false;
		}
	}
	
	// Make curl post request
	public function postRequest( $url, $params = array() ) {
		
		$post_params = json_encode( $params );
        $ch = curl_init ( $url );
    
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_params );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 60 );
        
        $result = curl_exec( $ch );
        
        if ( !$result ) {
	        error_log( 'iThemes API request failed' );
        }
                
        return json_decode( $result, true );
	}
	
	// Create HMAC signature
	public function getSignature( $token, $url, $params ) {
		
	    ksort( $params ); 
        $params  = implode( '', array_values( $params ) );
        return hash_hmac( 'sha256', $url . $params, $token, false );
    }
}