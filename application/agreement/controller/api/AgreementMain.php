<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/10
 * Time: 14:33
 */

namespace app\agreement\controller\api;

use think\Controller;
use think\Db;
use \app\agreement\controller\AgreementAutoLoad as AgreementAutoLoad;
use \app\agreement\model\AgreementModel as AgreementModel;
use \app\lib\controller\Picture;


/**
 * Class AgreementMain
 * @package app\agreement\controller\api
 */
class AgreementMain extends Controller
{
    use Picture;

    /**
     * 获取合同类型列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toAgreementType()
    {
        $list = Db::table('su_agreement_type')->field(['type_id agreementType','type_name as typeName'])->select();
        return $list;
    }

    /**
     * 执行合同添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AgreementAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::agreementAlreadyCreat($data);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $data['agreement']['agreement_time'] = time();
        $agreement = $data['agreement'];
        $agreement['agreement_id'] = $uuid[0];
        /* 执行图片上传操作，如果上传失败就返回错误信息，如果成功就根据传值以及当前时间创建图片文件修改数据 */
        $pic = self::toImgUp('agreementFile','agreement');
        if(!is_array($pic)) {
            return $pic;
        }
        $agreement['agreement_file'] = $pic['pic'];
        try{
            Db::table('su_internal_agreement')->insert($agreement);
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行修改合同方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AgreementAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::agreementAlreadyCreat($data,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $agreement = $data['agreement'];
        if(isset($agreement['agreement_id'])) {
            unset($agreement['agreement_id']);
        }
        try{
            $update = Db::table('su_internal_agreement')
                        ->where('agreement_id',$uuid[0])
                        ->update($agreement);
            return array('uid'=>$update);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行合同删除操作
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AgreementAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::agreementAlreadyCreat($data,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        /* 执行合同修改 */
        try{
            $update = Db::table('su_internal_agreement')
                ->where('agreement_id',$uuid[0])
                ->update(['show_type'=>0]);
            return array('uid'=>$update);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 检测传递的合同信息是否有误，以及是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function agreementAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['agreement'])) {
            return '请传递需要添加的合同信息';
        }
        if(!isset($data['agreement']['engineering_id']) && $token == 0) {
            return '请传递需要添加的工程id';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $agreement = $data['agreement'];
        if($token == 1){
            $list = AgreementModel::get(['agreement_id' => $agreement['agreement_id'],'show_type'=>1]);
        }else{
            $list = AgreementModel::get(['engineering_id' => $agreement['engineering_id'],'show_type'=>1]);
        }
        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的合同已存在，请检查填写的合同id';
        }elseif(!empty($list) && $token == 1){
            return array($agreement['agreement_id']);
        }elseif($token ==  1){
            return '查无此合同，请检查传递的合同id';
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
        $field = new AgreementAutoLoad();
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
            if(strstr($key,'_time') && is_int($row)) {
                $row = date('Y-m-d H:i:s', $row);
            }elseif($key == 'agreement_file' && $row != '') {
                $url = request()->domain();
                $row = $url.$row;
            }
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