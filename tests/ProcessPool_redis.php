<?php
swoole_set_process_name(sprintf('php-ProcPool:%s', 'master'));

echo sprintf("swoole version: %s".PHP_EOL, SWOOLE_VERSION);
echo sprintf("cpu num: %s".PHP_EOL, swoole_cpu_num());
echo sprintf("ip: %s".PHP_EOL, implode(',', swoole_get_local_ip()));
echo sprintf("mac: %s".PHP_EOL, implode(', ', swoole_get_local_mac()));
echo "running".PHP_EOL;


$workerNum = 5;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    $running = true;
    pcntl_signal(SIGTERM, function () use (&$running) {
        $running = false;
    });

    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('127.0.0.1', 6379);
    $key = "key1";
    while ($running) {
         $msgs = $redis->brpop($key, 2);
		 pcntl_signal_dispatch();
         if ( $msgs == null) continue;
         var_dump($msgs);
     }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();