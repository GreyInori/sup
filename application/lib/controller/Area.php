<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/25
 * Time: 14:56
 */

namespace app\lib\controller;

use think\Db;

/**
 * Class Area
 * @package app\lib\controller
 */
class Area
{
    /**
     * 获取地区父类方法
     * @param $areaId
     * @param array $result
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getAreaList($areaId, $result = array())
    {
        $list = Db::table('su_area')->where('area_id', $areaId)->field(['area_id','area_pid','area_name'])->select();
        if(empty($list)) {
            return $result;
        }
        array_unshift($result, $list[0]);
        $result = self::getAreaList($list[0]['area_pid'], $result);
        return $result;
    }
}