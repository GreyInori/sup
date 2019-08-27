<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 9:14
 */

namespace app\engineer\controller\api;

use think\Controller;
use think\Db;
use app\engineer\model\EngineerModel as EngineerModel;
use app\engineer\controller\EngineerAutoLoad as EngineerAutoLoad;
use app\engineer\controller\api\EngineerCheck as EngineerCheck;
/**
 * Class EngineerMain
 * @package app\engineer\controller
 */
class EngineerMain extends Controller
{
    /**
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new EngineerAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前工程是否已经存在 */
        $uuid = self::engineerAlreadyCreat($check);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $check['engineer']['engineering_id'] = $uuid[0];
        $inputCheck = new EngineerCheck();                // 传参是否在数据库内存在检测类
        /* 检测传递过来的人员列表是否存在，如果数据库内不存在就返回错误信息 */
        $listCheck = $inputCheck::listCheck($check);
        if($listCheck !== 1) {
            return $listCheck;
        }
        /* 进行人员检测，如果存在该人员就返回人员信息以及时间信息用于添加 */
        $people = $inputCheck::peopleCheck($check);
        if(!is_array($people)) {
            return $people;
        }
        $check['engineer']['input_time'] = $people['input_time'];
        $check['engineer']['input_person'] = $people['input_person'];
        $check['engineer']['contract_code'] = self::creatCode();      // 生成工程编号
        /* 进行工程以及工程详细信息添加等操作 */
        Db::startTrans();
        try{
            Db::table('su_engineering')->insert($check['engineer']);
            self::engineerMainCheck($check, $uuid[0]);
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行工程修改方法
     * @param $data
     * @return array|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new EngineerAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前工程是否已经存在 */
        $uuid = self::engineerAlreadyCreat($check,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $check['engineer']['engineering_id'] = $uuid[0];
        $inputCheck = new EngineerCheck();                // 传参是否在数据库内存在检测类
        /* 检测传递过来的人员列表是否存在，如果数据库内不存在就返回错误信息 */
        $listCheck = $inputCheck::listCheck($check);
        if($listCheck !== 1) {
            return $listCheck;
        }
        /* 进行工程以及工程详细信息添加等操作 */
        Db::startTrans();
        try{
            Db::table('su_engineering')->where('engineering_id',$uuid[0])->update($check['engineer']);
            self::engineerMainCheck($check, $uuid[0]);
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行工程删除方法
     * @param $data
     * @return array|mixed|string
     * @throws \think\exception\DbException
     */
    public static function toDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new EngineerAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前工程是否已经存在 */
        $uuid = self::engineerAlreadyCreat($check,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        /* 如果工程已经存在，就根据返回的工程id进行删除操作 */
        $uuid = $uuid[0];
        Db::startTrans();
        try{
            Db::table('su_engineering')->where('engineering_id',$uuid)->delete();
            self::mainDel($uuid);
            Db::commit();
            return array('success');
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 获取工程详细信息方法
     * @param $data
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toMain($data)
    {
        $result = array();
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new EngineerAutoLoad();
        $check = $group->toGroup($data);
        $field = $group::$fieldGroup;

        $list = Db::table('su_engineering')
                ->where('engineering_id',$check['engineer']['engineering_id'])
                ->field($field['engineer'])
                ->select();
        if(empty($list)) {
            return '查无此工程，请检查传递的工程id';
        }
        /* 获取到工程对应的详细人员以及企业的数据，进行匹配以及键值对转换 */
        $mainList = self::fetchMainList($list[0]);
        foreach($list[0] as $key => $row) {
            if(strchr($key,'_company') || strchr($key,'_people') && isset($mainList[$row])) {
                $list[0][$key] = $mainList[$row];
            }
            $result[array_search($key, $group::$fieldArr)] = $list[0][$key];
        }

        return $result;
    }

    /**
     * 执行工程详细信息删除方法
     * @param $uuid
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private static function mainDel($uuid)
    {
        Db::table('su_engineering_main')->where('engineering_id',$uuid)->delete();
        Db::table('su_engineering_reckoner')->where('engineering_id',$uuid)->delete();
        Db::table('su_engineering_child')->where('engineering_id',$uuid)->delete();
        return 'success';
    }

    /**
     * 获取工程对应的详细人员以及企业数据列表
     * @param $data
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchMainList($data)
    {
        $whereStr = array('company' => '','people' => '');
        /* 根据传入的值来给获取指定的人员以及企业的where IN 查询条件 */
        foreach($data as $key => $row) {
            if(strchr($key, '_company')) {
                $whereStr['company'] .= "{$row},";
            }
            if(strchr($key, '_people')) {
                $whereStr['people'] .= "{$row},";
            }
        }
        /* 根据查询条件获取指定的企业以及人员数据，用于匹配 */
        $whereStr['company'] = rtrim($whereStr['company'], ',');
        $whereStr['people'] = rtrim($whereStr['people'], ',');
        $company = self::fetchCompanyList($whereStr['company']);
        $people = self::fetchPeopleList($whereStr['people']);
        $mainList = array_merge($company,$people);
        return $mainList;
    }

    /**
     * 获取指定的企业列表方法
     * @param $companyStr
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchCompanyList($companyStr)
    {
        $result = array();
        $companyCheck = new \app\company\controller\CompanyAutoLoad();
        $company = Db::table('su_company')
                        ->alias('sc')
                        ->join('su_company_main scm','scm.company_id = sc.company_Id')
                        ->where('sc.company_id','IN',$companyStr)
                        ->field(['sc.company_id','sc.company_number','sc.company_full_name','scm.company_corporation','sc.company_linkman','sc.company_linkman_mobile'])
                        ->select();
        /* 如果查询结果不为空，就把索引数组的结果转换为主键对应详细信息的键值对数组，用于后面的匹配 */
        if(!empty($company)) {
            $result = self::valueToKey($company,'company_id',$companyCheck::$fieldArr);
        }
        return $result;
    }

    /**
     * 获取指定的人员列表方法
     * @param $peopleStr
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchPeopleList($peopleStr)
    {
        $result = array();
        $peopleCheck = new \app\people\controller\PeopleAutoLoad();
        $people = Db::table('su_people')
            ->where('people_id','IN',$peopleStr)
            ->field(['people_id','people_code','people_name','people_mobile','people_idCard'])
            ->select();
        /* 如果查询结果不为空，就把索引数组的结果转换为主键对应详细信息的键值对数组，用于后面的匹配 */
        if(!empty($people)) {
            $result = self::valueToKey($people,'people_id',$peopleCheck::$fieldArr);
        }
        return $result;
    }

    /**
     * 把查询结果的索引数组转换为主键对应详细信息的数组
     * @param $list
     * @param $field
     * @param $check
     * @return array
     */
    private static function valueToKey($list, $field, $check)
    {
        $result = array();
        /* 把传入的字段作为主键字段匹配转换数据 */
        foreach($list as $key => $row) {
            $keyWord = $row[$field];              // 用来保存主键用于匹配
            $checkArr = array();                   // 用来保存键值转换后的数组
            /* 把详细查询结果的字段转换为前端传递过来的字段 */
            foreach($row as $rowKey => $rowMain) {
                $checkArr[array_search($rowKey, $check)] = $rowMain;
            }
            $result[$keyWord] = $checkArr;
        }
        return $result;
    }

    /**
     * 生成工程编号方法
     * @return string
     */
    private static function creatCode()
    {
        $str = 'G';
        $timeStr = date('Ymd');
        $rand = rand(100000,999999);
        return $str.$timeStr.$rand;
    }

    /**
     * 检测传递工程信息是否有误，以及是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function engineerAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['engineer'])) {
            return '请传递需要录入的工程信息';
        }
        if(!isset($data['engineer']['engineering_name']) && $token == 0) {
            return '请传递需要录入的工程的名称';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $company = $data['engineer'];
        if($token == 1){
            $list = EngineerModel::get(['engineering_id' => $company['engineering_id']]);
        }else{
            $list = EngineerModel::get(['engineering_name' => $company['engineering_name']]);
        }
        /* 检测工程是否存在并如果是修改之类的操作的话就需要返回查询出来的工程id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的工程已存在，请检查填写的工程名称';
        }elseif(!empty($list) && $token == 1){
            return array($company['engineering_id']);
        }elseif($token ==  1){
            return '查无此工程，请检查传递的工程id';
        }
        $uuid = md5(uniqid(mt_rand(),true));
        return array($uuid);
    }

    /**
     * 检测工程详细信息是否存在方法
     * @param $data
     * @param $uid
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private static function engineerMainCheck($data, $uid)
    {
        /* 检测是否已经存在的企业详细信息数据，如果存在了就执行修改，不存在就进行插入 */
        $list = Db::table('su_engineering_main')->where('engineering_id', $uid)->field(['engineering_id'])->select();
        if(empty($list)){
            $main = self::engineerMainAdd($data, $uid, 'main');
        }else{
            $main = self::engineerMainEdit($data, $uid, 'main');
        }
        /* 返回修改的表的数量 */
        return $main;
    }

    /**
     * 执行工程详细信息修改方法
     * @param $data
     * @param $uid
     * @param $table
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private static function engineerMainEdit($data, $uid, $table)
    {
        if(isset($data[$table])) {
            $data['main']['engineering_id'] = $uid;
            Db::table("su_engineering_{$table}")->where('engineering_id', $uid)->update($data[$table]);
            return 1;
        }
        return 0;
    }

    /**
     * 执行工程详细信息添加方法
     * @param $data
     * @param $uid
     * @param $table
     * @return int
     */
    private static function engineerMainAdd($data, $uid, $table)
    {
        if(isset($data[$table])) {
            $data['main']['engineering_id'] = $uid;
            Db::table("su_engineering_{$table}")->insert($data[$table]);
            return 1;
        }
        return 0;
    }
}