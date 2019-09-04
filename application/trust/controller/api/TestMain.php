<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/4
 * Time: 10:44
 */

namespace app\trust\controller\api;

use think\Controller;
use think\Db;

/**
 * Class TestMain
 * @package app\trust\controller\api
 */
class TestMain extends Controller
{
    /**
     * 获取检测类型
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchTestList()
    {
        $list = Db::table('su_testing_type')
                ->where(['show_type'=>1])
                ->field(['type_id as testType','type_name as testTypeName'])
                ->select();

        return $list;
    }

    /**
     * 添加检测类型方法
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTestAdd()
    {
        $data = request()->param();
        if(!isset($data['testTypeName'])) {
            return '清传递需要添加的检测类型名';
        }
        $list = Db::table('su_testing_type')
            ->where(['show_type'=>1,'type_name'=>$data['testTypeName']])
            ->field(['type_id'])
            ->select();
        if(!empty($list)) {
            return '当前检测类型已经存在,请检查传递的检测类型名';
        }
        $add = array('type_name' => $data['testTypeName']);
        try {
            $add = Db::table('su_testing_type')
                ->insertGetId($add);
            return array($add);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 检测类型修改方法
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTestEdit()
    {
        $data = request()->param();
        if(!isset($data['testTypeName'])) {
            return '清传递需要修改的检测类型名';
        }
        if(!isset($data['testType'])) {
            return '请传递需要修改的检测类型id';
        }
        $list = Db::table('su_testing_type')
            ->where(['show_type'=>1,'type_id'=>$data['testType']])
            ->field(['type_id'])
            ->select();
        if( empty($list)) {
            return '当前检测类型不存在,请检查传递的检测类型';
        }
        $update = array('type_name' => $data['testTypeName']);
        try{
            $list = Db::table('su_testing_type')->where(['type_id'=>$data['testType']])->update($update);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行检测类型删除操作
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTestDel()
    {
        $data = request()->param();
        if(!isset($data['testType'])) {
            return '请传递需要修改的检测类型id';
        }
        $list = Db::table('su_testing_type')
            ->where(['show_type'=>1,'type_id'=>$data['testType']])
            ->field(['type_id'])
            ->select();
        if( empty($list)) {
            return '当前检测类型不存在,请检查传递的检测类型';
        }
        try{
            $list = Db::table('su_testing_type')->where(['type_id'=>$data['testType']])->update(['show_type'=>0]);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }
}