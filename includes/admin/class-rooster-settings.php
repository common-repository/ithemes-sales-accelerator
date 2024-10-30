<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class manages settings fields and tabs
**/

class IT_RST_Settings_Controller {
	
  public static $settings_name 	 = 'it_rooster_settings';
  public static $settings_page	 = 'ithemes-sales-acc-plugin-settings';
  public static $settings_prefix = 'it_rooster_';

  public function __construct() {
	  
      // Hook up settings initialization
      add_action( 'admin_init', array( $this, 'settings_init' ) );
  }

  // Function that initiates plugin settings
  public function settings_init() {
	  
      global $woocommerce;
	  $settings_title	 = __( 'Sales Accelerator Settings', 'ithemes-sales-accelerator' );
	  
      if ( $woocommerce ) {
        wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );
      }

      $settings = array(
          array(
              'name' 	=> 'main_settings',
              'title'	=> $settings_title,
              'page' 	=> self::$settings_page,
              'class'   => $this,
              'settings'=> array(
               		array(
                      'name'  => 'it_rooster_plugin_roles',
                      'title' => __( 'Plugin Access Roles<br><span class="desc">Select the user roles to grant access to the Sales Accelerator plugin menu and submenus.</span>', 'ithemes-sales-accelerator' ),
					),
					array(
                      'name'  => 'it_rooster_plugin_roles_exceptions',
                      'title' => __( 'Access Exceptions<br><span class="desc">Select users of the roles defined above that should not be granted access.</span>', 'ithemes-sales-accelerator' ),
					),
					array(
                      'name'  => 'it_rooster_plugin_api_generate_key',
                      'title' => __( 'API<br><span class="desc">Switch this option on to be able to use Sales Accelerator mobile app with this installation.</span>', 'ithemes-sales-accelerator' ),
					),
					array(
                      'name'  => 'it_rooster_notifications',
                      'title' => __( 'Notifications<br><span class="desc">Receive email updates on our latest additions to Sales Accelerator.</span>', 'ithemes-sales-accelerator' ),
					),
					array(
                      'name' => 'it_rooster_notification_desc',
                      'title' => __( '<div class="notifications_desc">Our notifications are short and sweet. We promise we won’t take too much time from you.</div>', 'ithemes-sales-accelerator' ),
					)
              )
          )
      );
      
      // Allow modules to add settings
	  $settings = apply_filters( 'it_rst_filter_settings', $settings );

      foreach ( $settings as $section ) {
          // add the main part
          add_settings_section( $section['name'], $section['title'], array(
              $section['class'],
              $section['name']
          ), $section['page'] );
		  
          // loop each settings of the block
          foreach ( $section['settings'] as $option ) {
	          	          
	          $class = ( isset( $option['class'] ) ) ? $option['class'] : $section['class'];
	          	          
	          // add & register the settings field
              add_settings_field( $option['name'], $option['title'], array(
                  $class,
                  $option['name']
              ), $section['page'], $section['name'] );
              
              register_setting( $section['name'], self::$settings_prefix . $section['name'], array( $section['class'], 'plugin_settings_validate' ) );       			  
          }
      }
  }
  
  // Core fields
  public function it_rooster_plugin_roles() {
	  
	  global $it_rst_main_settings;
      $value = isset( $it_rst_main_settings['plugin_roles'] ) ? $it_rst_main_settings['plugin_roles'] : '';
      echo "<input type='text' name='it_rooster_main_settings[plugin_roles]' id='it_rooster_settings[plugin_roles]' class='it_settings_roles_selectize' value='$value' autocomplete='off' />";
  }
  
  // Plugin role restriction exceptions field
  public function it_rooster_plugin_roles_exceptions() {
	  
	  global $it_rst_main_settings;
      $value = isset( $it_rst_main_settings['plugin_roles_exceptions'] ) ? $it_rst_main_settings['plugin_roles_exceptions'] : '';
      echo "<input type='text' name='it_rooster_main_settings[plugin_roles_exceptions]' id='it_rooster_settings[plugin_roles_exceptions]' class='it_settings_roles_exceptions_selectize' data-settings='role_exceptions' value='$value' autocomplete='off' />";
      
  }
  
  // Dummy field
  public function it_rooster_notification_desc() {}
  
  public function it_rooster_plugin_api_generate_key() {
	  
	  global $wpdb, $it_rst_main_settings; 
	  
      $value 	  = isset( $it_rst_main_settings['api_access'] ) ? $it_rst_main_settings['api_access'] : '';
      $value2 	  = isset( $it_rst_main_settings['api_id'] )     ? $it_rst_main_settings['api_id'] : '';
            	      
	  if ( $value && !$value2 ) {
		 
		  $user  	   = wp_get_current_user();
		  $scope 	   = 'read_write';
		  $app_name    = 'SalesAccelerator';
		  $app_user_id = 'SalesAccelerator App';
		  
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
		  
		  $it_rst_main_settings['api_id'] = $wpdb->insert_id;
		  update_option( 'it_rooster_main_settings', $it_rst_main_settings );
		  
		  $code = "$consumer_key|$consumer_secret|" . site_url();
		  echo '<img src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=' . $code . '" /><br><div class="tooltip" style="width:200px; text-align:center;"><span style="font-size:0.85em; color:#878787; text-decoration: underline;">' . __( 'instructions', 'ithemes-sales-accelerator' ) . '</span><span class="tooltiptext tooltip-top">' . __( 'Open SalesAccelerator app and scan this code to add the site.', 'ithemes-sales-accelerator' ) . '</span></span></div>';
	  }
	  else {
		  $checked  = ( $value ) ? 'checked ' : '';
		  $regenerate = false;
		  if ( $checked ){
			  $regenerate = true;
			  $result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys  WHERE key_id = $value2" );
			  if ( !$result ) {
				  $checked = '';
				  $it_rst_main_settings['api_access'] = 0;
				  update_option( 'it_rooster_main_settings', $it_rst_main_settings );
			  }
		  }
		  else {
			  if( $value2 ) {
				  $result = $wpdb->delete( $wpdb->prefix . 'woocommerce_api_keys', array( 'key_id' => $value2 ) );
				  $value2 = 0;
				  
				  $it_rst_main_settings['api_id'] = 0;
				  update_option( 'it_rooster_main_settings', $it_rst_main_settings );				  
			  }
		  }
		  
		  echo '<label class="switch-modules">
		  		<input name="it_rooster_main_settings[api_id]" id="it_rooster_main_settings[api_id]" value="' . $value2 . '" type="hidden">
				<input name="it_rooster_main_settings[api_access]" id="it_rooster_main_settings[api_access]" value="0" type="hidden">
				<input ' . $checked . 'class="it_rooster_checkbox" name="it_rooster_main_settings[api_access]" id="it_rooster_main_settings[api_access]" value="1" type="checkbox">
				<span class="slider-m round-m"></span>
		    </label>';
		 
		 if( $regenerate ) {
			echo '<br><div id="it_rst_regenerate_key" class="tooltip"><span style="font-size:0.85em; color:#878787; text-decoration: underline;">' . __( 'Regenerate Key', 'ithemes-sales-accelerator' ) . '</span><span class="tooltiptext tooltip-top">' . __( 'Click here to regenerate new API key.', 'ithemes-sales-accelerator' ) . '</span></span></div>';
		 }
	  }
  }
  
  // Notifications field
  public function it_rooster_notifications() {
	  
	  global $it_rst_main_settings;	 
      $value 	  = isset( $it_rst_main_settings['notifications'] ) ? $it_rst_main_settings['notifications'] : '';
      $checked  = ( $value ) ? 'checked ' : '';
	  echo '<label class="switch-modules">
				<input name="it_rooster_main_settings[notifications]" id="it_rooster_main_settings[notifications]" value="0" type="hidden">
					<input ' . $checked . 'class="it_rooster_checkbox" name="it_rooster_main_settings[notifications]" id="it_rooster_main_settings[notifications]" value="1" type="checkbox">
				<span class="slider-m round-m"></span>
		    </label>';
  }
  
  //Settings validation function
  public function plugin_settings_validate( $arr_input ) {
	  
    return $arr_input;
  }

}
