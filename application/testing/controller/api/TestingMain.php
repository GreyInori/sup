<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 0:46
 */

namespace app\testing\controller\api;

use think\Controller;
use think\Db;
use \app\testing\controller\TestingAutoLoad as TestingAutoLoad;

/**
 * Class TestingMain
 * @package app\testing\controller\api
 */
class TestingMain extends Controller
{
    /**
     * 委托异常添加方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function errorUpload($data)
    {
        $group = new TestingAutoLoad();
        $data = $group->toGroup($data);
        /* 检测传递的委托单号是否存在，如果不存在就返回错误信息 */
        $list = Db::table('su_testing_status')
                ->where('trust_id',$data['error']['trust'])
                ->field(['trust_id'])
                ->select();
        if(empty($list)) {
            return '查无此委托单,请检查传递的委托单id';
        }
        if(!isset($data['error']['error'])) {
            return '请传递错误信息';
        }
        $insert = array(
            'error_main' => $data['error']['error'],
            'trust_id' => $data['error']['trust']
        );
        Db::startTrans();
        try {
            $id = Db::table('su_testing_error')->insertGetId($insert);
            Db::table('su_testing_status')->where('trust_id',$data['error']['trust'])->update(['testing_error'=>1]);
            return array('uid'=>$id);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new TestingAutoLoad();
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
        $checkArr = array();
        foreach ($check as $key => $row) {
            $field = strchr($row,'.');
            $checkArr[$key] = ltrim($field,'.');
        }
        $result = array();
        foreach($list as $key => $row) {
            $result[array_search($key, $checkArr)] = $row;
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