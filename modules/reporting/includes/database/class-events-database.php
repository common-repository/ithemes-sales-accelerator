<?php
	
if (! defined('ABSPATH')) {
    exit();
}

/**
* This class handles Events database queries
**/

class RST_Reporting_Events_Database extends IT_Rooster_Database {

    public function __construct() {
	    
        $this->tableName = IT_RST_REPORTING_EVENTS_DATABASE;
    }
}