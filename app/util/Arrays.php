<?php
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