<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/11
 * Time: 9:17
 */

namespace app\admin\validate;

use think\Validate;

/**
 * Class AdminValidate
 * @package app\admin\controller
 */
class AdminValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'user' => 'require|chsDash',                  // 用户id
        'userName' => 'require|mobile',      // 用户手机号
        'userPass' => 'chsDash',           // 用户密码
        'company' => 'chsDash',           // 企业id
        'companyName' => 'chsDash',    // 企业名
        'role' => 'number',     // 成员角色
        'roleName' => 'chsDash'       // 成员角色名
    );

    protected $message = array(
        'user.require' => '请传递用户id',
        'user.chsDash' => '传递的用户id不符合规范',
        'userName.require' => '请传递用户手机号',
        'userName.mobile' => '传递的用户手机号不符合规范',
        'userPass.chsDash' => '传递的用户密码不符合规范',
        'company.chsDash' => '传递的企业id不符合规范',
        'companyName.chsDash' => '传递的企业id不符合规范',
        'role.chsDash' => '传递的成员角色不符合规范',
        'roleName.chsDash' => "传递的成员角色名不符合规范"
    );

    protected $scene = array(
        'add' => ['userName','userPass','company','role'],
        'edit' => ['user','userName','userPass','company','role'],
        'del' => ['user'],
        'list' => ['companyName','roleName'],
        'main' => ['user'],
    );
}