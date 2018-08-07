const MongoClient = require('mongodb').MongoClient;
const ObjectID = require('mongodb').ObjectID;

const config = require('../config').db;

let db;

function getDb(callback) {
	if(db){
		callback(db);
		return;
	}
	MongoClient.connect(config.url, {
		auth: {
			user: "admin",
			password: "admin"
		},
		useNewUrlParser: true
	}, function(err, client) {
		if (err) {
			callback(err);
			return;
		}
		callback(db = client.db(config.dbName));
	});
}



module.exports = {
	ObjectID,
	execute (param) {
		MongoClient.connect(config.url, {
			authSource: "admin",
			auth: {
				user: "admin",
				password: "admin"
			},
			useNewUrlParser: true
		}, function(err, client) {
			if (err) {
				if(param.error){
					param.error(err);
				}
				return;
			}
			const db = client.db(config.dbName);
			const coll = db.collection(param.collection);
			if(param.success){
				param.success(coll[param.action](param.param));
			}
		});
	}
}