<?php

if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class manages privacy functions
**/

class IT_RST_Privacy_Manager {
    
    // Singleton design pattern
    protected static $instance 	= NULL;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct() {
	    
	    add_action( 'admin_init', array( $this, 'it_rst_privacy_text' ) );
    }
    
    public function it_rst_privacy_text() {
	    if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
	        return;
	    }
	 
	    $content = sprintf(
	        __( '<h3>What personal data does Sales Accelerator store in the database?</h3>Our plugin requires WooCommerce to work, so all personal information stored in the database comes from WooCommerce. We comply with the create, change and delete instructions that WooCommerce executes, so by deleting data from WooCommerce, we also delete data from our tables, too. Nevertheless, if you wish to delete data from our tables and not WooCommerce, you have two options:<ol> <li> Uninstall our plugin or; </li><li>Navigate to the Settings menu and, from the Reporting tab, use the option to request deletion of ALL data we store.</li></ol>', 'ithemes-sales-accelerator' ) );
	 
	    wp_add_privacy_policy_content(
	        'iThemes Sales Accelerator',
	        wp_kses_post( wpautop( $content, false ) )
	    );
    }

}