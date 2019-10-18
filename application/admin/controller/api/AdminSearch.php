<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/11
 * Time: 15:42
 */

namespace app\admin\controller\api;

use think\Controller;
use think\Db;
use \app\admin\controller\api\AdminWhere as AdminWhere;

/**
 * Class AdminSearch
 * @package app\admin\controller\api
 */
class AdminSearch extends Controller
{
    /**
     * 获取委托单列表方法
     * @param $search
     * @return string
     */
    public static function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit();
        $where = new AdminWhere();
        $where = $where->getWhereArray($search);
        $where['sa.show_type'] = 1;
        if(isset($search['show'])){
            $where['sa.show_type'] = $search['show'];
        }
        if(empty($where)) {
            return '请传递正确的查询条件';
        }
        /* 如果不是管理员登录的话就只获取当前用户添加的用户列表 */
        $mobile = request()->param();
        if(isset($mobile['createUser'])) {
            $where['create_user|user_name'] = $mobile['createUser'];
        }

        /* 执行企业列表查询 */
        try{
            /* 判读是进行列表查询还是下拉框获取，下拉框获取的话不需要分页 */
            $data = request()->param();
            if(isset($data['page'])) {
                $list = Db::table('su_admin')
                    ->alias('sa')
                    ->join('su_company sc','sc.company_id = sa.user_company','left')
                    ->join('su_role sr','sr.role_id = sa.user_role')
                    ->field(['sa.user_id','sa.user_name','sa.user_company','sa.user_role','sc.company_full_name','sr.role_name'])
                    ->where($where)
                    ->limit($page[0], $page[1])
                    ->select();
            }else{
                $list = Db::table('su_admin')
                    ->alias('sa')
                    ->join('su_company sc','sc.company_id = sa.user_company','left')
                    ->join('su_role sr','sr.role_id = sa.user_role')
                    ->field(['sa.user_id','sa.user_name','sa.user_company','sa.user_role','sc.company_full_name','sr.role_name'])
                    ->where($where)
                    ->select();
            }
            /* 获取数据总量，用于计算分页总页数 */
            $count = Db::table('su_admin')
                ->alias('sa')
                ->join('su_company sc','sc.company_id = sa.user_company','left')
                ->join('su_role sr','sr.role_id = sa.user_role')
                ->field(['count(sa.user_id) as page'])
                ->where($where)
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