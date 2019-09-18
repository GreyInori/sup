<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/17
 * Time: 10:09
 */

namespace app\trust\controller\api;

use think\Controller;
use think\Db;
use \app\lib\controller\Picture;

/**
 * Class TrustBase
 * @package app\trust\controller\api
 */
class TrustBase extends Controller
{
    use Picture;

    /**
     * 通过委托单号获取已经存在的委托单检测人员照片
     * @param $trust
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTrustPic($trust)
    {
            $trust = $trust['trust_id'];
            $pic = Db::table('su_trust_people_pic')->where('trust_id',$trust)->field(['people_pic'])->select();
            if(empty($pic)) {
                return '当前委托单尚未上传取样人面部照片，请联系相关人员';
            }
            return $pic;
    }

    /**
     * 执行取样人用于面部检测照片上传操作
     * @param $path
     * @param $trust
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function baseAdd($path, $trust)
    {
        $token = 0;
        /* 判断传递过来的委托单号是否符合规范 */
        $trustList = Db::table('su_trust')->where('trust_id',$trust['trust_id'])->field(['trust_id'])->select();
        if(empty($trustList)) {
            return '查无此委托单，请检查传递的委托单号';
        }
        $trustBase = Db::table('su_trust_people_pic')->where('trust_id',$trust['trust_id'])->field(['trust_id'])->select();
        if(!empty($trustBase)) {
            $token = 1;
        }
        /* 根据当前收样人图片判断是需要修改还是添加操作 */
        try{
            if($token == 0){
                $update = Db::table('su_trust_people_pic')->insert(['trust_id'=>$trust['trust_id'],'people_pic'=>$path]);
            }else{
                $update = Db::table('su_trust_people_pic')->where(['trust_id'=>$trust['trust_id']])->update(['people_pic'=>$path]);
            }
            return array($update);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 进行图片上传操作，返回图片路径
     * @return array|int|string
     */
    public static function picUpload()
    {
        return self::toImgUp('trustPeople','pic');
    }

    /**
     * 执行删除图片操作
     * @param $path
     * @return array|string
     */
    public static function picDel($path)
    {
        $filePath = ROOT_PATH.'public'.$path;
        try{
            return array(unlink($filePath));
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 把图片转换成base64编码格式返回出来
     * @param $path
     * @return array
     */
    public static function imgToBase($path)
    {
        $img_base64 = '';
        if (file_exists(ROOT_PATH.'public'.$path)) {
            $app_img_file = ROOT_PATH.'public'.$path; // 图片路径
            $img_info = getimagesize($app_img_file); // 取得图片的大小，类型等
            $fp = fopen($app_img_file, "r"); // 图片是否可读权限
            /* 如果文件可读就进行base64转换操作 */
            if ($fp) {
                $filesize = filesize($app_img_file);
                $content = fread($fp, $filesize);
                $file_content = chunk_split(base64_encode($content)); // base64编码
                switch ($img_info[2]) {           //判读图片类型
                    case 1: $img_type = "gif";
                        break;
                    case 2: $img_type = "jpg";
                        break;
                    case 3: $img_type = "png";
                        break;
                    default: $img_type = 'jpg';
                        break;
                }
//                $img_base64 = 'data:image/' . $img_type . ';base64,' . $file_content;//合成图片的base64编码
                $img_base64 = $file_content;//合成图片的base64编码
            }
            fclose($fp);
        }
        return array($img_base64); //返回图片的base64
    }
}