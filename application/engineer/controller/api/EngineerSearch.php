<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 9:19
 */

namespace app\engineer\controller\api;

//use app\engineer\controller\EngineerAutoLoad;
use think\Controller;
use think\Db;
use \app\engineer\controller\api\EngineerWhere as EngineerWhere;

/**
 * Class EngineerSearch
 * @package app\engineer\controller\api
 */
class EngineerSearch extends Controller
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
//        $field = new EngineerAutoLoad();
//        $check = $field::$fieldArr;
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new EngineerWhere();
        $whereArr = $where->where;
        $where = $where->getWhereArray($search);
        $where['se.show_type'] = 1;
        /* 检测管理员状态，返回相关的查询条件 */
        $whereIn = self::roleCheck($search);
        if($whereIn) {
            $where['se.engineering_id'] = ['IN',$whereIn];
        }
        /* 根据预先写好的查询条件获取需要获取到的字段信息 */
        $field = array();
        foreach($whereArr as $whereKey => $whereRow) {
            array_push($field, $whereKey);
        }
        array_push($field,'set.type_name');
        array_push($field,'se.engineering_id');
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_engineering')
                ->alias('se')
                ->join('su_engineering_type set','se.engineering_type = set.type_id','left')
                ->field($field)
                ->where($where)
                ->limit($page[0], $page[1])
                ->order('se.input_time DESC')
                ->select();
        }catch(\Exception $e) {
            return $e->getMessage();
        }
//        $list = self::idToName($list, $check);
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
     * 把结果内的id转换成文字信息方法
     * @param $list
     * @param $check
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function idToName($list, $check)
    {
        $result = array();
        $whereIn = "";
        $checkArr = array();               // 用于保存匹配结果的数组
        /* 把结果内的企业以及人员的id提取出来，用作后面的匹配操作 */
        foreach($list as $key => $row) {
            foreach($row as $rowKey => $rowValue){
                if(strchr($rowKey,'_company') || strchr($rowKey,'_people')) {
                    $whereIn .= "{$rowValue},";
                }
            }
        }
        $whereIn = rtrim($whereIn,',');
        /* 根据查询出来的结果，获取到所有查出来的企业和人员名，由于id是唯一的，因此只需要循环塞进同一个数组内匹配就好了 */
        $company = Db::table('su_company')->where('company_id','IN',$whereIn)->field(['company_id','company_full_name'])->select();
        $people = Db::table('su_people')->where('people_id','IN',$whereIn)->field(['people_id','people_name'])->select();
        foreach($company as $k => $v) {
            $checkArr[$v['company_id']] = $v['company_full_name'];
        }
        foreach($people as $k => $v) {
            $checkArr[$v['people_id']] = $v['people_name'];
        }
        /* 循环查询结果，把里面的值替换成文字说明 */
        foreach($list as $key => $row) {
            foreach($row as $rowKey => $rowValue){
                if(strchr($rowKey,'_company') || strchr($rowKey,'_people') && isset($checkArr[$rowValue])) {
                    $list[$key][$rowKey] = $checkArr[$rowValue];
                }
                /* 把数据库字段转换成前端传递过来的字段 */
                $changeKey = array_search($rowKey, $check);
                $result[$key][$changeKey] = $list[$key][$rowKey];
            }
        }
        return $result;
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