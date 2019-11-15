<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 17:19
 */

namespace app\material\controller;

use think\Controller;

/**
 * Class MaterialAutoLoad
 * @package app\material\controller
 */
class MaterialAutoLoad extends Controller
{
    /**
     * @var array
     * 把传递过来的字段转换成后台数据库对应的字段
     */
    public static $fieldArr = array(
        'standard' => 'testing_id',
        'standardNumber' => 'testing_number',
        'companyName' => 'company_full_name',
        'standardCode' => 'testing_code',
        'standardType' => 'testing_type',
        'standardFrom' => 'testing_from',
        'basisNumber' => 'testing_basis_number',
        'basis' => 'testing_basis',
        'determineNumber' => 'determine_standard_number',
        'determine' => 'determine_standard',
        'priceId' => 'price_id',
        'company' => 'company_id',
        'remarks' => 'material_remarks',
        'price' => 'testing_price',
        'end' => 'is_end',
        'tag' => 'tag_number',
        'material' => 'material_name',
        'fileNumber' => 'file_number',
        'valid' => 'is_valid',
        'fileId' => 'file_id',
        'type' => 'type_id',
        'typeParent' => 'type_pid',
        'typeName' => 'type_name',
        'block' => 'block_type',
        'upload' => 'upload_type',
        'blockId' => 'block_id',
        'materialId' => 'material_id',
        'materialType' => 'material_type',
        'trial' => 'trial_id',
        'trialName' => 'trial_name',
        'trialDepict' => 'trial_depict',
        'trialHint' => 'trial_default_hint',
        'trialCustomHint' => 'trial_custom_hint',
        'default' => 'default_id',
        'defaultValue' => 'trial_default_value',
        'defaultToken' => 'trial_default_token',
        'defaultVerify' => 'trial_verify',
        'divideName' => 'divide_name',
        'remark' => 'material_remark',
    );

    /**
     * @var array
     * 给指定字段数据根据数据表区别分组
     */
    public static $fieldGroup = array(
        'standard' => array('testing_id','testing_number','company_full_name','testing_code','testing_type','testing_from','testing_basis_number','testing_basis','determine_standard_number','determine_standard'),
        'price' => array('price_id','company_id','testing_number','company_full_name','testing_code','testing_type','testing_from','testing_price','material_remarks','tag_number','is_end'),
        'file' => array('company_id','company_full_name','testing_code','file_number','file_id','material_name','is_valid','testing_number'),
        'type' => array('type_id','type_pid','type_name'),
        'block' => array('block_type','upload_type','block_id'),
        'material' => array('testing_code','type_id','material_type','material_id','material_name','block_id'),
        'materialField' => array('trial_name','trial_depict','trial_default_hint','trial_custom_hint','material_id','trial_id'),
        'materialDefault' => array('default_id','trial_default_value','trial_default_token','trial_verify','trial_id'),
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
        $materialValidate = new \app\material\validate\MaterialValidate();
        $request = request()->param();

        /* 对传递过来的参数进行制定场景 $control 来进行检测，如果不符合规则就返回错误信息，返回函数进行后面的处理 */
        $check = $materialValidate->scene($control)->check($request);

        if($check === false){
            return $materialValidate->getError();
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