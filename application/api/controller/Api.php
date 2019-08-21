<?php
	
	namespace app\api\controller;

	use think\Controller;
	use think\Request;

	use app\api\controller\Send;
	use app\api\controller\Oauth;

	use app\picture\controller\Picture;

	/**
	 * api入口文件基类，需要控制权限的控制器都应该继承该类
	 */
	class Api extends Controller
	{
		use Send;
		use Picture;

		/**
		 * @var \think\Request实例
		 */
		public    $request;

		protected $clientInfo;


		public function initCheck()
		{	
			$this->request = Request::instance()->param();		

			$this->request = is_array($this->request)?$this->request:json_decode($this->request,256);
			
			if(isset($this->request['data'])){

				$this->request['data'] =  is_array($this->request['data'])?$this->request['data']:json_decode($this->request['data'],256);

				// $this->request['data'] = json_decode($this->request['data'],256);
			}
			$this->init();
			// exit(var_dump(request()->file()));

			$this->uid = $this->clientInfo['uid'];
		}

		/**
		 * 初始化
		 * 
		 * 检测是否为己方客户端发起的请求
		 * @param $request['appToken']
		 */
		public function init()
		{

			$Oauth = new \app\api\controller\Oauth;

			$Oauth->authenticate($this->request);			
		}
		/**
		 * 数据转换
		 *
		 * 把传递过来的数组转换为用 ',' 拼接的字符串
		 * @param  $data         array
		 * @param  $keyField     string
		 * @param  $valField     string
		 * @param  $keyValue     int
		 * @return $changeData  array
		 */
		public static function arrTypeChange($data = [] , $keyField = '' , $valField = '' , $keyValue = '')
		{	
			/* 根据传递过来的键值字段，以及数据字段来创建数组 */
			$changeData = array();

			foreach($data as $k => $v){

				if($v[$keyField] == $keyValue){

					$changeData[$v[$valField]]['id']   = '';
					$changeData[$v[$valField]]['name'] = [];
				}else{

					$changeData[$v[$keyField]]['id'] .= "{$v[$valField]},";                       //返回数组[需要作为键的字段的值][id] = 需要返回的键对应的数值
					$changeData[$v[$keyField]]['name'][$v[$valField]] = "{$v['name']}";           //返回数组[需要作为键的字段的值][其他需要转换的字段] = 其他数据的数组
				}
			}
			return self::returnMsg(200,'success',$changeData);
			/* 删除返回数组内每个字段多余的空格 */
			foreach($changeData as $key => $value){

				$changeData[$key]['id'] = rtrim($value['id'],',');
			}
			return $changeData;
		}

		public function _empty(){

			return self::returnMsg(404,'empty method!');
		}
	}