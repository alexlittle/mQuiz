<?php 

include_once('extras.php');
include_once('format.php');
include_once('gift_format.php');

define("MAXSCORE", 10);

class GIFTImporter {
	
	public $quizid;
	public $quizmaxscore;
	
	public function import($questions){
		$counter = 1;
		foreach ($questions as $q){
			$maxscore = 0;
			switch ($q->qtype){
				case 'truefalse':
					$maxscore = $this->importTrueFalse($q,$counter);
					break;
				case 'multichoice':
					$maxscore = $this->importMultichoice($q,$counter);
					break;
				case 'shortanswer':
					$maxscore = $this->importShortAnswer($q,$counter);
					break;
				case 'numerical':
					$maxscore = $this->importNumerical($q,$counter);
					break;
				case 'essay':
					$maxscore = $this->importEssay($q,$counter);
					break;
			}
			$counter++;
			$this->quizmaxscore += $maxscore;
		}
	}
	
	private function importTrueFalse($q,$qcount){
		global $API;
		
		$questionid = $API->addQuestion($q->questiontext);
		$API->addQuestionToQuiz($this->quizid,$questionid,$qcount);
		if($q->correctanswer == true){
			$responseid = $API->addResponse('True',MAXSCORE);
			$API->addResponsetoQuestion($questionid,$responseid,1);
			$API->setProp('response', $responseid, 'feedback', $q->feedbacktrue['text']);
			$responseid = $API->addResponse('False',0);
			$API->addResponsetoQuestion($questionid,$responseid,2);
			$API->setProp('response', $responseid, 'feedback', $q->feedbackfalse['text']);
		} else {
			$responseid = $API->addResponse('True',0);
			$API->addResponsetoQuestion($questionid,$responseid,1);
			$API->setProp('response', $responseid, 'feedback', $q->feedbacktrue['text']);
			$responseid = $API->addResponse('False',MAXSCORE);
			$API->addResponsetoQuestion($questionid,$responseid,2);
			$API->setProp('response', $responseid, 'feedback', $q->feedbackfalse['text']);
		}
		
		$API->setProp('question', $questionid, 'maxscore', MAXSCORE);
		$API->setProp('question', $questionid, 'type', 'multichoice');
		return 10;
	}
	
	private function importMultichoice($q,$qcount){
		global $API;
		$questionid = $API->addQuestion($q->questiontext);
		$API->addQuestionToQuiz($this->quizid,$questionid,$qcount);
		$no_correct_answers = 0;
		for($i=0; $i<count($q->answer); $i++){
			if($q->fraction[$i] == true){
				$no_correct_answers++;
			} 
		}
		for($i=0; $i<count($q->answer); $i++){
			if($q->fraction[$i] == true){
				$score = 10/$no_correct_answers;
				$responseid = $API->addResponse($q->answer[$i]['text'],$score);
				$API->addResponsetoQuestion($questionid,$responseid,$i+1);
				$API->setProp('response', $responseid, 'feedback', $q->feedback[$i]['text']);
			} else {
				$responseid = $API->addResponse($q->answer[$i]['text'],0);
				$API->addResponsetoQuestion($questionid,$responseid,$i+1);
				$API->setProp('response', $responseid, 'feedback', $q->feedback[$i]['text']);
			}
		}
		$API->setProp('question', $questionid, 'maxscore', MAXSCORE);
		if($no_correct_answers>1) {
			$API->setProp('question', $questionid, 'type', 'multiselect');
		} else {
			$API->setProp('question', $questionid, 'type', 'multichoice');
		}
		return 10;
	}
	
	private function importEssay($q,$qcount){
		global $API;
		$questionid = $API->addQuestion($q->questiontext);
		$API->addQuestionToQuiz($this->quizid,$questionid,$qcount);
		$API->setProp('question', $questionid, 'maxscore', MAXSCORE);
		$API->setProp('question', $questionid, 'type', 'essay');
		return MAXSCORE;
	}
	
	private function importShortAnswer($q,$qcount){
		global $API;
		
		$questionid = $API->addQuestion($q->questiontext);
		$API->addQuestionToQuiz($this->quizid,$questionid,$qcount);
		$type = 'shortanswer';
		
		$divider = 0;
		for($i=0; $i<count($q->answer); $i++){
			$a = explode('-&gt;', $q->answer[$i]);
			if(isset($a[0]) && isset($a[1]) && $a[0] != ""){
				$divider++;
				$type = 'matching';
			}
		}		
		
		for($i=0; $i<count($q->answer); $i++){
			$pos = strpos($q->answer[$i], '-&gt;');
			if($pos !== false){
				$a = explode('-&gt;', $q->answer[$i]);
				if(isset($a[0]) && isset($a[1]) && $a[0] != ""){
					$score = MAXSCORE/$divider;
				} else {
					$score = 0;
				}
			} else {
				$score = 10;
			}
			$responseid = $API->addResponse($q->answer[$i],$score);
			$API->addResponsetoQuestion($questionid,$responseid,$i+1);
			$API->setProp('response', $responseid, 'feedback', $q->feedback[$i]['text']);
		}
		$API->setProp('question', $questionid, 'maxscore', MAXSCORE);
		$API->setProp('question', $questionid, 'type', $type);
		return 10;
	}
	
	private function importNumerical($q,$qcount){
		global $API;
		$questionid = $API->addQuestion($q->questiontext);
		$API->addQuestionToQuiz($this->quizid,$questionid,$qcount);
		$type = 'numerical';
		for($i=0; $i<count($q->answer); $i++){
			$score = 10*$q->fraction[$i];
			$responseid = $API->addResponse($q->answer[$i],$score);
			$API->addResponsetoQuestion($questionid,$responseid,$i+1);
			$API->setProp('response', $responseid, 'feedback', $q->feedback[$i]['text']);
			// add the tolerance for this answer
			$API->setProp('response', $responseid, 'tolerance', $q->tolerance[$i]);
		}
		$API->setProp('question', $questionid, 'maxscore', MAXSCORE);
		$API->setProp('question', $questionid, 'type', $type);
		return 10;
	}
	
}

?>