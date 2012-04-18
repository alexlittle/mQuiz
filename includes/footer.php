	<div style="clear:both"></div>
	</div> <!-- end content -->
</div> <!-- page -->
<div id="footer"> 
<a href="<?php echo $CONFIG->homeAddress; ?>info/about.php">About</a> | 
<a href="<?php echo $CONFIG->homeAddress; ?>info/terms.php">Terms/License</a><br/>
<a href="http://alexlittle.net">Alex Little</a> &copy; <?php echo date('Y');?><br/>
 
</div>
</body>
</html>

<?php 
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
writeToLog("info","pagehit",$_SERVER["REQUEST_URI"], $total_time, $CONFIG->mysql_queries_time, $CONFIG->mysql_queries_count);

$API->cleanUpDB();
?>