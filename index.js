var express = require('express')();
var http = require('http').Server(express);
var io = require('socket.io')(http);

express.get('/', function(req, res){
	//res.send('<h1>Hello world</h1>');
	res.sendFile(__dirname + '/index.html');
});

io.on('connection', function(socket){
	socket.broadcast.emit('chat message', '一个用户进入聊天室了'); //当前连接用户收不到这条信息
	//console.log('a user connected');
	socket.on('disconnect', function(){
		//console.log('user disconnected');
		socket.broadcast.emit('chat message', '一个用户退出聊天室了'); //当前连接用户收不到这条信息
	});
	socket.on('chat message', function(msg){
		io.emit('chat message', '你说: ' + msg)
		//console.log('message: ' + msg);
	});
});

http.listen(3000, function(){
	console.log('listening on *:3000');
});