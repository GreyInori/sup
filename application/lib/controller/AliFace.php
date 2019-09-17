<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/17
 * Time: 16:13
 */

namespace app\lib\controller;

use think\Controller;

/**
 * Class AliFace
 * @package app\lib\controller
 */
class AliFace extends Controller
{
    private static $akId = 'LTAI4FrwBbR9HWdL9ULpV1D4';
    private static $akSecret = 'IabJ6iVoszRE7weyaVfmrXMaBt9R2u';
    private static $url = 'https://dtplus-cn-shanghai.data.aliyuncs.com/face/verify';

    /**
     * 进行人脸识别操作
     * @param $img1
     * @param $img2
     * @return false|string
     */
    public static function toFaceVerify($img1,$img2)
    {
        $option = self::getFaceOption($img1,$img2);
        $sign = self::getStringToSign($option);
        $signature = self::getSignature($sign);
        $authHeader = "Dataplus".self::$akId.":".$signature;
        $option['http']['header']['authorization'] = $authHeader;
        $option['http']['header'] = implode(
            array_map(
                function($key, $val) {
                    return $key.":".$val."\r\n";
                },
                array_keys($option['http']['header']),
                $option['http']['header']
            )
        );
        $context = stream_context_create($option);
        $file = file_get_contents(self::$url, false, $context);
        return $file;
    }

    /**
     * 生成请求头
     * @param $img1
     * @param $img2
     * @return array
     */
    private static function getFaceOption($img1, $img2)
    {
        /* 请求参数 */
        $data = array(
            'type' => 1,
            'content_1' => $img1,
            'content_2' => $img2
        );
        /* 创建请求头数据 */
        $options = array(
            'http' => array(
                'header' => array(
                    'accept'=> "application/json",
                    'content-type'=> "application/json",
                    'date'=> gmdate("D, d M Y H:i:s \G\M\T"),
                    'authorization' => "",
                ),
                'method' => "POST", //可以是 GET, POST, DELETE, PUT
                'content' => json_encode($data) //如有数据，请用json_encode()进行编码
            )
        );
        return $options;
    }

    /**
     * 生成StringToSign
     * @param $options
     * @return string
     */
    private static function getStringToSign($options)
    {
        $http = $options['http'];
        $header = $http['header'];
        $urlObj = parse_url(self::$url);
        if(empty($urlObj['query'])){
            $path = $urlObj['path'];
        }else{
            $path = $urlObj['path']."?".$urlObj['query'];
        }
        $body = $http['content'];
        if(empty($body)){
            $bodymd5 = $body;
        }else{
            $bodymd5 = base64_encode(md5($body, true));
        }
        $stringToSign = $http['method']."\n".$header['accept']."\n".$bodymd5."\n".$header['content-type']."\n".$header['date']."\n".$path;
        return $stringToSign;
    }

    /**
     * 生成$signature
     * @param $stringToSign
     * @return string
     */
    public static function getSignature($stringToSign)
    {
        $signature = base64_encode(
            hash_hamc(
                "sha1",
                $stringToSign,
                self::$akSecret,
                true
            )
        );
        return $signature;
    }
}