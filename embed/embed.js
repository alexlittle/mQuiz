function init(){
	var opts = {
			'menu':[{'title':'Search','link':'#select'},
			        {'title':'Quizzes','link':'#quizzes'},
			        {'title':'Results','link':'#results'}],
			'allowregister': true,
			'url':'../api/?format=json'          
			};
	mQ.init(opts);
	
	mQ.store.set('username','anon');
	mQ.store.set('password','fd6bbe845b6a96f9cd82e06db44bd0a7');
	
	if($(location).attr('hash')){
		mQ.showPage($(location).attr('hash'));
	} else {
		mQ.showPage('#home');
	}
}