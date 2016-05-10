<?php
/**
 * process and record the operation which add events to personal plan
 * @author Yongjiang Zhang (86.yjzhang@gmail.com)
 * @since 18-6-2014
 */
session_start ();
require_once  dirname(__FILE__). '/../app/service/EventService.class.php';
$selected = isset ( $_SESSION ['schedule'] ) ? $_SESSION ['schedule'] : array ();
$operate = $_POST ['operate'];
$evtid = $_POST ['evtid'];
// $indexPage = $_POST ['index'];

$nextOperation = 1;

$evtService = new EventService ();
if ($operate > 0) {
	//add event
  // if ($indexPage != 'yes') {
  $evtService->logEventStat ( $evtid, 4 );
  // }
	$selected [$evtid] = 1;
  $nextOperation = - 1;
} else {
	//sub event
  // if ($indexPage != 'yes') {
  $evtService->logEventStat ( $evtid, 5 );
  // }
	unset ( $selected [$evtid] );
  $nextOperation = + 1;
}
// save updated event ids to session
$_SESSION ['schedule'] = $selected;
//indicate the next operation
echo $nextOperation;
?>
