<?php

function writeToLog($loglevel,$logtype,$logmsg,$logpagephptime=0,$logpagemysqltime=0,$logpagequeries=0,$userid=0){
	global $USER,$API;
	if ( isset($_SERVER["REMOTE_ADDR"]) )    {
		$ip=$_SERVER["REMOTE_ADDR"];
	} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
		$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
	} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
		$ip=$_SERVER["HTTP_CLIENT_IP"];
	} 
	
	$uagent = new uagent_info();
	$API->writeLog($loglevel,$USER->userid,$logtype,$logmsg,$ip,$logpagephptime,$logpagemysqltime,$logpagequeries,$uagent->useragent);
}

function _mysql_query($query,$db) {
    global $CONFIG;

    $start = microtime(true);
    $result = mysql_query($query,$db);
    if (!$result){
    	writeToLog('error','database',$query);
    }
    $CONFIG->mysql_queries_time += microtime(true) - $start;
    $CONFIG->mysql_queries_count++;

    return $result;
}

	
?>