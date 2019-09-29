<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/2
 * Time: 0:21
 */

namespace app\trust\controller\api;

use think\Controller;
use think\Db;
use \app\trust\model\TrustModel as TrustModel;
use \app\trust\controller\TrustAutoLoad as TrustAutoload;
use \app\lib\controller\Picture;

/**
 * Class TrustMain
 * @package app\trust\controller\api
 */
class TrustMain extends Controller
{
    use Picture;
    // +----------------------------------------------------------------------
    // | 委托单相关
    // +----------------------------------------------------------------------
    /**
     * 获取监理人对应的委托单列表方法
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toPersonTrust()
    {
        $data = request()->param();
        if(!isset($data['divideUser'])) {
            return '请传递监理人用户名';
        }
        if(!isset($data['dividePass'])) {
            return '请传递监理人密码';
        }
        /* 根据传递的账号和密码，判断在创建工程时有无创建对应的账号密码用户，如果有就拿工程名和id获取委托单列表 */
        $list = Db::table('su_engineering_divide')
                ->alias('sed')
                ->join('su_engineering se','se.engineering_id = sed.engineering_id')
                ->join('su_divide sd','sd.divide_id = sed.divide_id')
                ->where(['divide_user'=>$data['divideUser'],'divide_passwd'=>md5($data['dividePass'])])
                ->field(['se.engineering_id','se.engineering_name','sd.divide_name','sd.divide_id'])
                ->select();
        if(empty($list)) {
            return '当前监理人账号下尚未分配工程，请检查或者联系管理员';
        }
        /* 获取指定的委托单列表，并处理成前端传递过来的格式返回出去 */
        $trust = Db::table('su_trust')
                    ->where(['engineering_id'=>$list[0]['engineering_id']])
                    ->field(['trust_id','trust_code','testing_name'])
                    ->select();
        if(empty($trust)) {
            return '当前工程下尚未创建委托单，请检查或联系管理员';
        }
        $result = array();

        foreach($trust as $key => $row) {
            $row['engineering_name'] = $list[0]['engineering_name'];
            $row = self::fieldChange($row);
            array_push($result,$row);
        }
        return $result;
    }

    /**
     * 根据企业id获取对应的委托单方法
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toCompanyTrust()
    {
        $data = request()->param();
        if(!isset($data['company'])) {
            return '请传递企业id';
        }
        $list = Db::table('su_engineering_divide')
                ->where('member_id',$data['company'])
                ->field(['engineering_id'])
                ->select();
        if(empty($list)) {
            return '当前企业尚未分配到工程，请检查传递的企业id';
        }
        $engineerStr = "";
        foreach($list as $key => $row) {
            $engineerStr .= "{$row['engineering_id']},";
        }
        $trust = Db::table('su_status_file')->where('file_file',null)->field(['trust_id'])->select();
        $trustStr = '';
        foreach($trust as $key => $row) {
            $trustStr .= "{$row['trust_id']},";
        }
        $engineerStr = rtrim($engineerStr,',');

        $trustList = Db::table('su_trust')
                        ->alias('st')
                        ->join('su_engineering se','se.engineering_id = st.engineering_id')
                        ->where('st.engineering_id','IN',$engineerStr)
                        ->field(['st.engineering_id','st.trust_id','st.trust_code','se.engineering_name','st.serial_number','st.testing_result','st.input_time'])
                        ->where('trust_id','IN',rtrim($trustStr,','))
                        ->where('st.show_type',1)
                        ->order('st.input_time DESC')
                        ->select();
        if(!empty($trustList)) {
            foreach ($trustList as $key => $row) {
                $trustList[$key]['input_time'] = date('Y-m-d H:i:s',$row['input_time']);
            }
        }
        return $trustList;
    }

    /**
     * 执行委托单添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTrustAdd($data)
    {
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $trust = $data['trust'];
        $trust['trust_id'] = $uuid[0];
//        if(isset($trust['input_time'])) {
            $trust['input_time'] = time();
//        }
        $engineering = Db::table('su_engineering')
                            ->where(['engineering_id'=>$data['trust']['engineering_id'],'engineering_verify'=>1])
                            ->field(['engineering_verify'])
                            ->select();
        if(empty($engineering)) {
            return '当前工程尚未审核，请等审核后再进行委托单录入操作';
        }
        /* 如果传递了检测项目id的话，就根据检测项目获取相关的检测类型id，创建检测项目类型数据 */
        if(isset($trust['testing_material'])) {
            $material = Db::table('su_material')->where('material_id',$trust['testing_material'])->field(['material_type'])->select();
            $materialType = Db::table('su_material_type')->where('type_id',$material[0]['material_type'])->field(['type_pid'])->select();
            if(!empty($materialType)) {
                $trust['testing_type'] = $materialType[0]['type_pid'];
            }
        }
        $trust['trust_code'] = self::creatCode();
        $trust['serial_number'] = $trust['trust_code'];
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            Db::table('su_trust')->insert($trust);
            $Upload = self::fetchTrustUpload($trust,$uuid[0]);
            /* 创建委托测试过程数据 */
            $testing = self::testingCreate($trust);
            Db::table('su_testing_status')->insert($testing);
            if(!is_array($Upload)) {
                return $Upload;
            }
            Db::commit();
            return array('uid'=>$uuid[0]);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行委托单修改方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTrustEdit($data)
    {
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $trust = $data['trust'];
        if(isset($trust['input_time'])) {
            $trust['input_time'] = strtotime($trust['input_time']);
        }
        if(isset($trust['trust_id'])) {
            unset($trust['trust_id']);
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            $update = Db::table('su_trust')->where('trust_id',$uuid[0])->update($trust);
            Db::commit();
            return array($update);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行委托单删除操作
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTrustDel($data)
    {
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            $delete = Db::table('su_trust')->where('trust_id',$uuid[0])->update(['show_type'=>0]);
            Db::commit();
            return array($delete);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 执行委托单收样操作
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function toTrustAllow($data)
    {
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $list = Db::table('su_status_file')->where('file_code',$data['upload']['file_code'])->field(['trust_id'])->select();
        if(empty($list)) {
            return '查无此二维码相关委托单,请检查传递的二维码';
        }
        $allow = Db::table('su_trust')->where('trust_id',$list[0]['trust_id'])->field(['is_allow'])->select();
        if(!empty($allow) && $allow[0]['is_allow'] == 1){
            return '当前样品已收样';
        }
        /* 进行企业以及企业详细信息的添加操作 */
        Db::startTrans();
        try{
            $allow = Db::table('su_trust')->where('trust_id',$list[0]['trust_id'])->update(['is_allow'=>1]);
            Db::table('su_testing_status')->where('trust_id',$list[0]['trust_id'])->update(['receive_time'=>time(),'testing_process'=>3]);
            Db::commit();
            return array($allow);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 委托记录字段相关
    // +----------------------------------------------------------------------
    /**
     * 执行委托记录字段默认值添加方法
     * @param $data
     * @return array|string
     * @throws \think\exception\DbException
     *
     */
    public static function toTrustMaterialAdd($data)
    {
        $group = new TrustAutoLoad();
        $list = request()->param();
        unset($list['action']);
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data,1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        /* 根据传递的值创建委托单记录详细插入数据 */
        $trustMaterial = self::fetchTrustMaterial($list,$uuid[0]);
        if(!is_array($trustMaterial)) {
            return $trustMaterial;
        }
        /* 执行默认值添加操作 */
        Db::startTrans();
        try{
            $insert = Db::table('su_trust_list_default')->insertAll($trustMaterial);
            Db::commit();
            return array('uid'=>$insert);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * 生成委托单记录插入数据方法
     * @param $list
     * @param $uuid
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchTrustMaterial($list, $uuid)
    {
        $result = array();
        if(!isset($list['save'])) {
            return '请传递委托单记录数据';
        }
        $save = Db::table('su_trust_list_default')
            ->where('trust_id',$uuid)
            ->field(['save_id'])
            ->order('save_id','DESC')
            ->limit(0,1)
            ->select();
        $saveId = 1;
        if(!empty($save)) {
            $saveId += $save[0]['save_id'];
        }
        foreach($list['save'] as $key => $row) {
            $result[$key] = array(
                'trial_default_value' => $row['trialValue'],
                'trial_default_token' => $row['trialToken'],
                'default_id' => $row['default'],
                'trial_verify' => $row['trialVerify'],
                'trial_id' => $row['trial'],
                'trust_id' => $uuid,
                'save_id' => $saveId
            );
        }
        return $result;
    }

    /**
     * 获取委托单下对应的记录字段数据
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTrustSave()
    {
        $data = request()->param();
        if(!isset($data['trust'])) {
            return '请传递需要获取记录信息的委托单号';
        }
        $list = Db::table('su_trust')
                    ->alias('st')
                    ->join('su_trust_list_default stld','stld.trust_id = st.trust_id')
                    ->where('st.trust_id',$data['trust'])
                    ->field(['st.testing_material'])
                    ->select();
        if(empty($list)) {
            return '当前委托单尚未添加记录信息，请先添加后再进行查询';
        }
        /* 执行查询操作 */
        $list = Db::table('su_material_list')
                ->alias('sml')
                ->join('su_material sm','sm.material_id = sml.material_id ')
                ->where('sml.material_id',$list[0]['testing_material'])
                ->where('sml.show_type',1)
                ->field(['sml.trial_id','sml.trial_name','sml.trial_depict','sml.trial_default_hint','sml.trial_custom_hint','sm.testing_code'])
                ->select();
        if(empty($list)) {
            return $list;
        }

        /* 根据字段获取对应的默认值 */
        $list = self::fieldDefault($list,$data['trust']);
        return $list;
    }
    // +----------------------------------------------------------------------
    // | 委托单号图片相关
    // +----------------------------------------------------------------------
    /**
     * 进行委托单号需要上传的图片添加占位操作
     * @param $data
     * @param $uuid
     * @return array|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fetchTrustUpload($data, $uuid)
    {
        /* 根据委托单号查询获取到当前委托单需要上传的个图片数量列表 */
        if(!isset($data['testing_material'])) {
            return '请传递委托单相关的检测项目';
        }
        $block = Db::table('su_material')
                    ->where(['material_id'=>$data['testing_material'],'show_type'=>1])
                    ->field(['block_type'])
                    ->select();

        if(empty($block)) {
            return '当前检测项目下尚未存在上传图片规定,请先进行添加';
        }
        $upload = Db::table('su_material_upload')
                        ->where(['block_type'=>$block[0]['block_type'],'show_type'=>1])
                        ->field(['block_id','block_type','upload_type'])
                        ->select();
        /* 根据查询结果获取到当前试块需要上传的图片类型以及数量，转换成图片插入数组 */
        $uploadArr = array();
        foreach($upload as $key => $row) {
            $uploadArr[$key] = array(
                'trust_id' => $uuid,
                'file_type' => $row['upload_type'],
            );
        }
        if(!empty($uploadArr)) {
           Db::table('su_status_file')->insertAll($uploadArr);
        }
        return array(1);
    }

    /**
     * 根据委托单号获取委托单对应的图片信息方法
     * @param $data
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTrustUploadList($data)
    {
        $url = request()->domain();
        $group = new TrustAutoLoad();
        $data = $group->toGroup($data);
        $uuid = self::trustAlreadyCreat($data, 1);
        if(!is_array($uuid)) {
            return $uuid;
        }
        $uuid = $uuid[0];
        $UploadList = Db::table('su_status_file')
            ->alias('ssf')
            ->join('su_testing_file_type stft','stft.type_id=ssf.file_type')
            ->where(['ssf.trust_id'=>$uuid])
            ->field(['ssf.file_id','ssf.file_file','ssf.file_depict','ssf.file_type','ssf.file_time','ssf.file_code','ssf.upload_people','stft.type_name','stft.type_depict'])
            ->order('stft.type_id')
            ->select();
        foreach($UploadList as $key => $row) {
            if($row['file_file'] !== null) {
                $UploadList[$key]['file_file'] = $url.$row['file_file'];
            }
        }

        if(empty($UploadList)) {
            return '当前委托单所属分类尚未存在图片上传规则，请检查传递的委托单id';
        }
        return $UploadList;
    }

    /**
     * 根据二维码获取二维码绑定的图片信息
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTrustUploadForCode()
    {
        $url = request()->domain();
        $data = request()->param();
        /* 根据二维码查询对应的图片文件信息 */
        $code = $data['qrcode'];
        $UploadList = Db::table('su_status_file')
                          ->alias('ssf')
                          ->join('su_testing_file_type stft','stft.type_id=ssf.file_type')
                          ->where(['ssf.file_code'=>$code])
                          ->field(['ssf.trust_id','ssf.file_id','ssf.file_file','ssf.file_depict','ssf.file_type','ssf.file_time','ssf.file_code','ssf.upload_people','stft.type_name','stft.type_depict'])
                          ->order('stft.type_id')
                         ->select();
        /* 如果该文件数据存在图片的话，就把域名拼接到图片路径上 */
        foreach($UploadList as $key => $row) {
            if($row['file_file'] !== null) {
                $UploadList[$key]['file_file'] = $url.$row['file_file'];
            }
        }
        if(empty($UploadList)) {
            return '当前委托单所属分类尚未存在图片上传规则，请检查传递的委托单id';
        }
        return $UploadList;
    }

    /**
     * 执行图片上传方法
     * @param $data
     * @return array|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function toTrustUpload($data)
    {
        $group = new TrustAutoload();
        $data = $group->toGroup($data);
        /* 检测传递过来的文件id是否存在，如果不存在就返回错误信息 */
        $file = Db::table('su_status_file')
            ->where('file_id',$data['upload']['file_id'])
            ->field(['file_id','trust_id'])
            ->select();
        if(empty($file)) {
            return '查无此委托单检测项目图片信息, 请检查传递的图片id';
        }
        $testing = Db::table('su_testing_status')
            ->where('trust_id',$file[0]['trust_id'])
            ->field(['testing_status'])
            ->select();
        if(empty($testing)) {
            return '查无此检测项目信息';
        }
        $testUpdate = array(
            'testing_process' => 2,
            'sample_time' => time(),
            'sample_pic' => 1
        );
        /* 执行图片上传操作，如果上传失败就返回错误信息，如果成功就根据传值以及当前时间创建图片文件修改数据 */
        $pic = self::toImgUp('file','pic');
        if(!is_array($pic)) {
            return $pic;
        }
        $updateArr = array(
            'file_file' => $pic['pic'],
            'file_time' => time(),
        );
        foreach($data['upload'] as $key => $row) {
            if($key != 'file_file' || $key != 'file_time')
                $updateArr[$key] = $row;
        }
        /* 检测传递的二维码是否符合规范 */
        $qrCode = Db::table('su_qrcode')
                    ->where('qr_code',$updateArr['file_code'])
                    ->field(['is_use','trust_id'])
                    ->select();
        if(empty($qrCode)) {
            return '查无此二维码，请检查传递的二维码';
        }
        if($qrCode[0]['is_use'] == 1 && $qrCode[0]['trust_id'] !== $file[0]['trust_id']) {
            return '当前二维码已经被使用，请检查传递的二维码';
        }
        $url = request()->domain();
        /* 执行图片上传数据修改操作 */
        Db::startTrans();
        try {
            Db::table('su_status_file')
                ->where('file_id',$data['upload']['file_id'])
                ->update($updateArr);
            /* 进行委托检测信息修改 */
            Db::table('su_testing_status')
                ->where('trust_id',$file[0]['trust_id'])
                ->update($testUpdate);
            Db::table('su_trust')
                ->where('trust_id',$file[0]['trust_id'])
                ->update(['is_sample'=>1]);
            /* 标记当前二维码为已使用 */
            Db::table('su_qrcode')
                ->where('qr_code',$updateArr['file_code'])
                ->update(['is_use'=>1,'trust_id'=>$file[0]['trust_id']]);
            Db::commit();
            return array($url.$updateArr['file_file']);
        }catch(\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
    // +----------------------------------------------------------------------
    // | 辅助相关
    // +----------------------------------------------------------------------
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
        $path = ROOT_PATH.'public'.DS.'static'.DS.'images'.DS.'trustPeople'.DS."{$time}";
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
     * 获取委托单下对应的记录数据
     * @param $list
     * @param $trust
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function fieldDefault($list,$trust)
    {
        $result = array();
        $defaultStr = "";
        $defaultArr = array();
        /* 根据已经查询出来的检测字段数据缩小默认值的查询范围，获取需要的默认值 */
        foreach($list as $key => $row) {
            $defaultStr .= "{$row['trial_id']},";
        }
        $defaultStr = rtrim($defaultStr,',');
        $defaultList = Db::table('su_trust_list_default')
            ->where('trial_id','IN',$defaultStr)
            ->where('show_type',1)
            ->where('trust_id',$trust)
            ->field(['save_id','default_id','trial_id','trial_default_value','trial_default_token','trial_verify'])
            ->order('save_id ASC')
            ->select();
        if(empty($defaultList)) {
            return $list;
        }
        /* 把所有默认值转换成 字段id => 结果数组 的格式，方便匹配到字段下
            顺便把数据库字段转换成前端传递过来的字段
         */
        $saveArr = array();     // 记录的组号数组
        foreach($defaultList as $key => $row) {
            if(!isset($defaultArr[$row['trial_id']])) {
                $defaultArr[$row['trial_id']] = array();
            }
            /* 根据查询出来的组号创建对应的组号数组用于后期的分类 */
            if(!isset($defaultArr[$row['save_id']])) {
                $saveArr[$row['save_id']] = array();
            }
            $row = self::fieldChange($row);
            $defaultArr[$row['save'] - 1][$row['trial']] = $row;
        }
        /* 把对应组号的值分配到对应的组号数组下， 由于组号是从 1 开始的，转换为索引数组需要 -1 */
        foreach($saveArr as $saveKey => $saveRow) {
            if(!isset($result[$saveKey - 1])) {
                $result[$saveKey - 1] = array();
            }
            /* 根据查询项目下的组号 save_id 把对应数据分配到 键值是对应的 save_id 下的 $result 数组下 */
            foreach($list as $key => $row) {
                $result[$saveKey - 1][$key] = self::fieldChange($row);
                if(isset($defaultArr[$saveKey - 1][$row['trial_id']])){          // 把检测项目结果类型id 分配到对应的类型id父类下
                    $result[$saveKey - 1][$key]['default'] = $defaultArr[$saveKey - 1][$row['trial_id']];
                }
            }
        }

        return $result;
    }
    /**
     * 生成工程编号方法
     * @return string
     */
    private static function creatCode()
    {
        $str = 'WT';
        $timeStr = date('ymd');
        $rand = rand(100,999);
        return $str.$timeStr.$rand;
    }

    /**
     * 创建委托测试单数组
     * @param $trust
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    private static function testingCreate($trust)
    {
        $insert = array(
            'supervision_id' => (date('Ymdhi').rand(100,999)),
            'trust_progress' => md5(uniqid(mt_rand(),true)),
            'engineering_id' => $trust['engineering_id'],
            'trust_id' => $trust['trust_id'],
            'sample_time' => time()
        );
        /* 如果传递了检测项目的话，就给当前委托单添加对应的检测分类id */
        if(isset($trust['testing_material'])) {
            $type = Db::table('su_material')->where('material_id',$trust['testing_material'])->field(['material_type'])->select();
            $type = Db::table('su_material_type')->where('type_id',$type[0]['material_type'])->field(['type_pid'])->select();
            Db::table('su_trust')->where('trust_id',$trust['trust_id'])->update(['testing_quality'=>$type[0]['type_pid']]);
        }
        return $insert;
    }

    /**
     * 检测传递委托单信息是否有误，以及是否存在方法
     * @param $data
     * @param int $token
     * @return array|string
     * @throws \think\exception\DbException
     */
    private static function trustAlreadyCreat($data, $token = 0)
    {
        $uuid = md5(uniqid(mt_rand(),true));
        if($token == 0){
            return array($uuid);
        }
        if(!isset($data['trust'])) {
            return '请传递需要添加的委托信息';
        }
        /* 检测企业是否以及存在，如果不存在，就通过 uniqid 生成唯一id返回给方法调用 */
        $trust = $data['trust'];
        if($token == 1){
            $list = TrustModel::get(['trust_id' => $trust['trust_id'],'show_type'=>1]);
        }
        /* 检测委托是否存在并如果是修改之类的操作的话就需要返回查询出来的委托id进行返回 */
        if(!empty($list) && $token == 1){
            return array($trust['trust_id']);
        }elseif($token ==  1){
            return '查无此委托,请传递正确的委托单号';
        }
        return array($uuid);
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

    /**
     * 转换查询结果内字段方法
     * @param $list
     * @return array
     */
    public static function fieldChange($list)
    {
        $result = array();
        $field = new TrustAutoLoad();
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
            if(strstr($key,'_time') && is_int($row)) {
                $row = date('Y-m-d H:i:s',$row);
            }elseif(strstr($key,'_file')) {
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
}