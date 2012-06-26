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
	
	function getUserFromUsername($username){
		$sql = sprintf("SELECT * FROM user WHERE username ='%s' LIMIT 0,1",$username);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($o = mysql_fetch_object($result)){
			return $o;
		}
		return false;
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
		
		// find if pending user
		$sql = sprintf("SELECT * FROM user where username='%s' and pending = 1",$username);
		$result = _mysql_query($sql,$this->DB);
		
		if(mysql_num_rows($result) == 0){
			$str = "INSERT INTO user (username,password,firstname,lastname,email,pending) VALUES ('%s',md5('%s'),'%s','%s','%s',0)";
			$sql = sprintf($str,$username,$password,$firstname,$surname,$email);
		} else {
			$str = "UPDATE user SET
						password = md5('%s'),
						firstname = '%s',
						lastname = '%s',
						email = '%s',
						pending = 0
					WHERE username = '%s'";
			$sql = sprintf($str,$password,$firstname,$surname,$email,$username);
		}
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return false;
		}
		return true;
		
	}
	
	function addPendingUser($email){
		$email = strtolower($email);
		$sql = sprintf("SELECT * FROM user WHERE email='%s'",$email);
		$result = _mysql_query($sql,$this->DB);
		if(mysql_num_rows($result) == 0){
			$str = "INSERT INTO user (username,email,pending) VALUES ('%s','%s',1)";
			$sql = sprintf($str,$email,$email);
			_mysql_query($sql,$this->DB);
			return mysql_insert_id();
		} else {
			while($o = mysql_fetch_object($result)){
				return $o->userid;
			}
		}
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
		$sql = sprintf("SELECT * FROM user WHERE username='%s' and pending=0",$username);
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
		$user = $this->getUserFromUsername($qa->username);
		$quiz = $this->getQuiz($qa->quizref);
		
		$sql = sprintf("INSERT INTO quizattempt (qadate, qascore, maxscore, quizid, userid) 
					VALUES ('%s', %d, %d, %d, %d)",
					$qa->quizdate,
					$qa->userscore,
					$qa->maxscore,
					$quiz->quizid,
					$user->userid);
		_mysql_query($sql,$this->DB);
		$result = mysql_insert_id();
		if (!$result){
			return;
		}
		return $result;
	}
	
	function insertQuizAttemptResponse($qar){
		$text = explode('|',$qar->text);
		
		if(count($text)>1){
			$sql = sprintf("INSERT INTO quizattemptresponse (qaid,questionrefid,qarscore,responsetext) 
						VALUES (%d, '%s', %f,'')",
						$qar->qaid,
						$qar->questionRef,
						$qar->userScore);
			$result = _mysql_query($sql,$this->DB);
			$qarid = mysql_insert_id();
			foreach($text as $t){
				if(trim($t) != ""){
					$this->insertQuizAttemptResponseMulti($qarid, $t);
				}
			}
		} else {
			$sql = sprintf("INSERT INTO quizattemptresponse (qaid,questionrefid,qarscore,responsetext)
					VALUES (%d, '%s', %f,'%s')",
					$qar->qaid,
					$qar->questionRef,
					$qar->userScore,
					$qar->text);
			$result = _mysql_query($sql,$this->DB);
		}
		
	}
	
	function insertQuizAttemptResponseMulti($qarid,$responsetext){
		$sql = sprintf("INSERT INTO qarmulti (qarid,multiresponsetext)
				VALUES (%d,'%s')",
				$qarid,
				$responsetext);
		$result = _mysql_query($sql,$this->DB);
	}
	
	function getQuizzes(){
		$sql = "SELECT q.quizid, q.quiztitle, q.qref, q.quizdraft FROM quiz q 
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
		$sql = sprintf("SELECT q.quizid, q.quiztitle as title, q.qref as ref, q.quizdraft FROM quiz q
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
	
	function getQuizAttempts($qref, $opts = array()){
		if(array_key_exists('groupid',$opts)){
			$groupid = $opts['groupid'];
		} else {
			$groupid = 0;
		}
		$q = $this->getQuiz($qref);
		if(!$q){
			return;
		}
		
		if($groupid == 0){
			$sql = sprintf("SELECT qa.id, ((qascore*100)/ maxscore) as score, firstname, lastname, submitdate FROM quizattempt qa
							INNER JOIN user u ON qa.userid = u.userid
							INNER JOIN quiz q ON q.quizid = qa.quizid
							WHERE qa.quizid = %d
							AND q.quizdeleted = 0
							AND u.userid != q.createdby
							ORDER BY submitdate DESC",$q->quizid);
		} else {
			$sql = sprintf("SELECT qa.id, ((qascore*100)/ maxscore) as score, firstname, lastname, submitdate FROM quizattempt qa
							INNER JOIN user u ON qa.userid = u.userid
							INNER JOIN quiz q ON q.quizid = qa.quizid
							INNER JOIN usergroupquiz ugq ON u.userid = ugq.userid
							WHERE qa.quizid = %d
							AND q.quizdeleted = 0
							AND u.userid != q.createdby
							AND ugq.groupid = %d
							ORDER BY submitdate DESC",$q->quizid,$groupid);
		}
		$summary = array();
		$result = _mysql_query($sql,$this->DB);
		while($o = mysql_fetch_object($result)){
			array_push($summary,$o);
		}
		return $summary;
	}
	
	function getQuizAttempt($id){
		$sql = sprintf("SELECT qa.id, ((qascore*100)/ maxscore) as score, submitdate FROM quizattempt qa
							INNER JOIN quiz q ON q.quizid = qa.quizid
							WHERE qa.id = %d
							AND q.quizdeleted = 0",$id);
		$result = _mysql_query($sql,$this->DB);
		while($o = mysql_fetch_object($result)){
			return $o;
		}
		return false;
	}
	
	function getQuizAttemptDetail($id){
		$sql = sprintf("SELECT questiontext,qarscore,responsetext,questionpropvalue as maxscore FROM quizattemptresponse qar
						INNER JOIN question q on qar.questionrefid = q.questiontitleref
						INNER JOIN quizquestion qq on q.questionid = qq.questionid
						INNER JOIN questionprop qp ON q.questionid = qp.questionid
						WHERE qaid = %d
						AND qp.questionpropname='maxscore'
						ORDER BY qq.orderno ASC",$id);
		$d = array();
		$result = _mysql_query($sql,$this->DB);
		while($o = mysql_fetch_object($result)){
			array_push($d,$o);
		}
		return $d;
	}
	
	function quizHasAttempts($ref){
		$sql = sprintf("SELECT qa.id FROM quizattempt qa
						INNER JOIN quiz q ON q.quizid = qa.quizid 
						WHERE q.qref='%s'",$ref);
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
		if(array_key_exists('groupid',$opts)){
			$groupid = $opts['groupid'];
		} else {
			$groupid = 0;
		}
		if($groupid == 0){
			$sql = sprintf("SELECT COUNT(*) as no, 
									DAY(submitdate) as day, 
									MONTH(submitdate) as month, 
									YEAR(submitdate) as year,
									DATE_FORMAT(submitdate,'%%e-%%b-%%Y') AS displaydate
							FROM quizattempt qa
							INNER JOIN quiz q ON q.quizid = qa.quizid
							WHERE q.qref='%s' 
							AND submitdate > DATE_ADD(NOW(), INTERVAL -%d DAY) 
							AND q.quizdeleted = 0
							GROUP BY DAY(submitdate), MONTH(submitdate), YEAR(submitdate)",$ref,$days);
		} else {
			$sql = sprintf("SELECT COUNT(*) as no,
									DAY(submitdate) as day, 
									MONTH(submitdate) as month, 
									YEAR(submitdate) as year,
									DATE_FORMAT(submitdate,'%%e-%%b-%%Y') AS displaydate
							FROM quizattempt qa
							INNER JOIN quiz q ON q.quizid = qa.quizid
							INNER JOIN user u ON u.userid= qa.userid
							INNER JOIN usergroupquiz ugq ON u.userid = ugq.userid
							WHERE q.qref='%s' 
							AND submitdate > DATE_ADD(NOW(), INTERVAL -%d DAY) 
							AND q.quizdeleted = 0
							AND ugq.groupid = %d
							GROUP BY DAY(submitdate), MONTH(submitdate), YEAR(submitdate)",$ref,$days,$groupid);
		}
		
		$result = _mysql_query($sql,$this->DB);
		return $this->resultToArray($result);
	}
	
	function getQuizNoAttempts($qref){
		$sql = sprintf("SELECT Count(*) as noattempts, AVG(qascore*100/maxscore) as avgscore FROM quizattempt qa
						INNER JOIN quiz q ON q.quizid = qa.quizid
						WHERE q.qref = '%s'
						AND q.quizdeleted = 0",$qref);
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
	
	function getQuizScores($qref,$opts = Array()){
		if(array_key_exists('groupid',$opts)){
			$groupid = $opts['groupid'];
		} else {
			$groupid = 0;
		}
		if($groupid == 0){
			$sql = sprintf("SELECT Count(*) as NoScores, qascore*100/maxscore as scorepercent FROM quizattempt qa
							INNER JOIN quiz q ON q.quizid = qa.quizid
							WHERE q.qref = '%s'
							AND q.quizdeleted = 0
							GROUP BY qascore",$qref);
		} else {
			$sql = sprintf("SELECT Count(*) as NoScores, qascore*100/maxscore as scorepercent FROM quizattempt qa
							INNER JOIN quiz q ON q.quizid = qa.quizid
							INNER JOIN user u ON u.userid = qa.userid
							INNER JOIN usergroupquiz ugq ON ugq.userid = u.userid
							WHERE q.qref = '%s'
							AND q.quizdeleted = 0
							AND ugq.groupid = %d
							GROUP BY qascore",$qref,$groupid);
		}
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
	
	function getQuizAvgResponseScores($qref,$opts = Array()){
		if(array_key_exists('groupid',$opts)){
			$groupid = $opts['groupid'];
		} else {
			$groupid = 0;
		}
		if($groupid == 0){
			$sql = sprintf("SELECT AVG(qarscore) as avgscore, qq.questiontext FROM quizattemptresponse qar
							INNER JOIN quizattempt qa ON qa.id = qar.qaid
							INNER JOIN quiz q ON q.quizid = qa.quizid
							INNER JOIN question qq ON qq.questiontitleref = qar.questionrefid
							INNER JOIN quizquestion qqu ON qqu.questionid = qq.questionid
							WHERE q.qref = '%s'
							AND q.quizdeleted = 0
							GROUP BY qq.questiontext
							ORDER BY qqu.orderno ASC",$qref);
		} else {
			$sql = sprintf("SELECT AVG(qarscore) as avgscore, qq.questiontext FROM quizattemptresponse qar
							INNER JOIN quizattempt qa ON qa.id = qar.qaid
							INNER JOIN quiz q ON q.quizid = qa.quizid
							INNER JOIN question qq ON qq.questiontitleref = qar.questionrefid
							INNER JOIN quizquestion qqu ON qqu.questionid = qq.questionid
							INNER JOIN user u ON u.userid = qa.userid
							INNER JOIN usergroupquiz ugq ON ugq.userid = u.userid
							WHERE q.qref = '%s'
							AND q.quizdeleted = 0
							AND ugq.groupid = %d
							GROUP BY qq.questiontext
							ORDER BY qqu.orderno ASC",$qref,$groupid);
		}
		$result = _mysql_query($sql,$this->DB);
		return $this->resultToArray($result);
	}
	
	function getMyQuizScores(){
		global $USER;
		$sql = sprintf("SELECT AVG(score) as avgscore, count(*) as noattempts, max(score) as maxscore, min(score) as minscore, quiztitle as title, qref as ref  FROM 
						(SELECT ((qascore*100)/ maxscore) as score,  firstname, lastname, submitdate, q.quiztitle, q.qref FROM quizattempt qa
						INNER JOIN user u ON qa.userid = u.userid
						INNER JOIN quiz q ON q.quizid = qa.quizid
						WHERE u.userid = %d
						AND q.quizdeleted = 0
						ORDER BY submitdate DESC) a
						GROUP BY quiztitle, qref", $USER->userid);
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
	
	function getBestRankForQuiz($qref,$userid){
		$sql = sprintf("SELECT qascore, u.userid, quizref FROM quizattempt qa
						INNER JOIN user u ON qa.userid = u.userid
						INNER JOIN quiz q ON q.quizid = qa.quizid
						WHERE q.qref = '%s'
						ORDER BY qascore DESC",$qref);
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
			if($o->qascore != $prevscore){
				$rank = $count;
				$prevscore = $o->qascore;
			}
			if($o->userid == $userid){
				$myrank = $rank;
				break;
			}
		}
		$r = array("myrank"=>$myrank,"total"=>$count);
		return $r;
	}
	
	function getUserRecentAttempts($userid,$limit=10){
		$sql = sprintf("SELECT 
							qa.id,
							q.qref, 
							u.username,
							qa.maxscore, 
							qa.qascore as userscore, 
							qa.qadate AS quizdate,
							q.quiztitle
						FROM quiz q
						INNER JOIN quizattempt qa ON q.quizid = qa.quizid
						INNER JOIN user u ON qa.userid = u.userid
						WHERE u.userid = %d
						ORDER BY qa.qadate DESC
						LIMIT 0,%d",$userid,$limit);
		$result = _mysql_query($sql,$this->DB);
		$qas = array();
		while($o = mysql_fetch_object($result)){
			$o->rank = $this->getRankingForAttempt($o->id);
			$o->sent = true;
			unset($o->id);
			array_push($qas,$o);
		}
		return $qas;
	}
	
	function getRankingForAttempt($attemptid){
		$sql = sprintf("SELECT aqa.id, aqa.qascore FROM quizattempt qa
						INNER JOIN (SELECT id, qascore, quizid  FROM quizattempt) aqa ON aqa.quizid = qa.quizid
						WHERE qa.id = %d
						ORDER BY aqa.qascore DESC",$attemptid);
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
			if($o->qascore != $prevscore){
				$rank = $count;
				$prevscore = $o->qascore;
			}
			if($o->id == $attemptid){
				$myrank = $rank;
			}
		}
		return $myrank;
	}
	
	function getQuiz($qref){
		$sql = sprintf("SELECT q.quizid, q.quiztitle, q.qref, q.quizdraft, q.quizdescription, lastupdate FROM quiz q
						WHERE q.qref = '%s'
						AND q.quizdeleted = 0",$qref);
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
		$sql = sprintf("SELECT q.quizid, q.quiztitle as title, q.qref as ref, q.quizdraft FROM quiz q
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
	
	function getQuizForUser($qref,$userid){
		$sql = sprintf("SELECT q.quizid, q.quiztitle as title, q.qref as ref, q.quizdraft as draft, q.quizdescription as description FROM quiz q
						WHERE q.qref = '%s' 
						AND createdby=%d
						AND q.quizdeleted = 0
						ORDER BY q.quiztitle ASC",$qref,$userid);

		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return;
		}
		while($q = mysql_fetch_object($result)){
			$q->props = $this->getQuizProps($q->quizid);
			$q->tags = implode(", ",$this->getQuizTags($q->quizid));
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
	
	function getQuizTags($quizid){
		$tsql = sprintf("SELECT tagtext FROM tag t
						INNER JOIN quiztag qt ON qt.tagid = t.tagid
						WHERE qt.quizid = %d
						ORDER BY tagtext ASC",$quizid);
		$tags = _mysql_query($tsql,$this->DB);
		$t = array();
		while($tag = mysql_fetch_object($tags)){
			array_push($t,$tag->tagtext);
		}
		return $t;
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
		$qref = $this->createUUID("qt");
		$description = substr($description,0,300);
		$date = new DateTime();
		$str = "INSERT INTO quiz (qref,createdby,quizdraft, quiztitle,quizdescription, lastupdate) VALUES ('%s',%d,%d,'%s','%s','%s')";
		$sql = sprintf($str,$qref,$USER->userid,$draft,$title,$description,$date->format('Y-m-d H:i:s'));
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
		$sql = sprintf("UPDATE quiz SET quizdeleted = 1 WHERE qref='%s'",$ref);
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
						WHERE qref='%s'",$quizdraft,$title,$description, $date->format('Y-m-d H:i:s'),$ref);
		$result = _mysql_query($sql,$this->DB);
		if (!$result){
			return ;
		}
	}
	
	function updateQuizTags($quizid,$tags){
		// remove all tags for this quiz
		$sql = sprintf("DELETE FROM quiztag WHERE quizid = %d",$quizid);
		_mysql_query($sql,$this->DB);
		
		$tags = explode(",",$tags);
		foreach($tags as $k=>$t){
			if(trim($t)!= ""){
				$tagid = $this->getTagID($t);
				$sql = sprintf("INSERT INTO quiztag (tagid,quizid) VALUES(%d,%d)",$tagid,$quizid);
				_mysql_query($sql,$this->DB);
			}
		}
	}
	
	function getTagID($tag){
		$sql = sprintf("SELECT tagid FROM tag WHERE tagtext ='%s'",trim($tag));
		$result = _mysql_query($sql,$this->DB);
		if(mysql_num_rows($result) > 0){
			while($o = mysql_fetch_object($result)){
				return $o->tagid;
			}
		} else {
			$sql = sprintf("INSERT INTO tag (tagtext) VALUES('%s')",trim($tag));
			mysql_query($sql,$this->DB);
			return mysql_insert_id();
		}
	}
	
	function get10PopularQuizzes(){
		$sql = "SELECT Count(qa.id) as noattempts, q.qref, q.quiztitle FROM quizattempt qa
					INNER JOIN quiz q ON q.quizid = qa.quizid
					INNER JOIN user u ON u.userid = qa.userid
					WHERE u.userid != q.createdby
					AND q.quizdraft = 0
					AND q.quizdeleted = 0
					GROUP BY q.qref
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
		$sql = "SELECT q.qref as ref ,createdon, quiztitle as title FROM quiz q
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
				INNER JOIN quiz q ON q.qref = qa.quizref
				INNER JOIN user u ON u.username = qa.submituser
				WHERE q.quizdraft = 0
				AND q.quizdeleted = 0
				GROUP BY u.firstname, u.lastname
				HAVING COUNT(qa.quizref)>2
				ORDER BY AVG(qa.maxscore) DESC
				LIMIT 0,10";*/
		$sql = "SELECT AVG(qascore*100/maxscore) as avgscore, u.firstname, u.lastname FROM quizattempt qa
					INNER JOIN quiz q ON q.quizid = qa.quizid
					INNER JOIN user u ON u.userid = qa.userid
					WHERE u.userid != q.createdby
					AND q.quizdraft = 0
					AND q.quizdeleted = 0
					GROUP BY u.firstname, u.lastname
					HAVING COUNT(DISTINCT qa.quizid) > 2
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
	
	function searchQuizzes($terms, $opts = Array()){
		if(isset($opts['count'])){
			$count = $opts['count'];
		} else {
			$count = 5;
		}
	
		if(isset($opts['start'])){
			$start = $opts['start'];
		} else {
			$start = 0;
		}
		
		$sql = sprintf("SELECT MAX(weight), qref, quiztitle, quizdescription FROM (
						SELECT q.qref, quiztitle, quizdescription, lastupdate, 10 as weight FROM quiz q
						WHERE (quiztitle LIKE '%%%s%%' OR quizdescription LIKE '%%%s%%')
						AND quizdraft = 0
						AND quizdeleted = 0
						UNION
						SELECT q.qref, quiztitle, quizdescription, lastupdate, 5 as weight FROM quiz q
						INNER JOIN quiztag qt ON qt.quizid = q.quizid
						INNER JOIN tag t ON qt.tagid = t.tagid
						WHERE t.tagtext LIKE '%%%s%%'
						AND q.quizdraft = 0
						AND q.quizdeleted = 0
						UNION
						SELECT q.qref, quiztitle, quizdescription, lastupdate, 2 AS weight FROM quiz q
						INNER JOIN quizquestion qq ON q.quizid = qq.quizid
						INNER JOIN question qu ON qq.questionid = qu.questionid
						WHERE qu.questiontext LIKE '%%%s%%'
						AND q.quizdraft = 0
						AND q.quizdeleted = 0)
					a ",$terms,$terms,$terms,$terms);
		$sql .= " GROUP BY qref,quiztitle,quizdescription";
		$sql .= " ORDER BY weight DESC, quiztitle ASC";
		$sql .= sprintf(" LIMIT %d,%d",$start,$count);
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
		$sql = "SELECT DISTINCT qref, quiztitle, quizdescription, lastupdate FROM (";
		// get featured
		$sql .= "SELECT * FROM (SELECT q.quizid, q.qref, q.quiztitle, 10 AS weight,q.quizdescription, lastupdate FROM quiz q
							INNER JOIN quizprop qp ON q.quizid = qp.quizid
							WHERE qp.quizpropname = 'featured'
							AND qp.quizpropvalue = 'true'
							AND q.quizdraft = 0
							AND q.quizdeleted = 0) f";
		
		// TODO get those from friends who have taken quizzes
		
		// get most recent 5
		$sql .= " UNION
					SELECT * FROM (SELECT q.quizid, q.qref , q.quiztitle,5 AS weight,q.quizdescription, lastupdate FROM quiz q
							WHERE q.quizdraft = 0
							AND q.quizdeleted = 0
							ORDER BY createdon DESC) b";
		
		// get top 5 popular which haven't been attempted by this user
		$sql .= " UNION 
					SELECT * FROM 
					(SELECT q.quizid, q.qref, q.quiztitle, 4 AS weight,q.quizdescription, lastupdate FROM quizattempt qa
					INNER JOIN quiz q ON q.quizid = qa.quizid
					INNER JOIN user u ON u.userid = qa.userid
					WHERE u.userid != q.createdby
					AND q.quizdraft = 0
					AND q.quizdeleted = 0
					GROUP BY q.qref
					ORDER BY Count(qa.id) DESC) c";
		
		$sql .= sprintf(") a
					WHERE a.quizid NOT IN (SELECT quizid FROM quizattempt WHERE userid =%d)
					AND a.quizid NOT IN (SELECT quizid FROM quiz WHERE createdby =%d)
					ORDER BY weight DESC
					LIMIT 0,10",$USER->userid,$USER->userid);
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
	
	function suggestNext($quizref,$score){
		$sql = sprintf("SELECT qp.qref as quizref, qp.quiztitle as title FROM quiz qp
				INNER JOIN quizrelation qr ON qp.quizid = qr.parentquizid
				INNER JOIN quiz qc ON qc.quizid = qr.childquizid
				WHERE qc.qref = '%s'
				AND qr.threshold <= %d
				ORDER BY qr.threshold DESC",$quizref,$score);
		$result = _mysql_query($sql,$this->DB);
		$results = array();
		while($o = mysql_fetch_object($result)){
			array_push($results,$o);
		}
		return $results;
	}
	
	function tagCloud($tag = ""){
		if($tag == ""){
			$sql = "SELECT tagtext, COUNT(*) as weight FROM tag t
					INNER JOIN quiztag qt ON qt.tagid = t.tagid
					INNER JOIN quiz q ON qt.quizid = q.quizid
					WHERE quizdeleted = 0
					AND quizdraft = 0
					GROUP BY tagtext
					ORDER BY tagtext ASC";
			$result = _mysql_query($sql,$this->DB);
			$cloud = new stdClass;
			$cloud->tags = Array();
			while($o = mysql_fetch_object($result)){
				array_push($cloud->tags,$o);
			}
			
			$msql = sprintf("SELECT MAX(weight) as tagmax, MIN(weight) as tagmin FROM (%s) b",$sql);
			$result = _mysql_query($msql,$this->DB);
			while($o = mysql_fetch_object($result)){
				$cloud->max = $o->tagmax;
				$cloud->min = $o->tagmin;
			}
			return $cloud;
		} else {
			$tagid = $this->getTagID($tag);
			$sql = sprintf("SELECT qref as ref, quiztitle as title, quizdescription as description
							FROM quiz q
							INNER JOIN quiztag qt ON qt.quizid = q.quizid
							WHERE quizdeleted = 0
							AND quizdraft = 0
							AND qt.tagid = %d
							ORDER BY quiztitle ASC",$tagid);
			$result = _mysql_query($sql,$this->DB);
			$results = array();
			while($o = mysql_fetch_object($result)){
				array_push($results,$o);
			}
			return $results;
		}
	}
	
	function browseAlpha($init = ""){
		if($init == ""){
			$sql = "SELECT COUNT(*) as icount, initial FROM ( 
						SELECT upper(substring(quiztitle,1,1)) as initial 
						FROM quiz
						WHERE quizdeleted = 0
						AND quizdraft = 0) i
					GROUP BY initial ASC";
			$result = _mysql_query($sql,$this->DB);
			$results = array();
			while($o = mysql_fetch_object($result)){
				$results[$o->initial] = $o->icount;
			}
			return $results;
		} else {
			$sql = sprintf("SELECT qref as ref, quiztitle as title, quizdescription as description
							FROM quiz
							WHERE quizdeleted = 0
							AND quizdraft = 0
							AND upper(substring(quiztitle,1,1)) = '%s'
							ORDER BY quiztitle ASC",$init);
			$result = _mysql_query($sql,$this->DB);
			$results = array();
			while($o = mysql_fetch_object($result)){
				array_push($results,$o);
			}
			return $results;
		}
	}
	
	function isOwner($qref){
		global $USER;
		$sql = sprintf("SELECT * FROM quiz WHERE qref='%s' AND createdby = %d",$qref,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		while($o = mysql_fetch_object($result)){
			return true;
		}
		return false;
	}
	
	function invite($qref, $emails,$message){
		global $USER;
		//check quiz owner
		$q = $this->getQuizForUser($qref, $USER->userid);
		if(!$q){
			return false;
		}
		
		$mail = new Mailer();
		//split up the email addresses
		$emailArray = preg_split( "( |,)", $emails );
		$groupname = "Invited by email - ".$q->title." - ".date('d-M-Y');
		$groupid = $this->addGroup($groupname);
		foreach($emailArray as $email){
			if(trim($email) != ""){
				if(validEmailAddress($email)){
					$userid =$this->addPendingUser($email);
					$this->addUserGroupQuiz($userid,$groupid,$q->quizid);
					$mail->invite($email, $USER->firstname, $q->title, $q->ref);
				}
			}
		}
		return true;	
	}
	
	function addGroup($name){
		global $USER;
		//find if groups already exists for this user
		$sql = sprintf("SELECT groupid FROM `group` WHERE groupname ='%s' and ownerid=%d",$name,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		if(mysql_num_rows($result) == 0){
			$str = "INSERT INTO `group` (groupname,ownerid) VALUES ('%s',%d)";
			$sql = sprintf($str,$name,$USER->userid);
			_mysql_query($sql,$this->DB);
			return mysql_insert_id();
		} else {
			while($o = mysql_fetch_object($result)){
				return $o->groupid;
			}
		}
	}
	
	function addUserGroupQuiz($userid,$groupid,$quizid){
		$str = "INSERT INTO usergroupquiz (userid,groupid,quizid) VALUES (%d,%d,%d)";
		$sql = sprintf($str,$userid,$groupid,$quizid);
		_mysql_query($sql,$this->DB);
	}
	
	function getUserGroups(){
		global $USER;
		$sql = sprintf("SELECT g.groupid, g.groupname, true as owner FROM `group` g 
						WHERE g.ownerid = %d
						UNION
						SELECT g.groupid, g.groupname, false as owner FROM `group` g 
						INNER JOIN usergroupquiz ugq ON g.groupid = ugq.groupid
						WHERE ugq.userid = %d",$USER->userid,$USER->userid);
		$results = _mysql_query($sql,$this->DB);
		$groups = Array();
		while($o = mysql_fetch_object($result)){
			array_push($groups,$o);
		}
		return $groups;
	}
	
	function getUserGroupQuiz($quizid){
		global $USER;
		$sql = sprintf("SELECT DISTINCT g.groupid, g.groupname FROM `group` g 
						INNER JOIN usergroupquiz ugq ON g.groupid = ugq.groupid
						WHERE (g.ownerid = %d OR ugq.userid = %d) AND ugq.quizid = %d",$USER->userid,$USER->userid,$quizid);
		
		$result = _mysql_query($sql,$this->DB);
		$groups = Array();
		while($o = mysql_fetch_object($result)){
			array_push($groups,$o);
		}
		return $groups;
	}
	
	function getQuizObject($qref){
		$quiz = $this->getQuiz($qref);
		
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
		
		$q = array (	'qref'=>$quiz->qref,
						'quiztitle'=>$quiz->quiztitle,
						'quizdescription'=>$quiz->quizdescription,
						'maxscore'=>$maxscore,
						'lastupdate'=>$quiz->lastupdate,
						'q'=>$questions);
		return $q;	
	}
	
	function createQuizfromGIFT($content,$title,$quizdraft,$description,$tags){
		global $IMPORT_INFO,$MSG,$CONFIG,$USER;
		
		//first check if this quiz already exists
		$sql = sprintf("SELECT q.qref FROM quizprop qp
						INNER JOIN quiz q ON q.quizid = qp.quizid
						WHERE qp.quizpropname='content' 
						AND qp.quizpropvalue='%s'
						AND q.createdby=%d",$content,$USER->userid);
		$result = _mysql_query($sql,$this->DB);
		while($o = mysql_fetch_object($result)){
			// store JSON object for quiz (for caching)
			$obj = $this->getQuizObject($o->qref);
			return $obj;
		}
		
		$supported_qtypes = array('truefalse','multichoice','essay','shortanswer','numerical');
		$questions_to_import = array();
		include_once($CONFIG->homePath.'quiz/import/gift/import.php');
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
		if(count($questions_to_import) == 0){
			array_push($MSG,getstring('import.quiz.error.nosuppportedquestions'));
			return;
		}
		
		if(count($MSG) == 0){
			// now do the actual import
	
			// setup quiz with default props
			$quizid = $this->addQuiz($title,$quizdraft,$description);
			$this->setProp('quiz',$quizid,'generatedby','import');
			$this->setProp('quiz',$quizid,'content',$content);
			$this->updateQuizTags($quizid, $tags);
			$importer = new GIFTImporter();
			$importer->quizid = $quizid;
			$importer->import($questions_to_import);
				
			$this->setProp('quiz', $quizid, 'maxscore', $importer->quizmaxscore);
		
			$q = $this->getQuizById($quizid);
			// store JSON object for quiz (for caching)
			$obj = $this->getQuizObject($q->ref);
			$json = json_encode($obj);
			$this->setProp('quiz', $quizid, 'json', $json);
			return $obj;
		}
		return;
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
