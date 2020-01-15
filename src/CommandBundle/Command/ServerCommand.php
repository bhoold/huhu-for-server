<?php
/*
 * @Author: Raven 
 * @Date: 2020-01-15 19:31:25 
 * @Last Modified by: Raven
 * @Last Modified time: 2020-01-16 00:43:19
 */

namespace App\CommandBundle\Command;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


use App\SwooleBundle\Server\TcpServer;


/**
 * swoole服务
 */
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
        $host = $_ENV['TCP_SERVER_HOST'];
        $port = $_ENV['TCP_SERVER_PORT'];
        $daemonize = $_ENV['TCP_SERVER_DAEMONIZE'];
        $logfile = $_ENV['TCP_SERVER_LOGFILE'];
        if($logfile[0] !== '/') {
            $logfile = $_ENV['PWD'].DIRECTORY_SEPARATOR.$logfile;
        }

        if(TcpServer::start($host, $port, $daemonize, $logfile)) {
            //非守护进程会阻塞，等待server停止后才执行
            $output->writeln("<comment>tcpserver start succeed.</comment>");
        } else {
            $error = TcpServer::getError();
            $output->writeln("<comment>tcpserver start failed [".$error['number']."]: ".$error['desc'].".</comment>");
        }
        return 1;
    }
}