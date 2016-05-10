<?php
require_once dirname ( __FILE__ ) . '/../core/Bean.class.php';
class Account extends Bean {
	public function __construct($attr = array()) {
		parent::__construct ( $attr );
	}
	/*
	 * (non-PHPdoc) @see Bean::getName()
	 */
	protected function getName() {
		return get_class ( $this );
	}
}

?>
