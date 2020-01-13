<?php
swoole_set_process_name(sprintf('php-client'));

echo sprintf("swoole version: %s".PHP_EOL, SWOOLE_VERSION);
echo sprintf("cpu num: %s".PHP_EOL, swoole_cpu_num());
echo sprintf("ip: %s".PHP_EOL, implode(',', swoole_get_local_ip()));
echo sprintf("mac: %s".PHP_EOL, implode(', ', swoole_get_local_mac()));
echo "running".PHP_EOL;



//同步方式
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9601, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("{\"msgid\":\"111\",\"type\":\"register\",\"account\":\"myname\",\"password\":\"123456\",\"repassword\":\"123456\"}");
echo $client->recv();
$client->close();



/*
//协程方式
go(function() {
	$client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
	if (!$client->connect('127.0.0.1', 9501, 0.5))
	{
		exit("connect failed. Error: {$client->errCode}\n");
	}
	$client->send("hello world\n");
	$i = 1;
	while($i--) {
		echo $client->recv();
	}
	$client->close();
});
*/


/*
//进程池方式
$workerNum = 1;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} is started\n";

	$client = new Swoole\Client(SWOOLE_SOCK_TCP);
	if (!$client->connect('127.0.0.1', 9601, -1)) {
		exit("connect failed. Error: {$client->errCode}\n");
	}

	$client->send("hello world\n");

	$i = 1;
	while($i--) {
		echo $client->recv();
	}
	$client->close();

});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();
*/















/*
//异步方式
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

//注册连接成功回调
$client->on("connect", function($cli) {
    $cli->send("hello world\n");
});

//注册数据接收回调
$client->on("receive", function($cli, $data){
    echo "Received: ".$data."\n";
});

//注册连接失败回调
$client->on("error", function($cli){
    echo "Connect failed\n";
});

//注册连接关闭回调
$client->on("close", function($cli){
    echo "Connection close\n";
});

//发起连接
$client->connect('127.0.0.1', 9501, 0.5);
*/






