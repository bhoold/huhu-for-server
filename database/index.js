const MongoClient = require('mongodb').MongoClient;
const ObjectID = require('mongodb').ObjectID;

const config = require('../config').db;

let mClient,
	database;


function connect() {
	return new Promise(function(resolve, reject) {
		if(mClient && database){
			resolve(database);
		}else{
			MongoClient.connect(config.url, {
				authSource: "admin",
				auth: {
					user: "admin",
					password: "admin"
				},
				useNewUrlParser: true
			}).then(function(client) {
				mClient = client;
				database = client.db(config.dbName);
				resolve(database);
			}).catch(function(err) {
				reject(err.message || "连接数据失败");
			});
		}
	});
}



module.exports = {
	ObjectID,

	//查询多个
	select (param) {
		return new Promise(function(resolve, reject) {
			connect().then((db) => {
				const collection = db.collection(param.collection);
				let query = {},
					projection = {};

				if(param.query){
					query = param.query;
				}
				if(param.fields){
					projection.fields = [];
					param.fields.forEach(field => {
						projection.fields.push(field);
					});
				}

				collection.find(query, projection).toArray().then(function(arr){
					let list = [];
					arr.forEach(row => {
						let keys = Object.keys(row);
						let obj = {};
						keys.forEach(key => {
							if(key === "_id"){
								obj.id = row[key].toString();
							}else{
								obj[key] = row[key];
							}
						});
						list.push(obj);
					});
					resolve(list);
				}).catch(err => {
					reject({message: err.message || err});
				});
			}).catch(err => {
				reject({message: err.message || err});
			});
		});
	},

	//查询单个
	find (param) {
		return new Promise(function(resolve, reject) {
			connect().then((db) => {
				const collection = db.collection(param.collection);
				let query = {},
					projection = {};

				if(param.query){
					query = param.query;
				}
				if(param.fields){
					projection.fields = [];
					param.fields.forEach(field => {
						projection.fields.push(field);
					});
				}
				collection.findOne(query, projection, function(err, result){
					if(err){
						reject({message: err.message || err});
						return;
					}
					let obj = {};
					if(result){
						let keys = Object.keys(result);
						keys.forEach(key => {
							if(key === "_id"){
								obj.id = result[key].toString();
							}else{
								obj[key] = result[key];
							}
						});
					}
					resolve(obj);
				});
			}).catch(err => {
				reject({message: err.message || err});
			});
		});
	},

	//添加单个
	add (param) {
		return new Promise(function(resolve, reject) {
			connect().then((db) => {
				const collection = db.collection(param.collection);
				let query = {};

				if(param.query){
					query = param.query;
				}else{
					reject({message: "未提供数据"});
					return;
				}
				collection.insertOne(query, function(err, result){
					if(err){
						reject({message: err.message || err});
						return;
					}
					if(result.result && result.result.ok === 1){
						resolve(result.insertedId.toString());
					}else{
						reject({message: "添加失败"});
					}
				});
			}).catch(err => {
				reject({message: err.message || err});
			});
		});
	},
	
}