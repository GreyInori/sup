<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 9:11
 */

namespace app\engineer\controller;

use think\Controller;

/**
 * Class EngineerAutoLoad
 * @package app\engineer\controller
 */
class EngineerAutoLoad extends Controller
{
    /**
     * @var array
     * 把传递过来的字段转换成后台数据库对应的字段
     */
    public static $fieldArr = array(
        'people' => 'people_id',
        'company' => 'company_id',
        'code' => 'contract_code',
        'engineer' => 'engineering_id',
        'name' => 'engineering_name',
        'type' => 'engineering_type',
        'from' => 'QA_from',
        'level' => 'QA_level',
        'area' => 'engineering_area',
        'foundations' => 'engineering_foundations',
        'site' => 'site_area',
        'underground' => 'underground_area',
        'CCAD' => 'CCAD_area',
        'address' => 'engineering_address',
        'build' => 'build_company',
        'supervise' => 'supervise_company',
        'construction' => 'construction_company',
        'survey' => 'survey_company',
        'design' => 'design_company',
        'witness' => 'witness_people',
        'makeup' => 'makeup_people',
        'sampling' => 'sampling_people',
        'person' => 'input_person',
        'input' => 'input_type',
        'typeName' => 'type_name',
        'member' => 'member_id',
        'divideUser' => 'divide_user',
        'divideName' => 'divide_name',
        'dividePass' => 'divide_passwd',
        'divide' => 'divide_id',
        'control' => 'control_id',
        'controlChs' => 'control_chs',
        'controlParent' => 'control_pid',
        'controlUrl' => 'control_url',
        'controlIcon' => 'control_icon',
        'user' => 'user_id',
        'divideId' => 'divide_index',
        'companyName' => 'company_full_name',
        'mobile' => 'user_name',
        'verify' => 'engineering_verify',
        'peopleName' => 'people_name',
        'peopleCode' => 'people_code',
        'peopleMobile' => 'people_mobile',
        'testing' => 'testing_company',
        'nickName' => 'user_nickname',
        'reckoner' => 'engineering_reckoner',
    );

    /**
     * @var array
     * 给指定字段数据根据数据表区别分组
     */
    public static $fieldGroup = array(
        'main' => array('QA_from','QA_level','site_area','underground_area','CCAD_area','engineering_address'),
        'engineer' => array('user_nickname','company_full_name','user_name','user_id','company_id','engineering_id','input_type','input_person','contract_code','engineering_name','engineering_type','engineering_area','engineering_foundations','build_company','supervise_company','construction_company','survey_company','design_company','witness_people','makeup_people','sampling_people'),
        'engineerMain' => array('engineering_id','input_type','input_person','contract_code','engineering_name','engineering_type','engineering_area','engineering_foundations','build_company','supervise_company','construction_company','survey_company','design_company','witness_people','makeup_people','sampling_people'),
        'people' => array('people_id'),
        'company' => array('company_id'),
        'companyList' => array('build_company','supervise_company','construction_company','survey_company','design_company'),
        'peopleList' => array('witness_people','makeup_people','sampling_people'),
    );

    /**
     * 根据预定义的分组数组，传递过来的数组进行分组
     * @param array $data
     * @return array|mixed
     */
    public function toGroup($data = array())
    {
        $result = array();
        /* 循环判断传递过来的数据字段是否在预定义的几个字段数组内，如果在就进行分组 */
        foreach($data  as $dataKey => $dataRow){
            foreach(self::$fieldGroup as $fieldKey => $fieldRow){
                $result = $this->inCheck($dataKey, $dataRow, $fieldKey, $fieldRow, $result);
            }
        }
        return $result;
    }

    /**
     * 判断指定字段是否在字段分类数组内，如果有，就进行分类，返回分类后的数组
     * @param $dataKey
     * @param $dataRow
     * @param $fieldKey
     * @param $fieldRow
     * @param $data
     * @return mixed
     */
    private function inCheck($dataKey, $dataRow, $fieldKey, $fieldRow, $data)
    {
        /* 判断字段是否在指定字段分组内，如果有的话就进行分组 */
        if(in_array($dataKey, $fieldRow)){
            if(!isset($data[$fieldKey])){
                $data[$fieldKey] = array();
            }
            /* 把指定的值分组到指定的分组 */
            $data[$fieldKey][$dataKey] = $dataRow;
        }
        return $data;
    }

    /**
     * 检测传递过来的参数进行处理
     * @param string $control
     * @param string $field
     * @return array|mixed
     */
    public static function checkData($control = '', $field = '')
    {
        $companyValidate = new \app\engineer\validate\EngineerValidate();
        $request = request()->param();
        /* 对传递过来的参数进行制定场景 $control 来进行检测，如果不符合规则就返回错误信息，返回函数进行后面的处理 */
        $check = $companyValidate->scene($control)->check($request);
        if($check === false){
            return $companyValidate->getError();
        }
        $request = self::buildRequestField($request, $field);
        return $request;
    }

    /**
     * 把前端传递过来的参数转换为数据库对应字段返回出来
     * @param $data
     * @param array $field
     * @return array
     */
    private static function buildRequestField($data, $field = array())
    {
        $result = array();
        foreach($data as $key => $row){
            /* $field 数组内的数据是不需要对应数据库字段的额外条件，进行额外条件的查询或者分页 */
            if(!empty($field) && in_array($key, $field)){
                $fieldArr[$key] = $row;
            }
            if(!empty($row)  || $row !== '') {
                isset(self::$fieldArr[$key])?$result[self::$fieldArr[$key]] = $row : false;
            }
        }
        return $result;
    }
}