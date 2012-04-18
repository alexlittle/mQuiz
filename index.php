<?php
include_once("config.php");
$PAGE = "index";
include_once("./includes/header.php");
$top10popular = $API->get10PopularQuizzes();
$top10recent = $API->get10MostRecentQuizzes();
$leaderboard = $API->getLeaderboard();
?>

<div id="start1" class="homestart">
<h3>1. Create or import a quiz</h3> 

<a href="quiz/new.php" title="Create your quiz now..."><img src="images/mquiz-create.png"/></a>

</div>

<div id="start2" class="homestart">
<h3>2. Take quizzes in your PC or smartphone browser</h3> 
<iframe width="220" height="165" src="http://www.youtube.com/embed/kvOAo06CTcI" frameborder="0" allowfullscreen></iframe>
</div>

<div id="start3" class="homestart">
<h3>3. View and track results</h3> 
<a href="quiz/view.php?ref=qtAlex4ecb84e858a86&view=question"><img src="images/mquiz-results.png"/></a>
</div>

<div style="clear:both;">
<div id="top10quizzes" class="homewidget">
<h3>10 Most Popular Quizzes</h3>
<ol>
<?php 	
	foreach ($top10popular as $t){
		echo "<li>";
		printf("<a href='./m/#%s'>%s</a>",$t->ref,$t->title);
		printf("<br/><small>(%d <a href='./quiz/view.php?ref=%s'>attempts</a>)</small>",$t->noattempts,$t->ref);
		echo "</li>";
	}
?>
</ol></div>

<div id="scoreboard" class="homewidget">
<h3>Leaderboard - Top 10</h3> 
<ol>
<?php 	
	foreach ($leaderboard as $u){
		echo "<li>";
		printf("%s %s: %3d%%",$u->firstname, $u->lastname, $u->avgscore);
		echo "</li>";
	}
?>
</ol></div>

<div id="newquizzes" class="homewidget">
<h3>10 Most Recent Quizzes</h3>
<ol>
<?php 
	foreach ($top10recent as $t){
		echo "<li>";
		printf("<a href='./m/#%s'>%s</a>",$t->ref,$t->title);
		echo "<br/><small>(added on  ".date('d M Y',strtotime($t->createdon)).")</small>";
		echo "</li>";
	}
?>
</ol>
</div>
</div>
<?php 
include_once("./includes/footer.php");
