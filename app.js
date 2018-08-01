const express = require('express')();
const http = require('http').Server(express);


const config = require('./config');
console.log(config)




http.listen(config.server.port, function(){
	console.log('listening on *:%s', config.server.port);
});