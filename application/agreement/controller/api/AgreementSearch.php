<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/10
 * Time: 15:45
 */

namespace app\agreement\controller\api;

use think\Controller;
use think\Db;
use \app\agreement\controller\api\AgreementWhere as AgreementWhere;

/**
 * Class AgreementSearch
 * @package app\agreement\controller\api
 */
class AgreementSearch extends Controller
{

    public static function toList($search)
    {
//        $field = new EngineerAutoLoad();
//        $check = $field::$fieldArr;
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new AgreementWhere();
        $whereArr = $where->where;
        $where = $where->getWhereArray($search);
        $where['se.show_type'] = 1;
        /* 根据预先写好的查询条件获取需要获取到的字段信息 */
        $field = array();
        foreach($whereArr as $whereKey => $whereRow) {
            array_push($field, $whereKey);
        }
        array_push($field,'set.type_name');
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_engineering')
                ->alias('se')
                ->join('su_engineering_type set','se.engineering_type = set.type_id')
                ->field($field)
                ->where($where)
                ->limit($page[0], $page[1])
                ->select();
        }catch(\Exception $e) {
            return $e->getMessage();
        }
//        $list = self::idToName($list, $check);
        return $list;
    }

    /**
     * 分页初始化方法
     * @param $data
     * @return array
     */
    private static function pageInit($data)
    {
        $result = array(0,20);
        /* 如果传递数据不符合规范，就返回默认分页数据 */
        if(!isset($data['page']) || count($data['page']) != 2) {
            return $result;
        }
        $result = array($data['page'][0] * $data['page'][1], $data['page'][1]);
        return $result;
    }
}