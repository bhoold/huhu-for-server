<?php
swoole_set_process_name(sprintf('php-udp-server:%s', 'master'));

echo sprintf("swoole version: %s".PHP_EOL, SWOOLE_VERSION);
echo sprintf("cpu num: %s".PHP_EOL, swoole_cpu_num());
echo sprintf("ip: %s".PHP_EOL, implode(',', swoole_get_local_ip()));
echo sprintf("mac: %s".PHP_EOL, implode(', ', swoole_get_local_mac()));
echo "running".PHP_EOL;


//创建Server对象，监听 127.0.0.1:9502端口，类型为SWOOLE_SOCK_UDP
$serv = new swoole_server("127.0.0.1", 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP); 

//监听数据接收事件
$serv->on('Packet', function ($serv, $data, $clientInfo) {
    $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$data);
    var_dump($clientInfo);
});

//启动服务器
$serv->start(); 