<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/2
 * Time: 0:15
 */

namespace app\trust\controller;

use think\Validate;

/**
 * Class TrustAutoLoad
 * @package app\trust\controller
 */
class TrustAutoLoad extends Validate
{
    /**
     * @var array
     * 把传递过来的字段转换成后台数据库对应的字段
     */
    public static $fieldArr = array(
        'trust' => 'trust_id',
        'trustCode' => 'trust_code',
        'serial' => 'serial_number',
        'companyName' => 'company_full_name',
        'materialName' => 'testing_name',
        'project' => 'project_name',
        'testName' => 'testing_name',
        'customCompany' => 'custom_company',
        'preCompany' => 'pre_testing_company',
        'inputCompany' => 'input_testing_company',
        'testType' => 'testing_type',
        'material' => 'testing_material',
        'input' => 'input_time',
        'price' => 'testing_price',
        'submit' => 'is_submit',
        'print' => 'is_print',
        'witness' => 'is_witness',
        'sample' => 'is_sample',
        'testing' => 'is_testing',
        'report' => 'is_report',
        'cancellation' => 'is_cancellation',
        'allow' => 'is_allow',
        'result' => 'testing_result',
        'engineering' => 'engineering_id',
        'processing' => 'processing_type',
        'default' => 'default_id',
        'trial' => 'trial_id',
        'trialValue' => 'trial_default_value',
        'trialToken' => 'trial_default_token',
        'trialVerify' => 'trial_verify',
        'fileId' => 'file_id',
        'filePath' => 'file_file',
        'fileDepict' => 'file_depict',
        'fileTime' => 'file_time',
        'fileCode' => 'file_code',
        'UploadPeople' => 'upload_people',
        'imgType' => 'type_name',
        'witnessPeople' => 'witness_people',
        'fileType' => 'file_type',
        'depict' => 'file_depict',
        'people' => 'upload_people',
        'code' => 'file_code',
        'file' => 'file_id',
        'record' => 'record_id',
        'materialType' => 'type_id',
        'engineerName' => 'engineering_name',
        'typeDepict' => 'type_depict',
        'user_name' => 'user_name',
        'mobile' => 'user_name',
        'standardCode' => 'testing_code',
        'trialName' => 'trial_name',
        'trialDepict' => 'trial_depict',
        'trialHint' => 'trial_default_hint',
        'trialCustomHint' => 'trial_custom_hint',
    );

    /**
     * @var array
     * 给指定字段数据根据数据表区别分组
     */
    public static $fieldGroup = array(
        'trust' => array('type_id','witnessPeople','testing_type','processing_type','engineering_id','trust_id','serial_number','company_full_name','testing_name','project_name','custom_company','pre_testing_company','input_testing_company','testing_type','testing_material','input_time','testing_price','is_submit','is_print','is_witness','is_sample','is_testing','is_cancellation','is_allow','testing_result'),
        'default' => array('default_id','trial_id','trial_default_value','trial_default_token','trial_verify','trust_id'),
        'upload' => array('file_depict','upload_people','file_code','file_id'),
    );
    public static $listField = array(
        'list' => array('trust','serial','preCompany','inputCompany','testName','trustCode','project','customCompany','input','price','submit','print','witness','sample','testing','report','cancellation','allow','result'),
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
     * 判断指定字段是否在字段分类数组内，如果有，就进行分类，范回复分类后的数组
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
        $companyValidate = new \app\trust\validate\TrustValidate();
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
    public static function buildRequestField($data, $field = array())
    {
        $result = array();

        foreach($data as $key => $row){
            if(is_int($key)) {
                $result[$key] = array();
                foreach($row as $rowKey => $rowMain) {
                    isset(self::$fieldArr[$rowKey])?$result[$key][self::$fieldArr[$rowKey]] = $rowMain : false;
                }
            }
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