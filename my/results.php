<?php
include_once("../config.php");
$PAGE = "myresults";
include_once("../includes/header.php");
$results = $API->getMyQuizScores();

?>
<h1><?php echo getstring("myresults.title");?></h1>

<?php 

if(count($results) == 0){
	echo "<div class='info'>";
	echo getstring("myresults.none");
	echo "</div>";
} else {
	echo "<div id='title' class='quizlist'>";
	
	echo "<div style='clear:both'></div>";
	echo "<div class='quiztitle'>&nbsp;</div>";
	echo "<div class='quizcell'>Attempts</div>";
	echo "<div class='quizcell'>Highest Score</div>";
	echo "<div class='quizcell'>Lowest score</div>";
	echo "<div class='quizcell'>Average Score</div>";
	echo "<div class='quizcell'>Ranking</div>";
	echo "<div style='clear:both'></div>";
	echo "</div>";
	
	foreach ($results as $r){
		echo "<div id='".$r->ref."' class='quizlist'>";
		echo "<div class='quiztitle'><a href='".$CONFIG->homeAddress."quiz/view.php?ref=".$r->ref."'>".$r->title."</a></div>";
		echo "<div class='quizcell'>".$r->noattempts."</div>";
		echo "<div class='quizcell'>".sprintf('%3d',$r->maxscore)."%</div>";
		echo "<div class='quizcell'>".sprintf('%3d',$r->minscore)."%</div>";
		echo "<div class='quizcell'>".sprintf('%3d',$r->avgscore)."%</div>";
		$rank = $API->getRanking($r->ref, $USER->userid);
		echo "<div class='quizcell'>".$rank['myrank']."</div>";
		echo "<div style='clear:both'></div>";
		echo "</div>";
	}
}
?>
<small>Note: Results are only shown for quizzes that you did not create</small>
<?php 
include_once("../includes/footer.php");
?>
