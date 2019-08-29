<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/29
 * Time: 11:28
 */

namespace app\material\controller\api;

use think\Controller;
use think\Db;
use \app\material\controller\MaterialAutoLoad as MaterAutoLoad;
use \app\material\model\PriceModel as PriceModel;

class MaterialMain extends Controller
{
    public static function toMaterialAdd()
    {

    }

    public static function toMaterialList()
    {

    }

    /**
     * 执行检测费用添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toPriceAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::priceAlreadyCreat($data);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $price = $data['price'];
        if(isset($price['price_id'])) {
            unset($price['price_id']);
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            $id = Db::table('su_testing_price')->insertGetId($price);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行检测费用修改方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toPriceEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::priceAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $price = $data['price'];
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            unset($price['price_id']);
            $id = Db::table('su_testing_price')->where('price_id',$uuid[0])->update($price);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行检测费用删除方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toPriceDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::priceAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        try{
            Db::table('su_testing_price')->where('price_id',$uuid[0])->delete();
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
    private static function priceAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['price'])) {
            return '请传递需要添加的检测费用信息';
        }
        if(!isset($data['price']['testing_number']) && $token == 0) {
            return '请传递需要添加的检测费用编号';
        }
        /* 检测检测标准是否存在 */
        $standard = $data['price'];
        if($token == 1){
            $list = PriceModel::get(['price_id' => $standard['price_id']]);
        }else{
            $list = PriceModel::get(['testing_number' => $standard['testing_number']]);
        }
        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的检测费用已经存在，请检查传递的检测费用';
        }elseif(!empty($list) && $token == 1){
            return array($standard['price_id']);
        }elseif($token ==  1){
            return '查无此检测费用，请检查传递的检测费用';
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
