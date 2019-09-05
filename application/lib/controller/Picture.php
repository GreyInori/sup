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
	
		protected function companyImgUp($filePath = '', $requestPath = '')
		{
			/* 加载上传的图片，并进行图片处理 */
			if('' == $requestPath){

				$requestPath = 'file';
			}

			$image = request()->file($requestPath);

			if(!is_object($image)){

				$image = $image[$requestPath];
			}

			$data = $image->validate(['ext'=>'png,jpeg,jpg,gif,bmp'])->move(ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.$filePath.DS);

			if(!$data){

				return self::returnMsg(500,'fail',$image->getError());
			}
			/* 保存图片并获取图片完整路径 */
			$fileName = $data->getSaveName();

			// $file  = ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.$filePath.DS.$fileName;
			/* 缩放图片并保存删除源文件，返回图片路径 */
			// $image = \think\Image::open(ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.$filePath.DS.$fileName);

			// $del = unlink($file);

			// $image->save($file);

			$fileName = "/static/images/{$filePath}/{$fileName}";

			return $fileName;
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