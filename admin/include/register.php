<?php
/**
 * This is used for process add user operation
 */

//check if login
session_start ();
$login = $_SESSION ['login'];
if (! $login) {
	require_once dirname ( __FILE__ ) . '/../../app/service/AdminService.class.php';
	AdminService::redirect ( "../login.php?error=1" );
}

//get regist info from $_POST
$email = trim ( $_POST ['email'] );
$password = trim ( $_POST ['password'] );
$confirm = trim ( $_POST ['confirm'] );
$userType = intval(trim ( $_POST ['userType'] ));

require_once dirname ( __FILE__ ) . '/../../app/service/AdminService.class.php';
require_once dirname ( __FILE__ ) . '/../../app/model/Account.class.php';
//check if the account has been regist
$admin = new AdminService ();
if ($admin->isRegisted ( $email )) {
	$admin->redirect ( "../dashboard.php" );
} else {
	$code = $admin->checkPassword ( $password, $confirm );
	if ($code === 0) {
		$account = new Account ( array (
				"email" => $email,
				'password'=>md5($password),
				'account_type'=>$userType 
		) );
		//regist account and insert into database
		if ($admin->regist ( $account )) {
			// success. redirect to login
			$admin->redirect ( "../dashboard.php" );
		} else {
			// fail. redirect to regist
			$admin->redirect ( "../dashboard.php" );
		}
	}
}

?>
