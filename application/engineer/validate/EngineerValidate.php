<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 9:05
 */

namespace app\engineer\validate;

use think\Validate;

/**
 * Class EngineerValidate
 * @package app\engineer\validate
 */
class EngineerValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'people' => 'require|alphaDash',    // 人员id
        'engineer' => 'require|alphaDash',   // 工程id
        'company' => 'require|alphaDash',   // 企业id
        'code' => 'alphaDash',    // 工程编号
        'name' => 'require|chsDash',    // 工程名称
        'type' => 'number',     // 工程类型
        'from' => 'alphaDash',    // 所属质监站
        'level' => 'alphaDash',     // 质监登记号
        'area' => 'chsDash',     // 工程区域
        'foundations' => 'number',    // 地面基础类型
        'site' => 'number',     // 建筑面积
        'underground' => 'number',     // 地下室面积
        'CCAD' => 'number',    // 人防工程面积
        'address' => 'chsDash',    // 工程地点
        'build' => 'array',     // 建设单位
        'supervise' => 'array',   // 监理单位
        'construction' => 'array',  // 施工单位
        'survey' => 'array',     // 勘察单位
        'design' => 'array',    // 设计单位
        'witness' => 'array',   // 见证人员
        'makeup' => 'array',   // 填单人员
        'sampling' => 'array',    // 取样人员
        'foundationsName' => 'chsDash',   // 地面基础类型名
        'mobile' => 'mobile|require',                 // 注册人账号
    );

    protected $message = array(
        'people.require' => '请传递录入人id',
        'people.alphaDash' => '传递人员id不符合规范',
        'company.require' => '请传递录入人id',
        'company.alphaDash' => '传递企业id不符合规范',
        'engineer.require' => '请传递工程id',
        'engineer.alphaDash' => '传递工程id不符合规范',
        'name.require' => '请传递工程名称',
        'name.chsDash' => '传递工程名称不符合规范',
        'type.number' => '传递工程类型不符合规范',
        'from.alphaDash' => '传递所属质监站不符合规范',
        'level.alphaDash' => '传递质监登记号不符合规范',
        'area.chsDash' => '传递工程区域不符合规范',
        'foundations.number' => '传递地面基础类型不符合规范',
        'site.number' => '传递建筑面积不符合规范',
        'underground.number' => '传递地下室面具不符合规范',
        'CCAD.number' => '传递人防工程面积不符合规范',
        'address.chsDash' => '传递工程地点不符合规范',
        'build.array' => '传递建设单位不符合规范',
        'supervise.array' => '传递监理单位不符合规范',
        'construction.array' => '传递施工单位不符合规范',
        'survey.array' => '传递勘察单位不符合规范',
        'design.array' => '传递设计单位不符合规范',
        'witness.array' => '传递见证人员不符合规范',
        'makeup.array' => '传递填单人员不符合规范',
        'sampling.array' => '传递取样人员不符合规范',
        'foundationsName.chsDash' => '传递地面基础类型不符合规范',
    );

    protected $scene = array(
        'reg' => ['name','mobile'],
        'add' => ['name','type','from','level','area','foundations','site','underground','CCAD','address','build','supervise','construction','survey','design','witness','makeup','sampling'],     // 工程录入
        'edit' => ['engineer','name','type','from','level','area','foundations','site','underground','CCAD','address','build','supervise','construction','survey','design','witness','makeup','sampling'],
        'del' =>['engineer'],           // 工程删除
        'list' => ['from','area','address'],     // 工程查询
        'main' => ['engineer'],      // 工程详情
        'divide' => ['engineer'],      // 工程相关成员账号
        'foundationsAdd' => ['foundationsName'],   // 地面基础类型添加
        'foundationsEdit' => ['foundations','foundationsName'],   // 地面基础类型修改
        'foundationsDel' => ['foundations'],   // 地面基础类型删除
    );
}