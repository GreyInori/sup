<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 15:37
 */

namespace app\meeting\controller\api;

class MeetingWhere
{
    private $where = array(
        'meeting_title' => ['hm.meeting_title', 'LIKE', '%code%'],
        'user_mobile' => ['hmu.user_mobile', '='],
        'meeting_address' => ['hm.meeting_address', 'LIKE', '%code%'],
        'meeting_verify' => ['hm.meeting_verify', '='],
    );

    /**
     * 获取查询条件方法
     * @param array $where
     * @return array
     */
    public function getWhereArray($where = array())
    {
        return $this->createQueryCode($where);
    }

    /**
     * 创建查询条件方法
     * @param array $where
     * @return array
     */
    private function createQueryCode($where = array())
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