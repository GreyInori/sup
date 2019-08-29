<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/29
 * Time: 20:44
 */

namespace app\material\controller\api;

use think\Controller;
use think\Db;
use \app\material\controller\MaterialAutoLoad as MaterialAutoLoad;
use \app\material\model\TypeModel as TypeModel;
use \app\material\controller\api\MaterialWhere as MaterialWhere;

class MaterialMain extends Controller
{
    // +----------------------------------------------------------------------
    // | 检测类型相关
    // +----------------------------------------------------------------------
    /**
     * 获取分类列表方法
     * @param $data
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchTypeList($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $field = $group::$fieldGroup;
        $data = $group->toGroup($data);
        $list = Db::table('su_material_type')
                ->alias('smt')
                ->where('type_pid',$data['type']['type_id'])
                ->where('show_type',1)
                ->field($field['type'])
                ->select();
        if(empty($list)){
            return '查无此检测分类，请检查传递的检测分类id';
        }
        $list = self::fetchTypeChild($list);
        return $list;
    }

    /**
     * 执行分类添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTypeAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        if(!isset($type['type_pid'])) {
            $type['type_pid'] = 0;
        }
        $uuid = self::typeAlreadyCreat($data);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $type = $data['type'];
        if(isset($type['type_id'])) {
            unset($type['type_id']);
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            $id = Db::table('su_material_type')->insertGetId($type);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTypeEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        if(!isset($type['type_pid'])) {
            $type['type_pid'] = 0;
        }
        $uuid = self::typeAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $type = $data['type'];
        if(isset($type['type_id'])) {
            unset($type['type_id']);
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            $id = Db::table('su_material_type')->where('type_id',$uuid[0])->update($type);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行分类删除方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTypeDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $check = $data;
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::typeAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        /* 检测当前分类下是否还有子类，以及该分类是否存在 */
        $list = self::fetchTypeList($check);
        if(is_array($list)) {
            return '当前分类下还有子类，请先删除该分类下的子类再进行删除';
        }
        $del = Db::table('su_material_type')->where('type_id',$uuid[0])->field(['type_id'])->select();
        if(empty($del)) {
            return '查无该分类，请检查传递的检测类型id';
        }
        try{
            Db::table('su_material_type')->where('type_id',$uuid[0])->update(['show_type'=>0]);
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 图片上传规范相关
    // +----------------------------------------------------------------------
    /**
     * 获取上传图片规范表
     * @param $data
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchBlockList($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $data = $group->toGroup($data);
        $where = array('smu.show_type' => 1);
        if(isset($data['block']) && isset($data['block']['block_type'])) {
            $where['smu.block_type'] = $data['block']['block_type'];
        }
        /* 根据查询条件获取指定的 */
        $list = Db::table('su_material_upload')
                ->alias('smu')
                ->join('su_testing_file_type stft','stft.type_id = smu.upload_type')
                ->field(['smu.block_id','smu.block_type','smu.upload_type','stft.type_name'])
                ->where($where)
                ->order('smu.block_type ASC')
                ->select();
        if(empty($list)) {
            return '尚未查到上传图片规范表';
        }
        $list = self::fieldChange($list);
        return $list;
    }

    /**
     * 添加图片上传规范方法
     * @param $data
     * @return array|string
     */
    public static function toBlockAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $data = $group->toGroup($data);
        /* 进行图片上传规范的添加操作 */
        Db::startTrans();
        try{
            $id = Db::table('su_material_upload')->insertGetId($data['block']);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 图片上传规范修改方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toBlockEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $data = $group->toGroup($data);
        /* 进行图片上传规范的添加操作 */
        $block = Db::table('su_material_upload')->where('block_id',$data['block']['block_id'])->field(['block_id'])->select();
        if(empty($block)) {
            return '查无此图片上传规范，请检查传递的图片上传规范id';
        }
        $block = $data['block'];
        $uuid = $block['block_id'];
        unset($block['block_id']);

        Db::startTrans();
        try{
            $id = Db::table('su_material_upload')->where('block_id',$uuid)->update($block);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行图片上传规范删除方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toBlockDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $data = $group->toGroup($data);
        /* 进行图片上传规范的添加操作 */
        $block = Db::table('su_material_upload')->where('block_id',$data['block']['block_id'])->field(['block_id'])->select();
        if(empty($block)) {
            return '查无此图片上传规范，请检查传递的图片上传规范id';
        }
        $uuid = $data['block']['block_id'];
        Db::startTrans();
        try{
            $id = Db::table('su_material_upload')->where('block_id',$uuid)->update(['show_type'=>0]);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 检测项目相关
    // +----------------------------------------------------------------------
    public static function fetchMaterialList($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterialAutoLoad();
        $field = $group::$fieldGroup;
        /* 根据传递的类型id生成指定的查询语句 */
        $typeList = self::fetchTypeList($data);
        $where = new MaterialWhere();
        $where = $where->getWhereArray($typeList);
        var_dump($where);exit;
        /* 执行查询操作 */
        $list = Db::table('su_material')
            ->alias('sm')
            ->where($where)
            ->field($field['type'])
            ->select();
        if(empty($list)){
            return '查无此检测分类，请检查传递的检测分类id';
        }
        $list = self::fetchTypeChild($list);
        return $list;
    }
    // +----------------------------------------------------------------------
    // | 辅助相关
    // +----------------------------------------------------------------------
    /**
     * 获取查询出来的分类的子类，并分配给父类方法
     * @param $typeList
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchTypeChild($typeList)
    {
        /* 根据已经存在类型列表获取父类查询数据，查询出所有符合条件的子类 */
        $typeStr = "";
        $result = array();
        $child = array();
        foreach($typeList as $key => $row) {
            $typeStr .= "{$row['type_id']},";
            $result[$key] = self::fieldChange($row);    // 顺便把数据库字段转换为前端传递过来的字段
        }
        $typeStr = rtrim($typeStr,',');
        $list = Db::table('su_material_type')
                ->where('type_pid','IN',$typeStr)
                ->where('show_type',1)
                ->field(['type_id','type_name','type_pid'])
                ->select();
        if(empty($list)) {
            return $result;
        }
        /* 把所有子类转换成 父类id => 子类数据数组 的格式用于后面的分配 */
        foreach($list as $key => $row) {
            if(!isset($child[$row['type_pid']])){
                $child[$row['type_pid']] = array();
            }
            $row = self::fieldChange($row);
            array_push($child[$row['typeParent']],$row);
        }
        /* 为所有父类分配子类的数据 */
        foreach($typeList as $key => $row) {
            $result[$key]['child'] = array();
            if(isset($child[$row['type_id']])) {
                $result[$key]['child'] = $child[$row['type_id']];
            }
        }
        return $result;
    }

    /**
     * 判断指定检测类型是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function typeAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['type'])) {
            return '请传递需要检测的类型信息';
        }
        if(!isset($data['type']['type_name']) && $token == 0) {
            return '请传递需要添加的工程类型信息';
        }
        /* 检测检测标准是否存在 */
        $type = $data['type'];
        if($token == 1){
            $list = TypeModel::get(['type_id' => $type['type_id']]);
        }else{
            $list = TypeModel::get(['type_name' => $type['type_name'],'type_pid'=>$type['type_pid']]);
            $parent = TypeModel::get(['type_pid' => $type['type_pid']]);
        }
        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0 && isset($parent) && !empty($parent)){
            return '当前添加的类型名已经存在，请检查传递的类型名';
        }elseif(!empty($list) && $token == 1){
            return array($type['type_id']);
        }elseif($token ==  1){
            return '查无检测类型，请检查传递的检测类型id';
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
        $field = new MaterialAutoLoad();
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