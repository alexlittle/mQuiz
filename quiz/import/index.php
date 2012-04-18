<?php 
include_once("../../config.php");
$PAGE = "import";
include_once("../../includes/header.php");
global $IMPORT_INFO;
$IMPORT_INFO = array();

echo "<h1>Create quiz in GIFT format</h1>";

$submit = optional_param("submit","",PARAM_TEXT);
$title = optional_param("title","",PARAM_TEXT);
$quizdraft = optional_param('quizdraft',0,PARAM_INT);
$description = optional_param("description","",PARAM_TEXT);
$tags = optional_param("tags","",PARAM_TEXT);
$content = optional_param("content","",PARAM_TEXT);
$format = optional_param("format","gift",PARAM_TEXT);

$supported_qtypes = array('truefalse','multichoice','essay','shortanswer','numerical');
if ($submit != ""){
	
	if($title == ""){
		array_push($MSG,getstring('import.quiz.error.notitle'));
	}
	if($content == ""){
		array_push($MSG,getstring('import.quiz.error.nocontent'));
	}
	$questions_to_import = array();
	
	if($format == 'gift'){
		include_once('./gift/import.php');
		$import = new qformat_gift();
			
		$lines = explode("\n",$content);
		$questions = $import->readquestions($lines);
		foreach($questions as $q){
			if (in_array($q->qtype, $supported_qtypes)){
				array_push($questions_to_import,$q);
			} else {
				if($q->qtype != 'category'){
					array_push($IMPORT_INFO, $q->qtype." question type not yet supported ('".$q->questiontext."')");
				}
			}	
		}
	}
	
	if(count($questions_to_import) == 0){
		array_push($MSG,getstring('import.quiz.error.nosuppportedquestions'));
	}
	
	if(count($MSG) == 0){
		// now do the actual import
		if($format == 'gift'){
			// setup quiz with default props
			$quizid = $API->addQuiz($title,$quizdraft,$description);
			$API->setProp('quiz',$quizid,'generatedby','import');
			$API->setProp('quiz',$quizid,'content',$content);
			$importer = new GIFTImporter();
			$importer->quizid = $quizid;
			$importer->import($questions_to_import);
			
			$API->setProp('quiz', $quizid, 'maxscore', $importer->quizmaxscore);
		}
	
		$q = $API->getQuizById($quizid);
		// store JSON object for quiz (for caching)
		$json = json_encode($API->getQuizObject($q->ref));
		$API->setProp('quiz', $quizid, 'json', $json);
		
		printf("<div class='info'>%s<p>Why not <a href='%s'>try your quiz</a> out now?</p></div>", getstring("quiz.new.saved"),$CONFIG->homeAddress."m/#".$q->ref);
		if(!empty($IMPORT_INFO)){
			echo "<div class='info'>Some of your questions were not imported:<ul>";
			foreach ($IMPORT_INFO as $info){
				echo "<li>".$info."</li>";
			}
			echo "</ul></div>";
		}
		// send mail to owner
		$m = new Mailer();
		$m->sendQuizCreated($USER->email,$USER->firstname, $title, $q->ref);
		include_once("../../includes/footer.php");
		die;
	}
}

if(!empty($MSG)){
	echo "<div class='warning'><ul>";
	foreach ($MSG as $err){
		echo "<li>".$err."</li>";
    }
    echo "</ul></div>";
}
?>

<form method="post" action="">
	<div class="formblock">
		<div class="formlabel"><?php echo getstring('import.quiz.title'); ?></div>
		<div class="formfield"><input type="text" name="title" size="60" value="<?php echo $title; ?>"></input></div>
	</div>
	<div id="options" class="formblock">
		<div class='formlabel'>&nbsp;</div>
		<div class='formfield'>
			<input type="checkbox" name="quizdraft" value="1"
			<?php 
				if($quizdraft == 1){
					echo "checked='checked'";
				}
			?>
			/> Save as draft only
		</div>
	</div>
	<div class="formblock">
		<div class='formlabel'>Description<br/><small>(optional, max 300 characters, no HTML)</small></div>
		<div class='formfield'>
			<textarea name="description" cols="80" rows="3" maxlength="300"><?php echo $description; ?></textarea>
		</div>
	</div>
	<div class="formblock">
		<div class="formlabel">Quiz Questions<br/><small>(enter in <a target='_blank' href='http://microformats.org/wiki/gift'>GIFT format</a>)</small></div>
		<div class="formfield"><textarea name="content" cols="100" rows="20"><?php echo stripslashes($content); ?></textarea></div>
	</div>
	<div class="formblock">
		<div class="formlabel">&nbsp;</div>
		<div class="formfield"><input type="submit" name="submit" value="<?php echo getstring("import.quiz.add.button"); ?>"></input></div>
	</div>
	
</form>


<?php 
include_once("../../includes/footer.php");
?>