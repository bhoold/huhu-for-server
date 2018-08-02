module.exports = {
	index (req, res) {
		res.send("我是用户首页");
	},
	create (req, res, parm) {
		console.log(parm)
		res.send("我是用户添加页");
	}
}