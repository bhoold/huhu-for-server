<?php
/*
 * @Author: Raven 
 * @Date: 2020-01-15 23:35:17 
 * @Last Modified by: Raven
 * @Last Modified time: 2020-01-16 01:02:29
 */
declare(strict_types = 1);

namespace App\SwooleBundle;



class Error
{
    public static $start_repeat = [
        'number' => 1,
        'desc' => '服务不能启动两次'
    ];

    public static $scheduler_fail = [
        'number' => 2,
        'desc' => '协程调度启动失败'
    ];

    public static $message_nulla = [
        'number' => 3,
        'desc' => '用户注册接口信息异常'
    ];

    public static $start_fail = [
        'number' => 4,
        'desc' => 'tcp server 启动失败'
    ];




}
