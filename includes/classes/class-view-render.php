<?php
if ( !defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

class IT_View_Render {

    private $directory;
    
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
	    
        $this->directory = untrailingslashit( plugin_dir_path( IT_RST_PLUGIN_FILE ) ) . '/views/';
    }
    
    /*
     * Method to render provided templates and values
     */
    public function render( $fileInput, $values = NULL, $directory = '' ) {
	    
	    $directory = ( $directory ) ? $directory : $this->directory;
        $withExtensionFileInput = $fileInput . ".php";
		
		if ( file_exists( $directory . $withExtensionFileInput ) ) { 
        	include ( $directory . $withExtensionFileInput );
        }
    }
}
