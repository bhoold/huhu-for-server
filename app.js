const express = require('express')();
const http = require('http').Server(express);
const io = require('socket.io')(http);
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
let userList = {};

io.on('connection', function(socket){
	const query = socket.handshake.query;

	if(query.username === 'aaasdfsd'){
		//socket.close();
		//return;
	}

	if(query.id && query.username){
		validate({
			id: query.id,
			username: query.username
		}, function(res) {
			if(res.code === SUCCESS){
				socket.emit('login', {
					code: SUCCESS,
					message: "登录成功"
				});
				
				userList[query.id] = {
					username: query.username,
					socket: socket
				};
				socket.on('message', function(msg){
					if(msg.sender && msg.to && msg.message){
						if(msg.to.id){
							if(userList[msg.to.id]){
								userList[msg.to.id].socket.emit('message', {
									date: new Date().getTime(),
									from: msg.sender,
									message: msg.message
								})
							}
						}else{
							socket.broadcast.emit('message', {
								date: new Date().getTime(),
								from: msg.sender,
								message: msg.message
							});
						}
					}
				});

				socket.broadcast.emit('event', query.username + '上线了'); //当前连接用户收不到这条信息
				socket.on('disconnect', function(){
					socket.broadcast.emit('event', query.username + '下线了'); //当前连接用户收不到这条信息
				});
			

			}else{
				socket.emit('login', {
					code: ERROR,
					message: res.message || "登录失败"
				});
			}
		});
	}else{
		socket.emit('login', {
			code: ERROR,
			message: "请提供登录信息"
		});

	}

});


function validate (msg, callback) {
	db.execute({
		collection: "user",
		action: "find",
		param: {_id: new db.ObjectID(msg.id), username: msg.username},
		success (cursor) {
			cursor.toArray(function(err, result){
				if(err){
					callback({
						code: ERROR,
						message: err.message || "查询失败"
					});
					return;
				}
				if(result.length){
					callback({
						code: SUCCESS,
						message: "登录成功"
					});
				}else{
					callback({
						code: ERROR,
						message: "没有该用户"
					});
				}
			});
		},
		error (err) {
			callback({
				code: ERROR,
				message: err.message || "登录失败"
			});
		}
	});
}




http.listen(config.server.port, function(){
	console.log('listening on *:%s', config.server.port);
});