<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 10:19
 */

namespace app\engineer\controller;

use think\Controller;
use \app\api\controller\Send;
use \app\engineer\controller\EngineerAutoLoad as FieldCheck;
use \app\engineer\controller\api\EngineerMain as EngineerMain;
use \app\engineer\controller\api\DivideMain as DivideMain;
use \app\engineer\controller\api\EngineerSearch as EngineerSearch;

/**
 * Class Engineer
 * @package app\engineer\controller
 */
class Engineer extends Controller
{
    use Send;
    /**
     * 工程录入方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postEngineerAdd()
    {
        /* 检测传递的参数是否符合录入的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('add');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $result = EngineerMain::toAdd($data);
        if(!is_array($result)) {
            return self::returnMsg(500,'fail',$result);
        }
        return self::returnMsg(200,'success',$result['uid']);
    }

    /**
     * 工程修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postEngineerEdit()
    {
        /* 检测传递的参数是否符合录入的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('edit');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        $result = EngineerMain::toEdit($data);
        if(!is_array($result)) {
            return self::returnMsg(500,'fail',$result);
        }
        return self::returnMsg(200,'success',$result['uid']);
    }

    /**
     * 删除工程方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postEngineerDel()
    {
        /* 检测传递的参数是否符合录入的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('del');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        $result = EngineerMain::toDel($data);
        if(!is_array($result)) {
            return self::returnMsg(500,'fail',$result);
        }
        return self::returnMsg(200,'success',$result[0]);
    }

    /**
     * 工程审核通过方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postEngineerPass()
    {
        /* 检测传递的参数是否符合录入的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('del');
        if(!is_array($data)){
            return self::returnMsg(500,'fail',$data);
        }
        $result = EngineerMain::toPass($data);
        if(!is_array($result)) {
            return self::returnMsg(500,'fail',$result);
        }
        return self::returnMsg(200,'success',$result[0]);
    }

    /**
     * 获取工程列表方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function getEngineerList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = EngineerSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $change = new EngineerMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 获取工程详细信息方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getEngineerMain()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('main');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业详细数据，如果有抛出异常的话就返回错误信息 */
        $list = EngineerMain::toMain($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 根据工程id获取该工程下的成员公司
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getEngineerDivide()
    {
        $data = FieldCheck::checkData('divide');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业详细数据，如果有抛出异常的话就返回错误信息 */
        $list = EngineerMain::fetchEngineerDivide($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $list = EngineerMain::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 根据企业账号密码获取对应的企业详情
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDivideEngineer()
    {
        $data = EngineerMain::fetchDivideEngineer();
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        return self::returnMsg(200,'success',$data);
    }

    /**
     * 根据账号密码获取对应的企业id
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDivideCompanyId()
    {
        $data = EngineerMain::toDivideCompanyId();
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        return self::returnMsg(200,'success',$data[0]);
    }

    /**
     * 给企业添加指定成员
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postEngineerDivideAdd()
    {
        $data = EngineerMain::toEngineerDivideAdd();
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        return self::returnMsg(200,'success',$data);
    }

    /**
     * 给工程内成员分配指定的参与企业
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function postAllowDivide()
    {
        $data = EngineerMain::toAllowDivide();
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        return self::returnMsg(200,'success',$data);
    }
    // +----------------------------------------------------------------------
    // | 成员公司相关
    // +----------------------------------------------------------------------
    /**
     * 获取成员公司列表
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDivide()
    {
        $list = DivideMain::fetchDivide();
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 成员公司添加
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postDivideAdd()
    {
        $list = DivideMain::toDivideAdd();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 成员公司修改
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postDivideEdit()
    {
        $list = DivideMain::toDivideEdit();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 成员公司删除
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postDivideDel()
    {
        $list = DivideMain::toDivideDel();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }
    // +----------------------------------------------------------------------
    // | 地面基础类型相关
    // +----------------------------------------------------------------------
    /**
     * 工程类型列表
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFoundation()
    {
        $list = EngineerMain::fetchFoundations();
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 工程类型添加方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postFoundationAdd()
    {
        $list = EngineerMain::toFoundationsAdd();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 工程类型修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postFoundationEdit()
    {
        $list = EngineerMain::toFoundationsEdit();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 工程类型删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postFoundationDel()
    {
        $list = EngineerMain::toFoundationsDel();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 获取工程量类型列表
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getEngineerType()
    {
        $list = EngineerMain::fetchEngineerType();
        return self::returnMsg(200,'success',$list);
    }
    // +----------------------------------------------------------------------
    // | 地面基础类型相关
    // +----------------------------------------------------------------------
    /**
     * 地面基础类型添加方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postEngineerTypeAdd()
    {
        $list = EngineerMain::toEngineerTypeAdd();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 地面基础类型修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postEngineerTypeEdit()
    {
        $list = EngineerMain::toEngineerTypeEdit();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 地面基础类型删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postEngineerTypeDel()
    {
        $list = EngineerMain::toEngineerTypeDel();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }
}