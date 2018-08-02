




exports.use = function(req, res, next) {
	/* 
		req.protocol
		req.originalUrl
		req.path
		req.url
	*/
	let arr = req.path.split("/");
	let controller;

	if(arr[1] === undefined || arr[1] === ""){
		try{
			require('../controller/index').index(req, res);
		}catch(e) {
			console.log("no file");
		}
	}else{
		try{
			controller = require('../controller/' + arr[1]);
		}catch(e) {
			console.log("no file");
		}
		if(controller){
			if(arr[2] === undefined || arr[2] === ""){
				if(controller.index){
					controller.index(req, res);
				}else{
					console.log("no index");
				}
			}else{
				if(controller[arr[2]]){
					controller[arr[2]](req, res, arr.slice(3));
				}else{
					console.log("no action");
				}
			}
		}
	}
	next();
}