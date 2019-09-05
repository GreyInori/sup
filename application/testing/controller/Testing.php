<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 0:46
 */

namespace app\testing\controller;

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
}