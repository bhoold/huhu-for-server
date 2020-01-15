<?php
/*
 * @Author: Raven 
 * @Date: 2020-01-16 01:57:28 
 * @Last Modified by: Raven
 * @Last Modified time: 2020-01-16 01:58:44
 */
declare(strict_types = 1);

namespace App\SwooleBundle\Server;


class Event
{

    private function onStart(Server $server)
    {
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
		echo "Server master pid: ".$server->master_pid.PHP_EOL;
		echo "Server manager pid: ".$server->manager_pid.PHP_EOL;
    }

    private function onManagerStart(Server $server)
    {
		/*
			manager进程中可以调用sendMessage接口向其他工作进程发送消息
			onManagerStart触发时，说明：
			Task和Worker进程已创建
			Master进程状态不明，因为Manager与Master是并行的，onManagerStart回调发生是不能确定Master进程是否已就绪
		*/
		
        swoole_set_process_name(sprintf('huhu-tcp-server:%s', 'manager_'.$worker_id));
    }

    private function onWorkerStart(Server $server, int $worker_id)
    {
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

    private function onConnect(Server $server, int $fd, int $reactorId)
    {
        $clinfo = $server->getClientInfo($fd);
		
		echo sprintf("用户端连接%s:%s 当前服务器共有 %s 个连接\n", $clinfo['remote_ip'], $clinfo['remote_port'], count($server->connections));
    }

    private function onReceive(Server $server, int $fd, int $reactor_id, string $data)
    {
		/*
			udp协议只有onReceive事件
			在1.7.15以上版本中，当设置dispatch_mode = 1/3时会自动去掉onConnect/onClose事件回调
        */
        
		$server->send($fd, $data);
		echo $data;
		echo "\n";
    }

    private function onClose(Server $server, int $fd, int $reactorId)
    {
		/*
			onClose 回调函数如果发生了致命错误，会导致连接泄漏。通过 netstat 命令会看到大量 CLOSE_WAIT 状态的 TCP 连接
			onClose中依然可以调用getClientInfo方法获取到连接信息，在onClose回调函数执行完毕后才会调用close关闭TCP连接
			当服务器主动关闭连接时，底层会设置此参数为-1，可以通过判断$reactor_id < 0来分辨关闭是由服务器端还是客户端发起的。
			只有在PHP代码中主动调用close方法被视为主动关闭
        */
        
		$clinfo = $server->getClientInfo($fd);

		$name = "用户";
		if($reactor_id < 0) {
			$name = "服务";
		}
		echo sprintf("%s端正在关闭%s:%s 当前服务器共有 %s 个连接\n", $name, $clinfo['remote_ip'], $clinfo['remote_port'], count($server->connections));

    }

    private function onWorkerError(Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {
		/*
			signal = 11：说明Worker进程发生了segment fault段错误，可能触发了底层的BUG，请收集core dump信息和valgrind内存检测日志，向我们反馈此问题
			exit_code = 255：说明Worker进程发生了Fatal Error致命错误，请检查PHP的错误日志，找到存在问题的PHP代码，进行解决
			signal = 9：说明Worker被系统强行Kill，请检查是否有人为的kill -9操作，检查dmesg信息中是否存在OOM（Out of memory）
			如果存在OOM，分配了过大的内存。检查Server的setting配置，是否创建了非常大的Swoole\Table、Swoole\Buffer等内存模块
		*/

		echo sprintf("$worker_id:%s $worker_pid:%s $exit_code:%s $signal:%s\n", $worker_id, $worker_pid, $exit_code, $signal);
    }
}
