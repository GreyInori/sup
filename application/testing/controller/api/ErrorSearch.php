<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/8
 * Time: 23:06
 */

namespace app\testing\controller\api;

use think\Controller;
use think\Db;
use \app\testing\controller\api\ErrorWhere as ErrorWhere;

/**
 * Class ErrorSearch
 * @package app\testing\controller\api
 */
class ErrorSearch extends Controller
{
    /**
     * 获取异常列表方法
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function toList($search)
    {
        $page  = self::pageinit();
        $where = new ErrorWhere();
        $where = $where->getWhereArray($search);
        $where['st.show_type'] = 1;
        if(empty($where)) {
            return '请传递正确的查询条件';
        }
        if(isset($search['show'])) {
            $where['st.show_type'] = $search['show'];
        }
        $where['sts.testing_error'] = 1;
        try{
            $list = Db::table('su_testing_status')
                    ->alias('sts')
                    ->join('su_trust st','st.trust_id = sts.trust_id')
                    ->join('su_engineering se','se.engineering_id = st.engineering_id')
                    ->join('su_testing_error ste','ste.trust_id = sts.trust_id')
                    ->where($where)
                    ->field(['ste.error_id','ste.error_main','ste.error_response','se.construction_company','st.trust_code','ste.error_time'])
                    ->limit($page[0],$page[1])
                    ->select();
            $count = Db::table('su_testing_status')
                        ->alias('sts')
                        ->join('su_trust st','st.trust_id = sts.trust_id')
                        ->join('su_testing_error ste','ste.trust_id = sts.trust_id')
                        ->where($where)
                        ->field(['count(st.trust_id) as page'])
                        ->select();
        }catch(\Exception $e) {
            return $e->getMessage();
        }
        $list['count'] = $count[0]['page'];
        return $list;
    }

    /**
     * 分页初始化方法
     * @return array
     */
    private static function pageInit()
    {
        $data = request()->param();
        $result = array(0,200);
        /* 如果传递数据不符合规范，就返回默认分页数据 */
        if(!isset($data['page']) || count($data['page']) != 2) {
            return $result;
        }
        $result = array($data['page'][0] * $data['page'][1], $data['page'][1]);
        return $result;
    }
}