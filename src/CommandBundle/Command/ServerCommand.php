<?php
namespace App\CommandBundle\Command;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


use App\Controller\LuckyController;

use Swoole\Coroutine as Co;
use Swoole\Coroutine\Channel as Chan;
use Swoole\Coroutine\Server;
use Swoole\Coroutine\Server\Connection;



class ServerCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {

        $this
        // the name of the command (the part after "bin/console")
        // 命令的名字（"bin/console" 后面的部分）
        ->setName('server:start')
 
        // the short description shown while running "php bin/console list"
        // 运行 "php bin/console list" 时的简短描述
        ->setDescription('Run Server')
 
        // the full command description shown when running the command with
        // the "--help" option
        // 运行命令时使用 "--help" 选项时的完整命令描述
        ->setHelp("This command allows you to run Server...");
    
    
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //var_dump($_ENV["TCP_SERVER_IP"]);echo PHP_EOL;
        $server_host = $_ENV["TCP_SERVER_HOST"];
        $server_port = $_ENV["TCP_SERVER_PORT"];
        $ssl = false; // 是否使用ssl
        $reuse_port = false; //是否端口复用

        $serverName = sprintf('huhu-tcp-server:%s', 'master');
		swoole_set_process_name($serverName);
        $output->writeln("<comment>${serverName} running</comment>");
		echo sprintf("swoole version: %s".PHP_EOL, SWOOLE_VERSION);
		echo sprintf("cpu num: %s".PHP_EOL, swoole_cpu_num());
		echo sprintf("ip: %s".PHP_EOL, implode('|', swoole_get_local_ip()));
		echo sprintf("mac: %s".PHP_EOL, implode('|', swoole_get_local_mac()));




        $scheduler = new Co\Scheduler;
        $scheduler->add(function () 
            use (
                $output, 
                $server_host, 
                $server_port, 
                $ssl, 
                $reuse_port
            ) {
                $serv = new Server($server_host, $server_port, $ssl, $reuse_port);
                $serv->set(array(
                    'reactor_num' => 2, //数值与cpu核心数量相同或2倍
                    'worker_num' => 2, //数值与cpu核心数量相同或2倍
                    //package_eof的设置有\n，则导致与mfc客户端的CString类型冲，突造成解析不完整
                    //'package_eof' => "\r\n\r\n",  //http协议就是以\r\n\r\n作为结束符的，这里也可以使用二进制内容
                    //'open_eof_check' => 1,
                    //'daemonize' => true, //守护进程
                ));
                $serv->handle(function (Connection $conn) use ($output, $serv) {

                    while("" !== $message = $conn->recv()) {
                        $msgReq = json_decode($message, true);
                        if(is_array($msgReq) && isset($msgReq["msgid"]) && isset($msgReq["type"])) {
                            
                            switch($msgReq["type"]) {
                                case "register":
                                    go(function($account, $password, $repassword) {
                                        echo "注册了${account}, ${password}, ${repassword}";
                                    }, $msgReq["account"], $msgReq["password"], $msgReq["repassword"]);
                                break;
                                case "login":

                                break;
                                case "chat":
                                break;
                                case "list":
                                break;
                                
                            }

                            $msgRecv = array(
                                "msgid" => $msgReq["msgid"],
                                "status" => "ok"
                            );
                            $conn->send(json_encode($msgRecv));
                        }
                    }
                    
                    $output->writeln("<comment>disconnected</comment>");
                });
                //echo LuckyController::number();

                $output->writeln("<comment>server start...</comment>");
                $serv->start();
            }
        );


		if(false == $scheduler->start()) {
			echo "scheduler run failed.\n";
		}


        return 1;
    }
}