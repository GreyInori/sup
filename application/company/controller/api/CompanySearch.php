<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/21
 * Time: 15:35
 */

namespace app\company\controller\api;

use think\Controller;
use think\Db;
use \app\company\controller\api\CompanyWhere as companyWhere;

/**
 * 企业查询类
 * @package app\company\controller\api
 */
class CompanySearch extends Controller
{
    /**
     * 获取指定企业列表方法
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new companyWhere();
        $where = $where->getWhereArray($search);
        if(empty($where)) {
            return '请传递正确的查询条件';
        }
        $where['show_type'] = 1;

        if(isset($search['show'])){
            $where['show_type'] = $search['show'];
        }
        /* 如果传递了手机号，就获取对应手机号下的企业列表 */
        $mobile = request()->param();
        if(isset($mobile['createUser'])) {
            $where['sc.create_mobile'] = $mobile['createUser'];
        }

        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_company')
                ->alias('sc')
                ->join('su_company_main scm','sc.company_id = scm.company_id','LEFT')
                ->field(['sc.company_id','sc.company_number','sc.company_mobile','sc.company_full_name','sc.company_linkman','scm.company_corporation','scm.company_corporation_mobile'])
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