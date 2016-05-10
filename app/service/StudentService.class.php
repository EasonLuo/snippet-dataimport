<?php

/**
 *
 * @author Eason Luo (trueluo1987@gmail.com)
 * @since 18-6-2014
 */
require_once  dirname ( __FILE__ ) .'/../core/Database.class.php';
class StudentService {
	private $db;
	private static $studentType;
	
	/**
	 */
	function __construct() {
		$this->db = new Database ();
	}
	/**
	 * get all student types
	 * @return Ambigous <assoc, multitype:>
	 */
	public function getStudentType() {
		if (! self::$studentType) {
			self::$studentType = $this->db->query ( "select * from t_student_type" );
		}
		return self::$studentType;
	}
	
	/**
	 * get all data by specific field from student types 
	 * @param string $name
	 */
	public function listAll($name='name'){
		return $this->db->query('select sys_id,'.$name.' from t_student_type order by sys_id asc ');
	}
	
	/**
	 * get data by names
	 * @param array $names
	 * @return NULL|Ambigous <assoc, multitype:>
   */
  public function searchByName($names){
		if(count($names)<=0){
			return null;
		}
		$symbol = "";
		for($i=0;$i<count($names);$i++){
			if(!empty($names[$i])){
				$symbol .=",?";
			}
		} 
		$symbol = substr($symbol, 1);
		$sql = "select * from t_student_type where `name` in(".$symbol.")";
		return $this->db->query($sql,$names);
	}
}

?>
