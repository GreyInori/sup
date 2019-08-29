<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/29
 * Time: 17:48
 */

namespace app\material\controller\api\file;

use \app\material\controller\MaterialAutoLoad as MaterAutoLoad;
use \app\material\model\FileModel as FileModel;
use think\Db;
use think\Controller;

/**
 * Class FileMain
 * @package app\material\controller\file
 */
class FileMain extends Controller
{
    /**
     * 执行文件号添加操作
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toFileAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::FileAlreadyCreat($data);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $file = $data['file'];

        if(isset($file['file_id'])) {
            unset($file['file_id']);
        }

        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            $id = Db::table('su_file_number')->insertGetId($file);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 文件号修改方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toFileEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::fileAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $file = $data['file'];
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            unset($file['file_id']);
            $id = Db::table('su_file_number')->where('file_id',$uuid[0])->update($file);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行文件号删除方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toFileDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new MaterAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::fileAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        try{
            Db::table('su_file_number')->where('file_id',$uuid[0])->delete();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 检测文件号是否存在
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function fileAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['file'])) {
            return '请传递需要添加的文件号信息';
        }
        if(!isset($data['file']['material_name']) && $token == 0) {
            return '请传递需要添加的文件号信息';
        }
        /* 检测检测标准是否存在 */
        $file = $data['file'];
        if($token == 1){
            $list = FileModel::get(['file_id' => $file['file_id']]);
        }else{
            $list = FileModel::get(['material_name' => $file['material_name']]);
        }
        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的文件号信息已经存在，请检查传递的文件号信息';
        }elseif(!empty($list) && $token == 1){
            return array($file['file_id']);
        }elseif($token ==  1){
            return '查无此文件号信息，请检查传递的文件号信息';
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