<?php
include_once("../config.php");
$PAGE = "terms";
include_once("../includes/header.php");
?>
<h1>Terms/License</h1>

<p>mQuiz is a work in progress and is provided as-is. Not everything may work 100%, so if you find anything wrong, then <a href="mailto:alex@alexlittle.net">please let us know</a>.</p>

<p>The code for mQuiz is available for download (<a href="<?php echo $CONFIG->homeAddress ;?>developer/">visit the developer page for more info</a>), if you would like to run your own mQuiz server, or create your own client application.</p>


<h2>License</h2>
<p>mQuiz is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.</p>

<p>mQuiz is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.</p>

<p>You should have received a copy of the GNU General Public License
along with mQuiz.  If not, see <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.</p>

<?php
include_once("../includes/footer.php");
?>