<?php
/**
 * This is used for process locations
 * @author Eason Luo (trueluo1987@gmail.com)
 *
 */
class LocationService {
	
	private $db;
	public function __construct() {
		$this->db = new Database ();
	}
	
	public function listAll($name,$filters = null){
		$sql = "select `$name`,count(sys_id) cnt from t_location where `$name` is not null ";
		foreach ( $filters as $cond ) {
			$sql .= " and t." . $cond ['field'] . " " . $cond ['operator'] . " '" . $cond ['value'] . "' ";
		}
		$sql .= "group by `$name` order by `$name` asc ";
		$rs = $this->db->query ( $sql );
		return $rs;
	}
}