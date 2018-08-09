const express = require('express')();
const http = require('http').Server(express);
const io = require('socket.io')(http);
const redis = require('socket.io-redis');
const bodyParser = require('body-parser');


const config = require('./config');

const route = require('./routes');


let jsonParser = bodyParser.json();
//let urlencodedParser = bodyParser.urlencoded({ extended: false });



express.all('*', jsonParser, function(req, res, next) {
	if(req.headers.origin){
		res.header("Access-Control-Allow-Origin", "*");
		res.header("Access-Control-Allow-Headers", "token,Content-Type,Content-Length Authorization,Accept,X-Requested-With");
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


const db = require('./database');
const SUCCESS = 1000,
	ERROR = -1;

const onlineUser = require('./storage/onlineUser');


const MSGTYPE = require('./config/im/msgtype');
const NOTIFY = require('./config/im/notify');

const adapter = redis({ host: 'localhost', port: 6379 });
io.adapter(adapter);



// middleware
io.use((socket, next) => {
	const query = socket.handshake.query;
	if (isValid(query)) {
		return next();
	}
	return next(new Error("请提供登录信息"));
});

function isValid (query) {
	if((query.id && query.username) && query.id != 'undefined' && query.username != 'undefined'){
		return true;
	}else{
		return false;
	}
}


io.on('connection', function(socket){
	const query = socket.handshake.query;

	if(query.id && query.username){
		if(onlineUser.exist(query)){
			if(onlineUser.add(query, socket)){
				socket.emit(MSGTYPE.NOTIFY, {
					type: NOTIFY.LOGIN,
					code: SUCCESS,
					message: "登录成功"
				});
				listenSocket(socket, query);
			}else{
				socket.emit(MSGTYPE.NOTIFY, {
					type: NOTIFY.LOGIN,
					code: ERROR,
					message: "登录失败"
				});
				socket.disconnect(true);
			}
		}else{
			validateToken({
				id: query.id,
				username: query.username
			}, function(res) {
				if(res.code === SUCCESS){
					socket.emit(MSGTYPE.NOTIFY, {
						type: NOTIFY.LOGIN,
						code: SUCCESS,
						message: "登录成功"
					});
					
					if(onlineUser.add(query, socket)){
						listenSocket(socket, query);
					}
				}else{
					socket.emit(MSGTYPE.NOTIFY, {
						type: NOTIFY.LOGIN,
						code: ERROR,
						message: res.message || "登录失败"
					});
					socket.disconnect(true);
				}
			});
		}
	}else{
		socket.emit(MSGTYPE.NOTIFY, {
			type: NOTIFY.LOGIN,
			code: ERROR,
			message: "请提供登录信息"
		});
		socket.disconnect(true);
	}
});


function listenSocket(socket, query) {

	//广播登录提示
	socket.broadcast.emit(MSGTYPE.ONLINE, {
		id: query.id,
		username: query.username
	}); //当前连接用户收不到这条信息

	//广播下线提示
	socket.on('disconnect', function(){
		socket.broadcast.emit(MSGTYPE.OFFLINE, {
			id: query.id,
			username: query.username
		}); //当前连接用户收不到这条信息
		onlineUser.delete(query, socket);
	});

	socket.on(MSGTYPE.MESSAGE, function(msg){
		if(msg.sender && msg.to && msg.message){
			let time = new Date().getTime();
			if(msg.to.id){
				onlineUser.sockets(msg.to).forEach(item => {
					item.emit(MSGTYPE.MESSAGE, {
						date: time,
						from: msg.sender,
						message: msg.message
					});
				});
			}else{
				socket.broadcast.emit(MSGTYPE.MESSAGE, {
					date: time,
					from: msg.sender,
					message: msg.message
				});
			}
		}
	});
}


function validateToken (msg, callback) {
	if(!db.ObjectID.isValid(msg.id)){
		callback({
			code: ERROR,
			message: "用户id错误"
		});
		return;
	}

	db.find({
		collection: "user",
		query: {_id: new db.ObjectID(msg.id)}
	}).then(data => {
		if(data.username != msg.username){
			callback({
				code: ERROR,
				message: "没有该用户"
			});
		}else{
			callback({
				code: SUCCESS,
				message: "登录成功"
			});
		}
	}).catch(err => {
		callback({
			code: ERROR,
			message: err.message || "查询失败"
		});
	});
}




http.listen(config.server.port, function(){
	console.log('listening on *:%s', config.server.port);
});