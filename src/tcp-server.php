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
			$host��������ָ��������ip��ַ����127.0.0.1������������ַ������0.0.0.0����ȫ����ַ
				IPv4ʹ�� 127.0.0.1��ʾ����������0.0.0.0��ʾ�������е�ַ
				IPv6ʹ��::1��ʾ����������:: (�൱��0:0:0:0:0:0:0:0) ��ʾ�������е�ַ
			$port�����Ķ˿ڣ���9501
				���$sock_typeΪUnixSocket Stream/Dgram���˲�����������
				����С��1024�˿���ҪrootȨ��
				����˶˿ڱ�ռ��server->startʱ��ʧ��
			$mode���е�ģʽ
				SWOOLE_PROCESS�����ģʽ��Ĭ�ϣ�
				SWOOLE_BASE����ģʽ
			$sock_typeָ��Socket�����ͣ�֧��TCP��UDP��TCP6��UDP6��UnixSocket Stream/Dgram 6��
			ʹ��$sock_type | SWOOLE_SSL��������SSL������ܡ�����SSL���������ssl_key_file��ssl_cert_file
			
			1.7.11�汾�����˶�Unix Socket��֧�֣���ϸ��μ� /wiki/page/16.html
			���캯���еĲ�����swoole_server::addlistener������ȫ��ͬ��
			�߸��صķ�����������ص���Linux�ں˲���
			1.9.6����������������ö˿ڵ�֧�֣�$port������������Ϊ0������ϵͳ���������һ�����õĶ˿ڣ����м���������ͨ����ȡ$server->port�õ����䵽�Ķ˿ںš�
			1.9.7�����˶�systemd socket��֧�֡������˿���systemd����ָ��
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
			�ڴ��¼�֮ǰServer�ѽ��������²���

				�Ѵ�����manager����
				�Ѵ�����worker�ӽ���
				�Ѽ�������TCP/UDP/UnixSocket�˿ڣ���δ��ʼAccept���Ӻ�����
				�Ѽ����˶�ʱ��
			������Ҫִ��

				��Reactor��ʼ�����¼����ͻ��˿���connect��Server
			onStart�ص��У�������echo����ӡLog���޸Ľ������ơ�����ִ������������onWorkerStart��onStart�ص����ڲ�ͬ�����в���ִ�еģ��������Ⱥ�˳��

			������onStart�ص��У���$serv->master_pid��$serv->manager_pid��ֵ���浽һ���ļ��С��������Ա�д�ű�����������PID�����ź���ʵ�ֹرպ������Ĳ�����

			onStart�¼���Master���̵����߳��б����á�
			BASEģʽ��û��master���̣���˲�����onStart�¼����벻Ҫ��BASEģʽ��ʹ��ʹ��onStart�ص�������
		*/

		echo "Server: started\n";
	}



	public function onWorkerStart($serv, $worker_id) {
		/*
			���¼���Worker����/Task��������ʱ���������ﴴ���Ķ�������ڽ�������������ʹ��

			onWorkerStart/onStart�ǲ���ִ�еģ�û���Ⱥ�˳��
			����ͨ��$server->taskworker�������жϵ�ǰ��Worker���̻���Task����
			������worker_num��task_worker_num����1ʱ��ÿ�����̶��ᴥ��һ��onWorkerStart�¼�����ͨ���ж�$worker_id���ֲ�ͬ�Ĺ�������
			�� worker ������ task ���̷�������task ���̴�����ȫ������֮��ͨ��onFinish�ص�����֪ͨ worker ���̡����磬�����ں�̨������ʮ����û�Ⱥ��֪ͨ�ʼ���������ɺ������״̬��ʾΪ�����У���ʱ���ǿ��Լ����������������ʼ�Ⱥ����Ϻ󣬲�����״̬�Զ���Ϊ�ѷ��͡�

			$worker_id��һ����[0-$worker_num)�����ڵ����֣���ʾ���Worker���̵�ID
			$worker_id�ͽ���PIDû���κι�ϵ����ʹ��posix_getpid������ȡPID
			2.1.0�汾onWorkerStart�ص������д�����Э�̣���onWorkerStart���Ե���Э��API

		*/
		
		swoole_set_process_name(sprintf('huhu-tcp-server:%s', 'worker_'.$worker_id));

		echo "Server: workerStart\n";
	}











});



