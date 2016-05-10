<?php
/**
 * This is used for update events info by ajax invoke
 */

//check if login
session_start ();
$login = $_SESSION ['login'];
if (! $login) {
	require_once dirname ( __FILE__ ) . '/../../app/service/AdminService.class.php';
	AdminService::redirect ( "../login.php?error=1" );
}

	$eventId = trim($_POST['sys_id']);
	$data = array();
	//get data which need update from $_POST
	foreach ($_POST as $name=>$val){
		if($name==='sys_id'){
			continue;
		}
		$data[$name] = trim($val);
	}
	require_once dirname ( __FILE__ ) .'/../../app/service/EventService.class.php';
	require_once dirname ( __FILE__ ) .'/../../app/service/StudentService.class.php';
	require_once dirname ( __FILE__ ) .'/../../app/util/Format.class.php';
	$evtService = new EventService();
	$stdService = new StudentService();
	//process student_type update to t_event_student
	if(isset($data['student_type'])){
		$names = explode(",",$data['student_type']);
		$eventId = $_POST['sys_id'];
		$newStd = $stdService->searchByName($names);
		$evtService->deleteStdById($eventId);
		$std = array();
		foreach ($newStd as $row){
			$std[] = array('event_id'=>$eventId,'student_type_id'=>$row['sys_id']);
		}
		$rs = $evtService->createStdTypes($std);
		//return saved data as json format
		if(!empty($rs)&&$rs>0){
			echo json_encode(array("msg"=>"Saved.","error"=>0));
		}else{
			echo json_encode(array("msg"=>"Can not save.","error"=>1));
		}
		exit(0);
	}
	
	//process normal data update to t_event
	$yesnoConvert = array('require_booking', 'int_std_recommended', 'url_link');
	$timeConvert = array('start_time', 'end_time');
	foreach ($data as $name=>$val){
		if(in_array($name, $yesnoConvert)){
			$data[$name] = Format::zerone($val);
		}
		if($name=='event_date'){
			$data[$name] = date('Y-m-d',strtotime($val));
		}
		if(in_array($name, $timeConvert)){
			if(strpos($val, 'M') == strlen($val)-1){
				$data[$name] = substr($val, 0,strlen($val)-3).":00";
			}
		}
	}
	$rs = $evtService->updateEvent($eventId, $data);
	//return saved data as json format
	if(!empty($rs)&&$rs>0){
		echo json_encode(array("msg"=>"Saved.","error"=>0));
	}else{
		echo json_encode(array("msg"=>"Can not save.","error"=>1));
	}
?>
