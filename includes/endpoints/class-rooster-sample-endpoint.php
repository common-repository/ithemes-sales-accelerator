<?php
class IT_RST_Sample_Endpoint{
    
    // Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance(){
        if (null == self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct(){
    }    
    
    // Test endpoint
    public function test_core_endpoint(){
	    
	    return array( 'data' => 'Seems like it is working!' );	    
    }
}
