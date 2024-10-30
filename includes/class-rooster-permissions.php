<?php
	
if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class manages user permissions
**/

class IT_RST_Permissions {
    
    // Singleton design pattern
    protected static $instance = NULL;
    protected $permissions = array();
    
    // Method to return the singleton instance
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct() {
	    global $it_rst_main_settings;
	    
	    $access_roles  	 = isset( $it_rst_main_settings['plugin_roles'] ) 			? explode( ',', $it_rst_main_settings['plugin_roles'] ) 		   : array();
		$access_users  	 = isset( $it_rst_main_settings['plugin_roles_exceptions']) ? explode( ',', $it_rst_main_settings['plugin_roles_exceptions'] ) : array();
		$access_roles[]  = 'administrator';
	    $permissions 	 = array( 'core' => array( 'roles' => $access_roles, 'users' => $access_users ) );
	    	    
	    $permissions = apply_filters( 'it_rst_available_permissions', $permissions );
	    $this->permissions = $permissions;
	}
	
	// Check if user checks one or multiple permissions
	public function check_permission( $permissions ) {
		if ( !is_array($permissions ) ) {
			$permissions = array( $permissions );
		}
						
		$has_permission = true;
		$user 	   		= wp_get_current_user();
		$roles 			= ( is_array( $user->roles ) && !empty( $user->roles ) ) ? $user->roles : array();
		
		foreach ( $permissions as $permission ) {
			if ( isset( $this->permissions[$permission] ) ) {

			    // Show settings only to users with selected roles
			    if ( ( empty( $this->permissions[ $permission ]['roles'] ) || $roles && array_intersect( $roles, $this->permissions[ $permission ]['roles'] ) ) && !in_array( $user->ID, $this->permissions[ $permission ]['users'] ) ) {
				}
				else {
					$has_permission = false;
					break;
				}
			}
			else {
				$has_permission = false;
				break;
			}
		}	
		return $has_permission;
	}
}