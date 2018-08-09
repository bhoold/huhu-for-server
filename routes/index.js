let controllers = {};


exports.use = function(req, res, next) {
	let arr = req.path.split("/");
	let controller;

	if(arr[1] === undefined || arr[1] === ""){
		let key = '../controller/index';
		if(controllers[key]){
			if(controllers[key].index){
				controllers[key].index(req, res);
			}else{
				res.send("no index");
			}			
		}else{
			try{
				controllers[key] = require(key);
				if(controllers[key].index){
					controllers[key].index(req, res);
				}else{
					res.send("no index");
				}
			}catch(e) {
				res.send("no file");
			}
		}
	}else{
		let key = '../controller/' + arr[1];
		if(!controllers[key]){
			try{
				controllers[key] = require(key);
			}catch(e) {
				res.send("no file");
			}
		}
		if(controllers[key]){
			if(arr[2] === undefined || arr[2] === ""){
				if(controllers[key].index){
					controllers[key].index(req, res);
				}else{
					res.send("no index");
				}
			}else{
				if(controllers[key][arr[2]]){
					controllers[key][arr[2]](req, res);
				}else{
					res.send("no action");
				}
			}
		}
	}
	//next();
}

