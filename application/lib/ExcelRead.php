<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/14
 * Time: 10:17
 */

namespace app\lib;

/**
 *
 * @package app\lib
 */
class ExcelRead
{

    protected static function excelUpload()
    {
        $file = request()->file('implode');
        $info = $file->validate(['ext'=>'xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        $excelPath = $info->getSaveName();
        //获取上传文件地址
        $file_name = ROOT_PATH . 'public' . DS . 'uploads' . DS . $excelPath;
        return $file_name;
    }

    protected static function readExcel()
    {

    }
}