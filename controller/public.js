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

		db.execute({
			collection: "user",
			action: "find",
			param: {username},
			success (cursor) {
				cursor.toArray(function(err, result){
					if(result.length){
						res.send({
							code: ERROR,
							message: "已经存在该用户"
						});
						return;
					}
					db.execute({
						collection: "user",
						action: "insert",
						param: {username, password},
						success (cursor) {
							cursor.then(function(result){
								res.send({
									code: SUCCESS,
									message: "注册成功"
								});
							}).catch(function(err){
								res.send({
									code: ERROR,
									message: err.message || "注册失败"
								});
							});
						},
						error (err) {
							res.send({
								code: ERROR,
								message: err.message || "注册失败"
							});
						}
					});
				});
			},
			error (err) {
				res.send({
					code: ERROR,
					message: err.message || "注册失败"
				});
			}
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
		db.execute({
			collection: "user",
			action: "find",
			param: {username, password},
			success (cursor) {
				cursor.toArray(function(err, result){
					if(err){
						res.send({
							code: ERROR,
							message: err.message || "登录失败"
						});
						return;
					}
					if(result.length){
						res.send({
							code: SUCCESS,
							message: "登录成功",
							data: {
								id: result[0]._id.toString(),
								username: result[0].username
							}
						});
					}else{
						res.send({
							code: ERROR,
							message: "登录失败"
						});
					}
				});
			},
			error (err) {
				res.send({
					code: ERROR,
					message: err.message || "登录失败"
				});
			}
		});

	}
}