<?php

/**
 * A Helper class which help to display the data as html format
 * @author Yongjiang Zhang (86.yjzhang@gmail.com)
 * @since 18-6-2014
 *          
 */
class View {
	
	/**
	 */
	private function __construct() {
	}
	
	/**
	 * create a hidden type form field
	 * @param string $name
	 * @param string $value
	 * @return string html fragment<input>
	 */
	public static function hidden($name,$value){
		return "<input type='hidden' name='".$name."' value='".$value."'/>";
	}
	
	/**
	 * create a select list
	 * @param string $name
	 * @param array $pairs
	 * @param object $value
	 * @param boolean $emptyOption
	 * @param string $action
	 * @return string html fragment<select>
	 */
	public static function select($name, $pairs,$value, $emptyOption = false, $action) {
		$str = "<select name='$name' $action>";
		if($emptyOption){
			$str .= "<option value=''>-- All --</option>";
		}
		foreach ( $pairs as $val => $desc ) {
			if($value==$val){
				$str .= "<option value='$val' selected>$desc</option>";
			}else{
				$str .= "<option value='$val'>$desc</option>";
			}
		}
		$str .= "</select>";
		return $str;
	}
	
	/**
	 * convert 1/0 to yes/no
	 * @param object $val
	 * @return string
	 */
	public static function yesno($val) {
		return $val == 0 ? 'NO' : 'YES';
	}
	
	/**
	 * return $nbsp; or the original value
	 * @param object $val
	 * @return Ambigous <string, unknown>
	 */
	public static function emp($val) {
		$empty = false;
		if(null==$val){
			$empty = true;
		}else if(""==trim($val)){
			$empty = true;
		}
		return $empty ? "&nbsp;" : $val;
	}
	
	/**
	 * return 0; or the original value
	 * @param object $val
	 * @return Ambigous <string, unknown>
	 */
	public static function num($val) {
		$empty = false;
		if(null==$val){
			$empty = true;
		}else if(""==trim($val)){
			$empty = true;
		}
		return $empty ? 0 : $val;
	}
	
	/**
	 * convert timestamp to a formated date string
	 * @param unknown $val
	 * @return Ambigous <Ambigous, string, object>|string
	 */
	public static function time($val) {
		if (empty ( $val )) {
			return self::emp ( $val );
		}
		return date ( "H:i A", strtotime($val) );
	}
	
	/**
	 * create a link<a>
	 * @param string $val
	 * @param string $action
	 * @return Ambigous <Ambigous, string, object>|string
	 */
	public static function link($val,$action) {
		if (empty ( $val )) {
			return self::emp ( $val );
		}
		if($action){
			return "<a href=\"".$action."\">".$val."</a>";
		}
		return "<a href='".$val."'>$val</a>";
	}
	
	/**
	 * show error message with red font
	 * @param string $msg
	 * @return string
	 */
	public static function error($msg) {
		return "<font color='red'>$msg</font>";
	}
	
	/**
	 * generate input:text element
	 * @param string $name
	 * @param string $value
	 * @return string html fragment
	 */
	public static function text($name,$value){
		return "<input type='text' name='".$name."' value='".$value."'/>";
	}
	
	/**
	 * generate textarea element 
	 * @param string $name
	 * @param string $value
	 * @return string
	 */
	public static function area($name,$value){
		return "<textarea rows='5' name='".$name."'>".$value."</textarea>";
	}
	
	/**
	 * generate select html elements with specific data
	 * @param array $data
	 * @param boolean $empty
	 * @return string
	 */
	public static function opts($data,$empty){
		$str = "";
		if($empty){
			$str.="<option value=''>-- All --</option>";
		}
		foreach ($data as $val=>$text){
			$str.="<option value='$val'>$text</option>";
		}
		return $str;
	}
	
	/**
	 * generate date format html value
	 * @param unknown $val
	 * @return Ambigous <Ambigous, string, object>|string
	 */
	public static function date($val){
		if (empty ( $val )) {
			return self::emp ( $val );
		}
		return date ( "d-m-Y", strtotime($val) );
	}
}

?>
