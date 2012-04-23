<?php
include_once("../config.php");
$PAGE = "myquizzes";

include_once("../includes/header.php");
$quizzes = $API->getQuizzesForUser($USER->userid);
?>
<h1><?php echo getstring("myquizzes.title");?></h1>
<?php
if (count($quizzes) == 0){
	echo "<div class='info'>";
	echo getstring("myquizzes.none",array($CONFIG->homeAddress."quiz/new.php"));
	echo "</div>";
} else {
	echo "<div id='th' class='quizlist'>";
	echo "<div class='quiztitle'>Quiz</div>";
	echo "<div class='quizattempts'>Attempts</div>";
	echo "<div class='quizavg'>Average Score</div>";
	echo "<div class='quizopts'></div>";
	echo "<div style='clear:both'></div>";
	echo "</div>";
}


foreach ($quizzes as $q){
	echo "<div id='".$q->ref."' class='quizlist'>";
	echo "<div class='quiztitle'><a href='".$CONFIG->homeAddress."quiz/view.php?ref=".$q->ref."'>".$q->title."</a>";
	if($q->quizdraft == 1){
		echo " (draft)";
	}
	echo "</div>";
	echo "<div class='quizattempts'>".$q->noattempts."</div>";
	echo "<div class='quizavg'>".sprintf('%3d',$q->avgscore)."%</div>";
	echo "<div class='quizopts'><small>";
	if($q->noattempts == 0){
		echo "<a href='".$CONFIG->homeAddress."quiz/edit.php?ref=".$q->ref."'>[Edit] </a>";
	}
	echo "<a href='".$CONFIG->homeAddress."quiz/delete.php?ref=".$q->ref."'>[Delete]</a>";
	echo "<a href='".$CONFIG->homeAddress."quiz/options.php?qref=".$q->ref."'> [Share]</a>";
	echo "</small></div>";
	echo "<div style='clear:both'></div>";
	echo "</div>";
}

include_once("../includes/footer.php");
?>