<?php
	include_once('../config.php');
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>mQuiz</title>
    <link rel="StyleSheet" href="<?php echo $CONFIG->homeAddress; ?>embed/style.css" type="text/css" media="screen">
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/lib/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/lib/jquery.validate.min.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/lib/jquery-ui-1.8.19.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>embed/embed.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/dateformat.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/mquizengine.js"></script>
</head>
<body onload="init()" onhashchange="init()">
<div id="page">
	<div id="content"></div>
	<div id="footer">
		<a onclick="parent.location='http://mquiz.org'">Visit mQuiz</a>
	</div>
</div>
</body>
</html>