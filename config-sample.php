<?php

unset($CONFIG);
$CONFIG = new stdClass;

// include trailing slashes
$CONFIG->homeAddress = "http://".$_SERVER["SERVER_NAME"]."/mquiz/";
$CONFIG->homePath = "/var/www/mquiz/";

// this must be a writable directory
$CONFIG->imagecache = "/tmp/";

$CONFIG->langs = array("en"=>"English", "es"=>"Español");
$CONFIG->defaultlang = "en";

$CONFIG->dbtype = "mysql";
$CONFIG->dbhost = "localhost";
$CONFIG->dbname = "mquiz";
$CONFIG->dbuser = "XXXXXX";
$CONFIG->dbpass = "XXXXXX"; 

$CONFIG->googleanalytics = "";
$CONFIG->emailfrom = "xxxxxxxx@xxxxxxxxx.com";

$CONFIG->anonuser = "anon";

include_once("setup.php");