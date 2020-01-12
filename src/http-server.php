<?php
// 注意：确保客户端使用unicode字符集utf-8编码通信
include_once "../config/constant.php";


(new class{
    private $localhost = "0.0.0.0";
    private $port = 80;
    private $mpid = 0;
    private $works = [];
	private $reactorNum = 2; //数值与cpu核心数量相同或2倍
	private $workerNum = 2; //数值与cpu核心数量相同或2倍

    public function __construct(){


		$serverName = sprintf('huhu-http-server:%s', 'master');
		swoole_set_process_name($serverName);
		
		echo sprintf("swoole version: %s".PHP_EOL, SWOOLE_VERSION);
		echo sprintf("cpu num: %s".PHP_EOL, swoole_cpu_num());
		echo sprintf("ip: %s".PHP_EOL, implode(',', swoole_get_local_ip()));
		echo sprintf("mac: %s".PHP_EOL, implode(', ', swoole_get_local_mac()));
		echo "${serverName} running".PHP_EOL;


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

		$serv = new Swoole\Http\Server($this->localhost, $this->port /*, $mode, $sock_type*/);
		$serv->set(array(
			'reactor_num' => $this->reactorNum,
			'worker_num' => $this->workerNum,
            'document_root' => WWWPATH, // v4.4.0以下版本, 此处必须为绝对路径
            'enable_static_handler' => true,
			//package_eof设置有\n导致mfc客户端解析不完整
			//'package_eof' => "\r\n\r\n",  //http协议就是以\r\n\r\n作为结束符的，这里也可以使用二进制内容
            //'open_eof_check' => 1,
            //'daemonize' => true, //守护进程
		));

        
        $serv->on('request', function ($request, $response) {
            $fileName = $request->server['path_info'];
            if(file_exists(WWWPATH.$fileName)) {
            } else {
                
            }
            $response->redirect("index.html");
            //$response->end("<h1>Hello Swoole. #".rand(1000, 9999));


        });
        /*
		$serv->on('start', array($this, 'onStart'));
		$serv->on('managerStart', array($this, 'onManagerStart'));
		$serv->on('workerStart', array($this, 'onWorkerStart'));
		$serv->on('connect', array($this, 'onConnect'));
		$serv->on('close', array($this, 'onClose'));
		$serv->on('receive', array($this, 'onReceive'));
		$serv->on('workerError', array($this, 'onWorkerError'));
        */

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
		echo "Server master pid: ".$serv->master_pid.PHP_EOL;
		echo "Server manager pid: ".$serv->manager_pid.PHP_EOL;
	}



	public function onManagerStart($serv) {
		/*
			manager进程中可以调用sendMessage接口向其他工作进程发送消息
			onManagerStart触发时，说明：
			Task和Worker进程已创建
			Master进程状态不明，因为Manager与Master是并行的，onManagerStart回调发生是不能确定Master进程是否已就绪
		*/
		
		swoole_set_process_name(sprintf('huhu-tcp-server:%s', 'manager_'.$worker_id));
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

		swoole_set_process_name(sprintf('huhu-tcp-server:%s', ($server->taskworker?'task_':'worker_').$worker_id));
		
		echo "Server worker_${worker_id} pid: ".$serv->worker_pid.PHP_EOL;
	}



	public function onConnect($serv, $fd, $reactor_id) {
		/*
			worker进程回调
		*/

		if(count($serv->connections) > 500) {
			$serv->close($fd, true);
		}

		$clinfo = $serv->getClientInfo($fd);
		
		echo sprintf("用户端连接%s:%s 当前服务器共有 %s 个连接\n", $clinfo['remote_ip'], $clinfo['remote_port'], count($serv->connections));
	}



	public function onClose($serv, $fd, $reactor_id) {
		/*
			onClose 回调函数如果发生了致命错误，会导致连接泄漏。通过 netstat 命令会看到大量 CLOSE_WAIT 状态的 TCP 连接
			onClose中依然可以调用getClientInfo方法获取到连接信息，在onClose回调函数执行完毕后才会调用close关闭TCP连接
			当服务器主动关闭连接时，底层会设置此参数为-1，可以通过判断$reactor_id < 0来分辨关闭是由服务器端还是客户端发起的。
			只有在PHP代码中主动调用close方法被视为主动关闭
		*/

		$clinfo = $serv->getClientInfo($fd);

		$name = "用户";
		if($reactor_id < 0) {
			$name = "服务";
		}
		echo sprintf("%s端正在关闭%s:%s 当前服务器共有 %s 个连接\n", $name, $clinfo['remote_ip'], $clinfo['remote_port'], count($serv->connections));


	}



	public function onReceive($serv, $fd, $reactor_id, $data) {
		/*
			udp协议只有onReceive事件
			在1.7.15以上版本中，当设置dispatch_mode = 1/3时会自动去掉onConnect/onClose事件回调
		*/
		$serv->send($fd, $data);
		echo $data;
		echo "\n";
		/*
		echo $data;echo "\r\n";
		$timerId = $serv->tick(1000, function() use ($serv, $fd) {
			if($serv->exist($fd))
				$serv->send($fd, "hello world\n");
			else
				Swoole\Timer::clear($timerId);
		});
		*/
	}


	public function onWorkerError($serv, $worker_id, $worker_pid, $exit_code, $signal) {
		/*
			signal = 11：说明Worker进程发生了segment fault段错误，可能触发了底层的BUG，请收集core dump信息和valgrind内存检测日志，向我们反馈此问题
			exit_code = 255：说明Worker进程发生了Fatal Error致命错误，请检查PHP的错误日志，找到存在问题的PHP代码，进行解决
			signal = 9：说明Worker被系统强行Kill，请检查是否有人为的kill -9操作，检查dmesg信息中是否存在OOM（Out of memory）
			如果存在OOM，分配了过大的内存。检查Server的setting配置，是否创建了非常大的Swoole\Table、Swoole\Buffer等内存模块
		*/

		echo sprintf("$worker_id:%s $worker_pid:%s $exit_code:%s $signal:%s\n", $worker_id, $worker_pid, $exit_code, $signal);

	}




});



