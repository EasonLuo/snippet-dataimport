<?php

/** 
 * @author Yongjiang Zhang (86.yjzhang@gmail.com)
 * @since 18-6-2014
 */
abstract class Bean extends ArrayObject {
	
	/**
	 * magic function to return the value of the attribute $name;
	 *
	 * @param string $name        	
	 * @return object
	 */
	public function __get($name) {
		// self::validate ( $name );
		return $this [$name];
	}
	
	/**
	 * magic function to set $value to the given attribute $name
	 *
	 * @param string $name        	
	 * @param string $value        	
	 */
	public function __set($name, $value) {
		// self::validate ( $name );
		$this [$name] = $value;
	}
	
	/**
	 * return current class name
	 *
	 * @return string
	 */
	protected abstract function getName();
	public function toArray() {
		$arr = array ();
		foreach ( $this as $name=>$val ) {
			$arr [$name] = $val;
		}
		return $arr;
	}
}

?>
