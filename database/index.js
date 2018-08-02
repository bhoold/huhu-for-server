const MongoClient = require('mongodb').MongoClient;

const config = require('../config').db;


function getDb() {
	let db;
	MongoClient.connect(config.url, { useNewUrlParser: true }, function(err, client) {
		let message;
		if(err){
			message = err.message;
		}else{
			db = client.db(config.dbName);
		}
	});
	return db;
}


let db = getDb(), table;


module.exports = {
	table (name) {
		getDb(function(db){
			table = db.collection(name);
		});
		return this;
	},
	find (query) {
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