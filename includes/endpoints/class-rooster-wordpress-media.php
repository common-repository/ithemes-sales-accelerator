<?php

if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class provides endpoints to access wordpress media data
**/

class IT_RST_WordPress_Media {
    
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
	    
	    include_once( ABSPATH . 'wp-admin/includes/image.php' );
	    $this->server = new WP_REST_Server();
    }    
    
    // Get all users/Search users by parameters
    public function add_media_image( $request ) {
	    
	    $params = $request->get_params();
	    $image  = isset( $params['image'] ) ? $params['image'] : '';
	    $name   = isset( $params['name'] )  ? $params['name']  : '';
	    
	    if ( $image && $name ) {
		    $result = $this->uploadImage( $image, $name );
		    if ( $result ) {
		   		$media_array = $this->get_media_data( $result );
		   		return new IT_REST_Response( array( 'result' => true, 'data' => $media_array ), 200 );
		    }
		    else {
			    return new IT_REST_Response( array( 'result' => false, 'data' => array( 'error' => 'Invalid image' ) ), 200 );
		    }
	    }
	    else {
		    return new IT_REST_Response( array( 'result' => false, 'data' => array( 'error' => 'Image and name are required parameters' ) ), 200 );
	    }   
	}
	
	public function get_media_images( $request ) {
		
		$params = $request->get_params();
	    
	    ob_clean();
	    return $this->search_images( $request );	   
	}
	
	// Search users by parameters
	public function search_images( $request ) {
		
		// Get data from post
		$filter = $request->get_params();
		
		// Get single customer
		if ( isset( $filter['id'] ) ) {
			$images = $this->get_media_data( $filter['id'] );
		}
		else {
			// Search customers
			$query = $this->query_media( $filter );
			
			$images = array();

			foreach ( $query as $media ) {
				$images[] = $this->get_media_data( $media->ID );
			}
		}

		return $images;
	}
	
	// Query products by name
	private function query_media( $args = array() ) {

		$offset     = 0;
		$include    = '';
		$media_name = '';
		$per_page = 100;

		if( isset( $args['page'] ) ) {
			$offset = ($args['page'] - 1) * 10;
		}

		if( isset( $args['id'] ) && $args['id'] ){
			$include = $args['id'];
		}

		if ( isset( $args['search'] ) && $args['search'] ) {
			$media_name = $args['search'];
		}

		if ( isset( $args['per_page'] ) && $args['per_page'] ) {
			$per_page = $args['per_page'];
		}

		$meta_args = array(
			'posts_per_page'   => $per_page,
			'offset'           => $offset,
			'orderby'          => 'date',
			'include'          => $include,
			'order'            => 'DESC',
			'post_type'        => 'attachment',
			's'       		   => $media_name,
			'post_status'      => 'published',
			'suppress_filters' => true,
		);

		$media = get_posts( $meta_args );
		return $media;
	}
	
	// Get user data by id
	public function get_media_data( $id, $fields = null ) {
		
		$media    = get_post( $id );
		if ( $media->post_type == 'attachment' ) {
			$thumbnail = wp_get_attachment_thumb_url( $media->ID );
			$media_array = array( 'id' => $media->ID, 'guid' => $media->guid, 'thumbnail' => $thumbnail, 'name' => $media->post_title, 'slug' => $media->post_name, 'date_created' => $this->wc_rest_prepare_date_response( $media->post_date ), 'date_modified' => $this->wc_rest_prepare_date_response( $media->post_modified ) );
			return $media_array;
		}
		return array();
	}
	
	// Prepare date for rest response
	protected function wc_rest_prepare_date_response( $timestamp, $convert_to_utc = false ) {
		
		if ( $convert_to_utc ) {
			$timezone = new DateTimeZone( wc_timezone_string() );
		} else {
			$timezone = new DateTimeZone( 'UTC' );
		}

		try {
			if ( is_numeric( $timestamp ) ) {
				$date = new DateTime( "@{$timestamp}" );
			} else {
				$date = new DateTime( $timestamp, $timezone );
			}

			// convert to UTC by adjusting the time based on the offset of the site's timezone
			if ( $convert_to_utc ) {
				$date->modify( -1 * $date->getOffset() . ' seconds' );
			}

		} catch ( Exception $e ) {
			$date = new DateTime( '@0' );
		}

		return $date->format( 'Y-m-d\TH:i:s' );
	}
	
	// Delete Media by id
	public function delete_media_image( $request ) {
		
		$params = $request->get_params();
		$id 	= isset( $params['id'] ) ? $params['id'] : 0;
		if ( $id ) {
			if ( wp_delete_attachment( $id, true ) ){
				return new IT_REST_Response( array( 'result' => true, 'data' => array() ), 200 );
			}
		}
		return new IT_REST_Response( array( 'result' => false, 'data' => array() ), 200 );
	}
	
	public function uploadImage( $base64, $name ) {
	    if ( strlen( $base64 ) > 10 && base64_encode( base64_decode( $base64, true ) ) === $base64 ) {
		  $ending 		 = "jpg";
		  $temp 		 = base64_decode( $base64 );
	      $wp_upload_dir = wp_upload_dir();
	      $upload 		 = wp_upload_bits( "sales_accelerator_image_$name.$ending", null, $temp );
	      
	      if ( isset( $upload['error'] ) && $upload['error'] ) {
		      return array( 'result' => false, 'data' => array( 'error' => $upload['error'] ) );
	      }
	      else {
		      $wp_upload_dir = wp_upload_dir();
		      $attachment = array(
		        	'guid'           => $upload['url'],
		        	'post_mime_type' => "image/$ending",
		        	'post_title'     => "sales_accelerator_image_{$name}_{$ending}",
		        	'post_content'   => '',
		        	'post_status'    => 'inherit'
		      );
		        
		      $attach_id = wp_insert_attachment( $attachment, $upload['file'], 0 );
		      
		      if ( $attach_id ) {
			      $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	              $update = wp_update_attachment_metadata( $attach_id, $attach_data );
              }
			  return $attach_id;
	      }	    	
	   	}
	   	else {
		   	return false;
	   	}
    }
    
    public function get_image_id_by_url( $url ) {
	    
		global $wpdb;
		$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';" , $url ) ); 
        return $attachment[0]; 
	}
}