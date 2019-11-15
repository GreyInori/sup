<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 15:24
 */

namespace app\meeting\model;

use think\Db;

/**
 * 会议数据库操作相关类
 * @package app\meeting\model
 */
class MeetingModel extends Db
{
    /**
     * 根据条件查询指定的会议方法
     * @param $field
     * @param $where
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getMeeting($field, $where)
    {
        $list = Db::table('hy_meeting')
                ->where($field, $where)
                ->field(['meeting_id','meeting_code','meeting_x','meeting_y'])
                ->select();
        return $list;
    }

    /**
     * 获取用户对应会议详细信息
     * @param $where
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserMeeting($where)
    {
        $list = self::table('hy_meeting')
            ->alias('hm')
            ->join('hy_meeting_content hmc','hmc.meeting_id = hm.meeting_id','left')
            ->join('hy_meeting_user hmu','hm.meeting_id = hmu.meeting_id')
            ->where($where)
            ->field(['meeting_code','hm.meeting_id','meeting_title','meeting_start','meeting_end','meeting_x','meeting_y','meeting_verify','meeting_address','meeting_content','hmu.user_token'])
            ->select();
        return $list;
    }

    /**
     * 获取会议详情
     * @param $where
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getMeetingMain($where)
    {
        $list = self::table('hy_meeting')
            ->alias('hm')
            ->join('hy_meeting_content hmc','hmc.meeting_id = hm.meeting_id','left')
            ->where($where)
            ->field(['meeting_code','hm.meeting_id','meeting_title','meeting_start','meeting_end','meeting_x','meeting_y','meeting_verify','meeting_address','meeting_content'])
            ->select();
        return $list;
    }

    /**
     * 根据内容插入会议数据方法
     * @param $data
     * @return array|string
     */
    public static function createMeeting($data)
    {
        Db::startTrans();
        try{
            $id = Db::table('hy_meeting')->insertGetId($data['meeting']);
            /* 如果存在会议议程数据，就进行议程数据插入操作 */
            if(isset($data['content'])) {
                $data['content']['meeting_id'] = $id;
                Db::table('hy_meeting_content')->insert($data['content']);
            }
            Db::commit();
            return array('meeting'=>$id);
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 会议数据修改方法
     * @param $update
     * @return array|string
     */
    public static function doEditMeeting($update)
    {
        Db::startTrans();
        try{
            $change = self::table('hy_meeting')
                            ->where($update['where'])
                            ->update($update['update']);
            /* 如果有传递会议议程数据，就进行议程数据的修改 */
            if(isset($update['content'])) {
                self::table('hy_meeting_content')
                    ->where($update['where'])
                    ->update($update['content']);
            }
            Db::commit();
            return array('update' => $change);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 会议成员数据添加方法
     * @param $insert
     * @return array|string
     */
    public static function toMemberAdd($insert)
    {
        try{
            $request = self::table('hy_meeting_user')
                            ->insert($insert);
            return array('insert' => $request);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 修改会议成员信息方法
     * @param $where
     * @param $update
     * @return array|string
     */
    public static function memberChange($where, $update)
    {
        try{
            $request = self::table('hy_meeting_user')
                            ->where($where)
                            ->update($update);
            return array('update' => $request);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 会议成员删除方法
     * @param $where
     * @return array|string
     */
    public static function toMemberDel($where)
    {
        try{
            $request = self::table('hy_meeting_user')
                            ->where($where)
                            ->delete();
            return array('delete' => $request);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 查询获取对应的用户成员方法
     * @param $where
     * @param array $page
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchMember($where, $page = array(0,10000))
    {
        $list = self::table('hy_meeting_user')
                ->where($where)
                ->field(['meeting_id','user_mobile','user_name','user_position','user_token','department_name','user_sex','user_remark','user_status','department_id'])
                ->limit($page[0], $page[1])
                ->select();
        $count = self::table('hy_meeting_user')
                    ->where($where)
                    ->limit($page[0], $page[1])
                    ->count();
        $result = array(
            'list' => $list,
            'count' => $count
        );
        return $result;
    }

    /**
     * 获取部门相关会议方法
     * @param $where
     * @param $page
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchAdminMeeting($where, $page)
    {
        $list = self::table('hy_meeting')
                ->alias('hm')
                ->where($where)
                ->field(['meeting_code','meeting_id','meeting_title','meeting_start','meeting_end','meeting_x','meeting_y','meeting_verify','meeting_address','hm.department_id','hm.department_name'])
                ->limit($page[0],$page[1])
                ->select();
        $count = self::table('hy_meeting')
                    ->alias('hm')
                    ->where($where)
                    ->field(['meeting_id'])
                    ->count();
        $result = array(
            'list' => $list,
            'count' => ceil($count/$page[1])
        );
        return $result;
    }

    /**
     * 获取人员相关会议方法
     * @param $where
     * @param $page
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchUserMeeting($where, $page)
    {
        $list = self::table('hy_meeting_user')
                ->alias('hmu')
                ->join('hy_meeting hm','hm.meeting_id = hmu.meeting_id')
                ->where($where)
                ->field(['meeting_code','hm.meeting_id','meeting_title','meeting_start','meeting_end','meeting_x','meeting_y','meeting_verify','meeting_address','hm.department_id','hm.department_name'])
                ->limit($page[0],$page[1])
                ->select();
        $count = self::table('hy_meeting_user')
            ->alias('hmu')
            ->join('hy_meeting hm','hm.meeting_id = hmu.meeting_id')
            ->where($where)
            ->field(['hm.meeting_id'])
            ->count();
        $result = array(
            'list' => $list,
            'count' => ceil($count/$page[1])
        );
        return $result;
    }
}