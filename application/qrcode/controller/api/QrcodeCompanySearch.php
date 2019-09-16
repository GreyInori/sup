<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/16
 * Time: 10:12
 */

namespace app\qrcode\controller\api;

use think\Controller;
use think\Db;
use \app\qrcode\controller\api\QrcodeCompanyWhere as QrcodeCompanyWhere;

/**
 * Class QrcodeCompanySearch
 * @package app\qrcode\controller\api
 */
class QrcodeCompanySearch extends Controller
{
    /**
     * 查询企业列表方法
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function toList($search)
    {
        $page = self::pageInit($search);
        $where = new QrcodeCompanyWhere();
        $where = $where->getWhereArray($search);
        $where['show_type'] = 1;
        try{
            $list = Db::table('su_qrcode_company')
                    ->field(['company_id','company_name','company_linkman','company_mobile','company_code'])
                    ->where($where)
                    ->limit($page[0],$page[1])
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