<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/19
 * Time: 15:08
 */

namespace app\company\validate;

use think\Validate;

/**
 * 企业输入数据监测过滤方法
 * @package app\company\validate
 */
class CompanyValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'company' => 'require|alphaDash',             // 企业账号
        'name' => 'require|chsDash',                    // 企业名称
        'uniform' => 'require|alphaNum',     // 社会统一认证编号
        'linkMan' => 'require|chsDash',                // 联系人
        'mobile' => 'require|mobile',                    // 联系电话
        'check' => 'require|number',        // 验证码
        'contact' => 'chsDash',                // 其他联系方式
        'accept' => 'require|accepted',              // 是否同意协议
        'record' => 'require|number',        // 企业类型
        'corporation' => 'chsDash',           // 企业法人
        'corporationMobile' => 'mobile',     // 企业法人联系电话
        'code' => 'alphaDash',                  // 营业执照
        'start' => 'date',                          // 营业执照有效期始
        'end' => 'date',                          // 营业执照有效期止
        'capital' => 'number',
        'character' => 'number',
        ''
    );

    protected $message = array(
        'company.require' => '请传递企业账号',
        'company.alpha' => '企业账号不符合规范，企业账号只能为字母、数字、下划线',
        'name.require' => '请传递全称',
        'name.chsDash' => '企业全称不符合规范，企业全称只能为汉字、字母、数字、下划线',
        'uniform.require' => '请传递社会统一认证编号',
        'uniform.alphaNum' => '传递社会认证统一编号不符合规范',
        'linkman.require' => '请传递联系人名',
        'linkman.chsDsh' => '联系人名不符合规范',
        'mobile.require' => '请传递企业手机号',
        'mobile.mobile' => '请传递正确的手机号',
        'check.require' => '请传递验证码',
        'check.number' => '验证码不符合规范',
        'contact.chsDash' => '企业其他联系方式不符合规范',
        'accept.require' => '请确认是否同意协议',
        'accept.accepted' => '请确认是否同意协议',
        'record.require' => '请输入需要获取的企业类型',
        'record.number' => '传递的企业类型不符合规范'
    );

    protected $scene = array(
        'reg' => ['company','name','uniform','linkMan','mobile','code','contact','accept'],     // 企业账号注册申请
        'getCode' => ['mobile'],
        'list' => ['record']
    );
}