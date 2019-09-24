<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 2:13
 */

namespace app\testing\controller\api;

use think\Controller;
use think\Db;
use \app\testing\controller\api\TestingWhere as TestingWhere;
use \app\testing\controller\TestingAutoLoad as TestingAutoLoad;

/**
 * Class TestingSearch
 * @package app\testing\controller\api
 */
class TestingSearch extends Controller
{
    /**
     * 获取委托单列表方法
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toList($search)
    {
        $check = new TestingAutoLoad();
        $field = $check::$fieldGroup;
        $field = $field['testing'];
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new TestingWhere();
        $where = $where->getWhereArray($search);
        $where['st.show_type'] = 1;
        if(empty($where)) {
            return '请传递正确的查询条件';
        }

        if(isset($search['show'])){
            $where['st.show_type'] = $search['show'];
        }
        /* 如果传递了公司的话，就根据公司获取指定的工程列表，把查询条件缩小到该企业相关的项目 */
        if(isset($search['company_id'])){
            $engineer = Db::table('su_engineering_divide')
                            ->where('member_id',$search['company_id'])
                            ->field(['engineering_id'])
                            ->select();
            /* 如果该企业有负责的相关的工程的话，就获取工程查询条件，否则就匹配到无工程 */
            if(!empty($engineer)) {
                $engineerStr = '';
                foreach($engineer as $key => $row) {
                    $engineerStr .= "{$row['engineering_id']},";
                }
                $engineerStr = rtrim($engineerStr,',');
                $where['engineering_id'] = array('IN',$engineerStr);
            }else{
                $where['engineering_id'] = array('=',0);
            }
        }
        $key = array_search('company_id',$field);
        array_push($field,'sr.report_file');
        unset($field[$key]);
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_testing_status')
                ->alias('sts')
                ->join('su_trust st','st.trust_id = sts.trust_id')
                ->join('su_engineering se','se.engineering_id = st.engineering_id')
                ->join('su_material sm','sm.material_id = st.testing_material')
                ->join('su_material_type smt','smt.type_id = st.testing_type')
                ->join('su_report sr','sr.trust_id = st.trust_id')
                ->field($field)
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