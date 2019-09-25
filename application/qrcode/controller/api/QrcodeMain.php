<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/9/12
 * Time: 11:26
 */

namespace app\qrcode\controller\api;

use think\Controller;
use think\Db;
use \app\qrcode\model\QrcodeModel as QrcodeModel;
use \app\qrcode\controller\QrcodeAutoLoad as QrcodeAutoLoad;

/**
 * Class QrcodeMain
 * @package app\qrcode\controller\api
 */
class QrcodeMain extends Controller
{
    // +----------------------------------------------------------------------
    // | 客户相关
    // +----------------------------------------------------------------------
    /**
     * 添加企业方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toCompanyAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new QrcodeAutoLoad();
        $check = $group->toGroup($data);
        /* 检测当前企业是否存在 */
        $uuid = self::companyAlreadyCreat($check);
        if(!is_array($uuid)) {
            return $uuid;
        }
        if(isset($check['company']['company_id'])){
            unset($check['company']['company_id']);
        }
        /* 进行企业添加操作 */
        Db::startTrans();
        try{
            $id = Db::table('su_qrcode_company')->insertGetId($check['company']);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行企业修改操作
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toCompanyEdit($data)
    {
        $group = new QrcodeAutoLoad();
        /* 把传递过来的根据数据表进行分组，用于后续插入和检测操作 */
        $check = $group->toGroup($data);
        /* 检测当前企业是否存在 */
        $uuid = self::companyAlreadyCreat($check,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        if(isset($check['company']['company_id'])){
            unset($check['company']['company_id']);
        }
        /* 进行企业修改操作 */
        Db::startTrans();
        try{
            $update = Db::table('su_qrcode_company')->where('company_id',$uuid[0])->update($check['company']);
            Db::commit();
            return array($update);
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行企业删除操作
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toCompanyDel($data)
    {
        $group = new QrcodeAutoLoad();
        /* 把传递过来的根据数据表进行分组，用于后续插入和检测操作 */
        $check = $group->toGroup($data);
        /* 检测当前企业是否存在 */
        $uuid = self::companyAlreadyCreat($check,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        if(isset($check['company']['company_id'])){
            unset($check['company']['company_id']);
        }
        /* 进行企业修改操作 */
        Db::startTrans();
        try{
            $update = Db::table('su_qrcode_company')->where('company_id',$uuid[0])->update(['show_type'=>0]);
            Db::commit();
            return array($update);
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 业务类型相关
    // +----------------------------------------------------------------------
    /**
     * 执行业务类型添加
     * @param $data
     * @return array|string
     */
    public static function toWorkTypeAdd($data)
    {
        /* 把传递过来的数据根据数据表进行分组，用于后续插入和检测等操作 */
        $group = new QrcodeAutoLoad();
        $check = $group->toGroup($data);
        if(isset($check['work']['work_id'])){
            unset($check['work']['work_id']);
        }
        /* 进行企业添加操作 */
        Db::startTrans();
        try{
            $id = Db::table('su_qrcode_work')->insertGetId($check['work']);
            Db::commit();
            return array('uid'=>$id);
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行业务类型修改操作
     * @param $data
     * @return array|string
     */
    public static function toWorkTypeEdit($data)
    {
        $group = new QrcodeAutoLoad();
        /* 把传递过来的根据数据表进行分组，用于后续插入和检测操作 */
        $check = $group->toGroup($data);
        $uuid = $check['work']['work_id'];
        /* 进行企业修改操作 */
        Db::startTrans();
        try{
            $update = Db::table('su_qrcode_work')->where('work_id',$uuid)->update($check['work']);
            Db::commit();
            return array($update);
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行业务类型删除操作
     * @param $data
     * @return array|string
     */
    public static function toWorkTypeDel($data)
    {
        $group = new QrcodeAutoLoad();
        /* 把传递过来的根据数据表进行分组，用于后续插入和检测操作 */
        $check = $group->toGroup($data);
        $uuid = $check['work']['work_id'];
        /* 进行企业修改操作 */
        Db::startTrans();
        try{
            $update = Db::table('su_qrcode_work')->where('work_id',$uuid)->update(['show_type'=>0]);
            Db::commit();
            return array($update);
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 二维码相关
    // +----------------------------------------------------------------------
    /**
     * 执行二维码列表添加操作
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toQrcodeCreate()
    {
        $data = request()->param();
        $insertArr = array();
        /* 根据id获取对应的企业以及业务类型编码 */
        $code = self::fetchCode($data);
        if(!is_array($code)){
            return $code;
        }
        /* 获取序列号初始值 */
        $first = self::getFirstNum($code);
        for($num = 0; $num < $data['codeNum']; $num++) {
            $insertArr[$num] = array(
                'company_code' => $code['company'],
                'work_code' => $code['work'],
                'qr_time' => date('ymd'),
                'check_code' => $code['company'].$code['work'].rand(1000,9999),
                'rand_code' => rand(1000,9999)
            );
            /* 根据编码和序列号生成二维码编码 */
            $numCode = self::qrcodeNumberCreat($first+$num+1);
            $insertArr[$num]['qr_code'] = $code['company'].$code['work'].$insertArr[$num]['qr_time'].$numCode.$insertArr[$num]['rand_code'];
            $insertArr[$num]['qr_number'] = $numCode;
            /* 根据二维码编码生成对应的二维码图片，并保存到数据库 */
            $qrcode = self::curlUrl('http://jiance.server2.puankang.com.cn/qrcode/QrcodePng', ['ewmcode'=>$insertArr[$num]['qr_code']]);
            $insertArr[$num]['qr_path'] = self::creatFile($insertArr[$num]['qr_code'], $qrcode);
        }
        /* 执行添加操作，如果成功就返回插入的二维码列表 */
        Db::startTrans();
        try{
            Db::table('su_qrcode')->insertAll($insertArr);
            Db::commit();
            return $insertArr;
        }catch(\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 辅助相关
    // +----------------------------------------------------------------------
    /**
     * 获取销售客户以及业务类型编码
     * @param $data
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchCode($data)
    {
        $company = Db::table('su_qrcode_company')->where('company_id',$data['company'])->field(['company_code'])->select();
        $work = Db::table('su_qrcode_work')->where('work_id',$data['work'])->field(['work_code'])->select();
        /* 如果查询没有结果就返回错误信息，否则返回企业和业务类型的代码 */
        if(empty($company)) {
            return '查无指定的客户企业';
        }
        if(empty($work)) {
            return '查无指定的业务类型';
        }
        return array('work'=>$work[0]['work_code'],'company'=>$company[0]['company_code']);
    }

    /**
     * 获取二维码序列号起始值
     * @param $data
     * @return int|string
     * @throws \think\Exception
     */
    private static function getFirstNum($data)
    {
        $where = array(
            'work_code' => $data['work'],
            'qr_time' => date('ymd'),
            'company_code' => $data['company']
        );
        $list = Db::table('su_qrcode')
                ->where($where)
                ->count();
        return $list;
    }

    /**
     * 把二维码的序列号填充成指定长度
     * @param $num
     * @return string
     */
    private static function qrcodeNumberCreat($num)
    {
        $len = 8 - strlen($num);
        if($len == 0) {
            return $num;
        }
        $num = str_pad($num,8,"0",STR_PAD_LEFT);
        return $num;
    }

    /**
     * 检测企业是否存在
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function companyAlreadyCreat($data, $token = 0)
    {
        if(!isset($data['company'])) {
            return '请传递需要录入的企业信息';
        }
        if(!isset($data['company']['company_name']) && $token == 0) {
            return '请传递需要添加的企业的名称';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $company = $data['company'];
        if($token == 1){
            $list = QrcodeModel::get(['company_id' => $company['company_id']]);
        }else{
            $list = QrcodeModel::get(['company_name' => $company['company_name']]);
        }
        /* 检测工程是否存在并如果是修改之类的操作的话就需要返回查询出来的工程id进行返回 */
        if(!empty($list) && $token == 0){
            return '当前添加的企业已存在，请检查填写的企业名称';
        }elseif(!empty($list) && $token == 1){
            return array($company['company_id']);
        }elseif($token ==  1){
            return '查无此企业，请检查传递的企业id';
        }
        $uuid = md5(uniqid(mt_rand(),true));
        return array($uuid);
    }

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new QrcodeAutoLoad();
        $field = $field::$fieldArr;        // 用于比较转换的数组字段
        /* 如果是索引数组的话就需要对数组内所有数据的字段进行转换，否则就直接对数组内值进行转换 */
        if(!self::is_assoc($list)) {
            foreach($list as $key => $row) {
                $result[$key] = self::toFieldChange($row, $field);
            }
        }else {
            $result = self::toFieldChange($list, $field);
        }
        return $result;
    }

    /**
     * 把数据库字段转换为前端传递的字段返回
     * @param $list
     * @param $check
     * @return array
     */
    private static function toFieldChange($list, $check)
    {
        $result = array();
        foreach($list as $key => $row) {
            if(strchr($key,'path')) {
                $url = request()->domain();
                $row = $url.$row;
            }
            $result[array_search($key, $check)] = $row;
        }
        return $result;
    }

    /**
     * 检测数组是否为索引数组
     * @param $arr
     * @return bool
     */
    private static function is_assoc($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * 创建文件方法
     * @param $fileName
     * @param $content
     * @return int|string
     */
    public static function creatFile($fileName, $content)
    {
        /* 根据上传日期生成指定的文件夹 */
        $time = date('Ymd');
        $path = ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.'qrcode'.DS."{$time}";
        if(!is_dir($path)) {
            mkdir($path,0755);
        }
        /* 创建文件并写入数据 */
        $filePath = $path."/{$fileName}.jpg";
        try{
            $file = fopen($filePath, 'w');
            fwrite($file, $content);
            fclose($file);
        }catch(\Exception $e){
            return $e->getMessage();
        }
        return strchr($filePath, '/static');
    }

    /**
     * curl地址方法
     * @param string $url
     * @param array $value
     * @return bool|string
     */
    public static function curlUrl($url = '',$value = [])
    {
        $curl = curl_init();
        /* get传输方法带值curl微信消息推送接口 */
        if(!empty($value)){
            $url  = $url.'?'.http_build_query($value);
        }
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_HEADER,0);
        $output = curl_exec($curl);
        curl_close($curl);
        return($output);
    }
}