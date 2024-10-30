<?php
	
if (! defined('ABSPATH')) {
    exit();
}

/**
* This class handles Product database queries
**/

class RST_Reporting_Products_Database extends IT_Rooster_Database {

    public function __construct() {
	    
        $this->tableName = IT_RST_REPORTING_PRODUCTS_DATABASE;
    }
}