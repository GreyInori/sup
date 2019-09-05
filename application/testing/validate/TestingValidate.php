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
    );

    protected $message = array();

    protected  $scene = array(
        'list' => array('supervision'),
    );
}