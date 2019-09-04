<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/2
 * Time: 0:21
 */

namespace app\trust\controller\api;

use think\Controller;
use think\Db;
use app\trust\model\TrustModel as TrustModel;
use app\trust\controller\TrustAutoLoad as TrustAutoload;

/**
 * Class TrustMain
 * @package app\trust\controller\api
 */
class TrustMain extends Controller
{
    /**
     * 根据委托单号获取委托单对应的图片信息方法
     * @param $data
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTrustUploadList($data)
    {
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $uuid = $uuid[0];
        $UploadList = Db::table('su_status_file')
                            ->alias('ssf')
                            ->join('su_testing_file_type stft','stft.type_id=ssf.file_type')
                            ->where(['ssf.trust_id'=>$uuid])
                            ->field(['ssf.file_id','ssf.file_file','ssf.file_depict','ssf.file_time','ssf.file_code','ssf.upload_people','stft.type_name'])
                            ->order('stft.type_id')
                            ->select();

        if(empty($UploadList)) {
            return '当前委托单所属分类尚未存在图片上传规则，请检查传递的委托单id';
        }
        return $UploadList;
    }

    /**
     * 获取监理人对应的委托单列表方法
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toPersonTrust()
    {
        $data = request()->param();
        if(!isset($data['name'])) {
            return '请传递监理人用户名';
        }
        if(!isset($data['pass'])) {
            return '请传递监理人密码';
        }
        /* 根据传递的账号和密码，判断在创建工程时有无创建对应的账号密码用户，如果有就拿工程名和id获取委托单列表 */
        $list = Db::table('su_engineering_divide')
                ->alias('sed')
                ->join('su_engineering se','se.engineering_id = sed.engineering_id')
                ->where(['divide_name'=>$data['name'],'divide_passwd'=>md5($data['pass'])])
                ->field(['se.engineering_id','se.engineering_name'])
                ->select();
        if(empty($list)) {
            return '当前监理人账号下尚未分配工程，请检查或者联系管理员';
        }
        /* 获取指定的委托单列表，并处理成前端传递过来的格式返回出去 */
        $trust = Db::table('su_trust')
                    ->where(['engineering_id'=>$list[0]['engineering_id']])
                    ->field(['trust_id','trust_code','testing_name'])
                    ->select();
        if(empty($trust)) {
            return '当前工程下尚未创建委托单，请检查或联系管理员';
        }
        $result = array();

        foreach($trust as $key => $row) {
            $row['engineering_name'] = $list[0]['engineering_name'];
            $row = self::fieldChange($row);
            array_push($result,$row);
        }
        return $result;
    }

    /**
     * 执行委托单添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTrustAdd($data)
    {
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $trust = $data['trust'];
        $trust['trust_id'] = $uuid[0];
        if(isset($trust['input_time'])) {
            $trust['input_time'] = strtotime($trust['input_time']);
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_trust')->insert($trust);
            $Upload = self::fetchTrustUpload($trust,$uuid[0]);
            if(!is_array($Upload)) {
                return $Upload;
            }
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行委托单修改方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTrustEdit($data)
    {
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $trust = $data['trust'];
        if(isset($trust['input_time'])) {
            $trust['input_time'] = strtotime($trust['input_time']);
        }
        if(isset($trust['trust_id'])) {
            unset($trust['trust_id']);
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_trust')->where('trust_id',$uuid[0])->update($trust);
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行委托单删除操作
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTrustDel($data)
    {
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_trust')->where('trust_id',$uuid[0])->update(['show_type'=>0]);
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行委托记录字段默认值添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     *
     */
    public static function toTrustMaterialAdd($data)
    {
        $group = new TrustAutoLoad();
        $list = $data;
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        /* 根据传递的值创建委托单记录详细插入数据 */
        $trustMaterial = self::fetchTrustMaterial($list,$uuid[0]);
        if(!is_array($trustMaterial)) {
            return $trustMaterial;
        }
        /* 执行默认值添加操作 */
        Db::startTrans();
        try{
            Db::table('su_trust_list_default')->insertAll($trustMaterial);
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 生成委托单记录插入数据方法
     * @param $list
     * @param $uuid
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchTrustMaterial($list, $uuid)
    {
        $result = array();
        if(!isset($list['save'])) {
            return '请传递委托单记录数据';
        }
        $save = Db::table('su_trust_list_default')
            ->where('trust_id',$uuid)
            ->field(['save_id'])
            ->order('save_id','DESC')
            ->limit(0,1)
            ->select();
        $saveId = 1;
        if(!empty($save)) {
            $saveId += $save[0]['save_id'];
        }
        foreach($list['save'] as $key => $row) {
            $result[$key] = array(
                'trial_id' => $row['trial_id'],
                'trial_default_value' => $row['trial_default_value'],
                'trial_default_token' => $row['trial_default_token'],
                'trial_verify' => $row['trial_verify'],
                'trust_id' => $uuid,
                'save_id' => $saveId
            );
        }
        return $result;
    }

    /**
     * 进行委托单号需要上传的图片添加占位操作
     * @param $data
     * @param $uuid
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchTrustUpload($data, $uuid)
    {
        /* 根据委托单号查询获取到当前委托单需要上传的个图片数量列表 */
        if(!isset($data['testing_material'])) {
            return '请传递委托单相关的检测项目';
        }
        $block = Db::table('su_material')
                    ->where(['material_id'=>$data['testing_material'],'show_type'=>1])
                    ->field(['block_type'])
                    ->select();

        if(empty($block)) {
            return '当前检测项目下尚未存在上传图片规定,请先进行添加';
        }
        $upload = Db::table('su_material_upload')
                        ->where(['block_type'=>$block[0]['block_type'],'show_type'=>1])
                        ->field(['block_id','block_type','upload_type'])
                        ->select();
        /* 根据查询结果获取到当前试块需要上传的图片类型以及数量，转换成图片插入数组 */
        $uploadArr = array();
        foreach($upload as $key => $row) {
            $uploadArr[$key] = array(
                'trust_id' => $uuid,
                'file_type' => $row['upload_type'],
            );
        }
        if(!empty($uploadArr)) {
           Db::table('su_status_file')->insertAll($uploadArr);
        }
        return array(1);
    }

    /**
     * 检测传递委托单信息是否有误，以及是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function trustAlreadyCreat($data, $token = 0)
    {
        $uuid = md5(uniqid(mt_rand(),true));
        if($token == 0){
            return array($uuid);
        }
        if(!isset($data['trust'])) {
            return '请传递需要添加的委托信息';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $trust = $data['trust'];
        if($token == 1){
            $list = TrustModel::get(['trust_id' => $trust['trust_id']]);
        }
        /* 检测委托是否存在并如果是修改之类的操作的话就需要返回查询出来的委托id进行返回 */
        if(!empty($list) && $token == 1){
            return array($trust['trust_id']);
        }elseif($token ==  1){
            return '查无此委托,请传递正确的委托单号';
        }
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
        $field = new TrustAutoLoad();
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