<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 9:41
 */

namespace app\material\validate;

use think\Validate;

class MaterialValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'uuid' => 'require|alphaDash',      // 检测项目id
        'material' => 'chsDash',    // 材料名
        'type' => 'number',      // 材料类型
        'block' => 'number',     // 是否试块
        'typeName' => 'chsDash',   // 类型名
        'trial' => 'number',    // 试验项目id
        'trialName' => 'chsDash',     // 试验项目字段信息
        'depict' => 'chsDash',    // 试验项目描述信息
        'defaultHint' => 'chsDash',    // 通用信息提示
        'customValue' => 'chsDash',   // 个性化默认值
        'customHint' => 'chsDash',   // 个性化提示
        'defaultValue' => 'chsDash',   // 试验项目检测字段默认值
        'defaultToken' => 'number',   // 试验项目检测字段结果
        'defaultVerify' => 'number',   // 试验项目检测字段是否单选
        'standard' => 'number',   // 检测标准id
        'standardNumber' => 'alphaDash',   // 检测标准编号
        'standardCompany' => 'chsDash',   // 检测标准企业名称
        'standardCode' => 'alphaDash',   // 试验代号
        'standardType' => 'chsDash',   // 试验类别
        'standardFrom' => 'chsDash',   // 所检项目
        'file' => 'number',   // 文件号id
        'companyName' => 'chsDash',   // 企业名称
        'company' => 'alphaDash',   // 企业id
        'valid' => 'number',   // 是否有效
        'priceId' => 'alphaDash',   // 检测费用编号
        'price' => 'chsDash',     // 价格
        'tag' => 'chsDash',     // 标签
        'end' => 'number',    // 是否到期
    );

    protected $message = array(
        'uuid.require' => '请传递检测项目id',
        'uuid.alphaDash' => '传递检测项目id不符合规范',
        'material.chsDash' => '传递检测项目名不符合规范',
        'type.number' => '传递检测项目类型id不符合规范',
        'block.number' => '请确认上传的是否为试块',
        'typeName.chsDash' => '传递的类型名不符合规范',
        'trial.number' => '传递试验项目id不符合规范',
        'trialName.chsDash' => '传递试验项目字段信息不符合规范',
        'depict.chsDash' => '传递试验项目描述信息不符合规范',
        'defaultHint.chsDash' => '传递通用信息提示不符合规范',
        'customValue.chsDash' => '传递个性化默认值不符合规范',
        'customHint.chsDash' => '传递个性化提示',
        'defaultValue.chsDash' => '传递的试验项目检测字段默认值不符合规范',
        'defaultToke.number' => '传递的试验项目检测字段结果不符合规范',
        'defaultVerify.number' => '传递的试验项目检测字段是否单选不符合规范',
        'standard.number' => '传递的检测标准id不符合规范',
        'standardNumber.alphaDash' => '传递的检测标准编号不符合规范',
        'standardCompany.chsDash' => '传递检测标准企业名称不符合规范',
        'standardCode.alphaDash' => '传递的试验代号不符合规范',
        'standardType.chsDash' => '传递的试验类别不符合规范',
        'standardFrom.chsDash' => '传递的所检项目不符合规范',
        'file.number' => '传递文件号id不符合规范',
        'companyName.chsDash' => '传递的企业名称不符规范',
        'company.alphaDash' => '传递的企业id不符合规范',
        'valid.number' => '请确认是否有效',
        'priceId.alphaDash' => '传递的检测费用编号不符合规范',
        'price.chsDash' => '传递的价格不符合规范',
        'tag.chsDash' => '传递的标签不符合规范',
        'end.number' => '传递的是否到期不符合规范'
    );

    protected $scene = array(
        'materialList' => ['type'],
        'fileList' => [],
        'standardList' => ['standardNumber','standardCompany','standardCode','standardType','standardFrom'],
    );
}