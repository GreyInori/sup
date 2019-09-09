<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/2
 * Time: 0:25
 */

namespace app\trust\controller\api;

use think\Controller;
use think\Db;
use \app\trust\controller\api\TrustWhere as TrustWhere;

/**
 * Class TrustSearch
 * @package app\trust\controller\api
 */
class TrustSearch extends Controller
{
    /**
     * 获取委托单列表方法
     * @param $search
     * @return string
     */
    public static function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new TrustWhere();
        $where = $where->getWhereArray($search);
        $where['show_type'] = 1;
        if(isset($search['show'])){
            $where['show_type'] = $search['show'];
        }
        if(empty($where)) {
            return '请传递正确的查询条件';
        }
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_trust')
                ->alias('st')
                ->field(['st.is_report','st.trust_id','st.serial_number','st.input_testing_company','st.testing_name','st.project_name','st.custom_company','st.input_time','st.testing_price','st.is_submit','st.is_print','st.is_witness','st.is_sample','st.is_testing','st.is_cancellation','st.is_allow','st.testing_result'])
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