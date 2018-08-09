const db = require('../database');

const SUCCESS = 1000,
	ERROR = -1;




module.exports = {
	//账号注册
	signUp (req, res) {
		let username = req.body.username,
			password = req.body.password,
			repassword = req.body.repassword;
		if(password !== repassword){
			res.send({
				code: ERROR,
				message: "两次密码不一致"
			});
			return;
		}

		db.find({
			collection: "user",
			query: {username}
		}).then(data => {
			if(data.id){
				res.send({
					code: ERROR,
					message: "已经存在该用户"
				});
				return;
			}
			db.add({
				collection: "user",
				query: {username, password}
			}).then(id => {
				res.send({
					code: SUCCESS,
					message: "注册成功",
					data: id
				});
			}).catch(err => {
				res.send({
					code: ERROR,
					message: err.message || "注册失败"
				});
			});
		}).catch(err => {
			res.send({
				code: ERROR,
				message: err.message || "注册失败"
			});
		});
	},

	//登录
	signIn (req, res) {
		let username = req.body.username,
			password = req.body.password;

		if(!username || !password){
			res.send({
				code: ERROR,
				message: "请输入账号和密码"
			});
			return;
		}

		if(req.method.toLowerCase() != "post"){
			res.send({
				code: ERROR,
				message: "当前请求方式不被支持"
			});
			return;
		}

		db.find({
			collection: "user",
			query: {username, password},
			fields: ['username']
		}).then(data => {
			if(data.id){
				res.send({
					code: SUCCESS,
					message: "登录成功",
					data: data
				});
			}else{
				res.send({
					code: ERROR,
					message: "没有该用户"
				});
			}
		}).catch(err => {
			res.send({
				code: ERROR,
				message: err.message || "登录失败"
			});
		});

	}
}