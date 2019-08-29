<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 17:31
 */

namespace app\material\controller;

use app\material\controller\api\price\PriceMain as PriceMain;
use app\material\controller\api\price\PriceSearch;
//use app\material\model\StandardModel;
use think\Controller;
use \app\material\controller\api\standard\StandardSearch;
use \app\material\controller\MaterialAutoLoad as FieldCheck;
use \app\material\controller\api\standard\StandardMain as StandardMain;
use \app\api\controller\Send;

/**
 * Class Material
 * @package app\material\controller
 */
class Material extends Controller
{
    use Send;

    /**
     * 查询获取检测标准方法
     * @return false|string
     */
    public function getStandardList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('standardList',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = StandardSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $change = new StandardMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 检测标准添加方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postStandardAdd()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('standardAdd');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = StandardMain::toStandardAdd($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测标准修改方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postStandardEdit()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('standardEdit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = StandardMain::toStandardEdit($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测标准删除方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postStandardDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('standardDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = StandardMain::toStandardDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 查询获取检测标准方法
     * @return false|string
     */
    public function getPriceList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('priceList',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = PriceSearch::toPriceList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $change = new PriceMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 检测标准添加方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postPriceAdd()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('priceAdd');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = PriceMain::toPriceAdd($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测标准添加方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postPriceEdit()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('priceEdit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = PriceMain::toPriceEdit($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 删除检测标准费用方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postPriceDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('priceDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = PriceMain::toPriceDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    public function getFileList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('priceList',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = PriceSearch::toPriceList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $change = new PriceMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }
}