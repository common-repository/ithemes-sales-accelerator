<?php
if ( !defined( 'ABSPATH' ) ) {
    exit(); // Exit if accessed directly
}

/**
*  This class encapsulates WC_Product
**/

class IT_WC_Product {

    private $product;
    
    // Singleton design pattern
    protected static $instance = NULL;
    
    // Method to return the singleton instance
    public static function get_instance() {
        
        if ( null == self::$instance ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function __construct( $product ) {
	    
	    $this->product = WC()->product_factory->get_product( $product );
    }    
    
    public function get_product() {
	    
	    return $this->product;
    }
    
    public function get_id() {
	    
	    return $this->product->get_id();
    }
    
    public function get_variation_id() {
	    
	    return $this->product->get_variation_id();
    }
    
    public function get_sku() {
	    
	    return $this->product->get_sku();
    }
    
    public function get_name() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->product->get_name();
	    }
	    else {
		    return $this->product->get_title();
	    }
    }
    
    public function get_image_id() {
	    
	    return $this->product->get_image_id();
    }
    
    // Acess to product fields via custom function 
    
    public function get_product_type() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->product->get_type();
	    }
	    else {
		    return $this->product->product_type;
	    }
    }
    
    public function get_visibility() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->product->get_catalog_visibility();
	    }
	    else {
		    return $this->product->visibility;
	    }
    }
    
    public function get_sale_price_dates_from() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->product->get_date_on_sale_from();
	    }
	    else {
		    return $this->product->sale_price_dates_from;
	    }
    }
    
    public function get_sale_price_dates_to() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->product->get_date_on_sale_to();
	    }
	    else {
		    return $this->product->sale_price_dates_to;
	    }
    }
    
    public function get_download_limit() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->product->get_download_limit();
	    }
	    else {
		    return $this->product->download_limit;
	    }
    }
    
    public function get_download_expiry() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->product->get_download_expiry();
	    }
	    else {
		    return $this->product->download_expiry;
	    }
    }
    
    public function get_download_type() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return false;
	    }
	    else {
		    return $this->product->download_type;
	    }
    }
    
    public function get_backorders() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return $this->product->get_backorders();
	    }
	    else {
		    return $this->product->backorders;
	    }
    }
    
    public function get_attribute( $name = '' ) {
	   
		return $this->product->get_attribute( $name );
    }
    
    public function get_related() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
	    	return wc_get_related_products( $this->product->get_id() );
	    }
	    else {
		    return $this->product->get_related();
	    }
    }
    
    public function get_attributes() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		    
		    if ( $this->product->has_attributes( ) ) {
	    		return $this->product->get_attributes();
	    	}
	    	else {
	    		return array();
	    	}
	    }
	    else {
		    return $this->product->attributes;
	    }
    }
    
    public function get_parent_id() {
	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
            $parent_id = $this->product->get_parent_id();
        }
        else {
	        $parent_id = $this->product->parent->post->ID;
        }
        
        return $parent_id;
    }
    
    public function reduce_stock( $stock ) {
	    	    
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		    return wc_update_product_stock( $this->product, $stock, 'decrease' );
		}
		else{
	    	return $this->product->reduce_stock( $stock );
	    }
    }
    
    public function get_stock_quantity() {
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		    return $this->product->get_stock_quantity();
		}
		else{
	    	return $this->product->stock;
	    }
    }
    
     public function increase_stock( $stock ) {
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		    return wc_update_product_stock( $this->product, $stock, 'increase' );
		}
		else {
			return $this->product->increase_stock( $stock );
	    }
    }
    
    public function set_stock_quantity( $stock ) {
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		    return wc_update_product_stock( $this->product, $stock, 'set' );
		}
		else {
			return $this->product->set_stock_quantity( $stock );
	    }
    }
    
    public function check_stock_status() {
	    if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
		    $stock_status = $this->product->get_stock_status();
		}
		else {
			$stock_status = $this->product->stock_status;
	    }
		if ( ! $this->product->backorders_allowed() && $this->product->get_stock_quantity() <= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
			if ( $stock_status !== 'outofstock' ) {
				$this->product->set_stock_status( 'outofstock' );
			}
		} elseif ( $this->product->backorders_allowed() || $this->product->get_stock_quantity() > get_option( 'woocommerce_notify_no_stock_amount' ) ) {
			if ( $stock_status !== 'instock' ) {
				$this->product->set_stock_status( 'instock' );
			}
		}
	}
	
	public function get_category_names() {
		if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0', '>=' ) ) {
			return wc_get_product_category_list( $this->product->get_id() );
		}
		else {
			return $this->product->get_categories();
		}
		
	}
    
    public function get_child( $child_id ) {
	    
	    return $this->product->get_child( $child_id );
    }
    
    public function __call( $name, $arguments ) {
	    
	    if( method_exists( $this->product, $name ) ) {
	    	return $this->product->$name( $arguments );
	    }
	    else {
		    return false;
	    }
    }
}