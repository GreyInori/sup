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
use app\material\controller\api\file\FileMain as FileMain;
use app\material\controller\api\file\FileSearch as FileSearch;
//use app\material\model\StandardModel;
use app\material\controller\api\MaterialMain as MaterialMain;
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

    // +----------------------------------------------------------------------
    // | 检测标准相关
    // +----------------------------------------------------------------------
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
    // +----------------------------------------------------------------------
    // | 检测费用相关
    // +----------------------------------------------------------------------
    /**
     * 查询获取检测价格方法
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
     * 检测价格添加方法
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
     * 检测价格修改方法
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
    // +----------------------------------------------------------------------
    // | 文件号相关
    // +----------------------------------------------------------------------
    /**
     * 获取文件号列表
     * @return false|string
     */
    public function getFileList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('fileList',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = FileSearch::toFileList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $change = new FileMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 文件号添加方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postFileAdd()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('fileAdd');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = FileMain::toFileAdd($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 文件号修改方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postFileEdit()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('fileEdit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = FileMain::toFileEdit($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 文件号删除方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postFileDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('fileDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = FileMain::toFileDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }
    // +----------------------------------------------------------------------
    // | 检测类型相关
    // +----------------------------------------------------------------------
    /**
     * 获取检测类型列表方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTypeList()
    {
        $data = FieldCheck::checkData('typeList');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = MaterialMain::fetchTypelist($data);
        if(!is_array($list)){
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200, 'success', $list);
    }

    /**
     * 分类添加方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postTypeAdd()
    {
        $data = FieldCheck::checkData('typeAdd');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = MaterialMain::toTypeAdd($data);
        if(!is_array($list)){
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200, 'success', $list['uid']);
    }

    /**
     * 分类修改方法
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function postTypeEdit()
    {
        $data = FieldCheck::checkData('typeEdit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = MaterialMain::toTypeEdit($data);
        if(!is_array($list)){
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200, 'success', $list['uid']);
    }

    /**
     * 删除分类方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postTypeDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('typeDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = MaterialMain::toTypeDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }
    // +----------------------------------------------------------------------
    // | 图片上传规范相关
    // +----------------------------------------------------------------------
    /**
     * 获取上传图片规范数据
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBlockList()
    {
        $data = FieldCheck::checkData('blockList');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = MaterialMain::fetchBlocklist($data);
        if(!is_array($list)){
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200, 'success', $list);
    }

    /**
     * 图片上传规范添加方法
     * @return false|string
     */
    public function postBlockAdd()
    {
        $data = FieldCheck::checkData('blockAdd');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = MaterialMain::toBlockAdd($data);
        if(!is_array($list)){
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200, 'success', $list['uid']);
    }

    /**
     * 图片上传规范修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postBlockEdit()
    {
        $data = FieldCheck::checkData('blockEdit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = MaterialMain::toBlockEdit($data);
        if(!is_array($list)){
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200, 'success', $list['uid']);
    }

    /**
     * 图片上传规范删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postBlockDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('blockDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = MaterialMain::toBlockDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }
    // +----------------------------------------------------------------------
    // | 检测项目相关
    // +----------------------------------------------------------------------
    /**
     * 获取检测项目列表方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMaterialList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('materialList');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = MaterialMain::fetchMaterialList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 检测项目添加方法
     * @return false|string
     */
    public function postMaterialAdd()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('materialAdd');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = MaterialMain::toMaterialAdd($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测项目修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postMaterialEdit()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('materialEdit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = MaterialMain::toMaterialEdit($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测项目删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postMaterialDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('materialDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = MaterialMain::toMaterialDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 获取检测项目字段以及默认值方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMaterialField()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('materialField');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = MaterialMain::toMaterialField($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 检测项目字段添加方法
     * @return false|string
     */
    public function postMaterialFieldAdd()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('materialFieldAdd');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = MaterialMain::toMaterialFieldAdd($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测项目字段修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postMaterialFieldEdit()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('materialFieldEdit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = MaterialMain::toMaterialFieldEdit($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测项目字段删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postMaterialFieldDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('materialFieldDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = MaterialMain::toMaterialFieldDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }

    /**
     * 检测项目字段默认值添加方法
     * @return false|string
     */
    public function postMaterialDefaultAdd()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('materialDefaultAdd');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = MaterialMain::toDefaultAdd($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测项目字段默认值修改方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postMaterialDefaultEdit()
    {
        /* 检测传递参数是否符合规范 */
        $data = FieldCheck::checkData('materialDefaultEdit');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = MaterialMain::toDefaultEdit($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list['uid']);
    }

    /**
     * 检测项目字段默认值删除方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postMaterialDefaultDel()
    {
        /* 检测传递的参数是否符合企业添加的规范，如果不符合就返回错误信息 */
        $data = FieldCheck::checkData('materialDefaultDel');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 执行企业添加方法，如果成功的话就返回企业的id，否则返回错误信息 */
        $list = MaterialMain::toDefaultDel($data);
        if(is_array($list)){
            return self::returnMsg(200, 'success', $list['uid']);
        }else{
            return self::returnMsg(500,'fail',$list);
        }
    }
}