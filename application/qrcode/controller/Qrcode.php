<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/12
 * Time: 11:20
 */

namespace app\qrcode\controller;

use app\qrcode\controller\api\QrcodeSearch;
use think\Controller;
use \app\api\controller\Send;
use \app\qrcode\controller\QrcodeAutoLoad as  FieldCheck;
use \app\qrcode\controller\api\QrcodeMain as QrcodeMain;
use \app\qrcode\controller\api\QrcodeCompanySearch as QrcodeCompanySearch;
use app\qrcode\controller\api\QrcodeWorkSearch as QrcodeWorkSearch;

/**
 * Class Qrcode
 * @package app\qrcode\controller
 */
class Qrcode extends Controller
{
    use Send;
    // +----------------------------------------------------------------------
    // | 客户单位相关
    // +----------------------------------------------------------------------
    /**
     * 添加企业操作
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postCompanyAdd()
    {
        /* 检测传递的参数是否符合企业添加规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('companyAdd');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业id，否则返回错误信息 */
        $list = QrcodeMain::toCompanyAdd($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 修改企业操作
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postCompanyEdit()
    {
        /* 检测传递的参数是否符合企业修改规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('companyEdit');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业修改方法，如果成功的话就返回修改结果，否则返回错误信息 */
        $list = QrcodeMain::toCompanyEdit($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list[0]);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 删除企业操作
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postCompanyDel()
    {
        /* 检测传递的参数是否符合企业修改规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('companyDel');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业修改方法，如果成功的话就返回修改结果，否则返回错误信息 */
        $list = QrcodeMain::toCompanyDel($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list[0]);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 查询企业方法
     * @return false|string
     */
    public function getCompany()
    {
        $data = FieldCheck::checkData('companyList');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        $list = QrcodeCompanySearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $list = QrcodeMain::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }
    // +----------------------------------------------------------------------
    // | 业务类型相关
    // +----------------------------------------------------------------------
    /**
     * 添加业务类型
     * @return false|string
     */
    public function postWorkTypeAdd()
    {
        /* 检测传递的参数是否符合业务类型添加规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('workAdd');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业id，否则返回错误信息 */
        $list = QrcodeMain::toWorkTypeAdd($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 修改业务类型
     * @return false|string
     */
    public function postWorkTypeEdit()
    {
        /* 检测传递的参数是否符合业务类型添加规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('workEdit');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业id，否则返回错误信息 */
        $list = QrcodeMain::toWorkTypeEdit($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list[0]);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 删除业务类型
     * @return false|string
     */
    public function postWorkTypeDel()
    {
        /* 检测传递的参数是否符合业务类型添加规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('workDel');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业id，否则返回错误信息 */
        $list = QrcodeMain::toWorkTypeDel($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list[0]);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 获取业务类型列表
     * @return false|string
     */
    public function getWorkType()
    {
        /* 检测传递过来的查询条件是否符合规范 */
        $data = FieldCheck::checkData('workList');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行查询操作 */
        $list = QrcodeWorkSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $list = QrcodeMain::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }
    // +----------------------------------------------------------------------
    // | 二维码相关
    // +----------------------------------------------------------------------
    /**
     * 添加二维码列表
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postQrcodeCreate()
    {
        $data = FieldCheck::checkData('qrcodeCreat');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        $list = QrcodeMain::toQrcodeCreate();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $list = QrcodeMain::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 查询获取二维码列表
     * @return false|string
     */
    public function getQrcode()
    {
        $data = FieldCheck::checkData('qrcode','page');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        $list = QrcodeSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $list = QrcodeMain::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }
}