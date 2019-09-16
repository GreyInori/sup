<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/11
 * Time: 9:33
 */

namespace app\area\controller;

use think\Controller;
use \app\api\controller\Send;
use \app\area\controller\AreaAutoLoad as FieldCheck;
use \app\area\controller\api\AreaMain as AreaMain;

/**
 * Class Area
 * @package app\area\controller
 */
class Area extends Controller
{
    use Send;
    /**
     * 地区添加方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postAreaAdd()
    {
        $data = FieldCheck::checkData('add');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行合同添加方法,如果成功的话就返回合同id，否则返回错误信息 */
        $list = AreaMain::toAreaAdd($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list[0]);
        }else {
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 地区修改方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postAreaEdit()
    {
        $data = FieldCheck::checkData('edit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行合同添加方法,如果成功的话就返回合同id，否则返回错误信息 */
        $list = AreaMain::toAreaEdit($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list[0]);
        }else {
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 地区删除方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postAreaDel()
    {
        $data = FieldCheck::checkData('del');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行合同添加方法,如果成功的话就返回合同id，否则返回错误信息 */
        $list = AreaMain::toAreaDel($data);
        if(is_array($list)) {
            return self::returnMsg(200,'success',$list[0]);
        }else {
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 获取地区列表
     * @return false|string
     */
    public function getArea()
    {
        $data = FieldCheck::checkData('list');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行合同添加方法,如果成功的话就返回合同id，否则返回错误信息 */
        $list = AreaMain::toArea($data);
        if(is_array($list)) {
            $list = AreaMain::fieldChange($list);
            return self::returnMsg(200,'success',$list);
        }else {
            return self::returnMsg(500,'fail',$list);
        }
    }
}