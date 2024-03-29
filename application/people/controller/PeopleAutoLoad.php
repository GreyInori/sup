<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/19
 * Time: 17:43
 */

namespace app\people\controller;

use think\Controller;

/**
 * Class PeopleAutoLoad
 * @package app\people\controller
 */
class PeopleAutoLoad extends Controller
{
    /**
     * @var array
     * 把传递过来的字段转换成后台数据库对应的字段
     */
    public static $fieldArr = array(
        'uuid' => 'people_id',
        'code' => 'people_code',
        'name' => 'people_name',
        'idCard' => 'people_idCard',
        'mobile' => 'people_mobile',
        'professional' => 'professional_id',
        'birthday' => 'people_birthday',
        'information' => 'people_contact_information',
        'address' => 'people_address',
        'credential' => 'people_credential_code',
        'company' => 'company_Id',
        'verify' => 'people_verify',
        'sex' => 'people_sex',
        'user' => 'people_user',
        'pass' => 'people_passwd'
    );

    /**
     * @var array
     * 给指定字段数据根据数据表区别分组
     */
    public static $fieldGroup = array(
        'people' => array('people_id','people_code','people_passwd','people_verify','company_id','people_sex','people_user','people_name','people_name','people_idCard','people_mobile','professional_id','people_birthday','people_contact_information','people_address','people_credential_code'),
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
        $companyValidate = new \app\people\validate\PeopleValidate();
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