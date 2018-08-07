const db = require('../database');

const SUCCESS = 1000,
	ERROR = -1;





module.exports = {
	index (req, res) {
		res.send("我是用户首页");
	},
	list (req, res) {
		db.execute({
			collection: "user",
			action: "find",
			param: {},
			success (cursor) {
				cursor.toArray(function(err, result){
					if(err){
						res.send({
							code: ERROR,
							message: err.message || "获取用户列表失败"
						});
						return;
					}
					if(result.length){
						let list = [];
						result.forEach(item => {
							list.push({
								id: item._id.toString(),
								name: item.username
							});
						});
						res.send({
							code: SUCCESS,
							message: "查询成功",
							data: list
						});
					}else{
						res.send({
							code: ERROR,
							message: "没有用户"
						});
					}
				});
			},
			error (err) {
				res.send({
					code: ERROR,
					message: err.message || "获取用户列表失败"
				});
			}
		});
	},
	getState (req, res) {
		res.send("我是获取状态页");
	},
	create (req, res) {
		res.send("我是用户添加页");
	}
}