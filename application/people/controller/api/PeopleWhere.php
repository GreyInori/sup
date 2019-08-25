<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/21
 * Time: 11:34
 */

namespace app\people\controller\api;

use think\Controller;

/**
 * 生成人员列表查询条件类
 * @package app\people\controller\api
 */
class PeopleWhere extends Controller
{
    private $where = array(
        'is_record' => ['sc.company_type','='],
        'company_number' => ['sc.company_number','LIKE','code%'],
        'company_full_name' => ['sc.company_full_name','LIKE','%code%'],
        'company_linkman' => ['sc.company_link_man','LIKE','%code%'],
        'company_corporation' => ['scm.company_corporation','LIKE','%code%'],
        'company_corporation_mobile' => ['scm.company_corporation_mobile','LIKE','code%'],
    );

    /**
     * 根据传递的参数返回指定的查询条件
     * @param array $where
     * @return mixed
     */
    public function getWhereArray($where = array())
    {
        return $this->creatQueryCode($where);
    }

    /**
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
            if(!is_array($row)){
                $conditions = array($where[$key][0] => array($where[$key][1]));
                if(isset($where[$key][2])){
                    $row = str_replace('code',$row,$where[$key][2]);
                }
                array_push($conditions[$where[$key][0]], $row);
            }
        }
        return $conditions;
    }
}