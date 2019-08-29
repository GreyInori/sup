<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/29
 * Time: 20:44
 */

namespace app\material\controller\api;

use think\Controller;

/**
 * Class MaterialWhere
 * @package app\material\controller\api
 */
class MaterialWhere extends Controller
{
    /**
     * 返回查询条件
     * @param array $type
     */
    public function getWhereArray($type = array())
    {
        return $this->createQueryCode($type);
    }

    /**
     * 生成基本查询条件
     * @param $type
     * @return string
     */
    private function createQueryCode($type)
    {
        $checkType = $this->checkType($type);
        return $checkType;
    }

    /**
     * 根据传递的类型数组生成类型查询条件
     * @param $type
     * @param string $whereStr
     * @return string
     */
    private function checkType($type, $whereStr = '')
    {
        if(empty($type) || !is_array($type)) {
            return $whereStr;
        }
        foreach($type as $key => $row) {
            var_dump($row);exit;
            if(isset($row['child'])) {
                $whereStr .= $this->checkType($row['child'],$whereStr);
            }
            return "{$row['type']},";
        }
        return $whereStr;
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