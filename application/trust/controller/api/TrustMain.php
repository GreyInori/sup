<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/2
 * Time: 0:21
 */

namespace app\trust\controller\api;

use app\trust\model\TrustModel;
use think\Controller;
use think\Db;
use app\trust\model\TrustModel as TrustMode;
use app\trust\controller\TrustAutoLoad as TrustAutoload;

/**
 * Class TrustMain
 * @package app\trust\controller\api
 */
class TrustMain extends Controller
{
    public static function toTrustList()
    {

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
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_company')->insert($trust);
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
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $trust = $data['trust'];
        $trust['trust_id'] = $uuid[0];
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_company')->insert($trust);
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
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
            return $uuid;
        }
        if(!isset($data['trust'])) {
            return '请传递需要添加的委托信息';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $company = $data['trust'];
        if($token == 1){
            $list = TrustModel::get(['trust_id' => $company['trust_id']]);
        }
        /* 检测委托是否存在并如果是修改之类的操作的话就需要返回查询出来的委托id进行返回 */
        if(!empty($list) && $token == 1){
            return array($company['trust_id']);
        }elseif($token ==  1){
            return '查无此委托,请传递正确的委托单号';
        }
        return array($uuid);
    }
}