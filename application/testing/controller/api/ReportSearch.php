<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/9
 * Time: 1:27
 */

namespace app\testing\controller\api;

use think\Controller;
use think\Db;
use \app\testing\controller\api\ReportWhere as ReportWhere;

class ReportSearch extends Controller
{
    /**
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new ReportWhere();
        $where = $where->getWhereArray($search);
        $where['st.show_type'] = 1;
        if(isset($search['show'])){
            $where['st.show_type'] = $search['show'];
        }
        if(empty($where)) {
            return '请传递正确的查询条件';
        }
        try{
            $list = Db::table('su_trust')
                    ->alias('st')
                    ->join('su_report sr','sr.trust_id = st.trust_id')
                    ->join('su_report_main srm','srm.report_number = sr.report_number','left')
                    ->join('su_material_type smt','smt.type_id = st.testing_quality')
                    ->join('su_engineering se','se.engineering_id = st.engineering_id')
                    ->field(['sr.report_file','srm.report_content','st.input_testing_company','st.trust_id','smt.type_name','st.testing_name','sr.report_number','se.engineering_name','sr.report_time','sr.report_main'])
                    ->where($where)
                    ->order('sr.report_time DESC')
                    ->limit($page[0],$page[1])
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