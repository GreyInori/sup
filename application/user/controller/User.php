<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 13:48
 */

namespace app\user\controller;

use app\lib\Send;
use app\meeting\controller\api\MeetingMain;
use app\user\controller\api\UserMain;
use app\meeting\controller\api\MeetingSearch;

/**
 * 用户相关入口类
 * @package app\user\controller
 */
class User
{
    use Send;

    /**
     * 用户登录操作
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function Login()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData('login');
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        $request = UserMain::toLogin($request);
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 创建单位操作
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createDepartment()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData('createDepartment');
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        $request = UserMain::toCreateDepartment($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 获取单位列表方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function departmentList()
    {
        if(request()->method() !== 'GET') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData();
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        $request = UserMain::fetchDepartment($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, $request['count'], $request['list']);
    }

    /**
     * 单位修改方法
     * @return array|false|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function departmentEdit()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData('editDepartment');
        if(!is_array($request)) {
            return self::returnMsg(500 ,'fail', $request);
        }
        $request = UserMain::editDepartment($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 单位删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function departmentDel()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData('delDepartment');
        if(!is_array($request)) {
            return self::returnMsg(500 ,'fail', $request);
        }
        $request = UserMain::delDepartment($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 获取用户会议列表方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserMeeting()
    {
        if(request()->method() !== 'GET') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData('meetingList');
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail' ,$request);
        }
        $request = MeetingSearch::toList($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, $request['count'], $request['list']);
    }

    /**
     * 获取用户会议详情方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserMeetingMain()
    {
        if(request()->method() !== 'GET') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData('meetingList');
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail' ,$request);
        }
        $request = MeetingMain::toMain($request);
        if(!is_array($request)) {
            return self::returnMsg(500 ,'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 用户二维码签到方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userSignForCode()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData('qrSign');
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail' ,$request);
        }
        $request = UserMain::qrSign($request);
        if(!is_array($request)) {
            return self::returnMsg(500 ,'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 用户坐标签到方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userSignForAddress()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = UserAutoLoad::checkData('addressSign');
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail' ,$request);
        }
        $request = UserMain::addressSign($request);
        if(!is_array($request)) {
            return self::returnMsg(500 ,'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }
}