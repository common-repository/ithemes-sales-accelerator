<?php

if ( !defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
* This class handles product creation/updates
**/

class RST_Reporting_Products {
	
	public function __construct() {
				
		// Run only if there's no initial importing running at the same time
		if( !get_option( 'it_rooster_reporting_is_importing' ) ) {
			add_action( 'save_post', array( $this, 'sync_single_product_to_db'), 10, 1 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'sync_var_product_to_db' ), 10, 1 );
		}
		
		// Deletes product from database before being deleted from WooCommerce
		add_action( 'before_delete_post', array( $this, 'delete_product_from_db' ), 10, 1 );				
	}
	
	public function add_product_cost_simple_product_save( $post_id ) {
		
		if ( isset( $_POST['_it_rooster_product_cost'] ) ) {
			update_post_meta( $post_id, '_it_rooster_product_cost', esc_attr( $_POST['_it_rooster_product_cost'] ) );
		}
	}
	
	public function add_product_cost_variable_product_save( $post_id, $loop ) {
		
		if ( isset( $_POST['_it_rooster_product_cost'][$post_id] ) ) {
			$sanitized_price = sanitize_text_field( $_POST['_it_rooster_product_cost'][ $post_id ] );
			update_post_meta( $post_id, '_it_rooster_product_cost', $sanitized_price );
		}
	}
	
	public function add_product_cost_variable_product( $loop, $variation_data, $variation ) {
		
		$product_cost = get_post_meta( $variation->ID, '_it_rooster_product_cost', TRUE );
		echo '<div class="options_group">';
			
		woocommerce_wp_text_input(
			array(
				'id'          => "_it_rooster_product_cost[$variation->ID]",
				'class'       => 'show_if_variable wc_input_price',
				'label'       => __( 'Product Cost', 'ithemes-sales-accelerator' ),
				'type'        => 'text', 
				'desc_tip'    => 'true',
				'description' => __( 'Enter product cost (if any).', 'ithemes-sales-accelerator' ),
				'value'       => $product_cost,
			)
		);
						
		echo '</div>';
	}
	
	public function add_product_cost_simple_product() {
		
		global $post;
		$product = new IT_WC_Product( $post->ID );
		
		if ( !$product->is_type( 'variable' ) ) {
			echo '<div class="options_group">';
							
			woocommerce_wp_text_input(
				array(
					'id'          => '_it_rooster_product_cost',
					'class'       => 'show_if_simple short wc_input_price',
					'label'       => __( 'Product Cost', 'ithemes-sales-accelerator' ),
					'type'        => 'text', 
					'desc_tip'    => 'true',
					'description' => __( 'Enter product cost (if any).', 'ithemes-sales-accelerator' ),
				)
			);
				
			echo '</div>';
		}
	}
	
	// Saves simple product to database
	public function sync_single_product_to_db( $post_id ) {
		
		$post = get_post( $post_id );
				
		if ($post->post_status != 'publish' || $post->post_type != 'product') {
	        return;
	    }
	    
	    $product 	   = new IT_WC_Product( $post );
	    $product_class = $product->get_product();
		    
	    if ( $product_class instanceof WC_Product_Simple ) {
		    $this->syncProduct( $product );
	    }
	    else if ( isset( $product_class ) && ( $product_class instanceof WC_Product_Variable || $product_class instanceof WC_Product_Variation ) ) {
			
            // For product variations we must check their childs
            $children_array = $product_class->get_children();
            if ( sizeof( $children_array ) > 0 ) {
                foreach ( $children_array as $children ) {
                    $children_product = new IT_WC_Product( $children );
                    $this->syncProduct( $children_product );
                }
            }
        }
        
        $queries_cache = new RST_Reporting_Queries_Cache();
	    $queries_cache->delete_products_transient( $product );
	}
	
	// Saves variable product to database
	public function sync_var_product_to_db( $post_id ) {
		
		$product	   = new IT_WC_Product( $post_id );
	    $product_class = $product->get_product();

        if ( isset( $product_class ) && ( $product_class instanceof WC_Product_Variable || $product_class instanceof WC_Product_Variation ) ) {
            $this->syncProduct( $product );
        }
        
        $queries_cache = new RST_Reporting_Queries_Cache();
	    $queries_cache->delete_products_transient( $product );
	}
	
	public function delete_product_from_db( $post_id ) {
		
		$post = get_post( $post_id );
		if ( $post->post_type != 'product' && $post->post_type != 'product_variation' ) {
	        return;
	    }
	
	    $product 	    = new IT_WC_Product( $post );
	    $this->deleteProduct( $product );

        // For product variations we must check their childs
        $children_array = $product->get_children();
        if ( sizeof( $children_array ) > 0 ) {
            foreach ( $children_array as $children ) {
                $children_product = IT_WC_Product( $children );
                $this->deleteProduct( $children_product );
            }
        }
	}
	
	public function deleteProduct ( $product ) {
		
		$product_id 		= $product->get_id();
		$products_database 	= new RST_Reporting_Products_Database();
		$products_database->delete( array( 'product_id' => $product_id ) );
	}
	
	/* 
    	Saves product data into custom database
    */  
    public function syncProduct( $product ) {
	    
	    try {			    
		    $products_database 	= new RST_Reporting_Products_Database();
		    $product_id 		= $product->get_id();			    
		    $sku 	  			= $product->get_sku();
		    $name 	  			= $product->get_name();
		    $image_id 			= $product->get_image_id();
		    $image 	  	  		= wp_get_attachment_url( $image_id );
		    
		    $product_data 		= array( 'product_id' => $product_id, 'sku' => $sku, 'name' => $name, 'image' => $image );
		    
		    $product_db = $products_database->get_by( array( 'product_id' => $product_id ) );
		    if ( !empty( $product_db ) && isset( $product_db[0] ) ) {
		    	$products_database->update( $product_data, array( 'product_id' => $product_id ) );
		    }
		    else {
		    	$products_database->insert( $product_data );
		    }				    
		    
		    update_post_meta( $product_id, 'it_rooster_reporting_imported', 1 );
			update_post_meta( $product_id, 'it_rooster_reporting_db_version', IT_RST_REPORTING_MODULE_DB_VERSION );
		    
		    return true;
	    }
	    catch ( Exception $e ) {
		    return false;
	    }
	    
	    return false;
    }
}