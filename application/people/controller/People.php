<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/21
 * Time: 9:17
 */

namespace app\people\controller;

use think\Controller;
use \app\api\controller\Send;
use \app\people\controller\PeopleAutoLoad as FieldCheck;
use \app\people\controller\api\PeopleMain as PeopleMain;
use \app\people\controller\api\PeopleSearch as PeopleSearch;

/**
 * Class People
 * @package app\people\controller
 */
class People extends Controller
{
    use Send;

    /**
     * 人员注册方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postPeopleRegister()
    {
        /* 检测传递的参数是否符合注册的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('reg');

        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $result = PeopleMain::toRegister($data);

        if($result === 1) {
            return self::returnMsg(200,'success','注册成功');
        }
        return self::returnMsg(500,'fail',$result);
    }

    /**
     * 获取指定的人员列表
     * @return false|string
     */
    public function getPeopleList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取人员列表数据，如果有抛出异常的话就返回错误信息 */
        $list = PeopleSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 添加人员方法
     * @return false|string
     */
    public function postPeopleAdd()
    {
        /* 检测传递的参数是否符合人员添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('add');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行人员添加方法，如果成功的话就返回人员的id，否则返回错误信息 */
        $list = PeopleMain::toAdd($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 修改人员信息方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postPeopleEdit()
    {
        /* 检测传递的参数是否符合人员添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('edit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行人员添加方法，如果成功的话就返回人员的id，否则返回错误信息 */
        $list = PeopleMain::toEdit($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 人员删除方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postPeopleDel()
    {
        /* 检测传递的参数是否符合人员添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('del');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行人员添加方法，如果成功的话就返回人员的id，否则返回错误信息 */
        $list = PeopleMain::toDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 获取人员详细信息方法
     * @return false|string
     */
    public function getPeopleMain()
    {
        /* 检测传递的参数是否符合人员添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('del');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行人员详细信息查看，如果成功的话就返回人员的详细信息，否则返回错误信息 */
        $list = PeopleMain::toMain($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }
}