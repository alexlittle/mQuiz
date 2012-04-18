<?php
include_once("../config.php");
$PAGE = "faqs";
include_once("../includes/header.php");
?>

<h1>FAQs</h1>

<ul >
<li><strong>Do I need to have an Android phone to see my quiz?</strong><br/>At the moment yes, we're going to create client applications for other phone systems soon though. For now, you'll need to install and use an <a href="http://developer.android.com/guide/developing/tools/emulator.html">Android emulator</a> if you don't have a physical Android device.</li>
<li><strong>Can anyone submit their answers to my quiz?</strong><br/>Currently, yes, we're thinking about allowing the option to create private quizzes, so only those people you invite are able to download and respond.</li>
<li><strong>How is the leaderboard calculated?</strong><br/>The leaderboard is calculated on the average of all your quiz attempts on quizzes which were not created by yourself. You must submit results from at least 3 different quizzes to appear on the leaderboard.</li>
</ul>


<?php 
include_once("../includes/footer.php");
?>