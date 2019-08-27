<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/26
 * Time: 9:05
 */

namespace app\engineer\validate;

use think\Validate;

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
        'build' => 'alphaDash',     // 建设单位
        'supervise' => 'alphaDash',   // 监理单位
        'construction' => 'alphaDash',  // 施工单位
        'survey' => 'alphaDash',     // 勘察单位
        'design' => 'alphaDash',    // 设计单位
        'witness' => 'alphaDash',   // 见证人员
        'makeup' => 'alphaDash',   // 填单人员
        'sampling' => 'alphaDash',    // 取样人员
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
        'build.alphaDash' => '传递建设单位不符合规范',
        'supervise.alphaDash' => '传递监理单位不符合规范',
        'construction.alphaDash' => '传递施工单位不符合规范',
        'survey.alphaDash' => '传递勘察单位不符合规范',
        'design.alphaDash' => '传递设计单位不符合规范',
        'witness.alphaDash' => '传递见证人员不符合规范',
        'makeup.alphaDash' => '传递填单人员不符合规范',
        'sampling.alphaDash' => '传递取样人员不符合规范',
    );

    protected $scene = array(
        'add' => ['company','name','type','from','level','area','foundations','site','underground','CCAD','address','build','supervise','construction','survey','design','witness','makeup','sampling'],     // 工程录入
        'edit' => ['engineer','name','type','from','level','area','foundations','site','underground','CCAD','address','build','supervise','construction','survey','design','witness','makeup','sampling'],
        'del' =>['engineer'],           // 工程删除
        'list' => ['name','from','area','address'],     // 工程查询
        'main' => ['engineer'],      // 工程详情
    );
}