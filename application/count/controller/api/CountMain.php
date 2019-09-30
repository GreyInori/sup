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
     * 获取一周异常统计数据方法
     * @return array|mixed
     */
    public static function toWeekError()
{
    /* 获取并且转换本周异常数据，其中0为未处理，1为已处理 */
    $list = array(
        self::fetchWeekErr(0),self::fetchWeekErr(1)
    );
    $weekArray = array(
        self::changeWeekErr($list[0]),self::changeWeekErr($list[1])
    );
    $result = array(
        'x_val' => array(array(),array()),
        'y_val' => array(array(),array()),
        'type_val' => ['异常结果','已处理'],
    );
    /* 循环根据查询转换出来的异常数据结果，插入返回值中,并且把键值为0的星期一排到最后 */
    $result = self::makeErrResult($weekArray,0,$result);
    $result = self::makeErrResult($weekArray,1,$result);
    return $result;
}

    public static function toMoonError()
    {
        /* 获取并且转换本周异常数据，其中0为未处理，1为已处理 */
        $list = array(
            self::fetchMoonErr(0),self::fetchMoonErr(1)
        );
        $result = array(
            'x_val' => array(array(),array()),
            'y_val' => array(array(),array()),
            'type_val' => ['异常结果','已处理'],
        );
        $result = self::makeErrMoonResult($list,0,$result);
        $result = self::makeErrMoonResult($list,1,$result);
        /* 循环根据查询转换出来的异常数据结果，插入返回值中,并且把键值为0的星期一排到最后 */
        return $result;
    }

    private static function makeErrMoonResult($list, $token, $result)
    {
        $start = date('z',strtotime('-29 day'));
        $end = date('z');
        $errorList = array(array(),array());
        $errorDay = array(array(),array());
        foreach($list[$token] as $key => $row) {
            array_push($errorList[$token],$row['day']);
            $errorDay[$token][$row['day']] = $row['num'];
        }
        $num = 1;
        for($st = $start; $st <= $end; $st++) {
            array_push($result['x_val'][$token],$num);
            $num++;
            if(!in_array($st,$errorList[$token])) {
                $errorDay[$token][$st] = 0;
            }
        }
        ksort($errorDay[$token]);
        foreach($errorDay[$token] as $key => $row) {
            array_push($result['y_val'][$token],$row);
        }
        return $result;
    }

    /**
     * 把异常数据结果转换成前端需要的格式
     * @param $weekList
     * @param $token
     * @param $result
     * @return mixed
     */
    private static function makeErrResult($weekList, $token, $result)
    {
        /* 根据$token 来判断要插入的是已处理异常数据还是未处理异常数据 */
        foreach($weekList[$token] as $key => $row) {
            if($key != 0) {
                array_push($result['x_val'][$token],$row);
                array_push($result['y_val'][$token],self::weekToChs($key));
            }
        }
        array_push($result['x_val'][$token],$weekList[$token][0]);
        array_push($result['y_val'][$token],'星期日');
        return $result;
    }

    /**
     * 把数据转换成星期 => 数据量的格式，并把一周数据不全，没有的则为0
     * @param $list
     * @return array
     */
    private static function changeWeekErr($list)
    {
        $weekArray = array();
        /* 把查询出来的数据转换为星期 => 数据量的格式 */
        foreach($list as $key => $row) {
            $weekArray[$row['week']] = $row['num'];
        }

        for($i = 0; $i <= 6; $i++) {
            if(!isset($weekArray[$i])) {
                $weekArray[$i] = 0;
            }
        }
        ksort($weekArray);

        return $weekArray;
    }

    private static function fetchMoonErr($token = 0)
    {
        $time = strtotime('-29 day');
        if($token == 1) {
            $sql = "SELECT count(error_id) num,FROM_UNIXTIME(error_time,'%j') as day FROM su_testing_error WHERE error_time >= {$time} AND error_success = 1 GROUP BY FROM_UNIXTIME(error_time,'%j')";
        }else {
            $sql = "SELECT count(error_id) num,FROM_UNIXTIME(error_time,'%j') as day FROM su_testing_error WHERE error_time >= {$time} AND error_success = 0 GROUP BY FROM_UNIXTIME(error_time,'%j')";
        }
        $list = Db::query($sql);
        return $list;
    }

    /**
     * 获取指定范围异常数据方法
     * @param int $token
     * @return mixed
     */
    private static function fetchWeekErr($token = 0)
    {
        /* 生成查询范围时间 */
        $time = time();
        $year = date('Y',$time);
        $mon = date('m',$time);
        $week = date('W',$time);
        /* 判断是要查询已处理还是未处理 */
        if($token == 1){
            $sql = "SELECT count(error_id) num,FROM_UNIXTIME(error_time,'%w') as week FROM su_testing_error WHERE FROM_UNIXTIME(error_time,'%Y') = {$year} AND FROM_UNIXTIME(error_time,'%c') = {$mon} AND FROM_UNIXTIME(error_time,'%u') = {$week} AND error_success = 1 GROUP BY FROM_UNIXTIME(error_time,'%j')";

        }else{
            $sql = "SELECT count(error_id) num,FROM_UNIXTIME(error_time,'%w') as week FROM su_testing_error WHERE FROM_UNIXTIME(error_time,'%Y') = {$year} AND FROM_UNIXTIME(error_time,'%c') = {$mon} AND FROM_UNIXTIME(error_time,'%u') = {$week} GROUP BY FROM_UNIXTIME(error_time,'%j')";
        }
        $list = Db::query($sql);
        return $list;
    }

    /**
     * 数字星期转换成汉字
     * @param $week
     * @return string
     */
    private static function weekToChs($week)
    {
        $result = '';
        switch($week) {
            case '0':
                $result =  '星期日';
                break;
            case '1':
                $result =  '星期一';
                break;
            case '2':
                $result =  '星期二';
                break;
            case '3':
                $result =  '星期三';
                break;
            case '4':
                $result =  '星期四';
                break;
            case '5':
                $result =  '星期五';
                break;
            case '6':
                $result =  '星期六';
                break;
        }
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