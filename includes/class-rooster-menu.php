<?php

if ( !defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

/**
* This class manages plugin menus
**/

class IT_RST_Menu {
	
	public $rooster_settings_slug = 'ithemes-sales-acc-plugin-settings';
    public $general_settings_key  = 'main_settings';
    public $plugin_settings_tabs  = array();

    public function __construct(){
	    
	    // Defines setting tabs
        $this->plugin_settings_tabs = apply_filters( 'it_rst_filter_settings_tabs', array(
            "main_settings"	 => array( 'label' => __( 'General', 'ithemes-sales-accelerator' ), 'class' => 'main_settings' )
        ) );
        
        // Add rooster menu
        add_action( 'admin_menu', array( $this, 'add_rooster_menu' ) );
        
        // Change plugin options capability for main settings
        add_filter( 'option_page_capability_main_settings', array( $this, 'options_capability' ) );
        
        // Add the Rooster Toolbar to the Admin bar
        add_action( 'admin_bar_menu', array( $this, 'add_rooster_toolbar' ), 100 );
        
        // Check if db upgrade notice is to be shown
		add_action( 'admin_notices', array( $this, 'check_if_db_upgrade_is_needed' ) );     
		
		// About notifications shortcode
		add_shortcode( 'it_rst_notifications', array( $this, 'notification_about_shortcode' ) );
    }
    
    public function options_capability( $capability ) {
	    $permissions = IT_RST_Permissions::get_instance();
		    		    		    		    		    
		// Show menu and submenus only to users with selected roles
	    if ( $permissions->check_permission( 'core' ) ) {
			$capability = 'read';
		}
			
		return $capability;
    }
    
    public function notification_about_shortcode( $atts ) {
	    
	    $notification_obj 	  = IT_View_Notifications::get_instance();
	    $notifications    	  = $notification_obj->get_notifications();
	    
	    if ( $notifications ) {
	    
		    echo '<div class="box_content notification_box">';
		    echo '<h3>Notifications</h3>';	
		    echo '<p style="color: #878787; font-family: \'Quicksand\', sans-serif; font-size: 1em;padding: 0 100px; padding-bottom: 15px;">Be sure to view the important notifications found here.</p>';
	
		    foreach ( $notifications as $k => $notification ) {
			    $notification_text = $notification['about_notification'];
			    echo $notification_text;
		    }
		    
		    echo '</div>';
		    
		}
    }
    
    // Toolbar creation function
    public function add_rooster_toolbar() {
	    
	    $permissions = IT_RST_Permissions::get_instance();
		    		    		    		    		    		    
	    if ( is_admin() && $permissions->check_permission( 'core' ) ) {
		    global $wp_admin_bar;
		    $icon 			      = IT_RST_PLUGIN_URL . '/assets/img/sales-acc_wp_icon.svg';
		    $notification_obj 	  = IT_View_Notifications::get_instance();
		    $notifications    	  = $notification_obj->get_notifications();
		    $notifications_count  = count( $notifications );
		    $notification_counter = '';
		    $about_url            = menu_page_url( 'ithemes-sales-acc-plugin-about', false );
		    
		    if ( $notifications_count > 0 ) {		    
				$counter_text = sprintf( _n( '%s notification', '%s notifications', $notifications_count, 'ithemes-sales-accelerator' ), number_format_i18n( $notifications_count ) );
				$notification_counter = sprintf( ' <div class="wp-core-ui wp-ui-notification it_rst_issue_counter"><span aria-hidden="true">%d</span><span class="screen-reader-text">%s</span></div>', $notifications_count, $counter_text );
			}
		    
	        // Main Sales Accelerator node
	        $wp_admin_bar->add_node( array(
	            'id'    => 'it-sales-acc',
	            'title' => "<span class='it_rst_toolbar_icon'><img src='$icon'></img></span>" . $notification_counter,
	            'href'  => $about_url,
	        ) );
	        
	        // Notifications menu
	        if ( $notifications_count > 0 ) {
				$wp_admin_bar->add_menu( array(
					'parent' => 'it-sales-acc',
					'id'     => 'it-sales-acc-notifications',
					'title'  => __( 'Notifications', 'ithemes-sales-accelerator' ) . $notification_counter,
					'href'   => $about_url,
				) );
			}
		}
    }

    // Custom menu creation function
    public function add_rooster_menu() {
	    
        global $submenu;
        
        // add an item to the menu
        if ( is_admin() ) {
	
		    $permissions = IT_RST_Permissions::get_instance();
		    		    		    		    		    
		    // Show menu and submenus only to users with selected roles
		    if ( $permissions->check_permission( 'core' ) ) {
			          
	            add_menu_page( __( 'SalesAccelerator Modules', 'ithemes-sales-accelerator' ), __( 'Sales Accelerator', 'ithemes-sales-accelerator' ), 'read', 'ithemes-sales-acc', array(
	                $this,
	                'modules_page'
	            ), IT_RST_PLUGIN_URL . '/assets/img/sales-acc_wp_icon.svg', '60' );
	            
	            do_action( 'it_rst_menu_pages' );
									
	            // Settings submenu
	            add_submenu_page( 'ithemes-sales-acc', __( 'Sales Accelerator - Settings', 'ithemes-sales-accelerator' ), __('Settings', 'ithemes-sales-accelerator' ), 'read', 'ithemes-sales-acc-plugin-settings', array(
	                $this,
	                'settings_page'
	            ) );
	            
	            if ( !defined( 'IT_RST_PLUGIN_PREMIUM_ACTIVE' ) ) {
	            
		             // Upgrade submenu
		            add_submenu_page( 'ithemes-sales-acc', __( 'Sales Accelerator - Upgrade', 'ithemes-sales-accelerator' ), '<span style="color:#2EA2CC">' . __( 'Upgrade', 'ithemes-sales-accelerator' ) . '</span>', 'read', 'ithemes-sales-acc-plugin-upgrade', array(
		                $this,
		                'upgrade_page'
		            ) );    
	            }
	            	            
	            // Help & Support submenu
	            add_submenu_page( 'ithemes-sales-acc', __( 'Sales Accelerator - Help & Support', 'ithemes-sales-accelerator' ), __( 'Help & Support', 'ithemes-sales-accelerator' ), 'read', 'ithemes-sales-acc-plugin-help', array(
	                $this,
	                'help_page'
	            ) );
	            
	            // About submenu
	            add_submenu_page( 'ithemes-sales-acc', __( 'Sales Accelerator - About', 'ithemes-sales-accelerator' ), __( 'About', 'ithemes-sales-accelerator' ), 'read', 'ithemes-sales-acc-plugin-about', array(
	                $this,
	                'about_page'
	            ) );
	            
	            // Database upgrade page
		        add_submenu_page( null, __( 'SalesAccelerator - Database Upgrade', 'ithemes-sales-accelerator' ), __( 'Database Upgrade', 'ithemes-sales-accelerator' ), 'read', 'ithemes-sales-acc-plugin-database-upgrade', array(
		            $this,
		            'database_upgrade',
		        ) );
	                        
	            $submenu['ithemes-sales-acc'][0][0] = 'Modules';
	            
	            do_action( 'it_rst_admin_menu' );
				
				// Redirects to about page if plugin was activated for the first time
	            $this->check_if_just_activated();
            }
        }
    }
    
    public function check_if_just_activated() {
	    
	    global $rst_modules;
		
	    if ( !get_option( 'it_rooster_premium_already_activated' ) && defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) ) {
		    update_option( 'it_rooster_premium_already_activated', 1 );
		    update_option( 'it_rooster_premium_version', 1 );
		    do_action( 'it_rst_premium_just_activated' );
	    }
	    else if ( get_option( 'it_rooster_premium_version' ) && !defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) ) {
		    delete_option( 'it_rooster_premium_version', 0 );
		    delete_option( 'it_rooster_premium_already_activated', 0 );
		    do_action( 'it_rst_just_downgraded' );
	    }
	    if ( !get_option( 'it_rooster_already_activated' ) ){
		    update_option( 'it_rooster_already_activated', 1 );
		    do_action( 'it_rst_just_activated' );
		    exit( wp_redirect( menu_page_url( 'ithemes-sales-acc-plugin-about', false ) ) );
	    }
    }

    // Modules list page
    public function modules_page() {
	    $modulesController = new IT_RST_Modules();
        $modulesController->handleRequest();
    }
    
    // Add option tabs to settings page
    public function plugin_options_tabs() {
        global $wp_settings_fields;

        $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
        if ( version_compare( $GLOBALS['wp_version'], '3.7.10', '<=' ) ) {
			screen_icon();
		}
        echo '<h2 class="nav-tab-wrapper">';

        foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
	        $label = $tab_caption['label'];
	        $class = $tab_caption['class'];
	        	        
            if ( isset( $wp_settings_fields[$this->rooster_settings_slug][$tab_key] ) && count( $wp_settings_fields[$this->rooster_settings_slug][$tab_key] ) > 0 ) {
                $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
                echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->rooster_settings_slug . '&tab=' . $tab_key . '">' . $label . '</a>';
            }
        }
        echo '</h2>';
    }
    
    // Plugin Settings Page
    public function settings_page() {
	    
      	$tab   	   	 	= isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
      	$class 	   	 	= isset( $this->plugin_settings_tabs[ $tab ]['class'] ) ? $this->plugin_settings_tabs[ $tab ]['class'] : '';
      	$dismiss_notice = __( 'Dismiss this notice', 'ithemes-sales-accelerator' ) . '.';
      	
      	?>
      	<form method="post" id="mainform" action="options.php">      	
      	<div id="rst_reporting_wrapper" class="settings">
			<div class="inline" style="width:100%;">
				<div style="display: block;"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/sales-acc_white.svg" style="height:50px;"></div>
				<span class="name_page"><?php _e( 'SETTINGS', 'ithemes-sales-accelerator' ); ?></span>
				<div class="btt_dashboard"><a href="<?php echo get_dashboard_url(); ?>"><span><?php _e( 'Dashboard', 'ithemes-sales-accelerator' ); ?></span></a></div>
			</div>
		</div>
		<div class="inside settings">
			<div class="row">
				<div class="col hide-on-med-and-down l2 xl2">&nbsp;</div>
				<div class="col s12 m12 l8 xl8">
					<div class="box_content first_box">
						<?php settings_errors(); ?>
						<?php settings_fields( $tab ); ?>
						<div id="rp-timer-saved" class="updated settings-error notice is-dismissible" style="display: none;"> 
							<p><strong><?php _e( 'Settings saved.', 'ithemes-sales-accelerator' ); ?></strong></p>
							<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'ithemes-sales-accelerator' ); ?></span></button>
						</div>
						<div class="settings_tabs">
							<?php $this->plugin_options_tabs(); ?>
						</div>
						<div class="content_settings <?php echo $class; ?>" style="padding: 30px 50px;">
							<table class="form-table">
								 <?php do_settings_fields( $this->rooster_settings_slug, $tab ); ?>
							</table>
							<?php submit_button(); ?>
						</div>
						
					</div>
					
					<div class="box_content last_box">
						<h5 style="color:#fff;"><?php _e( 'Get Sales Accelerator on your iPhone', 'ithemes-sales-accelerator' ); ?></h5>
						<p style="text-align: center;"><?php _e( 'Install the free app on your phone to get the mobile experience.', 'ithemes-sales-accelerator' ); ?></p>
						<a href="https://ithemes.com/download-itsa-app" target="_blank"><span class="btt_download"><?php _e( 'Download the free app', 'ithemes-sales-accelerator' ); ?></span></a>
						<div style="margin-bottom:1px;">&nbsp;</div>
					</div>
					
					<div class="help_btts" style="width:100%; text-align:center; margin-top:40px;">
						<h5 style="color:#525252;"><?php _e( 'We’re here to help.', 'ithemes-sales-accelerator' ); ?></h5>
						<p style="color: #525252; font-family: 'Quicksand', sans-serif; font-size:14px; text-align: center;"><?php _e( 'Sales Accelerator comes with free, basic support for all users.', 'ithemes-sales-accelerator' ); ?></p>
						<div class="inline">
						
							<span class="last_btts it_link_external"><a href="https://wordpress.org/support/plugin/ithemes-sales-accelerator" target="_blank"><?php _e( 'Ask a Question', 'ithemes-sales-accelerator' ); ?></a></span>
							<span class="last_btts it_link_external"><a href="https://ithemeshelp.zendesk.com/hc/en-us/categories/115000280854-iThemes-Sales-Accelerator/" target="_blank"><?php _e( 'Search our Support Site', 'ithemes-sales-accelerator' ); ?></a></span>
							<span class="last_btts it_link_external"><a href="https://ithemes.com/sales-accelerator" target="_blank"><?php _e( 'Suggest a Feature', 'ithemes-sales-accelerator' ); ?></a></span>
						
						</div>
					</div>
				</div>
				<div class="col hide-on-med-and-down l2 xl2">&nbsp;</div>
			</div>	
		</div>		
		</form>
		<div class="img_footer"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/sales-acc_footer.svg"></div>
	  	<?php
    }
    
    // Upgrade page
    public function upgrade_page() {
	    
	    $view_render = IT_View_Render::get_instance();
        $view_render->render( 'upgrade', array() );
    }
    
     // Export page
    public function export_page() {
	    
	    $view_render = IT_View_Render::get_instance();
        $view_render->render( 'export', array() );
    }
    
    // Help & Support page
    public function help_page() {
	    
	    $view_render = IT_View_Render::get_instance();
        $view_render->render( 'help-support', array() );
    }
    
    // About page
    public function about_page() {
	    
	    $view_render = IT_View_Render::get_instance();
        $view_render->render( 'about', array() );
    }
    
    // Database upgrade page
    public function database_upgrade() {
	    if ( isset( $_GET['module'] ) ){
		    $module   = sanitize_text_field( $_GET['module'] );
		    $force    = ( isset( $_GET['force'] ) )			 ? sanitize_text_field( $_GET['force'] ) 		  : false;
		    $dismiss  = ( isset( $_GET['dismiss_notice'] ) ) ? sanitize_text_field( $_GET['dismiss_notice'] ) : false;
			
		    switch ( $module ){
			    case 'reporting':
			    	if ( class_exists( 'RST_Reporting_Database_Upgrade' ) ) {
				    	$db_upgrade = new RST_Reporting_Database_Upgrade();
				    	$db_upgrade->initialize_upgrade( $force, $dismiss );
			    	}
			    	break;
		    }
	    }
	    
	    // Redirect to previous page
	    $last_page = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
        wp_safe_redirect( $last_page );
        exit();
    }
    
    public function check_if_db_upgrade_is_needed() {
	    
	    $notification_obj = IT_View_Notifications::get_instance();
	    $notifications    = $notification_obj->get_notifications();
	    
	    foreach ( $notifications as $notification ) {
		    $admin_notification = isset( $notification['admin_notification'] ) ? $notification['admin_notification'] : '';
		    if ( $admin_notification ) {
			    echo $admin_notification;
		    }
	    }
    }
}
