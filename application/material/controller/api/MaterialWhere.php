<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/29
 * Time: 20:44
 */

namespace app\material\controller\api;

use think\Controller;
use think\Db;

/**
 * Class MaterialWhere
 * @package app\material\controller\api
 */
class MaterialWhere extends Controller
{
    /**
     * 返回查询条件方法
     * @param array $type
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getWhereArray($type = array())
    {
        return $this->createQueryCode($type);
    }

    /**
     * 生成基本查询条件
     * @param $type
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function createQueryCode($type)
    {
        $checkType = $this->checkType($type);
        $checkType['sm.show_type'] = 1;
        return $checkType;
    }

    /**
     * 获取检查项目类型的where查询语句
     * @param $type
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function checkType($type)
    {
        if(!isset($type['type_id'])) {
           return '';
        }
        /* 判断当前传递过来的类型下是否有子类，如果没有就直接返回类型查询条件，否则返回子类查询条件 */
        $typeList = Db::table('su_material_type')
                        ->where('type_pid',$type['type_id'])
                        ->field(['type_id'])
                        ->select();
        if(empty($typeList)) {
            return array('material_type'=>['=',$type['type_id']]);
        }
        $typeStr = '';
        foreach($typeList as $k => $v) {
            $typeStr .= "{$v['type_id']},";
        }
        $typeStr = rtrim($typeStr, ',');
        return array('material_type'=>['IN',$typeStr]);
    }
}