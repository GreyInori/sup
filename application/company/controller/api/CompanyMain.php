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
     * @return array|int|string
     * @throws \think\exception\DbException
     */
    public static function toRegister($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new CompanyAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前用户名是否已经存在 */
        $already = self::companyAlreadyCreat($check);
        if(!is_array($already)){
            return $already;
        }
        /* 生成企业注册时间并进行插入企业数据插入操作 */
        $data['company_register_time'] = time();
        $companyModel = new companyModel();
        $data['company_passwd'] = md5($data['company_passwd']);
        $data['company_id'] = $already[0];
        try{
            $companyModel->save($data);
        }catch(\Exception $e){
            return $e->getMessage();
        }
        return 1;
    }

    /**
     * 执行企业添加方法
     * @param $data
     * @return array|string
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
        $company = $data['company'];
        $company['company_id'] = $uuid[0];
        $company['company_number'] = self::creatCode();
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_company')->insert($company);
            self::companyMainCheck($data, $uuid[0]);          // 进行企业详细信息的修改或者添加操作
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行企业修改方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new CompanyAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::companyAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $company = $data['company'];
        $company['company_id'] = $uuid[0];
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_company')->where('company_id',$uuid[0])->update($company);
            self::companyMainCheck($data, $uuid[0]);          // 进行企业详细信息的修改或者添加操作
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行企业删除方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new CompanyAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::companyAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $company = array('show_type' => 0);
        try{
            Db::table('su_company')->where('company_id',$uuid[0])->update($company);
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 获取企业详细信息方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toMain($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new CompanyAutoLoad();
        $data = $group->toGroup($data);
        $field = $group::$fieldGroup;
        $check = $group::$fieldArr;
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::companyAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        try{
            /* 通过分组字段获取到需要查询的数据字段，返回出来 */
            $list = self::fetchMain($uuid, $field, $check);
            return $list;
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 获取企业详细信息
     * @param $uuid
     * @param $field
     * @param $check
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchMain($uuid, $field, $check)
    {
        $result = array();
       $company = Db::table('su_company')->where('company_id',$uuid[0])->field($field['company'])->select();
        $main = Db::table('su_company_main')->where('company_id',$uuid[0])->field($field['main'])->select();
        $text = Db::table('su_company_text')->where('company_id',$uuid[0])->field($field['text'])->select();

        /* 循环查询数据，把数据库内字段转换成前端传递过来的字段进行处理，并塞进返回值数组内 */
        foreach ($company[0] as $key => $row) {
            $key = array_search($key, $check);
            if($key == 'regTime' || $key == 'start' || $key == 'end'){
                $row = date('Y:m:d H:i:s', $row);
            }
            $result[$key] = $row;
        }
        if(!empty($main)) {
            foreach ($main[0] as $mainKey => $mainRow) {
                $mainKey = array_search($mainKey, $check);
                $result[$mainKey] = $mainRow;
            }
        }
        if(!empty($text)) {
            foreach ($text[0] as $textKey => $textRow) {
                $textKey = array_search($textKey, $check);
                $result[$textKey] = $textRow;
            }
        }
        if($result['area'] != null) {
            $area = new \app\lib\controller\Area();
            $result['area'] = $area::getAreaList($result['area']);
        }
        return $result;
    }

    /**
     * 生成工程编号方法
     * @return string
     */
    private static function creatCode()
    {
        $str = 'Q';
        $timeStr = date('Ymd');
        $rand = rand(100000,999999);
        return $str.$timeStr.$rand;
    }

    /**
     * 检测传递企业信息是否有误，以及是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function companyAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['company'])) {
            return '请传递需要添加的企业信息';
        }
        if(!isset($data['company']['company_full_name']) && $token == 0) {
            return '请传递需要添加的企业全称';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $company = $data['company'];
        if($token == 1){
            $list = CompanyModel::get(['company_id' => $company['company_id']]);
        }else{
            $list = CompanyModel::get(['company_full_name' => $company['company_full_name']]);
        }

        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的企业已存在，请检查填写的企业全称';
        }elseif(!empty($list) && $token == 1){
            return array($company['company_id']);
        }elseif($token ==  1){
            return '查无此企业，请检查传递的企业id';
        }
        $uuid = md5(uniqid(mt_rand(),true));
        return array($uuid);
    }

    /**
     * 数据库信息检查方法，检测需要添加的信息是否存在
     * @param $data
     * @param $uid
     * @return int|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private static function companyMainCheck($data, $uid)
    {
        /* 检测是否已经存在的企业详细信息数据，如果存在了就执行修改，不存在就进行插入 */
        $list = Db::table('su_company_main')->where('company_id', $uid)->field(['company_id'])->select();
        if(empty($list)){
            $main = self::companyMainAdd($data, $uid, 'main');
        }else{
            $main = self::companyMainEdit($data, $uid, 'main');
        }
        /* 检测是否已经存在的企业简介信息数据，如果存在了就执行修改，不存在就进行插入 */
        $list = Db::table('su_company_text')->where('company_id', $uid)->field(['company_id'])->select();
        if(empty($list)){
            $text = self::companyMainAdd($data, $uid, 'text');
        }else{
            $text = self::companyMainEdit($data, $uid, 'text');
        }
        /* 返回修改的表的数量 */
        return ($text + $main);
    }

    /**
     * 执行企业详细信息修改方法
     * @param $data
     * @param $uid
     * @param $table
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private static function companyMainEdit($data, $uid, $table)
    {
        if(isset($data[$table])) {
            $data['main']['company_id'] = $uid;
            Db::table("su_company_{$table}")->where('company_id', $uid)->update($data[$table]);
            return 1;
        }
        return 0;
    }

    /**
     * 企业详细信息添加方法
     * @param $data
     * @param $uid
     * @param $table
     * @return int|string
     */
    private static function companyMainAdd($data, $uid, $table)
    {
        if(isset($data[$table])) {
            $data['main']['company_id'] = $uid;
            Db::table("su_company_{$table}")->insert($data[$table]);
            return 1;
        }
        return 0;
    }
}