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