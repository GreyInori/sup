<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 11:55
 */

namespace app\user\validate;

use think\Validate;

/**
 * 用户输入数据监测类
 * @package app\user\validate
 */
class UserValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'mobile' => 'require|mobile',
        'user' => 'require',
        'departmentName' => 'require|chsDash',
        'department' => 'require|number',
        'meetingCode' => 'require|alphaDash',
        'meeting' => 'require',
        'x' => 'require',
        'y' => 'require',
    );

    protected $message = array(
        'mobile.require' => '请传递用户手机号',
        'mobile.mobile' => '传递的用户手机号不符合规范',
        'user.require' => '请传递用户id',
        'departmentName.require' => '请传递部门名称',
        'departmentName.alphaDash' => '传递的部门名称不符合规范',
        'department.require' => '请传递需要修改的单位id',
        'department.number' => '传递的单位id不符合规范',
        'meetingCode.require' => '请传递会议编码',
        'meetingCode.alphaDash' => '传递的会议编码不符合规范',
        'meeting.require' => '传递的会议id',
        'x.require' => '传递的会议x坐标',
        'y.require' => '传递的会议y坐标',
    );

    protected $scene = array(
        'login' => array('mobile'),
        'createDepartment' => array('departmentName'),
        'editDepartment' => array('department'),
        'delDepartment' => array('department'),
        'meetingList' => array('user'),
        'qrSign' => array('mobile','meetingCode'),
        'addressSign' => array('mobile','x','y','meeting')
    );
}
