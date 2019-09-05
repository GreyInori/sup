<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/5
 * Time: 10:18
 */

namespace app\engineer\controller\api;

use think\Controller;
use think\Db;

class DivideMain extends Controller
{
    /**
     * 获取工程成员列表方法
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchDivide()
    {
        $list = Db::table('su_divide')
                ->where('show_type',1)
                ->field(['divide_id as divide','divide_name divideName','divide_field divideField'])
                ->select();
        return $list;
    }

    /**
     * 成员列表添加方法
     * @return array|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toDivideAdd()
    {
        $data = request()->param();
        if(!isset($data['divideName'])) {
            return '请传递需要添加的成员名';
        }
        if(!isset($data['divideField'])) {
            return '请传递需要添加的成员字段名';
        }

        $list = Db::table('su_divide')
                ->where('divide_name',$data['divideName'])
                ->field(['divide_id'])
                ->select();
        if(!empty($list)) {
            return '当前添加的成员信息已经存在，请修改';
        }
        $insert = array(
            'divide_name' => $data['divideName'],
            'divide_field' => $data['divideField']
        );
        try{
            $insert = Db::table('su_divide')->insertGetId($insert);
            return array($insert);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行成员信息修改方法
     * @return array|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toDivideEdit()
    {
        $data = request()->param();
        if(!isset($data['divide'])) {
            return '请传递需要修改的成员id';
        }
        if(!isset($data['divideName'])) {
            return '请传递需要修改的成员名';
        }
        $list = Db::table('su_divide')
                    ->where('divide_id',$data['divide'])
                    ->field(['divide_id'])
                    ->select();
        if(empty($list)) {
            return '查无此成员,请检查传递的成员id';
        }
        $update = array('divide_name'=>$data['divideName']);
        try{
            $update = Db::table('su_divide')
                            ->where('divide_id',$data['divide'])
                            ->update($update);
            return array($update);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 成员删除方法
     * @return false|int|\PDOStatement|array|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toDivideDel()
    {
        $data = request()->param();
        if(!isset($data['divide'])) {
            return '请传递需要删除的成员id';
        }
        $list = Db::table('su_divide')
                ->where('divide_id',$data['divide'])
                ->field(['divide_id'])
                ->select();
        if(empty($list)) {
            return '查无此成员,请检查传递的成员id';
        }
        try{
            $list = Db::table('su_divide')
                    ->where('divide_id',$data['divide'])
                    ->update(['show_type',0]);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }
}