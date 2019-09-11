<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/10
 * Time: 14:17
 */

namespace app\agreement\controller;

use think\Controller;
use \app\api\controller\Send;
use \app\agreement\controller\AgreementAutoLoad as FieldCheck;
use \app\agreement\controller\api\AgreementMain as AgreementMain;
use \app\agreement\controller\api\AgreementSearch as AgreementSearch;

/**
 * Class Agreement
 * @package app\agreement\controller
 */
class Agreement extends Controller
{
    use Send;

    /**
     * 合同添加方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postAgreementAdd()
    {
        $data = FieldCheck::checkData('add');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行合同添加方法,如果成功的话就返回合同id，否则返回错误信息 */
        $list = AgreementMain::toAdd($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list['uid']);
        }else {
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 合同修改方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postAgreementEdit()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('edit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = AgreementMain::toEdit($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 获取合同列表
     * @return false|string
     */
    public function getAgreement()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = AgreementSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $change = new AgreementMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 合同删除方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postAgreementDel()
    {
        /* 检测传递的参数是否符合合同添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('del');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行合同删除方法，如果成功的话就返回删除结果，否则返回错误信息 */
        $list = AgreementMain::toDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }
}