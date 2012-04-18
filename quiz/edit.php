<?php
include_once("../config.php");
$PAGE = "editquiz";

$ref = optional_param('ref',"",PARAM_TEXT);
$q = $API->getQuizForUser($ref,$USER->userid);

if(isset($q->props['generatedby']) && $q->props['generatedby'] == 'import'){
	header('Location: '.$CONFIG->homeAddress.'quiz/import/edit.php?ref='.$ref);
	die;	
}
include_once("../includes/header.php");

?>
<h1><?php echo getstring("quiz.edit.title"); ?></h1>
<?php
if($q == null){
	echo "Quiz not found";
	include_once("../includes/footer.php");
	die;
} 
$submit = optional_param("submit","",PARAM_TEXT);
if ($submit != ""){
	$title = optional_param("title","",PARAM_TEXT);
	$description = optional_param("description","",PARAM_TEXT);
	$quizdraft = optional_param("quizdraft",0,PARAM_INT);
	
	if ($title != ""){
		
		//update quiz title	
		$API->updateQuiz($ref,$title,$quizdraft,$description);
				
		// remove quiz questions and responses
		$API->removeQuiz($q->quizid);
		
		// create the quiz object
		$quizid = $q->quizid;
	
		$noquestions = optional_param("noquestions",0,PARAM_INT);
		$quizmaxscore = 0;
		// create each question
		for ($i=1;$i<$noquestions+1;$i++){
			$qref = "q".($i);
			$questiontitle = optional_param($qref,"",PARAM_TEXT);
			if($questiontitle != ""){
				$questionid = $API->addQuestion($questiontitle);
				$API->addQuestionToQuiz($quizid,$questionid,$i);
				$questionmaxscore = 0;
				// create each response
				for ($j=1;$j<5;$j++){
					$rref = "q".($i)."r".($j);
					$mref = "q".($i)."m".($j);
					$responsetitle = optional_param($rref,"",PARAM_TEXT);
					$score= optional_param($mref,0,PARAM_INT);
					if($responsetitle != ""){
						$responseid = $API->addResponse($responsetitle,$score);
						$API->addResponsetoQuestion($questionid,$responseid,$j);
						$questionmaxscore += $score;
					}
				}
	
				//set max score for question
				$API->setProp('question', $questionid, 'maxscore', $questionmaxscore);
	
				$quizmaxscore += $questionmaxscore;
			}
		}
	
		// set the maxscore for quiz
		$API->setProp('quiz', $quizid, 'maxscore', $quizmaxscore);
		$API->setProp('quiz',$quizid,'generatedby','mquiz');
		
		// store JSON object for quiz (for caching)
		$json = json_encode($API->getQuizObject($ref));
		$API->setProp('quiz', $quizid, 'json', $json);
		
		printf("<div class='info'>%s<p>Why not <a href='%s'>try your quiz</a> out now?</p></div>", getstring("quiz.edit.saved"),$CONFIG->homeAddress."m/?preview=true#".$ref);
		include_once("../includes/footer.php");
		die;
	}
	//reload quiz (to get updated title)
	$q = $API->getQuizForUser($ref,$USER->userid);
}


$qq = $API->getQuizQuestions($q->quizid);

if ($API->quizHasAttempts($ref)){
	printf("<div class='info'>Sorry, you cannot edit this quiz as attempts have already been made on it.</div>");
	
	include_once("../includes/footer.php");
	die;
}

?>
<div id="quizform">
<form method="post" action="">
	<div class="formblock">
		<div class="formlabel"><?php echo getstring('quiz.edit.quiztitle'); ?></div>
		<div class="formfield">
			<input type="text" name="title" value="<?php echo $q->title; ?>" size="60"/><br/>
		</div>
	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class='formfield'>
			<input type="checkbox" name="quizdraft" value="1"
			<?php 
				if($q->draft == 1){
					echo "checked='checked'";
				}
			?>
			/> Save as draft only
		</div>
	</div>
	<div class="formblock">
			<div class='formlabel'>Description<br/><small>(optional)</small></div>
			<div class='formfield'>
				<textarea name="description" cols="80" rows="3" maxlength="300"><?php echo $q->description; ?></textarea><br/>
				<small>Max 300 characters, no HTML</small>
			</div>
		</div>
	<div class="formblock">
		<h2><?php echo getstring("quiz.edit.questions"); ?></h2>
	</div>
	<div id="questions">
		<?php 
			for($i=1; $i<count($qq)+1;$i++){
		?>
			<div class="formblock">
				<div class="formlabel"><?php echo getstring('quiz.edit.question'); echo " "; echo $i; ?></div>
				<div class="formfield">
					<input type="text" name="q<?php echo $i; ?>" value="<?php echo $qq[$i-1]->text; ?>" size="60"></input>
					<div class="responses">
					<div class="responsetext">Possible responses</div><div class="responsescore">Score</div>
					<?php 
						$qqr = $API->getQuestionResponses($qq[$i-1]->id);
						for($j=1; $j<5;$j++){ 
							if (isset($qqr[$j-1])){
					?>
						<div class="responsetext"><input type="text" name="<?php printf('q%dr%d',$i,$j); ?>" value="<?php echo $qqr[$j-1]->text; ?>" size="40"></input></div>
						<div class="responsescore"><input type="text" name="<?php printf('q%dm%d',$i,$j); ?>" value="<?php echo $qqr[$j-1]->score; ?>" size="5"></input></div>
					<?php 
							} else {
					?>
						<div class="responsetext"><input type="text" name="<?php printf('q%dr%d',$i,$j); ?>" value="" size="40"></input></div>
						<div class="responsescore"><input type="text" name="<?php printf('q%dm%d',$i,$j); ?>" value="0" size="5"></input></div>
					<?php
							}
						}
					?>
					</div>
				</div>
			</div>
		<?php 
			}
		?>
		

	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class="formfield"><input type="button" name="addquestion" value="<?php echo getstring("quiz.edit.add"); ?>" onclick="addQuestion()"/></div>
	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class="formfield"><input type="submit" name="submit" value="<?php echo getstring("quiz.edit.submit.button"); ?>"></input></div>
	</div>
	<input type="hidden" id="noquestions" name="noquestions" value="<?php echo count($qq); ?>">
</form>
</div>
<?php 
include_once("../includes/footer.php");
?>