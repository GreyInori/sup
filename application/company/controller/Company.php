<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/21
 * Time: 9:17
 */

namespace app\company\controller;

use think\Controller;
use \app\api\controller\Send;
use \app\company\controller\CompanyAutoLoad as FieldCheck;
use \app\company\controller\api\CompanyMain as CompanyMain;
use \app\company\controller\api\CompanySearch as CompanySearch;

/**
 * Class Company
 * @package app\company\controller
 */
class Company extends Controller
{
    use Send;

    /**
     * 企业注册方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postCompanyRegister()
    {
        /* 检测传递的参数是否符合注册的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('reg');

        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $result = CompanyMain::toRegister($data);

        if($result === 1) {
            return self::returnMsg(200,'success','注册成功');
        }
        return self::returnMsg(500,'fail',$result);
    }

    /**
     * 获取指定的企业列表
     * @return false|string
     */
    public function getCompanyList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = CompanySearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 添加企业方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postCompanyAdd()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('add');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = CompanyMain::toAdd($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 修改企业信息方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postCompanyEdit()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('edit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = CompanyMain::toEdit($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 企业删除方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postCompanyDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('del');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = CompanyMain::toDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 获取企业详细信息方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function getCompanyMain()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('del');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业详细信息查看，如果成功的话就返回企业的详细信息，否则返回错误信息 */
        $list = CompanyMain::toMain($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }
}