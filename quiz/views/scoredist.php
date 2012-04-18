<?php 
$scores = $API->getQuizScores($ref);
?>
<script type="text/javascript">
google.load("visualization", "1", {
	packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Score');
		data.addColumn('number', 'NoScores');
		data.addRows(<?php echo count($scores)?>);

			<?php
				$c = 0;
				foreach ($scores as $k=>$v){
					printf("data.setValue(%d, 0, 'scored %d %%');", $c,$k);
					printf('data.setValue(%d, 1, %d);', $c,$v);
					$c++;
				}
				
			?>
	
			var chart = new google.visualization.PieChart(document.getElementById('chart_div<?php echo $ref; ?>'));
			chart.draw(data, {
					width: 800, 
					height: 400, 
					chartArea:{left:50,top:50,width:"80%",height:"75%"}
				});
		}
		</script>
		<div id="chart_div<?php echo $quiz->ref; ?>"><?php echo getstring('warning.graph.unavailable');?></div>