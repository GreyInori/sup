<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/27
 * Time: 9:30
 */

namespace app\engineer\controller\api;

use think\Controller;

/**
 * Class ReckonerWhere
 * @package app\engineer\controller\api
 */
class ReckonerWhere extends Controller
{
    /**
     * @var array
     * 把传递过来id字段转换成后台数据库对应的字段
     */
    public $where = array(
        'contract_code' => ['se.contract_code','LIKE','code%'],
        'engineering_name' => ['se.engineering_name','LIKE','%code%'],
        'people_code' => ['sp.people_code','LIKE','%code%'],
        'people_name' => ['sp.people_name','LIKE','%code%'],
        'people_mobile' => ['sp.people_mobile','LIKE','%code%'],
    );

    /**
     * 根据传递的参数返回指定的查询条件
     * @param array $where
     * @return array
     */
    public function getWhereArray($where = array())
    {
        return $this->creatQueryCode($where);
    }

    /**
     * 生成查询条件方法
     * @param array $where
     * @return array
     */
    private function creatQueryCode($where = array())
    {
        $result = array();
        $resultWhere = array();
        /* 循环传递过来的查询条件，比对已经有了的字段，进行查询匹配 */
        foreach($where as $key => $row) {
            $whereCode = $this->whereChange($key, $row);
            if($whereCode != 'fail'){
                array_push($result, $whereCode);
            }
        }
        /* 由于查询数组需要的查询条件是键值对数组，因此需要把获取到的索引数组转换为 查询字段 => 查询条件 键值对格式 */
        foreach($result as $row) {
            foreach($row as $valueKey => $valueRow) {
                $resultWhere[$valueKey] = $valueRow;
            }
        }

        return $resultWhere;
    }

    /**
     * 生成详细的查询条件方法
     * @param $key
     * @param $row
     * @return array|string
     */
    private function whereChange($key, $row)
    {
        $where = $this->where;
        $conditions = 'fail';
        /* 根据事先固定的字段信息，判断当前查询请求字段是否存在 */
        if(isset($where[$key])) {
            /* 如果查询结果是单个值的数据的话，就进行制定查询条件匹配，由于事先定义好了 LIKE 查询的条件了，就根据制定字段的条件来生成需要的查询条件 */
            if(!is_array($row)) {
                $conditions = array($where[$key][0] => array($where[$key][1]));
                /* 如果查询条件的人员或者公司的话需要把通过文字信息获取到id字符串，用于查询操作 */
                if(isset($where[$key][2])) {
                    $row = str_replace('code', $row, $where[$key][2]);
                }
                array_push($conditions[$where[$key][0]], $row);
            }
        }
        return $conditions;
    }
}