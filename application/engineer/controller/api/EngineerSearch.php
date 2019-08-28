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
        /* 根据预先写好的查询条件获取需要获取到的字段信息 */
        $field = array();
        foreach($whereArr as $whereKey => $whereRow) {
            array_push($field, $whereKey);
        }
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_engineering')
                ->alias('se')
                ->field($field)
                ->where($where)
                ->limit($page[0], $page[1])
                ->select();
        }catch(\Exception $e) {
            return $e->getMessage();
        }
//        $list = self::idToName($list, $check);
        return $list;
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