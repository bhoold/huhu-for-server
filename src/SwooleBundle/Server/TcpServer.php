<?php
/*
 * @Author: Raven 
 * @Date: 2020-01-15 19:30:53 
 * @Last Modified by: Raven
 * @Last Modified time: 2020-01-15 23:42:42
 */
declare(strict_types = 1);

namespace App\SwooleBundle\Server;

use Swoole\Coroutine as Co;
use Swoole\Coroutine\Channel as Chan;
use Swoole\Coroutine\Server;
use Swoole\Coroutine\Server\Connection;

use App\SwooleBundle\User;
use App\SwooleBundle\Error;


/**
 * swoole tcp服务模块
 */
class TcpServer
{
    /**
     * 进程名称
     *
     * @var string
     */
    private $processName;

    /**
     * 服务ip
     *
     * @var string
     */
    private $host;

    /**
     * 服务端口
     *
     * @var int
     */
    private $port;

    /**
     * 是否使用ssl
     *
     * @var bool
     */
    private $ssl = false;

    /**
     * 是否复用端口
     *
     * @var bool
     */
    private $reuse_port = false;

    /**
     * 是否守护进程
     *
     * @var boolean
     */
    private $daemonize = false;

    /**
     * 日志文件,在$daemonize为true时使用
     *
     * @var string
     */
    private $logfile;

    /**
     * 错误信息
     *
     * @var array
     */
    private $error = [
        'number' => 0,
        'desc' => ''
    ];

    /**
     * 服务信息
     *
     * @var array
     */
    private $serverInfo = [
        'swoole_ver' => '',
        'cpu_num' => '',
        'ip' => '',
        'mac' => ''
    ];

    /**
     * 私有属性，用于保存实例
     *
     * @var TcpServer
     */
    private static $instance = null;
    
    /**
     * 私有化构造函数
     *
     * @param string $host
     * @param integer $port
     */
    private function __construct(string $host, int $port, bool $daemonize, string $logfile)
    {
        $this->processName = sprintf('huhu-tcp-server:%s', 'master');
        $this->host = $host;
        $this->port = $port;
        $this->daemonize = $daemonize;
        $this->logfile = $logfile;
        
        $this->serverInfo = [
            'swoole_ver' => SWOOLE_VERSION,
            'cpu_num' => swoole_cpu_num(),
            'ip' => implode('|', swoole_get_local_ip()),
            'mac' => implode('|', swoole_get_local_mac()),
        ];
    }

    /**
     * 启动服务
     *
     * @param string $host
     * @param integer $port
     * @return boolean
     */
    public static function start(string $host, int $port, bool $daemonize, string $logfile): bool
    {
        if(!(self::$instance instanceof self)){
            self::$instance = new self($host, $port, $daemonize, $logfile);
        } else {
            self::$instance->error = Error::$start_repeat;
            return false;
        }
        
        $instc = self::$instance;

        swoole_set_process_name($instc->processName);

        $scheduler = new Co\Scheduler;
        $scheduler->add(function () use($instc) {
            $serv = new Server($instc->host, $instc->port, $instc->ssl, $instc->reuse_port);
            $serv->set(array(
                'reactor_num' => 2, //数值与cpu核心数量相同或2倍
                'worker_num' => 2, //数值与cpu核心数量相同或2倍
                //'package_eof' => "\r\n\r\n",  //数据分隔标识，package_eof的设置有\n，则导致与mfc客户端的CString类型冲，突造成解析不完整
                //'open_eof_check' => 1,
                'daemonize' => $instc->daemonize, //守护进程
                'log_file' => $instc->logfile
            ));
            echo 'tcpserver test: ';
            var_dump($instc->daemonize);
            $serv->handle(function (Connection $conn) {
                while('' !== $message = $conn->recv()) {
                    $msgReq = json_decode($message, true);
                    if(is_array($msgReq) && isset($msgReq['msgid']) && isset($msgReq['type'])) {
                        
                        switch($msgReq['type']) {
                            case 'register':
                                echo 'register'.PHP_EOL;

                                $form = [
                                    'account' => $msgReq["account"],
                                    'password' => $msgReq["password"],
                                    'repassword' => $msgReq["repassword"]
                                ];
                                if(true === $error = User::register($form)) {
                                    echo 'register注册成功';
                                } else {
                                    echo 'register注册失败 ['.$error['number'].']: '.$error['desc'];
                                }




                                /*
                                echo "连接数据库...";
                                $host = '172.18.18.222';
                                $port = '3306';
                                $user = 'root';
                                $password = '123456';
                                $db = 'huhuim';

                                $link = mysqli_connect($host.':'.$port, $user, $password, $db);
                                if (!$link) {
                                    die('mysqli_connect Connect Error (' . mysqli_connect_errno() . ') '
                                            . mysqli_connect_error());
                                }
                                echo 'mysqli_connect Success... ' . mysqli_get_host_info($link) . "\n";
                                mysqli_close($link);
                                */
                                /*
                                $swoole_mysql = new Co\MySQL();
                                if(true === $swoole_mysql->connect([
                                    'host' => '172.18.18.222',
                                    'port' => 3306,
                                    'user' => 'root',
                                    'password' => '123456',
                                    'database' => 'sys',
                                    'timeout' => 5
                                ])) {
                                    echo "数据库连接成功\n";
                                    $res = $swoole_mysql->query('select * from sys_config');
                                    if($res === false) {
                                        return;
                                    }
                                    foreach ($res as $value) {
                                        print_r($value);
                                    }
                                } else {
                                    echo "数据库连接失败\n";
                                }
                                */
                                /*
                                $cli = HttpClient::getInstance('172.18.18.220');
                                echo $cli->getEnd('/index.php/user').PHP_EOL;
                                */


                                /*

                                $chan = new Chan();
                                go(function($arrForm, $chan) use ($output) {
                                    $msg = "sdfe";
                                    if(false === $result = $chan->push($msg)) {
                                        $output->writeln("<comment>register::chan->push失败</comment>");
                                    }
                                }, array(
                                    "account" => $msgReq["account"],
                                    "password" => $msgReq["password"], 
                                    "repassword" => $msgReq["repassword"]
                                ), $chan);
                                if(false !== $result = $chan->pop()) {
                                    echo $result;
                                } else {
                                    $output->writeln("<comment>register::chan->pop失败</comment>");
                                }*/
                            break;
                            case 'login':

                            break;
                            case 'chat':
                            break;
                            case 'list':
                            break;
                        }

                        // 回复消息到达
                        $msgRecv = array(
                            'msgid' => $msgReq['msgid'],
                            'status' => 'ok'
                        );
                        $conn->send(json_encode($msgRecv));
                    } else {
                        //todo: 业务不需要处理的，用日志记录用于分析
                    }
                }
            });
            $serv->start();// start后会阻塞
        });

		if(false === $scheduler->start()) {
            $instc->error = Error::$scheduler_fail;
            return false;
        }
        
        return true;
    }

    /**
     * 返回错误信息
     *
     * @return array
     */
    public static function getError(): array
    {
        return self::$instance->error;
    }
}
