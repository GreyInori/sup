<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/11
 * Time: 9:16
 */

namespace app\admin\controller;

use app\admin\controller\api\AdminSearch;
use think\Controller;
use \app\api\controller\Send;
use \app\admin\controller\AdminAutoLoad as FieldCheck;
use \app\admin\controller\api\AdminMain as AdminMain;

/**
 * Class Admin
 * @package app\admin\controller
 */
class Admin extends Controller
{
    use Send;

    /**
     * 添加管理员方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postAdminAdd()
    {
        $data = FieldCheck::checkData('add');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = AdminMain::toAdminAdd($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list[0]);
        }else {
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 修改管理员方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postAdminEdit()
    {
        $data = FieldCheck::checkData('edit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = AdminMain::toAdminEdit($data);
        if(is_array($list)){
            return self::returnMsg(200,'success',$list[0]);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 删除管理员方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postAdminDel()
    {
        $data = FieldCheck::checkData('del');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = AdminMain::toAdminDel($data);
        if(is_array($list)){
            return self::returnMsg(200,'success',$list[0]);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 获取管理员列表方法
     * @return false|string
     */
    public function getAdminList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = AdminSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $change = new AdminMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 获取绑定了企业的用户列表
     * @return false|string
     */
    public function getCompanyAdmin()
    {
        $list = AdminMain::toCompanyAdmin();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $list = AdminMain::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 根据账号密码获取管理员信息以及权限方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAdminLogin()
    {
        $data = AdminMain::toAdminId();
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        return self::returnMsg(200,'success',$data[0]);
    }

    /**
     * 获取管理员角色列表
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAdminRole()
    {
        $data = AdminMain::toRole();
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        return self::returnMsg(200,'success',$data);
    }
}