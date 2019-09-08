<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/2
 * Time: 0:14
 */

namespace app\testing\validate;

use think\Validate;

/**
 * Class TrustValidate
 * @package app\trust\validate
 */
class TestingValidate extends  Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'supervision' => 'alphaDash',   // 平台监督号
        'error' => 'chsDash',   // 异常信息
        'response' => 'chsDash',   // 异常回复
        'trust' => 'require',   // 委托单号
        'errorId' => 'number',   // 异常id
        'testName' => 'chsDash',   // 检测项目名称
        'reportMain' => 'chsDash',   // 检测报告
        'reportNumber' => 'alphaDash',   // 报告编号
    );

    protected $message = array();

    protected  $scene = array(
        'list' => array('supervision'),
        'postErr' => array('error','trust'),
        'errList' => array('error','response','errorId','trustCode','inputCompany'),
        'reportUpload' => array('trust','reportMain'),
        'reportList' => array('reportNumber','reportMain','testName',''),
    );
}