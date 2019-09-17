<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/19
 * Time: 17:43
 */

namespace app\company\controller;

use think\Controller;

/**
 * Class CompanyAutoLoad
 * @package app\company\controller
 */
class CompanyAutoLoad extends Controller
{
    /**
     * @var array
     * 把传递过来的字段转换成后台数据库对应的字段
     */
    public static $fieldArr = array(
        'uuid' => 'company_id',
        'pass' => 'company_passwd',
        'company' => 'company_name',
        'name' => 'company_full_name',
        'uniform' => 'company_code',
        'linkman' => 'company_linkman',
        'linkmanMobile' => 'company_linkman_mobile',
        'mobile' => 'company_mobile',
        'contact' => 'company_contact_information',
        'record' => 'is_record',
        'corporation' => 'company_corporation',
        'corporation_mobile' => 'company_corporation_mobile',
        'business' => 'company_business_license',
        'start' => 'company_business_start',
        'end' => 'company_business_end',
        'capital' => 'company_registered_capital',
        'character' => 'company_character',
        'website' => 'company_website',
        'fax' => 'company_fax',
        'area' => 'company_area',
        'regAddr' => 'company_register_address',
        'AD' => 'company_AD',
        'postal' => 'company_postal_code',
        'businessAddr' => 'company_business_address',
        'rules' => 'company_rules',
        'profile' => 'company_profile',
        'email' => 'company_linkman_email',
        'page' => 'page',
        'show' => 'show',
        'regTime' => 'company_register_time',
        'number' => 'company_number',
        'form' => 'company_form',
        'formName' => 'form_name',
        'characterId' => 'character_id',
        'characterName' => 'character_name',
    );

    /**
     * @var array
     * 给指定字段数据根据数据表区别分组
     */
    public static $fieldGroup = array(
        'main' => array('company_corporation','company_corporation_mobile','company_registered_capital','company_character','company_website','company_fax','company_area','company_register_address','company_AD','company_postal_code','company_business_address'),
        'text' => array('company_rules','company_profile'),
        'company' => array('company_passwd','company_form','company_id','company_register_time','company_name','company_full_name','company_code','company_linkman','company_linkman_mobile','company_mobile','company_contact_information','company_business_license','company_business_start','company_business_end')
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
        $companyValidate = new \app\company\validate\CompanyValidate();
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
            if(!empty($row) || $row !== '') {
                isset(self::$fieldArr[$key])?$result[self::$fieldArr[$key]] = $row : false;
            }
        }
        return $result;
    }
}