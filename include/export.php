<?php
/**
 * export saved events as a .ics file
 * @author Eason Luo
 * @since 18-6-2014
 */
session_start();
//get all saved events
$ids = isset($_SESSION['schedule']) ? $_SESSION['schedule'] : array();
$ids = array_keys($ids);
if(count($ids)<=0){
	require_once  dirname(__FILE__).'/../app/service/AdminService.class.php';
	AdminService::redirect ( "../myPlan.php?error=9" );
}
require_once  dirname(__FILE__). '/../app/service/EventService.class.php';
require_once dirname(__FILE__). '/../app/util/FileHelper.class.php';
require_once dirname(__FILE__). '/../app/calendar/Calendar.php';

//update stat info export+1
$evtService = new EventService ();
$evtService->logExportStat();
foreach ( $ids as $evtId ) {
	$evtService->logEventStat ( $evtId, 2 );
}
$events = $evtService->findEventsById ( $ids );

//build .ics content with events data
$cal = new Calendar ();
foreach ( $events as $event ) {
	$cal->addEvent ( $event );
}
$str = $cal->build ();

//output to a stream and download
$filename = "myschedule_".date('dmY',time()).".ics";
$size = strlen($str);
header ( "Accept-Ranges: bytes" );
header ( "Accept-Length:" . $size );
header ( "Content-type: application/octet-stream;charset=utf-8" );
// header ( "Content-type: text/calendar; charset=utf-8" );
header ( "Content-Disposition: attachment; filename=$filename" );
header ('HTTP/1.0 200 OK', true, 200);
echo $str;
exit();
?>
