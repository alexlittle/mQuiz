<?php 

$attempts = $API->getQuizAttemptsSummary($ref,array('days'=>$days));
$summary = array();
$date = mktime(0,0,0,date('m'),date('d'),date('Y'));
$date = $date - ($days*86400);
for($c = 0; $c <$days+1; $c++){
	$tempc =  date('j-M-Y',$date);
	$summary[$tempc] = 0;
	$date = $date + 86400;
}

foreach ($attempts as $a){
	$summary[$a->displaydate] = $a->no;
}
?>
<script type="text/javascript">

// Load the Visualization API and the piechart package.
google.load('visualization', '1.0', {
'packages':['corechart']});

	// Set a callback to run when the Google Visualization API is loaded.
	google.setOnLoadCallback(drawChart);
	function drawChart() {

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Date');
		data.addColumn('number', 'Total');
		data.addRows(<?php echo count($summary); ?>);
		<?php
			$count = 0;
			foreach($summary as $k=>$v){
				printf("data.setValue(%d,%d,'%s');", $count, 0, $k);
				printf("data.setValue(%d,%d,%d);", $count, 1, $v);
				$count++;
			}
		?>

        var chart = new google.visualization.LineChart(document.getElementById('attempts_chart_div'));
        chart.draw(data, {	width: 800, 
                			height: 400,
                			vAxis: {title: 'No attempts'},
                			pointSize:5,
                			chartArea:{left:50,top:50,width:"80%",height:"75%"}
							});
      }
</script>

<div id="attempts_chart_div" class="graph"><?php echo getstring('warning.graph.unavailable');?></div>
