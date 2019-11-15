<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/11
 * Time: 15:15
 */

namespace app\meeting\validate;

use think\Validate;

class MeetingValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'user' => 'require',
        'start' => 'date',
        'end' => 'date',
        'verify' => 'number',
        'title' => 'require',
        'department' => 'require|number',
        'meeting' => 'require|number',
        'position' => 'chsDash',
        'mobile' => 'require|mobile',
        'name' => 'chsDash',
        'departmentName' => 'chsDash',
        'sex' => 'number',
    );

    protected $message = array(
        'mobile.require' => '请传递用户手机号',
        'mobile.mobile' => '传递的用户手机号不符合规范',
        'start.date' => '传递的开始时间不符合规范',
        'end.date' => '传递的结束时间不符合规范',
        'verify.number' => '传递的会议状态不符合规范',
        'title.require' => '请传递会议的标题',
        'department.require' => '请传递创建会议的部门id',
        'department.number' => '传递的部门id不符合规范',
        'meeting.require' => '请传递需要操作的会议id',
        'meeting.number' => '传递的会议id不符合规范',
        'position.chsDash' => '传递的人员职务不符合规范',
        'name.chsDash' => '传递的人员姓名不符合规范',
        'departmentName.chsDash' => '传递的单位名称不符合规范',
        'sex.number' => '传递的人员性别不符合规范',
    );

    protected $scene = array(
        'list' => array('mobile', 'start', 'end', 'verify'),
        'main' => array('meeting'),
        'add' => array('title', 'start', 'end'),
        'edit' => array('meeting'),
        'del' => array('meeting'),
        'memberAdd' => array('meeting', 'department', 'mobile', 'position', 'name', 'departmentName', 'sex'),
        'memberDel' => array('meeting', 'mobile'),
        'memberList' => array('meeting'),
    );
}