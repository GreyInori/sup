<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/25
 * Time: 9:05
 */

namespace app\count\controller;

use think\Controller;
use app\api\controller\Send;
use app\count\controller\api\CountMain;

/**
 * Class Count
 * @package app\count\controller
 */
class Count extends Controller
{
    use Send;

    /**
     * 获取工程统计数据
     * @return false|string
     * @throws \think\Exception
     */
    public function getEngineerCount()
    {
        return self::returnMsg(200,'success',CountMain::toEngineerCount());
    }

    /**
     * 获取委托单统计数据
     * @return false|string
     * @throws \think\Exception
     */
    public function getTrustCount()
    {
        return self::returnMsg(200,'success',CountMain::toTrustCount());
    }

    /**
     * 获取异常统计数据
     * @return false|string
     * @throws \think\Exception
     */
    public function getErrorCount()
    {
        return self::returnMsg(200,'success',CountMain::toErrorCount());
    }

    /**
     * 获取企业统计数据
     * @return false|string
     * @throws \think\Exception
     */
    public function getCompanyCount()
    {
        return self::returnMsg(200,'success',CountMain::toCompanyCount());
    }

    public function getWeekError()
    {
        return self::returnMsg(200,'success',CountMain::toWeekError());
    }
}