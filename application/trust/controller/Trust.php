<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/2
 * Time: 0:15
 */

namespace app\trust\controller;

use think\Controller;
use \app\api\controller\Send;
use \app\trust\controller\TrustAutoLoad as FieldCheck;
use \app\trust\controller\api\TrustMain as TrustMain;
use \app\trust\controller\api\TrustSearch as TrustSearch;


/**
 * Class Trust
 * @package app\trust\controller
 */
class Trust extends Controller
{
    use Send;

    /**
     * 获取指定的委托单
     * @return false|string
     */
    public function getTrustList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = TrustSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 添加委托单方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postTrustAdd()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('add');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = TrustMain::toTrustAdd($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    public function postTrustMaterialAdd()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('trustMaterialAdd');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = TrustMain::toTrustMaterialAdd($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }
}