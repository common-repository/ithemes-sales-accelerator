<?php 
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles reporting module dashboard 
**/
		
class RST_Reporting_Dashboard {
	
	public function __construct() {
		$this->init();
	}

	public function init() {
					
		// Adds togle button
		add_action( 'in_admin_header', 			array( $this, 'rst_toggle_button'), 1 );
		
		// Cleans dashboard
		add_action( 'wp_dashboard_setup', 		array( $this, 'rst_clean_dashboard'), 9999 );
		
		// Adds dashboard link
		add_action( 'admin_menu', 		    	array( $this, 'rst_dashboard_link') );
		
		// Removes screen options
		add_action( 'contextual_help', 			array( $this, 'rst_remove_screen_options' ) );

		// Redirects logins to the module dashboard if needed
		add_filter( 'login_redirect', 			array( $this, 'rst_admin_page'), 10, 3 );
		
		// Hide notices in dashboard
		add_action( 'in_admin_header', 			array( $this, 'skipNotices' ), 100000 );
		
		// Remove dashboard widgets when on reporting dashboard
		add_action( 'wp_dashboard_setup', 	    array( $this, 'remove_dashboard_widgets' ), 9999 );
		
		// Menu Report Shortcode
		add_shortcode( 'rst_menu_report', 		array( $this, 'rst_menuReport' ) );
		
		// Menu Premium Shortcode
		add_shortcode( 'rst_menu_premium', 		array( $this, 'rst_menuPremium' ) );
		
		// Menu Orders Shortcode
		add_shortcode( 'rst_menu_orders', 		array( $this, 'rst_menuOrders' ) );
		
		// Menu Products Shortcode
		add_shortcode( 'rst_menu_products', 	array( $this, 'rst_menuProducts' ) );
		
		// Menu Customers Shortcode
		add_shortcode( 'rst_menu_customers',	array( $this, 'rst_menuCustomers' ) );
		
		// Menu Digest Shortcode
		add_shortcode( 'rst_menu_digest', 		array( $this, 'rst_menuDigest' ) );
		
		// Menu Integrations Shortcode
		add_shortcode( 'rst_menu_integrations', array( $this, 'rst_menuIntegrations' ) );
		
		// Dashboard Manager Shortcode
		add_shortcode( 'rst_manage_dashboard', 	array( $this, 'rst_managerDashboard' ) );
		
		// Arrow Up Shortcode
		add_shortcode( 'rst_arrow_up', 			array( $this, 'rst_arrowUp' ) );
		
		// Arrow Down Shortcode
		add_shortcode( 'rst_arrow_down', 		array( $this, 'rst_arrowDown' ) );
	}
			
	// Hides notices from dashboard
	public function skipNotices() {
		
		$permissions = IT_RST_Permissions::get_instance();
	    		    		    		    		    
	    // Show menu and submenus only to users with selected roles
	    if ( is_admin() && $permissions->check_permission( 'reporting' ) ) {
		    $screen = get_current_screen();
			$rst_dashboard = isset( $_GET['rst_dashboard'] ) ? $_GET['rst_dashboard'] : '';
			
			if ( ( isset( $_GET['rst_dashboard'] ) && $_GET['rst_dashboard'] ) || ( !isset( $_GET['rst_dashboard'] ) && $screen->id == 'dashboard' ) ) {
				global $wp_filter;
						
				if ( is_network_admin() && isset( $wp_filter['network_admin_notices'] ) ) {
					unset( $wp_filter['network_admin_notices'] ); 
				}
				elseif ( is_user_admin() && isset( $wp_filter['user_admin_notices'] ) ) {
					unset( $wp_filter['user_admin_notices'] ); 
				}
				else {
					if ( isset( $wp_filter['admin_notices'] ) ) {
						unset( $wp_filter['admin_notices'] ); 
					}
				}
				
				if ( isset( $wp_filter['all_admin_notices'] ) ) {
					unset( $wp_filter['all_admin_notices'] ); 
				}
			}
		}
	}
	
	// Removes dashboard widgets
	public function remove_dashboard_widgets() {
		
		$permissions = IT_RST_Permissions::get_instance();
	    		    		    		    		    
	    // Show menu and submenus only to users with selected roles
	    if ( $permissions->check_permission( 'reporting' ) ) {

			global $wp_meta_boxes;
			$rst_dashboard = isset( $_GET['rst_dashboard'] ) ? $_GET['rst_dashboard'] : '';
			
			if ( $rst_dashboard == '' ) {
				$rst_dashboard = get_option('rst_dashboard');
			}
			
			if ( $rst_dashboard == '1' ) {
		
				global $wp_meta_boxes;
			    $wp_meta_boxes['dashboard']['normal']['core'] = array();
			    $wp_meta_boxes['dashboard']['side']['core']   = array();
			}
		}
	}
	
	public function rst_clean_dashboard() {
		
		$permissions = IT_RST_Permissions::get_instance();
	    		    		    		    		    
	    // Show menu and submenus only to users with selected roles
	    if ( $permissions->check_permission( 'reporting' ) ) {

			global $wp_meta_boxes;
			$rst_dashboard = isset( $_GET['rst_dashboard'] ) ? $_GET['rst_dashboard'] : '';
			
			if ( $rst_dashboard == '' ) {
				$rst_dashboard = get_option('rst_dashboard');
			}
			
			if ( $rst_dashboard == '1' ) {
				// Remove Welcome Panel
				remove_action( 'welcome_panel', 'wp_welcome_panel' );
				
				// Remove Meta Boxes from Normal Dashboard
				foreach ( $wp_meta_boxes['dashboard']['normal'] as $boxes ) {
					foreach ( $boxes as $box ) {
						remove_meta_box( $box['id'], 'dashboard', 'normal' );
					}
				}

				// Remove Meta Boxes from Side Dashboard
				foreach ( $wp_meta_boxes['dashboard']['side'] as $boxes ) {
					foreach ( $boxes as $box ) {
						remove_meta_box( $box['id'], 'dashboard', 'side' );
					}
				}
				
				// Add main Widget to Reporting Dashboard
				add_meta_box( 'rst-main-widget', 'SalesAccelerator - Reporting', array( $this, 'rst_main_widget_dashboard' ), 'dashboard', 'normal', 'low' );
			}
			else {
				update_option( 'rst_dashboard','0' );
			}
		}
	}		
	
	public function rst_toggle_button() {
		
		$permissions = IT_RST_Permissions::get_instance();
	    		    		    		    		    
	    // Show menu and submenus only to users with selected roles
	    if ( $permissions->check_permission( 'reporting' ) ) {
		    
			global $current_screen;
			$pluginURL = plugin_dir_url(__FILE__);
			
			if ( $current_screen->base == "dashboard" ) {
				$rst_dashboard = isset( $_GET["rst_dashboard"] ) ? $_GET["rst_dashboard"] : '';
				
				if ( !$rst_dashboard ) {
					$rst_dashboard = get_option('rst_dashboard');
				}
				
				if ( $rst_dashboard == '1' ) {
				
					$import_status = get_option( 'it_rooster_reporting_import_status' );
					$progress      = isset( $import_status['progress'] ) ? $import_status['progress'] : 0;
					$time_string = __( 'Import is starting', 'ithemes-sales-accelerator' );
					
					$progress = round( $progress, 1 );
					
				    $dashboard_url = get_dashboard_url();
				   
					if ( $progress && $progress < 100 ) {
						
						// Average refresh time ( in seconds )
						$period     	  = 60;
						$items_per_period = 400;
						$time_left 		  = 0;
						$time_string      = __( 'Import completed', 'ithemes-sales-accelerator' );
						
						$tot_products = ( isset( $import_status['total_products'] ) )    ? $import_status['total_products']    : 0;
						$tot_order    = ( isset( $import_status['total_orders'] ) )      ? $import_status['total_orders']      : 0;
						$imp_products = ( isset( $import_status['imported_products'] ) ) ? $import_status['imported_products'] : 0;
						$imp_order    = ( isset( $import_status['imported_orders'] ) )   ? $import_status['imported_orders']   : 0;
						$items_left   = $tot_products + $tot_order - $imp_products - $imp_order;
						
						if ( $items_left > 0 ) {
							$time_left = ( $items_left / $items_per_period ) * $period;
							$time_left_mins = round( $time_left / 60, 0 );
							if ( !$time_left_mins ) {
								$time_left_mins = 1;
							}
							
							$mins_string = ( $time_left_mins == 1 ) ? __( 'minutes remaining', 'ithemes-sales-accelerator' ) : __( 'minutes remaining', 'ithemes-sales-accelerator' );
							$time_string = '(' . __( 'approx.', 'ithemes-sales-accelerator' ). ' ' . $time_left_mins . ' ' . $mins_string . ')';
						}
						else{
							$progress = 100;
						}
					}
					$html_div = '';
					
					update_option( 'rst_dashboard', '1' );
					if ( $progress < 100 ) { 
					$html_div = '
								<! --- Modal --- !>
								<div class="modal-modules">
									<div id="modal_content">
											<div class="box_content">
												<img src="' . IT_RST_PLUGIN_URL . '/assets/img/sales-acc_icon_blue.svg">
												<h3>' . __("Welcome to Sales Accelerator", "ithemes-sales-accelerator") . '</h3>
												<p>' . __("Get detailed data and e-commerce insights about your online store.", "ithemes-sales-accelerator") . '</p>
												<div style="width:70%; margin: 0 auto;"><div class="progress it_rooster_reporting_progressbar">
													  <div id="it_rooster_reporting_import_progressbar_top" class="progress-bar active" role="progressbar"
													  aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100" style="width:' .  $progress . '%">' . $progress . '%
													  </div>
													</div>
													<p class="txt_remaining" style="padding: 0 !important; text-align:center;">' . $time_string . '</p></div>
												</div>
									</div>
								</div>
								<! --- Modal --- !>
								<script>
									jQuery("#modal_content").css("margin-top",(jQuery(".modal-modules").height()-jQuery("#modal_content").height()-jQuery("#wpadminbar").height()-70)/2);
									jQuery("body").css("overflow","hidden");
								</script>
								';
					}
									
					$html_div .= '<div id="rst_reporting_wrapper" '; 
					
					if ( $progress < 100 ) { 
						$html_div .= 'class="blur"'; 
					}
						
					$html_div .= '>
								<div class="rst_reporting_switch">
									<label class="switchhs">
										<input type="checkbox" id="rst_switch" checked>
										<div class="sliderhs"></div>
									</label>
									<h2 class="h2_switch">' . __( 'SALES ACCELERATOR DASHBOARD', 'ithemes-sales-accelerator' ) . '</h2>

									<div class="logo_wrapper"><img class="hs_logo" src="' . $pluginURL . '/assets/img/sales-acc_logo.svg" style="height:50px"></div>

								</div>
								<div class="title_wrapper">
									<div class="row">
										<div class="col s12 m12 l12 xl12">
											<div class="title"><h1> ' . get_bloginfo('name') . '</h1></div>
										</div>	
									</div>
								</div>';
								
								$html_div .= '<div class="menu_buttons_wrapper inline">
								<div class="row">
									<div class="col s10 m10 l6 xl6 search_mobile1">';
									$menu_options = '<div class="menu_buttons inline">
										<a href="https://ithemes.com/sales-accelerator" target="_blank"><div class="menu_upgrade" style="min-width:100px; text-align:center;">' . do_shortcode( "[rst_menu_premium]" ) . 'Upgrade</div></a>
									</div>';
									$menu_options .= '</div>
									<div class="col s10 m10 l6 xl6 search_mobile2" style="text-align:right;"><div class="inline">';
								
									$menu_options = apply_filters( 'it_rst_reporting_dashboard_options_menu', $menu_options );
											
									$permissions = IT_RST_Permissions::get_instance();
    		    		    		    
								    // Show menu and submenus only to users with selected roles
								    if ( $permissions->check_permission( array( 'reporting', 'core' ) ) ) {
										$menu_options .= '<a href="' . get_admin_url() . 'admin.php?page=ithemes-sales-acc-plugin-settings"><div class="rst_manage_dashboard"> ' . do_shortcode( "[rst_manage_dashboard]" ) . ' ' . __( 'Manage Dashboard', 'ithemes-sales-accelerator' ) . '</div></a>';
									}
										
									$menu_options .= '</div></div>';
									$menu_options = apply_filters( 'it_rst_reporting_dashboard_menu_options', $menu_options );
									$html_div .= $menu_options;

									$html_div .= '
									<div class="col s2 m2 l6 xl6 search_mobile2" style="text-align:right; position: relative;"><div class="inline">';
									$html_div = apply_filters( 'it_rst_reporting_dashboard_menu_buttons', $html_div );
									$html_div = apply_filters( 'it_rst_reporting_dashboard_options_menu', $html_div );
									$html_div .= '</div></div>';
									$html_div .= '</div></div>';
								
							$html_div .= '</div>
							<div class="img_footer"><img src="' . $pluginURL . '/assets/img/sales-acc_footer.svg"></div>';
							
					echo $html_div;
				}
				else { 
					echo '<script>jQuery("#rst_switch").change(function() {if(jQuery(this).is(":checked")) {window.location = document.location.origin + "/wp-admin/?rst_dashboard=1";} else {window.location = document.location.origin + "/wp-admin/?rst_dashboard=0"}});</script><div class="rst_reporting_switch"><label class="switchhs"><input type="checkbox" id="rst_switch"><div class="sliderhs"></div></label><h2 class="h2_switch">' . __( 'SALESACCELERATOR DASHBOARD', 'ithemes-sales-accelerator' ) . '</h2></div>';
				}
			}
		
		}
	}
	
	public function rst_admin_page( $redirect_to, $request, $user ) {
		
		$url   = parse_url( $redirect_to );
		$query = isset($url['query']) ? $url['query'] : '';
		
		if ( current_user_can( 'manage_woocommerce' ) ) {
		
			$rst_dashboard = get_option('rst_dashboard');
			
			if ( !$rst_dashboard ) {
				$rst_dashboard = get_option('rst_dashboard');
			}
			
			if ( $rst_dashboard == '1' ) {
				if ( $query && strpos( $query, 'rst_dashboard=1' ) === false ) {
					return $redirect_to . '&rst_dashboard=1';
				}
				else if ( strpos($query, 'rst_dashboard=1') === false ) {
					return $redirect_to . '?rst_dashboard=1';
				}
			}
		}
		
		return $redirect_to;
	}
	
	public function rst_dashboard_link() {
		
	    global $menu;
	    global $submenu;
	    
	    $rst_dashboard = get_option('rst_dashboard');
	    
	    if ( isset( $_GET['rst_dashboard'] ) == '1' || $rst_dashboard == '1' ){
	    	$submenu['index.php'][0][2] = 'index.php?rst_dashboard=1';	
	    }
	}
	
	public function rst_remove_screen_options( $screen ) {
		
		global $current_screen;
		if ( $current_screen->base == 'dashboard' ) {
			$rst_dashboard = isset( $_GET['rst_dashboard'] ) ? $_GET['rst_dashboard'] : '';
			
			if ( !$rst_dashboard ) {
				$rst_dashboard = get_option('rst_dashboard');
			}
			
			if ( $rst_dashboard == '1' ) {
				
				// Remove Help Button
			    $screen = get_current_screen();
				$screen->remove_help_tabs();
				
				// Remove Options Button
				add_filter( 'screen_options_show_screen', '__return_false' );
			}
		}
	}
	
	public function searchForId( $id, $array ) {
		
	   foreach ( $array as $key => $val ) {
	       if ( $val['uid'] === $id ) {
	           return $key;
	       }
	   }
	   return null;
	}
	
	public function rst_main_widget_dashboard( ) {
		
		$pluginURL = plugin_dir_url(__FILE__);
		$html 	   = '';
		$boxCoord  = get_user_meta( get_current_user_id(), 'it_rooster_box_coord', true ); 
		$html 	  .= "<script>var main_dashboard = 1; var admin_url = '" . admin_url() . "';"; 
		
		if ( $boxCoord ) {
			$html .= "var serializedData = '" . $boxCoord . "';";
		}
				
	    $html .= '</script>';
	    
	    global $it_rst_dashboard_settings, $it_rst_reporting_settings, $it_rst_reporting_feature_settings;
	    
	    $it_rst_dashboard_settings = apply_filters( 'it_rst_reporting_dashboard_settings', $it_rst_dashboard_settings );
		
		/*
		 * Get all Fast Facts selected
		*/ 
	    $fastfacts = explode( ',', $it_rst_dashboard_settings['fast_facts'] );
	    
	    /*
		 * Get all Overview reports selected
		*/
	    $overview  = explode( ',', $it_rst_dashboard_settings['overview'] );
	    
	    /*
		 * Get Table report selected
		*/
		$table 	   = explode( ',', $it_rst_dashboard_settings['table'] );
		
		/*
		 * Get Charts reports selected
		*/
		$chart 	   = explode( ',', $it_rst_dashboard_settings['charts'] );

		$import_status = get_option( 'it_rooster_reporting_import_status' );
		$progress      = isset( $import_status['progress'] ) ? $import_status['progress'] : 0;
		$time_string   = __( 'Import is starting', 'ithemes-sales-accelerator' );
		
		$progress = round( $progress, 1 );
		
		if( $progress && $progress < 100 ){
			
			// Average refresh time ( in seconds )
			$period     	  = 60;
			$items_per_period = 150;
			$time_left 		  = 0;
			$time_string      = __( 'Import_completed', 'ithemes-sales-accelerator' );
			$tot_products 	  = ( isset( $import_status['total_products'] ) )    ? $import_status['total_products']    : 0;
			$tot_order    	  = ( isset( $import_status['total_orders'] ) )      ? $import_status['total_orders']      : 0;
			$imp_products 	  = ( isset( $import_status['imported_products'] ) ) ? $import_status['imported_products'] : 0;
			$imp_order    	  = ( isset( $import_status['imported_orders'] ) )   ? $import_status['imported_orders']   : 0;
			$items_left   	  = $tot_products + $tot_order - $imp_products - $imp_order;
			
			if ( $items_left > 0 ) {
				$time_left 		= ( $items_left / $items_per_period ) * $period;
				$time_left_mins = round( $time_left / 60, 0 );
				if ( !$time_left_mins ) {
					$time_left_mins = 1;
				}
				
				$mins_string = ( $time_left_mins == 1 ) ? __( 'minutes remaining', 'ithemes-sales-accelerator' ) : __( 'minutes remaining', 'ithemes-sales-accelerator' );
				$time_string = '(' . __( 'approx.', 'ithemes-sales-accelerator' ). ' ' . $time_left_mins . ' ' . $mins_string . ')';
			}
			else {
				$progress = 100;
			}
		}
	    
	    if ( $progress < 100 ) {
	    	$html .= '<div id="importing_blur" class="blur">';
	    }
	    
	    $grid_classes = 'data-gs-no-move="1" data-gs-locked="yes"';
	    $grid_classes = apply_filters( 'it_rst_reporting_dashboard_grid_classes', $grid_classes );
	    
	    $html .= '
			<div class="grid-stack">
				<div id="00" class="grid-stack-item fast-facts box"
			        data-gs-x="0" data-gs-y="0"
			        data-gs-width="9" data-gs-height="2" data-gs-id="00" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes">
			            <div class="grid-stack-item-content clear_box">
			            	<div class="grid-stack nested-grid3">
			            		<div id="title-1" class="grid-stack-item box-title"
							        data-gs-x="0" data-gs-y="0"
							        data-gs-width="12" data-gs-height="1" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes">
							            <div class="grid-stack-item-content clear_box inline">
							            	<h1>' . __( 'Fast Facts', 'ithemes-sales-accelerator' ) . '</h1>
							            </div>
							    </div>';
							    $ff = 1;
							    $x_axis = 0;
								foreach ( $fastfacts as $item ) {
									
									$ff_class = '';
									$ff_class = apply_filters( 'it_rst_reporting_dashboard_fastfacts_class', $ff_class, $item );
									$html .= '<div id="ff-' . $ff . '" class="grid-stack-item ' . $item . '"
							        data-gs-x="' . $x_axis . '" data-gs-y="1"
							        data-gs-width="3" data-gs-height="1" data-gs-id="ff-' . $ff . '" data-gs-no-resize="1"' . $grid_classes . ' data-gs-name="' . $item . '" data-gs-function="' . $item . '" data-gs-timer="' . $it_rst_reporting_feature_settings[$item]["refresh"]  . '" data-gs-type="box"'. $ff_class . '>';

							        $html .=
							            '<div class="grid-stack-item-content">
							            
									            	<div class="spinner-wrapperFF">
								            			<div class="sk-fading-circle">
														  <div class="sk-circle1 sk-circle"></div>
														  <div class="sk-circle2 sk-circle"></div>
														  <div class="sk-circle3 sk-circle"></div>
														  <div class="sk-circle4 sk-circle"></div>
														  <div class="sk-circle5 sk-circle"></div>
														  <div class="sk-circle6 sk-circle"></div>
														  <div class="sk-circle7 sk-circle"></div>
														  <div class="sk-circle8 sk-circle"></div>
														  <div class="sk-circle9 sk-circle"></div>
														  <div class="sk-circle10 sk-circle"></div>
														  <div class="sk-circle11 sk-circle"></div>
														  <div class="sk-circle12 sk-circle"></div>
														</div>
													</div>
													<div class="content"></div>
															
									            </div>
									    </div>';
									$ff++;
									$x_axis = $x_axis + 3; 
								}
								
							$html .= '</div>
						</div>
				</div>
							
			    <div id="0" class="grid-stack-item box"
			        data-gs-x="0" data-gs-y="2"
			        data-gs-width="9" data-gs-height="5" data-gs-id="0" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes" style="z-index:1;">
			            <div class="grid-stack-item-content clear_box">
			            	<div class="grid-stack nested-grid">
			            		<div id="title-1" class="grid-stack-item box-title"
							        data-gs-x="0" data-gs-y="0"
							        data-gs-width="12" data-gs-height="1" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes">
							            <div class="grid-stack-item-content clear_box inline">
							            	<div class="inline"><h1>Overview</h1><span class="selected_date">' . __( 'Last 30 Days', 'ithemes-sales-accelerator' ) . '</span>
												<div class="inline sd_div" style="width:100%;">
													<div style="width: 100%;display: flex;bottom: 11px;position: absolute;padding-left: 12px;">
														<div class="sk-circle">
														  <div class="sk-circle1 sk-child"></div>
														  <div class="sk-circle2 sk-child"></div>
														  <div class="sk-circle3 sk-child"></div>
														  <div class="sk-circle4 sk-child"></div>
														  <div class="sk-circle5 sk-child"></div>
														  <div class="sk-circle6 sk-child"></div>
														  <div class="sk-circle7 sk-child"></div>
														  <div class="sk-circle8 sk-child"></div>
														  <div class="sk-circle9 sk-child"></div>
														  <div class="sk-circle10 sk-child"></div>
														  <div class="sk-circle11 sk-child"></div>
														  <div class="sk-circle12 sk-child"></div>
														</div>
														<span class="saving_dashboard">' . __( 'Saving Dashboard', 'ithemes-sales-accelerator' ) . '</span>
													</div>
												</div>
											</div>
							            </div>
							    </div>';
							    
							    $ovw = 1;
							    $x_axis = 0;
							    
								foreach ( $overview as $item ) {

									if ( $ovw <= 3 ) :
								    	$border 		= 5;
								    	$height 		= 2;
								    	$y_axis 		= 1;
								    	$type_content 	= 'big_box';
								    else :
								    	$border 		= 1;
								    	$height 		= 1;
								    	$type_content 	= 'little_box';
								    endif ;
								    
								    if ( $x_axis == '12' ) :
								    	$x_axis = 0;
								    	$y_axis	= $y_axis + 2;
								    endif ;
								    
								    $ov_class = '';
								    $ov_class = apply_filters( 'it_rst_reporting_dashboard_overview_class', $ov_class, $item );
								    
								    $refresh_value = isset( $it_rst_reporting_feature_settings[$item]["refresh"] ) ? $it_rst_reporting_feature_settings[$item]["refresh"] : 10000;
								
									$html .= '<div id="overview-' . $ovw . '" class="grid-stack-item ' . $item . '"
							        data-gs-x="' . $x_axis . '" data-gs-y="' . $y_axis . '"
							        data-gs-width="4" data-gs-height="' . $height . '" data-gs-id="overview-' . $ovw . '" data-gs-no-resize="1"' . $grid_classes . ' data-gs-name="' . $item . '" data-gs-function="' . $item . '" data-gs-chart="1" data-gs-timer="' . $refresh_value  . '" data-gs-type="box" data-gs-borderGraph="' . $border . '"' . $ov_class . '>';
							        $html .= '
							            <div class="grid-stack-item-content">
									            	<div class="spinner-wrapper">
								            			<div class="sk-fading-circle">
														  <div class="sk-circle1 sk-circle"></div>
														  <div class="sk-circle2 sk-circle"></div>
														  <div class="sk-circle3 sk-circle"></div>
														  <div class="sk-circle4 sk-circle"></div>
														  <div class="sk-circle5 sk-circle"></div>
														  <div class="sk-circle6 sk-circle"></div>
														  <div class="sk-circle7 sk-circle"></div>
														  <div class="sk-circle8 sk-circle"></div>
														  <div class="sk-circle9 sk-circle"></div>
														  <div class="sk-circle10 sk-circle"></div>
														  <div class="sk-circle11 sk-circle"></div>
														  <div class="sk-circle12 sk-circle"></div>
														</div>
													</div>
													<div class="content ' . $type_content . '"></div>
									            </div>
									    </div>';
									$ovw++;
									$x_axis = $x_axis + 4; 
								}
							    
			            		$html .= '</div>
			            </div>
			    </div>
			    <div id="7" class="grid-stack-item box" style="z-index:0;"
				        data-gs-x="9" data-gs-y="0"
				        data-gs-width="3" data-gs-height="18" data-gs-id="7" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes">
				            <div class="grid-stack-item-content clear_box">
				            	<div class="grid-stack ">
					            	<div id="title-2" class="grid-stack-item box-title"
								        data-gs-x="0" data-gs-y="0"
								        data-gs-width="12" data-gs-height="1" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes">
								            <div class="grid-stack-item-content clear_box"><div class="inline"><h1>' . __( 'Recent Orders', 'ithemes-sales-accelerator' ) . '</h1> <span class="everything">' . __( 'Everything', 'ithemes-sales-accelerator' ) . '</span></div></div>
								    </div>';

								    if ( ! defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) ) {
									   
									    $html .= '<div id="box-newslletter" class="grid-stack-item"
									        data-gs-x="0" data-gs-y="1" data-gs-id="box-newslletter"
									        data-gs-width="12" data-gs-height="3" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes">
									            <div class="grid-stack-item-content ">
									            		
														<div class="content">
															<h1 style="padding: 10px 0; font-size: 1em !important;line-height: 18px;">Free Ebook: 5 Ways to Make More Money with WooCommerce </h1>
															<div class="inline" style="width:100%; padding: 10px 0;">
																<div class="nws1" style="width: 80%; padding-right: 10px;">Get tips for maximizing the<br/>income of your WooCommerce<br/>store + more WordPress<br/>e-commerce tips & updates</div>
																<div class="nws2" style="width: 100%;">Get tips for maximizing the income of your WooCommerce store + more WordPress e-commerce tips & updates</div>
																<div class="nws3" style="width: 20%; background: url(\'' . $pluginURL . '/assets/img/ebook-icon.png\'); background-position: center center;background-size: 100%;background-repeat: no-repeat;"></div>
															</div>
															<!-- Begin MailChimp Signup Form -->
															<div id="mc_embed_signup" style="margin-top:10px;">
																<form action="//ithemes.us2.list-manage.com/subscribe/post?u=7acf83c7a47b32c740ad94a4e&amp;id=27944208b3" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
																	<div id="mc_embed_signup_scroll" class="inline" style="width:100%;">																			
																		<div class="mc-field-group" style="width:80%;">
																			<input type="email" value="" name="EMAIL" class="required email" placeholder="Your email address" id="mce-EMAIL" style="margin-bottom:0; width:90%;">
																		</div>
																		<div id="mce-responses" class="clear" style="display:none;">
																			<div class="response" id="mce-error-response" style="display:none"></div>
																			<div class="response" id="mce-success-response" style="display:none"></div>
																		</div>
																		<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_7acf83c7a47b32c740ad94a4e_27944208b3" tabindex="-1" value=""></div>
																		<input type="submit" value="" name="subscribe" id="mc-embedded-subscribe" class="button" style="width:20%; background: url(\'' . $pluginURL . '/assets/img/send_newsletter.svg\');background-color: #2196F3;box-shadow: none; z-index:99; border-color: #2196F3; margin-left: 5px;background-position: center center;background-size: 30%;background-repeat: no-repeat;padding: 5px 16px;height: 27px;">
																    </div>
																</form>
															</div>
															<!--End mc_embed_signup-->
														</div>
												</div>
									    </div>';
									    
									    $ro_y = 4;
									    $ro_height = 14;
								    } else {
									    $ro_y = 1;
									    $ro_height = 17;
								    }
								    
								    $html .= '<div id="13" class="grid-stack-item box-recent-orders"
								        data-gs-x="0" data-gs-y="' . $ro_y . '" data-gs-id="13"
								        data-gs-width="12" data-gs-height="' . $ro_height . '" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes" data-gs-name="box-recent-orders" data-gs-function="recent_orders" data-gs-timer="300000" data-gs-type="box" data-gs-liveUpdate="0">
								        	<div class="spinner-loader"></div>
								            <div class="grid-stack-item-content ">
								            		<div class="spinner-wrapper">
								            			<div class="sk-fading-circle">
														  <div class="sk-circle1 sk-circle"></div>
														  <div class="sk-circle2 sk-circle"></div>
														  <div class="sk-circle3 sk-circle"></div>
														  <div class="sk-circle4 sk-circle"></div>
														  <div class="sk-circle5 sk-circle"></div>
														  <div class="sk-circle6 sk-circle"></div>
														  <div class="sk-circle7 sk-circle"></div>
														  <div class="sk-circle8 sk-circle"></div>
														  <div class="sk-circle9 sk-circle"></div>
														  <div class="sk-circle10 sk-circle"></div>
														  <div class="sk-circle11 sk-circle"></div>
														  <div class="sk-circle12 sk-circle"></div>
														</div>
													</div>
													<div class="content"></div>
											</div>
								    </div>
								</div>
				            
				            </div>
				</div>';
			    
			    $reporting_features = RST_Reporting_Features::get_instance();
			    $filterable 		= $reporting_features->is_feature_filterable( $table[0] );
			    
			    $tb_class = '';
				$tb_class = apply_filters( 'it_rst_reporting_dashboard_table_class', $tb_class, $table[0] );
								    
			    $html .= '<div id="8" class="grid-stack-item table-content"
			        data-gs-x="0" data-gs-y="7"
			        data-gs-width="9" data-gs-height="6" data-gs-id="8" data-gs-max-height="6" data-gs-min-height="6" data-gs-no-resize="1"' . $grid_classes . '>
			            <div class="grid-stack-item-content clear_box">
			            	<div class="grid-stack ">
					            	<div id="title-3" class="grid-stack-item box-title"
								        data-gs-x="0" data-gs-y="0"
								        data-gs-width="12" data-gs-height="1" data-gs-no-resize="1" data-gs-no-move="1" data-gs-locked="yes">
								            <div class="grid-stack-item-content clear_box"><div id="' . $table[0] . '" class="inline" style="width:100%;"><h1>' . $reporting_features->get_feature_name_by_key( $table[0] ) . '</h1>';
								            if ( $filterable ) {
								            	$html .= '<span class="selected_date">' . __( 'Last 30 Days', 'ithemes-sales-accelerator' ) . '</span>';
								            }
								            
								            $html .= '<input type="search" class="it_table_search" id="it_table_' . $table[0] . '_search" placeholder="search..">';
								            
								            $html = apply_filters( 'it_rst_reporting_dashboard_table_menu', $html );
								            $html .= '</div></div></div>';
								    
									$html .= '<div id="table-' . $table[0] . '" class="grid-stack-item ' . $table[0] . '"
							        data-gs-x="0" data-gs-y="1"
							        data-gs-width="12" data-gs-height="5" data-gs-id="table-1" data-gs-no-move="1" data-gs-locked="yes" data-gs-no-resize="1" data-gs-name="' . $table[0] . '" data-gs-function="' . $table[0] . '" data-gs-timer="' . $it_rst_reporting_feature_settings[$table[0]]["refresh"]  . '" data-gs-type="table" '. $tb_class . '>';
							        $html .= '
							            <div class="grid-stack-item-content">';
							            
									        if ( $filterable ) {
									        	$html .= '<div class="spinner-wrapper">';
									        }
									        else {
									        	$html .= '<div class="spinner-wrapper2">';
									        }
									        
							            	$html .= '	<div class="spinner-loader">
						            				<div class="bounce1"></div>
													<div class="bounce2"></div>
													<div class="bounce3"></div>
												</div>
											</div>';
											if ( $filterable ) {
									        	$html .= '<div class="content yes"></div>';
									        }
									        else {
									        	$html .= '<div class="content no"></div>';
									        }
											
										$html .= '</div>
								</div>
							</div>
			            </div>
			    </div>
			    
			    <div id="9" class="grid-stack-item chart"
			        data-gs-x="0" data-gs-y="13"
			        data-gs-width="9" data-gs-height="5" data-gs-id="9" data-gs-max-height="5"' . $grid_classes . ' data-gs-min-height="5" data-gs-no-resize="1">
			            <div class="grid-stack-item-content clear_box">
			            	<div class="grid-stack nested-grid2">';
			            		
			            		$nchart 		= 1;
								$x 				= 0;
								$chart_class 	= '';
			            	
			            		foreach ( $chart as $item ) {
									$chart_class 	= apply_filters( 'it_rst_reporting_dashboard_charts_class', $ov_class, $item );
									$tc 			= $reporting_features->is_lines_chart( $item );
									
									if ( $tc == '' ) {
										$tc = 'donut';
									}
									
									$refresh = isset( $it_rst_reporting_feature_settings[$item]["refresh"] ) ? $it_rst_reporting_feature_settings[$item]["refresh"] : 10000;

								    $html .= '<div id="chart-' . $nchart . '" class="grid-stack-item ' . $item . '"
								        data-gs-x="' . $x .'" data-gs-y="0" data-gs-id="chart-' . $nchart . '"
								        data-gs-width="6" data-gs-height="5" data-gs-no-resize="1"' . $grid_classes . ' data-gs-name="' . $item . '" data-gs-function="' . $item . '" data-gs-timer="' . $refresh . ' " data-gs-type="chart" data-gs-typeChart="' . $tc . '" ' . $chart_class . '>
								        	<div class="spinner-loader"></div>
								        	<div class="grid-stack-item-content chart-' . $tc . '">
							            		<div class="spinner-wrapper">
								            		<div class="spinner-loader">
							            				<div class="bounce1"></div>
														<div class="bounce2"></div>
														<div class="bounce3"></div>
													</div>
												</div>
												<div class="content"></div>
								            </div>
								    </div>';
								    
								    $nchart++;
								    $x = 6;
								}    
								    
							$html .= '</div>
			            
			            </div>
			    </div>
			</div>';
		if( $progress < 100 ) {
	    	$html .= '</div>';
		}
		
		$html = apply_filters( 'it_rst_reporting_dashboard_body', $html );
			
	    echo $html;
	}
	
	// Reports Menu Icon
	public function rst_menuReport() {
		
		return '<svg width="20px" height="18px" viewBox="1 7 20 18" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
<desc>Created with Sketch.</desc>
<defs></defs>
<g id="reports_icon" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(1.000000, 7.000000)">
    <path d="M17.4071504,1.08973251 C17.2509602,0.448459745 16.6068978,0.0530946561 15.9643772,0.204069759 L9.2994589,1.76520341 C9.02502316,1.83000857 8.85508399,2.10501765 8.91988915,2.37945338 C8.9846943,2.65388912 9.25970338,2.82382829 9.53413912,2.75902314 L16.1990574,1.18972669 C16.2456143,1.17873461 16.2946327,1.18677216 16.3352413,1.21205676 C16.3758499,1.23734137 16.4046922,1.27778308 16.4153714,1.32441273 L18.8642085,11.7605402 C18.8752006,11.8070971 18.867163,11.8561155 18.8418784,11.8967241 C18.8165938,11.9373327 18.7761521,11.9661749 18.7295224,11.9768541 L6.04250555,14.9664761 C5.79196396,15.0270177 5.62593469,15.2649262 5.65553848,15.520973 C5.68514228,15.7770199 5.90107886,15.9707659 6.15882531,15.97254 C6.1986969,15.9723465 6.23841302,15.9675532 6.27718577,15.9582551 L7.70567408,15.62154 C7.72557517,15.6303361 7.74602493,15.6378343 7.76689501,15.6439877 L14.1114238,17.3459295 C14.2113863,17.3718623 14.3142571,17.3848926 14.4175284,17.3847027 C14.9628522,17.3829965 15.4386955,17.0143855 15.5766447,16.4867958 L16.3255807,13.5930866 L18.9662434,12.9808773 C19.6108286,12.8283883 20.0105607,12.1831586 19.8600689,11.5381041 L17.4071504,1.08973251 Z M14.588947,16.2296679 C14.5771073,16.2762037 14.5469226,16.3159591 14.5052784,16.3398656 C14.4655924,16.3641899 14.4177953,16.3715433 14.3726331,16.3602725 L9.79534841,15.1317726 L15.2072784,13.8563366 L14.588947,16.2296679 Z M12.6645692,7.34651131 C12.7922599,7.42906027 12.9410656,7.4729934 13.0931157,7.47303456 C13.1507492,7.47308956 13.2082207,7.46693189 13.2645343,7.45466828 L15.3052319,7.00775551 C15.7355572,6.91503893 16.0095018,6.49133794 15.9174412,6.06087183 C15.7538459,5.31213208 15.2993063,4.65914152 14.6539533,4.24574813 C14.0086002,3.83235473 13.2253786,3.69247329 12.476825,3.85691844 L12.476825,3.85691844 C12.0464997,3.94963502 11.7725551,4.37333601 11.8646158,4.80380212 L12.3115285,6.8444997 C12.356718,7.05258887 12.4840229,7.23361211 12.6645692,7.34651131 L12.6645692,7.34651131 Z M14.0930575,5.10174396 C14.4447697,5.32544445 14.7107839,5.66119752 14.8481156,6.05474974 L13.2645343,6.40779042 L12.915575,4.818087 C13.3308056,4.77650792 13.7479024,4.87646734 14.0991796,5.10174396 L14.0930575,5.10174396 Z M11.1707786,5.88537184 C11.0135339,5.79154785 10.8246435,5.76646086 10.64836,5.81598812 C9.11870447,6.25604523 8.1465825,7.75528441 8.36894989,9.33137104 C8.59131728,10.9074577 9.94028604,12.0792181 11.5319821,12.078889 C11.7082623,12.0808613 11.8843925,12.0678905 12.058482,12.0401158 C13.7770627,11.7403431 14.9410637,10.1221643 14.6787377,8.39747057 C14.6482439,8.21908396 14.5466045,8.06073276 14.3971215,7.95872058 C14.2438499,7.85384708 14.0540726,7.81692543 13.8726622,7.8566857 L11.9135925,8.2852322 L11.4830053,6.32616252 C11.444766,6.14220189 11.3316308,5.98248165 11.1707786,5.88537184 L11.1707786,5.88537184 Z M11.8217611,9.34435424 L13.6951215,8.93621473 C13.6740673,10.0049181 12.8790214,10.8996535 11.820207,11.0462215 C10.7613926,11.1927896 9.75319003,10.5476717 9.44261658,9.52487445 C9.13204314,8.50207718 9.61131344,7.40528666 10.5728542,6.93837179 L10.9809937,8.81173218 C11.0210302,8.99413362 11.1318931,9.15315652 11.2891889,9.25381054 C11.4464847,9.35446455 11.6373257,9.38850277 11.8197204,9.34843564 L11.8217611,9.34435424 Z M2.76514523,16.8235109 C2.99539503,16.8237336 3.21820553,16.7419882 3.39368009,16.5929121 C4.65670652,15.5233236 5.54949093,14.0820124 5.94455207,12.4747843 L8.09952872,3.42224985 C8.24629245,2.83086234 8.14905839,2.20529877 7.82971194,1.68636079 C7.51036549,1.16742282 6.9957404,0.798715263 6.40166833,0.663226715 C5.17570923,0.402992667 3.96561912,1.16687902 3.67325565,2.38557548 L3.51816264,3.01206964 L3.51816264,3.01206964 L3.42837194,3.39163939 L2.36108711,3.1263487 C2.106167,3.06352445 1.83673027,3.10456113 1.61207187,3.24042779 C1.38741347,3.37629445 1.22594384,3.59585702 1.16319762,3.85079634 L0.116319762,8.06483686 C0.0520631736,8.3361063 0.2174522,8.60872557 0.487726723,8.67704613 C0.527823721,8.68668058 0.568930602,8.69147639 0.610168578,8.69133102 C0.844240326,8.69068174 1.04784086,8.53083008 1.10401739,8.30359848 L2.14273246,4.12016843 L3.19369172,4.38137772 L1.53664528,11.3870925 C1.13512944,12.9909774 1.2501089,14.6806754 1.86519759,16.215383 C2.01074285,16.5841903 2.36661791,16.8268324 2.76310453,16.8275923 L2.76514523,16.8235109 Z M2.52842431,11.6319762 L4.51402306,3.26511614 L4.66911608,2.64066268 C4.82464186,1.9628068 5.49101517,1.53151769 6.1731102,1.66724993 C6.50058569,1.74216642 6.78407237,1.94585029 6.95955649,2.23230796 C7.13504062,2.51876564 7.18772354,2.86384002 7.10570899,3.18961033 L4.95277304,12.2441855 C4.61252514,13.6144433 3.85612585,14.8456418 2.7875929,15.7684702 C2.27551444,14.4496179 2.18496748,13.0044314 2.52842431,11.6319762 L2.52842431,11.6319762 Z" id="Shape" fill="#C2CCD4"></path>
</g>
</svg>';
	}
	
	// Orders Menu Icon
	public function rst_menuOrders() {
		
		return '<svg width="19px" height="19px" viewBox="1 1 19 19" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
<desc>Created with Sketch.</desc>
<defs></defs>
<g id="rst_menu_orders" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(2.000000, 2.000000)">
    <path d="M1.89291501,2.14292265 L4.8715775,11.5735681 C5.01251717,12.0190061 5.42583396,12.3217678 5.8930373,12.3218053 L13.9289973,12.3218053 C14.3987985,12.3215716 14.8136515,12.0153189 14.9522428,11.566425 L17.0951655,4.6019264 C17.1954981,4.27695179 17.1355836,3.92364289 16.9337222,3.64991468 C16.7318609,3.37618647 16.4120303,3.21455309 16.0719199,3.21438398 L4.47870835,3.21438398 L3.70011312,0.74823716 C3.55917344,0.302799207 3.14585666,3.74350665e-05 2.67865332,-1.77635684e-15 L1.07146133,-1.77635684e-15 C0.479709576,-1.7367048e-15 -1.07057099e-15,0.479709576 -1.11022302e-15,1.07146133 C-1.18952709e-15,1.66321308 0.479709576,2.14292265 1.07146133,2.14292265 L1.89291501,2.14292265 Z M14.6218756,5.35730664 L13.1379016,10.1788826 L6.67877561,10.1788826 L5.15551475,5.35730664 L14.6218756,5.35730664 Z M6.96449863,17.1433812 C6.076871,17.1433812 5.35730664,16.4238169 5.35730664,15.5361892 C5.35730664,14.6485616 6.076871,13.9289973 6.96449863,13.9289973 C7.85212625,13.9289973 8.57169062,14.6485616 8.57169062,15.5361892 C8.57169062,16.4238169 7.85212625,17.1433812 6.96449863,17.1433812 L6.96449863,17.1433812 Z M11.2503439,15.5361892 C11.2503439,14.6485616 11.9699083,13.9289973 12.8575359,13.9289973 C13.7451636,13.9289973 14.4647279,14.6485616 14.4647279,15.5361892 C14.4647279,16.4238169 13.7451636,17.1433812 12.8575359,17.1433812 C11.9699083,17.1433812 11.2503439,16.4238169 11.2503439,15.5361892 L11.2503439,15.5361892 Z" id="Shape" fill="#C2CCD4"></path>
</g>
</svg>';
	}
	
	// Products Menu Icon
	public function rst_menuProducts() {
		
		return '
<svg width="17px" height="18px" viewBox="21 1 17 18" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
<desc>Created with Sketch.</desc>
<defs></defs>
<g id="rst_menu_products" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(21.000000, 1.000000)">
    <path d="M15.8732308,13.0846154 L15.8732308,4.82676923 C15.8728848,4.58133946 15.7421096,4.35459221 15.5298462,4.23138462 L8.37415385,0.0996923077 C8.1608099,-0.0234818749 7.89795933,-0.0234818749 7.68461538,0.0996923077 L0.534461538,4.22861538 C0.322198073,4.35182298 0.19142291,4.57857023 0.191076923,4.824 L0.191076923,13.0846154 C0.19142291,13.3300452 0.322198073,13.5567924 0.534461538,13.68 L7.68738462,17.8116923 C7.90072856,17.9348665 8.16357913,17.9348665 8.37692308,17.8116923 L15.5270769,13.68 C15.7404062,13.5574948 15.8723127,13.3306155 15.8732308,13.0846154 L15.8732308,13.0846154 Z M8.03076923,1.49261538 L13.8046154,4.81569231 L11.7,6.03138462 L5.92615385,2.70830769 L8.03076923,1.49261538 Z M8.03076923,8.16092308 L2.25692308,4.83784615 L4.54984615,3.51415385 L10.3236923,6.83723077 L8.03076923,8.16092308 Z M1.56738462,6.01753846 L7.34123077,9.34061538 L7.34123077,16.02 L1.56738462,12.6969231 L1.56738462,6.01753846 Z M8.72030769,16.02 L8.72030769,9.35169231 L14.4941538,6.02861538 L14.4941538,12.6858462 L8.72030769,16.02 Z" id="Shape" fill="#C2CCD4"></path>
</g>
</svg>';
	}
	
	// Customers Menu Icon
	public function rst_menuCustomers() {
		
		return '
<svg width="17px" height="18px" viewBox="5 1 17 18" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
<desc>Created with Sketch.</desc>
<defs></defs>
<g id="rst_menu_customers" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(5.000000, 1.000000)">
    <path d="M8.18181818,4.12181818 C9.31852061,4.12181818 10.24,3.20033879 10.24,2.06363636 C10.24,0.926933933 9.31852061,0.00545454545 8.18181818,0.00545454545 C7.04511575,0.00545454545 6.12363636,0.926933933 6.12363636,2.06363636 C6.12563791,3.1995087 7.04594585,4.11981664 8.18181818,4.12181818 L8.18181818,4.12181818 Z M8.18181818,0.909090909 C8.81945603,0.909090909 9.33636364,1.42599852 9.33636364,2.06363636 C9.33636364,2.70127421 8.81945603,3.21818182 8.18181818,3.21818182 C7.54418033,3.21818182 7.02727273,2.70127421 7.02727273,2.06363636 C7.02827386,1.42641366 7.54459548,0.910092046 8.18181818,0.909090909 L8.18181818,0.909090909 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M4.69454545,6.59090909 L4.69454545,6.60727273 L5.11090909,9.33454545 C5.17170783,9.86043548 5.4796923,10.3257892 5.94,10.5872727 L6.60727273,15.1327273 C6.66886844,15.6392289 7.09885774,16.0200542 7.60909091,16.02 L8.75090909,16.02 C9.26114226,16.0200542 9.69113156,15.6392289 9.75272727,15.1327273 L10.42,10.5872727 C10.8803077,10.3257892 11.1882922,9.86043548 11.2490909,9.33454545 L11.6654545,6.60727273 L11.6654545,6.59090909 C11.7254042,6.05731729 11.5554517,5.52342528 11.1980766,5.12267944 C10.8407015,4.7219336 10.3296747,4.49220309 9.79272727,4.49090909 L9.27272727,4.49090909 C9.1140668,4.49056097 8.96399091,4.56291898 8.86545455,4.68727273 L8.18181818,5.53090909 L7.50727273,4.68909091 C7.40714452,4.56151488 7.25306279,4.48817467 7.09090909,4.49090909 L6.56363636,4.49090909 C6.02732364,4.49323355 5.51727732,4.72340967 5.16070892,5.12402884 C4.80414051,5.52464801 4.63466278,6.05794492 4.69454545,6.59090909 L4.69454545,6.59090909 Z M5.83636364,5.72363636 C6.02053806,5.51493809 6.28529334,5.39513633 6.56363636,5.39454545 L6.90909091,5.39454545 L7.67272727,6.34909091 C7.79129839,6.49919155 7.97235521,6.58636706 8.16363636,6.58545455 L8.18909091,6.58545455 C8.37942573,6.58624773 8.55964058,6.499825 8.67818182,6.35090909 L9.45454545,5.39454545 L9.79818182,5.39454545 C10.0778054,5.39442822 10.3442634,5.51332624 10.530938,5.72151351 C10.7176125,5.92970079 10.8068587,6.20749879 10.7763636,6.48545455 L10.3636364,9.21090909 L10.3636364,9.22909091 C10.3316583,9.51866668 10.1377008,9.76481637 9.86363636,9.86363636 C9.70698091,9.92186188 9.59514487,10.0616569 9.57272727,10.2272727 L8.85818182,15.0036364 L8.85818182,15.0181818 C8.85257213,15.0721254 8.80696097,15.1130182 8.75272727,15.1127273 L7.61090909,15.1127273 C7.55667539,15.1130182 7.51106423,15.0721254 7.50545455,15.0181818 L7.50545455,15.0036364 L6.80181818,10.22 C6.77940058,10.0543842 6.66756455,9.91458916 6.51090909,9.85636364 C6.23684462,9.75754364 6.04288713,9.51139395 6.01090909,9.22181818 L6.01090909,9.20363636 L5.59454545,6.47636364 C5.56471736,6.20251103 5.65262335,5.92887884 5.83636364,5.72363636 L5.83636364,5.72363636 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M14.16,15.5945455 C14.16,14.7272727 13.1109091,14.0381818 11.2072727,13.6545455 C10.9622591,13.6043378 10.722935,13.7622591 10.6727273,14.0072727 C10.6225196,14.2522863 10.780441,14.4916105 11.0254545,14.5418182 C12.7072727,14.88 13.2509091,15.3963636 13.2509091,15.5945455 C13.2509091,15.7563636 12.9036364,16.1072727 11.9472727,16.4127273 C10.7234588,16.762928 9.45457732,16.9301925 8.18181818,16.9090909 C6.90784326,16.9305518 5.63771001,16.7632839 4.41272727,16.4127273 C3.45636364,16.1072727 3.10909091,15.7563636 3.10909091,15.5945455 C3.10909091,15.3963636 3.65454545,14.88 5.33454545,14.5418182 C5.57955905,14.4916105 5.73748043,14.2522863 5.68727273,14.0072727 C5.63706502,13.7622591 5.39774087,13.6043378 5.15272727,13.6545455 C3.25272727,14.0381818 2.20363636,14.7272727 2.20363636,15.5945455 C2.20363636,16.0781818 2.53818182,16.7636364 4.13636364,17.2745455 C5.45052866,17.6548925 6.81386467,17.8381003 8.18181818,17.8181818 C9.54986237,17.8375097 10.9132002,17.6536889 12.2272727,17.2727273 C13.8181818,16.7636364 14.16,16.08 14.16,15.5945455 L14.16,15.5945455 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M13.2436364,3.67272727 C13.9858058,3.67346282 14.6553054,3.22695683 14.9398311,2.54149272 C15.2243569,1.85602861 15.0678501,1.06665987 14.5433171,0.54160649 C14.018784,0.0165531058 13.2295709,-0.140736348 12.543825,0.143109569 C11.8580791,0.426955485 11.4109095,1.09601207 11.4109091,1.83818182 C11.4119092,2.85024923 12.2315704,3.67072367 13.2436364,3.67272727 L13.2436364,3.67272727 Z M13.2436364,0.909090909 C13.7567591,0.909090909 14.1727273,1.32505908 14.1727273,1.83818182 C14.1727273,2.35130456 13.7567591,2.76727273 13.2436364,2.76727273 C12.7305136,2.76727273 12.3145455,2.35130456 12.3145455,1.83818182 C12.3155459,1.32547403 12.7309286,0.910091317 13.2436364,0.909090909 L13.2436364,0.909090909 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M14.1818182,4.12727273 C14.025957,4.12665254 13.8784628,4.19771795 13.7818182,4.32 L13.2490909,4.98545455 L12.7163636,4.32 C12.6153549,4.19203396 12.4537499,4.12767598 12.2924237,4.15116908 C12.1310975,4.17466219 11.9945594,4.28243721 11.9342419,4.43389636 C11.8739244,4.5853555 11.8989913,4.75748851 12,4.88545455 L12.7563636,5.82909091 C12.8751853,5.97264625 13.0518307,6.05574146 13.2381818,6.05574146 C13.4245329,6.05574146 13.6011783,5.97264625 13.72,5.82909091 L14.3636364,5.03272727 L14.6290909,5.03272727 C14.8509252,5.03259766 15.0624207,5.12649519 15.211107,5.29112457 C15.3597934,5.45575394 15.4317376,5.67568836 15.4090909,5.89636364 L15.0454545,8.24181818 L15.0454545,8.26 C15.0201186,8.48491481 14.8692758,8.67593536 14.6563636,8.75272727 C14.4989999,8.81041008 14.3863795,8.95031124 14.3636364,9.11636364 L13.7636364,13.4054545 C13.7290955,13.652952 13.9016206,13.8816291 14.1490909,13.9163636 L14.2127273,13.9163636 C14.4378117,13.9158864 14.6283621,13.7501231 14.66,13.5272727 L15.2309091,9.47454545 C15.6297757,9.23611826 15.8946611,8.82534416 15.9472727,8.36363636 L16.3109091,6.01636364 L16.3109091,6 C16.3637008,5.52261543 16.2105414,5.04535702 15.8898169,4.68783946 C15.5690924,4.3303219 15.1112031,4.12643284 14.6309091,4.12727273 L14.1818182,4.12727273 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M3.12,3.67272727 C4.13319148,3.67272727 4.95454545,2.8513733 4.95454545,1.83818182 C4.95454545,0.824990341 4.13319148,0.00363636364 3.12,0.00363636364 C2.10680852,0.00363636364 1.28545455,0.824990341 1.28545455,1.83818182 C1.2864568,2.85095786 2.10722396,3.67172502 3.12,3.67272727 L3.12,3.67272727 Z M3.12,0.909090909 C3.63190942,0.914100048 4.04331161,1.33226009 4.03998033,1.84418318 C4.03664904,2.35610626 3.61983965,2.7688768 3.10790839,2.7672234 C2.59597714,2.76557001 2.18184269,2.35011575 2.18181818,1.83818182 C2.18229468,1.5903394 2.28158341,1.35291864 2.45768592,1.17852255 C2.63378843,1.00412646 2.87216469,0.907154179 3.12,0.909090909 L3.12,0.909090909 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M0.0581818182,6.02363636 L0.416363636,8.36363636 C0.470053808,8.8240144 0.734804907,9.23323628 1.13272727,9.47090909 L1.70363636,13.5236364 C1.73527431,13.7464867 1.92582464,13.91225 2.15090909,13.9127273 L2.21454545,13.9127273 C2.46201578,13.8779928 2.63454091,13.6493156 2.6,13.4018182 L2,9.11454545 C1.97725688,8.94849306 1.86463645,8.8085919 1.70727273,8.75090909 C1.49436057,8.67411718 1.34351778,8.48309663 1.31818182,8.25818182 L1.31818182,8.24 L0.954545455,5.89454545 C0.931898761,5.67387018 1.003843,5.45393576 1.15252932,5.28930639 C1.30121564,5.12467701 1.5127112,5.03077947 1.73454545,5.03090909 L2,5.03090909 L2.63818182,5.82727273 C2.75197668,5.97125704 2.9255684,6.05503683 3.10909091,6.05454545 L3.12,6.05454545 C3.30708416,6.05915473 3.48548242,5.97567781 3.60181818,5.82909091 L4.36363636,4.88545455 C4.50354492,4.68992138 4.46620651,4.41913014 4.27860213,4.26875653 C4.09099776,4.11838292 3.81857207,4.14088386 3.65818182,4.32 L3.12545455,4.98545455 L2.59454545,4.32363636 C2.49573481,4.19663229 2.34269597,4.12382086 2.18181818,4.12727273 L1.72727273,4.12727273 C1.24856351,4.1290146 0.793127506,4.334021 0.474421576,4.69122133 C0.155715646,5.04842167 0.00374337306,5.52418842 0.0563636364,6 L0.0581818182,6.02363636 Z" id="Shape" fill="#C2CCD4"></path>
</g>
</svg>';
	}
	
	// Digest Menu Icon
	public function rst_menuDigest() {
		
		return '
<svg width="18px" height="15px" viewBox="0 0 18 15" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
<desc>Created with Sketch.</desc>
<defs></defs>
<g id="rst_menu_digest" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(1.000000, 1.000000)">
    <g id="Design" stroke-width="2" stroke="#B5C1CC">
        <g id="Dashboard-Copy-4">
            <g id="Group-5">
                <path d="M0,0 L16,0" id="Line"></path>
                <path d="M0,6 L16,6" id="Line-Copy-14"></path>
                <path d="M0,12 L16,12" id="Line-Copy-16"></path>
            </g>
        </g>
    </g>
</g>
</svg>';
	}
	
	// Integrations Menu Icon
	public function rst_menuIntegrations() {
		
		return '
<svg width="15px" height="19px" viewBox="11 1 15 19" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
<desc>Created with Sketch.</desc>
<defs></defs>
<g id="noun_1122992_cc" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(11.000000, 1.000000)">
    <path d="M5.54877551,14.0528571 C5.09864714,14.0525912 4.69929172,14.3415497 4.55877551,14.7691837 L3.81306122,17.0816327 L0.506938776,17.0816327 C0.253338635,17.0816327 0.047755102,17.2872162 0.047755102,17.5408163 C0.047755102,17.7944165 0.253338635,18 0.506938776,18 L13.8269388,18 C14.0805389,18 14.2861224,17.7944165 14.2861224,17.5408163 C14.2861224,17.2872162 14.0805389,17.0816327 13.8269388,17.0816327 L10.5208163,17.0816327 L9.76959184,14.7691837 C9.62907563,14.3415497 9.22972021,14.0525912 8.77959184,14.0528571 L7.62061224,14.0528571 L7.62061224,11.665102 L12.8571429,11.665102 C13.0791505,11.6596158 13.291217,11.5719093 13.4522449,11.4189796 L13.6010204,11.2702041 C13.8688814,11.0015816 13.8971233,10.5763834 13.6671429,10.2746939 L12.8057143,9.15061224 L13.5844898,8.12571429 C13.7535314,7.9125155 13.820659,7.63594959 13.7681633,7.36897959 C13.6814322,6.94401831 13.3092185,6.63766966 12.8755102,6.63428571 L7.62061224,6.63428571 L7.62061224,5.02897959 L10.8073469,5.02897959 C11.2067527,5.02903646 11.5930348,4.88637916 11.8965306,4.62673469 L13.4706122,3.27857143 C13.6852449,3.09450078 13.8098095,2.82661994 13.8122449,2.54387755 L13.8122449,2.54387755 C13.8136764,2.25503923 13.6888935,1.97998009 13.4706122,1.79081633 L11.9020408,0.415102041 C11.596332,0.149762052 11.2047982,0.00424196575 10.8,0.00551020408 L1.46938776,0.00551020408 C1.24738008,0.0109964174 1.03531366,0.0987029015 0.874285714,0.251632653 L0.734693878,0.400408163 C0.466832909,0.669030689 0.438590963,1.09422888 0.668571429,1.39591837 L1.53,2.52 L0.75122449,3.54489796 C0.58218289,3.75809675 0.515055242,4.03466266 0.56755102,4.30163265 C0.654282127,4.72659393 1.02649574,5.03294259 1.46020408,5.03632653 L6.70591837,5.03632653 L6.70591837,6.64897959 L3.51918367,6.64897959 C3.11977792,6.64892272 2.73349582,6.79158002 2.43,7.05122449 L0.855918367,8.39938776 C0.641285697,8.58345841 0.516721106,8.85133925 0.514285714,9.13408163 L0.514285714,9.13408163 C0.512854208,9.42291996 0.637637132,9.69797909 0.855918367,9.88714286 L2.42632653,11.2518367 C2.73203531,11.5171767 3.12356918,11.6626968 3.52836735,11.6614286 L6.70591837,11.6614286 L6.70591837,14.0491837 L5.54877551,14.0528571 Z M1.47857143,4.09591837 L2.33081633,2.9755102 L4.46693878,2.9755102 C4.72053892,2.9755102 4.92612245,2.76992667 4.92612245,2.51632653 C4.92612245,2.26272639 4.72053892,2.05714286 4.46693878,2.05714286 L2.32897959,2.05714286 L1.46938776,0.947755102 L1.50795918,0.918367347 L10.8036735,0.918367347 C10.9862626,0.917845475 11.1629433,0.983041308 11.3014286,1.10204082 L12.8718367,2.46673469 C12.8885346,2.48166428 12.897919,2.50311431 12.897551,2.5255102 L12.897551,2.5255102 C12.8971771,2.54472534 12.8884615,2.56282702 12.8736735,2.57510204 L11.2995918,3.92326531 C11.1615017,4.04170947 10.9856017,4.1068576 10.8036735,4.10693878 L1.46938776,4.11244898 L1.47857143,4.09591837 Z M3.25653061,10.7081633 C3.17177135,10.6764524 3.09338733,10.6297953 3.02510204,10.5704082 L1.45469388,9.20571429 C1.43799601,9.1907847 1.42861162,9.16933467 1.42897959,9.14693878 L1.42897959,9.14693878 C1.42935352,9.12772364 1.43806914,9.10962196 1.45285714,9.09734694 L3.02693878,7.74918367 C3.16502891,7.63073951 3.34092886,7.56559138 3.52285714,7.5655102 L12.8608163,7.5655102 L12.8516327,7.58204082 L11.9993878,8.70244898 L9.85959184,8.70244898 C9.6059917,8.70244898 9.40040816,8.90803251 9.40040816,9.16163265 C9.40040816,9.41523279 9.6059917,9.62081633 9.85959184,9.62081633 L11.997551,9.62081633 L12.8571429,10.7210204 L12.8277551,10.7504082 L3.52285714,10.7504082 C3.43193359,10.750619 3.34171625,10.7344421 3.25653061,10.7026531 L3.25653061,10.7081633 Z M8.90265306,15.0612245 L9.55102041,17.0816327 L4.7755102,17.0816327 L5.42204082,15.0612245 C5.4394264,15.0078731 5.48899086,14.9716245 5.54510204,14.9712245 L8.7777551,14.9712245 C8.83454308,14.9708295 8.88505773,15.0072297 8.90265306,15.0612245 L8.90265306,15.0612245 Z" id="Shape" fill="#C2CCD4"></path>
</g>
</svg>';
	}
	
	// Manager Dashboard Icon
	public function rst_managerDashboard() {
		
		return '
<svg width="19px" height="19px" viewBox="0 1 19 19" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
<!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
<desc>Created with Sketch.</desc>
<defs></defs>
<g id="rst_manage_dashboard" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(1.000000, 1.000000)">
    <path d="M6.86204082,9.21122449 C7.11564096,9.21122449 7.32122449,9.00564096 7.32122449,8.75204082 C7.32122449,8.49844068 7.11564096,8.29285714 6.86204082,8.29285714 C5.29324163,8.29285845 4.02129007,9.56426377 4.02061488,11.1330628 C4.0199397,12.7018618 5.2907964,13.9743615 6.859595,13.9757132 C8.42839361,13.9770649 9.70144121,12.7067571 9.70346939,11.1379592 C9.70346939,10.884359 9.49788585,10.6787755 9.24428571,10.6787755 C8.99068557,10.6787755 8.78510204,10.884359 8.78510204,11.1379592 C8.78510062,11.9175846 8.31526832,12.6203733 7.59484582,12.9183787 C6.87442332,13.2163841 6.04541294,13.0508664 5.49466079,12.4990622 C4.94390865,11.947258 4.77997338,11.1179333 5.07935309,10.3980808 C5.3787328,9.67822832 6.08241681,9.20973807 6.86204082,9.21122449 L6.86204082,9.21122449 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M0.991836735,12.7212245 L1.67326531,12.8406122 C1.75907095,13.1023444 1.86468238,13.3571627 1.98918367,13.6028571 L1.59244898,14.1685714 C1.25885972,14.6432455 1.31546407,15.2891553 1.72653061,15.6985714 L2.30142857,16.2734694 C2.71084466,16.6845359 3.35675453,16.7411403 3.83142857,16.407551 L4.39714286,16.0108163 C4.64286398,16.1352596 4.89767778,16.2408692 5.15938776,16.3267347 L5.27877551,17.0081633 C5.37651171,17.5818372 5.87418349,18.0010927 6.45612245,18 L7.26979592,18 C7.85242847,18.0019913 8.35112888,17.5825237 8.44897959,17.0081633 L8.56836735,16.3267347 C8.83008964,16.2409024 9.08490592,16.1352918 9.33061224,16.0108163 L9.89632653,16.407551 C10.3710006,16.7411403 11.0169104,16.6845359 11.4263265,16.2734694 L12.0012245,15.6985714 C12.412291,15.2891553 12.4688954,14.6432455 12.1353061,14.1685714 L11.7385714,13.6028571 C11.863034,13.3571449 11.9686442,13.1023296 12.0544898,12.8406122 L12.7359184,12.7212245 C12.9028673,12.6996584 13.0444735,12.5881242 13.104553,12.4308744 C13.1646325,12.2736246 13.133472,12.0960825 13.0234349,11.9686899 C12.9133977,11.8412973 12.7422741,11.7846504 12.5779592,11.8212245 L11.6191837,11.9902041 C11.4426062,12.0190651 11.299074,12.1482441 11.2518367,12.3208163 C11.149839,12.7013189 10.9985584,13.0668623 10.8018367,13.4081633 C10.7133963,13.5620595 10.7219882,13.7532294 10.8238776,13.8985714 L11.3877551,14.6938776 C11.470345,14.8069981 11.4570282,14.9636664 11.3565306,15.0612245 L10.7816327,15.6361224 C10.6840746,15.73662 10.5274062,15.7499368 10.4142857,15.6673469 L9.61714286,15.1163265 C9.47180079,15.0144371 9.28063091,15.0058452 9.12673469,15.0942857 C8.78661487,15.2907701 8.42231675,15.4420465 8.04306122,15.5442857 C7.87048895,15.591523 7.74130999,15.7350552 7.71244898,15.9116327 L7.54346939,16.8704082 C7.5113011,16.9959064 7.39750576,17.0831495 7.26795918,17.0816327 L6.45612245,17.0816327 C6.31977113,17.0819901 6.20283712,16.984416 6.17877551,16.8502041 L6.00979592,15.8914286 C5.98093491,15.7148511 5.85175595,15.5713189 5.67918367,15.5240816 C5.2988741,15.4232051 4.93334315,15.2731645 4.59183673,15.0777551 C4.43794052,14.9893146 4.24677064,14.9979065 4.10142857,15.0997959 L3.30428571,15.6508163 C3.19116521,15.7334062 3.03449685,15.7200894 2.93693878,15.6195918 L2.37122449,15.0612245 C2.27072691,14.9636664 2.2574101,14.8069981 2.34,14.6938776 L2.89102041,13.8967347 C2.99505557,13.7536269 3.00721609,13.5633504 2.9222449,13.4081633 C2.72570401,13.0680713 2.57442453,12.7037656 2.4722449,12.3244898 C2.42500759,12.1519175 2.28147541,12.0227386 2.10489796,11.9938776 L1.14612245,11.824898 C1.01199407,11.7990288 0.915895444,11.6804555 0.918367347,11.5438776 L0.918367347,10.7320408 C0.918009891,10.5956895 1.01558396,10.4787555 1.14979592,10.4546939 L2.10857143,10.2857143 C2.28514888,10.2568533 2.42868105,10.1276743 2.47591837,9.95510204 C2.5779672,9.57519928 2.72924936,9.21026965 2.92591837,8.86959184 C3.01435884,8.71569562 3.00576693,8.52452574 2.90387755,8.37918367 L2.35285714,7.58204082 C2.27026725,7.46892031 2.28358406,7.31225195 2.38408163,7.21469388 L2.93877551,6.64714286 C3.03536732,6.54548736 3.19211674,6.53059617 3.30612245,6.6122449 L4.10326531,7.16326531 C4.24860737,7.26515469 4.43977725,7.27374659 4.59367347,7.18530612 C4.93437921,6.98869373 5.29930123,6.83741472 5.67918367,6.73530612 C5.85175595,6.68806881 5.98093491,6.54453663 6.00979592,6.36795918 L6.17877551,5.40918367 C6.21010396,5.16666185 6.04511336,4.9424024 5.80425793,4.90012982 C5.5634025,4.85785723 5.33190559,5.01252907 5.27877551,5.25122449 L5.15938776,5.93265306 C4.89766793,6.01849201 4.64285214,6.12410241 4.39714286,6.24857143 L3.83142857,5.85183673 C3.35675453,5.51824748 2.71084466,5.57485182 2.30142857,5.98591837 L1.72653061,6.56081633 C1.31604362,6.97070217 1.26021786,7.6165752 1.59428571,8.09081633 L1.99102041,8.65653061 C1.86653204,8.902231 1.76092102,9.15704829 1.67510204,9.41877551 L0.991836735,9.55102041 C0.416788494,9.64898443 -0.00289188358,10.148715 -1.63136852e-16,10.7320408 L-1.63136852e-16,11.5457143 C-0.000195940605,12.1269594 0.418847975,12.623604 0.991836735,12.7212245 L0.991836735,12.7212245 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M17.3277551,5.16489796 L16.8134694,4.92612245 C16.7654232,4.53298724 16.6628028,4.14846989 16.5085714,3.78367347 L16.8336735,3.31897959 C17.1556815,2.86032798 17.1014438,2.23659458 16.705102,1.84040816 L16.1540816,1.28938776 C15.7578952,0.893046001 15.1341618,0.838808314 14.6755102,1.16081633 L14.2163265,1.49326531 C13.8515301,1.33903396 13.4670128,1.23641357 13.0738776,1.18836735 L12.835102,0.674081633 C12.5988665,0.165084463 12.0306036,-0.100053197 11.4887755,0.0459183673 L10.7540816,0.244285714 C10.206298,0.386875397 9.8429204,0.905875101 9.89632653,1.46938776 L9.94591837,2.02040816 C9.63060441,2.25963964 9.34943555,2.54080849 9.11020408,2.85612245 L8.54632653,2.80653061 C7.98828368,2.75710744 7.47541729,3.11580402 7.33040816,3.65693878 L7.13204082,4.39163265 C6.98606925,4.93346073 7.25120691,5.50172361 7.76020408,5.73795918 L8.2744898,5.97673469 C8.32255955,6.36986535 8.42517908,6.75437948 8.57938776,7.11918367 L8.25428571,7.58387755 C7.9322777,8.04252916 7.98651539,8.66626256 8.38285714,9.06244898 L8.93387755,9.61346939 C9.33006397,10.0098111 9.95379737,10.0640488 10.412449,9.74204082 L10.8771429,9.41693878 C11.2378079,9.57458215 11.6185678,9.68151634 12.0085714,9.73469388 L12.2473469,10.2489796 C12.4853402,10.7549851 13.051993,11.0174103 13.5918367,10.8716327 L14.3265306,10.6732653 C14.8684003,10.528994 15.2280115,10.015928 15.1787755,9.45734694 L15.1291837,8.89346939 C15.2866238,8.77395308 15.4357441,8.64385633 15.5755102,8.50408163 L15.5755102,8.50408163 C15.7204912,8.3636478 15.8555119,8.21328375 15.9795918,8.05408163 L16.5434694,8.10367347 C17.1015122,8.15309664 17.6143786,7.79440006 17.7593878,7.25326531 L17.9577551,6.51857143 C18.1075105,5.97395909 17.8408617,5.40101415 17.3277551,5.16489796 L17.3277551,5.16489796 Z M17.072449,6.26693878 L16.8740816,7.00163265 C16.846406,7.11695877 16.7388225,7.19491782 16.6206122,7.18530612 L15.7959184,7.12102041 C15.6289514,7.10679136 15.4675722,7.18501445 15.3753061,7.32489796 C15.1217536,7.70770545 14.7938279,8.03563112 14.4110204,8.28918367 C14.2711369,8.38144979 14.1929138,8.54282896 14.2071429,8.70979592 L14.2806122,9.5344898 C14.2902239,9.65270007 14.2122649,9.76028356 14.0969388,9.78795918 L13.3622449,9.98632653 C13.2493239,10.0169658 13.1307312,9.96193257 13.0812245,9.85591837 L12.7304082,9.10285714 C12.6592243,8.95081023 12.510457,8.8499869 12.3428571,8.84020408 C11.8826757,8.81501255 11.4325665,8.69560858 11.0204082,8.48938776 C10.8702657,8.41458335 10.6912202,8.42797652 10.5538776,8.52428571 L9.87979592,9 C9.78392075,9.06706151 9.65370257,9.05543488 9.57122449,8.97244898 L9.02020408,8.42142857 C8.94347892,8.33826171 8.93506865,8.2128723 9,8.12020408 L9.47571429,7.44061224 C9.5698847,7.30456539 9.58323022,7.1282641 9.51061224,6.97959184 C9.30472136,6.56981741 9.18471669,6.12229999 9.15795918,5.6644898 C9.14817636,5.49688996 9.04735303,5.34812267 8.89530612,5.27693878 L8.1422449,4.92612245 C8.03623069,4.87661577 7.98119748,4.75802307 8.01183673,4.64510204 L8.21020408,3.91040816 C8.2378797,3.79508205 8.3454632,3.71712299 8.46367347,3.72673469 L9.28836735,3.80020408 C9.4553343,3.81443313 9.61671348,3.73621003 9.70897959,3.59632653 C9.96253214,3.21351904 10.2904578,2.88559336 10.6732653,2.63204082 C10.81632,2.53894851 10.8956352,2.37394456 10.8789796,2.20408163 L10.8055102,1.37938776 C10.7958985,1.26117749 10.8738576,1.15359399 10.9891837,1.12591837 L11.7238776,0.92755102 C11.8367986,0.896911765 11.9553913,0.95194498 12.004898,1.05795918 L12.3557143,1.81102041 C12.4268982,1.96306732 12.5756655,2.06389065 12.7432653,2.07367347 C13.2010755,2.10043097 13.6485929,2.22043564 14.0583673,2.42632653 C14.2096617,2.50079948 14.3896622,2.48597592 14.5267347,2.3877551 L15.2063265,1.91204082 C15.3022017,1.84497931 15.4324199,1.85660593 15.514898,1.93959184 L16.0659184,2.49061224 C16.1489043,2.57309032 16.1605309,2.7033085 16.0934694,2.79918367 L15.6122449,3.47326531 C15.5159357,3.61060793 15.5025425,3.78965346 15.5773469,3.93979592 C15.7832378,4.34957034 15.9032425,4.79708777 15.93,5.25489796 C15.9397828,5.4224978 16.0406062,5.57126509 16.1926531,5.64244898 L16.9457143,5.99326531 C17.0538826,6.04085182 17.1114832,6.15994498 17.0816327,6.27428571 L17.072449,6.26693878 Z" id="Shape" fill="#C2CCD4"></path>
    <path d="M12.5430612,3.48979592 C11.4566382,3.48979592 10.5759184,4.37051577 10.5759184,5.45693878 C10.5759184,6.54336178 11.4566382,7.42408163 12.5430612,7.42408163 C13.6294842,7.42408163 14.5102041,6.54336178 14.5102041,5.45693878 C14.5081825,4.37135424 13.6286458,3.4918175 12.5430612,3.48979592 L12.5430612,3.48979592 Z M12.5430612,6.51122449 C12.1161481,6.51345577 11.7300294,6.25800683 11.5651084,5.86422915 C11.4001874,5.47045148 11.4890224,5.01608359 11.7901078,4.71341641 C12.0911931,4.41074922 12.5450892,4.31953446 12.9397257,4.48238973 C13.3343621,4.64524499 13.5918309,5.03001983 13.5918367,5.45693878 C13.5908426,6.03664556 13.1227548,6.50719259 12.5430612,6.51122449 L12.5430612,6.51122449 Z" id="Shape" fill="#C2CCD4"></path>
</g>
</svg>';
	}

	public function rst_arrowUp() {
		
		return '
			<svg width="14px" height="9px" viewBox="63 83 14 9" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			    <!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
			    <desc>Created with Sketch.</desc>
			    <defs></defs>
			    <g id="up-arrow" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(63.000000, 83.000000)">
			        <path d="M13.8507326,6.69183817 L7.31754234,0.159089817 C7.22419983,0.0656491003 7.11681421,0.0188550894 6.99533636,0.0188550894 C6.87385852,0.0188550894 6.76627649,0.0656491003 6.67288487,0.159089817 L0.140234727,6.69183817 C0.0466467055,6.78542619 0,6.89281182 0,7.01433876 C0,7.1358166 0.0467940109,7.24334953 0.140234727,7.33674115 L0.841015549,8.03747287 C0.934456266,8.13091358 1.0419892,8.17760939 1.16346704,8.17760939 C1.28494488,8.17760939 1.39247781,8.13091358 1.48591853,8.03747287 L6.99533636,2.52815323 L12.5049506,8.03781658 C12.5983422,8.1312573 12.7059243,8.17765849 12.8272057,8.17765849 C12.9488799,8.17765849 13.0564129,8.13096269 13.1498045,8.03781658 L13.8506835,7.33679025 C13.9440751,7.24339863 13.9904763,7.1358166 13.9904763,7.01438786 C13.9905254,6.89281182 13.9441242,6.78527888 13.8507326,6.69183817 L13.8507326,6.69183817 Z" id="Shape" fill="#03AE98"></path>
			    </g>
			</svg>';
	}
	
	public function rst_arrowDown() {
		
		return '
			<svg width="14px" height="9px" viewBox="0 59 14 9" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			    <!-- Generator: Sketch 40.3 (33839) - http://www.bohemiancoding.com/sketch -->
			    <desc>Created with Sketch.</desc>
			    <defs></defs>
			    <g id="angle-arrow-down" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" transform="translate(0.000000, 59.000000)">
			        <path d="M13.8566596,0.860189474 L13.1554807,0.159059649 C13.0620491,0.0654315789 12.9544211,0.0187649123 12.8327439,0.0187649123 C12.7113614,0.0187649123 12.6037825,0.0654315789 12.5103509,0.159059649 L6.99832982,5.67078596 L1.48655439,0.159207018 C1.39307368,0.0655789474 1.28549474,0.0189122807 1.16396491,0.0189122807 C1.04238596,0.0189122807 0.934807018,0.0655789474 0.841375439,0.159207018 L0.140294737,0.860385965 C0.0466666667,0.953817544 0,1.06139649 0,1.18297544 C0,1.30445614 0.0468140351,1.41203509 0.140294737,1.50546667 L6.67574035,8.04105965 C6.76917193,8.13454035 6.8768,8.18125614 6.99832982,8.18125614 C7.11985965,8.18125614 7.22729123,8.13454035 7.32067368,8.04105965 L13.8566596,1.50546667 C13.9500912,1.41198596 13.9965123,1.30440702 13.9965123,1.18297544 C13.9965123,1.06139649 13.9500912,0.953817544 13.8566596,0.860189474 L13.8566596,0.860189474 Z" id="Shape" fill="#EB6551"></path>
			    </g>
			</svg>';
	}
	
	public function rst_menuPremium() {
		
		return '
<svg width="22px" height="18px" viewBox="0 0 22 18" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="vertical-align:top;">
<!-- Generator: Sketch 46 (44423) - http://www.bohemiancoding.com/sketch -->
<desc>Created with Sketch.</desc>
<defs></defs>
<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
    <g id="noun_739701_cc" fill-rule="nonzero" fill="#FFFFFF">
        <path d="M19.1071429,5.442 C17.8534286,5.442 16.8285714,6.44114286 16.7868571,7.68514286 L15.172,8.41542857 L12.8417143,4.42057143 C13.2022857,3.974 13.4128571,3.40942857 13.4128571,2.81342857 C13.4128571,1.40171429 12.2662857,0.253142857 10.8568571,0.253142857 C9.44742857,0.253142857 8.30085714,1.40171429 8.30085714,2.81342857 C8.30085714,3.40942857 8.51142857,3.97428571 8.872,4.42057143 L6.54171429,8.41542857 L4.92685714,7.68514286 C4.88514286,6.44114286 3.86057143,5.442 2.60657143,5.442 C1.32657143,5.442 0.285142857,6.48342857 0.285142857,7.76342857 C0.285142857,8.87485714 1.07,9.806 2.11428571,10.0322857 L3.14285714,14.696 L3.14285714,16.8571429 C3.14285714,17.4882857 3.65457143,18 4.28571429,18 L17.4285714,18 C18.0597143,18 18.5714286,17.4882857 18.5714286,16.8571429 L18.5714286,14.696 L19.5997143,10.0325714 C20.644,9.80571429 21.4285714,8.87457143 21.4285714,7.76342857 C21.4285714,6.48314286 20.3871429,5.442 19.1071429,5.442 Z M4.28571429,16.8571429 L4.28571429,14.8571429 L17.4285714,14.8571429 L17.4285714,16.8571429 L4.28571429,16.8571429 Z M19.1071429,8.942 C18.958,8.942 18.8174286,8.90828571 18.6885714,8.85742857 L17.4917143,14.2857143 L4.22285714,14.2857143 L3.02628571,8.85742857 C2.89742857,8.90828571 2.75628571,8.942 2.60714286,8.942 C1.95771429,8.942 1.42857143,8.41142857 1.42857143,7.76342857 C1.42857143,7.10914286 1.95771429,6.58485714 2.60714286,6.58485714 C3.25657143,6.58485714 3.78571429,7.10914286 3.78571429,7.76342857 C3.78571429,7.97571429 3.72514286,8.17314286 3.62485714,8.35057143 L7.01028571,9.88085714 L10.3608571,4.13685714 C9.82457143,3.936 9.44428571,3.42057143 9.44428571,2.81314286 C9.44428571,2.03057143 10.0771429,1.39571429 10.8574286,1.39571429 C11.6377143,1.39571429 12.2705714,2.03057143 12.2705714,2.81314286 C12.2705714,3.42057143 11.8902857,3.936 11.354,4.13685714 L14.7045714,9.88085714 L18.09,8.35057143 C17.9897143,8.17314286 17.9291429,7.97571429 17.9291429,7.76342857 C17.9291429,7.10914286 18.4582857,6.58485714 19.1077143,6.58485714 C19.7571429,6.58485714 20.2857143,7.10914286 20.2857143,7.76342857 C20.2857143,8.41114286 19.7565714,8.942 19.1071429,8.942 Z" id="Shape"></path>
    </g>
</g>
</svg>';
	}
}