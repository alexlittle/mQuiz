

<?php

$attempts = $API->getQuizAttempts($ref,array('days'=>$days));

foreach ($attempts as $a){
	echo "<div id='".$a->id."' class='attemptlist' name='al' title='Click for full details'>";
	echo "<div class='attemptdate'>".date('D d M Y H:i',strtotime($a->submitdate))."</div>";
	echo "<div class='attemptname'>";
	echo $a->firstname." ".$a->lastname;
	echo "</div>";
	echo "<div class='attemptscore'>".sprintf('%3d',$a->score)."%</div>";
	echo "<div style='clear:both'></div>";
	
	echo "<div id='d".$a->id."'>";
	$detail = $API->getQuizAttemptDetail($a->id);
	foreach($detail as $d){
		printf("<div class='attemptdetail'>");
		printf("<div class='adq'>%s</div>",$d->questiontext);
		printf("<div class='adrtext'>%s</div>",$d->responsetext);
		printf("<div class='adscore'>%.0f/%.0f</div>",$d->qarscore,$d->maxscore);
		printf("<div style='clear:both'></div>");
		printf("</div>");
	}
	echo "</div>";
	echo "</div>";
}
?>
<script type="text/javascript">

$('div[name=al]').each(function(i){
	$('#d'+$(this).attr('id')).hide();
	$(this).toggle(function(){
					var id= $(this).attr('id');
					$('#d'+id).show('blind');
				},
				function (){
					var id= $(this).attr('id');
					$('#d'+id).hide('blind');
				});
}
);


</script>



