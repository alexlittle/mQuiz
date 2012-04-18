<?php

$attempts = $API->getQuizAttempts($ref,array('days'=>$days));


foreach ($attempts as $a){
	echo "<div id='".$ref."' class='attemptlist'>";
	echo "<div class='attemptdate'>".date('D d M Y H:i',strtotime($a->submitdate))."</div>";
	echo "<div class='attemptname'>";
	echo $a->firstname." ".$a->lastname;
	echo "</div>";
	echo "<div class='attemptscore'>".sprintf('%3d',$a->score)."%</div>";
	echo "<div style='clear:both'></div>";
	echo "</div>";
}

