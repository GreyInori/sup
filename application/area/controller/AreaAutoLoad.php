<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/11
 * Time: 9:33
 */

namespace app\area\controller;

use think\Controller;

/**
 * Class AreaAutoLoad
 * @package app\area\controller
 */
class AreaAutoLoad extends Controller
{
    public static $fieldArr = array(
        'area' => 'area_id',
        'areaName' => 'area_name',
        'areaParent' => 'area_pid',
    );

    public static $fieldGroup = array(
        'area' => array('area_id','area_name','area_pid'),
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
        $companyValidate = new \app\area\validate\AreaValidate();
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