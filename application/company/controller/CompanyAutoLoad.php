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
    protected static $fieldArr = array(
        'company' => 'company_name',
        'name' => 'company_full_name',
        'uniform' => 'company_code',
        'linkman' => 'company_linkman',
        'mobile' => 'company_mobile',
        'contact' => 'company_contact_information',
        'record' => 'is_record',
        'corporation' => 'company_corporation',
        'corporation_mobile' => 'company_corporation_mobile'
    );

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

            isset(self::$fieldArr[$key])?$result[self::$fieldArr[$key]] = $row : false;
        }

        return $result;
    }

}