<?php
namespace AppBundle\Command;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


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
        $output->writeln("<comment>Server start...</comment>");
        


        return 1;
    }
}