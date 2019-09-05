<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/22
 * Time: 10:38
 */

namespace app\lib\controller;

trait Picture
{
    /**
     * 图片上传方法
     * @param string $path
     * @param array $field
     * @return array|bool
     */
    public static function imgUp($path = '', $field = array())
    {
        $request = array();   // 返回值的格式为 需要上传的图片字段信息 => 保存的图片路径
        /* 如果没有传递需要上传的图片字段值的话，就返回错误信息 */
        if(empty($field)) {
            return '请传递需要上传的图片字段数据';
        }
        /* 由于传递过来的需要处理的图片字段数据是数组格式，因此要循环数组，把每个字段制定的图片对象进行保存 */
        foreach($field as $picKey){
            $pic = self::toImgUp($path, $picKey);
            /* 图片上传失败时会返回错误信息，直接把错误信息返回 */
            if(!is_array($pic)){
                return $pic;
            }
            $request[$picKey] = $pic['pic'];
        }
        return $request;
    }

    public function imgBatchUP()
    {

    }

    /**
     * 执行图片上传方法
     * @param $path
     * @param $pic
     * @return array|int|string
     */
    private static function toImgUp($path, $pic)
    {
        if(!is_object(request()->file($pic))){
            return array('pic'=>'');
        }
        /* 判断上传的文件是否符规范，如果是就丢到制定的目录下 */
        $image = request()->file($pic);
        $data = $image->validate(['ext'=>'png,jpg,jpeg,gif,bmp'])->move(ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.$path.DS);
        if(!$data){
            return $image->getError();
        }
        /* 图片上传成功后返回图片路径数组，因为返回字符串的话会被方法直接当做错误结果返回 */
        $fileName = $data->getSaveName();
        $fileName = "/static/images/{$data}/{$fileName}";
        return array('pic'=>$fileName);
    }
}