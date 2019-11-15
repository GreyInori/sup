<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 0:46
 */

namespace app\testing\controller;

use app\testing\controller\api\ErrorSearch as ErrorSearch;
use app\testing\controller\api\ReportSearch;
use think\Controller;
use \app\testing\controller\api\TestingSearch as TestingSearch;
use \app\testing\controller\api\TestingMain as TestingMain;
use \app\testing\controller\TestingAutoLoad as FieldCheck;
use \app\api\controller\Send;

/**
 * Class Testing
 * @package app\testing\controller
 */
class Testing extends Controller
{
    use Send;
    // +----------------------------------------------------------------------
    // | 检测进度相关
    // +----------------------------------------------------------------------
    /**
     * 获取检测进度列表
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTestingList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = TestingSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $numData = request()->param();
        $num = 20;
        if(isset($numData['page'])) {
            $num = $numData['page'][1];
        }
        $page = ceil($list['count']/$num);
        unset($list['count']);
        $change = new TestingMain();
        $list = $change::fieldChange($list);
        /* 把报告内的签名列表转换为索引数组 */
        foreach ($list as $key => $row) {
            if(isset($row['reportSign']) && $row['reportSign'] != '' && $row['reportSign'] != null) {
                $list[$key]['reportSign'] = explode(',',$row['reportSign']);
                /* 如果非空就给签名路径添加网站域名 */
                $url = request()->domain();
                foreach ($list[$key]['reportSign'] as $signKey => $signValue) {
                    $list[$key]['reportSign'][$signKey] = $url . $signValue;
                }
            }
        }
        return self::returnMsg(200,$page,$list);
    }

    /**
     * 上传委托单异常方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postTestingError()
    {
        $data = FieldCheck::checkData('postErr');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = TestingMain::errorUpload($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 回复异常方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postTestingErrorResponse()
    {
        $data = FieldCheck::checkData('resErr');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $data = TestingMain::toErrorResponse();
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        return self::returnMsg(200,'success',$data);
    }

    /**
     * 获取异常委托单列表
     * @return false|string
     */
    public function getTestingError()
    {
        /* 检查传递的参数是否符合规范 */
        $data = FieldCheck::checkData('errList',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        /* 获取异常列表数据，如果抛出异常的话就返回错误信息 */
        $list = ErrorSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 根据查询出来的数据总条数以及每页显示的数据量，计算总页数 */
        $numData = request()->param();
        $num = 20;
        if(isset($numData['page'])) {
            $num = $numData['page'][1];
        }
        $page = ceil($list['count']/$num);
        unset($list['count']);
        /* 进行数据库对应前端字段转换并返回 */
        $change = new TestingMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,$page,$list);
    }

    /**
     * 获取未上传报告的委托单列表
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getReportUploadList()
    {
        /* 检查传递参数是否符合规范 */
        $data = FieldCheck::checkData('list',['page']);
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = TestingSearch::toList($data,0);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 根据查询出来的数据总条数以及每页显示的数据量，计算总页数 */
        $numData = request()->param();
        $num = 20;
        if(isset($numData['page'])) {
            $num = $numData['page'][1];
        }
        $page = ceil($list['count']/$num);
        unset($list['count']);
        $fieldArr = array('engineerName','constructionCompany','materialName','reportMain','reportNumber','reportTime','testingType','trust');
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $change = new TestingMain();
        $list = $change::fieldChange($list);
        /* 把查询结果转换为报告页面需要的数据字段 */
        foreach($list as $key => $row) {
            foreach($row as $mainKey => $mainRow) {
                if($mainKey == 'testingName') {
                    $list[$key]['materialName'] = $mainRow;
                    unset($list[$key][$mainKey]);
                }elseif(!in_array($mainKey,$fieldArr) || is_int($mainKey)) {
                    unset($list[$key][$mainKey]);
                }elseif($mainRow == null) {
                    $list[$key][$mainKey] = ' ';
                }
            }
        }
        return self::returnMsg(200,$page,$list);
    }

    /**
     * 报告上传方法
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postReportUpload()
    {
        $data = FieldCheck::checkData('reportUpload');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = TestingMain::toReportUpload($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 获取测试报告列表
     * @return false|string
     */
    public function getReportList()
    {
        $data = FieldCheck::checkData('reportList');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $list = ReportSearch::toList($data);
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 根据查询出来的数据总条数以及每页显示的数据量，计算总页数 */
        $numData = request()->param();
        $num = 20;
        if(isset($numData['page'])) {
            $num = $numData['page'][1];
        }
        $page = ceil($list['count']/$num);
        unset($list['count']);
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
        $change = new TestingMain();
        $list = $change::fieldChange($list);
        return self::returnMsg(200,$page,$list);
    }

    /**
     * 根据委托单号获取指定的委托单报告
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getReport()
    {
        $data = FieldCheck::checkData('reportMain');
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $main = new TestingMain();
        $list = $main::toResponse();
        if(!is_array($list)) {
            return self::returnMsg(500,'fail',$list);
        }
        /* 把查询结果的字段转换为前端传递过来的字段数据 */
//        var_dump($list);exit;
        $list = $main::fieldChange($list);
        return self::returnMsg(200,'success',$list);
    }

    /**
     * 委托单报告修改
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function postReportEdit()
    {
        $data = FieldCheck::checkData('reportEdit');
        if(!isset($data['sr.report_number'])) {
            return self::returnMsg(500,'fail','请传递需要修改的报告编号');
        }
        if(!is_array($data)) {
            return self::returnMsg(500,'fail',$data);
        }
        $edit = TestingMain::toReportEdit($data);
        if(!is_array($edit)) {
            return self::returnMsg(500,'fail',$edit);
        }
        return self::returnMsg(200,'success',$edit);
    }
}