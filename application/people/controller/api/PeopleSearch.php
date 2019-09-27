<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/21
 * Time: 15:35
 */

namespace app\people\controller\api;

use think\Controller;
use think\Db;
use \app\people\controller\api\PeopleWhere as companyWhere;

/**
 * 人员查询类
 * @package app\people\controller\api
 */
class PeopleSearch extends Controller
{
    /**
     * 获取指定企业列表方法
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit();
        $where = new companyWhere();
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
            $list = Db::table('su_people')
                ->alias('sp')
                ->field(['sp.people_user','sp.people_id','sp.people_code','sp.people_name','sp.people_mobile','sp.people_idCard'])
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
     * @return array
     */
    private static function pageInit()
    {
        $page = request()->param();
        $result = array(0,20);
        /* 如果传递数据不符合规范，就返回默认分页数据 */
        if(!isset($page['page']) || count($page['page']) != 2) {
            return $result;
        }
        $result = array($page['page'][0] * $page['page'][1], $page['page'][1]);
        return $result;
    }
}