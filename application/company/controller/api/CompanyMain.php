<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/21
 * Time: 9:18
 */

namespace app\company\controller\api;

use think\Controller;
use think\Db;
use app\company\model\companyModel as companyModel;
use app\company\controller\CompanyAutoLoad as CompanyAutoLoad;

/**
 * Class CompanyMain
 * @package app\company\controller\api
 */
class CompanyMain extends Controller
{
    /**
     * 创建企业方法
     * @param $data
     * @return int|string
     */
    public static function toRegister($data)
    {
        /* 生成企业注册时间并进行插入企业数据插入操作 */
        $data['company_register_time'] = time();

        $companyModel = new companyModel();

        try{
            $companyModel->save($data);
        }catch(\Exception $e){
            return $e->getMessage();
        }
        return 1;
    }

    /**
     * 执行企业详细信息添加方法
     * @param $data
     * @return string
     * @throws \think\exception\DbException
     */
    public static function toAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new CompanyAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::companyAlreadyCreat($data);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $company['company_id'] = $uuid[0];
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_company')->insert($company);
            self::companyMainAdd($company, $uuid);
            return 'success';
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 检测传递企业信息是否有误，以及是否存在方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function companyAlreadyCreat($data)
    {
        if(!isset($data['company'])) {
            return '请传递需要添加的企业信息';
        }
        if(!isset($data['company']['company_full_name'])) {
            return '请传递需要添加的企业全称';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $company = $data['company'];
        $list = CompanyModel::get(['company_full_name' => $company['company_full_name']]);

        if(!empty($list)){
            return '当前添加的企业已存在，请检查填写的企业全称';
        }
        $uuid = md5(uniqid(mt_rand(),true));
        return array($uuid);
    }

    private static function cmpanyMainCheck($uid)
    {

    }

    private static function companyMainAdd($data, $uid)
    {
        if(isset($data['main'])) {
            $data['main']['company_id'] = $uid;
            Db::table('su_company_main')->insert($data['main']);
        }

        if(isset($data['text'])) {
            $data['text']['company_id'] = $uid;
            Db::table('su_company_text')->insert($data['text']);
        }
    }


}