<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 15:11
 */

namespace app\meeting\controller;

use app\lib\Send;
use app\meeting\controller\api\MeetingMain;
use app\meeting\controller\api\MeetingSearch;

class Meeting
{
    use Send;

    /**
     * 创建会议方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function meetingAdd()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = MeetingAutoLoad::checkData('add');
        if(!is_array($request)) {
            return self::returnMsg(500,'fail',$request);
        }
        $request = MeetingMain::toAdd($request);
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 会议修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function meetingEdit()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = MeetingAutoLoad::checkData('edit');
        if(!is_array($request)) {
            return self::returnMsg(500,'fail',$request);
        }
        $request = MeetingMain::toEdit($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 会议删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function meetingDel()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = MeetingAutoLoad::checkData('del');
        if(!is_array($request)) {
            return self::returnMsg(500,'fail',$request);
        }
        $request = MeetingMain::toDel($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 获取会议列表方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function meetingList()
    {
        if(request()->method() !== 'GET') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = MeetingAutoLoad::checkData('list');
        if(!is_array($request)) {
            return self::returnMsg(500,'fail',$request);
        }
        $request = MeetingSearch::toList($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, $request['count'], $request['list']);
    }

    /**
     * 获取会议详情方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function meetingMain()
    {
        if(request()->method() !== 'GET') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = MeetingAutoLoad::checkData('main');
        if(!is_array($request)) {
            return self::returnMsg(500,'fail',$request);
        }
        $request = MeetingMain::toMain($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 获取会议成员列表
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function meetingMemberList()
    {
        if(request()->method() !== 'GET') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = MeetingAutoLoad::checkData('memberList');
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        $request = MeetingMain::fetchMember($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 添加会议成员方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function meetingMemberAdd()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = MeetingAutoLoad::checkData('memberAdd');
        if(!is_array($request)) {
            return self::returnMsg(500,'fail', $request);
        }
        $request = MeetingMain::memberCreate($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }

    /**
     * 会议成员删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function meetingMemberDel()
    {
        if(request()->method() !== 'POST') {
            return self::returnMsg(500, 'fail', '请求方式出错');
        }
        $request = MeetingAutoLoad::checkData('memberDel');
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        $request = MeetingMain::memberDel($request);
        if(!is_array($request)) {
            return self::returnMsg(500, 'fail', $request);
        }
        return self::returnMsg(200, 'success', $request);
    }
}