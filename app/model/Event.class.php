<?php

/**
 * event model
 * @author Yongjiang Zhang (86.yjzhang@gmail.com)
 * @since 18-6-2014
 */
require_once  dirname(__FILE__).'/../core/Bean.class.php';
class Event extends Bean {
	private $evt4std;
	public function __construct($attr = array(), $evt4std = array()) {
		parent::__construct ( $attr );
		$this->evt4std = $evt4std;
	}
	protected function getName() {
		return get_class ( $this );
	}
	
	public function getEvt4Std() {
		return $this->evt4std;
	}
	public function invalid() {
		$str = trim ( $this ['event_name'] );
		return empty ( $str );
	}
}
