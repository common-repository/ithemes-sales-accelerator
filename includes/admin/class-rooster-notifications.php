<?php
if ( !defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

/**
* This class manages plugin notifications
**/

class IT_View_Notifications {

    // Singleton design pattern
    protected static $instance = NULL;
    private $notifications;
    
    // Method to return the singleton instance
    public static function get_instance() {
	    
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct() {
	    
	    $this->init_notifications();
    }
    
    // Initializes notifications variable through a filter
    public function init_notifications() {
	    
	    $this->notifications = apply_filters( 'it_rst_available_notifications', array() );
    }
    
    // Get current new notifications
    public function get_notifications() {
	    
	    return $this->notifications;
    }
}
