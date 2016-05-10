<?php
/**
 * This is used for events create in dashboard
 */

//check if login
session_start ();
$login = $_SESSION ['login'];
if (! $login) {
	require_once dirname ( __FILE__ ) . '/../../app/service/AdminService.class.php';
	AdminService::redirect ( "../login.php?error=1" );
}

/**
 * get data from $_POST
 * @param array $names
 * @return multitype:unknown
 */
function fetch($names = array()) {
	$data = array ();
	foreach ( $names as $name ) {
		$data [$name] = $_POST [$name];
	}
	return $data;
}

$item = fetch ( array (
		'event_name',
		'description',
		'provider',
		'category',
		'location',
		'requested_venue',
		'require_booking',
		'int_std_recommended',
		'event_date',
		'start_time',
		'end_time',
		'student_type',
		'url_link' 
) );
//create failed if event_name is empty
if(empty($item['event_name'])){
	echo -1;
	exit(0);
}
require_once dirname ( __FILE__ ) .'/../../app/service/EventService.class.php';
require_once dirname ( __FILE__ ) .'/../../app/service/StudentService.class.php';
require_once dirname ( __FILE__ ) .'/../../app/util/Format.class.php';
$evtService = new EventService();
$yesnoConvert = array('require_booking', 'int_std_recommended', 'url_link');
$timeConvert = array('start_time', 'end_time');
//format data
foreach ($item as $name=>$val){
	if(in_array($name, $yesnoConvert)){
		$item[$name] = Format::zerone($val);
	}
	if($name=='event_date'){
		$item[$name] = date('Y-m-d',strtotime($val));
	}
	if(in_array($name, $timeConvert)){
		if(strpos($val, 'M') == strlen($val)-1){
			$item[$name] = substr($val, 0,strlen($val)-3).":00";
		}
	}
}
$item['event_status'] = 1;
$item['account_id'] = $login['sys_id'];
$std = explode("|", $item['student_type']);
unset($item['student_type']);
//do insert event
$newId = $evtService->insertEvent($item);
$stdService = new StudentService();
//process insert student_type to t_event_student
$stdtype = $stdService->searchByName($std);
$evt4std = array();
foreach ($stdtype as $row){
	$evt4std[] = array('event_id'=>$newId,'student_type_id'=>$row['sys_id']);
}
$evtService->createStdTypes($evt4std);
//return new_event_id
echo $newId;

