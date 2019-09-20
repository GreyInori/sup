<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/11
 * Time: 9:34
 */

namespace app\area\controller\api;

use \app\api\controller\Send;
use \app\area\controller\AreaAutoLoad as AreaAutoLoad;
use \app\area\model\AreaModel as AreaModel;
use think\Controller;
use think\Db;

/**
 * Class AreaMain
 * @package app\area\controller\api
 */
class AreaMain extends Controller
{
    use Send;
    /**
     * 地区添加方法
     * @param $data
     * @return array|false|int|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toAreaAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AreaAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $pid = self::areaAlreadyCreat($data);
        if(!is_array($pid)) {
            return $pid;
        }
        $pid = Db::table('su_area')
                ->where('area_pid',$data['area']['area_pid'])
                ->field(['area_id'])
                ->order('area_id DESC')
                ->limit(0,1)
                ->select();
        if(empty($pid)) {
            return '查无此父类id，请检查传递的父类id';
        }
        $insert = array(
            'area_id' => ($pid[0]['area_id'] + 1),
            'area_pid' => $data['area']['area_pid'],
            'area_name' => $data['area']['area_name']
        );
        try{
            Db::table('su_area')->insertGetId($insert);
            return array($insert['area_id']);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    public static function toArea($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AreaAutoLoad();
        $data = $group->toGroup($data);
        try{
            $list = Db::table('su_area')
                    ->where('area_pid',$data['area']['area_pid'])
                    ->where('show_type',1)
                    ->field(['area_id','area_name'])
                    ->select();
            return $list;
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toAreaEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AreaAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::areaAlreadyCreat($data,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $area = $data['area'];
        if(isset($area['area_id'])) {
            unset($area['area_id']);
        }
        try{
            $update = Db::table('su_area')
                ->where('area_id',$uuid[0])
                ->update($area);
            return array($update);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行删除地区方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toAreaDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AreaAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::areaAlreadyCreat($data,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $area = $data['area'];
        if(isset($area['area_id'])) {
            unset($area['area_id']);
        }
        try{
            $update = Db::table('su_area')
                ->where('area_id',$uuid[0])
                ->update(['show_type'=>0]);
            return array($update);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 检测传递的地区信息是否有误，以及是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function areaAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['area'])) {
            return '请传递需要添加的地区信息';
        }
        if(!isset($data['area']['area_id']) && $token == 1) {
            return '请传递需要添加的地区id';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $area = $data['area'];
        if($token == 0){
            $list = AreaModel::get(['area_name' => $area['area_name'],'show_type'=>1]);
        }else{
            $list = AreaModel::get(['area_id' => $area['area_id'],'show_type'=>1]);
        }
        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的地区已存在，请检查填写的地区名';
        }elseif(!empty($list) && $token == 1){
            return array($area['area_id']);
        }elseif($token ==  1){
            return '查无此地区，请检查传递的地区id';
        }
        $uuid = md5(uniqid(mt_rand(),true));
        return array($uuid);
    }

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new AreaAutoLoad();
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