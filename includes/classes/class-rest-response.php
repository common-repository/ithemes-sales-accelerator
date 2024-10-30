<?php
if ( !defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

/**
*	Extends WP_Rest_Response to clean output buffer beforehand
*/

class IT_REST_Response extends WP_REST_Response {

    public function __construct( $data = null, $status = 200, $headers = array() ) {
	    
        ob_clean();
        parent::__construct( $data, $status, $headers );
    }
}