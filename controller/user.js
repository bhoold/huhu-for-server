const db = require('../database');
const onlineUser = require('../storage/onlineUser');

const SUCCESS = 1000,
	ERROR = -1;



module.exports = {
	index (req, res) {
		res.send("我是用户首页");
	},
	list (req, res) {
		db.select({
			collection: "user",
			fields: ['username']
		}).then(list => {
			res.send({
				code: SUCCESS,
				message: "查询成功",
				data: list
			});
		}).catch(err => {
			res.send({
				code: ERROR,
				message: err.message || "获取用户列表失败"
			});
		});
	},
	getState (req, res) {
		let users = onlineUser.list();
		let list = [];
		for(let key in users){
			list.push({
				id: key,
				username: users[key].username
			})
		}
		res.send({
			code: SUCCESS,
			message: "查询成功",
			data: list
		});
	},
	create (req, res) {
		res.send("我是用户添加页");
	}
}