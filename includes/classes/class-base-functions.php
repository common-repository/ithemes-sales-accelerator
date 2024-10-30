<?php

class IT_RST_Base_Functions {
	
	public $result;
	
	// flexible object encoder
	public static function encode_object( $obj ) {

		$str = json_encode( $obj );

		if ( $str == '{}' ) return serialize( $obj );
		else return $str;
	}	
	
	// flexible object decoder
	public static function decode_object( $str, $assoc = false, $loadAmazonClasses = false ) {

		if ( $str == '' ) return false; 

		// json_decode
		$obj = json_decode( $str, $assoc );
		if ( is_object($obj) || is_array($obj) ) return $obj;
		
		// unserialize fallback
		$obj = maybe_unserialize( $str );
		if ( is_object($obj) || is_array($obj) ) return $obj;
		
		return $str;
	}	

	public function is_ajax() {
		return defined( 'DOING_AJAX' ) && DOING_AJAX;
	}

	// Check if given WordPress plugin is active
	public static function is_plugin_active( $plugin ) {

		if ( is_multisite() ) {

			// check for network activation
			if ( ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

			if ( function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( $plugin ) )
				return true;				

		}

    	return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
	}

	// Custom mb_strlen implementation
	public static function mb_strlen( $string ) {

		// use mb_strlen() if available
		if ( function_exists('mb_strlen') ) return mb_strlen( $string );

		// fallback if PHP was compiled without multibyte support
		$length = preg_match_all( '(.)su', $string, $matches );
    	return $length;
	}

	// Custom mb_substr implementation
	public static function mb_substr( $string, $start, $length ) {

		// use mb_substr() if available
		if ( function_exists('mb_substr') ) return mb_substr( $string, $start, $length );

		// fallback if PHP was compiled without multibyte support
		// $string = substr( $string, $start, $length );

		// snippet from http://www.php.net/manual/en/function.mb-substr.php#107698
	    $string = join("", array_slice( preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY), $start, $length ) );

    	return $string;
	}
	
	static public function getLocalTimeZone() {

        // get the local timezone from WP
        $tz = get_option('timezone_string');
        if ( ! $tz ) $tz = wc_timezone_string(); // 'Europe/London'

        return $tz;
    }

    public static function getCurrentLocalTime( $format = 'H:i' ) {

        // create the DateTimeZone object using local timezone from WP
        $dtime = new DateTime( 'now', new DateTimeZone( self::getLocalTimeZone() ) );

        // return the time using the preferred format
        $time = $dtime->format( $format );

        return $time;
    }

    public static function convertTimestampToLocalTime( $timestamp ) {

        // set this to the time zone provided by the user
        $tz = get_option( 'timezone_string') ;
        if ( ! $tz ) $tz = wc_timezone_string();
         
        // create the DateTimeZone object for later
        $dtzone = new DateTimeZone($tz);
         
        // first convert the timestamp into a string representing the local time
        $time = date('r', $timestamp);
         
        // now create the DateTime object for this time
        $dtime = new DateTime($time);
         
        // convert this to the user's timezone using the DateTimeZone object
        $dtime->setTimeZone($dtzone);
         
        $time = $dtime->format('Y-m-d H:i:s');

        return $time;
    }

    public static function convertLocalTimeToTimestamp( $time ) {

        // set this to the time zone provided by the user
        $tz = get_option( 'timezone_string' );
        if ( ! $tz ) $tz = wc_timezone_string(); // 'Europe/London'
         
        // create the DateTimeZone object for later
        $dtzone = new DateTimeZone($tz);
         
        // now create the DateTime object for this time and user time zone
        $dtime = new DateTime($time, $dtzone);
         
        // print the timestamp
        $timestamp = $dtime->format( 'U' );

        return $timestamp;
    }
    
    // convert raw CSV data to PHP array
    public static function csv_to_array( $input, $query = false, $delimiter = "\t" ) {

        $header  = null;
        $data    = array();
        $csvData = str_getcsv( $input, "\n", '' );

        foreach( $csvData as $csvLine ) {

            if ( $csvLine == null ) continue; // skip empty lines

            if ( is_null($header) ) {
                $header = explode($delimiter, $csvLine);    
            } else {

                // handle query string
                if ( $query && false === stripos( $csvLine, $query ) ) continue;


                // split row into cells
                $items = explode($delimiter, $csvLine);

                for ( $n = 0, $m = count($header); $n < $m; $n++ ){
                    $prepareData[$header[$n]] = isset( $items[$n] ) ? $items[$n] : '';
                }

                $data[] = $prepareData;
            }
        }
        return $data;
    }
}