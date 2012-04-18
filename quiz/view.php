<?php
include_once("../config.php");
$PAGE = "viewquiz";
$HEADER = "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";
include_once("../includes/header.php");

$ref = optional_param("ref","",PARAM_TEXT);
$days = optional_param("days",14,PARAM_INT);
$view = optional_param("view","bydate",PARAM_TEXT);

$views = array ('bydate'=>'Attempts by date', 'scoredist'=>'Score distribution', 'question'=>'Average score by question');
if($API->isOwner($ref)){
	$views['list'] = 'Detailed list';
}
$quiz = $API->getQuiz($ref);

if($quiz == null){
	echo getstring("warning.quiz.notfound");
	include_once("../includes/footer.php");
	die;
}

printf("<h1>%s</h1>",$quiz->title);

if($quiz->description != ""){
	printf("<p class='desc'>%s</p>",$quiz->description);
}

if(!$API->quizHasAttempts($ref)){
	printf("No attempts have been made on this quiz yet.");
	include_once("../includes/footer.php");
	die;
}

echo "<p>View: ";
$i = 0;
foreach($views as $k=>$v){
	if ($k == $view){
		printf("<span class='selected'>%s</span>",$v);
	} else {
		printf("<a href='?ref=%s&days=%d&view=%s'>%s</a>",$ref,$days,$k,$v);
	}
	if($i != count($views)-1){
		printf(" | ");
	}
	$i++;
}
echo "</p>";

switch ($view){
	case 'scoredist':
		include_once('views/scoredist.php');
		break;
	case 'list':
		if($API->isOwner($ref)){
			include_once('views/list.php');
		}
		break;
	case 'question':
		include_once('views/question.php');
		break;
	default:
		include_once('views/bydate.php');
		break;
}

include_once("../includes/footer.php");
?>