<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 17:31
 */

namespace app\material\controller;

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
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取企业列表数据，如果有抛出异常的话就返回错误信息 */
        $list = StandardSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        $change = new StandardMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }
}