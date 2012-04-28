<?php 
	include_once("../config.php");
	$uagent_obj = new uagent_info();
?>

function initPage(){
	mQ.initStore();
	<?php 
    	if(isLoggedIn()){
    		printf("mQ.store.set('username','%s');",$USER->username);
    		printf("mQ.store.set('displayname','%s');",$USER->firstname." ".$USER->lastname);
    		printf("mQ.store.set('password','%s');",$USER->password);
    		
    	} 
    	if(!$uagent_obj->DetectIphone() && !$uagent_obj->DetectAndroidPhone()){
    		printf("mQ.store.set('source','%s');",$CONFIG->homeAddress);
    	}
    ?>
}


function addQuestion(){
	
	var qno = $('#questions > div').size()+1;
	
	var fb = $("<div class='formblock'></div>");
	var fl = $("<div class='formlabel'></div>").text("Question " +qno);
	fb.append(fl);
	var ff = $("<div class='formfield'></div>");
	ff.append("<textarea name='q"+qno+"' cols='80' rows='3' maxlength='300'></textarea>");
	ff.append("<div class='responses'>");
	ff.append("<div class='responsetext'>Possible responses</div><div class='responsescore'>Score</div>");
	for(i=1; i<5 ; i++){
		ff.append("<div class='responsetext'><input type='text' name='q"+qno+"r"+i+"' value='' size='40'></input></div>");
		ff.append("<div class='responsescore'><input type='text' name='q"+qno+"m"+i+"' value='0' size='5'></input></div>");
	}
	ff.append("</div>");
	fb.append(ff);
	$('#questions').append(fb);
		
	$('#noquestions').val(qno);
}	


