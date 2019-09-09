<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 0:47
 */

namespace app\testing\controller;

use think\Controller;

class TestingAutoLoad extends Controller
{
    /**
     * 吧传递过来的字段转换成后台数据库对应的字段
     * @var array
     */
    public static $fieldArr = array(
        'supervision' => 'sts.supervision_id',
        'preCompany' => 'st.pre_testing_company',
        'inputCompany' => 'st.input_testing_company',
        'testingType' => 'smt.type_name',
        'testingName' => 'sm.material_name',
        'trustCode' => 'st.trust_code',
        'engineerName' => 'se.engineering_name',
        'customCompany' => 'sc.custom_company',
        'inputTime' => 'st.input_time',
        'status' => 'sts.testing_status',
        'isSample' => 'sts.sample_pic',
        'isScene' => 'sts.scene_pic',
        'isSceneVideo' => 'sts.scene_video',
        'isData' => 'sts.data_file',
        'isCurve' => 'sts.curve_file',
        'isVideo' => 'sts.video_file',
        'isReport' => 'sts.testing_report',
        'isChange' => 'sts.testing_change',
        'isError' => 'sts.testing_error',
        'isGatherChange' => 'sts.gather_change',
        'isUnsaved' => 'sts.gather_unsaved',
        'isRenewal' => 'sts.gather_renewal',
        'isRepeatTesting' => 'sts.repeat_testing',
        'isRepeatReport' => 'sts.repeat_report',
        'isUnsigned' => 'sts.tester_unsigned',
        'isAbsent' => 'sts.tester_absent',
        'receiveTime' => 'sts.receive_time',
        'testingTime' => 'sts.testing_time',
        'dataUploadTime' => 'sts.data_upload_time',
        'dataUploadTD' => 'sts.data_upload_TD',
        'reportTime' => 'sts.report_time',
        'reportUploadTime' => 'sts.report_upload_time',
        'reportUploadTD' => 'sts.report_upload_TD',
        'testingProcess' => 'sts.sts.testing_process',
        'company' => 'company_id',
        'response' => 'ste.error_response',
        'error' => 'ste.error_main',
        'errorId' => 'ste.error_id',
        'testName' => 'testing_name',
        'reportNumber' => 'sr.report_number',
        'reportMain' => 'sr.report_main',
        'trust' => 'st.trust_id',
        'result' => 'testing_result',
        'reportTimes' => 'sr.report_time',
        'materialName' => 'st.testing_name',
        'reportFile' => 'sr.report_file'
    );

    public static $fieldGroup = array(
        'testing' => array('sts.supervision_id','st.pre_testing_company','st.input_testing_company'
        ,'smt.type_name','sm.material_name'
        ,'st.trust_code','se.engineering_name'
        ,'st.custom_company','st.input_time','sts.testing_status','sts.sample_pic'
        ,'sts.scene_pic','sts.scene_video','sts.data_file','sts.curve_file','sts.video_file'
        ,'sts.testing_report','sts.testing_change'
        ,'sts.testing_error','sts.gather_change','sts.gather_unsaved'
        ,'sts.gather_renewal','sts.repeat_testing','sts.repeat_report'
        ,'sts.tester_unsigned','sts.tester_absent','sts.receive_time','sts.testing_time'
        ,'sts.data_upload_time','sts.data_upload_TD','sts.report_time'
        ,'sts.report_upload_time','sts.report_upload_TD','sts.testing_process','company_id'),
        'error' => array(
            'st.trust_id','ste.error_id','ste.error_id','st.trust_code','ste.error_main','st.input_testing_company','ste.error_response'
        ),
        'report' => array(
            'sr.report_main','st.trust_id','sr.report_number','sr.report_time'
        ),
    );

    /**
     * 根据预定义的分组数组，传递过来的数组进行分组
     * @param array $data
     * @return array|mixed
     */
    public function toGroup($data = array())
    {
        $result = array();
        /* 循环判断传递过来的数据字段是否在预定义的几个字段数组内，如果在就进行分组 */
        foreach($data  as $dataKey => $dataRow){
            foreach(self::$fieldGroup as $fieldKey => $fieldRow){
                $result = $this->inCheck($dataKey, $dataRow, $fieldKey, $fieldRow, $result);
            }
        }
        return $result;
    }

    /**
     * 判断指定字段是否在字段分类数组内，如果有，就进行分类，范回复分类后的数组
     * @param $dataKey
     * @param $dataRow
     * @param $fieldKey
     * @param $fieldRow
     * @param $data
     * @return mixed
     */
    private function inCheck($dataKey, $dataRow, $fieldKey, $fieldRow, $data)
    {
        /* 判断字段是否在指定字段分组内，如果有的话就进行分组 */
        if(in_array($dataKey, $fieldRow)){
            if(!isset($data[$fieldKey])){
                $data[$fieldKey] = array();
            }
            /* 把指定的值分组到指定的分组 */
            $data[$fieldKey][$dataKey] = $dataRow;
        }
        return $data;
    }

    /**
     * 检测传递过来的参数进行处理
     * @param string $control
     * @param string $field
     * @return array|mixed
     */
    public static function checkData($control = '', $field = '')
    {
        $companyValidate = new \app\testing\validate\TestingValidate();
        $request = request()->param();

        /* 对传递过来的参数进行制定场景 $control 来进行检测，如果不符合规则就返回错误信息，返回函数进行后面的处理 */
        $check = $companyValidate->scene($control)->check($request);

        if($check === false){
            return $companyValidate->getError();
        }
        $request = self::buildRequestField($request, $field);

        return $request;
    }

    /**
     * 把前端传递过来的参数转换为数据库对应字段返回出来
     * @param $data
     * @param array $field
     * @return array
     */
    public static function buildRequestField($data, $field = array())
    {
        $result = array();

        foreach($data as $key => $row){
            if(is_int($key)) {
                $result[$key] = array();
                foreach($row as $rowKey => $rowMain) {
                    isset(self::$fieldArr[$rowKey])?$result[$key][self::$fieldArr[$rowKey]] = $rowMain : false;
                }
            }
            /* $field 数组内的数据是不需要对应数据库字段的额外条件，进行额外条件的查询或者分页 */
            if(!empty($field) && in_array($key, $field)){
                $fieldArr[$key] = $row;
            }
            isset(self::$fieldArr[$key])?$result[self::$fieldArr[$key]] = $row : false;
        }
        return $result;
    }
}