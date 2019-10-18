<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/16
 * Time: 17:39
 */

namespace app\qrcode\controller\api;

use think\Controller;
use think\Db;
use \app\qrcode\controller\api\QrcodeWhere as QrcodeWhere;

/**
 * Class QrcodeSearch
 * @package app\qrcode\controller\api
 */
class QrcodeSearch extends Controller
{
    /**
     * 查询二维码方法
     * @param $search
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function toList($search)
    {
        $page = self::pageInit();
        $where = new QrcodeWhere();
        $where = $where->getWhereArray($search);
        $where['qr_path'] = array('<>','null');
        try{
            $list = Db::table('su_qrcode')
                    ->field(['company_code','work_code','qr_time','qr_code','is_use','qr_path'])
                    ->where($where)
                    ->order('create_time DESC,qr_time DESC')
                    ->limit($page[0],$page[1])
                    ->select();
            $count = Db::table('su_qrcode')
                    ->field(['count(qr_code) as page'])
                    ->where($where)
                    ->select();
            $list['count'] = $count[0]['page'];
            return $list;
        }catch(\Exception $e) {
            return $e->getMessage();
        }
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