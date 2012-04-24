<?php
include_once("config.php");
$PAGE = "search";
include_once("./includes/header.php");

$s = optional_param("s","",PARAM_TEXT);

$results = Array();
if($s != ""){
	$results = $API->searchQuizzes($s,Array('count'=>10));
}
?>
<h1>Search</h1>

<form action="<?php echo $CONFIG->homeAddress; ?>search.php" method="get">
<input type="text" name="s" value="<?php echo $s; ?>"/>
</form>
<div id="searchresults">
<?php 
foreach($results as $r){
	echo "<div class='quizlist'>";
	printf("<a href='%sm/#%s'>%s</a>",$CONFIG->homeAddress,$r->qref,$r->quiztitle);
	if($r->quizdescription != ""){
		printf(" - <span>%s</span>",$r->quizdescription);
	}
	echo "</div>";
}
if($s != "" && count($results)==0){
	echo "No results for: ".$s;
}
?>
</div>
<?php 
include_once("./includes/footer.php");
