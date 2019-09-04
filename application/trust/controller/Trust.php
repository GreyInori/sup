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
use \app\trust\controller\api\TestMain as TestMain;
use \app\trust\controller\api\ProcessTypeMain as ProcessTypeMain;

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
        $change = new TrustMain();
        $list = $change::fieldChange($list);
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
        $data = FieldCheck::checkData('trustAdd');
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

    /**
     * 修改委托单方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postTrustEdit()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('trustEdit');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = TrustMain::toTrustEdit($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 根据委托单号获取对应的图片数据
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTrustUpload()
    {
        $data = FieldCheck::checkData('trustUploadList');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = TrustMain::toTrustUploadList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $list = TrustMain::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 删除委托单方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postTrustDel()
    {
        /* 检测传递的参数是否符合删除委托单的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('trustDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = TrustMain::toTrustDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 委托单详细记录信息添加方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postTrustMaterialAdd()
    {
        $data = request()->param();
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::buildRequestField($data);
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

    /**
     * 根据监理人账号密码获取相对应的委托单方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPersonTrust()
    {
        $list = TrustMain::toPersonTrust();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }
    // +----------------------------------------------------------------------
    // | 检测类型相关
    // +----------------------------------------------------------------------
    /**
     * 获取检测类型列表方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTestList()
    {
        $list = TestMain::fetchTestList();
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 检测类型添加方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postTestAdd()
    {
        $list = TestMain::toTestAdd();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 检测类型修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postTestEdit()
    {
        $list = TestMain::toTestEdit();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 检测类型删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postTestDel()
    {
        $list = TestMain::toTestDel();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }
    // +----------------------------------------------------------------------
    // | 样品处理方式相关
    // +----------------------------------------------------------------------
    /**
     * 获取样品处理方式列表方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProcessTypeList()
    {
        $list = ProcessTypeMain::fetchProcessTypeList();
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 样品处理方式添加方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postProcessTypeAdd()
    {
        $list = ProcessTypeMain::toProcessTypeAdd();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 样品处理方式修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postProcessTypeEdit()
    {
        $list = ProcessTypeMain::toProcessTypeEdit();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 样品处理方式删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postProcessTypeDel()
    {
        $list = ProcessTypeMain::toProcessTypeDel();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

}