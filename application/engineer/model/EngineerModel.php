<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 9:07
 */

namespace app\engineer\model;

use think\Model;

/**
 * Class EngineerModel
 * @package app\engineer\model
 */
class EngineerModel extends Model
{
    protected $table = "su_engineering";

    protected $connection = [
        // 数据类型
        'type' => 'mysql',
        // 数据库连接dsn配置
        'dsn' => '',
        // 服务器地址
        'hostname' => '47.111.19.119',
        // 数据库名
        'database' => 'jianceplat',
        // 数据库用户名
        'username' => 'jianceplat',
        // 数据库密码
        'password' => 'DPrnycpCBZzGZ8A6',
        // 数据库连接端口
        'hostport' => 33900,
        // 数据库连接参数
        'params' => [],
        // 数据库默认编码采用utf-8
        'charset' => 'utf8',
        // 数据表前缀
        'prefix' => 'su_',
    ];
}