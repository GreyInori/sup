<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/11
 * Time: 9:32
 */

namespace app\area\validate;

use think\Validate;

/**
 * Class AreaValidate
 * @package app\area\validate
 */
class AreaValidate extends Validate
{
    protected $regex = array(
        'mobile' => '/^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[012345678]|9[89]])\d{8}$/'
    );

    protected $rule = array(
        'area' => 'require|number',       // 地区id
        'areaName' => 'chsDash',        // 地区名
        'areaParent' => 'number',              // 地区父类id
    );

    protected $message = array(
        'area.require' => '请传递地区id',
        'area.number' => '传递的地区id不符合规范',
        'areaName.chsDash' => '传递的地区名不符合规范',
        'areaParent.number' => '传递的地区父类id不符合规范'
    );

    protected $scene = array(
        'add' => ['areaName','areaParent'],     // 地区添加
        'edit' => ['area','areaName','areaParent'],    // 地区修改
        'del' => ['area'],        // 地区删除
        'list' => ['areaParent']    // 获取地区列表
    );
}