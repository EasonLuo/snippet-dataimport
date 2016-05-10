<?php
require_once  dirname ( __FILE__ ) .'/../core/Database.class.php';
require_once  dirname ( __FILE__ ) .'/../model/Event.class.php';
require_once  dirname ( __FILE__ ) .'/StudentService.class.php';
require_once  dirname(__FILE__).'/../util/functions.php';

/**
 * Handle event related 
 * @author eason.luo
 *
 */

class EventService {
	private $db;
	public function __construct() {
		$this->db = new Database ();
	}
	
	/**
	 * resolve excel file to two parts: event info and evt4std info
	 *
	 * @param unknown $arrEvt        	
	 * @return multitype:unknown multitype:unknown
	 */
	public function resolve($arrEvt) {
		$evt4std = array ();
		foreach ( $arrEvt as $title => $val ) {
			if (in_array ( $title, array (
					'new_student',
					'returning_student',
					'undergraduate',
					'postgraduate_coursework',
					'postgraduate_research' 
			) )) {
				$evt4std [$title] = $val;
				unset ( $arrEvt [$title] );
			}
		}
		return array (
				'event' => $arrEvt,
				'evt4std' => $evt4std 
		);
	}
	
	/**
	 * import events from excel template file
	 * @param array $events
	 * @return number
	 */
	public function importEvents($events) {
		$count = 0;
		try {
			$stdService = new StudentService ();
			$studenttypes = $stdService->getStudentType ();
			$this->db->beginTransaction ();
			foreach ( $events as $event ) {
				if ($event->invalid ()) {
					continue;
				}
				$arr = $event->toArray ();
				unset ( $arr ['sys_id'] );
				$rs = $this->db->insertByMark ( 't_event', $arr );
				if ($rs > 0) {
					$count ++;
					$evtId = $this->db->lastId ();
					//$this->insertStat ( $evtId );
					$studenttypes4event = $this->createEvt4Std ( $evtId, $event->getEvt4Std (), $studenttypes );
					foreach ( $studenttypes4event as $e4s ) {
						$this->db->insertByMark ( 't_event_student', $e4s );
					}
				}
			}
			$this->db->commit ();
		} catch ( PDOException $e ) {
			$this->db->rollBack ();
		}
		return $count;
	}
	
	/**
	 *
	 * @param unknown $evtId        	
	 * @return boolean
	 */
	public function insertStat($evtId) {
		return $this->db->insertByNamed ( 't_event_stat', array (
				'event_id' => $evtId 
		) );
	}
	
	/**
	 * record when events have been viewed/exported/printed/added/removed : 
   * opersation = 1/2/3/4/5
	 * @param int $eventId
	 * @param number $operation
	 * @return boolean
	 */
	public function logEventStat($eventId, $operation = 1) {
		$stat = array ();
		$stat ['event_id'] = $eventId;
		$stat ['operation'] = $operation;
		$stat ['operate_time'] = date ( 'Y-m-d H:i:s', time () );
		require_once dirname ( __FILE__ ) .'/../../lib/mobiledetect/Mobile_Detect.php';
		$detect = new Mobile_Detect ();
		$stat ['user_agent'] = $detect->isMobile () ? 1 : 2;
		$detect = null;
		$count = $this->db->insertByMark ( 't_event_stat', $stat );
		return $count;
	}
	
	/**
	 * record when events have been exported via pc or mobile
	 * @return boolean
	 */
	public function logExportStat() {
		require_once dirname ( __FILE__ ) .'/../../lib/mobiledetect/Mobile_Detect.php';
		$detect = new Mobile_Detect ();
		$arg = array ();
		$arg ['export_time'] = date ( 'Y-m-d H:i:s', time () );
		$arg ['client_ip'] = IP ();
		$arg ['user_agent'] = $detect->isMobile () ? 1 : 2;
		$detect = null;
		return $this->db->insertByMark ( 't_export_stat', $arg );
	}
	
	/**
	 * record when events have been printed via pc or mobile
	 * @return boolean
	 */
	public function logPrintStat() {
		require_once dirname ( __FILE__ ) .'/../../lib/mobiledetect/Mobile_Detect.php';
		$detect = new Mobile_Detect ();
		$arg = array ();
		$arg ['print_time'] = date ( 'Y-m-d H:i:s', time () );
		$arg ['client_ip'] = IP ();
		$arg ['user_agent'] = $detect->isMobile () ? 1 : 2;
		$detect = null;
		return $this->db->insertByMark ( 't_print_stat', $arg );
	}

	/**
	 * 
	 * @param int $event_id
	 * @param array $evt4std
	 * @param array $studentTypes
	 * @return multitype:multitype:unknown
	 */
	public function createEvt4Std($event_id, $evt4std, $studentTypes) {
		$arr = array ();
		foreach ( $studentTypes as $type ) {
			if ($evt4std [$type ['description']] == 1) {
				// echo "$event_id"." --> ".$type['sys_id']."<br>";
				$rd = array (
						'event_id' => $event_id,
						'student_type_id' => $type ['sys_id'] 
				);
				$arr [] = $rd;
			}
		}
		return $arr;
	}
	
	/**
	 * 
	 * @param array $search define fields for search
	 * @param number $section 1:new upload 2:current 3:expired 4:trash 5:all
	 * @param array $timen year:specific year  expired:if expired only
	 * @param array $filter 
	 * @param array $order
	 * @return Ambigous <assoc, multitype:>
	 * @example 
	 * $service = new EventService();
	 * $search = array('event_name','description');
	 * $time = array('year'=>2014,'expired'=>-1);
	 * $filter = array();
	 * $filter[] = array('field'=>'category','operator'=>'=','value'=>$_GET['category']);
	 * $order = array();
	 * $order[] = array('orderType'=>1,'orderBy'=>'event_date');
	 * $list = $service->search($search,5,$time,$filter,$order);
	 */
	public function search($search = array(), $section = 0, $time = array(), $filter = array(), $order = array()) {
		$sql = "select t.* from ( ";
		$sql .= "select t0.*, ";
		// search->fields
		$fields = isset ( $search ['fields'] ) ? $search ['fields'] : array ();
		$arrFields = array ();
		foreach ( $fields as $f ) {
			$arrFields [] = "IFNULL(t0.$f,'')";
		}
		$strFields = join ( ",',',", $arrFields );
		$sql .= "if(date(now())>t0.event_date,1,0) expired,";
		$sql .= "concat($strFields) search,";
		$sql .= "group_concat( ";
		$sql .= "distinct t3.`name` ";
		$sql .= "order by t3.student_type_id asc ";
		$sql .= ") student_type, ";
		$sql .= "IFNULL(t4.viewed, 0) viewed, ";
		$sql .= "IFNULL(t4.exported, 0) exported, ";
		$sql .= "IFNULL(t4.printed, 0) printed, ";
		$sql .= "IFNULL(t4.added, 0) added, ";
		$sql .= "IFNULL(t4.removed, 0) removed, ";
		$sql .= "IFNULL(t4.absolute, 0) absolute ";
		$sql .= "from  t_event t0 ";
		$sql .= "left join ( ";
		$sql .= "select t1.event_id, t2.sys_id student_type_id, t2.`name` ";
		$sql .= "from t_event_student t1, t_student_type t2 ";
		$sql .= "where t1.student_type_id = t2.sys_id ";
		$sql .= ") t3 ";
		$sql .= "on t0.sys_id = t3.event_id ";
		$sql .= "LEFT JOIN ( ";
		$sql .= "SELECT ";
		$sql .= "event_id, ";
		$sql .= "sum(IF(operation = 1, 1, 0)) viewed, ";
		$sql .= "sum(IF(operation = 2, 1, 0)) exported, ";
		$sql .= "sum(IF(operation = 3, 1, 0)) printed, ";
		$sql .= "sum(IF(operation = 4, 1, 0)) added, ";
		$sql .= "sum(IF(operation = 5, 1, 0)) removed, ";
		$sql .= "sum(IF(operation = 4, 1, 0)) - sum(IF(operation = 5, 1, 0)) absolute ";
		$sql .= "FROM t_event_stat ";
		$sql .= "GROUP BY event_id ";
		$sql .= ") t4 ON t0.sys_id = t4.event_id ";
		$sql .= "group by t0.sys_id ";
		if (count ( $order ) > 0) {
			$arrOrder = array ();
      foreach ( $order as $o ) {
				if (! empty ( $o ['orderBy'] ) && ! empty ( $o ['orderType'] )) {
					$orderType = $o ['orderType'] > 0 ? 'asc' : 'desc';
          if ($o['orderBy']=='viewed' || $o['orderBy']=='exported' || $o['orderBy']=='added' || $o['orderBy']=='removed' || $o['orderBy']=='printed' || $o['orderBy']=='absolute') {
           $arrOrder [] = " t4." . $o ['orderBy'] . " " . $orderType;
          }
          else {
					$arrOrder [] = " t0." . $o ['orderBy'] . " " . $orderType;
          }
				}
			}
			if (count ( $arrOrder ) > 0) {
				$strOrder = join ( ",", $arrOrder );
				$sql .= "order by " . $strOrder;
			}
		} else {
			$sql .= "order by t0.event_date asc , t0.start_time asc, t0.sys_id asc";
		}
		$sql .= ") t ";
		$sql .= "where 1 = 1 ";
		
		// section
		switch ($section) {
			// new
			case 1 :
				$sql .= " and t.event_status = 1 ";
				$sql .= " and t.event_status >= 0";
				break;
			// current
			case 2 :
				$sql .= " and t.event_status = 2 ";
				$sql .= " and t.event_status >= 0 ";
				$sql .= " and date(t.event_date) >= date(now()) ";
				break;
			// expired
			case 3 :
				$sql .= " and date(t.event_date) < date(now()) ";
				$sql .= " and t.event_status = 2 ";
				break;
			// Trash
			case 4 :
				$sql .= " and t.event_status < 0 ";
				break;
			//all
			case 5 :
				break;
			default :
				$sql .= " and t.event_status >= 0 ";
				break;
		}
		// search->keyword
		$index = 0;
		$kw = explode ( " ", isset ( $search ['keywords'] ) ? $search ['keywords'] : "" );
		foreach ( $kw as $arg ) {
			$arg = trim ( $arg );
			if (empty ( $arg )) {
				continue;
			}
			if ($index == 0) {
				$sql .= "and (t.search like '%$arg%' ";
			} else {
				$sql .= "and t.search like '%$arg%' ";
			}
			$index ++;
		}
		if ($index > 0) {
			$sql .= " )";
		}
		// time
		if (! empty ( $time ['year'] )) {
			$sql .= "and year(t.event_date)=" . $time ['year'] . ' ';
		}
		if (isset ( $time ['expired'] )) {
			if ($time ['expired'] > 0) {
				$sql .= "and date(now())>t.event_date";
			} else {
				$sql .= "and date(now())<=t.event_date";
			}
		}
		// filter
		foreach ( $filter as $cond ) {
			$sql .= " and t." . $cond ['field'] . " " . $cond ['operator'] . " '" . $cond ['value'] . "' ";
		}
		if (! is_array ( $search ) && ! empty ( $search )) {
			$args = array (
					$args 
			);
		}
		
		// echo htmlspecialchars ( $sql );
		return $this->db->query ( $sql );
	}
	
	/**
	 * 
	 * @param unknown $period
	 * @param unknown $type
	 * @return Ambigous <assoc, multitype:>
	 */
	public function stat($period, $type) {
    $operation = $type%2==0 ? 2 : 1;
    $client = $type/2 > 1 ? 2 : 1;
    if ($type == 5) {
      $operation = 3;
      $client = 2;
    }
		$sql = " SELECT distinct DATE_FORMAT(operate_time, '%d-%m-%Y') dt, count(distinct operate_time) cnt ";
		$sql .= " FROM t_event_stat ";
		$sql .= " WHERE operation = $operation AND user_agent = $client ";
		if (isset ( $period ['start'] )) {
			$sql .= " AND operate_time>=date('" . $period ['start'] . "')";
		}
		if (isset ( $period ['end'] )) {
			$sql .= " AND operate_time<=date('" . $period ['end'] . "')";
		}
		$sql .= " GROUP BY DATE_FORMAT(operate_time, '%d-%m-%Y') ";
		$sql .= " ORDER BY DATE_FORMAT(operate_time, '%d-%m-%Y') ASC ";
		// echo htmlspecialchars ( $sql );
		return $this->db->query ( $sql );
	}
	
	/**
	 * print export button record table
	 * @param array $period
	 * @return Ambigous <assoc, multitype:>
	 */
	public function exportstat($period) {
		$sql = "SELECT ";
		$sql .= "DATE_FORMAT(s.export_time, '%d-%m-%Y') dt, ";
		$sql .= "sum(IF(s.user_agent = 1, 1, 0)) mobile, ";
		$sql .= "sum(IF(s.user_agent = 2, 1, 0)) pc ";
		$sql .= "FROM ";
		$sql .= "t_export_stat s ";
		$sql .= "where 1 = 1 ";
		if (isset ( $period ['start'] )) {
			$sql .= " AND DATE_FORMAT(s.export_time, '%Y-%m-%d')>=date('" . $period ['start'] . "')";
		}
		if (isset ( $period ['end'] )) {
			$sql .= " AND DATE_FORMAT(s.export_time, '%Y-%m-%d')<=date('" . $period ['end'] . "')";
		}
		$sql .= "GROUP BY ";
		$sql .= "DATE_FORMAT(s.export_time, '%d-%m-%Y') ";
    // echo htmlspecialchars($sql);
		return $this->db->query ( $sql );
	}
	
	/**
	 * print print button record table 
	 * @param array $period
	 * @return Ambigous <assoc, multitype:>
	 */
	public function printstat($period) {
		$sql = "SELECT ";
		$sql .= "DATE_FORMAT(p.print_time, '%d-%m-%Y') dt, ";
		$sql .= "sum(IF(p.user_agent = 1, 1, 0)) mobile, ";
		$sql .= "sum(IF(p.user_agent = 2, 1, 0)) pc ";
		$sql .= "FROM ";
		$sql .= "t_print_stat p ";
		$sql .= "where 1 = 1 ";
		if (isset ( $period ['start'] )) {
			$sql .= " AND DATE_FORMAT(p.print_time, '%Y-%m-%d')>=date('" . $period ['start'] . "')";
		}
		if (isset ( $period ['end'] )) {
			$sql .= " AND DATE_FORMAT(p.print_time, '%Y-%m-%d')<=date('" . $period ['end'] . "')";
		}
		$sql .= "GROUP BY ";
		$sql .= "DATE_FORMAT(p.print_time, '%d-%m-%Y') ";
    // echo htmlspecialchars($sql);
		return $this->db->query ( $sql );
	}

	/**
	 * print export/print button record merged table
	 * @param array $period
	 * @return Ambigous <assoc, multitype:>
	 */
	public function eventstat($period) {
    $sql = "
      SELECT
      IFNULL(a.dt, b.dt1) dt,
      IFNULL(mobile, 0) e_mobile, IFNULL(pc, 0) e_pc, IFNULL(mobile1, 0) p_mobile, IFNULL(pc1, 0) p_pc
      FROM
      (
        SELECT
        s.export_time dt,
        SUM(IF(s.user_agent = 1, 1, 0)) mobile, SUM(IF(s.user_agent = 2, 1, 0)) pc
        FROM t_export_stat s
        WHERE 1 = 1";
        if (isset ( $period ['start'] )) {
          $sql .= " AND DATE_FORMAT(s.export_time, '%Y-%m-%d')>=date('" . $period ['start'] . "')";
        }
        if (isset ( $period ['end'] )) {
          $sql .= " AND DATE_FORMAT(s.export_time, '%Y-%m-%d')<=date('" . $period ['end'] . "')";
        }
        $sql .="GROUP BY
        DATE_FORMAT(s.export_time, '%d-%m-%Y')
      )a 
      LEFT JOIN
        (
          SELECT 
          p.print_time dt1,
          SUM(IF(p.user_agent = 1, 1, 0)) mobile1, sum(IF(p.user_agent = 2, 1, 0)) pc1 
          FROM t_print_stat p
          WHERE 1 = 1";
        if (isset ( $period ['start'] )) {
          $sql .= " AND DATE_FORMAT(p.print_time, '%Y-%m-%d')>=date('" . $period ['start'] . "')";
        }
        if (isset ( $period ['end'] )) {
          $sql .= " AND DATE_FORMAT(p.print_time, '%Y-%m-%d')<=date('" . $period ['end'] . "')";
        }
        $sql .="GROUP BY
          DATE_FORMAT(p.print_time, '%d-%m-%Y')
        )b
      ON a.dt = b.dt1
  UNION
      SELECT
      IFNULL(a.dt, b.dt1) dt,
      IFNULL(mobile, 0) e_mobile, IFNULL(pc, 0) e_pc, IFNULL(mobile1, 0) p_mobile, IFNULL(pc1, 0) p_pc
      FROM
      (
        SELECT
        s.export_time dt,
        SUM(IF(s.user_agent = 1, 1, 0)) mobile, SUM(IF(s.user_agent = 2, 1, 0)) pc
        FROM t_export_stat s
        WHERE 1 = 1";
        if (isset ( $period ['start'] )) {
          $sql .= " AND DATE_FORMAT(s.export_time, '%Y-%m-%d')>=date('" . $period ['start'] . "')";
        }
        if (isset ( $period ['end'] )) {
          $sql .= " AND DATE_FORMAT(s.export_time, '%Y-%m-%d')<=date('" . $period ['end'] . "')";
        }
        $sql .="GROUP BY
        DATE_FORMAT(s.export_time, '%d-%m-%Y')
      )a 
      RIGHT JOIN
        (
          SELECT 
          p.print_time dt1,
          SUM(IF(p.user_agent = 1, 1, 0)) mobile1, sum(IF(p.user_agent = 2, 1, 0)) pc1 
          FROM t_print_stat p
          WHERE 1 = 1";
        if (isset ( $period ['start'] )) {
          $sql .= " AND DATE_FORMAT(p.print_time, '%Y-%m-%d')>=date('" . $period ['start'] . "')";
        }
        if (isset ( $period ['end'] )) {
          $sql .= " AND DATE_FORMAT(p.print_time, '%Y-%m-%d')<=date('" . $period ['end'] . "')";
        }
        $sql .="GROUP BY
          DATE_FORMAT(p.print_time, '%d-%m-%Y')
        )b
      ON a.dt = b.dt1
      ORDER BY dt
      ";

    // echo htmlspecialchars($sql);
		return $this->db->query ( $sql );
	}

	/**
	 * 
	 * @param unknown $period
	 * @param unknown $type
	 * @return Ambigous <assoc, multitype:>
	 */
	public function getText($location) {
		$sql = " SELECT content FROM t_text ";
		$sql .= " WHERE loc = '$location' ";
		// echo htmlspecialchars ( $sql );
		return $this->db->query ( $sql );
	}

	/**
	 * 
	 * @param unknown $period
	 * @param unknown $type
	 * @return Ambigous <assoc, multitype:>
	 */
	public function updateText($location, $text) {
    $text = trim($text);
		$sql = " UPDATE t_text ";
		$sql .= "SET content = $text";
		$sql .= " WHERE loc = $location ";
		// echo htmlspecialchars ( $sql );
		return $this->db->query ( $sql );
	}

	/**
	 *
	 * @param string $name        	
	 * @param array $filters        	
	 * @return Ambigous <assoc, multitype:>
	 */
	public function listAll($name, $filters = array()) {
// do not show any expired events
  $sql = "select `$name`,count(sys_id) cnt from t_event where `$name` is not null and date(now())<event_date ";
// }
		foreach ( $filters as $cond ) {
			$sql .= " and " . $cond ['field'] . " " . $cond ['operator'] . " '" . $cond ['value'] . "' ";
		}
		$sql .= "group by `$name` order by `$name` asc ";
		$rs = $this->db->query ( $sql );
		//echo htmlspecialchars($sql);
		return $rs;
	}
	
	/**
	 *
	 * @param array $ids        	
	 * @return boolean
	 */
	public function publish($ids) {
		$str = "";
		foreach ( $ids as $id ) {
			$str .= ",? ";
		}
		$str = substr ( $str, 1 );
		$sql = "update t_event set event_status = 2 where sys_id in ( $str ) and event_status = 1";
		return $this->db->execute ( $sql, $ids );
	}
	/**
	 *
	 * @param array $ids        	
	 * @return boolean
	 */
	public function remove($ids) {
		$str = "";
		foreach ( $ids as $id ) {
			$str .= ",? ";
		}
		$str = substr ( $str, 1 );
		$sql = "update t_event set event_status = (0-event_status) where sys_id in ( $str ) and event_status>0 ";
		return $this->db->execute ( $sql, $ids );
	}
	/**
	 *
	 * @param array $ids        	
	 * @return boolean
	 */
	public function pushback($ids) {
		$str = "";
		foreach ( $ids as $id ) {
			$str .= ",? ";
		}
		$str = substr ( $str, 1 );
		$sql = "update t_event set event_status = abs(event_status) where sys_id in ( $str ) and event_status<0 ";
		return $this->db->execute ( $sql, $ids );
	}
	/**
	 *
	 * @param array $ids        	
	 * @return boolean
	 */
	public function permanentdelete($ids) {
		$str = "";
		foreach ( $ids as $id ) {
			$str .= ",? ";
		}
		$str = substr ( $str, 1 );
		$this->db->beginTransaction ();
		// delete event for student type
		$sql1 = "delete from t_event_student where event_id in ( $str )";
		$this->db->execute ( $sql1, $ids );
		// delete event stat
		$sql2 = "delete from t_event_stat where event_id in ( $str )";
		$this->db->execute ( $sql2, $ids );
		// delete event
		$sql3 = "delete from t_event where sys_id in ( $str ) and event_status<0 ";
		$rs = $this->db->execute ( $sql3, $ids );
		$this->db->commit ();
		return $rs;
	}
	
	/**
	 *
	 * @param array $ids        	
	 * @return Ambigous <assoc, multitype:>
	 */
	public function findEventsById($ids, $sortBy = array()) {
		$str = "";
		foreach ( $ids as $id ) {
			$str .= ",? ";
		}
		$str = substr ( $str, 1 );
		$sql = "select * from t_event where sys_id in ( $str ) ";
		if (count ( $sortBy ) > 0) {
			$arrOrder = array ();
			foreach ( $sortBy as $order ) {
				if (! empty ( $order ['orderBy'] ) && ! empty ( $order ['orderType'] )) {
					$orderType = $order ['orderType'] > 0 ? 'asc' : 'desc';
					$arrOrder [] = $order ['orderBy'] . " " . $orderType;
				}
			}
			if (count ( $arrOrder ) > 0) {
				$strOrder = join ( ",", $arrOrder );
				$sql .= "order by " . $strOrder;
			}
		} else {
			$sql .= "order by sys_id asc ";
		}
		return $this->db->query ( $sql, $ids );
	}
	
	/**
	 * get the nearest dates out of range
	 *
	 * @param string $start        	
	 * @param string $end        	
	 * @return multitype:NULL
	 */
	public function countEventsOutOfRange($start, $end) {
		$sql = "select t.event_date d from ( ";
		$sql .= "select t0.*, group_concat( ";
		$sql .= "distinct t3.`name` ";
		$sql .= "order by t3.student_type_id asc ";
		$sql .= ") student_type ";
		$sql .= "from  t_event t0 ";
		$sql .= "left join ( ";
		$sql .= "select t1.event_id, t2.sys_id student_type_id, t2.`name` ";
		$sql .= "from t_event_student t1, t_student_type t2 ";
		$sql .= "where t1.student_type_id = t2.sys_id ";
		$sql .= ") t3 ";
		$sql .= "on t0.sys_id = t3.event_id ";
		$sql .= "group by t0.sys_id ";
		$sql .= ") t ";
		$sql .= "where 1 = 1 ";
		$early = null;
		if (! empty ( $start )) {
			$earlySql = $sql . "and t.start_time<date('$start') ";
			$earlySql .= "order by t.start_time desc limit 1 ";
			$rs = $this->db->query ( $earlySql );
			if (count ( $rs ) > 0) {
				$early = $rs [0] ['d'];
			}
		}
		$late = null;
		if (! empty ( $end )) {
			$latelySql = $sql . "and t.end_time>=date('$end') ";
			$latelySql .= "order by t.end_time asc limit 1 ";
			$rs = $this->db->query ( $latelySql );
			if (count ( $rs ) > 0) {
				$late = $rs [0] ['d'];
			}
		}
		return array (
				'early' => $early,
				'late' => $late 
		);
	}
	
	/**
	 *
	 * @param int $evtId        	
	 * @return Ambigous <object, NULL>
	 */
	public function loadEvent($evtId) {
		return $this->db->load ( 't_event', array (
				'sys_id' => $evtId 
		) );
	}
	
	/**
	 *
	 * @param int $id        	
	 * @param array $fields        	
	 * @return boolean
	 */
	public function updateEvent($id, $fields) {
		return $this->db->update ( 't_event', array (
				'sys_id' => $id 
		), $fields );
	}
	
	/**
	 *
	 * @param array $locations        	
	 * @return Ambigous <number, boolean>
	 */
	public function insertLocations($locations) {
		$count = 0;
		foreach ( $locations as $l ) {
			$count += $this->db->insertByMark ( 't_location', $l );
		}
		return $count;
	}
	
	/**
	 *
	 * @param unknown $shortName        	
	 */
	public function loadLocation($shortName) {
		return $this->db->load ( 't_location', array (
				'short_name' => $shortName 
		) );
	}
	public function clearAll($name) {
		$sql = "truncate table t_" . $name;
		return $this->db->execute ( $sql, array () );
	}
	public function options($name,$filter=array()){
		$list = $this->listAll($name,$filter);
		$result = array ();
		foreach ( $list as $row ) {
			$val = $row [$name];
			$result[$val] = $val;
		}
		return $result;
	}
	
	public function insertEvent($data){
		$this->db->insertByMark('t_event', $data);
		return $this->db->lastId();
	}
	
	public function createStdTypes($data){
		$count = 0;
		foreach ($data as $row){
			$count += $this->db->insertByMark('t_event_student', $row);
		}
		return $count;
	}
	
	public function deleteStdById($eventId){
		$sql = "delete from t_event_student where event_id = ? ";
		return $this->db->execute($sql, array($eventId));
	}
}

?>
