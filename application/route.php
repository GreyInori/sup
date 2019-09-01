<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

Route::get('/','Index/Index/index');
Route::controller('/company','Company/Company');
Route::controller('/people','People/People');
Route::controller('/engineer','Engineer/Engineer');
Route::controller('/material','Material/Material');
Route::controller('/trust','Trust/Trust');
/* 所有路由匹配不到的情况下触发 */
Route::miss('Api/Exception/miss');
