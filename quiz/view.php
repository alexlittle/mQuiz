<?php
include_once("../config.php");
$PAGE = "viewquiz";
$HEADER = "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";
include_once("../includes/header.php");

$ref = optional_param("ref","",PARAM_TEXT);
$days = optional_param("days",14,PARAM_INT);
$view = optional_param("view","bydate",PARAM_TEXT);
$groupid = optional_param("groupid",0,PARAM_INT);

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

printf("<h1>%s</h1>",$quiz->quiztitle);

if($quiz->quizdescription != ""){
	printf("<p class='desc'>%s</p>",$quiz->quizdescription);
}

if(!$API->quizHasAttempts($ref)){
	printf("No attempts have been made on this quiz yet.");
	include_once("../includes/footer.php");
	die;
}

// get user groups
$groups = $API->getUserGroupQuiz($quiz->quizid);
if(count($groups)>0){
	printf("<form method='get' action=''>");
	printf("<input type='hidden' name='ref' value='%s'>",$ref);
	printf("<input type='hidden' name='view' value='%s'>",$view);
	echo "Select group: <select name='groupid'>";
	if($groupid == 0){
		echo "<option value='0' selected='selected'>All</option>";
	} else {
		echo "<option value='0'>All</option>";
	}
	foreach($groups as $group){
		if($groupid == $group->groupid){
			printf("<option value='%d' selected='selected'>%s</option>",$group->groupid,$group->groupname);
		} else {
			printf("<option value='%d'>%s</option>",$group->groupid,$group->groupname);
		}
	}
	echo "</select>";
	echo "<input type='submit' value='Go'/>";
	echo "</form>";
}

echo "<p>";
$i = 0;
foreach($views as $k=>$v){
	if ($k == $view){
		printf("<span class='selected'>%s</span>",$v);
	} else {
		printf("<a href='?ref=%s&days=%d&view=%s&groupid=%d'>%s</a>",$ref,$days,$k,$groupid,$v);
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
