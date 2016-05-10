<?php
/**
 * define common user errors
 * @author Eason Luo (trueluo1987@gmail.com)
 * @since 18-6-2014
 *
 */
namespace core;

class USER_ERROR {
	const UNLOGIN = 1;
	const NO_SUCH_ACCOUNT = 2;
	const PASSWORD_ERROR = 3;
	const PASSWORD_MISMATCH = 4;
	const PASSWORD_TOO_SHORT = 5;
	const PASSWORD_TOO_LONG = 6;
	const PASSWORD_TOO_WEEK = 7;
	const PASSWORD_BAD_WORD = 8;
	const PASSWORD_DIDNOT_CHANGE = 10;
}

?>
