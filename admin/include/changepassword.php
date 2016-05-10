<?php
/**
 * this is used for reseting password
 */
session_start ();

require_once dirname ( __FILE__ ) . '/../../app/service/AdminService.class.php';
$login = $_SESSION ['login'];
if (! $login) {
	AdminService::redirect ( "../login.php?error=1" );
}

$oldpw = trim ( $_POST ['oldpassword'] );
$newpw = trim ( $_POST ['newpassword'] );
$newcon = trim ( $_POST ['newconfirm'] );
$admin = new AdminService ();
if ($login ['password'] !== md5($oldpw)) {
	AdminService::redirect ( "../dashboard.php?error=3" );
} else if($oldpw===$newpw){
	AdminService::redirect ( "../dashboard.php?error=10" );
}else{
	$err = $admin->checkPassword ( $newpw, $newcon );
	if ($err !== 0) {
		AdminService::redirect ( "../dashboard.php?error=$err" );
	} else {
		$account = array (
				'account' => $login ['email'],
				'password' => md5 ( $newpw ) 
		);
		if ($admin->changePassword ( $account )) {
			$login['password'] = $account['password'];
			$_SESSION ['login'] = $login;
		}
	}
	AdminService::redirect ( "../dashboard.php" );
}
