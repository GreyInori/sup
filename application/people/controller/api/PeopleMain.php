<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/21
 * Time: 9:18
 */

namespace app\people\controller\api;

use app\lib\controller\Picture;
use think\Controller;
use think\Db;
use app\people\model\PeopleModel as PeopleModel;
use app\people\controller\PeopleAutoLoad as PeopleAutoLoad;

/**
 * Class PeopleMain
 * @package app\people\controller\api
 */
class PeopleMain extends Controller
{
    use Picture;
    /**
     * 创建人员方法
     * @param $data
     * @return array|int|string
     * @throws \think\exception\DbException
     */
    public static function toRegister($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new PeopleAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前用户名是否已经存在 */
        $already = self::peopleAlreadyCreat($check);
        if(!is_array($already)){
            return $already;
        }
        /* 生成人员注册时间并进行插入企业数据插入操作 */
        $data['people_register_time'] = time();
        $peopleModel = new PeopleModel();
        $data['people_passwd'] = md5(123456);
        if(isset($data['people_passwd'])) {
            $data['people_passwd'] = md5($data['people_passwd']);
        }
        $data['people_id'] = $already[0];
        if(isset($data['people_birthday'])) {
            $data['people_birthday'] = strtotime($data['people_birthday']);
        }
        $data['people_code'] = "P".date('Ymd').rand(10000,99999);
        /* 进行人员注册操作，成功返回人人员id */
        try{
            $peopleModel->save($data);
            return array('uid'=>$data['people_id']);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 执行人员添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new PeopleAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前用户名是否已经存在 */
        $already = self::peopleAlreadyCreat($check);
        if(!is_array($already)){
            return $already;
        }
        /* 生成人员注册时间并进行插入人员数据操作 */
        $data['people_register_time'] = time();
        $peopleModel = new PeopleModel();
        $data['people_passwd'] = md5(123456);
        if(isset($data['people_passwd'])) {
            $data['people_passwd'] = md5($data['people_passwd']);
        }
        $data['people_id'] = $already[0];
        if(isset($data['people_birthday'])) {
            $data['people_birthday'] = strtotime($data['people_birthday']);
        }
        $data['people_code'] = "P".date('Ymd').rand(10000,99999);
        /* 执行图片上传操作，如果成功就把图片路径塞进人员添加数据中 */
        $sign = self::toImgUp('sign','sign');
        $pic = self::toImgUp('people','pic');
        if(is_array($sign)) {
            $data['people_sign'] = $sign['pic'];
        }
        if(is_array($pic)) {
            $data['people_pic'] = $pic['pic'];
        }

        /* 进行人员注册操作，成功返回人人员id */
        try{
            $peopleModel->save($data);
            return array('uid'=>$data['people_id']);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 人员修改方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new PeopleAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前用户名是否已经存在 */
        $already = self::peopleAlreadyCreat($check,1);
        if(!is_array($already)){
            return $already;
        }
        /* 生成人员注册时间并进行插入人员数据操作 */
        $data['people_register_time'] = time();
        $data['people_passwd'] = md5(123456);
        if(isset($data['people_passwd'])) {
            $data['people_passwd'] = md5($data['people_passwd']);
        }
        $data['people_id'] = $already[0];
        if(isset($data['people_birthday'])) {
            $data['people_birthday'] = strtotime($data['people_birthday']);
        }
        $data['people_code'] = "P".date('Ymd').rand(10000,99999);
        /* 执行图片上传操作，如果成功就把图片路径塞进人员添加数据中 */
        $sign = self::toImgUp('sign','sign');
        $pic = self::toImgUp('people','pic');
        if(is_array($sign)) {
            $data['people_sign'] = $sign['pic'];
        }
        if(is_array($pic)) {
            $data['people_pic'] = $pic['pic'];
        }

        /* 进行人员注册操作，成功返回人人员id */
        try{
            $uid = $data['people_id'];
            unset($data['people_id']);
           Db::table('su_people')->where('people_id',$uid)->update($data);
            return array('uid'=>$uid);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 执行人员删除方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new PeopleAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::PeopleAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $company = array('show_type' => 0);
        try{
            Db::table('su_people')->where('people_id',$uuid[0])->update($company);
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 检测传递企业信息是否有误，以及是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function peopleAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['people'])) {
            return '请传递需要添加的人员信息';
        }
        if(!isset($data['people']['people_user']) && $token == 0) {
            return '请传递需要添加的用户名';
        }
        /* 检测人员是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $people = $data['people'];
        if($token == 1){
            $list = PeopleModel::get(['people_id' => $people['people_id']]);
        }else{
            $list = PeopleModel::get(['people_user' => $people['people_user']]);
        }

        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的用户名已存在，请检查填写的用户名';
        }elseif(!empty($list) && $token == 1){
            return array($people['people_id']);
        }elseif($token ==  1){
            return '查无此人员，请检查传递的人员id';
        }
        $uuid = md5(uniqid(mt_rand(),true));
        return array($uuid);
    }
}