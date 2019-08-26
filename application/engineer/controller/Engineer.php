<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 10:19
 */

namespace app\engineer\controller;

use think\Controller;
use \app\api\controller\Send;
use \app\engineer\controller\EngineerAutoLoad as FieldCheck;
use \app\engineer\controller\api\EngineerMain as EngineerMain;
use \app\engineer\controller\api\EngineerSearch as EngineerSearch;

/**
 * Class Engineer
 * @package app\engineer\controller
 */
class Engineer extends Controller
{
    use Send;

    /**
     * 工程录入方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postEngineerAdd()
    {
        /* 检测传递的参数是否符合录入的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('add');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $result = EngineerMain::toAdd($data);
        if(!is_array($result)) {
            return self::returnMsg(500,'fail',$result);
        }
        return self::returnMsg(200,'success',$result['uid']);
    }

    /**
     * 工程修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postEngineerEdit()
    {
        /* 检测传递的参数是否符合录入的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('edit');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        $result = EngineerMain::toEdit($data);
        if(!is_array($result)) {
            return self::returnMsg(500,'fail',$result);
        }
        return self::returnMsg(200,'success',$result['uid']);
    }

    /**
     * 删除工程方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postEngineerDel()
    {
        /* 检测传递的参数是否符合录入的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('del');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        $result = EngineerMain::toDel($data);
        if(!is_array($result)) {
            return self::returnMsg(500,'fail',$result);
        }
        return self::returnMsg(200,'success',$result[0]);
    }

    /**
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function getEngineerList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = EngineerSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }
}