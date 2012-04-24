<?php
	include_once('../config.php');
?>

<!DOCTYPE HTML>
<html manifest="mquiz.appcache">
<head>
    <title>mQuiz</title>
    <meta name="viewport" content="width=device-width, user-scalable=no" />
    <link rel="StyleSheet" href="<?php echo $CONFIG->homeAddress; ?>m/includes/style.css" type="text/css" media="screen">
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/lib/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/lib/jquery.validate.min.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/lib/jquery-ui-1.8.19.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>embed/embed.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/dateformat.js"></script>
    <script type="text/javascript" src="<?php echo $CONFIG->homeAddress; ?>m/includes/mquizengine.js"></script>
</head>
<body onload="init()" onhashchange="init()">
<div id="page">
	<div id="content">
	</div> <!-- end #content -->
	<div id="footer">
		
	</div>
</div> <!-- end #page -->
</body>
</html>