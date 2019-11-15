<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 13:49
 */

namespace app\user\controller;

use think\Controller;

/**
 * 传入字段验证方法
 * @package app\user\controller
 */
class UserAutoLoad extends Controller
{
    /**
     * 前端字段和数据库字段对照表
     * @var array
     */
    public static $fieldArr = array(
        'user' => 'user_id',
        'mobile' => 'user_mobile',
        'cutTime' => 'user_cut_time',
        'admin' => 'admin_role',
        'departmentName' => 'department_name',
        'department' => 'department_id',
        'meeting' => 'meeting_id',
        'title' => 'meeting_title',
        'start' => 'meeting_start',
        'end' => 'meeting_end',
        'x' => 'meeting_x',
        'y' => 'meeting_y',
        'address' => 'meeting_address',
        'content' => 'meeting_content',
        'verify' => 'meeting_verify',
        'meetingCode' => 'meeting_code',
        'position' => 'user_position',
    );

    /**
     * 传递的数据检测方法
     * @param string $control
     * @param string $field
     * @return array|mixed
     */
    public static function checkData($control = '', $field = '')
    {
        $userValidate = new \app\user\validate\UserValidate();
        $method = request()->method();
        $request =request()->{$method}();
        /* 对传递过来的参数进行指定场景 $control 来进行检测，如果不符合规则就返回错误信息 */
        if($control !== '') {
            $check = $userValidate->scene($control)->check($request);
            if($check === false) {
                return $userValidate->getError();
            }
        }
        $request = self::buildRequestField($request, $field);
        return $request;
    }

    /**
     * 前端对应数据库字段转换方法
     * @param $data
     * @param array $field
     * @return array
     */
    public static function buildRequestField($data, $field = array())
    {
        $result = array();
        foreach($data as $key => $row) {
            /* $field 数组内的数据是不需要对应数据库字段的额外条件，进行额外条件的查询或者分页 */
            if(!empty($field) && in_array($key, $field)) {
                $field[$key] = $row;
            }
            if(!empty($row) || $row !== '') {
                isset(self::$fieldArr[$key])?$result[self::$fieldArr[$key]] = $row : false;
            }
        }
        return $result;
    }
}