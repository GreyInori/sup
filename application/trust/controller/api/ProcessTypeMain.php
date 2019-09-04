<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/4
 * Time: 11:59
 */

namespace app\trust\controller\api;

use think\Controller;
use think\Db;

/**
 * Class ProcessTypeMain
 * @package app\trust\controller\api
 */
class ProcessTypeMain extends Controller
{
    /**
     * 获取样品处理方式
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchProcessTypeList()
    {
        $list = Db::table('su_processing_type')
            ->where(['show_type'=>1])
            ->field(['type_id as processType','type_name as processTypeName'])
            ->select();

        return $list;
    }

    /**
     * 添加样品处理方式方法
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toProcessTypeAdd()
    {
        $data = request()->param();
        if(!isset($data['processTypeName'])) {
            return '清传递需要添加的样品处理方式名';
        }
        $list = Db::table('su_processing_type')
            ->where(['show_type'=>1,'type_name'=>$data['processTypeName']])
            ->field(['type_id'])
            ->select();
        if(!empty($list)) {
            return '当前样品处理方式已经存在,请检查传递的样品处理方式名';
        }
        $add = array('type_name' => $data['processTypeName']);
        try {
            $add = Db::table('su_processing_type')
                ->insertGetId($add);
            return array($add);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 盐工处理方式修改方法
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toProcessTypeEdit()
    {
        $data = request()->param();
        if(!isset($data['processTypeName'])) {
            return '清传递需要修改的样品处理方式名';
        }
        if(!isset($data['processType'])) {
            return '请传递需要修改的样品处理方式id';
        }
        $list = Db::table('su_processing_type')
            ->where(['show_type'=>1,'type_id'=>$data['processType']])
            ->field(['type_id'])
            ->select();
        if( empty($list)) {
            return '当前样品处理方式不存在,请检查传递的样品处理方式id';
        }
        $update = array('type_name' => $data['processTypeName']);
        try{
            $list = Db::table('su_processing_type')->where(['type_id'=>$data['processType']])->update($update);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行样品处理方式删除操作
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toProcessTypeDel()
    {
        $data = request()->param();
        if(!isset($data['processType'])) {
            return '请传递需要删除的样品处理方式id';
        }
        $list = Db::table('su_processing_type')
            ->where(['show_type'=>1,'type_id'=>$data['processType']])
            ->field(['type_id'])
            ->select();
        if( empty($list)) {
            return '当前样品处理方式不存在,请检查传递的样品处理方式id';
        }
        try{
            $list = Db::table('su_processing_type')->where(['type_id'=>$data['processType']])->update(['show_type'=>0]);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }
}