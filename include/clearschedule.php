<?php
/**
 * This is used for
 */
session_start();
$selected = $_SESSION['schedule'];
require_once dirname(__FILE__). '/../app/service/EventService.class.php';
require_once dirname(__FILE__). '/../app/service/AdminService.class.php';
if(!$selected||count($selected)===0){
	AdminService::redirect ( "../myPlan.php?error=9" );
}
$evtService = new EventService();
$count = $evtService->updateStatByEvent(array_keys($selected), array('added'=>-1));
if($count>0){
	unset($_SESSION['schedule']);
}

AdminService::redirect ( "../myPlan.php" );

?>
