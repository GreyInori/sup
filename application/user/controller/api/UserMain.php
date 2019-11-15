<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 14:22
 */

namespace app\user\controller\api;

use app\meeting\model\MeetingModel;
use app\user\controller\UserAutoLoad;
use think\Controller;
use app\user\model\UserModel;
use app\lib\SquarePoint;
/**
 * 用户详细操作相关方法
 * Class UserMain
 * @package app\user\controller\api
 */
class UserMain extends Controller
{
    use SquarePoint;

    /**
     * 用户注册方法
     * @param $mobile
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function toLogin($mobile)
    {
        $result = UserModel::getUserMain('hu.user_mobile', $mobile['user_mobile']);
        if(!empty($result)) {
            if($result[0]['user_cut_time']   == '' || $result[0]['user_cut_time'] < time()) {
                $time = 30 * 24 * 60 * 60;
                $time += time();
                UserModel::userEdit(array('user_mobile' => $mobile['user_mobile']) ,array('user_cut_time' => $time));
            }
            $result[0] = self::fieldChange($result[0]);
            return $result[0];
        }
        /* 如果查询不到手机号的话就进行用户创建 */
        $time = 30 * 24 * 60 * 60;
        $time += time();
        $insert = array('user_mobile'=>$mobile['user_mobile'], 'user_cut_time'=>$time);
        /* 判断当前用户是否为部门管理员，如果是的话就更改管理员类型 */
        if(empty(UserModel::isDepartment($mobile['user_mobile']))) {
            $insert['admin_role'] = 0;
        }else{
            $insert['admin_role'] = 1;
        }
        UserModel::createUser($insert);
        /* 再获取一次用户信息进行返回 */
        $result = UserModel::getUserMain('hu.user_mobile', $mobile['user_mobile']);
        // $result[0]['is_admin'] = is_null($result[0]['department_id'])? 0: 1;
        // unset($result[0]['department_id']);
        $result[0] = self::fieldChange($result[0]);
        return $result[0];
    }

    /**
     * 进行二维码签到方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function qrSign($data)
    {
        $meeting = MeetingModel::getMeeting('meeting_code', $data['meeting_code']);
        if(empty($meeting)) {
            return '查无此会议，请检查传递的会议编码';
        }
        $where = array(
            'user_token' => md5($data['meeting_code'].'-'.$data['user_mobile'])
        );
        $member = MeetingModel::getUserMeeting($where);
        if(empty($member)) {
            return '查无此会议成员数据，请检查传递的人员你手机号';
        }
        $update = MeetingModel::memberChange($where, array('user_status'=>1));
        if(!is_array($update)) {
            return $update;
        }
        /* 判断修改结果，返回对应的信息 */
        $result = '';
        switch($update['update']) {
            case 1:
                $result =  array('status' => '签到成功');
                break;
            case 0:
                $result =  array('status' => '签到失败，可能当前会议已经签到');
        }
        return $result;
    }

    /**
     * 用户坐标签到方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function addressSign($data)
    {
        $meeting = MeetingModel::getMeeting('meeting_id', $data['meeting_id']);
        if(empty($meeting)) {
            return '查无此会议，请检查传递的会议编码';
        }elseif($meeting[0]['meeting_x'] == 0 || $meeting[0]['meeting_y'] == 0) {
            return '当前会议尚未开启坐标签到';
        }
        $where = array(
            'meeting_id' => $data['meeting_id'],
            'user_mobile' => $data['user_mobile'],
        );
        $member = MeetingModel::getUserMeeting($where);
        if(empty($member)) {
            return '查无此会议成员数据，请检查传递的人员你手机号';
        }
        /* 获取会议地点附近1公里范围的左边，并进行用户坐标判断 */
        $square = self::getPoint(array($meeting[0]['meeting_x'], $meeting[0]['meeting_y'], 1));
        if($data['meeting_x'] <= $square['right_top']['lng'] && $data['meeting_x'] >= $square['left-top']['lng'] && $data['meeting_y'] <= $square['right-top']['lat'] && $data['meeting_y'] >= $square['right-bottom']['lat']) {
            /* 如果在指定范围内就进行签到操作 */
            $update = MeetingModel::memberChange($where, array('user_status'=>2));
            if(!is_array($update)) {
                return $update;
            }
            /* 判断修改结果，返回对应的信息 */
            $result = '';
            switch($update['update']) {
                case 1:
                    $result =  array('status' => '签到成功');
                    break;
                case 0:
                    $result =  array('status' => '签到失败，可能当前会议已经签到');
            }
            return $result;
        }else{
            return '超出签到范围';
        }
    }

    /**
     * 获取单位列表方法
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchDepartment($data)
    {
        $page = self::pageInit();
        $where = array('show_type' => 1);
        if(isset($data['department_name'])) {
            $where['department_name'] = array('LIKE',"%{$data['department_name']}%");
        }
        $request = UserModel::fetchDepartmentList($where, $page);
        $request['list'] = self::fieldChange($request['list']);
        return $request;
    }

    /**
     * 单位修改方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function editDepartment($data)
    {
        $department = UserModel::findDepartment('department_id', $data['department_id']);
        if(empty($department)) {
            return '查无此单位,请检查传递的单位id';
        }
        /* 进行修改条件以及修改内容添加操作 */
        $update = array(
            'where' => array('department_id' => $data['department_id']),
            'update' => $data
        );
        unset($update['update']['department_id']);
        $request = UserModel::doEditDepartment($update['where'], $update['update']);
        return $request;
    }

    /**
     * 单位删除方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function delDepartment($data)
    {
        $department = UserModel::findDepartment('department_id', $data['department_id']);
        if(empty($department)) {
            return '查无此单位,请检查传递的单位id';
        }
        /* 进行修改条件以及修改内容添加操作 */
        $update = array(
            'where' => array('department_id' => $data['department_id']),
            'update' => ['show_type' => 0]
        );
        unset($update['update']['department_id']);
        $request = UserModel::doEditDepartment($update['where'], $update['update']);
        return $request;
    }

    /**
     * 执行单位添加操作
     * @param $data
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toCreateDepartment($data)
    {
        $result = UserModel::findDepartment('department_name', $data['department_name']);
        if(!empty($result)) {
            return '当前添加的单位名称已经存在，请检查填写的单位名称';
        }
        $user = UserModel::getUserMain('user_mobile', $data['user_mobile']);
        if(!empty($user)) {
            $result = UserModel::createDepartment($data);
            return $result;
        }
        UserModel::startTrans();
        try{
            UserModel::createUser(array('user_mobile' => $data['user_mobile'], 'admin_role' => 1));
            $result = UserModel::createDepartment($data);
            UserModel::commit();
            return $result;
        }catch(\Exception $e) {
            UserModel::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 分页初始化方法
     * @return array
     */
    private static function pageInit()
    {
        $data = request()->param();
        if(isset($data['page']) && !is_array($data['page'])) {
            $data['page'] = json_decode($data['page'],256);
        }
        $result = array(0,200);
        /* 如果传递数据不符合规范，就返回默认分页数据 */
        if(!isset($data['page']) || count($data['page']) != 2) {
            return $result;
        }
        $result = array($data['page'][0] * $data['page'][1], $data['page'][1]);
        return $result;
    }

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new UserAutoLoad();
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
                $row = date('Y-m-d H:i',$row);
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