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
        $inputCheck = new EngineerCheck();                // 传参是否在数据库内存在检测类
        $value = self::engineerValueCreat($check['engineer'],$uuid[0]);
        /* 进行人员检测，如果存在该人员就返回人员信息以及时间信息用于添加 */
        $people = $inputCheck::peopleCheck($check);
        if(!is_array($people)) {
            return $people;
        }
        $check['engineer'] = $value['engineer'];
        $check['divide'] = $value['divide'];
        $check['engineer']['engineering_id'] = $uuid[0];
        $check['engineer']['input_time'] = $people['input_time'];
        $check['engineer']['input_person'] = $people['input_person'];
        $check['engineer']['contract_code'] = self::creatCode();      // 生成工程编号
        /* 进行工程以及工程详细信息添加等操作 */
        Db::startTrans();
        try{
            Db::table('su_engineering')->insert($check['engineer']);
            Db::table('su_engineering_divide')->insertAll($check['divide']);
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
        $value = self::engineerValueCreat($check['engineer'],$uuid[0]);
        $check['engineer'] = $value['engineer'];
        $check['engineer']['engineering_id'] = $uuid[0];
        $check['divide'] = $value['divide'];
//        $inputCheck = new EngineerCheck();                // 传参是否在数据库内存在检测类
        /* 检测传递过来的人员列表是否存在，如果数据库内不存在就返回错误信息 */
//        $listCheck = $inputCheck::listCheck($check);
//        if($listCheck !== 1) {
//            return $listCheck;
//        }
        if(isset($check['engineer']['input_person'])) {
            unset($check['engineer']['input_person']);
        }
        if(isset($check['engineer']['contract_code'])) {
            unset($check['engineer']['contract_code']);
        }
        /* 进行工程以及工程详细信息添加等操作 */
        Db::startTrans();
        try{
            Db::table('su_engineering')->where('engineering_id',$uuid[0])->update($check['engineer']);
            Db::table('su_engineering_divide')->where('engineering_id',$uuid[0])->delete();
            Db::table('su_engineering_divide')->insertAll($check['divide']);
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
            Db::table('su_engineering_divide')->where('engineering_id',$uuid)->delete();
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
     * @return array|string
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
//        $mainList = self::fetchMainList($list[0]);
        $mainList = self::fetchDivide($check['engineer']['engineering_id']);
        foreach($list[0] as $key => $row) {
            if(isset($mainList[$key])) {
                $list[0][$key] = $mainList[$key];
            }
//            if(strchr($key,'_company') || strchr($key,'_people') && isset($mainList[$row])) {
//                $list[0][$key] = $mainList[$row];
//            }
            $result[array_search($key, $group::$fieldArr)] = $list[0][$key];
        }
        return $result;
    }

    /**
     * 获取工程内成员列表数据
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchDivide($uid)
    {
        $company = self::fetchCompanyMember($uid);
        $people = self::fetchPeopleMember($uid);

        return array_merge($company,$people);
    }

    /**
     * 获取工程内企业成员的详细数据
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchCompanyMember($uid)
    {
        $result = array();
        $field = new \app\company\controller\CompanyAutoLoad();        //  用于把企业信息转换成前端传递过来字段
        $field = $field::$fieldArr;
        $company = Db::table('su_engineering_divide')
                    ->alias('sed')
                    ->join('su_divide sd','sd.divide_id = sed.divide_id')
                    ->join('su_company sc','sc.company_id = sed.member_id')
                    ->join('su_company_main scm','scm.company_id = sc.company_id','left')
                    ->field(['sd.divide_field','sc.company_id','sc.company_number','sc.company_full_name','scm.company_corporation','sc.company_linkman','sc.company_linkman_mobile'])
                    ->where('sed.engineering_id',$uid)
                    ->select();
        /* 把查询出来的企业数据转换成前端传递过来的字段，并且根据成员字段信息对企业列表数据进行分类，返回后能直接根据工程内字段数据匹配到指定的数据 */
        foreach($company as $key => $row) {
            $value = array();
            if(!isset($result[$row['divide_field']])) {
                $result[$row['divide_field']] = array();
            }
            foreach($row as $rowKey => $rowMain) {
                if(array_search($rowKey,$field)) {
                    $value[array_search($rowKey,$field)] = $rowMain;
                }
            }
            array_push($result[$row['divide_field']],$value);
        }
        return $result;
    }

    /**
     * 获取工程内人员成员的详细数据
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchPeopleMember($uid)
    {
        $result = array();
        $field = new \app\people\controller\PeopleAutoLoad();             // 用于把人员数据转换成前端传递过来的字段数据
        $field = $field::$fieldArr;
        $people = Db::table('su_engineering_divide')
                   ->alias('sed')
                    ->join('su_divide sd','sd.divide_id = sed.divide_id')
                    ->join('su_people sp','sp.people_id = sed.member_id')
                    ->field(['sd.divide_field','people_id','people_code','people_name','people_mobile','people_idCard'])
                    ->where('sed.engineering_id',$uid)
                    ->select();
        /* 把查询出来的人员数据转换成前端传递过来的字段，并且根据成员字段信息对人员列表数据进行分类，返回后能直接根据工程内字段数据匹配到指定的数据 */
        foreach($people as $key => $row) {
            $value = array();
            if(!isset($result[$row['divide_field']])) {
                $result[$row['divide_field']] = array();
            }
            foreach($row as $rowKey => $rowMain) {
                if(array_search($rowKey,$field)) {
                    $value[array_search($rowKey,$field)] = $rowMain;
                }
            }
            array_push($result[$row['divide_field']],$value);
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
    public static function fetchMainList($data)
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
     * 把传入的成员数组数据转换成分别向工程表以及成员表插入的数据格式
     * @param $data
     * @param $uid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function engineerValueCreat($data, $uid)
    {
        $result = array('engineer'=>array(),'divide'=>array());
        /* 循环前端传入的数据，重组成工程数据以及成员数据的两个数组 */
        foreach($data as $key => $row) {
            $value = $row;
            /* 如果碰上以 _company 或者 _people结尾的字段,就是成员相关数据
                其中工程数据库内需要 "成员名，"的格式，成员数据需要处理成索引数组
             */
            if(strchr($key,'_company') || strchr($key,'_people') && is_array($row)) {
                $value = '';
                foreach($row as $rowKey => $rowMain) {
                    $value .= "{$rowMain['name']},";
                    array_push($result['divide'],array('engineering_id'=>$uid,'member_id'=>$rowMain['id'],'divide_id'=>$key));
                }
                $value = rtrim($value,',');
            }
            $result['engineer'][$key] = $value;
        }
        $result['divide'] = self::divideChange($result['divide']);
        return $result;
    }

    /**
     * 把工程参与成员索引数组的指定字段转换为公用字典表id
     * @param $list
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function divideChange($list)
    {
        $divide = Db::table('su_divide')->field(['divide_id','divide_field'])->select();
        $divideList = array();
        /* 循环字典表内的数据，把数据转换成数据表字段 => 字典索引id 的格式，用于后面跟详细信息列表进行比较获取索引值 */
        foreach($divide as $key => $row) {
            $divideList[$row['divide_field']] = $row['divide_id'];
        }
        foreach($list as $key => $row) {
            $list[$key]['divide_id'] = $divideList[$row['divide_id']];
        }
        return $list;
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