const db = require('../database');

module.exports = {
	//账号注册
	signUp (req, res, param) {
		let username = param[0],
			password = param[1],
			repassword = param[2];
		if(password !== repassword){
			res.send("2次密码不一致");
			return;
		}

		if(db.table('user').find({username})){
			res.send("已经存在该用户");
		}else{
			if(db.table('user').create({username, password})){
				res.send("创建用户成功");
			}else{
				res.send("创建用户失败");
			}
		}
	},

	//登录
	signIn (req, res, param) {
		let username = param[0],
			password = param[1];

		if(db.table('user').find({username, password})){
			res.send("登录成功");
		}else{
			res.send("登录失败");
		}
	}
}