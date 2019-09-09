<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 0:46
 */

namespace app\testing\controller;

use app\testing\controller\api\ErrorSearch as ErrorSearch;
use app\testing\controller\api\ReportSearch;
use think\Controller;
use \app\testing\controller\api\TestingSearch as TestingSearch;
use \app\testing\controller\api\TestingMain as TestingMain;
use \app\testing\controller\TestingAutoLoad as FieldCheck;
use \app\api\controller\Send;

/**
 * Class Testing
 * @package app\testing\controller
 */
class Testing extends Controller
{
    use Send;
    // +----------------------------------------------------------------------
    // | 检测进度相关
    // +----------------------------------------------------------------------
    /**
     * 获取检测进度列表
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTestingList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = TestingSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $change = new TestingMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 上传委托单异常方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postTestingError()
    {
        $data = FieldCheck::checkData('postErr');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = TestingMain::errorUpload($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 获取异常委托单列表
     * @return false|string
     */
    public function getTestingError()
    {
        /* 检查传递的参数是否符合规范 */
        $data = FieldCheck::checkData('errList',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取异常列表数据，如果抛出异常的话就返回错误信息 */
        $list = ErrorSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $change = new TestingMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 报告上传方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postReportUpload()
    {
        $data = FieldCheck::checkData('reportUpload');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = TestingMain::toReportUpload($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 获取测试报告列表
     * @return false|string
     */
    public function getReportList()
    {
        $data = FieldCheck::checkData('reportList');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = ReportSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $change = new TestingMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 根据委托单号获取指定的委托单报告
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getReport()
    {
        $data = FieldCheck::checkData('reportMain');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $main = new TestingMain();
        $list = $main::toResponse();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
//        var_dump($list);exit;
        $list = $main::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }
}