<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/19
 * Time: 15:08
 */

namespace app\people\validate;

use think\Validate;

/**
 * 人员输入数据监测过滤方法
 * @package app\people\validate
 */
class PeopleValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'user' =>'require|alphaDash',   // 人员账号
        'uuid' => 'require|alphaDash',        // 人员id
        'code' => 'alphaDash',       // 人员编号
        'name' => 'chsDash',    // 人员姓名
        'idCard' => 'alphaDash',     // 人员身份证号码
        'mobile' => 'mobile',     // 人员手机号
        'professional' => 'number',  // 人员职称id
        'birthday' => 'date',    // 人员生日
        'information' => 'chsDash',   // 其他联系方式
        'address' => 'chsDash',      // 人员住址
        'credential' => 'alphaDash',    // 见证人编号
        'company' => 'alphaDash',    // 企业id
        'check' => 'require',           // 手机验证码
//        'verify' => 'number',            // 人员类型id
        'sex' => 'number',        // 人员性别
        'accept' => 'accepted',    // 是否同意协议
        'role' => 'require|number',      // 人员权限
    );

    protected $message = array(
        'uuid.require' =>  '请传递人员id',
        'uuid.alphaDash' => '传递人员id不符合规范',
        'code.alphaDash' => '传递人员编号不符合规范',
        'name.chsDash' => '传递人员姓名不符合规范',
        'idCard.alphaDash' => '传递身份证号不符合规范',
        'mobile.mobile' => '传递电话号码不符合规范',
        'professional.number' => '传递人员职称不符合规范',
        'birthday.date' => '传递人员生日不符合规范',
        'information.chsDash' => '传递其他联系方式不符合规范',
        'address.chsDash' => '传递人员住址不符合规范',
        'credential.alphaDash' => '传递见证人编号不符合规范',
        'company.alphaDash' => "传递企业id不符合规范",
//        'verify.number' => "传递人员类型id不符合规范",
        'check.require' => '请传递手机验证码',
        'user.require' => '请传递人员账号',
        'user.alphaDash' => '传递的人员账号不符合规范',
        'sex.number' => '传递人员啊性别不符合规范',
        'accept.accepted' => '请确认是否同意协议',
    );

    protected $scene = array(
        'reg' => ['address','user','pass','name','sex','idCard','mobile','information','accept'],     // 人员账号注册申请
        'getCode' => ['mobile'],
        'list' => ['name','idCard','code'],
        'add' => ['user','pass','name','sex','idCard','mobile','information','accept'],
        'edit' => ['uuid','pass','name','sex','idCard','mobile','information','accept'],
        'del' => ['uuid'],
    );
}