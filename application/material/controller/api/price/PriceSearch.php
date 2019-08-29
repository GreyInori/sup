<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/29
 * Time: 14:32
 */

namespace app\material\controller\api\price;

use think\Controller;
use think\Db;
use \app\material\controller\api\price\PriceWhere as PriceWhere;

/**
 * Class PriceSearch
 * @package app\material\controller\api\price
 */
class PriceSearch extends Controller
{
    /**
     * @param $search
     * @return string
     */
    public static function toPriceList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new PriceWhere();
        $where = $where->getWhereArray($search);
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_testing_price')
                ->alias('stp')
                ->field(['stp.testing_code','stp.testing_number','stp.company_full_name','stp.testing_type','stp.testing_from','stp.testing_price','stp.material_remarks','stp.tag_number','stp.is_end'])
                ->where($where)
                ->limit($page[0], $page[1])
                ->select();
        }catch(\Exception $e) {
            return $e->getMessage();
        }
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