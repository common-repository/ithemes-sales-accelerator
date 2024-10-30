<?php

if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* Main Plugin class
*/
if( !class_exists( 'IT_RST_RoosterStart' ) ) {
	
	class IT_RST_RoosterStart {
	
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
			
	    	add_action( 'plugins_loaded', array( $this, 'init' ), 10 );
	    	
	    	// Add 1 min cronjob schedule
			add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );
		}
		
		// Notice that shows requirement of installing rest api plugin
		public function needs_rest_api_plugin_activation_notice() {
			
			// Show error notice if REST API is not active
	  		$it_rest_notice = __( 'Your wordpress version requires REST API plugin in order for SalesAccelerator to work: ', 'ithemes-sales-accelerator' );
	  		$download_label = __( 'Download here', 'ithemes-sales-accelerator' );
	  		$download_url 	= site_url() . '/wp-admin/plugin-install.php?s=WordPress+REST+API+(Version+2)&tab=search&type=term';
	  		
	  		echo "<div class='error'><p><strong>$it_rest_notice</strong><a href=$download_url>$download_label</a></p></div>";
		}
				
		public function woocommerce_error_activation_notice() {
	      	
	      	// Show error notice if WooCommerce is not active
	  		$woocommerce_notice = __( 'You need WooCommerce active in order to use SalesAccelerator.', 'ithemes-sales-accelerator' );
	  		echo "<div class='error'><p><strong>$woocommerce_notice</strong></p></div>";
	  	}
	  	
	  	public function woocommerce_version_error_activation_notice() {
		 	
		 	$min_version   = '3.0';
		 	
		 	// Show error notice if WooCommerce version is outdated
	  		$woocommerce_notice = __( "You need WooCommerce version $min_version+ in order to use SalesAccelerator.", 'ithemes-sales-accelerator' );
	  		echo "<div class='error'><p><strong>$woocommerce_notice</strong></p></div>"; 	
	  	}
	  	
	  	public function woocommerce_api_error_activation_notice() {
		  	
		  	// Show error notice if WooCommerce is not active
	  		$woocommerce_api_notice = __( 'You need WooCommerce Rest API option active in order to use SalesAccelerator. You can change it: ', 'ithemes-sales-accelerator' );
	  		$settings_label		    = __( 'Here', 'ithemes-sales-accelerator' );
	  		$settings_url 			= get_admin_url() . 'admin.php?page=wc-settings&tab=api';
	  		
	  		echo "<div class='error'><p><strong>$woocommerce_api_notice</strong><a href=$settings_url>$settings_label</a></p></div>";
	  	}
	  	
	  	public function init() {
		  	
		  	global $wp_version;
		  	$min_wc_version   = '3.0';
		  	if ( $wp_version >= 4.6 && $wp_version < 4.7 && !in_array( 'rest-api/plugin.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			  	// If so display warning message on admin notices
				add_action( 'admin_notices', array( $this, 'needs_rest_api_plugin_activation_notice' ) );
			}
			else {
		  	
			  	// Check if WooCommerce is inactive
			  	if ( !class_exists( 'WooCommerce' ) ) {
				  	
			    	// If so display warning message on admin notices
					add_action( 'admin_notices', array( $this, 'woocommerce_error_activation_notice' ) );
		    	}
		    	else {
			    	
			    	global $woocommerce;
			    	
			    	// Check if woocommerce api is disabled
			    	if( get_option('woocommerce_api_enabled') !== 'yes' ) {
				    	
				    	// If so display warning message on admin notices
						add_action( 'admin_notices', array( $this, 'woocommerce_api_error_activation_notice' ) );
			    	}
			    	else if ( version_compare( $woocommerce->version, $min_wc_version, "<" ) ) { 
				    	
				    	// If so display warning message on admin notices
						add_action( 'admin_notices', array( $this, 'woocommerce_version_error_activation_notice' ) );
			    	}
			    	else{
				    	
				    	add_action ( 'init', array( $this, 'check_new_version' ) );
				    	$this->add_constants();
				    	$this->init_rest();
				    	$this->init_modules();
			        	$this->includes();
			        	
			        	// Hide notices in plugin pages
						add_action( 'in_admin_header', array( $this, "hide_notices" ), 100000 );					
		        	}
		        }
	        }
	    }
	    	    	    
	    // Adds plugin constants
		public function add_constants() {
						
			// Add main settings variable
			global $it_rst_main_settings;
			$it_rst_main_settings = get_option( 'it_rooster_main_settings' );
			
			// If main settings do not exist yet create default options
			if( !$it_rst_main_settings ){
				$it_rst_main_settings = array( 'plugin_roles' => '', 'api_access' => '0', 'notifications' => '0' );
								
				update_option( 'it_rooster_main_settings', $it_rst_main_settings );
			}
		}
	    
		/**
		* Handles the translations
		*/
		public function load_plugin_textdomain() {
			
			load_plugin_textdomain( 'ithemes-sales-accelerator', FALSE, dirname( plugin_basename(__FILE__) ) . '/assets/translations' );
		}
	
		/**
		* Define constant if not already set
		*
		* @param string $name
		* @param string|bool $value
		*/
		private function define( $name, $value ) {
			
			if ( !defined( $name ) ) {
				define( $name, $value );
			}
		}
		
		// Checks if plugin has been updated
		public function check_new_version() {
			
			$plugin_version = IT_RST_PLUGIN_VERSION;
			$last_version 	= get_option( 'rst_reporting_plugin_version' );
								
			// Is it a new version?
			if( $plugin_version !== $last_version ){
				flush_rewrite_rules();
			}
		}
		
		// Hide notices from plugin pages
		public function hide_notices() {
			
			$permissions = IT_RST_Permissions::get_instance();
		    		    		    		    		    
		    // Show menu and submenus only to users with selected roles
		    if( $permissions->check_permission( 'core' ) ) {
				
				$page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : '';
				if ( strpos( $page, 'ithemes-sales-acc' ) !== false ) {
					
					global $wp_filter;
							
					if( is_network_admin() && isset( $wp_filter['network_admin_notices'] ) ) {
						unset( $wp_filter['network_admin_notices'] ); 
					}
					elseif( is_user_admin() && isset( $wp_filter['user_admin_notices'] ) ) {
						unset($wp_filter['user_admin_notices'] ); 
					}
					else{
						if( isset( $wp_filter['admin_notices'] ) ) {
							unset( $wp_filter['admin_notices'] ); 
						}
					}
					
					if( isset( $wp_filter['all_admin_notices'] ) ) {
						unset( $wp_filter['all_admin_notices'] ); 
					}
				}
			}
		}
		
		// Includes of our plugin
		public function includes() {
			
			// Load Assets
			new IT_RST_AssetsLoader();
			
			// Initialize permissions
			new IT_RST_Permissions();
			
			// Load Endpoints
			new IT_RST_Endpoints();
			
			// Load Rest Endpoints
			new IT_RST_Rest();
					
			// Settings loader
			new IT_RST_Settings_Controller();
			
			// Plugin Menu
	        new IT_RST_Menu();
	        
	        // Initialize connection with Ithemes API
	        new IT_iThemes_API();
	        
	        // Initialize Notification System
	        IT_View_Notifications::get_instance();

	        // Initiates core general features
	        new IT_RST_General_Features();
	        
	        // Initiate privacy policy hooks
	        new IT_RST_Privacy_Manager();
		}
		
		/** 
		*	Init rest endpoint files
		*/
		public function init_rest() {
			
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/class-rooster-rest.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/class-privacy.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-woocommerce-orders.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-woocommerce-products.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-woocommerce-customers.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-woocommerce-shipping.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-modules-endpoint.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-settings-endpoint.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-wordpress-users.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-wordpress-media.php';
			require_once dirname( IT_RST_PLUGIN_FILE ) . '/includes/endpoints/class-rooster-api-endpoint.php';
		}
		
		/**
		* Initialize Rooster Modules
		*/
		public function init_modules() {
			
			global $rst_modules;
			
			require_once IT_RST_PLUGIN_PATH . 'modules/load.php';
			
			$modules 	   = apply_filters( 'it_rst_available_modules', array() );
			$module_status = get_option( 'it_rooster_modules_status' );
								
			foreach ( $modules as $module ) {
				$external  = ( isset( $module['external'] ) )    ? $module['external']    : false;
				$available = ( isset( $module['available'] ) )   ? $module['available']   : true;
				if ( !$external && $available ) {
					if ( ( isset( $module_status[$module['slug']] ) && isset( $module_status[$module['slug']]['active'] ) && $module_status[$module['slug']]['active'] ) || ( isset( $module['extends'] ) && $module['extends'] && isset( $module_status[$module['extends']]['active'] ) && $module_status[$module['extends']]['active'] ) ) {
						$path = $module['path'];
						if ( $path ) {
							include_once IT_RST_PLUGIN_PATH . 'modules/' . $module['path'];
							$classname = $module['class'];
							new $classname();
						}
					}			
				}
			}
			
			$rst_modules = $modules;
			do_action( 'it_rst_modules_initiated', $modules, $module_status );
		}
			
		/**
		* Plugin deactivated
		*/
		public static function deactivate_plugin() {
			
			update_option( 'it_rooster_deactivated', 1 );
		}
		
		public function add_cron_schedule( $schedules ) {
	    
	      if ( !isset( $schedules['1min'] ) ) {
	        $schedules["1min"] = array(
	            'interval' => 60,
	            'display' => __( 'Once every 1 minutes' ),
	        );
	      }
		  return $schedules;
	  	}
	}
}