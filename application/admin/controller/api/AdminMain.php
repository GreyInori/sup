<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/11
 * Time: 9:24
 */

namespace app\admin\controller\api;

use think\Controller;
use think\Db;
use \app\admin\controller\AdminAutoLoad as AdminAutoLoad;
use \app\admin\model\AdminModel as AdminModel;

/**
 * Class adminMain
 * @package app\admin\controller\api
 */
class AdminMain extends Controller
{
    /**
     * 执行用户添加操作
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toAdminAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AdminAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $pid = self::adminAlreadyCreat($data);
        if(!is_array($pid)) {
            return $pid;
        }
        /* 默认的用户密码为123456 */
        if(!isset($data['user_pass'])) {
            $data['admin']['user_pass'] = md5('123456');
        }else{
            $data['admin']['user_pass'] = md5($data['user_pass']);
        }

        try{
            $id = Db::table('su_admin')->insertGetId($data['admin']);
            return array($id);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 执行管理员修改方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toAdminEdit($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AdminAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $pid = self::adminAlreadyCreat($data, 1);
        if(!is_array($pid)) {
            return $pid;
        }
        if(isset($data['admin']['user_id'])) {
            unset($data['admin']['user_id']);
        }
        if(isset($data['admin']['user_pass'])) {
            $data['admin']['user_pass'] = md5($data['admin']['user_pass']);
        }
        try{
            $id = Db::table('su_admin')->where('user_id',$pid[0])->update($data['admin']);
            return array($id);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 执行管理员删除方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toAdminDel($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new AdminAutoLoad();
        $data = $group->toGroup($data);
        /* 如果检测通过的话方法会返回一个索引数组，其中第一项就是生成的uuid，否则就会返回错误信息字符串 */
        $pid = self::adminAlreadyCreat($data, 1);
        if(!is_array($pid)) {
            return $pid;
        }
        if(isset($data['admin']['user_id'])) {
            unset($data['admin']['user_id']);
        }
        if(isset($data['admin']['user_pass'])) {
            $data['admin']['user_pass'] = md5($data['admin']['user_pass']);
        }
        try{
            $id = Db::table('su_admin')->where('user_id',$pid[0])->update(['show_type'=>0]);
            return array($id);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 根据账号密码获取相对应管理员id
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toAdminId()
    {
        $data = request()->param();
        if(!isset($data['userName'])) {
            return '请传递用户名';
        }
        if(!isset($data['userPass'])) {
            return '请传递用户密码';
        }
        $where = array(
            'sa.user_name' => $data['userName'],
            'sa.user_pass' => md5($data['userPass']),
            'sa.show_type' => 1
        );
        $list = Db::table('su_admin')
                ->alias('sa')
                ->join('su_role sr','sr.role_id = sa.user_role')
                ->join('su_company sc','sc.company_id = sa.user_company','left')
                ->where($where)
                ->field(['sa.user_id as user','sa.user_role as role','sc.company_id as company','sc.company_full_name as companyName','sr.role_name as roleName'])
                ->select();
        if(empty($list)) {
            return '账号或密码错误，请检查传递的账号和密码';
        }
        $node = self::fetchNode($list[0]['role']);
        $list[0]['node'] = $node;
        return array($list[0]);
    }

    /**
     * 获取管理员角色列表方法
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toRole()
    {
        $list = Db::table('su_role')
                    ->field(['role_id as role','role_name as roleName'])
                    ->select();
        if(empty($list)) {
            return '尚未存在管理员角色';
        }
        return $list;
    }

    /**
     * 检测传递的用户相关信息是否有误以及是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function adminAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['admin'])) {
            return '请传递需要添加的用户信息';
        }
        if(!isset($data['admin']['user_id']) && $token == 1) {
            return '请传递需要处理的用户id';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $admin = $data['admin'];
        if($token == 0){
            $list = AdminModel::get(['user_name' => $admin['user_name'],'show_type'=>1]);
        }else{
            $list = AdminModel::get(['user_id' => $admin['user_id'],'show_type'=>1]);
        }
        /* 检测企业是否存在并如果是修改之类的操作的话就需要返回查询出来的企业id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的用户名已经存在，请检查传递的用户手机号';
        }elseif(!empty($list) && $token == 1){
            return array($admin['user_id']);
        }elseif($token ==  1){
            return '查无此用户，请检查传递的用户id';
        }
        $uuid = md5(uniqid(mt_rand(),true));
        return array($uuid);
    }
    // +----------------------------------------------------------------------
    // | 辅助类型相关
    // +----------------------------------------------------------------------
    /**
     * 根据角色id获取对应的权限列表
     * @param $role
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function  fetchNode($role)
    {
        $list = Db::table('su_role_node')
            ->alias('srn')
            ->join('su_control sc','sc.control_id = srn.control_id')
            ->where('srn.role_id',$role)
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
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new AdminAutoLoad();
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