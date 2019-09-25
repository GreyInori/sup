<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/25
 * Time: 9:06
 */

namespace app\count\controller\api;

use think\Controller;
use think\Db;

/**
 * Class CountMain
 * @package app\count\controller\api
 */
class CountMain extends Controller
{
    /**
     * 工程统计
     * @return array
     * @throws \think\Exception
     */
    public static function toEngineerCount()
    {
        $time = self::creatStartTime();
        $result = array(
            'year' => Db::table('su_engineering')->where(['input_time'=>['>=',"{$time['year']['start']} AND <= {$time['year']['end']}"]])->group('engineering_area')->field(['engineering_area as area','count(engineering_id) as num'])->select(),
            'mouth' => Db::table('su_engineering')->where(['input_time'=>['>=',"{$time['mouth']['start']} AND <= {$time['mouth']['end']}"]])->group('engineering_area')->field(['engineering_area as area','count(engineering_id) as num'])->select(),
            'week' => Db::table('su_engineering')->where(['input_time'=>['>=',"{$time['week']['start']} AND <= {$time['week']['end']}"]])->group('engineering_area')->field(['engineering_area as area','count(engineering_id) as num'])->select(),
            'day' => Db::table('su_engineering')->where(['input_time'=>['>=',"{$time['day']['start']} AND <= {$time['day']['end']}"]])->group('engineering_area')->field(['engineering_area as area','count(engineering_id) as num'])->select(),
            'sum' => Db::table('su_engineering')->group('engineering_area')->field(['engineering_area as area','count(engineering_id) as num'])->select(),
        );
        return $result;
    }

    /**
     * 获取委托单统计数据
     * @return array
     * @throws \think\Exception
     */
    public static function toTrustCount()
    {
        $time = self::creatStartTime();
        $result = array(
            'year' => Db::table('su_trust')->alias('st')->join('su_engineering se','se.engineering_id = st.engineering_Id')->where(['st.input_time'=>['>=',"{$time['year']['start']} AND <= {$time['year']['end']}"]])->group('se.engineering_area')->field(['se.engineering_area as area','count(st.trust_id) as num'])->select(),
            'mouth' => Db::table('su_trust')->alias('st')->join('su_engineering se','se.engineering_id = st.engineering_Id')->where(['st.input_time'=>['>=',"{$time['mouth']['start']} AND <= {$time['mouth']['end']}"]])->group('se.engineering_area')->field(['se.engineering_area as area','count(st.trust_id) as num'])->select(),
            'week' => Db::table('su_trust')->alias('st')->join('su_engineering se','se.engineering_id = st.engineering_Id')->where(['st.input_time'=>['>=',"{$time['week']['start']} AND <= {$time['week']['end']}"]])->group('se.engineering_area')->field(['se.engineering_area as area','count(st.trust_id) as num'])->select(),
            'day' => Db::table('su_trust')->alias('st')->join('su_engineering se','se.engineering_id = st.engineering_Id')->where(['st.input_time'=>['>=',"{$time['day']['start']} AND <= {$time['day']['end']}"]])->group('se.engineering_area')->field(['se.engineering_area as area','count(st.trust_id) as num'])->select(),
            'sum' => Db::table('su_trust')->alias('st')->join('su_engineering se','se.engineering_id = st.engineering_Id')->group('se.engineering_area')->field(['se.engineering_area as area','count(st.trust_id) as num'])->select(),
        );
        return $result;
    }

    /**
     * 委托单异常统计数据
     * @return array
     * @throws \think\Exception
     */
    public static function toErrorCount()
    {
        $time = self::creatStartTime();
        $result = array(
            'year' => Db::table('su_testing_error')->alias('ste')->join('su_trust st','ste.trust_id = st.trust_id')->join('su_engineering se','se.engineering_id = st.engineering_Id')->where(['ste.error_time'=>['>=',"{$time['year']['start']} AND <= {$time['year']['end']}"]])->group('se.engineering_area')->field(['engineering_area as area','count(ste.error_id) as num'])->select(),
            'mouth' => Db::table('su_testing_error')->alias('ste')->join('su_trust st','ste.trust_id = st.trust_id')->join('su_engineering se','se.engineering_id = st.engineering_Id')->where(['ste.error_time'=>['>=',"{$time['mouth']['start']} AND <= {$time['mouth']['end']}"]])->group('se.engineering_area')->field(['engineering_area as area','count(ste.error_id) as num'])->select(),
            'week' => Db::table('su_testing_error')->alias('ste')->join('su_trust st','ste.trust_id = st.trust_id')->join('su_engineering se','se.engineering_id = st.engineering_Id')->where(['ste.error_time'=>['>=',"{$time['week']['start']} AND <= {$time['week']['end']}"]])->group('se.engineering_area')->field(['engineering_area as area','count(ste.error_id) as num'])->select(),
            'day' => Db::table('su_testing_error')->alias('ste')->join('su_trust st','ste.trust_id = st.trust_id')->join('su_engineering se','se.engineering_id = st.engineering_Id')->where(['ste.error_time'=>['>=',"{$time['day']['start']} AND <= {$time['day']['end']}"]])->group('se.engineering_area')->field(['engineering_area as area','count(ste.error_id) as num'])->select(),
            'sum' => Db::table('su_testing_error')->alias('ste')->join('su_trust st','ste.trust_id = st.trust_id')->join('su_engineering se','se.engineering_id = st.engineering_Id')->group('se.engineering_area')->field(['engineering_area as area','count(ste.error_id) as num'])->select(),
        );
        return $result;
    }

    /**
     * 企业统计数据
     * @return array
     * @throws \think\Exception
     */
    public static function toCompanyCount()
    {
        $time = self::creatStartTime();
        $result = array(
            'year' => Db::table('su_company')->where(['company_register_time'=>['>=',"{$time['year']['start']} AND <= {$time['year']['end']}"]])->count(),
            'mouth' => Db::table('su_company')->where(['company_register_time'=>['>=',"{$time['mouth']['start']} AND <= {$time['mouth']['end']}"]])->count(),
            'week' => Db::table('su_company')->where(['company_register_time'=>['>=',"{$time['week']['start']} AND <= {$time['week']['end']}"]])->count(),
            'day' => Db::table('su_company')->where(['company_register_time'=>['>=',"{$time['day']['start']} AND <= {$time['day']['end']}"]])->count(),
            'sum' => Db::table('su_company')->count(),
        );
        return $result;
    }

    /**
     * @return array
     */
    private static function creatStartTime()
    {

        $mouth = date('m');
        $year = date('Y');
        $week = date('N');
        $end = time();
        /* 获取需要查询的时间范围数据 */
        $result = array(
            'week' => [                                                    // 本月查询数据
                'start' => strtotime("-{$week} day"),
                'end' => $end,
            ],
            'year' => [                                                    // 本周查询数据
                'start' => strtotime("{$year}-1-1"),
                'end' => $end,
            ],
            'mouth' => [                                                             // 今年查询数据
                'start' => strtotime("{$year}-{$mouth}-1"),
                'end' => $end,
            ],
            'day' => [                                                                          // 当天查询数据
                'start' => strtotime(date('Y-m-d 00:00:00',time())),
                'end' => $end,
            ],
        );
        return $result;
    }
}