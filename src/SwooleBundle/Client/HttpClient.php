<?php
/*
 * @Author: Raven 
 * @Date: 2020-01-15 19:30:34 
 * @Last Modified by: Raven
 * @Last Modified time: 2020-01-15 23:17:33
 */
declare(strict_types = 1);

namespace App\SwooleBundle\Client;

use Swoole\Coroutine as Co;
use Swoole\Coroutine\Channel as Chan;
use Swoole\Coroutine\Server;
use Swoole\Coroutine\Server\Connection;

/**
 * 协程http模块
 */
class HttpClient
{
    /**
     * 域名或ip
     * 
     * @var string
     */
    private $host;

    /**
     * 端口
     * 
     * @var int
     */
    private $port;

    /**
     * 入口
     * 
     * @var string
     */
    private $path;

    /**
     * http头
     * 
     * @var array
     */
    private $headers = [
        'Host' => "localhost",
        "User-Agent" => 'Chrome/49.0.2587.3',
        'Accept' => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
    ];

    /**
     * 连接设置
     *
     * @var array
     */
    private $config = [
        'timeout' => 1
    ];

    /**
     * 私有属性，用于保存实例
     *
     * @var HttpClient
     */
    private static $instance = null;

    /**
     * 构造函数
     *
     * @param string $host
     * @param integer $port
     * @param string $path
     */
    private function __construct(string $host, int $port, string $path)
    {
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
    }

    /**
     * 获取类实例
     *
     * @param string $host
     * @param integer $port
     * @param string $path
     * @return HttpClient
     */
    public static function getInstance(string $host, int $port = 80, string $path = "/index.php/"): HttpClient
    {
        if(!(self::$instance instanceof self)){
            self::$instance = new self($host, $port, $path);
        }
        return self::$instance;
    }

    /**
     * 克隆方法私有化，防止复制实例
     *
     * @return void
     */
    private function __clone(){}


    /**
     * 使用get获取
     *
     * @param string $action
     * @return string
     */
    public function get(string $action, array $param = []): string
    {
        $cli = new Co\Http\Client($this->host, $this->port);
        $cli->setHeaders($this->headers);
        $cli->set($this->config);
        $cli->get($this->path.$action);
        $content = $cli->body;
        $cli->close();
        return $content;
    }

    public function post(string $action, array $param = []): string
    {
        $cli = new Co\Http\Client($this->host, $this->port);
        $cli->setHeaders($this->headers);
        $cli->set($this->config);
        $cli->get($this->path.$action);
        $content = $cli->body;
        $cli->close();
        return $content;
    }

}
