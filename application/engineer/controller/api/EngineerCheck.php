<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 11:21
 */

namespace app\engineer\controller\api;

use think\Controller;
use think\Db;

class EngineerCheck extends Controller
{
    /**
     * 人员是否存在检测方法
     * @param $data
     * @return string
     * @throws \think\exception\DbException
     */
    public static function peopleCheck($data)
    {
        /* 根据传入的人员手机号生成录入时间录入人等数据 */
        $nickname = Db::table('su_admin')->where('user_name',$data['engineer']['user_name'])->field(['user_nickname'])->select();
        if(!empty($nickname)) {
            $data['input_person'] = $nickname[0]['user_nickname'];
        }
        $data['input_time'] = time();
        return $data;
    }

    /**
     * 检测人员以及企业列表数据是否存在以及对的上传递的数据数量
     * @param $list
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function listCheck($list)
    {
        /* 进行人员列表检测 */
        $peopleListCheck = self::peopleListCheck($list['peopleList']);
        if(!$peopleListCheck !== 1) {
            return $peopleListCheck;
        }
        /* 进行企业列表检测 */
        $companyListCheck = self::companyListCheck($list['companyList']);
        if(!$companyListCheck !== 1) {
            return $companyListCheck;
        }
        return 1;
    }

    /**
     * 检测指定的人员列表是否存在
     * @param $peopleList
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function peopleListCheck($peopleList)
    {
        $peopleStr = '';
        $peopleArr = array();
        /* 由于传递过来的人员id可能会有重复的情况，因此检测时需要过滤掉重复的值 */
        foreach($peopleList as $peopleKey => $peopleRow) {
            if(!in_array($peopleRow, $peopleArr)){
                $peopleStr .= "{$peopleRow},";
                array_push($peopleArr, $peopleRow);
            }
        }
        $peopleStr = rtrim($peopleStr, ',');

        $people = Db::table('su_people')
            ->where('people_id','IN',$peopleStr)
            ->field(['people_id'])
            ->select();

        if(count($peopleArr) != count($people)) {
            return '传递的人员列表内有不存在的人员id，请检查传递的人员id';
        }
        return 1;
    }

    /**
     * 检测企业列表是否存在
     * @param $companyList
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function companyListCheck($companyList)
    {
        $companyArr = array();
        /* 由于传递过来的人员id可能会有重复的情况，因此检测时需要过滤掉重复的值 */
        $companyStr = array();
        foreach($companyList as $companyKey => $companyRow) {
            if(!in_array($companyRow, $companyArr)) {
                $companyStr .= "{$companyRow},";
                array_push($peopleArr, $companyArr);
            }
        }
        $companyStr = rtrim($companyStr, ',');
        $company = Db::table('su_company')
            ->where('company_id','IN',$companyStr)
            ->field(['company_id'])
            ->select();
        if(count($companyArr) != count($company)) {
            return '传递的公司列表内有不存在的公司，请检查的传递的公司id';
        }
        return 1;
    }
}