<?php

if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles module Settings
**/

class RST_Reporting_Settings {
	
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
		    
		// Add settings tab
		add_filter( 'it_rst_filter_settings_tabs', array( $this, 'add_reporting_settings_tab' ), 25, 1 );
		
		// Add settings to created tab
		add_filter( 'it_rst_filter_settings', 	   array( $this, 'add_reporting_settings' ), 25, 1) ;		
		add_filter( 'it_rst_filter_settings', 	   array( $this, 'add_dashboard_settings' ), 25, 1 );	
		
		// Shortcodes
		add_shortcode( 'rst_reports_list',   	   array( $this, 'rst_reportsList' ) );	
		add_shortcode( 'rst_arrow_green',  		   array( $this, 'rst_arrowGreen' ) );	
		add_shortcode( 'rst_arrow_yellow',         array( $this, 'rst_arrowYellow' ) );
		add_shortcode( 'rst_arrow_red',    		   array( $this, 'rst_arrowRed' ) );		
		
		// Change plugin options capability for reporting and dashboard settings
        add_filter( "option_page_capability_reporting_settings", array( $this, 'options_capability' ) );
        add_filter( "option_page_capability_dashboard_settings", array( $this, 'options_capability' ) );        
	}
	
	public function options_capability( $capability ) {
		
		$permissions = IT_RST_Permissions::get_instance();
		    		    		    		    		    
	    // Show menu and submenus only to users with selected roles
	    if ( $permissions->check_permission( 'reporting' ) ) {
			$capability = 'read';			
		}
		
		return $capability;
    }
	
	public function add_reporting_settings_tab( $tabs ) {
		
		$permissions = IT_RST_Permissions::get_instance();
		    		    		    		    		    
	    // Show menu and submenus only to users with selected roles
	    if ( $permissions->check_permission( 'reporting' ) ) {
			$tabs['reporting_settings'] = array( 'label' => __( 'Reporting', 'ithemes-sales-accelerator' ), 'class' => 'reporting_settings' );
			$tabs['dashboard_settings'] = array( 'label' => __( 'Dashboard', 'ithemes-sales-accelerator' ), 'class' => 'dashboard_settings' );
		}
		return $tabs;
	}
		
	public function add_reporting_settings( $settings ) {
			
		$settings[] = array(
		  'name' 	=> 'reporting_settings',
          'title'	=> __( 'Sales Accelerator Settings', 'ithemes-sales-accelerator' ),
          'page' 	=> IT_RST_Settings_Controller::$settings_page,
          'class'   => $this,
          'settings'=> array(
           		array(
					'name' => 'it_rooster_reporting_roles',
					'title' => __( 'Reporting Access Roles<br><span class="desc">Select the user roles to grant access to the Sales Accelerator plugin menu and submenus.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
                      'name' => 'it_rooster_reporting_roles_exceptions',
                      'title' => __('Access Exceptions<br><span class="desc">Select users of the roles defined above that should not be granted access to dashboard.</span>', "ithemes-sales-accelerator"),
					),
				array(
					'name' => 'it_rooster_reporting_order_status',
					'title' => __( 'Order Status<br><span class="desc">Choose the order types to include in Stats.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_reporting_total_net',
					'title' => __( 'Total Net<br><span class="desc">Choose which fields to be considered in the Total Net.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_reporting_total_sales',
					'title' => __( 'Total Sales<br><span class="desc">Calculate total Sales by Gross or Net?</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_reporting_recent_orders',
					'title' => __( 'Recent Orders<br><span class="desc">Choose if you want to see the Recent Orders (sidebar) by Creation Date or Updated Date.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_reporting_cache',
					'title' => __( 'Enable Object Cache<br><span class="desc">This caches reports queries to allow faster results.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_reporting_new_import',
					'title' => __( 'Re-Import<br><span class="desc">Delete imported data and start new import.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_reporting_remove_data',
					'title' => __( 'Delete data<br><span class="desc">Remove all reporting data.</span>', 'ithemes-sales-accelerator' ),
				),
			),
		);
		
		return $settings;
	}
	
	public function add_dashboard_settings( $settings ) {
			
		$settings[] = array(
		  'name' 	=> 'dashboard_settings',
          'title'	=> __( 'Sales Accelerator Settings', 'ithemes-sales-accelerator' ),
          'page' 	=> IT_RST_Settings_Controller::$settings_page,
          'class'   => $this,
          'settings'=> array(
	          	array(
		          	'name' => 'it_rooster_dashboard_grid_help',
		          	'title' => __( 'Report Types & Live Updates<br><span class="desc">See which report types are available and if live updates are enabled.</span>', 'ithemes-sales-accelerator' ),
	          	),
           		array(
					'name' => 'it_rooster_dashboard_grid_ff',
					'title' => __( 'Fast Facts<br><span class="desc">Choose the four items for the Fast Facts section of your Reports dashboard.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_dashboard_grid_overview',
					'title' => __( 'Overview<br><span class="desc">Choose nine items for the Overview section of your Reports dashboard. The first three items will be placed in the bigger boxes.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_dashboard_table_overview',
					'title' => __( 'Table<br><span class="desc">Choose a report item to be included in the table section of your Reports dashboard.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_dashboard_charts_overview',
					'title' => __( 'Charts<br><span class="desc">Choose a report item to be included in a chart in your Reports dashboard.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
					'name' => 'it_rooster_dashboard_reset_grid',
					'title' => __( 'Reset Grid<br><span class="desc">Reset the grid position to default.</span>', 'ithemes-sales-accelerator' ),
				),
				array(
                  'name' => 'it_rooster_dashboard_notification_desc',
                  'title' => __('<div class="notifications_desc">Resetting the grid will result in losing any changes you have made to the Reports dashboard using drag and drop.</div>', "ithemes-sales-accelerator"),
				),
			),
		);
		
		return $settings;
	}
		
	// Grid System - Fast Facts
	public function it_rooster_dashboard_grid_ff() {
		
		global $it_rst_dashboard_settings;
		$settings = $it_rst_dashboard_settings;
		$value 	  = isset( $settings['fast_facts'] ) ? $settings['fast_facts'] : '';
	    echo "<input type='text' name='it_rooster_dashboard_settings[fast_facts]' id='it_rooster_dashboard_settings[fast_facts]' class='it_settings_fastfacts_selectize' value='$value' autocomplete='off' /><span class='desc add_option' id='it_settings_fastfacts_selectize' style='text-align:center;'>add new option</span>";
	}
	
	// Dummy field
    public function it_rooster_dashboard_notification_desc() {}
	
	// Grid System - Overview
	public function it_rooster_dashboard_grid_overview() {
		
		global $it_rst_dashboard_settings;
		$settings = $it_rst_dashboard_settings;
	    $value 	  = isset( $settings['overview'] ) ? $settings['overview'] : '';
	    echo "<input type='text' name='it_rooster_dashboard_settings[overview]' id='it_rooster_dashboard_settings[overview]' class='it_settings_overview_selectize' value='$value' autocomplete='off' /><span class='desc add_option' id='it_settings_overview_selectize' style='text-align:center;'>add new option</span>";
	}
	
	// Grid System - Table
	public function it_rooster_dashboard_table_overview() {
		
		global $it_rst_dashboard_settings;
		$settings = $it_rst_dashboard_settings;
	    $value 	  = isset( $settings['table'] ) ? $settings['table'] : '';
	    echo "<input type='text' name='it_rooster_dashboard_settings[table]' id='it_rooster_dashboard_settings[table]' class='it_settings_table_selectize' value='$value' autocomplete='off' /><span class='add_option desc' id='it_settings_table_selectize' style='text-align:center;'>add new option</span>";
	}
	
	// Grid System - Charts
	public function it_rooster_dashboard_charts_overview() {
		
		global $it_rst_dashboard_settings;
		$settings = $it_rst_dashboard_settings;
	    $value 	  = isset( $settings['charts'] ) ? $settings['charts'] : '';
	    echo "<input type='text' name='it_rooster_dashboard_settings[charts]' id='it_rooster_dashboard_settings[charts]' class='it_settings_charts_selectize' value='$value' autocomplete='off' /><span class='add_option desc' id='it_settings_charts_selectize' style='text-align:center;'>add new option</span>";
	}
		
	// Fields functions
	public function it_rooster_reporting_roles() {
		
      global $it_rst_reporting_settings;
	  $settings = $it_rst_reporting_settings;
      $value    = isset( $settings['access_roles'] ) ? $settings['access_roles'] : '';
      echo "<input type='text' name='it_rooster_reporting_settings[access_roles]' id='it_rooster_reporting_settings[access_roles]' class='it_settings_roles_selectize' value='$value' autocomplete='off' />";    
	}
	
	public function it_rooster_reporting_roles_exceptions() {
		
	  global $it_rst_reporting_settings;
	  $settings = $it_rst_reporting_settings;
      $value 	= isset( $settings['module_roles_exceptions'] ) ? $settings['module_roles_exceptions'] : '';
      echo "<input type='text' name='it_rooster_reporting_settings[module_roles_exceptions]' id='it_rooster_reporting_settings[module_roles_exceptions]' class='it_settings_roles_exceptions_selectize' data-settings='reporting_exceptions' value='$value' autocomplete='off' />";
	}
	
	public function it_rooster_reporting_cache() {
		
		global $it_rst_reporting_settings;
		$settings = $it_rst_reporting_settings;
		$value 	  = $settings['cache_enabled'];
		$checked  = ( $value ) ? 'checked ' : '';
		    
		echo '<label class="switch-modules">
				<input name="it_rooster_reporting_settings[cache_enabled]" id="it_rooster_reporting_settings[cache_enabled]" value="0" type="hidden">
					<input ' . $checked . 'class="it_rooster_checkbox" name="it_rooster_reporting_settings[cache_enabled]" id="it_rooster_reporting_settings[cache_enabled]" value="1" type="checkbox">
				<span class="slider-m round-m"></span>
		    </label>';
	}
	
	// Set recent orders (Creation date/Updated date)
	public function it_rooster_reporting_recent_orders() {
		
		global $it_rst_reporting_settings;
		$settings 	= $it_rst_reporting_settings;
		$options 	= array( 'creation_date' => 'Creation Date', 'updated_date' => 'Updated Date' );
        $fill1 		= ( isset( $settings['recent_orders_creation'] ) && $settings['recent_orders_creation'] )  ? '#4DC2F0'  : '#FFFFFF';
        $fill2 		= ( isset( $settings['recent_orders_updated'] )  && $settings['recent_orders_updated']   ) ? '#4DC2F0'  : '#FFFFFF';
        $chkd1 		= ( isset( $settings['recent_orders_creation'] ) && $settings['recent_orders_creation'] )  ? ' checked' : '';
        $chkd2 		= ( isset( $settings['recent_orders_updated'] )  && $settings['recent_orders_updated'] )   ? ' checked' : '';
                        
        echo '<div class="inline">
        		<input name="it_rooster_reporting_settings[recent_orders_creation]" id="it_rooster_reporting_settings[recent_orders_creation]" value="0" type="hidden">
        		<input class="it_rooster_checkbox it_rooster_creation_date" name="it_rooster_reporting_settings[recent_orders_creation]" id="it_rooster_reporting_settings[recent_orders_creation]" value="1" type="checkbox"' . $chkd1 . '>
				<svg width="20px" height="20px" class="creation" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				    <!-- Generator: Sketch 45.2 (43514) - http://www.bohemiancoding.com/sketch -->
				    <desc>Created with Sketch.</desc>
				    <defs>
				        <rect id="path-1" x="94" y="687" width="20" height="20" rx="10"></rect>
				    </defs>
				    <g id="Components" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				        <g id="Guide-02-Avenir-Copy-2" transform="translate(-94.000000, -687.000000)">
				            <g id="Rectangle-Copy-6">
				                <use fill="' . $fill1 . '" fill-rule="evenodd" xlink:href="#path-1" id="circle"></use>
				                <rect stroke="#DFDFDF" stroke-width="1" x="94.5" y="687.5" width="19" height="19" rx="9.5"></rect>
				            </g>
				            <circle id="Oval" fill="#FFFFFF" cx="104" cy="697" r="3"></circle>
				        </g>
				    </g>
				</svg> <span style="padding-left:5px; padding-top: 2px;">Creation Date</span>
			</div>
			<div class="inline" style="padding-left:10px;">
				<input name="it_rooster_reporting_settings[recent_orders_updated]" id="it_rooster_reporting_settings[recent_orders_updated]" value="0" type="hidden">
				<input class="it_rooster_checkbox it_rooster_updated_date" name="it_rooster_reporting_settings[recent_orders_updated]" id="it_rooster_reporting_settings[recent_orders_updated]" value="1" type="checkbox"' . $chkd2 . '>
				<svg width="20px" height="20px" class="updated" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				    <!-- Generator: Sketch 45.2 (43514) - http://www.bohemiancoding.com/sketch -->
				    <desc>Created with Sketch.</desc>
				    <defs>
				        <rect id="path-1" x="94" y="687" width="20" height="20" rx="10"></rect>
				    </defs>
				    <g id="Components" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				        <g id="Guide-02-Avenir-Copy-2" transform="translate(-94.000000, -687.000000)">
				            <g id="Rectangle-Copy-6">
				                <use fill="' . $fill2 . '" fill-rule="evenodd" xlink:href="#path-1" id="circle"></use>
				                <rect stroke="#DFDFDF" stroke-width="1" x="94.5" y="687.5" width="19" height="19" rx="9.5"></rect>
				            </g>
				            <circle id="Oval" fill="#FFFFFF" cx="104" cy="697" r="3"></circle>
				        </g>
				    </g>
				</svg> <span style="padding-left:5px; padding-top: 2px;">Updated Date</span>
			</div>';
	}
	
	// Set orders status to consider
	public function it_rooster_reporting_order_status() {
		
		global $it_rst_reporting_settings;
		$settings = $it_rst_reporting_settings;
	    $value 	  = isset( $settings['order_status'] ) ? $settings['order_status'] : '';
	    echo "<input type='text' name='it_rooster_reporting_settings[order_status]' id='it_rooster_reporting_settings[order_status]' class='it_settings_status_selectize' value='$value' autocomplete='off' />";
	}
	
	// Set total sales by gross or net
	public function it_rooster_reporting_total_sales() {
		
		global $it_rst_reporting_settings;
		$settings 	= $it_rst_reporting_settings;
		$options 	= array( 'gross' => 'Gross', 'net' => 'Net' );
        $fill1 		= ( isset( $settings['total_sales_gross'] ) && $settings['total_sales_gross'] ) ? '#4DC2F0'  : '#FFFFFF';
        $fill2 		= ( isset( $settings['total_sales_net'] )   && $settings['total_sales_net']   ) ? '#4DC2F0'  : '#FFFFFF';
        $chkd1 		= ( isset( $settings['total_sales_gross'] ) && $settings['total_sales_gross'] ) ? ' checked' : '';
        $chkd2 		= ( isset( $settings['total_sales_net'] )   && $settings['total_sales_net'] ) 	? ' checked' : '';
        
        echo '<div class="inline">
        		<input name="it_rooster_reporting_settings[total_sales_gross]" id="it_rooster_reporting_settings[total_sales_gross]" value="0" type="hidden">
        		<input class="it_rooster_checkbox total_sales_gross" name="it_rooster_reporting_settings[total_sales_gross]" id="it_rooster_reporting_settings[total_sales_gross]" value="1" type="checkbox"' . $chkd1 . '>
				<svg width="20px" height="20px" class="gross" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				    <!-- Generator: Sketch 45.2 (43514) - http://www.bohemiancoding.com/sketch -->
				    <desc>Created with Sketch.</desc>
				    <defs>
				        <rect id="path-1" x="94" y="687" width="20" height="20" rx="10"></rect>
				    </defs>
				    <g id="Components" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				        <g id="Guide-02-Avenir-Copy-2" transform="translate(-94.000000, -687.000000)">
				            <g id="Rectangle-Copy-6">
				                <use fill="' . $fill1 . '" fill-rule="evenodd" xlink:href="#path-1" id="circle"></use>
				                <rect stroke="#DFDFDF" stroke-width="1" x="94.5" y="687.5" width="19" height="19" rx="9.5"></rect>
				            </g>
				            <circle id="Oval" fill="#FFFFFF" cx="104" cy="697" r="3"></circle>
				        </g>
				    </g>
				</svg> <span style="padding-left:5px; padding-top: 2px;">Gross Sales</span>
			</div>
			<div class="inline" style="padding-left:10px;">
				<input name="it_rooster_reporting_settings[total_sales_net]" id="it_rooster_reporting_settings[total_sales_net]" value="0" type="hidden">
				<input class="it_rooster_checkbox total_sales_net" name="it_rooster_reporting_settings[total_sales_net]" id="it_rooster_reporting_settings[total_sales_net]" value="1" type="checkbox"' . $chkd2 . '>
				<svg width="20px" height="20px" class="net" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
				    <!-- Generator: Sketch 45.2 (43514) - http://www.bohemiancoding.com/sketch -->
				    <desc>Created with Sketch.</desc>
				    <defs>
				        <rect id="path-1" x="94" y="687" width="20" height="20" rx="10"></rect>
				    </defs>
				    <g id="Components" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				        <g id="Guide-02-Avenir-Copy-2" transform="translate(-94.000000, -687.000000)">
				            <g id="Rectangle-Copy-6">
				                <use fill="' . $fill2 . '" fill-rule="evenodd" xlink:href="#path-1" id="circle"></use>
				                <rect stroke="#DFDFDF" stroke-width="1" x="94.5" y="687.5" width="19" height="19" rx="9.5"></rect>
				            </g>
				            <circle id="Oval" fill="#FFFFFF" cx="104" cy="697" r="3"></circle>
				        </g>
				    </g>
				</svg> <span style="padding-left:5px; padding-top: 2px;">Net Sales</span>
			</div>';
	}
	
	// Set elements to consider on the total net calculation
	public function it_rooster_reporting_total_net() {
		
		global $it_rst_reporting_settings;
		$settings = $it_rst_reporting_settings;
	    $value 	  = isset( $settings['total_net'] ) ? $settings['total_net'] : '';
	    echo "<input type='text' name='it_rooster_reporting_settings[total_net]' id='it_rooster_reporting_settings[total_net]' class='it_settings_total_net_selectize' value='$value' autocomplete='off' />";
	}
	
	// Shortcode to Reports List 
	public function rst_reportsList() {
		
		$features 	  = RST_Reporting_Features::get_instance();
		$feature_list = $features->get_features();
		
		$html = '<div class="list">
					<div class="row npm">
							<div class="col s4">' . __( 'Title', 'ithemes-sales-accelerator' ) . '</div>
							<div class="col s2">' . __( 'Fast Fact', 'ithemes-sales-accelerator' ) . '</div>
							<div class="col s2">' . __( 'Overview', 'ithemes-sales-accelerator' ) . '</div>
							<div class="col s2">' . __( 'Table', 'ithemes-sales-accelerator' ) . '</div>
							<div class="col s2">' . __( 'Chart', 'ithemes-sales-accelerator' ) . '</div>
					</div>';
					
					foreach ( $feature_list as $feature ) {
						
						$value 		= $feature['value'];
						$ff_key     = array_search( 'fastfacts', $feature['type'] );
						$ov_key     = array_search( 'overview', $feature['type'] );
						$tb_key     = array_search( 'table', $feature['type'] );
						$ch_key     = array_search( 'charts', $feature['type'] );
												
						if ( $ff_key !== false && isset( $feature['premium'][ $ff_key ] ) && !$feature['premium'][ $ff_key ] ) {
							$ff = '[rst_arrow_green]';
						}
						else if ( $ff_key !== false ) {
							$ff = '[rst_arrow_yellow]';
						}
						else {
							$ff = '[rst_arrow_red]';
						}
						
						if ( $ov_key !== false && isset( $feature['premium'][ $ov_key ] ) && !$feature['premium'][ $ov_key ] ) {
							$ov = '[rst_arrow_green]';
						}
						else if ( $ov_key !== false ) {
							$ov = '[rst_arrow_yellow]';
						}
						else {
							$ov = '[rst_arrow_red]';
						}
						
						if ( $tb_key !== false && isset( $feature['premium'][ $tb_key ] ) && !$feature['premium'][ $tb_key ] ) {
							$tb = '[rst_arrow_green]';
						}
						else if ( $tb_key !== false ) {
							$tb = '[rst_arrow_yellow]';
						}
						else {
							$tb = '[rst_arrow_red]';
						}
						
						if ( $ch_key !== false && isset( $feature['premium'][ $ch_key ] ) && !$feature['premium'][ $ch_key ] ) {
							$ch = '[rst_arrow_green]';
						}
						else if ( $ch_key !== false ) {
							$ch = '[rst_arrow_yellow]';
						}
						else {
							$ch = '[rst_arrow_red]';
						}
												
						$html .= '<div class="row npm">
							<div class="col s4">' . $value . '</div>
							<div class="col s2">' . do_shortcode( $ff ) . '</div>
							<div class="col s2">' . do_shortcode( $ov ) . '</div>
							<div class="col s2">' . do_shortcode( $tb ) . '</div>
							<div class="col s2">' . do_shortcode( $ch ) . '</div>
						</div>';
					}
					
				$html .= '</div><div class="inline" style="margin-top: 20px;font-size: .9em;color: #4C4F53;font-family: \'Nunito\', sans-serif; "><span style="margin-right:5px;">' . do_shortcode('[rst_arrow_green]') . '</span> Available <span style="margin:0 5px;">' . do_shortcode('[rst_arrow_red]') . '</span> Not available <span style="margin:0 5px;">' . do_shortcode('[rst_arrow_yellow]') . '</span> Disabled in Free Version';

				
				// Show this message only for free users
				if ( !defined( 'IT_RST_PLUGIN_PREMIUM_ACTIVE' ) ) {
					$html .= '<span class="upgrade">' . __( 'Upgrade now!', 'ithemes-sales-accelerator' ) . '</span>';
				}
				
				$html .= '</div>';
		
		return $html;
	}
	
	public function rst_arrowGreen() {
		
		return '<svg width="14px" height="15px" viewBox="0 0 14 15" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
		    <!-- Generator: Sketch 45.2 (43514) - http://www.bohemiancoding.com/sketch -->
		    <desc>Created with Sketch.</desc>
		    <defs></defs>
		    <g id="Components" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
		        <g id="Guide-02-Avenir-Copy-2" transform="translate(-497.000000, -2750.000000)">
		            <g id="Group-9-Copy" transform="translate(94.000000, 2715.000000)">
		                <g id="Group-8" transform="translate(403.000000, 35.400000)">
		                    <circle id="Oval-2" fill="#47CF97" cx="7" cy="7" r="7"></circle>
		                    <polyline id="Path-3-Copy-2" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" points="3.86920561 7.93588908 5.6455475 9.95380312 9.56953262 4.55"></polyline>
		                </g>
		            </g>
		        </g>
		    </g>
		</svg>';
	}
	
	public function rst_arrowYellow() {
		
		return '<svg width="14px" height="15px" viewBox="0 0 14 15" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
		    <!-- Generator: Sketch 45.2 (43514) - http://www.bohemiancoding.com/sketch -->
		    <desc>Created with Sketch.</desc>
		    <defs></defs>
		    <g id="Components" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
		        <g id="Guide-02-Avenir-Copy-2" transform="translate(-497.000000, -2750.000000)">
		            <g id="Group-9-Copy" transform="translate(94.000000, 2715.000000)">
		                <g id="Group-8" transform="translate(403.000000, 35.400000)">
		                    <circle id="Oval-2" fill="#E6E600" cx="7" cy="7" r="7"></circle>
		                    <polyline id="Path-3-Copy-2" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" points="3.86920561 7.93588908 5.6455475 9.95380312 9.56953262 4.55"></polyline>
		                </g>
		            </g>
		        </g>
		    </g>
		</svg>';
	}
	
	public function rst_arrowRed() {
		
		return '<svg width="14px" height="14px" viewBox="0 0 14 14" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			    <!-- Generator: Sketch 45.2 (43514) - http://www.bohemiancoding.com/sketch -->
			    <desc>Created with Sketch.</desc>
			    <defs></defs>
			    <g id="Components" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
			        <g id="Guide-02-Avenir-Copy-2" transform="translate(-499.000000, -2850.000000)">
			            <g id="Group-10" transform="translate(94.000000, 2814.000000)">
			                <g id="Group-7" transform="translate(405.000000, 36.000000)">
			                    <rect id="Rectangle-2" fill="#ED644C" x="0" y="0" width="14" height="14" rx="4"></rect>
			                    <g id="Group-6" transform="translate(3.500000, 3.500000)" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round">
			                        <path d="M0.249891493,0.421929253 L6.41922176,6.59125952" id="Path-3"></path>
			                        <path d="M0.249891493,0.421929253 L6.41922176,6.59125952" id="Path-3-Copy" transform="translate(3.334557, 3.506594) scale(-1, 1) translate(-3.334557, -3.506594) "></path>
			                    </g>
			                </g>
			            </g>
			        </g>
			    </g>
			</svg>';
	}
	
	public function it_rooster_dashboard_reset_grid() {
		
		echo '<span id="it_rst_reset_grid" class="last_btts">' . __( 'Reset', 'ithemes-sales-accelerator' ) . '</span>';
	}
	
	public function it_rooster_reporting_new_import() {
		
		echo '<span id="it_rst_reset_import" class="last_btts">' . __( 'Import', 'ithemes-sales-accelerator' ) . '</span>';
	}
	
	public function it_rooster_reporting_remove_data() {
		
		echo '<span id="it_rst_delete_data" class="last_btts">' . __( 'Delete', 'ithemes-sales-accelerator' ) . '</span>';
	}
	
	public function it_rooster_dashboard_grid_help() {
		
		echo '<div class="help modules">
						<div class="openModal_Reports"><span class="last_btts" style="display:inline-flex; text-align: center;">' . __( 'Available', 'ithemes-sales-accelerator' ) . '<br>' . __( 'Reports', 'ithemes-sales-accelerator' ) . '</span>
							<! --- Modal --- !>
							<div class="modal-modules">
								<div id="modal_content">
									<div class="inline" style="width:100%; position: relative; margin-bottom:30px;">
										<div class="name_desc" style="margin:0;">
											<h5>' . __( 'List of all Reports', 'ithemes-sales-accelerator' ) . '</h5>
										</div>
										<div style="position: absolute; right: 0;">
											<svg class="closeModal" width="17px" height="17px" viewBox="0 0 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
											    <defs></defs>
											    <g id="Modules" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
											        <g id="Modules---Welcome" transform="translate(-993.000000, -704.000000)" fill-rule="nonzero" fill="#D4D4D4">
											            <g id="Modal-Info" transform="translate(418.000000, 664.000000)">
											                <g id="Group-2" transform="translate(575.000000, 40.000000)">
											                    <path d="M8.5,7.08578644 L1.70710678,0.292893219 C1.31658249,-0.0976310729 0.683417511,-0.0976310729 0.292893219,0.292893219 C-0.0976310729,0.683417511 -0.0976310729,1.31658249 0.292893219,1.70710678 L7.08578644,8.5 L0.55714681,15.0286396 C0.166622518,15.4191639 0.166622518,16.0523289 0.55714681,16.4428532 C0.947671102,16.8333775 1.58083608,16.8333775 1.97136037,16.4428532 L8.5,9.91421356 L15.0286396,16.4428532 C15.4191639,16.8333775 16.0523289,16.8333775 16.4428532,16.4428532 C16.8333775,16.0523289 16.8333775,15.4191639 16.4428532,15.0286396 L9.91421356,8.5 L16.7071068,1.70710678 C17.0976311,1.31658249 17.0976311,0.683417511 16.7071068,0.292893219 C16.3165825,-0.0976310729 15.6834175,-0.0976310729 15.2928932,0.292893219 L8.5,7.08578644 L8.5,7.08578644 Z" id="Combined-Shape"></path>
											                </g>
											            </g>
											        </g>
											    </g>
											</svg>
										</div>
										
									</div>
									<div class="content_modal">
										' . do_shortcode( '[rst_reports_list]' ) . '
									</div>
								</div>
							</div>
							<! --- Modal --- !>
						</div>';
						
						do_action( 'it_rst_reporting_dashboard_settings_grid_help' );
	}
	
	//Settings validation function
	public function plugin_settings_validate( $arr_input ) {
		
	  global $it_rst_dashboard_settings, $it_rst_reporting_settings;
	  if ( isset( $arr_input['fast_facts'] ) && isset( $arr_input['overview'] ) && isset( $arr_input['table'] ) && isset( $arr_input['charts'] ) ) {
		  
		  $fastfacts  = ( $arr_input['fast_facts'] ) ? explode( ',', $arr_input['fast_facts'] ) : array();
		  $overview   = ( $arr_input['overview'] )   ? explode( ',', $arr_input['overview'] ) 	: array();
		  $table      = ( $arr_input['table'] ) 	 ? explode( ',', $arr_input['table'] ) 		: array();
		  $charts     = ( $arr_input['charts'] ) 	 ? explode( ',', $arr_input['charts'] )     : array();
		  $error 	  = '';
		  
		  $min_overview = ( defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) ) ? 9 : 6;
		  		  
		  if ( count( $fastfacts ) < 4 ) {
			  $error = __( 'Fast facts need at least 4 elements', 'ithemes-sales-accelerator' );
		  } 
		  else if ( count( $overview ) !== $min_overview ) {
			  $error = __( 'Overview need to have ' . $min_overview . ' elements', 'ithemes-sales-accelerator' );
		  }
		  else if ( count( $table ) < 1 ) {
			  $error = __( 'Table need at least 1 elements', 'ithemes-sales-accelerator' );
		  }
		  else if ( count( $charts ) < 2 ) {
			  $error = __( 'Charts need at least 2 elements', 'ithemes-sales-accelerator' );
		  }
		  
		  if ( !$error ) {
			  $features = RST_Reporting_Features::get_instance();		  
			  foreach ( $fastfacts as $fastfact ) {
				  $error = $features->is_feature_version_ready( $fastfact );
			  }
			  foreach ( $overview as $over ) {
				  $error = $features->is_feature_version_ready( $over );
			  }
			  foreach ( $table as $tabl ) {
				  $error = $features->is_feature_version_ready( $tabl );
			  }
			  foreach ( $charts as $chart ) {
				  $error = $features->is_feature_version_ready( $chart );
			  }
		  }
		  		  
		  if ( $error ) {
			  add_settings_error(
		        'it_rst_dashboard_settings_error',
		        esc_attr( 'settings_updated' ),
		        $error,
		        'error'
			  );
			  
			  return $it_rst_dashboard_settings;
		  }		  
	  }
	
	  if ( isset( $arr_input['total_net'] ) && isset( $arr_input['order_status'] ) ) {
		$error = '';
		
		if ( !$arr_input['total_net'] ) {
			$error = __( 'Total Net needs at least 1 element', 'ithemes-sales-accelerator' );
		}
		else if ( !$arr_input['order_status'] ) {
			$error = __( 'Order Status needs at least 1 element', 'ithemes-sales-accelerator' );
		}
		
		if ( $error ) {
		  add_settings_error(
	        'it_rst_dashboard_settings_error',
	        esc_attr( 'settings_updated' ),
	        $error,
	        'error'
		  );
		  
		  return $it_rst_reporting_settings;
		}
	  }

	  return $arr_input;
	}
}