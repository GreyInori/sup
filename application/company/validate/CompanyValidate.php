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
        'uuid' => 'require|alphaDash',        // 企业id
        'pass' => 'require',                     // 企业密码
        'company' => 'require|alphaDash',             // 企业账号
        'name' => 'require|chsDash',                    // 企业名称
        'uniform' => 'alphaNum',     // 社会统一认证编号
        'linkMan' => 'chsDash',                // 联系人
        'mobile' => 'mobile',                    // 联系电话
        'check' => 'require|number',        // 验证码
        'contact' => 'chsDash',                // 其他联系方式
        'accept' => 'require|accepted',              // 是否同意协议
        'record' => 'require|number',        // 企业类型
        'corporation' => 'chsDash',           // 企业法人
        'corporationMobile' => 'mobile',     // 企业法人联系电话
        'business' => 'alphaDash',                  // 营业执照
        'start' => 'date',                          // 营业执照有效期始
        'end' => 'date',                          // 营业执照有效期止
        'capital' => 'number',                  // 注册资本
        'character' => 'number',               // 企业经济性质
        'website' => 'url',                      // 企业网址
        'fax' => 'number',                  // 企业传真
        'area' => 'number',               // 企业地区
        'regAddr' => 'chsDash',         // 工商注册地办公地址
        'AD'      => 'chsDash',            //工商注册地行政主管部门
        'postal' => 'number',                // 工商注册地邮编
        'businessAddr' => 'chsDash',       // 企业营业地址
        'rules' => 'chsDash',                // 企业章程
        'profile' => 'chsDash',             // 企业简介
        'email' => 'email',                // 企业邮箱
        'show' => 'number'              // 企业软删除状态
    );

    protected $message = array(
        'uuid.require' =>  '请传递企业id',
        'uuid.alphaDash' => '传递企业id不符合规范，企业id只能为字母、数字、下划线',
        'pass.require' => '请传递需要注册的企业密码',
        'company.require' => '请传递企业账号',
        'company.alphaDash' => '企业账号不符合规范，企业账号只能为字母、数字、下划线',
        'name.require' => '请传递全称',
        'name.chsDash' => '企业全称不符合规范，企业全称只能为汉字、字母、数字、下划线',
        'uniform.alphaNum' => '传递社会认证统一编号不符合规范',
        'linkman.chsDsh' => '联系人名不符合规范',
        'mobile.mobile' => '请传递正确的手机号',
        'check.require' => '请传递验证码',
        'check.number' => '验证码不符合规范',
        'contact.chsDash' => '企业其他联系方式不符合规范',
        'accept.require' => '请确认是否同意协议',
        'accept.accepted' => '请确认是否同意协议',
        'record.require' => '请输入需要获取的企业类型',
        'record.number' => '传递的企业类型不符合规范',
        'corporation.chsDash' => '企业法人名不符合规范',
        'corporationMobile.mobile' => '企业法人手机号码不符合规范',
        'business.alphaDash' => '传递的营业执照格式不符合规范',
        'start.date' => '营业执照有效期始需要传递正确的时间格式',
        'end_date' => '营业执照有效期止需要传递正确的时间格式',
        'capital.number' => '注册资本只需要传递金额数据',
        'character.number' => '企业经济性质不符合规范',
        'website.url' => '企业网址需要传递正确的网址格式',
        'fax.number' => '传递的传真数据格式不符合规范',
        'area.number' => '传递的企业地区数据不符合规范',
        'regAddr.chsDash' => '传递的注工商注册地办公地址不符合规范',
        'AD.chsDash' => '传递的工商注册地行政主管部门数据不符合规范',
        'postal.number' => '请传递正确的工商注册地邮编',
        'businessAddr.chsDash' => '请传递正确的工商注册地地址',
        'rules.chsDash' => '传递的企业章程数据不符合规范',
        'profile.chsDash' => '传递的企业简介数据不符合规范',
        'email.email' => '请传递正确格式的企业邮箱',
        'show.number' => '请传递正确格式的企业状态',
    );

    protected $scene = array(
        'reg' => ['company','name','uniform','linkMan','mobile','code','contact','accept','pass'],     // 企业账号注册申请
        'getCode' => ['mobile'],
        'list' => ['record'],
        'add' => ['name','uniform','business','start','end','capital','character','corporationMobile','website','fax','area','regAddr','postal','AD','rules','profile','linkman','mobile','contact','email'],
        'edit' => ['uuid','name','uniform','business','start','end','capital','character','corporationMobile','website','fax','area','regAddr','postal','AD','rules','profile','linkman','mobile','contact','email'],
        'del' => ['uuid'],
    );
}