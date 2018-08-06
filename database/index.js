const MongoClient = require('mongodb').MongoClient;
const ObjectID = require('mongodb').ObjectID;

const config = require('../config').db;

let db;

function getDb(callback) {
	if(db){
		callback(db);
		return;
	}
	MongoClient.connect(config.url, { useNewUrlParser: true }, function(err, client) {
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
		MongoClient.connect(config.url, { useNewUrlParser: true }, function(err, client) {
			if (err) {console.log(111)
				if(param.error){console.log(222)
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
	},
	init () {
		getDb(function(db) {
			console.log(db)
		});
	},
	collection (name) {
		getDb(function(db){
			table = db.collection(name);
		});
		return this;
	},
	find (query) {
		getDb(function(db){
			table = db.collection(name);
		});


		this.collection(function(collection){
			collection.find()
		})
		table.find(query)
		return this;
	},
	create () {
		return this;
	},
	update () {
		return this;
	},
	delete () {
		return this;
	},
	close () {
		return this;
	}
}