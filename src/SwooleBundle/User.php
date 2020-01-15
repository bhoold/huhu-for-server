<?php
/*
 * @Author: Raven 
 * @Date: 2020-01-15 19:31:06 
 * @Last Modified by: Raven
 * @Last Modified time: 2020-01-15 23:42:48
 */
declare(strict_types = 1);

namespace App\SwooleBundle;

use App\SwooleBundle\Client\HttpClient;
use App\SwooleBundle\Error;



/**
 * 用户模块
 */
class User
{
    public function register(array $info)
    {
        $cli = HttpClient::getInstance('172.18.18.209', 1073, '/mocms/index.php/');
        $result = $cli->post('user/login', $info);

        $arrMsg = json_decode($result, true);
        if(!is_array($arrMsg)) {
            return Error::$message_nulla;
        } else {
            return true;
        }
    }
}
