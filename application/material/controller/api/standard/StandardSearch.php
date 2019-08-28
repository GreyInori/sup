<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 17:15
 */

namespace app\material\controller\api\standard;

use think\Controller;
use think\Db;
use \app\material\controller\api\standard\StandardWhere as StandardWhere;

/**
 * Class StandardSearch
 * @package app\material\controller\api\standard
 */
class StandardSearch extends Controller
{
    public static function toList($search)
    {
        /* 初始化，根据传递的数据生成指定的分页信息以及查询条件 */
        $page = self::pageInit($search);
        $where = new StandardWhere();
        $where = $where->getWhereArray($search);
        if(empty($where)) {
            return '请传递正确的查询条件';
        }
        /* 执行企业列表查询 */
        try{
            $list = Db::table('su_testing_standard')
                ->alias('sts')
                ->field(['sts.testing_id','sts.testing_number','sts.testing_company','sts.testing_code','sts.testing_type','sts.testing_from','sts.testing_basis_number','sts.testing_basis','sts.determine_standard_number','sts.determine_standard'])
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