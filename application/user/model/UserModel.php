<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 13:42
 */

namespace app\user\model;

use think\Db;

/**
 * 用户相关model类
 * @package app\user\model
 */
class UserModel extends Db
{
    /**
     * 用户登录查询方法
     * @param $field
     * @param $where
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserMain($field, $where)
    {
        $list = self::table('hy_user')
                ->alias('hu')
                ->join('hy_department hd','hd.user_mobile = hu.user_mobile','left')
                ->where($field, $where)
                ->field(['user_id','hu.user_mobile','user_cut_time','admin_role','department_id','department_name'])
                ->select();
        return $list;
    }

    /**
     * 执行用户数据修改方法
     * @param $where
     * @param $update
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function userEdit($where, $update)
    {
        $list = self::table('hy_user')
            ->alias('hu')
            ->where($where)
            ->update($update);
        return $list;
    }

    /**
     * 查询部门方法
     * @param $field
     * @param $where
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function findDepartment($field, $where)
    {
        $list = self::table('hy_department')
                ->where($field, $where)
                ->select();
        return $list;
    }

    /**
     * 查询单位列表数据并进行分页方法
     * @param $where
     * @param $page
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchDepartmentList($where, $page = array(0, 200))
    {
        $list = self::table('hy_department')
                ->where($where)
                ->limit($page[0],$page[1])
                ->field(['department_id','department_name','user_mobile'])
                ->select();
        $count = self::table('hy_department')
                    ->where($where)
                    ->count();
        $result = array(
            'list' => $list,
            'count' => ceil($count/$page[1])
        );
        return $result;
    }

    /**
     * 执行单位修改操作
     * @param $where
     * @param $update
     * @return array|string
     */
    public static function doEditDepartment($where, $update)
    {
        try{
            $update = self::table('hy_department')
                            ->where($where)
                            ->update($update);
            return array('update' => $update);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 判断用户是否为部门管理员方法
     * @param $mobile
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function isDepartment($mobile)
    {
        $list = self::table('hy_department')
                ->where('user_mobile',$mobile)
                ->field(['department_id'])
                ->select();
        return $list;
    }

    /**
     * 用户创建方法
     * @param $mobile
     * @return int|string
     */
    public static function createUser($mobile)
    {
        $id = self::table('hy_user')->insertGetId($mobile);
        return $id;
    }

    /**
     * 创建部门方法
     * @param $data
     * @return array|string
     */
    public static function createDepartment($data)
    {
        try{
            $id = self::table('hy_department')->insertGetId($data);
            return array('department' => $id);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function getUserMeeting()
    {
        
    }
}