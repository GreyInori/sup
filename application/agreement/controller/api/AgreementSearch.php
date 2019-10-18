<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/10
 * Time: 15:45
 */

namespace app\agreement\controller\api;

use think\Controller;
use think\Db;
use \app\agreement\controller\api\AgreementWhere as AgreementWhere;

/**
 * Class AgreementSearch
 * @package app\agreement\controller\api
 */
class AgreementSearch extends Controller
{
    /**
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
        $where = new AgreementWhere();
        $where = $where->getWhereArray($search);
        $where['sia.show_type'] = 1;
        /* 如果有传递手机号的话，就根据该手机号拿到相关的工程列表，否则返回空 */
        $mobile = request()->param();
        if(!isset($mobile['mobile'])) {
            $where['se.engineering_id'] = '';
        }else{
            $whereStr = self::engineerWhere($mobile['mobile']);
            $where['se.engineering_id'] = array('IN',$whereStr);
        }
        /* 执行合同表查询 */
        try{
            $list = Db::table('su_internal_agreement')
                ->alias('sia')
                ->join('su_engineering se','se.engineering_id = sia.engineering_id')
                ->join('su_agreement_type sat','sat.type_id = sia.agreement_type')
                ->field(['sia.agreement_name','se.engineering_id','sia.agreement_time','sia.agreement_id','sat.type_name','se.engineering_name','se.construction_company','sia.quality_station','sia.input_person','sia.agreement_file'])
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
     * 获取手机号相关工程id列表
     * @param $mobile
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function engineerWhere($mobile)
    {
        $list = Db::table('su_engineering_divide')->where('divide_user',$mobile)->field(['engineering_id'])->select();
        $whereStr = '';
        foreach($list as $key => $row) {
            $whereStr .= "{$row['engineering_id']},";
        }
        return rtrim($whereStr,',');
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