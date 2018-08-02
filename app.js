const express = require('express')();
const http = require('http').Server(express);
const bodyParser = require('body-parser');


const config = require('./config');

const route = require('./routes');


let jsonParser = bodyParser.json();
//let urlencodedParser = bodyParser.urlencoded({ extended: false });



express.all('*', jsonParser, function(req, res, next) {
	if(req.headers.origin){
		res.header("Access-Control-Allow-Origin", "*");
		res.header("Access-Control-Allow-Headers", "tokenStr,Content-Type,Content-Length Authorization,Accept,X-Requested-With");
		res.header("Access-Control-Allow-Methods","post,get,options");
	}
	if(req.headers.accept && req.headers.accept.indexOf("application/json") > -1){
		res.header("Content-Type", "application/json;charset=utf-8");
	}
	res.header("Servery",' mockServer');
	res.header("X-Powered-By",' weiju');

	if(req.method == "OPTIONS"){
		res.sendStatus(204);
	}else{
		route.use(req, res, next);
	}
});






http.listen(config.server.port, function(){
	console.log('listening on *:%s', config.server.port);
});