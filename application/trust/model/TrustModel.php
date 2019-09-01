<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/2
 * Time: 0:16
 */

namespace app\trust\model;

use think\Model;

/**
 * Class TrustModel
 * @package app\trust\model
 */
class TrustModel extends Model
{
    protected $table = "su_trust";

    protected $connection = [
        // 数据类型
        'type' => 'mysql',
        // 数据库连接dsn配置
        'dsn' => '',
        // 服务器地址
        'hostname' => '120.79.164.128',
        // 数据库名
        'database' => 'supervise',
        // 数据库用户名
        'username' => 'root',
        // 数据库密码
        'password' => '123456',
        // 数据库连接端口
        'hostport' => 3306,
        // 数据库连接参数
        'params' => [],
        // 数据库默认编码采用utf-8
        'charset' => 'utf8',
        // 数据表前缀
        'prefix' => 'su_',
    ];
}