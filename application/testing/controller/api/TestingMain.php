<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 0:46
 */

namespace app\testing\controller\api;

use think\Controller;
use think\Db;
use \app\testing\controller\TestingAutoLoad as TestingAutoLoad;
use \app\lib\controller\Picture;

/**
 * Class TestingMain
 * @package app\testing\controller\api
 */
class TestingMain extends Controller
{
    use Picture;
    /**
     * 委托异常添加方法
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function errorUpload($data)
    {
        $group = new TestingAutoLoad();
        $data = $group->toGroup($data);
        /* 检测传递的委托单号是否存在，如果不存在就返回错误信息 */
        $list = Db::table('su_testing_status')
                ->where('trust_id',$data['error']['st.trust_id'])
                ->field(['trust_id'])
                ->select();
        if(empty($list)) {
            return '查无此委托单,请检查传递的委托单id';
        }
        if(!isset($data['error']['ste.error_main'])) {
            return '请传递错误信息';
        }
        $insert = array(
            'error_main' => $data['error']['ste.error_main'],
            'trust_id' => $data['error']['st.trust_id'],
            'error_time' => time(),
        );
        Db::startTrans();
        try {
            $id = Db::table('su_testing_error')->insertGetId($insert);
            Db::table('su_testing_status')->where('trust_id',$data['error']['st.trust_id'])->update(['testing_error'=>1]);
            Db::commit();
            return array($id);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 回复异常方法
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toErrorResponse()
    {
        $data = request()->param();
        if(!isset($data['errorId'])) {
            return '请传递异常id';
        }
        if(!isset($data['response'])) {
            return '请传递异常回复信息';
        }
        /* 检测要回复的异常是否存在，如果不存在就返回错误信息，否则进行回复 */
        $err = Db::table('su_testing_error')->where('error_id',$data['errorId'])->field(['error_id'])->select();
        if(empty($err)) {
            return '查无此异常，请检查传递的异常id';
        }
        try{
            $update = Db::table('su_testing_error')->where('error_id',$data['errorId'])->update(['error_response'=>$data['response'],'error_success'=>1]);
            return array($update);
        }catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 委托单报告上传方法
     * @param $data
     * @return array|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toReportUpload($data)
    {
        $group = new TestingAutoLoad();
        /* 检测传递的委托单信息是否符合规范 */
        $data = $group->toGroup($data);
        $list = Db::table('su_trust')
                ->where('trust_id',$data['report']['st.trust_id'])
                ->field(['trust_id'])
                ->select();
        if(empty($list)) {
            return '查无此委托单，请检查传递的委托单id';
        }
        $list = Db::table('su_report')
                ->where('trust_id',$data['report']['st.trust_id'])
                ->field(['report_number'])
                ->select();
        if(!empty($list)) {
            return '当前委托单报告已经存在，请检查传递的委托单id';
        }
        $reportInsert = array(
            'report_main' => $data['report']['sr.report_main'],
            'report_time' => time(),
            'trust_id' => $data['report']['st.trust_id']
        );
        /* 执行图片上传操作，如果上传失败就返回错误信息，如果成功就根据传值以及当前时间创建图片文件修改数据 */
        $file = self::toImgUp('report','pdf');
        if(!is_array($list)) {
            return $file;
        }
        $reportInsert['report_file'] = $file['pic'];
        /* 委托单状态修改数组创建 */
        $trustUpdate = array(
            'testing_report' => 1,
            'trust_progress' => 4,
            'testing_status' => '已检测',
            'report_upload_time' => time(),
        );
        Db::startTrans();
        try{
            var_dump($reportInsert);exit;
            $update = Db::table('su_report')->insertGetId($reportInsert);
            /* 委托状态修改 */
            Db::table('su_testing_status')
                ->where('trust_id',$data['report']['st.trust_id'])
                ->update($trustUpdate);
            Db::table('su_trust')
                ->where('trust_id',$data['report']['st.trust_id'])
                ->update(['is_report'=>1]);
            Db::commit();
            return array($update);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 根据委托单号获取委托报告数据
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toResponse()
    {
        $data = request()->param();
        if(!isset($data['trust'])) {
            return '请传递委托单号';
        }
        $list = Db::table('su_trust')
            ->alias('st')
            ->join('su_report sr','sr.trust_id = st.trust_id')
            ->join('su_material_type smt','smt.type_id = st.testing_quality')
            ->join('su_engineering se','se.engineering_id = st.engineering_id')
            ->field(['st.input_testing_company','st.trust_id','smt.type_name','st.testing_name','sr.report_number','se.engineering_name','sr.report_time','sr.report_file','sr.report_main'])
            ->where('st.trust_id',$data['trust'])
            ->select();
        return $list;
    }

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new TestingAutoLoad();
        $field = $field::$fieldArr;        // 用于比较转换的数组字段
        /* 如果是索引数组的话就需要对数组内所有数据的字段进行转换，否则就直接对数组内值进行转换 */
        if(!self::is_assoc($list)) {
            foreach($list as $key => $row) {
                $result[$key] = self::toFieldChange($row, $field);
            }
        }else {
            $result = self::toFieldChange($list, $field);
        }
        return $result;
    }

    /**
     * 把数据库字段转换为前端传递的字段返回
     * @param $list
     * @param $check
     * @return array
     */
    private static function toFieldChange($list, $check)
    {
        $checkArr = array();
        foreach ($check as $key => $row) {
            $field = strchr($row,'.');
            $checkArr[$key] = ltrim($field,'.');
        }
        $result = array();
        foreach($list as $key => $row) {
            if(strstr($key,'_time') && is_int($row)) {
                $row = date('Y-m-d H:i:s',$row);
            }elseif($key == 'report_file') {
                $url = request()->domain();
                $row = $url.$row;
            }
            $result[array_search($key, $checkArr)] = $row;
        }
        return $result;
    }

    /**
     * 检测数组是否为索引数组
     * @param $arr
     * @return bool
     */
    private static function is_assoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}