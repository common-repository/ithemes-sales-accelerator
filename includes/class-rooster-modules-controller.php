<?php

if ( !defined( 'ABSPATH' ) ) {
  	exit; // Exit if accessed directly
}

/**
* This class manages active modules and the modules page
**/

class IT_RST_Modules {
	
	private $viewRender;
	public function __construct() {}

	/**
	 * [handleRequest method that handles an incoming request (save or edit) ]
	 */
	public function handleRequest() {
		
		global $rst_modules;	
		
		$values		= array();		
		$modules 	= array();
		
		// Checks if form has been submited
		if ( isset( $_POST["submit"] ) ) {
			
			// Validates nounce
			if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'it_rooster_list_modules' ) ) {
				foreach ( $rst_modules as $module ) {
					if ( isset( $_POST[ $module['slug'] ] ) ){
						$modules[$module['slug']] = array( 'active' => $_POST[$module['slug']] );
					}
					else {
						$modules[$module['slug']] = array( 'active' => 0 );
					}
				}
				
				// Updates module status
				update_option( 'it_rooster_modules_status', $modules );
				$values['modules'] = $modules;
				$values['success'] = __( 'Saved with success', 'ithemes-sales-accelerator' );
			}
			// Invalid nounce
			else {
				$values['error']	= __( 'Invalid request!', 'ithemes-sales-accelerator' );
			}
		}
		else {
			$values['modules'] = get_option( 'it_rooster_modules_status' );
		}
		
		// Renders view
		$this->renderView( $values );
	}

	/**
	 * [renderView method renders the view for rooster add and edit controller]
	 * @param  [type] $values [values]
	 */
	public function renderView( $values = null ) {
		
		$this->viewRender = IT_View_Render::get_instance();
        $this->viewRender->render( "list-modules", $values );
	}
}