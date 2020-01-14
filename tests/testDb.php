<?php
header('content-type:text/html;charset=utf-8');
$host = '172.18.18.222';
$port = '3306';
$user = 'root';
$password = '123456';
$db = 'sys';

if(function_exists('mysqli_connect')){ //mysqli_connect是mysql构造函数的别名，实际不需要两个判断
    $link = mysqli_connect($host.':'.$port, $user, $password, $db);
    if (!$link) {
        die('mysqli_connect Connect Error (' . mysqli_connect_errno() . ') '
                . mysqli_connect_error());
    }
    echo 'mysqli_connect Success... ' . mysqli_get_host_info($link) . "\n";
    mysqli_close($link);
}else if(function_exists('mysqli')){
    $link = new mysqli($host, $user, $password, $db);
    if ($link->connect_error) {
        die('mysqli Connect Error (' . $link->connect_errno . ') '. $link->connect_error);
    }
    echo 'mysqli Success... ' . $mysqli->host_info . "\n";
    $mysqli->close();
}else if(function_exists('PDO')){
    $dsn = "mysql:host={$host};dbname={$db}";
    $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    );
    try {
        $link = new PDO($dsn, $user, $password, $options);
        echo 'pdo Success...';
    }catch(PDOException $e){
        die('pdo Connect Error '.$e->getMessage());
    }
}else{
    echo 'no driver';
}

