let users = {};

module.exports = {
	exist (user) {
		let flag = false;
		if(user.id && users[user.id]){
			flag = true;
		}
		return flag;
	},
	add (user, socket) {
		let flag = false;
		if(user.id && user.username){
			if(users[user.id]){
				if(!users[user.id].sockets.includes(socket)){
					users[user.id].sockets.push(socket);
					flag = true;
				}
			}else{
				users[user.id] = {
					username: user.username,
					sockets: [socket]
				};
				flag = true;
			}
		}
		return flag;
	},
	delete (user, socket) {
		let flag = false;
		if(user.id){
			if(users[user.id]){
				let index = users[user.id].sockets.indexOf(socket);
				if(index > -1){
					users[user.id].sockets.splice(index, 1);
					flag = true;
				}
			}
		}
		return flag;
	},
	list () {
		return users;
	},
	sockets (user) {
		let list = [];
		if(user.id && users[user.id]){
			list = users[user.id].sockets;
		}
		return list;
	}
}