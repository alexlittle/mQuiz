function addQuestion(){
	
	var qno = $('#questions > div').size()+1;
	
	var fb = $("<div class='formblock'></div>");
	var fl = $("<div class='formlabel'></div>").text("Question " +qno);
	fb.append(fl);
	var ff = $("<div class='formfield'></div>");
	ff.append("<input type='text' name='q"+qno+"' value='' size='60'></input>");
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


