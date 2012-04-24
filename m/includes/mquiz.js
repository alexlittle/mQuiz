
function init(){
	var opts = {
			'menu':[{'title':'Search','link':'#select'},
			        {'title':'Quizzes','link':'#quizzes'},
			        {'title':'Results','link':'#results'}],
			'allowregister': true,
			'finallinks': [{'title':'Try another quiz','link':'#select'},
			               {'title':'View all recent results','link':'#results'}]
			};
	mQ.init(opts);
	
	if($(location).attr('hash')){
		mQ.showPage($(location).attr('hash'));
	} else {
		mQ.showPage('#home');
	}
}