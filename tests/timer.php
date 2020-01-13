<?php
swoole_set_process_name(sprintf('php-timer'));

echo sprintf("swoole version: %s".PHP_EOL, SWOOLE_VERSION);
echo sprintf("cpu num: %s".PHP_EOL, swoole_cpu_num());
echo sprintf("ip: %s".PHP_EOL, implode(',', swoole_get_local_ip()));
echo sprintf("mac: %s".PHP_EOL, implode(', ', swoole_get_local_mac()));
echo "running".PHP_EOL;


//每隔2000ms触发一次
$timerId1 = swoole_timer_tick(2000, function ($timer_id) {
    echo "tick-2000ms\n";
});

//3000ms后执行此函数
$timerId2 = swoole_timer_after(3000, function () {
    echo "after 3000ms.\n";
});

//swoole_timer_clear($timerId1);