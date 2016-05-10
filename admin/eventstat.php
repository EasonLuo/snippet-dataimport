<?php
/**
 * @author Eason Luo (trueluo1987@gmail.com)
 */
session_start ();
$login = $_SESSION ['login'];
if (! $login) {
	require_once dirname(__FILE__).'/../app/service/AdminService.class.php';
	AdminService::redirect ( "login.php?error=1" );
}

require_once dirname(__FILE__).'/../app/service/EventService.class.php';
require_once dirname(__FILE__).'/../app/util/View.php';
$period = array('start'=>date('Y-m-d',strtotime('-2 month',time())),'end'=>date('Y-m-d',time()));
function getStatData ($period,$type){
	$evtService = new EventService();
	$stat = $evtService->stat($period, $type);
	$temp = array();
	foreach ($stat as $row){
		$temp[$row['dt']] = $row['cnt'];
	}
	$data = array();
	$days = getDays($period);
	for ($i = 1;$i<=$days;$i++){
		$data[date('d-m-Y',strtotime('+'.($i-$days).' day'))] = 0;
	}
	foreach ($temp as $k=>$v){
		$data[$k] += $data[$k]+$v;
	}
	$keys = array();
	foreach (array_keys($data) as $k){
		$keys[] = substr($k, 0,5);
	}
	$newkeys = json_encode($keys);
	$values = json_encode(array_values($data));
	return array($newkeys,$values);
}

function getDays($period){
	$start = strtotime($period['start']);
	$end = strtotime($period['end']);
	$days = round(($end-$start)/86400);
	return $days;
	
}
list($viewedK_m,$viewedV_m)     = getStatData($period,1);
list($viewedK_d,$viewedV_d)     = getStatData($period,3);
list($exportedK_m,$exportedV_m) = getStatData($period,2);
list($exportedK_d,$exportedV_d) = getStatData($period,4);
list($printedK_d,$printedV_d)   = getStatData($period,5);

$evtService = new EventService();
$exportstat = $evtService->exportstat($period);
$printstat = $evtService->printstat($period);
$eventstat = $evtService->eventstat($period);

?>
<div class="close_button" onclick="hide('divEventStat')" style="background: url(../images/button-close-hover.png);"></div>
<div id='main'>
	<div id='usage'>
	<h2 style="margin-bottom:20px;">Event Planner Analytics 2014</h2>
	<div id='desc'>
	<span id='viewed_m'><label for='viewed'>View (Mobile)</label></span>
	<span id='exported_m'><label for='exported'>Export (Mobile)</label></span>
	<span id='viewed_d'><label for='viewed'>View (Desktop)</label></span>
	<span id='exported_d'><label for='exported'>Export (Desktop)</label></span>
	<span id='printed_d'><label for='printed'>Print (Desktop)</label></span>
	</div>
	<canvas id='chart' width='1000px' height='350px'></canvas>
	</div>

	<div id='print' style="width:100%;float:left;">
	<div id='print-title' style="margin-bottom:20px;">
	<h2>Event Planner Analytics 2014</h2>
	</div>
	<div>
	<table>
		<thead>
		<tr>
			<th style="padding:5px;">Date</th>
			<th style="padding:5px;">Export via Mobile</th>
			<th style="padding:5px;">Export via Desktop</th>
			<th style="padding:5px;">Print via Mobile</th>
			<th style="padding:5px;">Print via Desktop</th>
		</tr>
		</thead>
		<tbody>
<?php
$sum_export_m = 0;
$sum_export_d = 0;
$sum_print_m  = 0;
$sum_print_d  = 0;
foreach ($eventstat as $item){?>
		<tr>
			<td><?php echo View::emp(date("Y-m-d", strtotime($item['dt'])))?></td>
			<!-- <td><?php// echo View::emp($item['dt'])?></td> -->
			<td><?php echo View::emp($item['e_mobile'])?></td>
			<td><?php echo View::emp($item['e_pc'])?></td>
			<td><?php echo View::emp($item['p_mobile'])?></td>
			<td><?php echo View::emp($item['p_pc'])?></td>
		</tr>

    <?php
$sum_export_m += $item['e_mobile'];
$sum_export_d += $item['e_pc'];
$sum_print_m += $item['p_mobile'];
$sum_print_d += $item['p_pc'];
 }?>
<tr>
  <td>Total</td>
  <td><?php echo View::num($sum_export_m)?></td>
  <td><?php echo View::num($sum_export_d)?></td>
  <td><?php echo View::num($sum_print_m)?></td>
  <td><?php echo View::num($sum_print_d)?></td>
</tr>
		</tbody>
	</table>
	</div>
	</div>

	</div>
</div>
<script src='../js/Chart.js'></script>
<script type='text/javascript'>
(function(){
	var options = {

	    ///Boolean - Whether grid lines are shown across the chart
	    scaleShowGridLines : true,

	    //String - Colour of the grid lines
	    scaleGridLineColor : "rgba(0,0,0,.1)",

	    //Number - Width of the grid lines
	    scaleGridLineWidth : 1,

	    //Boolean - Whether the line is curved between points
	    bezierCurve : true,

	    //Number - Tension of the bezier curve between points
	    bezierCurveTension : 0.4,

	    //Boolean - Whether to show a dot for each point
	    pointDot : true,

	    //Number - Radius of each point dot in pixels
	    pointDotRadius : 1,

	    //Number - Pixel width of point dot stroke
	    pointDotStrokeWidth : 1,

	    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
	    pointHitDetectionRadius : 20,

	    //Boolean - Whether to show a stroke for datasets
	    datasetStroke : true,

	    //Number - Pixel width of dataset stroke
	    datasetStrokeWidth : 2,

	    //Boolean - Whether to fill the dataset with a colour
	    datasetFill : true,

	    //String - A legend template
	    legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"

	};
	var data = {
        labels: <?php echo $viewedK_m?>,
		    datasets: [
		        {
		            label: "View (Mobile)",
		            fillColor: "rgba(220,220,220,0)",
		            strokeColor: "rgba(0,184,252,1)",
		            pointColor: "rgba(0,184,252,1)",
		            pointStrokeColor: "#fff",
		            pointHighlightFill: "#666",
		            pointHighlightStroke: "rgba(220,220,220,1)",
		            data: <?php echo $viewedV_m?>
		        },
		        {
		            label: "Export (Mobile)",
		            fillColor: "rgba(151,187,205,0)",
		            strokeColor: "rgba(26,194,33,1)",
		            pointColor: "rgba(26,194,33,1)",
		            pointStrokeColor: "#fff",
		            pointHighlightFill: "#666",
		            pointHighlightStroke: "rgba(220,220,220,1)",
		            data: <?php echo $exportedV_m?>
		        },
            {
		            label: "View (Desktop)",
		            fillColor: "rgba(220,220,220,0)",
		            strokeColor: "rgba(178,27,27,1)",
		            pointColor: "rgba(178,27,27,1)",
		            pointStrokeColor: "#fff",
		            pointHighlightFill: "#666",
		            pointHighlightStroke: "rgba(220,220,220,1)",
		            data: <?php echo $viewedV_d?>
            },
             {
		            label: "Export (Desktop)",
		            fillColor: "rgba(250,186,100,0)",
		            strokeColor: "rgba(255,204,0,1)",
		            pointColor: "rgba(255,204,0,1)",
		            pointStrokeColor: "#fff",
		            pointHighlightFill: "#666",
		            pointHighlightStroke: "rgba(220,220,220,1)",
		            data: <?php echo $exportedV_d?>
            },
            {
		            label: "Print (Desktop)",
		            fillColor: "rgba(250,186,100,0)",
		            strokeColor: "rgba(200,61,255,1)",
		            pointColor: "rgba(200,61,255,1)",
		            pointStrokeColor: "#fff",
		            pointHighlightFill: "#666",
		            pointHighlightStroke: "rgba(220,220,220,1)",
		            data: <?php echo $printedV_d?>
		        }
		    ]
		};
	var ctx = document.getElementById('chart').getContext('2d');
	var chart = new Chart(ctx).Line(data,{});
})();
</script>
