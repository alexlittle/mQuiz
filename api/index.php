<?php 
include_once("../config.php");

$format = optional_param("format","plain",PARAM_TEXT);

if($format == 'json'){
	header('Content-type: application/json; charset=UTF-8');
} else {
	header("Content-type:text/plain;charset:utf-8");
}


$method = optional_param("method","",PARAM_TEXT);
$username = optional_param("username","",PARAM_TEXT);
$password = optional_param("password","",PARAM_TEXT);


$response = new stdClass();

/*
 * Methods with no login required
 */

if($method == 'register'){
	$email = optional_param("email",$username,PARAM_TEXT);
	$passwordAgain = optional_param("passwordagain","",PARAM_TEXT);
	$firstname = optional_param("firstname","",PARAM_TEXT);
	$lastname = optional_param("lastname","",PARAM_TEXT);
	
	if ($email == ""){
		$response->error = "Enter your email";
	} else	if(!preg_match("/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/i", $email) ) {
		$response->error = "Invalid email address format";
	} else if (strlen($password) < 6){ // check password long enough
		$response->error = "Your password must be 6 characters or more";
	} else if ($password != $passwordAgain){ // check passwords match
		$response->error = "Your passwords don't match";
	} else if ($firstname == ""){
		$response->error = "Enter your firstname";
	} else if ($lastname == ""){
		$response->error = "Enter your lastname";
	} else {
		if($API->checkUserExists($email)){
			$response->error = "Email already registered";
		} else {
			$API->addUser($email, $password, $firstname, $lastname, $email);
			$m = new Mailer();
			$m->sendSignUpNotification($firstname." ".$lastname);
	
			$login = userLogin($username,$password);
			$response->login = $login;
			$response->hash = md5($password);
			$response->name = $USER->firstname + " "+ $USER->lastname;
		}
	}
}


if($method == 'login'){
	$login = userLogin($username,$password);
	if($login){
		$response->login = $login;
		$response->hash = md5($password);
		$response->name = $USER->firstname ." " .$USER->lastname;
	} else {
		$response->error = "Login failed";
	}
	
}

if($method == 'search'){
	$t = optional_param("t","",PARAM_TEXT);
	if($t == ""){
		$response->error = "No search terms provided";
	} else {
		$response = $API->searchQuizzes($t);
	}
}

/*
* Methods with login required
*/
if ($method != "search" && $method != "register" && $method != "login"){
	if (!userLogin($username,$password,false)){
		$response->login = false;
	} else {
		
		if($method == 'list'){
			$quizzes = $API->getQuizzes();
		
			$page = curPageURL();
			if(endsWith($page,'/')){
				$url_prefix = $page;
			} else {
				$url_prefix = dirname($page)."/";
			}
		
			$response = array();
			foreach($quizzes as $q){
				if(!$q->quizdraft){
					$o = array(	'id'=>$q->ref,
									'name'=>$q->title,
									'url'=>$url_prefix."?format=json&method=getquiz&ref=".$q->ref);
					array_push($response,$o);
				}
			}
		}
		
		if($method == 'suggest'){
			$response = $API->suggestQuizzes();
		}
		
		if($method == 'getquiz'){
			$ref = optional_param('ref','',PARAM_TEXT);
			$quiz = $API->getQuiz($ref);
			if($quiz == null){
				$response->error = "Quiz not found";
			} else if($quiz->quizdraft == 1 && !$API->isOwner($ref)){
				$response->error = "Quiz not available for download";
			} else {
				$response = $API->getQuizObject($ref);
			}
		}
		
		if($method == 'submit'){
			$content = optional_param("content","",PARAM_TEXT);
			if($content == ""){
				$response->error = "no content";
			} else {
				$json = json_decode(stripslashes($content));
				// only save results if not owner
				if(!$API->isOwner($json->quizid)){
					saveResult($json,$username);
					$ranking = $API->getRanking($json->quizid, $USER->userid);
					$response->rank = $ranking['myrank'];
				}
				$response->result = true;
			}
		}
	}
}

/*
 * Output the response
 */

echo json_encode($response);

writeToLog("info","pagehit",$_SERVER["REQUEST_URI"]." method: ".$method);


function curPageURL() {
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function endsWith($haystack, $needle){
	$length = strlen($needle);
	$start  = $length * -1; //negative
	return (substr($haystack, $start) === $needle);
}

function saveResult($json,$username){
	global $API;
	try{
		if (isset($json->quizid)){
			$quiz = $API->getQuiz($json->quizid);
		} else {
			return false;
		}
		
		$qa = new QuizAttempt();
		$qa->quizref = $json->quizid;
		$qa->username = $json->username;
		$qa->maxscore = $json->maxscore;
		$qa->userscore = $json->userscore;
		$qa->quizdate = $json->quizdate;
		$qa->submituser = $username;
		
		// insert to quizattempt
		$newId = $API->insertQuizAttempt($qa);
		
		$responses = $json->responses;
		foreach ($responses as $r){
			$qar = new QuizAttemptResponse();
			$qar->qaid = $newId;
			$qar->userScore = $r->score;
			$qar->questionRef = $r->qid;
			$qar->text = $r->qrtext;
			$API->insertQuizAttemptResponse($qar);
		}
		return true;
	} catch (Exception $e){
		return false;
	}
}
?>