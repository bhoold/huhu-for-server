exports.use = function(req, res, next) {
	let arr = req.path.split("/");
	let controller;

	if(arr[1] === undefined || arr[1] === ""){
		try{
			require('../controller/index').index(req, res);
		}catch(e) {
			res.send("no file");
		}
	}else{
		try{
			controller = require('../controller/' + arr[1]);
		}catch(e) {
			res.send("no file");
		}
		if(controller){
			if(arr[2] === undefined || arr[2] === ""){
				if(controller.index){
					controller.index(req, res);
				}else{
					res.send("no index");
				}
			}else{
				if(controller[arr[2]]){
					controller[arr[2]](req, res);
				}else{
					res.send("no action");
				}
			}
		}
	}
	//next();
}

