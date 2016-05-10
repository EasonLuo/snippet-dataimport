<?php

/** 
 * a database helper
 * @author Eason Luo
 * @since 18-6-2014
 */
require_once dirname ( __FILE__ ) . '/../util/functions.php';
class Database {
	private $db;
	
	/**
	 * create a pdo connection
	 *
	 * @param array $config  if $config is not given, use the default config file:db.ini      	
	 */
	function __construct($config = null) {
		if (! $config) {
			$config = parse_ini_file ( dirname ( __FILE__ ) . '/../../config/db.ini' );
		}
		$dsn = "mysql:host=" . $config ['server'] . ";" . "dbname=" . $config ['database'];
		$this->db = new PDO ( $dsn, $config ['username'], $config ['password'], array (
				PDO::ATTR_PERSISTENT => true 
		) );
		$this->init ();
	}
	
	/**
	 * never used
	 * @return boolean
	 */
	public function init() {
		$filename = dirname ( __FILE__ ) . "/../../config/type.ini";
		if (file_exists ( $filename )) {
			return false;
		}
		$stmt = $this->db->query ( "show tables" );
		$arr = $stmt->fetchAll ( PDO::FETCH_COLUMN );
		$str = "";
		foreach ( $arr as $table ) {
			$stmt = $this->db->query ( "show columns from $table" );
			$columns = $stmt->fetchAll ( PDO::FETCH_COLUMN );
			$str .= $table . "=" . join ( ",", $columns ) . "\r\n";
		}
		$file = fopen ( $filename, "w+" );
		fwrite ( $file, $str );
		fclose ( $file );
	}
	
	/**
	 * insert record into $table with named values
	 * @param string $table        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function insertByNamed($table, $args) {
		$keys = array_keys ( $args );
		$params = join ( ",", wrap ( $keys, ":", "" ) );
		$columns = join ( ",", wrap ( $keys, '`', '`' ) );
		$values = array ();
		foreach ( $args as $column => $value ) {
			$values [] = $value;
		}
		$sql = "insert into " . $table . " (" . $columns . ") values (" . $params . ")";
		$pstmt = $this->db->prepare ( $sql );
		foreach ( $args as $param => $val ) {
			$pstmt->bindParam ( ":$param", $val, $this->getDataType ( $val ) );
		}
		$rs = $pstmt->execute ();
		$pstmt = null;
		return $rs;
	}
	
	/**
	 * insert records by preparedstatement with question mark
	 *
	 * @param string $table        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function insertByMark($table, $args) {
		$keys = array_keys ( $args );
		$columns = join ( ",", wrap ( $keys, '`', '`' ) );
		$str = "";
		foreach ( $args as $key => $vla ) {
			$str .= ",?";
		}
		$str = substr ( $str, 1 );
		$sql = "insert into $table ( $columns 	) values ( $str )";
		$pstmt = $this->db->prepare ( $sql );
		$index = 1;
		foreach ( $args as $key => $val ) {
			$pstmt->bindValue ( $index ++, $val );
		}
		$rs = $pstmt->execute ();
    // print_r($pstmt->errorInfo());    
		return $rs;
	}
	
	/**
	 *
	 * @param string $table        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function delete($table, $args) {
		$keys = array_keys ( $args );
		if (count ( $keys ) === 0) {
			return false;
		}
		$id = $keys [0];
		$value = $args [$id];
		$sql = "delete from " . $table . " where " . $id . "=:" . $id;
		$pstmt = $this->db->prepare ( $sql );
		$pstmt->bindParam ( ":" . $id, $value );
		$rs = $pstmt->execute ();
		$pstmt = null;
		return $rs;
	}
	
	/**
	 * update record specified by keys with args
	 *
	 * @param string $table        	
	 * @param array $keys        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function update($table, $keys, $args) {
		$key = array_keys ( $keys );
		if (count ( $key ) === 0) {
			return false;
		}
		$columns = array_keys ( $args );
		$values = array ();
		foreach ( $columns as $column ) {
			$values [] = $column . "=:" . $column;
		}
		$values = join ( ",", $values );
		$id = $key [0];
		$sql = "update " . $table . " set " . $values . " where " . $id . "=:$id";
		$pstmt = $this->db->prepare ( $sql );
		foreach ( $columns as $column ) {
			$pstmt->bindParam ( ":" . $column, $args [$column] );
		}
		$pstmt->bindParam(":$id", $keys[$id]);
		$rs = $pstmt->execute ();
		$pstmt = null;
		return $rs;
	}
	
	/**
	 * load single record by primary key
	 *
	 * @param string $table        	
	 * @param array $args        	
	 * @return object
	 */
	public function load($table, $args) {
		$key = array_keys ( $args );
		if (count ( $key ) === 0) {
			return null;
		}
		$id = $key [0];
		$sql = "select * from " . $table . " where " . $id . " = :" . $id;
		$pstmt = $this->db->prepare ( $sql );
		$pstmt->bindParam ( ":" . $id, $args [$id] );
		if (! $pstmt->execute ()) {
			return null;
		}
		$arr = $pstmt->fetchAll ( PDO::FETCH_ASSOC );
		$pstmt = null;
		return $arr [0];
	}
	
	/**
	 * execute a sql statment with specific args
	 * @param unknown $sql
	 * @param unknown $args
	 * @return boolean
	 */
	public function execute($sql, $args) {
		$pstmt = $this->db->prepare ( $sql );
		$index = 1;
		foreach ( $args as $key => $val ) {
			$pstmt->bindValue ( $index ++, $val );
		}
		$rs = $pstmt->execute ();
		return $rs;
	}
	
	/**
	 * general query
	 *
	 * @param string $sql        	
	 * @param array $args        	
	 * @return assoc array
	 */
	public function query($sql, $args = array()) {
		$pstmt = $this->db->prepare ( $sql );
		$index = 1;
		foreach ( $args as $key => $val ) {
			$pstmt->bindValue ( $index ++, $val );
		}
		if ($pstmt->execute ()) {
			$arr = $pstmt->fetchAll ( PDO::FETCH_ASSOC );
		}
		$pstmt = null;
		return $arr;
	}
	
	/**
	 * start a database transaction
	 * @return boolean
	 */
	public function beginTransaction() {
		return $this->db->beginTransaction ();
	}
	
	/**
	 * commit a datatbase transaction if started
	 * @return boolean
	 */
	public function commit() {
		return $this->db->commit ();
	}
	
	/**
	 * rollback a database transaction if failed
	 * @return boolean
	 */
	public function rollBack() {
		return $this->db->rollBack ();
	}
	
	/**
	 * return the last insert primary key by auto increasing
	 */
	public function lastId() {
		$rs = $this->query ( "select last_insert_id() last_id " );
		return $rs [0] ['last_id'];
	}
	
	/**
	 * return the data type of val. this function may be used in sql statement.bindParam().
	 * @param string $val
	 * @return number|NULL
	 */
	public function getDataType($val) {
		if (is_numeric ( $val )) {
			return PDO::PARAM_INT;
		}
		if (is_string ( $val )) {
			return PDO::PARAM_STR;
		}
		return null;
	}
}

?>
