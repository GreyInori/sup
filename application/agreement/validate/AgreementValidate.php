<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/10
 * Time: 14:18
 */

namespace app\agreement\validate;

use think\Validate;

/**
 * Class AgreementValidate
 * @package app\agreement\validate
 */
class AgreementValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'agreement' => 'require|alphaDash',     // 合同id
        'agreementNumber' => 'alphaDash',               // 合同编号
        'agreementType' => 'number',      // 合同类型id
        'agreementTime' => 'date',        // 录入时间
        'person' => 'chsDash',                // 录入人
        'quality' => 'chsDash',           // 质监站名
        'engineering' => 'alphaDash',     // 工程id
    );

    protected $message = array(
        'agreement.require' => '请传递合同id',
        'agreement.alphaDash' => '传递合同id不符合规范',
        'agreementNumber.alphaDash' => '传递合同编号不符合规范',
        'agreementType.number' => '传递合同类型id不符合规范',
        'agreementTime.date' => '传递录入时间不符合规范',
        'person.chsDash' => '传递录入人不符合规范',
        'quality.chsDash' => '传递质监站不符合规范',
        'engineering.alphaDash' => '传递工程id不符合规范',
    );

    protected $scene = array(
        'add' => ['agreementNumber','agreementType','person','quality','engineering'],     // 合同添加
        'edit' => ['agreement','agreementNumber','agreementType','person','quality','engineering'],     // 合同添加
        'del' => ['agreement'],     // 合同添加
        'list' => ['agreementTime','person','quality'],     // 合同添加
    );
}