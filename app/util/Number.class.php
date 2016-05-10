<?php
/**
 * This is used for process Number format data(never used)
 * @author Administrator
 *
 */
class Number {
	private $char = '0123456789abcdefghijklmnopqrstuvwxyz';
	/**
	 * convert number to specific base format
	 * @param unknown $number
	 * @param unknown $base
	 * @return string
	 */
	public static function convert($number, $base) {
		$str = "";
		if ($number == 0) {
			return "";
		} else {
			$str = self::convert ( floor ( $number / $base ), $base );
		}
		return $str . substr ( $number % base, 1 );
	}
}

?>