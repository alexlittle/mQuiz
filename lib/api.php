<?php 

define('DEFAULT,DAYS',14);

/*
 * API Class
 */
class API {
	
	private $DB = false;
	   
	/*
	 * Constructor
	 */
	function api(){
	    global $CONFIG;
	    if($this->DB){
	        return $this->DB;
	    }
	    $this->DB = mysql_connect( $CONFIG->dbhost, $CONFIG->dbuser, $CONFIG->dbpass) or die('Could not connect to server.' );
	    mysql_select_db($CONFIG->dbname, $this->DB) or die('Could not select database.');
	    mysql_set_charset('utf8',$this->DB); 
	    return $this->DB;
	}
	
	function cleanUpDB(){
		if( $this->DB != false ){
			mysql_close($this->DB);
		}
		$this->DB = false;
	}
	
	function getUser(&$user){
		$sql = "SELECT * FROM user WHERE username ='".$user->username."' LIMIT 0,1";
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($row = mysql_fetch_array($result)){
			$user->userid = $row['userid'];
			$user->username = $row['username'];
			$user->firstname = $row['firstname'];
			$user->lastname =  $row['lastname'];
			$user->email =  $row['email'];
			$user->password =  $row['password'];
		}
	}
	
	function checkUserNameNotInUse($username){
		global $USER;
		$sql = sprintf("SELECT * FROM user WHERE username='%s' AND userid != %d",$username,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($row = mysql_fetch_array($result)){
			return true;
		}
		return false;
	}
	
	function updateUser($username,$firstname,$lastname){
		global $USER;
		$username = strtolower($username);
		$sql = sprintf("UPDATE user SET username = '%s', email = '%s', firstname = '%s', lastname = '%s' WHERE userid = %d",$username,$username,$firstname,$lastname,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return false;
		}
		return true;
	}
	
	function updateUserPassword($password){
		global $USER;
		$sql = sprintf("UPDATE user SET password = md5('%s') WHERE userid = %d",$password,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return false;
		}
		return true;
	}
	
	function addUser($username,$password,$firstname,$surname,$email){
		$username = strtolower($username);
		$email = strtolower($email);
		$str = "INSERT INTO user (username,password,firstname,lastname,email) VALUES ('%s',md5('%s'),'%s','%s','%s')";
		$sql = sprintf($str,$username,$password,$firstname,$surname,$email);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return false;
		}
		return true;
	}
	
	function getUserProperties(&$user){
		$sql = "SELECT * FROM userprops WHERE userid=".$user->userid;
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$user->props[$row['propname']] = $row['propvalue'];
		}
	}
	
	function setUserProperty($userid,$name,$value){
		// first check to see if it exists already
		$sql = sprintf("SELECT * FROM userprops WHERE userid= %d AND propname='%s'",$userid,$name);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$updateSql = sprintf("UPDATE userprops SET propvalue='%s' WHERE userid= %d AND propname='%s'",$value,$userid,$name);
			$result = _mysql_query($updateSql,$this->DB);
			return;
		}
	
		$insertSql = sprintf("INSERT INTO userprops (propvalue, userid,propname) VALUES ('%s',%d,'%s')",$value,$userid,$name);
		$result = _mysql_query($insertSql,$this->DB);
	}
	
	function userValidatePassword($username,$password){
		global $USER;
		$username = strtolower($username);
		$sql = sprintf("SELECT userid FROM user WHERE username='%s' AND (password=md5('%s') OR password='%s')",$username,$password,$password);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return false;
		}
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			return true;
		}
		return false;
	}
	
	function userChangePassword($newpass){
		global $USER;
		$sql = sprintf("UPDATE user SET password = md5('%s') WHERE userid=%d",$newpass,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		if($result){
			return true;
		} else {
			return false;
		}
	}
	
	function checkUserExists($username){
		$sql = sprintf("SELECT * FROM user WHERE username='%s'",$username);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return false;
		}
		while($row = mysql_fetch_array($result)){
			return true;
		}
		return false;
	}
	
	function resetPassword($username){
		if(!$this->checkUserExists($username)){
			return false;
		}
		$newpass = substr($this->createUUID(""), 7,60);
		$sql = sprintf("UPDATE user set password=md5('%s') WHERE username='%s'",$newpass,$username);
		$result = _mysql_query($sql,$this->DB);
		
		$tempU = new User($username);
		$this->getUser($tempU);
		$m = new Mailer();
		$m->resetPassword($tempU, $newpass);
		return true;
	}
	/*
	 *
	*/
	function writeLog($loglevel,$userid,$logtype,$logmsg,$ip,$logpagephptime,$logpagemysqltime,$logpagequeries,$logagent){
		$sql = sprintf("INSERT INTO log (loglevel,userid,logtype,logmsg,logip,logpagephptime,logpagemysqltime,logpagequeries,logagent) 
						VALUES ('%s',%d,'%s','%s','%s',%f,%f,%d,'%s')", 
						$loglevel,$userid,$logtype,mysql_real_escape_string($logmsg),$ip,$logpagephptime,$logpagemysqltime,$logpagequeries,$logagent);
		mysql_query($sql,$this->DB);
	}
	
	function insertQuizAttempt($qa){
		$qa->submituser = strtolower($qa->submituser);
		$qa->user = strtolower($qa->submituser);
		$sql = sprintf("INSERT INTO quizattempt (quizref,qadate,qascore,qauser,submituser, maxscore) 
					VALUES ('%s',%d, %d, '%s', '%s',%d)",
					$qa->quizref,
					$qa->quizdate,
					$qa->userscore,
					$qa->username,
					$qa->submituser,
					$qa->maxscore);
		mysql_query($sql,$this->DB);
		$result = mysql_insert_id();
		if (!$result){
			return;
		}
		return $result;
	}
	
	function insertQuizAttemptResponse($qar){
		$sql = sprintf("INSERT INTO quizattemptresponse (qaid,questionrefid,qarscore,responsetext) 
					VALUES (%d, '%s', %d,'%s')",
					$qar->qaid,
					$qar->questionRef,
					$qar->userScore,
					$qar->text);
		$result = mysql_query($sql,$this->DB);
	}
	
	function getQuizzes(){
		$sql = "SELECT q.quizid, q.quiztitle as title, q.quiztitleref as ref, q.quizdraft FROM quiz q 
				WHERE quizdraft = 0
				AND quizdeleted = 0";
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		$quizzes = array();
		while($r = mysql_fetch_object($result)){
			array_push($quizzes,$r);
		}
		return $quizzes;
	}
	
	function getQuizzesForUser($userid){
		$sql = sprintf("SELECT q.quizid, q.quiztitle as title, q.quiztitleref as ref, q.quizdraft FROM quiz q
						WHERE q.createdby = %d
						AND quizdeleted = 0
						ORDER BY q.quiztitle ASC",$userid);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		$quizzes = array();
		while($q = mysql_fetch_object($result)){
			$attempts = $this->getQuizNoAttempts($q->ref);
			$q->noattempts = $attempts->noattempts;
			$q->avgscore = $attempts->avgscore;
			$q->props = $this->getQuizProps($q->quizid);
			array_push($quizzes,$q);
		}
		return $quizzes;
	}
	
	function getQuizAttempts($ref, $opts = array()){
		$sql = sprintf("SELECT ((qascore*100)/ maxscore) as score, firstname, lastname, submitdate FROM quizattempt qa
						INNER JOIN user u ON qa.submituser = u.username
						INNER JOIN quiz q ON q.quiztitleref = qa.quizref
						WHERE quizref = '%s'
						AND q.quizdeleted = 0
						AND u.userid != q.createdby
						ORDER BY submitdate DESC",$ref);
		$summary = array();
		$result = _mysql_query($sql,$this->DB);
		while($o = mysql_fetch_object($result)){
			array_push($summary,$o);
		}
		return $summary;
	}
	
	function quizHasAttempts($ref){
		$sql = sprintf("SELECT id FROM quizattempt WHERE quizref='%s'",$ref);
		$result = _mysql_query($sql,$this->DB);
		if(mysql_num_rows($result) > 0){
			return true;
		} else {
			return false;
		}
	}
	
	
	function getQuizAttemptsSummary($ref, $opts = array()){
		if(array_key_exists('days',$opts)){
			$days = max(0,$opts['days']);
		} else {
			$days = DEFAULT_DAYS;
		}
		$sql = sprintf("SELECT COUNT(*) as no, 
								DAY(submitdate) as day, 
								MONTH(submitdate) as month, 
								YEAR(submitdate) as year,
								DATE_FORMAT(submitdate,'%%e-%%b-%%Y') AS displaydate
						FROM quizattempt qa
						INNER JOIN quiz q ON q.quiztitleref = qa.quizref
						WHERE quizref='%s' 
						AND submitdate > DATE_ADD(NOW(), INTERVAL -%d DAY) 
						AND q.quizdeleted = 0
						GROUP BY DAY(submitdate), MONTH(submitdate), YEAR(submitdate)",$ref,$days);
		$result = _mysql_query($sql,$this->DB);
		return $this->resultToArray($result);
	}
	
	function getQuizNoAttempts($quizref){
		$sql = sprintf("SELECT Count(*) as noattempts, AVG(qascore*100/maxscore) as avgscore FROM quizattempt qa
						INNER JOIN quiz q ON q.quiztitleref = qa.quizref
						WHERE quizref = '%s'
						AND q.quizdeleted = 0",$quizref);
		$result = _mysql_query($sql,$this->DB);

		$a = new stdClass;
		$a->noattempts = 0;
		$a->avgscore = 0;
		if (!$result){
			return $a;
		}
		while($r = mysql_fetch_object($result)){
			$a->noattempts = $r->noattempts;
			if($r->avgscore == null){
				$a->avgscore = 0;
			} else {
				$a->avgscore = $r->avgscore;
			}
				
		}
		return $a;
	}
	
	function getQuizScores($quizref){
		$sql = sprintf("SELECT Count(*) as NoScores, qascore*100/maxscore as scorepercent FROM quizattempt qa
						INNER JOIN quiz q ON q.quiztitleref = qa.quizref
						WHERE quizref = '%s'
						AND q.quizdeleted = 0
						GROUP BY qascore",$quizref);
		$result = _mysql_query($sql,$this->DB);
		$resp = array();
		if (!$result){
			return $resp;
		}
		while($r = mysql_fetch_object($result)){
			$resp[$r->scorepercent] = $r->NoScores; 
		}
		return $resp;
	}
	
	function getQuizAvgResponseScores($quizref){
		$sql = sprintf("SELECT AVG(qarscore) as avgscore, qq.questiontext FROM quizattemptresponse qar
						INNER JOIN quizattempt qa ON qa.id = qar.qaid
						INNER JOIN quiz q ON q.quiztitleref = qa.quizref
						INNER JOIN question qq ON qq.questiontitleref = qar.questionrefid
						INNER JOIN quizquestion qqu ON qqu.questionid = qq.questionid
						WHERE quizref = '%s'
						AND q.quizdeleted = 0
						GROUP BY qq.questiontext
						ORDER BY qqu.orderno ASC",$quizref);
		$result = _mysql_query($sql,$this->DB);
		return $this->resultToArray($result);
	}
	
	function getMyQuizScores(){
		global $USER;
		$sql = sprintf("SELECT AVG(score) as avgscore, count(*) as noattempts, max(score) as maxscore, min(score) as minscore, quiztitle as title, quiztitleref as ref  FROM 
						(SELECT ((qascore*100)/ maxscore) as score,  firstname, lastname, submitdate, quiztitle, quiztitleref FROM quizattempt qa
						INNER JOIN user u ON qa.submituser = u.username
						INNER JOIN quiz q ON q.quiztitleref = qa.quizref
						WHERE u.userid = %d
						AND q.quizdeleted = 0
						ORDER BY submitdate DESC) a
						GROUP BY quiztitle, quiztitleref", $USER->userid);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		$results = array();
		while($o = mysql_fetch_object($result)){
			array_push($results,$o);
		}
		return $results;
	}
	
	function getRanking($ref,$userid){
		$sql = sprintf("SELECT * FROM
						(SELECT MAX((qascore*100)/ maxscore) as score,  u.userid, quiztitleref FROM quizattempt qa
						INNER JOIN user u ON qa.submituser = u.username
						INNER JOIN quiz q ON q.quiztitleref = qa.quizref
						WHERE qa.quizref = '%s'
						AND q.quizdeleted = 0
						GROUP BY u.userid, quiztitleref) a
						ORDER BY score DESC",$ref);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		$rank = 0;
		$count = 0;
		$prevscore = -1;
		$myrank = 0;
		while($o = mysql_fetch_object($result)){
			$count++;
			if($o->score != $prevscore){
				$rank = $count;
				$prevscore = $o->score;
			}
			if($o->userid == $userid){
				$myrank = $rank;
			}
		}
		$r = array("myrank"=>$myrank,"total"=>$count);
		return $r;
	}
	
	function getQuiz($ref){
		$sql = sprintf("SELECT q.quizid, q.quiztitle as title, q.quiztitleref as ref, q.quizdraft, q.quizdescription as description, lastupdate FROM quiz q
						WHERE q.quiztitleref = '%s'
						AND q.quizdeleted = 0",$ref);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($q = mysql_fetch_object($result)){
			$q->props = $this->getQuizProps($q->quizid);
			return $q;
		}
	}
	
	function getQuizById($quizid){
		$sql = sprintf("SELECT q.quizid, q.quiztitle as title, q.quiztitleref as ref, q.quizdraft FROM quiz q
							WHERE q.quizid = %d
							AND q.quizdeleted = 0",$quizid);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($q = mysql_fetch_object($result)){
			$q->props = $this->getQuizProps($q->quizid);
			return $q;
		}
	}
	
	function getQuizForUser($ref,$userid){
		$sql = sprintf("SELECT q.quizid, q.quiztitle as title, q.quiztitleref as ref, q.quizdraft as draft, q.quizdescription as description FROM quiz q
						WHERE q.quiztitleref = '%s' 
						AND createdby=%d
						AND q.quizdeleted = 0
						ORDER BY q.quiztitle ASC",$ref,$userid);

		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($q = mysql_fetch_object($result)){
			$q->props = $this->getQuizProps($q->quizid);
			return $q;
		}
	}
	
	function getQuizProps($quizid){
		$psql = sprintf("SELECT * FROM quizprop WHERE quizid = %d",$quizid);
		$props = _mysql_query($psql,$this->DB);
		$p = array();
		while($prop = mysql_fetch_object($props)){
			$p[$prop->quizpropname] = $prop->quizpropvalue;
		}
		return $p;
	}
	
	function getQuizQuestions($quizid){
		$sql = sprintf("SELECT q.questionid, q.questiontitleref, qq.orderno, q.questiontext FROM question q 
						INNER JOIN quizquestion qq ON qq.questionid = q.questionid
						WHERE qq.quizid = %d
						ORDER BY orderno ASC",$quizid);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
		$questions = array();
		while($r = mysql_fetch_object($result)){
			$q = new stdClass;
			$q->id = $r->questionid;
			$q->refid = $r->questiontitleref;
			$q->text = $r->questiontext;
			$q->orderno =$r->orderno;
			$q->props = array();
			$psql = sprintf("SELECT * FROM questionprop WHERE questionid = %d",$r->questionid);
			$props = mysql_query($psql,$this->DB);
			while($prop = mysql_fetch_object($props)){
				$q->props[$prop->questionpropname] = $prop->questionpropvalue;
			}
			array_push($questions,$q);
		}
		return $questions;
	}
	
	function getQuestionResponses($questionid){
		$sql = sprintf("SELECT r.responseid, r.responsetitleref, qr.orderno, r.responsetext, r.score FROM response r 
						INNER JOIN questionresponse qr ON qr.responseid = r.responseid
						WHERE qr.questionid = %d
						ORDER BY orderno ASC",$questionid);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
		$responses = array();
		while($o = mysql_fetch_object($result)){
			$r = new stdClass;
			$r->refid = $o->responsetitleref;
			$r->text = $o->responsetext;
			$r->orderno =$o->orderno;
			$r->score = $o->score;
			$r->props = array();
			$psql = sprintf("SELECT * FROM responseprop WHERE responseid = %d",$o->responseid);
			$props = mysql_query($psql,$this->DB);
			while($prop = mysql_fetch_object($props)){
				$r->props[$prop->responsepropname] = $prop->responsepropvalue;
			}
			array_push($responses,$r);
		}
		return $responses;
	}
	
	function addQuiz($title, $draft=0,$description=""){
		global $USER, $CONFIG;
		$quiztitleref = $this->createUUID("qt");
		$description = substr($description,0,300);
		$date = new DateTime();
		$str = "INSERT INTO quiz (quiztitleref,createdby,quizdraft, quiztitle,quizdescription, lastupdate) VALUES ('%s',%d,%d,'%s','%s','%s')";
		$sql = sprintf($str,$quiztitleref,$USER->userid,$draft,$title,$description,$date->format('Y-m-d H:i:s'));
		mysql_query($sql,$this->DB);
		$result = mysql_insert_id();
		if (!$result){
			return ;
		}
		return $result;
	}
	
	function addQuestion($title){
		global $USER, $CONFIG;
		$questiontitleref = $this->createUUID("qqt");
	
		$str = "INSERT INTO question (questiontitleref,createdby,questiontext) VALUES ('%s',%d,'%s')";
		$sql = sprintf($str,$questiontitleref,$USER->userid,$title);
		mysql_query($sql,$this->DB);
		$result = mysql_insert_id();
		if (!$result){
			return ;
		}
		return $result;
	}
	
	function addResponse($title,$score){
		global $USER,$CONFIG;
		$responsetitleref = $this->createUUID("qqrt");
	
		$str = "INSERT INTO response (responsetitleref,createdby,score,responsetext) VALUES ('%s',%d,%f,'%s')";
		$sql = sprintf($str,$responsetitleref,$USER->userid,$score,$title);

		mysql_query($sql,$this->DB);
		$result = mysql_insert_id();
		if (!$result){
			return ;
		}
		return $result;
	}
	
	function addQuestionToQuiz($quizid,$questionid,$orderno){
		$str = "INSERT INTO quizquestion (quizid,questionid,orderno) VALUES (%d,%d,%d)";
		$sql = sprintf($str,$quizid,$questionid,$orderno);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
		return $result;
	}
	
	function addResponseToQuestion($questionid,$responseid,$orderno){
		$str = "INSERT INTO questionresponse (questionid,responseid,orderno) VALUES (%d,%d,%d)";
		$sql = sprintf($str,$questionid,$responseid,$orderno);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
		return $result;
	}
	
	function setProp($obj,$id,$name,$value){
		$value = addslashes($value);
		// first check to see if it exists already
		$sql = sprintf("SELECT * FROM %sprop WHERE %sid= %d AND %spropname='%s'",$obj,$obj,$id,$obj,$name);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$updateSql = sprintf("UPDATE %sprop SET %spropvalue='%s' WHERE %sid= %d AND %spropname='%s'",$obj,$obj,$value,$obj,$id,$obj,$name);
			_mysql_query($updateSql,$this->DB);
			return;
		}
		
		$insertSql = sprintf("INSERT INTO %sprop (%spropvalue, %sid,%spropname) VALUES ('%s',%d,'%s')",$obj,$obj,$obj,$obj,$value,$id,$name);
		$result = _mysql_query($insertSql,$this->DB);
		if (!$result){
			return ;
		}
	}
	
	function deleteQuiz($ref){
		//remove questions/responses first
		$q = $this->getQuiz($ref);
		$sql = sprintf("UPDATE quiz SET quizdeleted = 1 WHERE quiztitleref='%s'",$ref);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
	}
	
	function removeQuiz($quizid){
		$questions = $this->getQuizQuestions($quizid);
		foreach ($questions as $q){
			$responses = $this->getQuestionResponses($q->id);
			foreach ($responses as $r){
				$this->removeResponse($r->refid);
			}
			$this->removeQuestion($q->refid);
		}
	}
	
	function removeResponse($ref){
		$sql = sprintf("DELETE FROM response WHERE responsetitleref='%s'",$ref);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
	}
	
	function removeQuestion($ref){
		$sql = sprintf("DELETE FROM question WHERE questiontitleref='%s'",$ref);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
	}
	
	function updateQuiz($ref,$title,$quizdraft,$description=""){
		$description = substr($description,0,300);
		$date = new DateTime();
		$sql = sprintf("UPDATE quiz 
							SET quizdraft = %d,
							quiztitle = '%s',
							quizdescription = '%s',
							lastupdate = '%s'
						WHERE quiztitleref='%s'",$quizdraft,$title,$description, $date->format('Y-m-d H:i:s'),$ref);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
	}
	
	function get10PopularQuizzes(){
		$sql = "SELECT Count(qa.id) as noattempts, qa.quizref as ref, quiztitle as title FROM quizattempt qa
					INNER JOIN quiz q ON q.quiztitleref = qa.quizref
					INNER JOIN user u ON u.username = qa.submituser
					WHERE u.userid != q.createdby
					AND q.quizdraft = 0
					AND q.quizdeleted = 0
					GROUP BY qa.quizref
					ORDER BY Count(qa.id) DESC
					LIMIT 0,10";
		$result = _mysql_query($sql,$this->DB);
		$top10 = array();
		while($o = mysql_fetch_object($result)){
			array_push($top10,$o);
		}
		return $top10;
	}
	
	function get10MostRecentQuizzes(){
		$sql = "SELECT q.quiztitleref as ref ,createdon, quiztitle as title FROM quiz q
					WHERE quizdraft = 0
					AND quizdeleted = 0
					ORDER BY createdon DESC
					LIMIT 0,10";
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
		$top10 = array();
		while($o = mysql_fetch_object($result)){
			array_push($top10,$o);
		}
		return $top10;
	}
	
	function getLeaderboard(){
		// if want to use highest score per quiz, then average this...
		/*$sql = "SELECT AVG(qa.maxscore) as avgscore, u.firstname, u.lastname FROM 
				(SELECT MAX(qascore*100/maxscore) as maxscore, submituser, quizref  FROM quizattempt
				GROUP BY submituser, quizref) qa
				INNER JOIN quiz q ON q.quiztitleref = qa.quizref
				INNER JOIN user u ON u.username = qa.submituser
				WHERE q.quizdraft = 0
				AND q.quizdeleted = 0
				GROUP BY u.firstname, u.lastname
				HAVING COUNT(qa.quizref)>2
				ORDER BY AVG(qa.maxscore) DESC
				LIMIT 0,10";*/
		$sql = "SELECT AVG(qascore*100/maxscore) as avgscore, u.firstname, u.lastname FROM quizattempt qa
					INNER JOIN quiz q ON q.quiztitleref = qa.quizref
					INNER JOIN user u ON u.username = qa.submituser
					WHERE u.userid != q.createdby
					AND q.quizdraft = 0
					AND q.quizdeleted = 0
					GROUP BY u.firstname, u.lastname
					HAVING COUNT(DISTINCT quizref) > 2
					ORDER BY AVG(qascore*100/maxscore) DESC
					LIMIT 0,10";
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
		$leaders = array();
		while($o = mysql_fetch_object($result)){
			array_push($leaders,$o);
		}
		return $leaders;
	}
	
	function searchQuizzes($terms){
		$sql = sprintf("SELECT * FROM (SELECT quiztitleref as ref, quiztitle as title, quizdescription as description, lastupdate FROM quiz 
					WHERE (quiztitle LIKE '%%%s%%' OR quizdescription LIKE '%%%s%%')
					AND quizdraft = 0
					AND quizdeleted = 0
					UNION
					SELECT q.quiztitleref as quizref, quiztitle, quizdescription as description, lastupdate FROM quiz q
					INNER JOIN quizquestion qqq ON q.quizid = qqq.quizid
					INNER JOIN question qq ON qqq.questionid - qq.questionid
					WHERE qq.questiontext LIKE '%%%s%%'
					AND q.quizdraft = 0
					AND q.quizdeleted = 0)
					a LIMIT 0,5",$terms,$terms,$terms);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return false;
		}
		$results = array();
		while($o = mysql_fetch_object($result)){
			array_push($results,$o);
		}
		return $results;
	}
	
	function suggestQuizzes(){
		global $USER;
		$sql = "SELECT DISTINCT quizref as ref, quiztitle as title, quizdescription as description, lastupdate FROM (";
		// get featured
		$sql .= "SELECT * FROM (SELECT q.quizid, q.quiztitleref as quizref, q.quiztitle, 10 AS weight,q.quizdescription, lastupdate FROM quiz q
							INNER JOIN quizprop qp ON q.quizid = qp.quizid
							WHERE qp.quizpropname = 'featured'
							AND qp.quizpropvalue = 'true'
							AND q.quizdraft = 0
							AND q.quizdeleted = 0) f";
		
		// TODO get those from friends who have taken quizzes
		
		// get most recent 5
		$sql .= " UNION
					SELECT * FROM (SELECT q.quizid, q.quiztitleref as quizref, q.quiztitle,5 AS weight,q.quizdescription, lastupdate FROM quiz q
							WHERE q.quizdraft = 0
							AND q.quizdeleted = 0
							ORDER BY createdon DESC) b";
		
		// get top 5 popular which haven't been attempted by this user
		$sql .= " UNION 
					SELECT * FROM 
					(SELECT q.quizid, qa.quizref, q.quiztitle, 4 AS weight,q.quizdescription, lastupdate FROM quizattempt qa
					INNER JOIN quiz q ON q.quiztitleref = qa.quizref
					INNER JOIN user u ON u.username = qa.submituser
					WHERE u.userid != q.createdby
					AND q.quizdraft = 0
					AND q.quizdeleted = 0
					GROUP BY qa.quizref
					ORDER BY Count(qa.id) DESC) c";
		
		$sql .= sprintf(") a
					WHERE a.quizref NOT IN (SELECT quizref FROM quizattempt WHERE qauser ='%s')
					AND a.quizref NOT IN (SELECT quiztitleref FROM quiz WHERE createdby =%d)
					ORDER BY weight DESC
					LIMIT 0,10",$USER->username,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		$results = array();
		if (!$result){
			return $results;
		}
		while($o = mysql_fetch_object($result)){
			array_push($results,$o);
		}
		return $results;
	}
	
	function isOwner($ref){
		global $USER;
		$sql = sprintf("SELECT * FROM quiz WHERE quiztitleref='%s' AND createdby = %d",$ref,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		while($o = mysql_fetch_object($result)){
			return true;
		}
		return false;
	}
	
	function getQuizObject($ref){
		$quiz = $this->getQuiz($ref);
		
		$questions = array();
		
		$qq = $this->getQuizQuestions($quiz->quizid);
		
		foreach($qq as $q){
		
			$responses = array();
			$resps = $this->getQuestionResponses($q->id);
		
			foreach($resps as $o){
				$props = (object) $o->props;
				$r = array(
										'refid'=> $o->refid,
										'orderno'=> $o->orderno,
										'text'=>$o->text,
										'score'=>$o->score,
										'props'=>$props
				);
				array_push($responses,$r);
			}
		
			if(array_key_exists('maxscore',$q->props)){
				$score = $q->props['maxscore'];
			} else {
				$score = 0;
			}
			if(array_key_exists('type',$q->props)){
				$type = $q->props['type'];
			} else {
				$type = "multichoice";
			}
			$props = (object) $q->props;
			$newq = array(
								'refid'=>$q->refid,
								'orderno'=> $q->orderno,
								'text'=>$q->text,
								'type'=>$type,
								'props'=>$props,
								'r'=>$responses
			);
			array_push($questions,$newq);
		}
		
		if(array_key_exists('maxscore',$quiz->props)){
			$maxscore = $quiz->props['maxscore'];
		} else {
			$maxscore = 0;
		}
		
		$q = array (	'refid'=>$quiz->ref,
						'ref'=>$quiz->ref,
						'title'=>$quiz->title,
						'description'=>$quiz->description,
						'maxscore'=>$maxscore,
						'lastupdate'=>$quiz->lastupdate,
						'q'=>$questions);
		
		return $q;	
	}
	
	private function createUUID($prefix){
		global $USER;
		return $prefix.strtolower($USER->userid).uniqid();
	}
	
	private function resultToArray($r){
		$temp = array();
		while($o = mysql_fetch_object($r)){
			array_push($temp, $o);
		}
		return $temp;
	}
}