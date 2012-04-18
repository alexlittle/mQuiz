<?php 
	$avgscores = $API->getQuizAvgResponseScores($ref);
?>
<script type="text/javascript">
google.load("visualization", "1", {
	packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Question Ref');
		data.addColumn('number', 'Average Score');
		data.addRows(<?php echo count($avgscores); ?>);

		<?php
			$c = 0;
			foreach ($avgscores as $k=>$v){
				printf("data.setValue(%d, 0, '%s');", $c,addslashes($v->questiontext));
				printf('data.setValue(%d, 1, %f);', $c,$v->avgscore);
				$c++;
			}
		?>
		var chart = new google.visualization.BarChart(document.getElementById('bar_div<?php echo $ref; ?>'));
		chart.draw(data, {
				width: 900, 
				height: 600,
				hAxis: {minValue:0},
				vAxis: {title: 'Question'},
				chartArea:{left:200,top:50,width:"60%",height:"75%"}
		});
}
</script>
<div id="bar_div<?php echo $ref; ?>"></div>