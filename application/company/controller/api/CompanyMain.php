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

    public static function toAdd($data)
    {

    }
}