<?php

$serverName = sprintf('huhu-tcp-server:%s', 'master');
swoole_set_process_name($serverName);

echo sprintf("swoole version: %s".PHP_EOL, SWOOLE_VERSION);
echo sprintf("cpu num: %s".PHP_EOL, swoole_cpu_num());
echo sprintf("ip: %s".PHP_EOL, implode(',', swoole_get_local_ip()));
echo sprintf("mac: %s".PHP_EOL, implode(', ', swoole_get_local_mac()));
echo "${serverName} running".PHP_EOL;




(new class{
    private $localhost="127.0.0.1";
    private $port=9501;
    private $mpid=0;
    private $works=[];
	private $workerNum = 2;

    public function __construct(){
        try {
            $this->mpid = posix_getpid();
            $this->run();
        }catch (\Exception $e){
            die('ALL ERROR: '.$e->getMessage());
        }
    }

    public function run(){
		/*
			$host参数用来指定监听的ip地址，如127.0.0.1，或者外网地址，或者0.0.0.0监听全部地址
				IPv4使用 127.0.0.1表示监听本机，0.0.0.0表示监听所有地址
				IPv6使用::1表示监听本机，:: (相当于0:0:0:0:0:0:0:0) 表示监听所有地址
			$port监听的端口，如9501
				如果$sock_type为UnixSocket Stream/Dgram，此参数将被忽略
				监听小于1024端口需要root权限
				如果此端口被占用server->start时会失败
			$mode运行的模式
				SWOOLE_PROCESS多进程模式（默认）
				SWOOLE_BASE基本模式
			$sock_type指定Socket的类型，支持TCP、UDP、TCP6、UDP6、UnixSocket Stream/Dgram 6种
			使用$sock_type | SWOOLE_SSL可以启用SSL隧道加密。启用SSL后必须配置ssl_key_file和ssl_cert_file
			
			1.7.11版本增加了对Unix Socket的支持，详细请参见 /wiki/page/16.html
			构造函数中的参数与swoole_server::addlistener中是完全相同的
			高负载的服务器，请务必调整Linux内核参数
			1.9.6增加了随机监听可用端口的支持，$port参数可以设置为0，操作系统会随机分配一个可用的端口，进行监听。可以通过读取$server->port得到分配到的端口号。
			1.9.7增加了对systemd socket的支持。监听端口由systemd配置指定
		*/

		$serv = new Swoole\Server($this->localhost, $this->port /*, $mode, $sock_type*/);
		$serv->set(array(
			'worker_num' => $this->workerNum
		));

		$serv->on('start', array($this, 'onStart'));
		$serv->on('workerStart', array($this, 'onWorkerStart'));


		if($serv->start()) {
			echo "run failed.\n";
		}
    }

	
	public function onStart($serv) {
		/*
			在此事件之前Server已进行了如下操作

				已创建了manager进程
				已创建了worker子进程
				已监听所有TCP/UDP/UnixSocket端口，但未开始Accept连接和请求
				已监听了定时器
			接下来要执行

				主Reactor开始接收事件，客户端可以connect到Server
			onStart回调中，仅允许echo、打印Log、修改进程名称。不得执行其他操作。onWorkerStart和onStart回调是在不同进程中并行执行的，不存在先后顺序。

			可以在onStart回调中，将$serv->master_pid和$serv->manager_pid的值保存到一个文件中。这样可以编写脚本，向这两个PID发送信号来实现关闭和重启的操作。

			onStart事件在Master进程的主线程中被调用。
			BASE模式下没有master进程，因此不存在onStart事件。请不要在BASE模式中使用使用onStart回调函数。
		*/

		echo "Server: started\n";
	}



	public function onWorkerStart($serv, $worker_id) {
		/*
			此事件在Worker进程/Task进程启动时发生。这里创建的对象可以在进程生命周期内使用

			onWorkerStart/onStart是并发执行的，没有先后顺序
			可以通过$server->taskworker属性来判断当前是Worker进程还是Task进程
			设置了worker_num和task_worker_num超过1时，每个进程都会触发一次onWorkerStart事件，可通过判断$worker_id区分不同的工作进程
			由 worker 进程向 task 进程发送任务，task 进程处理完全部任务之后通过onFinish回调函数通知 worker 进程。例如，我们在后台操作向十万个用户群发通知邮件，操作完成后操作的状态显示为发送中，这时我们可以继续其他操作。等邮件群发完毕后，操作的状态自动改为已发送。

			$worker_id是一个从[0-$worker_num)区间内的数字，表示这个Worker进程的ID
			$worker_id和进程PID没有任何关系，可使用posix_getpid函数获取PID
			2.1.0版本onWorkerStart回调函数中创建了协程，在onWorkerStart可以调用协程API

		*/
		
		swoole_set_process_name(sprintf('huhu-tcp-server:%s', 'worker_'.$worker_id));

		echo "Server: workerStart\n";
	}











});



