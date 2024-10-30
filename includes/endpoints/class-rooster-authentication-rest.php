<?php
	
if ( !defined( 'ABSPATH' ) ) {
    exit();
}
	
/**
* This class handles rest authentication
**/

class IT_RST_Authentication_Rest {
    
    // Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct() {
	    
	    $current_user = wp_get_current_user();
		if ( 0 == $current_user->ID ) {
			auth_redirect();
		} else {
			if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
	    		global $wpdb;
				$app_name 	 = 'SalesAccelerator';
				$app_user_id = 'SalesAccelerator App';
				$scope 		 = 'read_write';
				$user 		 = wp_get_current_user();
				
				// Created API keys.
				$permissions     = ( in_array( $scope, array( 'read', 'write', 'read_write' ) ) ) ? sanitize_text_field( $scope ) : 'read';
				$consumer_key    = 'ck_' . wc_rand_hash();
				$consumer_secret = 'cs_' . wc_rand_hash();
				$description = sprintf(
					__( '%1$s - API %2$s (created on %3$s at %4$s).', 'woocommerce' ),
					wc_clean( $app_name ),
					$scope,
					date_i18n( wc_date_format() ),
					date_i18n( wc_time_format() )
				);
				$wpdb->insert(
					$wpdb->prefix . 'woocommerce_api_keys',
					array(
						'user_id'         => $user->ID,
						'description'     => $description,
						'permissions'     => $permissions,
						'consumer_key'    => wc_api_hash( $consumer_key ),
						'consumer_secret' => $consumer_secret,
						'truncated_key'   => substr( $consumer_key, -7 ),
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
		            
	         
			    	    
				$html = '
					<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US"><head></head><body id="error-page">	
				
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
					<meta name="viewport" content="width=device-width">
					<meta name="robots" content="noindex,follow">
					<title>WordPress › App Access</title>
					<style type="text/css">
						html {
							background: #f1f1f1;
						}
						body {
							background: #fff;
							color: #444;
							font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
							margin: 2em auto;
							padding: 1em 2em;
							max-width: 700px;
							-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
							box-shadow: 0 1px 3px rgba(0,0,0,0.13);
						}
						h1 {
							border-bottom: 1px solid #dadada;
							clear: both;
							color: #666;
							font-size: 24px;
							margin: 30px 0 0 0;
							padding: 0;
							padding-bottom: 7px;
						}
						#error-page {
							margin-top: 50px;
						}
						#error-page p {
							font-size: 14px;
							line-height: 1.5;
							margin: 25px 0 20px;
						}
						#error-page code {
							font-family: Consolas, Monaco, monospace;
						}
						ul li {
							margin-bottom: 10px;
							font-size: 14px ;
						}
						a {
							color: #0073aa;
						}
						a:hover,
						a:active {
							color: #00a0d2;
						}
						a:focus {
							color: #124964;
						    -webkit-box-shadow:
						    	0 0 0 1px #5b9dd9,
								0 0 2px 1px rgba(30, 140, 190, .8);
						    box-shadow:
						    	0 0 0 1px #5b9dd9,
								0 0 2px 1px rgba(30, 140, 190, .8);
							outline: none;
						}
						.button {
							background: #f7f7f7;
							border: 1px solid #ccc;
							color: #555;
							display: inline-block;
							text-decoration: none;
							font-size: 13px;
							line-height: 26px;
							height: 28px;
							margin: 0;
							padding: 0 10px 1px;
							cursor: pointer;
							-webkit-border-radius: 3px;
							-webkit-appearance: none;
							border-radius: 3px;
							white-space: nowrap;
							-webkit-box-sizing: border-box;
							-moz-box-sizing:    border-box;
							box-sizing:         border-box;
				
							-webkit-box-shadow: 0 1px 0 #ccc;
							box-shadow: 0 1px 0 #ccc;
						 	vertical-align: top;
						}
				
						.button.button-large {
							height: 30px;
							line-height: 28px;
							padding: 0 12px 2px;
						}
				
						.button:hover,
						.button:focus {
							background: #fafafa;
							border-color: #999;
							color: #23282d;
						}
				
						.button:focus  {
							border-color: #5b9dd9;
							-webkit-box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
							box-shadow: 0 0 3px rgba( 0, 115, 170, .8 );
							outline: none;
						}
				
						.button:active {
							background: #eee;
							border-color: #999;
						 	-webkit-box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
						 	box-shadow: inset 0 2px 5px -3px rgba( 0, 0, 0, 0.5 );
						 	-webkit-transform: translateY(1px);
						 	-ms-transform: translateY(1px);
						 	transform: translateY(1px);
						}
				
							</style>
				
				
					<div>			
							<h1> Give SalesAccelerator access to your sales information</h1>
							<p>Hi ' . $user->display_name . '!</p>
							<p>Our app would like to access information from your WooCommerce installation. Do you allow our app to access it? </p>
							<p><b>Note:</b> This information is kept safe and is only use by the app installed on your device.</p>
							<form action="/sales-acc_authentication_process" method="post"> 
							<input type="hidden" name="ck" value="' . $consumer_key . '" />
							<input type="hidden" name="cs" value="' . $consumer_secret . '" />
							<input type="submit" name="allow" value="Yes, I allow this app to access my information" class="button" />
							</form>
							<p><a href="/nevermind">Nevermind, I don\'t want to use this app anymore.</a></p>
							
							</div>			
				
					</body></html>';			
				echo $html;
			}
			else {
				echo '';
			}
    	}
    }        
}