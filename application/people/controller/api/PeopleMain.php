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
        $data['people_passwd'] = md5($data['people_passwd']);
        $data['people_id'] = $already[0];
        try{
            $peopleModel->save($data);
        }catch(\Exception $e){
            return $e->getMessage();
        }
        return 1;
    }

    /**
     * 执行人员添加方法
     * @param $data
     * @return array|string
     */
    public static function toAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new PeopleAutoLoad();
        $data = $group->toGroup($data);
        $uuid = md5(uniqid(mt_rand(),true));
        $people = $data['people'];
        $people['people_id'] = $uuid;
        /* 进行人员以及人员详细信息的添加操作 */
        try{
            Db::table('su_people')->insert($people);
            $pic = self::picCheck($uuid);
            if(!empty($pic)){
                Db::table('su_people_file')->insert($pic);
            }
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行人员添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new PeopleAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $uuid = self::PeopleAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $people = $data['people'];
        $people['people_id'] = $uuid[0];
        /* 进行人员以及人员详细信息的添加操作 */
        try{
            Db::table('su_people')->where('people_id',$uuid[0])->update($people);
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 检测上传的人员图片
     * @param $uuid
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function picCheck($uuid)
    {
        $field = Db::table('su_people_file_type')->field(['type_id','type_en'])->select();
        $peopleImg = array();              // 用于插入的图片数组
        $peopleField = array();             // 需要处理的图片字段
        $fieldCheck = array();              // 用于把图片类型的英文名比照成id
        /* 根据数据库数据拿出需要上传的图片字段，并对应上id */
        foreach($field[0] as $key => $row) {
            array_push($peopleField, $row['type_en']);
            $fieldCheck[$row['type_en']] = $row['type_id'];
        }
        $image = self::imgUp('people',$peopleField);
        foreach($image as $imageKey => $imageRow) {
            if($imageRow !== ''){
                array_push($peopleImg,array('people_id'=>$uuid, 'people_path'=> $imageRow,'type_id'=>$fieldCheck[$imageKey]));
            }
        }
        return $peopleImg;
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
            return '查无此企业，请检查传递的企业id';
        }
        $uuid = md5(uniqid(mt_rand(),true));
        return array($uuid);
    }
}