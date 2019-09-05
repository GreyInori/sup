<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 9:18
 */

namespace app\engineer\controller\api;

use think\Controller;
use think\Db;

/**
 * Class EngineerWhere
 * @package app\engineer\controller\api
 */
class EngineerWhere extends Controller
{
    /**
     * @var array
     * 把传递过来id字段转换成后台数据库对应的字段
     */
    public $where = array(
        'contract_code' => ['se.contract_code','LIKE','code%'],
        'engineering_name' => ['se.engineering_name','LIKE','%code%'],
        'engineering_area' => ['se.engineering_area','LIKE','%code%'],
        'engineering_type' => ['se.engineering_type','=','type'],
        'build_company' => ['se.build_company','LIKE','%code%'],
        'construction_company' => ['se.construction_company','LIKE','%code%'],
        'supervise_company' => ['se.supervise_company','LIKE','%code%'],
        'witness_people' => ['se.witness_people','LIKE','%code%'],
        'makeup_people' => ['se.makeup_people','LIKE','%code%'],
        'sampling_people' => ['se.sampling_people','LIKE','%code%'],
        'input_person' => ['se.input_person','LIKE','%code%'],
        'input_type' => ['se.input_type','=','input']
    );

    /**
     * 根据传递的参数返回指定的查询条件
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getWhereArray($where = array())
    {
        return $this->creatQueryCode($where);
    }

    /**
     * 生成查询条件方法
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function creatQueryCode($where = array())
    {
        $result = array();
        $resultWhere = array();
        /* 循环传递过来的查询条件，比对已经有了的字段，进行查询匹配 */
        foreach($where as $key => $row) {
            $whereCode = $this->whereChange($key, $row);
            if($whereCode != 'fail'){
                array_push($result, $whereCode);
            }
        }
        /* 由于查询数组需要的查询条件是键值对数组，因此需要把获取到的索引数组转换为 查询字段 => 查询条件 键值对格式 */
        foreach($result as $row) {
            foreach($row as $valueKey => $valueRow) {
                $resultWhere[$valueKey] = $valueRow;
            }
        }
        if(isset($where['company_id'])) {
            $resultWhere['engineering_id'] = self::fetchDivideEngineer($where['company_id']);
        }
        return $resultWhere;
    }

    /**
     * 生成详细的查询条件方法
     * @param $key
     * @param $row
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function whereChange($key, $row)
    {
        $where = $this->where;
        $conditions = 'fail';
        /* 根据事先固定的字段信息，判断当前查询请求字段是否存在 */
        if(isset($where[$key])) {
            /* 如果查询结果是单个值的数据的话，就进行制定查询条件匹配，由于事先定义好了 LIKE 查询的条件了，就根据制定字段的条件来生成需要的查询条件 */
            if(!is_array($row)) {
                $conditions = array($where[$key][0] => array($where[$key][1]));
                /* 如果查询条件的人员或者公司的话需要把通过文字信息获取到id字符串，用于查询操作 */
                if(isset($where[$key][2])) {
                    switch ($where[$key][2]) {
                        case 'company':
                            $row = self::createCompany($row);
                            break;
                        case 'people':
                            $row = self::createPeople($row);
                            break;
                        case 'type':
                            $row = self::typeGet($row);
                            break;
                        case 'input':
                            $row = self::inputGet($row);
                            break;
                        default:
                            $row = str_replace('code', $row, $where[$key][2]);
                    }
                }
                array_push($conditions[$where[$key][0]], $row);
            }
        }
        return $conditions;
    }

    /**
     * 根据查询条件获取符合条件的企业id
     * @param $name
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function createCompany($name)
    {
        $result = "";
        /* 根据文字查询条件获取符合条件的企业列表用于后面的操作 */
        $companyList = Db::table('su_company')
                            ->where('company_full_name','LIKE',"%{$name}%")
                            ->field(['company_id'])
                            ->select();
        if(empty($companyList)) {
            return $result;
        }
        /* 把查询出来的企业列表转换成where IN 需要的字符串格式 */
        foreach($companyList as $companyKey => $companyRow) {
            $result .= "{$companyRow['company_id']},";
        }
        $result = rtrim($result,',');
        return $result;
    }

    /**
     * 根据查询条件获取符合条件的人员id
     * @param $name
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function createPeople($name)
    {
        $result = "";
        /* 根据文字查询条件获取符合条件的人员列表用于后面的操作 */
        $companyList = Db::table('su_people')
                            ->where('people_name','LIKE',"%{$name}%")
                            ->field(['people_id'])
                            ->select();
        if(empty($companyList)) {
            return $result;
        }
        /* 把查询出来的人员列表转换成where IN 需要的字符串格式 */
        foreach($companyList as $companyKey => $companyRow) {
            $result .= "{$companyRow['people_id']},";
        }
        $result = rtrim($result,',');
        return $result;
    }

    /**
     * 根据建筑类型获取指定的建筑id
     * @param $area
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function typeGet($area)
    {
        $list = Db::table('su_engineering_type')
                ->where('type_name','LIKE',"%{$area}%")
                ->field(['type_id'])
                ->select();
        if(empty($list)){
            return '';
        }
        return $list[0]['type_id'];
    }

    /**
     * 根据录入类型获取指定的录入类型id
     * @param $area
     * @return int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function inputGet($area)
    {
        $list = Db::table('su_input_type')
            ->where('type_name','LIKE',"%{$area}%")
            ->field(['type_id'])
            ->select();
        if(empty($list)){
            return 1;
        }
        return $list[0]['type_id'];
    }

    /**
     * 根据企业id获取相关的所有工程的id
     * @param $company
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function fetchDivideEngineer($company)
    {
        $list = Db::table('su_engineering_divide')
                    ->where('member_id',$company)
                    ->field(['engineering_id'])
                    ->group('engineering_id')
                    ->select();
        if(empty($list)) {
            return array('=',0);
        }
        $whereStr = "";
        foreach($list as $key => $row) {
            $whereStr .= "{$row['engineering_id']},";
        }
        $whereStr = rtrim($whereStr,',');
        return array('IN',$whereStr);
    }
}