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

/**
 * Class AgreementMain
 * @package app\agreement\controller\api
 */
class AgreementMain extends Controller
{
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
            $list = AgreementModel::get(['agreement_id' => $agreement['agreement_id']]);
        }else{
            $list = AgreementModel::get(['engineering_id' => $agreement['engineering_id']]);
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
}