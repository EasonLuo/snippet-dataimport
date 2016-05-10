<?php
/**
 * This is used for formating values
 * @author Yongjiang Zhang (86.yjzhang@gmail.com)
 * @since 18-06-2014
 *
 */
class Format {
	/**
	 * format yes /no values
	 * @param unknown $str
	 * @return number
	 */
	public static function zerone($str) {
		if (empty ( $str )) {
			return 0;
		}
		if (in_array ( trim ( $str ), array (
				'NO',
				'no',
				'No',
				'nO',
				'N',
				'n',
				'0',
				'' 
		) )) {
			return 0;
		} else {
			return 1;
		}
	}
	
	/**
	 * format date values
	 * @param string $str
	 * @return NULL|string
	 */
	public static function date($str) {
		if (empty ( $str )) {
			return null;
		}
		return date ( 'd/m/Y', strtotime ( str_replace ( '/', '-', $str ) ) );
	}
	
	/**
	 * format time values (never used)
	 * @param string $str
	 * @return NULL
	 */
	public static function time($str) {
		if (empty ( $str )) {
			return null;
		}
	}
}

?>
