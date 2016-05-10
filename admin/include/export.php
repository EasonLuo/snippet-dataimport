<?php

$ids = $_POST['event_id'];
$ids = is_array($ids) ? $ids : array($ids);
require_once dirname(__FILE__). '/../../app/service/EventService.class.php';
require_once dirname(__FILE__). '/../../app/calendar/Calendar.php';
$evtService = new EventService();
$events = $evtService->findExportEvents($ids);
print_r($events);
$cal = new Calendar();
foreach ($events as $event){
	$cal->addEvent($event);
}
$str = $cal->build();

$file = fopen("../export.ics", "w+");
fwrite($file, $str);
fclose($file);
?>
