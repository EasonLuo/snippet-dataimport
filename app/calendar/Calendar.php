<?php
/**
 * create ical(.ics) content from events
 * @author Yongjiang Zhang (86.yjzhang@gmail.com)
 * @since 18-6-2014
 */
require_once dirname ( __FILE__ ) . '/../util/functions.php';
class Calendar {
	//define ics sections
	private static $begin = "BEGIN:VCALENDAR";
	private static $end = "END:VCALENDAR";
	private static $version = 'VERSION:2.0';
	private static $prodid = 'PRODID:-//UNSW Australia//OWeek Event Planner//EN';
	private static $uid = "UID:";
	private static $method = "METHOD:PUBLISH";
	private static $calscale = "CALSCALE:GREGORIAN";
	private static $timezone = "X-WR-TIMEZONE:Australia/Sydney";
	private static $dtstamp = "DTSTAMP:";
	private static $dtstart = "DTSTART;TZID=Australia/Sydney:";
	private static $dtend = "DTEND;TZID=Australia/Sydney:";
	private static $location = "LOCATION:";
	private static $summary = "SUMMARY:";
	private static $description = "DESCRIPTION:";
	private static $status = "STATUS:COMFIRMED";
	private static $beginEvt = "BEGIN:VEVENT";
	private static $endEvt = "END:VEVENT";
	private static $rowmax = 50;
	private static $datefmt = "Ymd";
	private static $timefmt = "His";
	
	/**
	 * contain export data
	 * @var array
	 */
	private $items;
	function __construct() {
		$this->items = array ();
	}
	public function addEvent($event) {
		$this->items [] = $event;
	}
	public function build() {
		$str = self::$begin . crlf ();
		$str .= self::$prodid . crlf ();
		$str .= self::$version . crlf ();
		$str .= self::$calscale . crlf ();
		$str .= self::$method . crlf ();
		$str .= self::$timezone . crlf ();
		
		$index = 0;
		$now = time ();
		//create formatted content from given values
		foreach ( $this->items as $item ) {
			$str .= self::$beginEvt . crlf ();
			$str .= self::$uid . self::uid () . "@" . $now  . "E" . $index . crlf ();
			$str .= self::$dtstamp . date ( self::$datefmt, $now ) . "T" . date ( self::$timefmt, $now ) . crlf ();
			$date = strtotime ( $item ['event_date'] );
			$start = strtotime ( $item ['start_time'] );
			$str .= self::$dtstart . date ( self::$datefmt, $date ) . "T" . date ( self::$timefmt, $start ) . crlf ();
      $end = $item ['start_time'] < $item ['end_time'] ? strtotime ( $item ['end_time'] ) : $start;
			$str .= self::$dtend . date ( self::$datefmt, $date ) . "T" . date ( self::$timefmt, $end ) . crlf ();
			$str .= self::$location . $item ['requested_venue'] . crlf ();
			$evtName = $item ['event_name'];
			// if (strlen ( $evtName ) > self::$rowmax) {
			// 	$evtName = brick ( $evtName, self::$rowmax, crlf () . tab () );
			// }
			$str .= self::$summary . $evtName . crlf ();
			$desc = $item ['description'];
			// if (strlen ( $desc ) > self::$rowmax) {
			// 	$desc = brick ( $desc, self::$rowmax, crlf () . tab () );
			// }
			$str .= self::$description . $desc . crlf ();
			$str .= self::$status . crlf ();
			$str .= self::$endEvt . crlf ();
		}
		
		$str .= self::$end . crlf ();
		
		return $str;
	}
	
	/**
	 * generate unique id for each exported event
	 * @return string
	 */
	public static function uid() {
		for($i = 0; $i < 4; $i ++) {
			$randAsciiNumArray = array (
					rand ( 48, 57 ),
					rand ( 65, 90 ) 
			);
			$randAsciiNum = $randAsciiNumArray [rand ( 0, 1 )];
			$randStr = chr ( $randAsciiNum );
		}
		return $randStr;
	}
}

?>
