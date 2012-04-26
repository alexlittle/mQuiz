function init(){
	var opts = {
			'menu':[{'title':'Search','link':'#select'},
			        {'title':'Quizzes','link':'#quizzes'},
			        {'title':'Results','link':'#results'}],
			'allowregister': true,
			'allowanon': true,
			'url':'../api/?format=json'
			};
	mQ.init(opts);
	
	if(!mQ.loggedIn()){
		
	}
	if($(location).attr('hash')){
		mQ.showPage($(location).attr('hash'));
	} else {
		mQ.showPage('#home');
	}
}