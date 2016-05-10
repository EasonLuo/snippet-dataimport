<?php
/**
 * This is used for user management/access control
 * @author Eason Luo (trueluo1987@gmail.com)
 */
use core\USER_ERROR;
require_once  dirname ( __FILE__ ) .'/../core/USER_ERROR.php';
require_once   dirname ( __FILE__ ) .'/../core/Database.class.php';
class AdminService {
	private $db;
	public function __construct() {
		$this->db = new Database ();
	}
	
	/**
	 * check account if is registed
	 * @param unknown $email
	 * @return boolean
	 */
	public function isRegisted($email) {
		$sql = "select count(1) cnt from t_account where email = ? ";
		$rs = $this->db->query ( $sql, array (
				$email 
		) );
		return $rs [0] ['cnt'] > 0;
	}
	
	/**
	 * redirect to specific url
	 * @param unknown $url
	 */
	public static function redirect($url) {
		header ( "location: $url" );
		exit ();
	}
	
	/**
	 * process login action
	 * @param string $email
	 * @param string $password
	 * @return Ambigous <assoc, multitype:>
	 */
	public function login($email, $password) {
		$sql = "select * from t_account where email = ? and password = ? ";
		$rs = $this->db->query ( $sql, array (
				'email' => $email,
				'password' => $password 
		) );
		return $rs;
	}
	
	/**
	 * process regist action
	 * @param array $account
	 * @return boolean
	 */
	public function regist($account) {
		$data = $account->toArray ();
    $count = $this->db->insertByMark ( 't_account', $data );
    return $count>0;
	}
	
	/**
	 * check if password is valid
	 * @param string $password
	 * @param string $confirm
	 * @return number
	 */
	public function checkPassword($password, $confirm) {
		if ($password !== $confirm) {
			return USER_ERROR::PASSWORD_MISMATCH;
		}
		if (strlen ( $password ) < 6) {
			return USER_ERROR::PASSWORD_TOO_SHORT;
		}
		if (strlen ( $password ) > 20) {
			return USER_ERROR::PASSWORD_TOO_LONG;
		}
		return 0;
	}
	
	/**
	 * change password for specific account
	 * @param array $account
	 * @return boolean
	 */
	public function changePassword($account) {
		$args = array (
				'password' => $account ['password'],
				'account' => $account ['account'] 
		);
		$sql = "update t_account set `password` = ? where `email` = ? ";
		return $this->db->execute ( $sql, $args );
	}
}

