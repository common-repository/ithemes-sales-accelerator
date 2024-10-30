<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class provides endpoints to access wordpress user data
**/

class IT_RST_WordPress_Users {
    
    // Singleton design pattern
    protected static $instance = NULL;
    private $server;
    
    // Method to return the singleton instance
    public static function get_instance() {
        
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
	    
	    $this->server = new WP_REST_Server();
    }    
    
    // Get all users/Search users by parameters
    public function get_users( $request ) {
	    
	    $params = $request->get_params();
	    
	    ob_clean();
	    return $this->search_users( $request );	    
	}	
	
	// Search users by parameters
	public function search_users( $request ) {
		
		// Get data from post
		$filter = $request->get_params();
		
		// Get single customer
		if ( isset( $filter['id'] ) ) {
			$users = current( $this->get_user_data( $filter['id'] ) );
		}
		else {
			// Search customers
			$query = $this->query_users( $filter );
			
			$users = array();

			foreach ( $query->get_results() as $user_id ) {
				$users[] = current( $this->get_user_data( $user_id ) );
			}
		}

		return $users;
	}
	
	// Get user data by id
	public function get_user_data( $id, $fields = null ) {
		
		$user    = new WP_User( $id );
		return $user;
	}
	
	
	// Query users by parameters
	private function query_users( $args = array() ) {

		// default users per page
		$users_per_page = get_option( 'posts_per_page' );
		
		// Set base query arguments
		$query_args = array(
			'fields'  => 'ID',
			'orderby' => 'registered',
			'number'  => 10

			);

		// Custom Role
		if ( ! empty( $args['role'] ) ) {
			$roles_array = explode( ',', $args['role'] );
			$query_args['role__in'] = $roles_array;
		}

		// Search
		if (!empty( $args['search'] ) ) {

			if ( strpos($args['search'], ' ') !== false ) {
				$search_array 	   = explode( ' ', $args['search'] );
				$first_name_search = $search_array[0];
				$last_name_search  = $search_array[1];

				$query_args['search'] 	  = '*' . esc_attr( $args['search'] ) . '*';
				$query_args['meta_query'] = array(
				        'relation' => 'AND',
				        array(
				            'key'     => 'first_name',
				            'value'   => $first_name_search,
				            'compare' => 'LIKE',
				        ),
				        array(
				            'key'     => 'last_name',
				            'value'   => $last_name_search,
				            'compare' => 'LIKE',
				        ),
				);
			}
			else if ( strpos( $args['search'], '@' ) !== false ) {
				$query_args['search'] 		  = '*' . esc_attr( $args['search'] ) . '*';
				$query_args['search_columns'] = array( 'user_email' );
			}
			else {
				$query_args['search'] = '*'.esc_attr( $args['search'] ).'*';
				$query_args['meta_query'] = array(
				        'relation' => 'OR',
				        array(
				            'key'     => 'first_name',
				            'value'   => $args['search'],
				            'compare' => 'LIKE',
				        ),
				        array(
				            'key'     => 'last_name',
				            'value'   => $args['search'],
				            'compare' => 'LIKE',
				        ),
				        array(
				            'key' => 'description',
				            'value' => $args['search'] ,
				            'compare' => 'LIKE',
				        ),
				);
			}
		}

		// Limit number of users returned
		if ( ! empty( $args['limit'] ) ) {
			if ( $args['limit'] == -1 ) {
				unset( $query_args['number'] );
			} else {
				$query_args['number'] = absint( $args['limit'] );
				$users_per_page       = absint( $args['limit'] );
			}
		} else {
			$args['limit'] = $query_args['number'];
		}

		// Page
		$page = ( isset( $args['page'] ) ) ? absint( $args['page'] ) : 1;

		// Offset
		if ( ! empty( $args['offset'] ) ) {
			$query_args['offset'] = absint( $args['offset'] );
		} else {
			$query_args['offset'] = $users_per_page * ( $page - 1 );
		}

		// Created date
		if ( ! empty( $args['created_at_min'] ) ) {
			$this->created_at_min = $this->server->parse_datetime( $args['created_at_min'] );
		}

		if ( ! empty( $args['created_at_max'] ) ) {
			$this->created_at_max = $this->server->parse_datetime( $args['created_at_max'] );
		}

		// Order (ASC or DESC, ASC by default)
		if ( ! empty( $args['order'] ) ) {
			$query_args['order'] = $args['order'];
		}

		// Orderby
		if ( ! empty( $args['orderby'] ) ) {
			$query_args['orderby'] = $args['orderby'];

			// Allow sorting by meta value
			if ( ! empty( $args['orderby_meta_key'] ) ) {
				$query_args['meta_key'] = $args['orderby_meta_key'];
			}
		}
								
		$query = new WP_User_Query( $query_args );


		// Helper members for pagination headers
		$query->total_pages = ( $args['limit'] == -1 ) ? 1 : ceil( $query->get_total() / $users_per_page );
		$query->page = $page;

		return $query;
	}
}