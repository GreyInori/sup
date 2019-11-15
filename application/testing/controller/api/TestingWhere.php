<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/6
 * Time: 2:13
 */

namespace app\testing\controller\api;

use think\Controller;

/**
 * Class TestingWhere
 * @package app\testing\controller\api
 */
class TestingWhere extends Controller
{
    private $where = array(
        'st.trust_id' => ['st.trust_id','='],
        'st.is_allow' => ['st.is_allow','='],
        'sts.supervision_id' => ['sts.supervision_id','LIKE','code%'],
        'st.pre_testing_company' => ['st.pre_testing_company','LIKE','%code%'],
        'st.input_testing_company' => ['st.input_testing_company','LIKE','%code%'],
        'st.type_name' => ['st.smt.type_name','LIKE','code%'],
        'sm.material_name' => ['sm.material_name','LIKE','%code%'],
        'st.trust_code' => ['st.trust_code','LIKE','%code%'],
        'se.engineering_name' => ['se.engineering_name','LIKE','%code%'],
        'st.custom_company' => ['st.custom_company','LIKE','%code%'],
        'st.input_time' => ['st.input_time','>','time'],
        'sts.testing_status' => ['sts.testing_status','='],
        'sts.testing_process' => ['sts.testing_process','='],
        'sts.sample_pic' => ['sts.sample_pic','='],
        'sts.scene_pic' => ['sts.scene_pic','='],
        'sts.scene_video' => ['sts.scene_video','='],
        'sts.data_file' => ['sts.data_file','='],
        'sts.curve_file' => ['sts.curve_file','='],
        'sts.video_file' => ['sts.video_file','='],
        'sts.testing_report' => ['sts.testing_report','='],
        'sts.testing_change' => ['sts.testing_change','='],
        'sts.testing_error' => ['sts.testing_error','='],
        'sts.gather_change' => ['sts.gather_change','='],
        'sts.gather_unsaved' => ['sts.gather_unsaved','='],
        'sts.gather_renewal' => ['sts.gather_renewal','='],
        'sts.repeat_testing' => ['sts.repeat_testing','='],
        'sts.repeat_report' => ['sts.repeat_report','='],
        'sts.tester_unsigned' => ['sts.tester_unsigned','='],
        'sts.tester_absent' => ['sts.tester_absent','='],
        'sts.receive_time' => ['sts.receive_time','>','time'],
        'sts.testing_time' => ['sts.testing_time','>','time'],
        'sts.data_upload_time' => ['sts.data_upload_time','>','time'],
        'sts.data_upload_TD' => ['sts.data_upload_TD','>','time'],
        'sts.report_time' => ['sts.report_time','>','time'],
        'sts.report_upload_time' => ['sts.report_upload_time','>','time'],
        'sts.report_upload_TD' => ['sts.report_upload_TD','>','time'],
    );

    /**
     * 根据传递的参数返回指定的查询条件
     * @param array $where
     * @return mixed
     */
    public function getWhereArray($where = array())
    {
        return $this->creatQueryCode($where);
    }

    /**
     * @param array $where
     * @return array
     */
    private function creatQueryCode($where = array())
    {
        $result = array();
        $resultWhere = array();
        /* 循环传递过来的查询条件，比对已经有了的字段，进行查询匹配 */
        foreach($where as $key => $row) {
            $whereCode = $this->whereChange($key, $row);
            if($whereCode != 'fail'){
                array_push($result, $whereCode);
            }
        }
        /* 由于查询数组需要的查询条件是键值对数组，因此需要把获取到的索引数组转换为 查询字段 => 查询条件 键值对格式 */
        foreach($result as $row) {
            foreach($row as $valueKey => $valueRow) {
                $resultWhere[$valueKey] = $valueRow;
            }
        }
        return $resultWhere;
    }

    /**
     * 生成详细的查询条件方法
     * @param $key
     * @param $row
     * @return array|string
     */
    private function whereChange($key, $row)
    {
        $where = $this->where;
        $conditions = 'fail';
        /* 根据事先固定的字段信息，判断当前查询请求字段是否存在 */
        if(isset($where[$key])) {
            /* 如果查询结果是单个值的数据的话，就进行制定查询条件匹配，由于事先定义好了 LIKE 查询的条件了，就根据制定字段的条件来生成需要的查询条件 */
            if(!is_array($row)){
                $conditions = array($where[$key][0] => array($where[$key][1]));
                if(isset($where[$key][2]) && $where[$key][1] == "LIKE"){
                    $row = str_replace('code',$row,$where[$key][2]);
                }elseif(isset($where[$key][2]) && $where[$key][2] == 'time'){
                    $row = strtotime($row);
                }
                array_push($conditions[$where[$key][0]], $row);
            }
        }
        return $conditions;
    }
}