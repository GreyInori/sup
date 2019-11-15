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
use app\company\controller\api\CompanyMain as CompanyMain;
use think\Exception;

/**
 * Class EngineerMain
 * @package app\engineer\controller
 */
class EngineerMain extends Controller
{
    // +----------------------------------------------------------------------
    // | 工程相关
    // +----------------------------------------------------------------------
    /**
     * 执行工程添加操作
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
        $value = self::engineerValueCreat($check['engineer'],$uuid[0]);      // 检测传递的参数是否存在，如果存在就返回传递过来的参数
        /* 进行人员检测，如果存在该人员就返回人员信息以及时间信息用于添加 */
        $people = $inputCheck::peopleCheck($check);
        if(!is_array($people)) {
            return $people;
        }
        $check['engineer'] = $value;
//        $check['divide'] = $value['divide'];
        $check['engineer']['engineering_id'] = $uuid[0];
        $check['engineer']['input_time'] = $people['input_time'];
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
     * 工程注册方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toReg($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new EngineerAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前工程是否已经存在 */
        $uuid = self::engineerAlreadyCreat($check);
        if(!is_array($uuid)) {
            return $uuid;
        }
        /* 创建工程数据信息数组 */
        $engineer = array(
            'engineering_id' => $uuid[0],
            'input_time' => time(),
            'contract_code' => self::creatCode(),
            'engineering_name' => $check['engineer']['engineering_name']
        );
        /* 创建工程成员信息 */
        $admin = self::adminCheck($check['engineer'], $data['divide_id']);
        if(!is_array($admin)){
            return $admin;
        }
        /* 把当前注册人的名字录入到工程填单人信息内 */
        $engineer['input_person'] = $admin['user_nickname'];
        unset($admin['user_nickname']);
        $admin['engineering_id'] = $engineer['engineering_id'];
        $admin['divide_id'] = $data['divide_id'];
        if($data['divide_id'] == 4) {
            $admin['divide_id'] = 2;
        }elseif($data['divide_id'] == 3) {
            $admin['divide_id'] = 1;
        }
        /* 获取到创建工程的企业id，用来给工程修改详细信息 */
        $field = Db::table('su_divide')->where('divide_id',$admin['divide_id'])->field(['divide_field'])->select();
        $companyName = Db::table('su_company')->where('company_id',$admin['member_id'])->field(['company_full_name'])->select();
        $engineer[$field[0]['divide_field']] = $companyName[0]['company_full_name'];
        Db::startTrans();
        try{
            Db::table('su_engineering')->insert($engineer);
            Db::table('su_engineering_divide')->insert($admin);
            Db::commit();
            return array('uid'=>$engineer['engineering_id']);
        }catch(\Exception $e){
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
//        var_dump($value);exit;
        $check['engineer'] = $value;
        $check['engineer']['engineering_id'] = $uuid[0];
//        $check['divide'] = $value['divide'];
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
        if(isset($check['engineer']['company_id'])) {
            unset($check['engineer']['company_id']);
        }
        /* 对工程填单人数据进行验证 */
        if(isset($check['engineer']['user_name'])) {
            $check['engineer']['makeup_people'] = self::makeupCheck($check['engineer']['user_name'], $uuid[0], 'makeup_people');
            unset($check['engineer']['user_name']);
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
            Db::table('su_engineering')->where('engineering_id',$uuid)->update(['show_type'=>0]);
//            Db::table('su_engineering_divide')->where('engineering_id',$uuid)->delete();
//            self::mainDel($uuid);
            Db::commit();
            return array('success');
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 工程审核通过方法
     * @param $data
     * @return array|mixed|string
     * @throws \think\exception\DbException
     */
    public static function toPass($data)
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
            Db::table('su_engineering')->where('engineering_id',$uuid)->update(['engineering_verify'=>1]);
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
        foreach($field['engineerMain'] as $key => $row) {
            $field['engineerMain'][$key] = 'se.'.$row;
        }
        array_push($field['engineerMain'],'sem.QA_from');
        array_push($field['engineerMain'],'sem.QA_level');
        array_push($field['engineerMain'],'sem.site_area');
        array_push($field['engineerMain'],'sem.underground_area');
        array_push($field['engineerMain'],'sem.CCAD_area');
        array_push($field['engineerMain'],'sem.engineering_address');

        $list = Db::table('su_engineering')
                ->alias('se')
                ->join('su_engineering_main sem','sem.engineering_id','left')
                ->where('se.engineering_id',$check['engineer']['engineering_id'])
                ->field($field['engineerMain'])
                ->select();
        if(empty($list)) {
            return '查无此工程，请检查传递的工程id';
        }
        /* 工程结算人内容获取 */
        $reckoner = Db::table('su_engineering_reckoner')
                        ->alias('ser')
                        ->join('su_admin sa','sa.user_id = ser.people_id')
                        ->where('ser.engineering_id',$list[0]['engineering_id'])
                        ->field(['sa.user_id','sa.user_name','sa.user_nickname'])
                        ->select();
        if(!empty($reckoner)) {
            $reckoner = self::fieldChange($reckoner);
        }
        $result['reckoner'] = $reckoner;
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
     * 根据工程id获取工程成员列表
     * @param $data
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchEngineerDivide($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new EngineerAutoLoad();
        $check = $group->toGroup($data);
        $list = Db::table('su_engineering_divide')
                    ->alias('sed')
                    ->join('su_divide sd','sd.divide_id = sed.divide_id')
                    ->join('su_company sc','sc.company_Id = sed.member_id')
                    ->where('sed.engineering_id',$check['engineer']['engineering_id'])
                    ->field(['sed.member_id','sed.divide_user','sd.divide_name','sd.divide_id','sed.divide_index','sc.company_full_name'])
                    ->select();
        return $list;
    }

    /**
     * 根据工程id获取工程下的检测单位列表
     * @param $data
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchEngineerTesting($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new EngineerAutoLoad();
        $check = $group->toGroup($data);
        /* 生成检测单位查询条件 */
        $where = array(
            'sed.engineering_id' => $check['engineer']['engineering_id'],
            'sed.divide_id' => 11,
        );
        $list = Db::table('su_engineering_divide')
            ->alias('sed')
            ->join('su_company sc','sc.company_Id = sed.member_id')
            ->where($where)
            ->field(['sc.company_id','sc.company_full_name'])
            ->select();
        return $list;
    }

    /**
     * 根据企业账号密码获取对应的企业详情
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchDivideEngineer()
    {
        $data = request()->param();
        if(!isset($data['divideUser'])) {
            return '请传递企业账号';
        }
        if(!isset($data['dividePass'])) {
            return '请传递企业密码';
        }
        $list = Db::table('su_engineering_divide')
            ->where(['divide_user'=>$data['divideUser'],'divide_passwd'=>md5($data['dividePass'])])
            ->field(['engineering_id'])
            ->select();
        if(empty($list)) {
            return '账号或密码错误，请检查';
        }
        $engineer = array('engineering_id'=>$list[0]['engineering_id']);
        $main = self::toMain($engineer);
        return $main;
    }

    /**
     * 根据账号密码获取相对应的企业id
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toDivideCompanyId()
    {
        $data = request()->param();
        if(!isset($data['divideUser'])) {
            return '请传递企业账号';
        }
        if(!isset($data['dividePass'])) {
            return '请传递企业密码';
        }
        $admin = Db::table('su_admin')
                        ->where(['user_name'=>$data['divideUser'],'user_pass'=>md5($data['dividePass'])])
                        ->field(['user_id'])
                        ->select();
        if(empty($admin)) {
            return '账号或密码错误，请检查';
        }
        $list = Db::table('su_engineering_divide')
            ->alias('sed')
            ->join('su_divide sd','sd.divide_id = sed.divide_id')
            ->where(['sed.divide_user'=>$data['divideUser'],'sed.divide_passwd'=>md5($data['dividePass'])])
            ->field(['sed.member_id as company','sed.divide_id as divide','sd.divide_name as divideName'])
            ->order('sed.divide_index DESC')
            ->select();
        if(empty($list)) {
            return '当前账号尚未被分配到工程，请联系相关人员';
        }
        $node = self::fetchNode($list[0]['divide']);
        $list[0]['node'] = $node;
        return array($list[0]);
    }

    /**
     * 给工程添加指定成员方法
     * @return int|string|array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toEngineerDivideAdd()
    {
        $data = request()->param();
        if(!isset($data['engineer'])) {
            return '请传递需要添加成员的工程id';
        }
        if(!isset($data['divide'])) {
            return '请传递需要添加的成员身份';
        }
        /* 判断成员数据是否存在 */
        $divide = Db::table('su_divide')
                    ->where('divide_id',$data['divide'])
                    ->field(['divide_field','divide_id'])
                    ->select();
        if(empty($divide)) {
            return '查无此成员身份，请检查出传递的成员id';
        }
        /* 检测指定工程是否存在，并且获取工程内指定成员角色信息用于修改 */
        $engineer = Db::table('su_engineering')
            ->where('engineering_id',$data['engineer'])
            ->field(['contract_code',$divide[0]['divide_field']])
            ->select();
        if(empty($engineer)) {
            return '查无此工程身份，请检查出传递的成员id';
        }
        /* 根据查询结果生成用户名等数据进行操作 */
        $divideUpdate = array(
            'engineering_id' => $data['engineer'],
            'divide_id' => $divide[0]['divide_id'],
            'divide_user' => self::creatCompanyCode($divide[0]['divide_field'],$engineer[0]['contract_code']),
        );
        /* 如果传递了用户id等相关信息后，就根据该用户创建相关的工程成员数据 */
        if(isset($data['user'])) {
            $user = Db::table('su_admin')
                        ->where('user_id',$data['user'])
                        ->field(['user_company','user_name','user_pass'])
                        ->select();
            if(empty($user)) {
                return '查无此用户，请检查传递的用户id';
            }
            /* 检测指定企业是否已经以指定的角色存在于指定的工程下，如果存在，就不进行操作 */
            $alreadyHas = Db::table('su_engineering_divide')
                ->where(['engineering_id'=>$data['engineer'],'divide_id'=>$data['divide'],'divide_user'=>$user[0]['user_name']])
                ->field(['engineering_id'])
                ->select();
            if(!empty($alreadyHas)) {
                return '该人员已经存在于当前工程下';
            }

            $divideUpdate['member_id'] = $user[0]['user_company'];       //
            $divideUpdate['divide_user'] = $user[0]['user_name'];       //  工程下人员的用户名就等于人员添加时输入的手机号
            $divideUpdate['divide_passwd'] = $user[0]['user_pass'];        // 工程下人员密码就等于人员添加时输入的密码
        }
        Db::startTrans();
        try{
            /* 如果传递了企业id的话，就给添加的数组增添加企业 */
            if(isset($divideUpdate['member_id'])) {
                self::updateEngineerMember($divideUpdate['member_id'],$divide[0]['divide_field'],$data['engineer']);
            }
            /* 如果分配的是监理单位的话，就对采样人员数据进行完善 */
            if($data['divide'] == 3) {
                $dividePeople = self::makeupCheck($divideUpdate['divide_user'], $data['engineer'], 'sampling_people');
                Db::table('su_engineering')->where('engineering_id',$data['engineer'])->update(['sampling_people'=>$dividePeople]);
            }
            $insert = Db::table('su_engineering_divide')->insertGetId($divideUpdate);
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
        return array('divide'=>$insert,'divideUser'=>$divideUpdate['divide_user']);
    }

    /**
     * 删除工程下指定成员方法
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toEngineerDivideDel()
    {
        $data = request()->param();
        if(!isset($data['divide'])) {
            return '请传递需要删除的工程成员id';
        }
        if(!isset($data['engineer'])) {
            return '请传递需要删除的工程id';
        }
        /* 判断成员是否存在，如果存在就进行删除操作 */
        $list = Db::table('su_engineering_divide')
                ->where(['divide_index'=>$data['divide'],'engineering_id'=>$data['engineer']])
                ->field(['engineering_id','divide_id'])
                ->select();
        if(empty($list)) {
            return '不存在该工程成员，请检查传递的工程成员id';
        }
        Db::startTrans();
        try{
            Db::table('su_engineering_divide')->where('divide_index',$data['divide'])->delete();
            /* 重新分配工程下的成员企业信息 */
            $divideList = self::engineerDivideUpdate($data['divide'],$data['engineer'],$list[0]['divide_id']);
            Db::commit();
            return array($divideList);
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 给工程指定成员分配企业方法
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public static function toAllowDivide()
    {
        $data = request()->param();
        /* 检测传递的工程成员以及企业id是否存在 */
        if(!isset($data['divide'])) {
            return '请传递工程参与成员id';
        }
        if(!isset($data['company'])) {
            return '请传递分配的企业id';
        }

        $divideList = Db::table('su_engineering_divide')
                        ->where('divide_index',$data['divide'])
                        ->field(['engineering_id'])
                        ->select();
        if(empty($divideList)) {
            return '查无此工程参与成员,请检查传递的成员id';
        }
        $companyList = Db::table('su_company')
                            ->where('company_id',$data['company'])
                            ->field(['company_id'])
                            ->select();
        if(empty($companyList)) {
            return '查无此企业,请检查传递的企业id';
        }
        /* 根据传参生成插入修改数据数组进行修改操作 */
        $update = array('member_id'=>$data['company']);
        $update = Db::table('su_engineering_divide')
                        ->where('divide_index',$data['divide'])
                        ->update($update);
        return array($update);
    }
    // +----------------------------------------------------------------------
    // | 结算人相关
    // +----------------------------------------------------------------------
    /**
     * 添加工程结算人
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function reckonerAdd()
    {
        $data = request()->param();
        /* 检测是否重复添加数据 */
        $list = Db::table('su_engineering_reckoner')->where('engineering_id',$data['engineer'])->where('people_id',$data['peopleId'])->field(['engineering_id'])->select();
        if(!empty($list)) {
            return '当前工程已经存在该结算人，请先删除或者给其他工程分配结算人';
        }
        /* 检测填写的数据是否存在 */
        $people = Db::table('su_admin')->where('user_id',$data['peopleId'])->field(['user_id'])->select();
        $engineer = Db::table('su_engineering')->where('engineering_id',$data['engineer'])->field(['engineering_id'])->select();
        if(empty($people)) {
            return '该人员不存在，请检查传递的人员id';
        }
        if(empty($engineer)) {
            return '该工程不存在，请检查传递的工程id';
        }
        /* 执行添加操作 */
        try{
            /* 如果当前工程下存在结算人的话就进行修改操作，否则就进行添加操作 */
            $isset = Db::table('su_engineering_reckoner')->where('engineering_id',$data['engineer'])->field(['engineering_id'])->select();
            if(empty($isset)) {
                $add = Db::table('su_engineering_reckoner')->insert(['engineering_id'=>$data['engineer'],'people_id'=>$data['peopleId']]);
            }else{
                $add = Db::table('su_engineering_reckoner')->where('engineering_id',$data['engineer'])->update(['people_id'=>$data['peopleId']]);
            }
            return array($add);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行工程结算人修改
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function reckonerEdit()
    {
        $data = request()->param();
        /* 检测填写的数据是否存在 */
        $people = Db::table('su_people')->where('people_id',$data['peopleId'])->field(['people_id'])->select();
        $engineer = Db::table('su_engineering_reckoner')->where('engineering_id',$data['engineer'])->field(['engineering_id'])->select();
        if(empty($people)) {
            return '该人员不存在，请检查传递的人员id';
        }
        if(empty($engineer)) {
            return '该工程不存在，请检查传递的工程id';
        }
        /* 执行添加操作 */
        try{
            $edit = Db::table('su_engineering_reckoner')->where('engineering_id',$data['engineer'])->update(['engineering_id'=>$data['engineer'],'people_id'=>$data['peopleId']]);
            return array($edit);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function  reckonerDel()
    {
        $data = request()->param();
        /* 检测填写的数据是否存在 */
        $engineer = Db::table('su_engineering_reckoner')->where('engineering_id',$data['engineer'])->field(['engineering_id'])->select();
        if(empty($engineer)) {
            return '该工程不存在，请检查传递的工程id';
        }
        /* 执行添加操作 */
        try{
            $del = Db::table('su_engineering_reckoner')->where('engineering_id',$data['engineer'])->delete();
            return array($del);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 地面基础类型相关
    // +----------------------------------------------------------------------
    /**
     * 获取地面基础类型列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchFoundations()
    {
        $list = Db::table('su_foundations_type')
                ->where(['show_type'=>1])
                ->field(['type_id as foundations','type_name foundationsName'])
                ->select();
        return $list;
    }

    /**
     * 执行地面基础类型添加方法
     * @return int|array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toFoundationsAdd()
    {
        $data = request()->param();
        if(!isset($data['foundationsName'])) {
            return '清传递需要添加的地面基础类型名';
        }
        $list = Db::table('su_foundations_type')
                ->where(['show_type'=>1,'type_name'=>$data['foundationsName']])
                ->field(['type_id'])
                ->select();
        if(!empty($list)) {
            return '当前地面基础类型已经存在,请检查传递的地面基础类型名';
        }
        $add = array('type_name' => $data['foundationsName']);
        try {
            $add = Db::table('su_foundations_type')
                ->insertGetId($add);
            return array($add);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 执行地面基础类型修改操作
     * @return false|\PDOStatement|array|string|\think\Collection|\think\db\Query
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toFoundationsEdit()
    {
        $data = request()->param();
        if(!isset($data['foundationsName'])) {
            return '清传递需要修改的地面基础类型名';
        }
        if(!isset($data['foundations'])) {
            return '请传递需要修改的地面基础类型id';
        }
        $list = Db::table('su_foundations_type')
            ->where(['show_type'=>1,'type_id'=>$data['foundations']])
            ->field(['type_id'])
            ->select();
        if( empty($list)) {
            return '当前地面基础类型不存在,请检查传递的地面基础id';
        }
        $update = array('type_name' => $data['foundationsName']);
        try{
            $list = Db::table('su_foundations_type')->where(['type_id'=>$data['foundations']])->update($update);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 地面基础类型删除操作
     * @return false|int|\PDOStatement|string|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toFoundationsDel()
    {
        $data = request()->param();
        if(!isset($data['foundations'])) {
            return '请传递需要修改的地面基础类型id';
        }
        $list = Db::table('su_foundations_type')
            ->where(['show_type'=>1,'type_id'=>$data['foundations']])
            ->field(['type_id'])
            ->select();
        if(empty($list)) {
            return '当前地面基础类型不存在,请检查传递的地面基础id';
        }
        try{
            $list = Db::table('su_foundations_type')->where(['type_id'=>$data['foundations']])->update(['show_type'=>0]);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 工程类型相关
    // +----------------------------------------------------------------------
    /**
     * 获取工程类型列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchEngineerType()
    {
        $list = Db::table('su_engineering_type')
            ->where(['show_type'=>1])
            ->field(['type_id as engineerType','type_name engineerTypeName'])
            ->select();
        return $list;
    }

    /**
     * 执行工程类型添加方法
     * @return int|array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toEngineerTypeAdd()
    {
        $data = request()->param();
        if(!isset($data['engineerTypeName'])) {
            return '清传递需要添加的工程类型名';
        }
        $list = Db::table('su_engineering_type')
            ->where(['show_type'=>1,'type_name'=>$data['engineerTypeName']])
            ->field(['type_id'])
            ->select();
        if(!empty($list)) {
            return '当前工程类型已经存在,请检查传递的工程类型名';
        }
        $add = array('type_name' => $data['engineerTypeName']);
        try {
            $add = Db::table('su_engineering_type')
                ->insertGetId($add);
            return array($add);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 执行工程类型修改操作
     * @return false|\PDOStatement|array|string|\think\Collection|\think\db\Query
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toEngineerTypeEdit()
    {
        $data = request()->param();
        if(!isset($data['engineerTypeName'])) {
            return '清传递需要修改的工程类型名';
        }
        if(!isset($data['engineerType'])) {
            return '请传递需要修改的工程类型id';
        }
        $list = Db::table('su_engineering_type')
            ->where(['show_type'=>1,'type_id'=>$data['engineerType']])
            ->field(['type_id'])
            ->select();
        if( empty($list)) {
            return '当前工程类型不存在,请检查传递的工程类型id';
        }
        $update = array('type_name' => $data['engineerTypeName']);
        try{
            $list = Db::table('su_engineering_type')->where(['type_id'=>$data['engineerType']])->update($update);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 工程类型删除操作
     * @return false|int|\PDOStatement|string|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toEngineerTypeDel()
    {
        $data = request()->param();
        if(!isset($data['engineerType'])) {
            return '请传递需要删除的工程类型id';
        }
        $list = Db::table('su_engineering_type')
            ->where(['show_type'=>1,'type_id'=>$data['engineerType']])
            ->field(['type_id'])
            ->select();
        if(empty($list)) {
            return '当前工程类型不存在,请检查传递的工程类型id';
        }
        try{
            $list = Db::table('su_engineering_type')->where(['type_id'=>$data['engineerType']])->update(['show_type'=>0]);
            return array($list);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 辅助类型相关
    // +----------------------------------------------------------------------
    /**
     * 对工程内是否存在指定用户为对应的角色进行判断
     * @param $mobile
     * @param $engineerId
     * @param $field
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function makeupCheck($mobile, $engineerId, $field)
    {
        $list = Db::table('su_engineering')->where('engineering_id',$engineerId)->field([$field])->select();
        $user = Db::table('su_admin')->where('user_name',$mobile)->field(['user_nickname'])->select();

        if(strchr($list[0][$field],$user[0]['user_nickname'])) {
            return $list[0][$field];
        }
        $result = $list[0][$field] . "," . $user[0]['user_nickname'];
        $result = ltrim($result,',');
        return $result;
    }

    /**
     * 检测传递的用户名是否存在，如果不存在就重新创建并添加企业数据
     * @param $admin
     * @param $role
     * @return array|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function adminCheck($admin, $role)
    {
        $list = Db::table('su_admin')
                ->where('user_name',$admin['user_name'])
                ->where('show_type',1)
                ->field(['user_name','user_company','user_pass','user_id','user_nickname'])
                ->select();

        if(isset($list[0]['user_company']) && ($list[0]['user_company'] == '' || $list[0]['user_company'] == null)) {
            $list[0]['user_company'] = self::companyCheck($admin);
        }
        /* 如果用户名不存在的话就进行创建，如果存在的话就返回成员信息 */
        if(!empty($list)) {
            $member = array(
                'member_id' => $list[0]['user_company'],
                'divide_user' => $list[0]['user_name'],
                'divide_passwd' => $list[0]['user_pass'],
                'user_nickname' => $list[0]['user_nickname'],
            );
            return $member;
        }
        $companyId = self::companyCheck($admin);
        $member = array('user_name'=>$admin['user_name'],'user_pass'=>md5(123456),'create_user'=>$admin['user_name'],'user_company' => $companyId);
        if($role == 1) {
            $member['user_role'] = 3;
        }else{
            $member['user_role'] = 4;
        }
        /* 如果传递了人员姓名，就给管理员添加上真实姓名 */
        if(isset($admin['user_nickname'])) {
            $member['user_nickname'] = $admin['user_nickname'];
        }
        /* 进行企业添加以及工程对应成员的创建 */
        Db::startTrans();
        try{
            Db::table('su_admin')->insertGetId($member);
            Db::commit();
            $member = array(
                'member_id' => $member['user_company'],
                'divide_user' => $member['user_name'],
                'divide_passwd' => $member['user_pass'],
                'user_nickname' => $member['user_nickname'],
            );
            return $member;
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 检测对应的企业是否存在，如果不存在就创建，返回id
     * @param $admin
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function companyCheck($admin)
    {
        if(!isset($admin['company_full_name'])) {
            return '请传递注册工程的企业名';
        }
        /* 生成企业存在检测规范的数组，进行企业是否存在检测 */
        $companyObj = new CompanyMain();
        $companyId = array('company'=>$admin);
        $companyId = $companyObj::companyAlreadyCreat($companyId);
        /* 进行企业添加操作 */
        if(is_array($companyId)) {
            self::companyCreate($admin, $companyId[0]);
            $companyId = $companyId[0];
        }else{
            $companyId = strchr($companyId,'id: ');
            $companyId = ltrim($companyId,'id: ');
        }
        return $companyId;
    }

    /**
     * 进行企业的创建
     * @param $company
     * @param $companyId
     */
    private static function companyCreate($company, $companyId)
    {
        $company = array(
            'company_full_name' => $company['company_full_name'],
            'company_id' => $companyId,
            'company_mobile' => $company['user_name'],
            'create_mobile' => $company['user_name'],
            'company_number' => CompanyMain::creatCode(),
        );

        Db::table('su_company')->insert($company);
    }

    /**
     * 重新给指定工程分配成员数据
     * @param $divideId
     * @param $engineerId
     * @param $divide
     * @return mixed
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private static function engineerDivideUpdate($divideId, $engineerId, $divide)
    {
        /* 根据成员id和成员类型id等数据获取需要修改的工程成员字段以及需要修改的值 */
        $field = Db::table('su_divide')
                    ->where('divide_id', $divide)
                    ->field(['divide_field'])
                    ->select();

        $list = Db::table('su_engineering_divide')
                ->alias('sed')
                ->join('su_company sc','sc.company_id = sed.member_id')
                ->where(['sed.engineering_Id'=>$engineerId,'sed.member_id'=>['<>',$divideId],'divide_id'=>$divide])
                ->field(['sc.company_full_name'])
                ->select();
        /* 根据工程删除指定成员后剩下的该成员类型下的成员数据，循环拼接成新的成员数据进行修改操作 */
        if(empty($list)) {
            $update = array($field[0]['divide_field'] => '');
        }else{
            $updateStr = '';
            foreach($list as $key => $row) {
                $updateStr .= "{$row['company_full_name']},";
            }
            $update = array($field[0]['divide_field'] => rtrim($updateStr,','));
        }
        /* 返回值为修改后的成员信息字符串 */
        Db::table('su_engineering')->where('engineering_id',$engineerId)->update($update);
        return $update[$field[0]['divide_field']];
    }

    /**
     * 根据角色id获取对应的权限列表
     * @param $divide
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function  fetchNode($divide)
    {
        $list = Db::table('su_control_node')
                ->alias('scn')
                ->join('su_control sc','sc.control_id = scn.control_id')
                ->where('scn.divide_id',$divide)
                ->field(['sc.control_id','control_chs','control_pid','control_url','control_icon'])
                ->order('control_pid')
                ->select();
        if(empty($list)) {
            return array();
        }
        /* 根据父类id名创建格式为 父类id => 值 的数组，用于给子类匹配 */
        $divideParent = array();
        $parent = Db::table('su_control')->where('control_pid',0)->field(['control_id','control_chs','control_pid','control_url','control_icon'])->select();
        foreach($parent as $key => $value) {
            $value = self::fieldChange($value);
            $divideParent[$value['control']] = $value;
            $divideParent[$value['control']]['child'] = array();
        }
        /* 把子类的数据塞进父类里面去 */
        foreach($list as $key => $value) {
            $value = self::fieldChange($value);
            if(!$value['controlParent'] == 0) {
                array_push($divideParent[$value['controlParent']]['child'],$value);
            }
        }
        $result = array();
        foreach ($divideParent as $row) {
            array_push($result,$row);
        }
        return $result;
    }

    /**
     * 工程内成员信息修改方法
     * @param $company
     * @param $field
     * @param $engineer
     * @return array|int|string
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
   private static function updateEngineerMember($company,$field,$engineer)
   {
       $company = Db::table('su_company')
           ->where('company_id',$company)
           ->field(['company_full_name'])
           ->select();
       /* 如果当前工程已经存在指定的工程成员的话，就用 ，号拼接已存在的以及新添加的成员企业名 */
       $engineerDivide = Db::table('su_engineering')
                            ->where('engineering_id',$engineer)
                            ->field($field)
                            ->select();
       if(empty($engineerDivide) || $engineerDivide[0][$field] === '' || $engineerDivide[0][$field] === null || strchr($engineerDivide[0][$field],$company[0]['company_full_name'])) {
           $update = array(
               $field => $company[0]['company_full_name'],
           );
       }else{
           $update = array(
               $field => "{$engineerDivide[0][$field]},{$company[0]['company_full_name']}"
           );
       }
       $update = Db::table('su_engineering')->where('engineering_id',$engineer)->update($update);
       return $update;
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
                    ->join('su_company sc','sc.company_id = sed.member_id','left')
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
//    private static function mainDel($uuid)
//    {
//        Db::table('su_engineering_main')->where('engineering_id',$uuid)->delete();
//        Db::table('su_engineering_reckoner')->where('engineering_id',$uuid)->delete();
//        Db::table('su_engineering_child')->where('engineering_id',$uuid)->delete();
//        return 'success';
//    }

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
     * 创建工程下的分工企业数组
     * @param $code
     * @param $engineer
     * @param $companyId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function createDivide($code, $engineer,$companyId = '')
    {
        $list = Db::table('su_divide')
                    ->where('divide_field','LIKE','%_company')
                    ->where('show_type',1)
                    ->field(['divide_id','divide_name','divide_field'])
                    ->select();
        $company = array();
        /* 根据查询出来的预设成员结果数组，给工程分配各分工账号 */
        foreach($list as $key => $row) {
            $company[$key] = array(
                'engineering_id' => $engineer,
                'divide_id' => $row['divide_id'],
                'divide_user' => self::creatCompanyCode($row['divide_field'],$code),
                'divide_passwd' => md5('123456'),
                'member_id' => '',
            );
            /* 如果创建的时候有传递企业id，那么这个企业角色就是施工单位，为施工单位角色指定企业 */
            if($companyId != '' && $row['divide_id'] == 2) {
                $company[$key]['member_id'] = $companyId;
            }
        }
        return $company;
    }

    /**
     * 根据字段生成各公司的码用户名
     * @param $field
     * @param $code
     * @return mixed|string
     */
    private static function creatCompanyCode($field, $code)
    {
        $token = '';
        switch($field) {
            case 'build_company':
                $token = 'JS';
                break;
            case 'construction_company':
                $token = 'SG';
                break;
            case 'supervise_company' :
                $token = 'JL';
                break;
            case 'design_company':
                $token = 'SJ';
                break;
            case 'survey_company':
                $token = 'KC';
                break;
            case 'testing_company':
                $token = 'CS';
                break;
        }
        $name = str_replace('G',$token, $code);
        $rand = rand(10,99);
        $name .= $rand;
        return $name;
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
        $timeStr = date('ymd');
        $rand = rand(1000,9999);
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
            $list = EngineerModel::get(['engineering_id' => $company['engineering_id'],'show_type'=>1]);
        }else{
            $list = EngineerModel::get(['engineering_name' => $company['engineering_name'],'show_type'=>1]);
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
        return $result['engineer'];
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

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new EngineerAutoLoad();
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
            if($row == null) {
                $row = '';
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