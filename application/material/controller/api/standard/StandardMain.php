<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 17:50
 */

namespace app\material\controller\api\standard;

use app\material\controller\MaterialAutoLoad;
use think\Controller;
use think\Db;
use \app\material\controller\MaterialAutoLoad as MaterAutoLoad;
use \app\material\model\StandardModel as StandardModel;

/**
 * Class StandardMain
 * @package app\material\controller\api\standard
 */
class StandardMain extends Controller
{
    /**
     * 添加检测标准方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toStandardAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::standardAlreadyCreat($data);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $standard = $data['standard'];
        if(isset($standard['testing_id'])) {
            unset($standard['testing_id']);
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            $id = Db::table('su_testing_standard')->insertGetId($standard);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 检测标准修改方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toStandardEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::standardAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $standard = $data['standard'];
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            unset($standard['testing_id']);
            $id = Db::table('su_testing_standard')->where('testing_id',$uuid[0])->update($standard);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行检测标准删除方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toStandardDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::standardAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        try{
            Db::table('su_testing_standard')->where('testing_id',$uuid[0])->delete();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 检查检测标准是否以及存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function standardAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['standard'])) {
            return '请传递需要添加的检测标准信息';
        }
        if(!isset($data['standard']['testing_number']) && $token == 0) {
            return '请传递需要添加的检测标准编号';
        }
        /* 检测检测标准是否存在 */
        $standard = $data['standard'];
        if($token == 1){
            $list = StandardModel::get(['testing_id' => $standard['testing_id']]);
        }else{
            $list = StandardModel::get(['testing_number' => $standard['testing_number']]);
        }

        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的检测标准已经存在，请检查传递的检测标准';
        }elseif(!empty($list) && $token == 1){
            return array($standard['testing_id']);
        }elseif($token ==  1){
            return '查无此检测标准，请检查传递的检测标准';
        }
        return array(1);
    }

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new MaterAutoLoad();
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