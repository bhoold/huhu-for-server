<?php
swoole_set_process_name(sprintf('php-server:%s', 'master'));

go(function () {
    $redis = new Swoole\Coroutine\Redis();
    $redis->connect('127.0.0.1', 6379);
    $val = $redis->get('key');
});