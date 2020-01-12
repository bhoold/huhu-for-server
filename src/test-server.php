<?php //测试文件
swoole_set_process_name(sprintf('php-server:%s', 'master'));

echo sprintf("swoole version: %s".PHP_EOL, SWOOLE_VERSION);
echo sprintf("cpu num: %s".PHP_EOL, swoole_cpu_num());
echo sprintf("ip: %s".PHP_EOL, implode(',', swoole_get_local_ip()));
echo sprintf("mac: %s".PHP_EOL, implode(', ', swoole_get_local_mac()));
echo "running".PHP_EOL;





//将内容进行UNICODE编码
function unicode_encode($name){
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2)
    {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0)
        {    // 两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
        }
        else
        {
            $str .= $c2;
        }
    }
    return $str;
}

//将UNICODE编码后的内容进行解码
function unicode_decode($name){
    // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches))
    {
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++)
        {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0)
            {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            }
            else
            {
                $name .= $str;
            }
        }
    }
    return $name;
}

// 使用 iconv 转换并判断是否等值，效率不高
function is_utf8 ($str) {
  if (count($str) === count(iconv('UTF-8', 'UTF-8//IGNORE', $str))) {
    return true;
  } else {
	return false;
  }
}



$serv = new Swoole\Server("172.18.18.220", 9501);
$serv->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});
$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
    //$serv->send($fd, iconv('UTF-8', 'UCS-2', "S速度woole: ").$data);
	//echo iconv('UCS-2', 'UTF-8', $data);

	$serv->send($fd, $data);
	echo $data;

	echo "\n";
	//echo mb_detect_encoding($data);
    //$serv->close($fd);

});
$serv->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});
$serv->start();







