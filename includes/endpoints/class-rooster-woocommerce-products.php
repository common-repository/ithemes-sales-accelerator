<?php
	
if ( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
* This class provides endpoints to access product data
**/

class IT_RST_WooCommerce_Products {
    
    // Singleton design pattern
    protected static $instance = NULL;
    private $server;
    private $namespace = 'wc/v1';
    private $rest_base = 'products';
    
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
    
    // Get all products/Search products by parameters
    public function get_products( $request ) {
	    
	   	ob_clean();
		return $this->search_product( $request );
	}	   
	
	// Get product by id
	public function get_product( $request ) {
		
		ob_clean();
	    return $this->search_product( $request );
	}
	
	// Delete product by id
	public function delete_product( $request ) {
		
		$params = $request->get_params();
		$id 	= isset( $params['id'] ) ? $params['id'] : 0;
		if ( $id ) {
			if ( wp_delete_post( $id ) ){
				return true;	
			}
		}
		
		return false;
	}
	
	// Add product
	public function add_product( $request ) { 
		
		$params = $request->get_params();

		$id 		 	  = isset( $params['id'] ) 	   	       ? $params['id'] 			    : 0;				
		$name 		 	  = isset( $params['name'] ) 	   	   ? $params['name'] 			: '';
		$description 	  = isset( $params['description'] )    ? $params['description'] 	: '';
		$categories  	  = isset( $params['categories'] )     ? $params['categories'] 	    : array();
		$featured_image   = isset( $params['featured_image'] ) ? $params['featured_image']  : array();
		$gallery_images   = isset( $params['gallery_images'] ) ? $params['gallery_images']  : array();
		$sku              = isset( $params['sku'] ) 		   ? $params['sku'] 		    : '';
		$regular_price    = isset( $params['regular_price'] )  ? $params['regular_price'] 	: 0.00;
		$sale_price       = isset( $params['sale_price'] )     ? $params['sale_price'] 		: 0.00;
		$stock_managed    = isset( $params['stock_managed'] )  ? $params['stock_managed']   : false;
		$stock            = isset( $params['stock'] )          ? $params['stock'] 		    : 0;
		$weight           = isset( $params['weight'] )         ? $params['weight'] 		    : 0;
		$length           = isset( $params['length'] )         ? $params['length'] 		    : 0;
		$width            = isset( $params['width'] )          ? $params['width'] 		    : 0;
		$height           = isset( $params['height'] )         ? $params['height'] 		    : 0;
		$in_stock         = isset( $params['in_stock'] )       ? $params['in_stock'] 		: false;
		$shipping         = isset( $params['shipping'] )       ? $params['shipping'] 		: array();
		$enable_review    = isset( $params['enable_review'] )  ? $params['enable_review']   : true;
		$purchase_note    = isset( $params['purchase_note'] )  ? $params['purchase_note']   : '';
		
		if ( $name && is_numeric( $regular_price ) ) {
			
			$comment_status = ( $enable_review ) ? 'open'    : 'closed';
			
			if ( !$id ) {
			
			$product = array(
		         'post_title'     => $name,
		         'post_content'   => $description,
		         'post_status'    => 'publish',
		         'post_excerpt'   => '',
		         'post_name'      => $name,
		         'post_type'      => 'product',
		         'comment_status' => $comment_status,
	         );

		      //Create product/post:
		      $product_id = wp_insert_post( $product );
		      
		   }
		   else {
			   $product_id = $id;
			   
			   $product = array(
		          'ID'             => $product_id,
		          'post_title'     => $name,
		          'post_content'   => $description,
		          'comment_status' => $comment_status,
		      );
		
		      wp_update_post( $product );
		   }
		      
	       if ( $product_id ) {
		      
			  $manage_stock   = ( $stock_managed ) ? 'yes'     : 'no';
			  $stock_status   = ( $in_stock)       ? 'instock' : 'outofstock';
	      
		      update_post_meta( $product_id, '_sku', $sku );
		      update_post_meta( $product_id, '_price', $regular_price );
		      update_post_meta( $product_id, '_regular_price', $regular_price );
		      update_post_meta( $product_id, '_visibility', 'visible' );
		      update_post_meta( $product_id, '_manage_stock', $manage_stock );
		      update_post_meta( $product_id, '_stock_status', $stock_status );
		      update_post_meta( $product_id, '_downloadable', 'no' );
		      update_post_meta( $product_id, '_featured', 'no' );
		      update_post_meta( $product_id, '_virtual', 'no' );
		      update_post_meta( $product_id, '_backorders', 'no' );
		      update_post_meta( $product_id, '_purchase_note', $purchase_note );
		      update_post_meta( $product_id, '_weight', $weight );
		      update_post_meta( $product_id, '_width', $width );
		      update_post_meta( $product_id, '_height', $height );
		      update_post_meta( $product_id, '_length', $length );
		      
		      if ( isset( $shipping['weight'] ) ) {
			      update_post_meta( $product_id, '_weight', $shipping['weight'] );
		      }
		      if ( isset( $shipping['length'] ) ) {
			      update_post_meta( $product_id, '_length', $shipping['length'] );
		      }
		      if ( isset( $shipping['width'] ) ) {
			      update_post_meta( $product_id, '_width', $shipping['width'] );
		      }
		      if ( isset( $shipping['height'] ) ) {
			      update_post_meta( $product_id, '_height', $shipping['height'] );
		      }
		      
		      if ( $sale_price ) {
		      	  update_post_meta( $product_id, '_sale_price', $sale_price );
		      }
    
		      if ( is_array( $categories ) && $categories ) {
			      $category_ids = array();
			      foreach( $categories as $category ) {
				      $term = get_term_by( 'name', $category, 'product_cat', OBJECT );
				      if( $term && isset( $term->term_id ) && $term->term_id ) {
					      $category_ids[] = $term->term_id;
				      } 
				      else {
					    $cid = wp_insert_term(
					        $category,
					        'product_cat',
						     array(
						            'parent' => 0,
						    )
					    );
					    if ( isset( $cid['term_id'] ) ) {
					    	$category_ids[] = $cid['term_id'];
					    }
					 }
			      }
			      $result = wp_set_post_terms( $product_id, $category_ids, 'product_cat' );
			  }
			  
			  if ( isset( $shipping['shipping_class'] ) ) {
			      wp_set_post_terms( $product_id, array( $shipping['shipping_class'] ), 'product_shipping_class' );
			  }
			  
			  if ( $featured_image ) {
				  update_post_meta( $product_id, '_thumbnail_id', $featured_image );
			  }
			  
			  if ( $gallery_images ) {
				  $gallery_string = implode( ',', $gallery_images );
				  update_post_meta( $product_id, '_product_image_gallery', $gallery_string );
			  }
			  
			  if ( is_array( $stock ) ) {
				  do_action( 'it_rooster_warehouses_update_stock', $product_id, $stock );
			  }
			  else {
				  update_post_meta( $product_id, '_stock', $stock );
			  }
			  
		   }
		   return new IT_REST_Response( array( 'result' => true, 'data' => array( $product_id ) ), 200 );
		}
		else {
		      return new IT_REST_Response( array( 'result' => false, 'data' => array( ) ), 200 );
		}
	}
	
	// Get product categories
	public function get_product_categories( $request ) {
		
		$categories = $this->hierarchical_category_tree( 0 );
		return $categories;
	}
	
	public function hierarchical_category_tree( $parent ) {
	  
	  $categories_hierarchy = array();
	  $categories = get_categories( array(
  								'hide_empty'   => 'false',
  								'orderby' 	   => 'id',
  								'order' 	   => 'ASC',
  								'taxonomy'     => 'product_cat',
  								'parent'       => $parent,
  								'hierarchical' => 1,
  							)
  						);
  						
	  foreach ( $categories as $category ) {
		  
		  $children 			  = $this->get_term_children( $category->term_id );
		  $categories_hierarchy[] = array( 'id' => $category->term_id, 'name' => $category->name, 'slug' => $category->slug, 'children' => $children ); 
	  }
	  					
	  return $categories_hierarchy;
	}  
	
	public function get_term_children( $parent ) {
		
		$continue 	= false;
		$child_cats = array();
		
		$categories = get_categories( array(
								'hide_empty'   => 'false',
								'orderby' 	   => 'id',
								'order' 	   => 'ASC',
								'taxonomy'     => 'product_cat',
								'parent'       => $parent,
								'hierarchical' => 1,
							)
						);
						
		foreach( $categories as $category ) {
			$children 			  = $this->get_term_children( $category->term_id );
			$child_cats[] = array( 'id' => $category->term_id, 'name' => $category->name, 'slug' => $category->slug, 'children' => $children );
		}
		
		return $child_cats;
	}
	
	// Add a product category
	public function add_product_category( $request ) {
		
		$params = $request->get_params();
		$parent = isset( $params['parent'] ) ? $params['parent'] : 0;
		$name   = isset( $params['name'] )   ? $params['name']   : '';
		
		if ( $name ) {
			$cid = wp_insert_term(
		        $name,
		        'product_cat',
			     array(
			            'parent' => $parent,
			    )
		    );
		    if ( !( $cid instanceof WP_Error ) ) {
			    $term_id  = $cid['term_id'];
			    $category = get_term( $term_id, 'product_cat' );
			    if ( !( $category instanceof WP_Error ) ) {
				    return new IT_REST_Response( array( 'result' => true, 'data' => array( 'id' => $category->term_id, 'name' => $category->name, 'slug' => $category->slug, 'children' => array() ) ), 200 );
				}
				else {
					return new IT_REST_Response( array( 'result' => false, 'data' => array( $category ) ), 200 );
				}
			}
			else {
				return new IT_REST_Response( array( 'result' => false, 'data' => array( $cid ) ), 200 );
			}
		}
		
		return new IT_REST_Response( array( 'result' => false, 'data' => array() ), 200 );
	}
	
	// Delete product category
	public function delete_product_category ( $request) {
		$params = $request->get_params();
		$id 	= isset( $params['id'] ) ? $params['id'] : 0;
		if ( $id ) {
			return wp_delete_term( $id, 'product_cat' );
		}
		
		return false;
	}
	
	// Search products by name
	public function search_product( $request ) {
					
		$filter = $request->get_params();
		$products = array();
		
		// Get single product
		if ( isset( $filter['id'] ) ) {
			$product = get_post( $filter['id'] );
			if ( $product->post_type == 'product' || $product->post_type == 'product_variation' ) {
				$products = $this->prepare_product_for_response( $product, array() );
			}
		}
		else {
			// Search products
			$query = $this->query_products( $filter );
			$products = array();
		
			foreach ( $query as $product ) {
				$new_product = $this->prepare_product_for_response( $product, array() );
				$products[] = $new_product;
			}
		}

		return $products;
	}
	
	// Query products by name
	private function query_products( $args = array() ) {

		$offset = 0;
		$include = '';
		$product = '';

		if( isset( $args['page'] ) ) {
			$offset = ($args['page'] - 1) * 10;
		}

		if( isset( $args['id'] ) && $args['id'] ){
			$include = $args['id'];
		}

		if ( isset( $args['search'] ) && $args['search'] ) {
			$product = $args['search'];
		}
		if ( isset( $args['ids'] ) && $args['ids'] ) {
			$include = explode(',', $args['ids']);
		}

		$meta_args = array(
			'posts_per_page'   => 10,
			'offset'           => $offset,
			'orderby'          => 'date',
			'include'          => $include,
			'order'            => 'DESC',
			'post_type'        => 'product',
			's'       		   => $product,
			'post_status'      => 'published',
			'post__in'		   => $post_in,
			'suppress_filters' => true,
		);

		$products = get_posts( $meta_args );
		return $products;
	}	
	
	// Prepare product for rest response
	public function prepare_product_for_response( $post, $request ) {
		$product = new IT_WC_Product( $post );	
		$data    = $this->get_product_data( $product );

		// Add variations to variable products.
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			$data['variations'] = $this->get_variation_data( $product );
		}

		// Add grouped products data.
		if ( $product->is_type( 'grouped' ) && $product->has_child() ) {
			$data['grouped_products'] = $product->get_children();
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $product ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
		 * prepared for the response.
		 *
		 * @param WP_REST_Response   $response   The response object.
		 * @param WP_Post            $post       Post object.
		 * @param WP_REST_Request    $request    Request object.
		 */
		 
		//return $data;
		return apply_filters( "woocommerce_api_product_response", $response->data, $post, $request );
	}
	
	// Get product data for rest response
	protected function get_product_data( $product ) {
				
		$data = array(
			'id'                    => (int) $product->is_type( 'variation' ) ? $product->get_variation_id() : $product->get_id(),
			'name'                  => $product->get_title(),
			'slug'                  => $product->get_post_data()->post_name,
			'permalink'             => $product->get_permalink(),
			'date_created'          => $this->wc_rest_prepare_date_response( $product->get_post_data()->post_date_gmt ),
			'date_modified'         => $this->wc_rest_prepare_date_response( $product->get_post_data()->post_modified_gmt ),
			'type'                  => $product->get_product_type(),
			'status'                => $product->get_post_data()->post_status,
			'featured'              => $product->is_featured(),
			'catalog_visibility'    => $product->get_visibility(),
			'description'           => wpautop( do_shortcode( $product->get_post_data()->post_content ) ),
			'short_description'     => apply_filters( 'woocommerce_short_description', $product->get_post_data()->post_excerpt ),
			'sku'                   => $product->get_sku(),
			'price'                 => $product->get_price(),
			'regular_price'         => $product->get_regular_price(),
			'sale_price'            => $product->get_sale_price() ? $product->get_sale_price() : '',
			'date_on_sale_from'     => $product->get_sale_price_dates_from() ? date( 'Y-m-d', $product->get_sale_price_dates_from() ) : '',
			'date_on_sale_to'       => $product->get_sale_price_dates_to() ? date( 'Y-m-d', $product->get_sale_price_dates_to() ) : '',
			'price_html'            => $product->get_price_html(),
			'on_sale'               => $product->is_on_sale(),
			'purchasable'           => $product->is_purchasable(),
			'total_sales'           => (int) get_post_meta( $product->get_id(), 'total_sales', true ),
			'virtual'               => $product->is_virtual(),
			'downloadable'          => $product->is_downloadable(),
			'downloads'             => $this->get_downloads( $product ),
			'download_limit'        => '' !== $product->get_download_limit() ? (int) $product->get_download_limit() : -1,
			'download_expiry'       => '' !== $product->get_download_expiry() ? (int) $product->get_download_expiry() : -1,
			'download_type'         => $product->get_download_type() ? $product->get_download_type() : 'standard',
			'external_url'          => $product->is_type( 'external' ) ? $product->get_product_url() : '',
			'button_text'           => $product->is_type( 'external' ) ? $product->get_button_text() : '',
			'tax_status'            => $product->get_tax_status(),
			'tax_class'             => $product->get_tax_class(),
			'manage_stock'          => $product->managing_stock(),
			'stock_quantity'        => $product->get_stock_quantity(),
			'in_stock'              => $product->is_in_stock(),
			'backorders'            => $product->get_backorders(),
			'backorders_allowed'    => $product->backorders_allowed(),
			'backordered'           => $product->get_product()->is_on_backorder(),
			'sold_individually'     => $product->is_sold_individually(),
			'weight'                => $product->get_weight(),
			'dimensions'            => array(
				'length' => $product->get_length(),
				'width'  => $product->get_width(),
				'height' => $product->get_height(),
			),
			'shipping_required'     => $product->needs_shipping(),
			'shipping_taxable'      => $product->is_shipping_taxable(),
			'shipping_class'        => $product->get_shipping_class(),
			'shipping_class_id'     => (int) $product->get_shipping_class_id(),
			'reviews_allowed'       => ( 'open' === $product->get_post_data()->comment_status ),
			'average_rating'        => wc_format_decimal( $product->get_average_rating(), 2 ),
			'rating_count'          => (int) @$product->get_rating_count(),
			'related_ids'           => array_map( 'absint', array_values( $product->get_related() ) ),
			'upsell_ids'            => array_map( 'absint', $product->get_upsells() ),
			'cross_sell_ids'        => array_map( 'absint', $product->get_cross_sells() ),
			'parent_id'             => $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_post_data()->post_parent,
			'purchase_note'         => wpautop( do_shortcode( wp_kses_post( $product->get_purchase_note() ) ) ),
			'categories'            => $this->get_taxonomy_terms( $product ),
			'tags'                  => $this->get_taxonomy_terms( $product, 'tag' ),
			'images'                => $this->get_images( $product ),
			'attributes'            => @$this->get_attributes( $product ),
			'default_attributes'    => $this->get_default_attributes( $product ),
			'variations'            => array(),
			'grouped_products'      => array(),
			'menu_order'            => $this->get_product_menu_order( $product ),
			'featured_src'       	=> (string) wp_get_attachment_url( get_post_thumbnail_id( $product->is_type( 'variation' ) ? $product->get_variation_id() : $product->get_id() ) ),
			'featured_media'       	=> $this->get_featured_image( get_post_thumbnail_id( $product->is_type( 'variation' ) ? $product->get_variation_id() : $product->get_id() ) ),
		);

		return $data;
	}
	
	/**
	 * Add the values from additional fields to a data object.
	 *
	 * @param array  $object
	 * @param WP_REST_Request $request
	 * @return array modified object with additional fields.
	 */
	protected function add_additional_fields_to_object( $object, $request ) {

		$additional_fields = $this->get_additional_fields();
		foreach ( $additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['get_callback'] ) {
				continue;
			}

			$object[ $field_name ] = call_user_func( $field_options['get_callback'], $object, $field_name, $request, $this->get_object_type() );
		}

		return $object;
	}
	
	/**
	 * Get all the registered additional fields for a given object-type.
	 *
	 * @param  string $object_type
	 * @return array
	 */
	protected function get_additional_fields( $object_type = null ) {

		if ( ! $object_type ) {
			$object_type = $this->get_object_type();
		}

		if ( ! $object_type ) {
			return array();
		}

		global $wp_rest_additional_fields;

		if ( ! $wp_rest_additional_fields || ! isset( $wp_rest_additional_fields[ $object_type ] ) ) {
			return array();
		}

		return $wp_rest_additional_fields[ $object_type ];
	}
	
	/**
	 * Get the object type this controller is responsible for managing.
	 *
	 * @return string
	 */
	protected function get_object_type() {
		$schema = $this->get_item_schema();

		if ( ! $schema || ! isset( $schema['title'] ) ) {
			return null;
		}

		return $schema['title'];
	}
	
	/**
	 * Get the Product's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$weight_unit    = get_option( 'woocommerce_weight_unit' );
		$dimension_unit = get_option( 'woocommerce_dimension_unit' );
		$schema         = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'product',
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name' => array(
					'description' => __( 'Product name.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'slug' => array(
					'description' => __( 'Product slug.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'permalink' => array(
					'description' => __( 'Product URL.', 'woocommerce' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created' => array(
					'description' => __( "The date the product was created, in the site's timezone.", 'woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_modified' => array(
					'description' => __( "The date the product was last modified, in the site's timezone.", 'woocommerce' ),
					'type'        => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'type' => array(
					'description' => __( 'Product type.', 'woocommerce' ),
					'type'        => 'string',
					'default'     => 'simple',
					'enum'        => array_keys( wc_get_product_types() ),
					'context'     => array( 'view', 'edit' ),
				),
				'status' => array(
					'description' => __( 'Product status (post status).', 'woocommerce' ),
					'type'        => 'string',
					'default'     => 'publish',
					'enum'        => array_keys( get_post_statuses() ),
					'context'     => array( 'view', 'edit' ),
				),
				'featured' => array(
					'description' => __( 'Featured product.', 'woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'catalog_visibility' => array(
					'description' => __( 'Catalog visibility.', 'woocommerce' ),
					'type'        => 'string',
					'default'     => 'visible',
					'enum'        => array( 'visible', 'catalog', 'search', 'hidden' ),
					'context'     => array( 'view', 'edit' ),
				),
				'description' => array(
					'description' => __( 'Product description.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'short_description' => array(
					'description' => __( 'Product short description.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'sku' => array(
					'description' => __( 'Unique identifier.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'price' => array(
					'description' => __( 'Current product price.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'regular_price' => array(
					'description' => __( 'Product regular price.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'sale_price' => array(
					'description' => __( 'Product sale price.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'date_on_sale_from' => array(
					'description' => __( 'Start date of sale price.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'date_on_sale_to' => array(
					'description' => __( 'End data of sale price.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'price_html' => array(
					'description' => __( 'Price formatted in HTML.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'on_sale' => array(
					'description' => __( 'Shows if the product is on sale.', 'woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'purchasable' => array(
					'description' => __( 'Shows if the product can be bought.', 'woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'total_sales' => array(
					'description' => __( 'Amount of sales.', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'virtual' => array(
					'description' => __( 'If the product is virtual.', 'woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'downloadable' => array(
					'description' => __( 'If the product is downloadable.', 'woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'downloads' => array(
					'description' => __( 'List of downloadable files.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id' => array(
							'description' => __( 'File MD5 hash.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'name' => array(
							'description' => __( 'File name.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'file' => array(
							'description' => __( 'File URL.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'download_limit' => array(
					'description' => __( 'Amount of times the product can be downloaded.', 'woocommerce' ),
					'type'        => 'integer',
					'default'     => -1,
					'context'     => array( 'view', 'edit' ),
				),
				'download_expiry' => array(
					'description' => __( 'Number of days that the customer has up to be able to download the product.', 'woocommerce' ),
					'type'        => 'integer',
					'default'     => -1,
					'context'     => array( 'view', 'edit' ),
				),
				'download_type' => array(
					'description' => __( 'Download type, this controls the schema on the front-end.', 'woocommerce' ),
					'type'        => 'string',
					'default'     => 'standard',
					'enum'        => array( 'standard', 'application', 'music' ),
					'context'     => array( 'view', 'edit' ),
				),
				'external_url' => array(
					'description' => __( 'Product external URL. Only for external products.', 'woocommerce' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
				),
				'button_text' => array(
					'description' => __( 'Product external button text. Only for external products.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'tax_status' => array(
					'description' => __( 'Tax status.', 'woocommerce' ),
					'type'        => 'string',
					'default'     => 'taxable',
					'enum'        => array( 'taxable', 'shipping', 'none' ),
					'context'     => array( 'view', 'edit' ),
				),
				'tax_class' => array(
					'description' => __( 'Tax class.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'manage_stock' => array(
					'description' => __( 'Stock management at product level.', 'woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'stock_quantity' => array(
					'description' => __( 'Stock quantity.', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'in_stock' => array(
					'description' => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce' ),
					'type'        => 'boolean',
					'default'     => true,
					'context'     => array( 'view', 'edit' ),
				),
				'backorders' => array(
					'description' => __( 'If managing stock, this controls if backorders are allowed.', 'woocommerce' ),
					'type'        => 'string',
					'default'     => 'no',
					'enum'        => array( 'no', 'notify', 'yes' ),
					'context'     => array( 'view', 'edit' ),
				),
				'backorders_allowed' => array(
					'description' => __( 'Shows if backorders are allowed.', 'woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'backordered' => array(
					'description' => __( 'Shows if the product is on backordered.', 'woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'sold_individually' => array(
					'description' => __( 'Allow one item to be bought in a single order.', 'woocommerce' ),
					'type'        => 'boolean',
					'default'     => false,
					'context'     => array( 'view', 'edit' ),
				),
				'weight' => array(
					'description' => sprintf( __( 'Product weight (%s).', 'woocommerce' ), $weight_unit ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'dimensions' => array(
					'description' => __( 'Product dimensions.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'length' => array(
							'description' => sprintf( __( 'Product length (%s).', 'woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'width' => array(
							'description' => sprintf( __( 'Product width (%s).', 'woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'height' => array(
							'description' => sprintf( __( 'Product height (%s).', 'woocommerce' ), $dimension_unit ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'shipping_required' => array(
					'description' => __( 'Shows if the product need to be shipped.', 'woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'shipping_taxable' => array(
					'description' => __( 'Shows whether or not the product shipping is taxable.', 'woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'shipping_class' => array(
					'description' => __( 'Shipping class slug.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'shipping_class_id' => array(
					'description' => __( 'Shipping class ID.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'reviews_allowed' => array(
					'description' => __( 'Allow reviews.', 'woocommerce' ),
					'type'        => 'boolean',
					'default'     => true,
					'context'     => array( 'view', 'edit' ),
				),
				'average_rating' => array(
					'description' => __( 'Reviews average rating.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'rating_count' => array(
					'description' => __( 'Amount of reviews that the product have.', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'related_ids' => array(
					'description' => __( 'List of related products IDs.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'upsell_ids' => array(
					'description' => __( 'List of up-sell products IDs.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
				),
				'cross_sell_ids' => array(
					'description' => __( 'List of cross-sell products IDs.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
				),
				'parent_id' => array(
					'description' => __( 'Product parent ID.', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'purchase_note' => array(
					'description' => __( 'Optional note to send the customer after purchase.', 'woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'categories' => array(
					'description' => __( 'List of categories.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id' => array(
							'description' => __( 'Category ID.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'name' => array(
							'description' => __( 'Category name.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'slug' => array(
							'description' => __( 'Category slug.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'tags' => array(
					'description' => __( 'List of tags.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id' => array(
							'description' => __( 'Tag ID.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'name' => array(
							'description' => __( 'Tag name.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'slug' => array(
							'description' => __( 'Tag slug.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'images' => array(
					'description' => __( 'List of images.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id' => array(
							'description' => __( 'Image ID.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'date_created' => array(
							'description' => __( "The date the image was created, in the site's timezone.", 'woocommerce' ),
							'type'        => 'date-time',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'date_modified' => array(
							'description' => __( "The date the image was last modified, in the site's timezone.", 'woocommerce' ),
							'type'        => 'date-time',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'src' => array(
							'description' => __( 'Image URL.', 'woocommerce' ),
							'type'        => 'string',
							'format'      => 'uri',
							'context'     => array( 'view', 'edit' ),
						),
						'name' => array(
							'description' => __( 'Image name.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'alt' => array(
							'description' => __( 'Image alternative text.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'position' => array(
							'description' => __( 'Image position. 0 means that the image is featured.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'attributes' => array(
					'description' => __( 'List of attributes.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id' => array(
							'description' => __( 'Attribute ID.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'name' => array(
							'description' => __( 'Attribute name.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'position' => array(
							'description' => __( 'Attribute position.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'visible' => array(
							'description' => __( "Define if the attribute is visible on the \"Additional Information\" tab in the product's page.", 'woocommerce' ),
							'type'        => 'boolean',
							'default'     => false,
							'context'     => array( 'view', 'edit' ),
						),
						'variation' => array(
							'description' => __( 'Define if the attribute can be used as variation.', 'woocommerce' ),
							'type'        => 'boolean',
							'default'     => false,
							'context'     => array( 'view', 'edit' ),
						),
						'options' => array(
							'description' => __( 'List of available term names of the attribute.', 'woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'default_attributes' => array(
					'description' => __( 'Defaults variation attributes.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id' => array(
							'description' => __( 'Attribute ID.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'name' => array(
							'description' => __( 'Attribute name.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'option' => array(
							'description' => __( 'Selected attribute term name.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
				'variations' => array(
					'description' => __( 'List of variations.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'id' => array(
							'description' => __( 'Variation ID.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'date_created' => array(
							'description' => __( "The date the variation was created, in the site's timezone.", 'woocommerce' ),
							'type'        => 'date-time',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'date_modified' => array(
							'description' => __( "The date the variation was last modified, in the site's timezone.", 'woocommerce' ),
							'type'        => 'date-time',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'permalink' => array(
							'description' => __( 'Variation URL.', 'woocommerce' ),
							'type'        => 'string',
							'format'      => 'uri',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'sku' => array(
							'description' => __( 'Unique identifier.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'price' => array(
							'description' => __( 'Current variation price.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'regular_price' => array(
							'description' => __( 'Variation regular price.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'sale_price' => array(
							'description' => __( 'Variation sale price.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'date_on_sale_from' => array(
							'description' => __( 'Start date of sale price.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'date_on_sale_to' => array(
							'description' => __( 'End data of sale price.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'on_sale' => array(
							'description' => __( 'Shows if the variation is on sale.', 'woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'purchasable' => array(
							'description' => __( 'Shows if the variation can be bought.', 'woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'visible' => array(
							'description' => __( 'If the variation is visible.', 'woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'virtual' => array(
							'description' => __( 'If the variation is virtual.', 'woocommerce' ),
							'type'        => 'boolean',
							'default'     => false,
							'context'     => array( 'view', 'edit' ),
						),
						'downloadable' => array(
							'description' => __( 'If the variation is downloadable.', 'woocommerce' ),
							'type'        => 'boolean',
							'default'     => false,
							'context'     => array( 'view', 'edit' ),
						),
						'downloads' => array(
							'description' => __( 'List of downloadable files.', 'woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'properties'  => array(
								'id' => array(
									'description' => __( 'File MD5 hash.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
								'name' => array(
									'description' => __( 'File name.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
								'file' => array(
									'description' => __( 'File URL.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
							),
						),
						'download_limit' => array(
							'description' => __( 'Amount of times the variation can be downloaded.', 'woocommerce' ),
							'type'        => 'integer',
							'default'     => null,
							'context'     => array( 'view', 'edit' ),
						),
						'download_expiry' => array(
							'description' => __( 'Number of days that the customer has up to be able to download the variation.', 'woocommerce' ),
							'type'        => 'integer',
							'default'     => null,
							'context'     => array( 'view', 'edit' ),
						),
						'tax_status' => array(
							'description' => __( 'Tax status.', 'woocommerce' ),
							'type'        => 'string',
							'default'     => 'taxable',
							'enum'        => array( 'taxable', 'shipping', 'none' ),
							'context'     => array( 'view', 'edit' ),
						),
						'tax_class' => array(
							'description' => __( 'Tax class.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'manage_stock' => array(
							'description' => __( 'Stock management at variation level.', 'woocommerce' ),
							'type'        => 'boolean',
							'default'     => false,
							'context'     => array( 'view', 'edit' ),
						),
						'stock_quantity' => array(
							'description' => __( 'Stock quantity.', 'woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
						),
						'in_stock' => array(
							'description' => __( 'Controls whether or not the variation is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce' ),
							'type'        => 'boolean',
							'default'     => true,
							'context'     => array( 'view', 'edit' ),
						),
						'backorders' => array(
							'description' => __( 'If managing stock, this controls if backorders are allowed.', 'woocommerce' ),
							'type'        => 'string',
							'default'     => 'no',
							'enum'        => array( 'no', 'notify', 'yes' ),
							'context'     => array( 'view', 'edit' ),
						),
						'backorders_allowed' => array(
							'description' => __( 'Shows if backorders are allowed.', 'woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'backordered' => array(
							'description' => __( 'Shows if the variation is on backordered.', 'woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'weight' => array(
							'description' => sprintf( __( 'Variation weight (%s).', 'woocommerce' ), $weight_unit ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'dimensions' => array(
							'description' => __( 'Variation dimensions.', 'woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'properties'  => array(
								'length' => array(
									'description' => sprintf( __( 'Variation length (%s).', 'woocommerce' ), $dimension_unit ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
								'width' => array(
									'description' => sprintf( __( 'Variation width (%s).', 'woocommerce' ), $dimension_unit ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
								'height' => array(
									'description' => sprintf( __( 'Variation height (%s).', 'woocommerce' ), $dimension_unit ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
							),
						),
						'shipping_class' => array(
							'description' => __( 'Shipping class slug.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
						),
						'shipping_class_id' => array(
							'description' => __( 'Shipping class ID.', 'woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'image' => array(
							'description' => __( 'Variation image data.', 'woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'properties'  => array(
								'id' => array(
									'description' => __( 'Image ID.', 'woocommerce' ),
									'type'        => 'integer',
									'context'     => array( 'view', 'edit' ),
								),
								'date_created' => array(
									'description' => __( "The date the image was created, in the site's timezone.", 'woocommerce' ),
									'type'        => 'date-time',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
								'date_modified' => array(
									'description' => __( "The date the image was last modified, in the site's timezone.", 'woocommerce' ),
									'type'        => 'date-time',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
								'src' => array(
									'description' => __( 'Image URL.', 'woocommerce' ),
									'type'        => 'string',
									'format'      => 'uri',
									'context'     => array( 'view', 'edit' ),
								),
								'name' => array(
									'description' => __( 'Image name.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
								'alt' => array(
									'description' => __( 'Image alternative text.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
								'position' => array(
									'description' => __( 'Image position. 0 means that the image is featured.', 'woocommerce' ),
									'type'        => 'integer',
									'context'     => array( 'view', 'edit' ),
								),
							),
						),
						'attributes' => array(
							'description' => __( 'List of attributes.', 'woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'properties'  => array(
								'id' => array(
									'description' => __( 'Attribute ID.', 'woocommerce' ),
									'type'        => 'integer',
									'context'     => array( 'view', 'edit' ),
								),
								'name' => array(
									'description' => __( 'Attribute name.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
								'option' => array(
									'description' => __( 'Selected attribute term name.', 'woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit' ),
								),
							),
						),
					),
				),
				'grouped_products' => array(
					'description' => __( 'List of grouped products ID.', 'woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'menu_order' => array(
					'description' => __( 'Menu order, used to custom sort products.', 'woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}
	
	/**
	 * Add the schema from additional fields to an schema array.
	 *
	 * The type of object is inferred from the passed schema.
	 *
	 * @param array $schema Schema array.
	 */
	protected function add_additional_fields_schema( $schema ) {
		
		if ( empty( $schema['title'] ) ) {
			return $schema;
		}
	}
	
	/**
	 * Filter a response based on the context defined in the schema.
	 *
	 * @param array $data
	 * @param string $context
	 * @return array
	 */
	public function filter_response_by_context( $data, $context ) {

		$schema = $this->get_item_schema();
		foreach ( $data as $key => $value ) {
			if ( empty( $schema['properties'][ $key ] ) || empty( $schema['properties'][ $key ]['context'] ) ) {
				continue;
			}

			if ( ! in_array( $context, $schema['properties'][ $key ]['context'] ) ) {
				unset( $data[ $key ] );
			}

			if ( 'object' === $schema['properties'][ $key ]['type'] && ! empty( $schema['properties'][ $key ]['properties'] ) ) {
				foreach ( $schema['properties'][ $key ]['properties'] as $attribute => $details ) {
					if ( empty( $details['context'] ) ) {
						continue;
					}
					if ( ! in_array( $context, $details['context'] ) ) {
						if ( isset( $data[ $key ][ $attribute ] ) ) {
							unset( $data[ $key ][ $attribute ] );
						}
					}
				}
			}
		}

		return $data;
	}
	
	/**
	 * Prepare links for the request.
	 *
	 * @param WC_Product $product Product object.
	 * @return array Links for the given product.
	 */
	protected function prepare_links( $product ) {
		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $product->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( $product->is_type( 'variation' ) && $product->parent ) {
			$links['up'] = array(
				'href' => rest_url( sprintf( '/%s/products/%d', $this->namespace, $product->parent->id ) ),
			);
		} elseif ( $product->is_type( 'simple' ) && ! empty( $product->post->post_parent ) ) {
			$links['up'] = array(
				'href' => rest_url( sprintf( '/%s/products/%d', $this->namespace, $product->post->post_parent ) ),
			);
		}

		return $links;
	}
	
	// Get product downloads
	protected function get_downloads( $product ) {
		
		$downloads = array();

		if ( $product->is_downloadable() ) {
			foreach ( $product->get_files() as $file_id => $file ) {
				$downloads[] = array(
					'id'   => $file_id, // MD5 hash.
					'name' => $file['name'],
					'file' => $file['file'],
				);
			}
		}

		return $downloads;
	}
	
	// Get product taxonomy terms
	protected function get_taxonomy_terms( $product, $taxonomy = 'cat' ) {
		
		$terms = array();

		foreach ( wp_get_post_terms( $product->get_id(), 'product_' . $taxonomy ) as $term ) {
			$terms[] = array ("id" => $term->term_id , "name" => $term->name, "slug" => $term->slug);
		}

		return $terms;
	}
	
	// Get product atributes
	protected function get_attributes( $product ) {
		
		$attributes = array();

		if ( $product->is_type( 'variation' ) ) {
			// Variation attributes.
			
			foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
				$name = str_replace( 'attribute_', '', $attribute_name );
				
				// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
				if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
					$attributes[] = array(
						'id'     => $this->wc_attribute_taxonomy_id_by_name( $name ),
						'name'   => $this->get_attribute_taxonomy_label( $name ),
						'option' => $attribute,
					);
				} else {
					$attributes[] = array(
						'id'     => 0,
						'name'   => str_replace( 'pa_', '', $name ),
						'option' => $attribute,
					);
				}
			}
		} else {
			foreach ( $product->get_attributes() as $attribute ) {
				// Taxonomy-based attributes are comma-separated, others are pipe (|) separated.
				if ( $attribute['is_taxonomy'] ) {
					$attributes[] = array(
						'id'        => $attribute['is_taxonomy'] ? $this->wc_attribute_taxonomy_id_by_name( $attribute['name'] ) : 0,
						'name'      => $this->get_attribute_taxonomy_label( $attribute['name'] ),
						'position'  => (int) $attribute['position'],
						'visible'   => (bool) $attribute['is_visible'],
						'variation' => (bool) $attribute['is_variation'],
						'options'   => array_map( 'trim', explode( ',', $product->get_attribute( $attribute['name'] ) ) ),
					);
				} else {
					$attributes[] = array(
						'id'        => 0,
						'name'      => str_replace( 'pa_', '', $attribute['name'] ),
						'position'  => (int) $attribute['position'],
						'visible'   => (bool) $attribute['is_visible'],
						'variation' => (bool) $attribute['is_variation'],
						'options'   => array_map( 'trim', explode( '|', $product->get_attribute( $attribute['name'] ) ) ),
					);
				}
			}
		}

		return $attributes;
	}
	
	// Get taxonomy attribute label
	protected function get_attribute_taxonomy_label( $name ) {
		
		$tax    = get_taxonomy( $name );
		$labels = get_taxonomy_labels( $tax );

		return $labels->singular_name;
	}

	// Get attribute taxonomy id by name
	protected function wc_attribute_taxonomy_id_by_name( $name ) {
		
		$name       = str_replace( 'pa_', '', $name );
		$taxonomies = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_id', 'attribute_name' );

		return isset( $taxonomies[ $name ] ) ? (int) $taxonomies[ $name ] : 0;
	}
	
	// Get product default attributes
	protected function get_default_attributes( $product ) {
		
		$default = array();

		if ( $product->is_type( 'variable' ) ) {
			foreach ( (array) get_post_meta( $product->get_id(), '_default_attributes', true ) as $key => $value ) {
				if ( 0 === strpos( $key, 'pa_' ) ) {
					$default[] = array(
						'id'     => $this->wc_attribute_taxonomy_id_by_name( $key ),
						'name'   => $this->get_attribute_taxonomy_label( $key ),
						'option' => $value,
					);
				} else {
					$default[] = array(
						'id'     => 0,
						'name'   => str_replace( 'pa_', '', $key ),
						'option' => $value,
					);
				}
			}
		}

		return $default;
	}
	
	// Get product menu order
	protected function get_product_menu_order( $product ) {
		
		$menu_order = $product->get_post_data()->menu_order;

		if ( $product->is_type( 'variation' ) ) {
			$variation  = get_post( $product->get_variation_id() );
			$menu_order = $variation->menu_order;
		}

		return $menu_order;
	}
	
	public function get_featured_image( $attachment_id ) {
		$attachment_post = get_post( $attachment_id );
		if ( $attachment_post->post_type == 'attachment' ) {
			$thumbnail = wp_get_attachment_thumb_url( $attachment_id, 'full' );
			$image = array(
					'id'            => (int) $attachment_id,
					'date_created'  => $this->wc_rest_prepare_date_response( $attachment_post->post_date ),
					'date_modified' => $this->wc_rest_prepare_date_response( $attachment_post->post_modified ),
					'guid'          => $attachment_post->guid,
					'thumbnail'     => $thumbnail,
					'name'          => $attachment_post->post_title,
					'slug' 			=> $attachment_post->post_name,
					'alt'           => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true )
			);
			
			return $image;
		}
		return array();
	}
	
	// Get product images
	protected function get_images( $product ) {
		
		$images = array();
		$attachment_ids = array();

		if ( $product->is_type( 'variation' ) ) {
			if ( has_post_thumbnail( $product->get_variation_id() ) ) {
				// Add variation image if set.
				$attachment_ids[] = get_post_thumbnail_id( $product->get_variation_id() );
			} elseif ( has_post_thumbnail( $product->get_id() ) ) {
				// Otherwise use the parent product featured image if set.
				$attachment_ids[] = get_post_thumbnail_id( $product->get_id() );
			}
		} else {
			// Add featured image.
			if ( has_post_thumbnail( $product->get_id() ) ) {
				$attachment_ids[] = get_post_thumbnail_id( $product->get_id() );
			}
			// Add gallery images.
			$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_attachment_ids() );
		}

		// Build image data.
		foreach ( $attachment_ids as $position => $attachment_id ) {
			$attachment_post = get_post( $attachment_id );
			if ( is_null( $attachment_post ) ) {
				continue;
			}

			$thumbnail = wp_get_attachment_thumb_url( $attachment_id, 'full' );

			$images[] = array(
				'id'            => $attachment_id,
				'date_created'  => $this->wc_rest_prepare_date_response( $attachment_post->post_date ),
				'date_modified' => $this->wc_rest_prepare_date_response( $attachment_post->post_modified ),
				'guid'          => $attachment_post->guid,
				'thumbnail'     => $thumbnail,
				'name'          => $attachment_post->post_title,
				'slug' 			=> $attachment_post->post_name,
				'alt'           => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'position'      => (int) $position,
			);
		}
		
		return $images;
	}
	
	/**
	 * Get the placeholder image URL for products etc.
	 *
	 * @access public
	 * @return string
	 */
	public function wc_placeholder_img_src() {
		return apply_filters( 'woocommerce_placeholder_img_src', WC()->plugin_url() . '/assets/images/placeholder.png' );
	}

	/**
	 * Get an individual variation's data.
	 *
	 * @param WC_Product $product
	 * @return array
	 */
	protected function get_variation_data( $product ) {
		$variations = array();

		foreach ( $product->get_children() as $child_id ) {
			$variation = new IT_WC_Product( $child_id );
			$variation_obj = $product->get_child( $child_id );
						
			if ( ! $variation_obj->exists() ) {
				continue;
			}

			$post_data = get_post( $variation->get_variation_id() );

			$variations[] = array(
				'id'                 => $variation->get_variation_id(),
				'date_created'       => $this->wc_rest_prepare_date_response( $post_data->post_date_gmt ),
				'date_modified'      => $this->wc_rest_prepare_date_response( $post_data->post_modified_gmt ),
				'permalink'          => $variation->get_permalink(),
				'sku'                => $variation->get_sku(),
				'price'              => $variation->get_price(),
				'regular_price'      => $variation->get_regular_price(),
				'sale_price'         => $variation->get_sale_price(),
				'date_on_sale_from'  => $variation->get_sale_price_dates_from() ? date( 'Y-m-d', $variation->get_sale_price_dates_from() ) : '',
				'date_on_sale_to'    => $variation->get_sale_price_dates_to() ? date( 'Y-m-d', $variation->get_sale_price_dates_to() ) : '',
				'on_sale'            => $variation->is_on_sale(),
				'purchasable'        => $variation->is_purchasable(),
				'visible'            => $variation->is_visible(),
				'virtual'            => $variation->is_virtual(),
				'downloadable'       => $variation->is_downloadable(),
				'downloads'          => $this->get_downloads( $variation ),
				'download_limit'     => '' !== $variation->get_download_limit() ? (int) $variation->get_download_limit() : -1,
				'download_expiry'    => '' !== $variation->get_download_expiry() ? (int) $variation->get_download_expiry() : -1,
				'tax_status'         => $variation->get_tax_status(),
				'tax_class'          => $variation->get_tax_class(),
				'manage_stock'       => $variation->managing_stock(),
				'stock_quantity'     => $variation->get_stock_quantity(),
				'in_stock'           => $variation->is_in_stock(),
				'backorders'         => $variation->get_backorders(),
				'backorders_allowed' => $variation->backorders_allowed(),
				'backordered'        => $variation->is_on_backorder(),
				'weight'             => $variation->get_weight(),
				'dimensions'         => array(
					'length' => $variation->get_length(),
					'width'  => $variation->get_width(),
					'height' => $variation->get_height(),
				),
				'shipping_class'     => $variation->get_shipping_class(),
				'shipping_class_id'  => $variation->get_shipping_class_id(),
				'image'              => $this->get_images( $variation ),
				'attributes'         => $this->get_attributes( $variation ),
			);
		}

		return $variations;
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
}