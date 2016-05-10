<?php
/**
 * These are helper functions
 */

/**
 * print crlf
 * @return string
 */
function crlf() {
	$os = $_SERVER ['HTTP_USER_AGENT'];
	if (stristr ( $os, 'Win' )) {
		$crlf = "\r\n";
	} elseif (stristr ( $os, 'Mac' )) {
		$crlf = "\r";
	} else {
		$crlf = "\n";
	}
	return $crlf;
}

/**
 * print tab key
 * @return string
 */
function tab() {
	return "\t";
}

/**
 * split string by fixed length
 * @param string $data
 * @param number $length
 * @param string $glue
 * @return unknown|string
 */
function brick($data, $length = 1, $glue = "") {
	if (! data || strlen ( $data ) < $length) {
		return $data;
	}
	$arrTemp = str_split ( $data, $length );
	return implode ( $glue, $arrTemp );
}

/**
 * wrap each elements in array with specific chars left / right
 * @param array $arr
 * @param string $left
 * @param string $right
 * @param string $filter
 * @return unknown|multitype:string unknown
 */
function wrap($arr, $left = "", $right = "", $filter = null) {
	if (! $arr || count ( $arr ) === 0) {
		return $arr;
	}
	$newArr = array ();
	foreach ( $arr as $key => $value ) {
		if (function_exists ( $filter )) {
			if (! $filter ( $value )) {
				$newArr [$key] = $value;
				continue;
			}
		}
		$newArr [$key] = $left . $value . $right;
	}
	return $newArr;
}

/**
 * This is use for making sql symbol => insert into table_name(...) values([[?,?,?,?]]);
 * @param string $char
 * @param string $glue
 * @param int $count
 * @return string
 */
function chain($char, $glue, $count) {
	if ($count <= 0) {
		return "";
	}
	$str = "";
	while ( $count -- > 0 ) {
		$str .= "," . $char;
	}
	return substr ( $str, 1 );
}

/**
 * array to json string
 * @param array $arr
 * @param string $conbiner
 * @param unknown $lf
 * @return string
 */
function array2string($arr, $conbiner, $lf) {
	if (! isset ( $arr ) || count ( $arr ) <= 0) {
		return "";
	}
	$str = "";
	$conbiner = $conbiner ? $conbiner : ":";
	foreach ( $arr as $key => $value ) {
		if (is_array ( $value )) {
			$str .= "{" . array2string ( $value, $conbiner, $lf ) . "}";
			continue;
		} else {
			$str .= $key . $conbiner . $value;
			if (lf) {
				$str .= "\r\n";
			}
		}
	}
	return $str;
}

/**
 *
 *
 * never used
 * get date range base on the base date by specific type and mode
 *
 * @param int $type
 *        	0:day, 1:week, 2:month
 * @param int $mode
 *        	-1:pre, 1:post
 * @param date $base
 *        	default:now
 */
function getDateRange($type = 0, $mode = 0, $base = null) {
	$range = array (
			'day',
			'week',
			'month' 
	);
	$t = isset ( $range [$type] ) ? $range [$type] : 'day';
	$b = ! empty ( $base ) ? $base : time ();
	$m = empty ( $mode ) ? 0 : $mode;
	$target = strtotime ( "$m $t", $b );
	switch ($type) {
		case 1 :
			$start = strtotime ( "this Monday", $target );
			$end = strtotime ( "+7 day", $start );
			break;
		case 2 :
			$days = date ( "t", $target );
			$start = strtotime ( date ( "01-m-Y", $target ) );
			$end = strtotime ( "+$days day", $start );
			break;
		default :
			$offset = 1;
			$start = strtotime ( date ( "d-m-Y", $target ) );
			$end = strtotime ( "+1 day", $start );
			break;
	}
	return array (
			'start' => $start,
			'end' => $end,
			'current' => $target 
	);
}

/**
 * never used
 * @param unknown $month
 * @param unknown $day
 * @return multitype:|multitype:string
 */
function dateRange($month, $day) {
	if(!$month||!$day){
		return array();
	}
	$t = time ();
	$y = date ( 'Y', $t );
	$m = $month ? $month : date ( 'm', $t );
	if (!$day||$day<0||$day>31) {
		$start = date ( "Y-m-d", mktime ( null, null, null, $m, 1, $y ) );
		$end = date ( "Y-m-d", mktime ( null, null, null, $m + 1, 1, $y ) );
	} else {
		$start = date ( "Y-m-d", mktime ( null, null, null, $m, $day, $y ) );
		$end = date ( "Y-m-d", mktime ( null, null, null, $m, $day+1, $y ) );
	}
	return array (
			'start' => $start,
			'end' => $end 
	);
}

/**
 * return client IP
 * @return Ambigous <string, unknown>
 */
function IP(){
	// Get user IP address
	if ( isset($_SERVER['HTTP_CLIENT_IP']) && ! empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
	}
	
	$ip = filter_var($ip, FILTER_VALIDATE_IP);
	$ip = ($ip === false) ? '0.0.0.0' : $ip;
	return $ip;
}

/**
 * for each item in array,call $fn and map all the result of $fn into a array
 * @param array $arr
 * @param function $fn
 * @param array $arg
 * @return multitype:NULL
 */
function map($arr, $fn, $arg) {
	$newArr = array ();
	foreach ( $arr as $key => $item ) {
		$newArr [$key] = $fn ( $arg, $item );
	}
	return $newArr;
}

/**
 * save search keywords to session(never used)
 * @param unknown $arrName
 * @param unknown $clear
 */
function saveSearch($arrName, $clear) {
	session_start ();
	if (isset ( $_SESSION ['search'] ) && ! $clear) {
		$val = $_SESSION ['search'];
	} else {
		unset ( $_SESSION ['search'] );
		$val = array ();
	}
	foreach ( $arrName as $name ) {
		$val [$name] = $_GET [$name];
	}
	$_SESSION ['search'] = $val;
}

/**
 * get search keywords from session(never used)
 * @return Ambigous <multitype:, unknown>
 */
function getSearch() {
	session_start ();
	return isset ( $_SESSION ['search'] ) ? $_SESSION ['search'] : array ();
}

/**
 * clear all search keywords from session
 */
function clearSearch() {
	session_start ();
	unset ( $_SESSION ['search'] );
}
