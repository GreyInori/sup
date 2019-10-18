<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/27
 * Time: 9:30
 */

namespace app\engineer\controller\api;

use think\Controller;
use think\Db;
use \app\engineer\controller\api\ReckonerWhere as ReckonerWhere;

class ReckonerSearch extends Controller
{
    /**
     * 获取指定工程列表方法
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit();
        $where = new ReckonerWhere();
        $where = $where->getWhereArray($search);
        $where['se.show_type'] = 1;
        /* 检测管理员状态，返回相关的查询条件 */
        $whereIn = self::roleCheck($search);
        if($whereIn) {
            $where['se.engineering_id'] = ['IN',$whereIn];
        }
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_engineering_reckoner')
                ->alias('ser')
                ->join('su_engineering se','se.engineering_id = ser.engineering_id')
                ->join('su_people sp','sp.people_id = ser.people_id')
                ->field(['se.engineering_id','se.engineering_name','sp.people_id','sp.people_code','sp.people_name','sp.people_mobile'])
                ->where($where)
                ->limit($page[0], $page[1])
                ->order('se.input_time DESC')
                ->select();
            $count = Db::table('su_engineering_reckoner')
                ->alias('ser')
                ->join('su_engineering se','se.engineering_id = ser.engineering_id')
                ->join('su_people sp','sp.people_id = ser.people_id')
                ->field(['count(se.engineering_id) as page'])
                ->where($where)
                ->order('se.input_time DESC')
                ->select();
        }catch(\Exception $e) {
            return $e->getMessage();
        }
        $list['count'] = $count[0]['page'];
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
        $admin = Db::table('su_admin')->where('user_name', $admin['user_name'])->field(['user_role'])->select();
        if(empty($admin) || $admin[0]['user_role'] == 1) {
            return false;
        }
        /* 如果不是最高管理员的话，就获取当前账号相关的工程列表 */
        $list = Db::table('su_engineering_divide')->where('divide_user',$admin['user_name'])->field(['engineering_id'])->select();
        if(!empty($list)) {
            foreach($list as $key => $row) {
                $result .= "{$row['engineering_id']},";
            }
        }
        return rtrim($result,',');
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