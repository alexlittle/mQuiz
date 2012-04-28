function init(){
	var opts = {
			'allowanon': true,
			'url':'../api/?format=json'
			};
	mQ.init(opts);
	
	if($(location).attr('hash')){
		mQ.showPage($(location).attr('hash'));
	} else {
		mQ.showPage('#home');
	}
}