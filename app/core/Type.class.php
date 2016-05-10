<?php

/**
 * load file /config/type.ini and define all the attributes for all types which extend Bean
 * @author Yongjiang Zhang (86.yjzhang@gmail.com)
 *
 */
class Type {
	private static $attrs = array ();
	private static $spec = array ();
	private static $mode = "r";
	private static $commentmark = "#";
	public static function init() {
		$filename = dirname ( __FILE__ ) . "/../../config/type.ini";
		if (count ( self::$attrs ) > 0) {
			return false;
		}
		$file = fopen ( $filename, self::$mode );
		if (! file_exists ( $filename )) {
			die ( "file does not exist " . $filename );
		}
		while ( ! feof ( $file ) ) {
			$line = trim ( fgets ( $file ) );
			if (strlen ( $line ) === 0) {
				continue;
			}
			if (strpos ( $line, self::$commentmark ) === 0) {
				continue;
			}
			$pair = explode ( "=", $line );
			self::$attrs [trim ( $pair [0] )] = explode ( ",", $pair [1] );
		}
		fclose ( $file );
	}
	public static function attrs($class) {
		return self::$attrs [$class];
	}
}
