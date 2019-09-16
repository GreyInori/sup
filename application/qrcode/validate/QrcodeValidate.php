<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/16
 * Time: 9:15
 */

namespace app\qrcode\validate;

use think\Validate;

/**
 * Class QrcodeValidate
 * @package app\qrcode\validate
 */
class QrcodeValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'company' => 'require|number',     // 企业id
        'companyCode' => 'alphaDash',     // 平台上的企业编码
        'companyName' => 'chsDash',      // 企业名称
        'linkman' => 'chsDash',             // 企业联系人
        'mobile' => 'number',              // 企业电话
        'work' => 'require|number',      // 业务类型id
        'workName' => 'chsDash',        // 业务类型名称
        'workCode' => 'alphaDash',      // 业务类型代码
        'codeNum' => 'require|number',    // 创建的二维码的数量
    );

    protected $message = array(
        'company.require' => '请传递企业id',
        'company.number' => '传递的企业id不符合规范',
        'companyCode.alphaDash' => '传递的企业编码不符合规范',
        'companyName.chsDash' => '传递的企业名称不符合规范',
        'linkman.chsDash' => '传递的企业联系人不符合规范',
        'mobile.number' => '传递的企业电话不符合规范',
        'work.require' => '请传递业务类型id',
        'work.number' => '传递的业务类型id不符合规范',
        'workName.chsDash' => '传递的业务类型名不符合规范',
        'workCode.alphaDash' => '传递的业务类型编码不符合规范',
        'codeNum.require' => '请传递需要创建的二维码数量',
        'codeNum.number' => '传递的二维码数量不符合规范',
    );

    protected $scene = array(
        'companyAdd' => ['companyCode','companyName','linkman','mobile'],
        'companyEdit' => ['company','companyCode','companyName','linkman'],
        'companyDel' => ['company'],
        'companyList' => ['companyCode','companyName','linkman'],
        'workAdd' => ['workName','workCode'],
        'workEdit' => ['work','workName','workCode'],
        'workDel' => ['work'],
        'workList' => ['workName','workCode'],
        'qrcodeCreat' => ['company','work','codeNum']
    );
}