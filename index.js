var express = require('express')();
var http = require('http').Server(express);
var io = require('socket.io')(http);



const MongoClient = require('mongodb').MongoClient;
// Connection url
const url = 'mongodb://localhost:27017';
// Database Name
const dbName = 'mytestdb';






express.all('*', function(req, res, next) {
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
		next();
	}
});








express.get('/', function(req, res){
	//res.send('<h1>Hello world</h1>');
	res.sendFile(__dirname + '/index.html');
});


express.post('/signUp/:username/:password/:repassword', function(req, res){
	let username = req.params.username,
		password = req.params.password,
		repassword = req.params.repassword;
	
	if(username && password && repassword){
		if(password === repassword){
			addUser(username, password);
		}else{
			res.send('两次密码不一样');
		}
	}else{
		res.send('请完整填写表单');
	}
});

express.post('/signIn/:username/:password', function(req, res){
	res.send('<h1>signIn</h1>');
});


//添加
express.get('/create', function(req, res){
	MongoClient.connect(url, { useNewUrlParser: true }, function(err, client) {
		let message = "连接成功";
		if(err){
			message = err.message;
		}else{
			const db = client.db(dbName);
			insertDocuments(db, function(result) {
				message = result;
				client.close();

				console.log(message);
				res.send(message);
			});
		}
	});
});

//查看
express.get('/list', function(req, res){
	MongoClient.connect(url, { useNewUrlParser: true }, function(err, client) {
		let message = "连接成功";
		if(err){
			message = err.message;
		}else{
			const db = client.db(dbName);
			findDocuments(db, function(result) {
				message = result;
				client.close();

				console.log(message);
				res.send(message);
			});
		}
	});
});

//更新
express.get('/update', function(req, res){
	MongoClient.connect(url, { useNewUrlParser: true }, function(err, client) {
		let message = "连接成功";
		if(err){
			message = err.message;
		}else{
			const db = client.db(dbName);
			updateDocument(db, function(result) {
				message = result;
				client.close();

				console.log(message);
				res.send(message);
			});
		}
	});
});

//删除
express.get('/delete', function(req, res){
	MongoClient.connect(url, { useNewUrlParser: true }, function(err, client) {
		let message = "连接成功";
		if(err){
			message = err.message;
		}else{
			const db = client.db(dbName);
			removeDocument(db, function(result) {
				message = result;
				client.close();

				console.log(message);
				res.send(message);
			});
		}
	});
});














//添加
const insertDocuments = function(db, callback) {
	// Get the documents collection
	const collection = db.collection('documents');
	// Insert some documents
	collection.insertMany([{a : 1}, {a : 2}, {a : 3}], function(err, result) {
		callback(result);
	});
}

//查看所有
const findDocuments = function(db, callback) {
	// Get the documents collection
	const collection = db.collection('documents');
	// Find some documents
	collection.find({}).toArray(function(err, result) {
		callback(result);
	});
}

//根据条件查看
const findDocumentsByFilter = function(db, callback) {
	// Get the documents collection
	const collection = db.collection('documents');
	// Find some documents
	collection.find({'a': 3}).toArray(function(err, result) {
		callback(result);
	});
}

//更新
const updateDocument = function(db, callback) {
	// Get the documents collection
	const collection = db.collection('documents');
	// Update document where a is 2, set b equal to 1
	collection.updateOne({ a : 2 }, { $set: { b : 1 } }, function(err, result) {
		callback(result);
	});  
}

//删除
const removeDocument = function(db, callback) {
	// Get the documents collection
	const collection = db.collection('documents');
	// Delete document where a is 3
	collection.deleteOne({ a : 3 }, function(err, result) {
		callback(result);
	});    
}

//创建索引
const indexCollection = function(db, callback) {
	db.collection('documents').createIndex({ "a": 1 }, null, function(err, results) {
		callback(results);
	});
};




















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