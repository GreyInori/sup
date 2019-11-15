<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 15:12
 */

namespace app\meeting\controller\api;

use app\meeting\model\MeetingModel;
use app\user\model\UserModel;

/**
 * Class MeetingSearch
 * @package app\meeting\api
 */
class MeetingSearch
{
    /**
     * 获取会议列表方法
     * @param $search
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static  function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageinit();
        $where = new MeetingWhere();
        $where = $where->getWhereArray($search);
        $where['hm.show_type'] = 1;
        /* 通过用户 model 类判断当前用户是否为管理员 */
        $userCheck = new \app\user\model\UserModel;
        if(isset($search['user_mobile'])) {
            $user = $userCheck::getUserMain('hu.user_mobile', $search['user_mobile']);
        }else{
            $user = $userCheck::getUserMain('user_id', $search['user_id']);
        }
        if(empty($user)) {
            return '查无此用户，请检查传递的手机号';
        }
        /* 根据用户的身份类型获取对应的会议列表 */
        if($user[0]['admin_role'] == 0) {
            $where['hmu.user_mobile'] = $user[0]['user_mobile'];
           $meetingList = MeetingModel::fetchUserMeeting($where, $page);
        }elseif($user[0]['admin_role'] == 1) {
            $department = UserModel::findDepartment('user_mobile', $user[0]['user_mobile']);
            $where['hm.department_id'] = $department[0]['department_id'];
            unset($where['hmu.user_mobile']);
            $meetingList = MeetingModel::fetchAdminMeeting($where, $page);
        }else{
            unset($where['hmu.user_mobile']);
            $meetingList = MeetingModel::fetchAdminMeeting($where, $page);
        }
        /* 对查询结果字段进行转换 */
        $meetingList['list'] = MeetingMain::fieldChange($meetingList['list']);
        MeetingMain::meetingVerifyChange($meetingList['list']);
        return $meetingList;
    }

    /**
     * 分页初始化方法
     * @return array
     */
    public static function pageInit()
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
}