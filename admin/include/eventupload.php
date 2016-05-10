<?php
/**
 * This is used for events upload from excel file
 */
//check if login
error_reporting(E_ALL);
session_start ();
date_default_timezone_set('Australia/Sydney');

$login = $_SESSION ['login'];
if (! $login) {
	require_once dirname(__FILE__).'/../../app/service/AdminService.class.php';
	AdminService::redirect ( "../login.php?error=1" );
}

require_once  dirname(__FILE__).'/../../app/util/PHPExcelHandler.class.php';
require_once  dirname(__FILE__).'/../../app/util/FileHelper.class.php';
require_once  dirname(__FILE__).'/../../app/model/Event.class.php';
require_once  dirname(__FILE__).'/../../app/service/EventService.class.php';
require_once  dirname(__FILE__).'/../../app/service/AdminService.class.php';
require_once  dirname(__FILE__).'/../../app/util/Format.class.php';
$service = new EventService ();
//process file upload
$file = $_FILES ['file'];
if ($file ['error'] === 0) {
	$filename = $file ['name'];
	$tmpname = $file ['tmp_name'];
	$newFileName = dirname ( __FILE__ ) . '/../../res/upload/' . $filename;
	//check if file exists
	if (file_exists ( $newFileName )) {
		$newFileName = FileHelper::rename ( $newFileName );
	}
	//save uploaded file
	if (FileHelper::upload ( $file )) {
		//create excel handler
		$handler = new PHPExcelHandler ( $newFileName, array (
      'A' => 'event_name',
			'B' => 'description',
			'C' => 'provider',
			'D' => 'category',
			'E' => 'require_booking',
			'F' => 'int_std_recommended',
			'G' => 'location',
			'H' => 'requested_venue',
			'I' => 'event_date',
			'J' => 'start_time',
			'K' => 'end_time',
			'L' => 'new_student',
			'M' => 'returning_student',
			'N' => 'undergraduate',
			'O' => 'postgraduate_coursework',
			'P' => 'postgraduate_research',
			'Q' => 'url_link'
		) );
		
		$events = array ();
		//process excel file multi-sheet
		// echo $handler->getSheetCount ();
		for($index = 0; $index < $handler->getSheetCount (); $index ++) {
			$handler->activate ( $index );
			if("reference"===$handler->getSheetName()){
				continue;
			}
			$allColumn = $handler->getHighestColumn ();
			$allRow = $handler->getHighestRow ();
			$sheetName = $handler->getSheetName ();
			//read data row by row
			for($currentRow = 2; $currentRow <= $allRow; $currentRow ++) {
				$arrEvt = array ();
				//read data column by column
				//char 'A' is 65
				$currentColumn = 65;
				$chrColumn = chr($currentColumn);
				//only process A-Z (A<=B ,AA<=B too)
				for(; $chrColumn<='Z'; $currentColumn ++) {
					$chrColumn = chr($currentColumn);
					// only accept A-R defined when PHPExcelHandler created
					if(!$handler->accept($chrColumn)){
						continue;
					}
					$val = $handler->getValueAt ( $chrColumn, $currentRow );
					$title = $handler->getTitle ( $chrColumn );
					//check if the title is valid
					if(empty($title)){
						continue;
					}
					//convert special data type
					if ($title === 'event_date') {
						$val = $handler->convertDate ( $val );
					} else if (in_array ( $title, array (
							'start_time',
							'end_time' 
					) )) {
            // echo "ttt:". date('H:i:s', strtotime($val));
            // echo "time is:". date('H:i:s', $val)."/";
						$val = $handler->convertTime ( $val );
					} else if (in_array ( $title, array (
							'require_booking',
							'int_std_recommended',
							'new_student',
							'returning_student',
							'undergraduate',
							'postgraduate_coursework',
							'postgraduate_research',
							'url_link' 
					) )) {
						$val = $handler->convertBoolean ( $val );
					} else {
						$val = $handler->convertString ( $val );
					}
					$arrEvt [$title] = $val;
				}
        // $start_time = date('H:i A', strtotime($arrEvt ['start_time']));
        echo "name:". $arrEvt ['event_name'] ."start:" . $arrEvt ['start_time'] . "-end:" . $arrEvt ['end_time'] . " | ";
        $arrEvt ['start_time'] = $arrEvt ['event_date'] . " " . $arrEvt ['start_time'];
        // print_r($arrEvt ['start_time']);
        // print_r(date('H:i A', strtotime($arrEvt ['start_time'])));
				$arrEvt ['end_time'] = $arrEvt ['event_date'] . " " . $arrEvt ['end_time'];
				$arrEvt ['event_group'] = $sheetName;
				$arrEvt ['event_status'] = 1;
				$arrEvt ['account_id'] = $login['sys_id'];
				$arrs = $service->resolve ( $arrEvt );
				//create a event entity
				$events [] = new Event ( $arrs ['event'], $arrs ['evt4std'] );
			}
		}
    // var_dump($events);
		//save events to database
		$service->importEvents ( $events );
		//redirect to dashboard when finished.
		AdminService::redirect ( "../dashboard.php?section=1" );
	} else {
		AdminService::redirect ( "../dashboard.php?error=1" );
	}
} else {
}
?>
