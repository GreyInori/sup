<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 15:14
 */

namespace app\meeting\controller\api;

use app\meeting\controller\MeetingAutoLoad;
use app\meeting\model\MeetingModel;
use app\user\model\UserModel;

class MeetingMain
{

    /**
     * 根据时间修改会议状态
     * @param $data
     * @return array|string
     */
    public static function meetingVerifyChange($data)
    {
        $changeMeeting = "";
        foreach($data as $key => $row) {
            $changeMeeting .= "{$row['meeting']},";
        }
        $changeMeeting = rtrim($changeMeeting,',');
        /* 根据会议开始和结束时间创建修改数组 */
        $startUpdate = array(
            'where' => array(
                'meeting_id' => array('IN',$changeMeeting),
                'meeting_start' => array('<',time())
            ),
            'update' => array('meeting_verify' => 1)
        );
        $endUpdate = array(
            'where' => array(
                'meeting_id' => array('IN',$changeMeeting),
                'meeting_end' => array('<',time())
            ),
            'update' => array('meeting_verify' => 2)
        );
        MeetingModel::doEditMeeting($startUpdate);
        MeetingModel::doEditMeeting($endUpdate);
        return $changeMeeting;
    }

    /**
     * 执行创建会议方法
     * @param $data
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public  static function toAdd($data)
    {
        /* 进行标题是否重复的检测 */
        $meeting = MeetingModel::getMeeting('meeting_title', $data['meeting_title']);
        $department = UserModel::findDepartment('user_mobile', $data['user_mobile']);
        if(!empty($meeting)) {
            return '当前会议标题已经存在，请检查填写的会议标题';
        }
        if(empty($department)) {
            return '当前手机号尚未绑定单位，请检查';
        }
        $data['department_id'] = $department[0]['department_id'];
        $data['department_Name'] = $department[0]['department_name'];
        $insert = array(
            'meeting' => $data
        );
        $insert['meeting']['meeting_code'] = md5(uniqid().rand(100,999));
        $insert['content']['meeting_content'] = '';
        /* 如果传递了议程内容的话，就创建议程插入数据数组 */
        if(isset($data['meeting_content'])) {
            $insert['content'] = array('meeting_content'=>$data['meeting_content']);
            unset($insert['meeting']['meeting_content']);
        }
        $meeting = MeetingModel::createMeeting($insert);
        return $meeting;
    }

    /**
     * 会议修改方法
     * @param $data
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toEdit($data)
    {
        $meeting = MeetingModel::getMeeting('meeting_id', $data['meeting_id']);
        if(isset($data['user_mobile'])) {
            $department = UserModel::findDepartment('user_mobile', $data['user_mobile']);
            $data['department_id'] = $department[0]['department_id'];
        }
        if(empty($meeting)) {
            return '查无此会议，请检查传递的会议id';
        }
        $update = array(
            'update' => $data,
            'where' => array('meeting_id' => $data['meeting_id'])
        );
        /* 判断是否有传递会议议程数据，如果有就塞到修改数组里 */
        if(isset($data['meeting_content'])) {
            $update['content'] = array('meeting_content' => $data['meeting_content']);
            unset($update['update']['meeting_content']);
        }
        unset($update['update']['meeting_id']);
        $request = MeetingModel::doEditMeeting($update);
        return $request;
    }

    /**
     * 会议删除方法
     * @param $data
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toDel($data)
    {
        $meeting = MeetingModel::getMeeting('meeting_id', $data['meeting_id']);
        if(empty($meeting)) {
            return '查无此会议，请检查传递的会议id';
        }
        $update = array(
            'update' => array('show_type' => 0),
            'where' => array('meeting_id' => $data['meeting_id'])
        );
        $request = MeetingModel::doEditMeeting($update);
        return $request;
    }

    /**
     * 获取用户对应会议详情
     * @param $data
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toMain($data)
    {
        /* 如果是后台进行查询就不需要传递用户id */
        if(!isset($data['user_id'])) {
            $data['hm.meeting_id'] = $data['meeting_id'];
            unset($data['meeting_id']);
            $request = MeetingModel::getMeetingMain($data);
            $request = self::fieldChange($request);
            return $request[0];
        }
        $mobile = UserModel::getUserMain('hu.user_id', $data['user_id']);
        $data['hmu.user_mobile'] = $mobile[0]['user_mobile'];
        $data['hmu.meeting_id'] = $data['meeting_id'];
        unset($data['meeting_id']);
        unset($data['user_id']);
        $request = MeetingModel::getUserMeeting($data);
        $request = self::fieldChange($request);
        return $request[0];
    }

    /**
     * 获取会议成员列表
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchMember($data)
    {
        $result = array(array() ,array() ,array());
        $member = MeetingModel::fetchMember($data);
        if(empty($member)) {
            return '当前会议下尚未添加参会人员';
        }
        /* 根据人员的签到状态对返回值进行分类 */
        foreach($member['list'] as $key => $row) {
            $row = self::fieldChange($row);
            array_push($result[$row['status']], $row);
        }
        return $result;
    }

    /**
     * 添加会议成员方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function memberCreate($data)
    {
        /* 检查传递的会议、用户以及部门信息是否存在 */
        $meeting = MeetingModel::getMeeting('meeting_id', $data['meeting_id']);
        if(empty($meeting)) {
            return '查无此会议，请检查传递的会议id';
        }
        $department = UserModel::findDepartment('department_id', $data['department_id']);
        if(empty($department)) {
            return '查无此部门，请检查传递的部门id';
        }
        $member = MeetingModel::fetchMember(array('meeting_id'=>$data['meeting_id'], 'user_mobile'=>$data['user_mobile']),array(0, 1));
        if(!empty($member['list'])) {
            return '当前会议成员已经存在，请更改输入的手机号或会议id';
        }
        /* 生成需要添加的成员数据，成员的签到token = md5(会议token-用户手机号) */
        $insert = $data;
        $insert['user_token'] = md5($meeting[0]['meeting_code'].'-'.$data['user_mobile']);
        $request = MeetingModel::toMemberAdd($insert);
        return $request;
    }

    /**
     * 会议成员删除方法
     * @param $data
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function memberDel($data)
    {
        $member = MeetingModel::fetchMember(array('meeting_id'=>$data['meeting_id'], 'user_mobile'=>$data['user_mobile']),array(0, 1));
        if(empty($member['list'])) {
            return '当前会议成员不存在，请更改输入的手机号或会议id';
        }
        $request = MeetingModel::toMemberDel($data);
        return $request;
    }

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new MeetingAutoLoad();
        $field = $field::$fieldArr;        // 用于比较转换的数组字段
        /* 如果是索引数组的话就需要对数组内所有数据的字段进行转换，否则就直接对数组内值进行转换 */
        if(!self::is_assoc($list)) {
            foreach($list as $key => $row) {
                $result[$key] = self::toFieldChange($row, $field);
            }
        }else {
            $result = self::toFieldChange($list, $field);
        }
        return $result;
    }

    /**
     * 把数据库字段转换为前端传递的字段返回
     * @param $list
     * @param $check
     * @return array
     */
    private static function toFieldChange($list, $check)
    {
        $result = array();
        foreach($list as $key => $row) {
            if($row === null) {
                $row = '';
            }
            if($key == 'meeting_start' || $key == 'meeting_end') {
                $row = date('Y-m-d H:s', $row);
            }
            $result[array_search($key, $check)] = $row;
        }
        return $result;
    }

    /**
     * 检测数组是否为索引数组
     * @param $arr
     * @return bool
     */
    private static function is_assoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}