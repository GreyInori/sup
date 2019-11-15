<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 15:25
 */

namespace app\meeting\controller;

use think\Controller;

/**
 * 会议相关字段检测转换类
 * @package app\meeting\controller
 */
class MeetingAutoLoad extends Controller
{
    public static $fieldArr = array(
        'department' => 'department_id',
        'meeting' => 'meeting_id',
        'title' => 'meeting_title',
        'start' => 'meeting_start',
        'end' => 'meeting_end',
        'x' => 'meeting_x',
        'y' => 'meeting_y',
        'address' => 'meeting_address',
        'content' => 'meeting_content',
        'mobile' => 'user_mobile',
        'verify' => 'meeting_verify',
        'position' => 'user_position',
        'name' => 'user_name',
        'token' => 'user_token',
        'departmentName' => 'department_name',
        'sex' => 'user_sex',
        'remark' => 'user_remark',
        'status' => 'user_status',
        'meetingCode' => 'meeting_code',
    );

    public static function checkData($control = '', $field = '')
    {
        $userValidate = new \app\meeting\validate\MeetingValidate();
        $method = request()->method();
        $request =request()->{$method}();
        /* 对传递过来的参数进行指定场景 $control 来进行检测，如果不符合规则就返回错误信息 */
        $check = $userValidate->scene($control)->check($request);
        if($check === false) {
            return $userValidate->getError();
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
                /* 如果传递的参数是时间相关的话，就转换为时间戳 */
                if($key == 'start' || $key == 'end') {
                    $row = strtotime($row);
                }
                isset(self::$fieldArr[$key])?$result[self::$fieldArr[$key]] = $row : false;
            }
        }
        return $result;
    }
}