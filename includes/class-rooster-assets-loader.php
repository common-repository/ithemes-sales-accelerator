<?php

if ( !defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/**
* This class loads core and module assets
**/

class IT_RST_AssetsLoader {
  /**
  * Path to the includes directory
  * @var string
  */
  private $js_include_path = '';
  private $css_include_path = '';

  /**
  * The Constructor
  */
  public function __construct() {
	  
    $this->js_include_path  = '/assets/js';
    $this->css_include_path = '/assets/css';
    
    // Enqueue Assets
    add_action( 'admin_enqueue_scripts', array( $this, 'autoload_backend' ) );
    add_action( 'wp_enqueue_scripts', 	 array( $this, 'autoload_frontend' ) );
    
    // Remove other plugin scripts on some plugin pages
    //add_action( 'admin_enqueue_scripts', array( $this, 'remove_backend_scripts' ), 9999 );
  }

  /**
  * Include a javascript file into wordpress
  * @param  string $path
  * @return bool successful or not
  */
  private function load_js_file( $path, $slug, $version, $type = 'path', $localize = array(), $full_path = false ) {
	  
	if ( !$version ) {
		$version = IT_RST_PLUGIN_VERSION;
	}
	
	if ( $type == 'url' ) {
    	wp_register_script( $slug, $path, array( 'jquery' ), $version, true );
    }
    else {
	    $path = ( $full_path ) ? $path : plugins_url( $path, IT_RST_PLUGIN_FILE);
	    wp_register_script( $slug, $path, array( 'jquery' ), $version, true );
    }
    
    foreach ( $localize as $k=>$v ) {
	    wp_localize_script( $slug, $k, $v );
    }
    
    wp_enqueue_script( $slug ); 
  }
  
  private function load_css_file( $path, $slug, $version, $type = 'path', $full_path = false ) {
	  
	if( $type == 'url' ){
    	wp_register_style( $slug, $path, false, $version );
    }
    else {
	    $path = ( $full_path ) ? $path : plugins_url( $path, IT_RST_PLUGIN_FILE);
	    wp_register_style( $slug, $path, false, $version );
    }
    
    wp_enqueue_style( $slug );
  }

  /**
  * Auto-load files on the backend
  */
  public function autoload_backend() {
	  
	$plugins_path = untrailingslashit( plugin_dir_path( IT_RST_PLUGIN_FILE ) );
	
	// Get user roles and insert it on admin.js through localize
	global $wp_roles, $it_rst_main_settings, $current_screen;
	
    $all_roles 		= $wp_roles->roles;
    $editable_roles = apply_filters( 'editable_roles', $all_roles );
    $roles 			= array();
    $statuses 		= wc_get_order_statuses();
    $order_statuses = array();
	$rst_dashboard  = isset( $_GET["rst_dashboard"] ) ? $_GET["rst_dashboard"] : get_option( 'rst_dashboard' );
    
    foreach ( $editable_roles as $k=>$v ) {
	    
	    // Load all roles but exclude administrator
	    if ( $k !== 'administrator' ) {
			$roles[] = array( 'name' => $v['name'], 'slug' => $k );   
		} 
    }
    
    unset( $statuses['wc-refunded'] );
    
    foreach ( $statuses as $k => $v ) {
	    $new_k 			  = str_replace( 'wc-', '', $k );
	    $order_statuses[] = array( 'value' => $v, 'key' => $new_k );
    }
    
    // Gets current plugin roles exception values
    $users_exception = array();
    
    if( isset( $it_rst_main_settings['plugin_roles_exceptions'] ) ){
	    $current_exceptons	= explode( ',', $it_rst_main_settings['plugin_roles_exceptions'] );
	  
	    foreach( $current_exceptons as $k => $user ){
	       $users_exception[] = current( new WP_User( $user ) );
	    }          
    }
    
    $dashboard 	   = ( get_option('rst_dashboard') ) ? '?rst_dashboard=1' : '';
    $dashboard_url = get_dashboard_url() . $dashboard;
    $total_net 	   = array( array( 'key' => 'total_shipping', 'value' => 'Shipping' ), array( 'key' => 'total_fees', 'value' => 'Fees' ), array( 'key' => 'total_taxes', 'value' => 'Taxes' ) );
                	
	// Loads core backend assets
	$backend_assets = 	array(
							array( 'slug' => 'admin-js', 'path' => $this->js_include_path . '/admin.js', 'type' => 'js', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'plugin_pages', 'dashboard_on' ), 'localize' => array( 'rst_ajax_settings' => array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ), 'it_admin_settings' => array( 'dashboard_url' => $dashboard_url, 'roles' => $roles, 'order_status' => $order_statuses, 'total_net' => $total_net, 'role_exceptions' => $users_exception ) ) ),
							array( 'slug' => 'admin-global-js', 'path' => $this->js_include_path . '/admin_global.js', 'type' => 'js', 'version' => IT_RST_PLUGIN_VERSION ),
							array( 'slug' => 'selectize-js', 'path' => $this->js_include_path . '/selectize.js', 'type' => 'js', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'plugin_pages', 'dashboard_on', 'product_pages' ) ),
							array( 'slug' => 'nice-select-js', 'path' => $this->js_include_path . '/jquery.nice-select.min.js', 'type' => 'js', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'plugin_pages', 'dashboard_on' ) ),
							array( 'slug' => 'admin-css', 	 'path' => $this->css_include_path . '/admin.css', 'type' => 'css', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'plugin_pages' ) ),
							array( 'slug' => 'admin-global-css', 	 'path' => $this->css_include_path . '/admin_global.css', 'type' => 'css', 'version' => IT_RST_PLUGIN_VERSION ),
							array( 'slug' => 'selectize-css','path' => $this->css_include_path . '/selectize.css', 'type' => 'css', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'plugin_pages', 'dashboard_on', 'product_pages' ) ),
							array( 'slug' => 'nice-select-css','path' => $this->css_include_path . '/nice-select.css', 'type' => 'css', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'plugin_pages', 'dashboard_on' ) ),		
							array( 'slug' => 'materialize', 'url' => '//cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/css/materialize.min.css', 'type' => 'css', 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
							array( 'slug' => 'font-quicksand','url' => '//fonts.googleapis.com/css?family=Quicksand:400,500', 'type' => 'css', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
							array( 'slug' => 'font-nunito','url' => '//fonts.googleapis.com/css?family=Nunito:400,800', 'type' => 'css', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
							array( 'slug' => 'sortable-js','url' => '//code.jquery.com/ui/1.10.4/jquery-ui.js', 'type' => 'js', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
							array( 'slug' => 'bootstrap-min-js','url' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', 'type' => 'js', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
							array( 'slug' => 'bootstrap-min-css','url' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', 'type' => 'css', 'version' => IT_RST_PLUGIN_VERSION, 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),	
							array( 'slug' => 'moment_js', 'url' => '//cdn.jsdelivr.net/momentjs/latest/moment.min.js', 'type' => 'js', 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
							array( 'slug' => 'daterangepicker_css', 'url' => '//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css', 'type' => 'css', 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
							array( 'slug' => 'daterangepicker_js', 'url' => '//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js', 'type' => 'js', 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
							array( 'slug' => 'font-awesome', 'url' => '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', 'type' => 'css', 'scope' => array( 'dashboard_on', 'plugin_pages' ) ),
						);
	
	$backend_assets = apply_filters( 'it_rst_backend_assets', $backend_assets );
		
	foreach ( $backend_assets as $asset ) {	
		if ( isset( $asset['path'] ) ) {
			$path 	 = $asset['path'];
			$type 	 = 'path';
			$slug 	 = $asset['slug'];
			$file 	 = str_replace( $plugins_path, "", $path );
		}
		else if ( isset( $asset['url'] ) ) {
			$file 	 = $asset['url'];
			$type 	 = 'url';
			$slug 	 = $asset['slug'];
		}
				
		$localize  = isset( $asset['localize'] ) ? $asset['localize']  : array();
		$version   = isset( $asset['version'])   ? $asset['version']   : '';
		$scope     = isset( $asset['scope']) 	 ? $asset['scope']     : array();
		$full_path = isset( $asset['full_path']) ? $asset['full_path'] : false;
		$run 	   = false;
				
		// No scope defined, run in every page
		if ( empty( $scope ) ){
			$run = true;
		}
		if ( !$run && in_array( 'dashboard_on', $scope ) ) {
			if ( $current_screen->base == "dashboard" && $rst_dashboard == "1" ) {
				$run = true;
			}
		}
		
		if ( !$run && in_array( 'dashboard_off', $scope ) ) {
			if ( !$current_screen->base == "dashboard" || $rst_dashboard !== "1" ) {
				$run = true;
			}
		}
		
		if ( !$run && in_array( 'plugin_pages', $scope ) ) {
			$page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : '';
			if ( strpos( $page, 'ithemes-sales-acc' ) !== false ) {
				$run = true;
			}
		}
				
		if ( !$run && isset( $scope['custom_page'] ) ) {
			$page = ( isset( $_GET['page'] ) ) ? $_GET['page'] : '';
			if ( strpos( $page, $scope['custom_page'] ) !== false ) {
				$run = true;
			}
		}
		
		if ( !$run && in_array( 'product_pages', $scope ) ) {
			global $post;
			if ( is_object( $post ) && $post->post_type == 'product' ) {
				$run = true;
			}	
		}
				
		if ( $slug && $type && $file && $run ) {
			if( $asset['type'] == 'js') {
				$this->load_js_file( $file, $slug, $version, $type, $localize, $full_path );
			}
			else if ($asset['type'] == 'css') {
				$this->load_css_file( $file, $slug, $version, $type, $full_path );
			}
		}
	}
  }

  /**
  * Auto-load files on the frontend
  */
  public function autoload_frontend() {
	$plugins_path = untrailingslashit( plugin_dir_path( IT_RST_PLUGIN_FILE ) );
	
	// Loads core frontend assets
	$frontend_assets = array(
				   	   		array( 'slug' => 'it-rst-main-js', 'path' => $this->js_include_path . '/main.js', 'type' => 'js', 'version' => IT_RST_PLUGIN_VERSION, 'localize' => array( 'rst_ajax_settings' => array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) ) ),				   	   		
				   	   		array( 'slug' => 'it-rst-main-css', 'path' => $this->css_include_path . '/main.css', 'type' => 'css', 'version' => IT_RST_PLUGIN_VERSION )
					   );
	
	$frontend_assets = apply_filters( 'it_rst_frontend_assets', $frontend_assets );
	
  	foreach ( $frontend_assets as $asset ) {	
	  	if( isset( $asset['path'] ) ) {
			$path 	 = $asset['path'];
			$type 	 = 'path';
			$slug 	 = $asset['slug'];
			$file 	 = str_replace( $plugins_path, "", $path );
		}
		else if ( isset( $asset['url'] ) ) {
			$file 	 = $asset['url'];
			$type 	 = 'url';
			$slug 	 = $asset['slug'];
		}
		
		$localize = isset( $asset['localize'] ) ? $asset['localize'] : array();
		$version  = isset( $asset['version'] )  ? $asset['version']  : '';
		$scope    = isset( $asset['scope'])     ? $asset['scope']    : array();
		$full_path = isset( $asset['full_path']) ? $asset['full_path'] : false;
		$run 	  = true;
					
		if( $slug && $type && $file && $run ){
			if( $asset['type'] == 'js' ){
				$this->load_js_file( $file, $slug, $version, $type, $localize, $full_path );
			}
			else if( $asset['type'] == 'css' ){
				$this->load_css_file( $file, $slug, $version, $type, $full_path );
			}
		}
	}
  }
  
  /**
  * Remove other plugin scripts on the dashboard
  **/
  public function remove_backend_scripts() {
	  
	  global $current_screen;
	  $rst_dashboard  = isset( $_GET["rst_dashboard"] ) ? $_GET["rst_dashboard"] : get_option( 'rst_dashboard' );
	  
	  // Remove all scripts except sales accelerator and woocommerce
	  if ( $current_screen->base == "dashboard" && $rst_dashboard == "1" ) {
		  
		  $plugins_url = plugins_url();
		  $wc_url      = plugins_url( '/', WC_PLUGIN_FILE );
	  
		  foreach ( wp_scripts()->registered as $script ) {
			  if ( ( strpos( $script->src, $plugins_url ) !== false ) && ( strpos( $script->src, IT_RST_PLUGIN_URL ) === false ) && ( strpos( $script->src, $wc_url ) === false ) ) {
				wp_dequeue_script( $script->handle );
			    wp_deregister_script( $script->handle );
			  }
		  }		  
	  }
  }
}