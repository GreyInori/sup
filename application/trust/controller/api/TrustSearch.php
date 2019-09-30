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
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new TrustWhere();
        $where = $where->getWhereArray($search);
        $where['st.show_type'] = 1;
        if(isset($search['show'])){
            $where['st.show_type'] = $search['show'];
        }
        if(empty($where)) {
            return '请传递正确的查询条件';
        }
        /* 检测管理员状态，返回相关的查询条件 */
        $whereIn = self::roleCheck($search);
        if($whereIn) {
            $where['st.engineering_id'] = ['IN',$whereIn];
        }
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_trust')
                ->alias('st')
                ->join('su_report sr','sr.trust_id = st.trust_id','left')
                ->join('su_report_main srm','srm.report_number = sr.report_number','left')
                ->field(['srm.report_content','sr.report_file','st.testing_material','st.is_report','st.trust_id','st.serial_number','st.input_testing_company','st.testing_name','st.project_name','st.custom_company','st.input_time','st.testing_price','st.is_submit','st.is_print','st.is_witness','st.is_sample','st.is_testing','st.is_cancellation','st.is_allow','st.testing_result'])
                ->where($where)
                ->limit($page[0], $page[1])
                ->order('st.input_time DESC')
                ->select();
        }catch(\Exception $e) {
            return $e->getMessage();
        }
        return $list;
    }

    /**
     * 检测当前获取列表是否为最高管理员方法，如果不是，就返回相关列表的whereIN查询条件
     * @param $admin
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function roleCheck($admin)
    {
        $result = '';
        if(!isset($admin['user_name'])) {
            return false;
        }
        $main = Db::table('su_admin')->where('user_name', $admin['user_name'])->where('show_type',1)->field(['user_role'])->select();
        if(empty($main) || $main[0]['user_role'] == 1) {
            return false;
        }
        if(isset($admin['user_name'])) {
            $user = $admin['user_name'];
        }elseif(isset($admin['mobile'])) {
            $user = $admin['mobile'];
        }
        if(isset($user)) {
            $list = Db::table('su_engineering_divide')->where('divide_user',$admin['user_name'])->field(['engineering_id'])->select();
        }else{
            return false;
        }
        /* 如果不是最高管理员的话，就获取当前账号相关的工程列表 */
        if(!empty($list)) {
            foreach($list as $key => $row) {
                $result .= "{$row['engineering_id']},";
            }
        }
        return rtrim($result,',');
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