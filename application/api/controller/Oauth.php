<?php
	namespace app\api\controller;

	use think\Request;
	use think\Cache;
	use think\Db;

	use app\api\controller\Send;
	/**
	 * API授权认证方法
	 */
	class Oauth
	{	
		use Send;
		
		/**
		 * accessToken存储前缀
		 *
		 * @var string
		 */
		public static $accessTokenPrefix = 'accessToken_';

		/**
		 * 认证授权 通过用户信息和路由
		 * @param  Request $request
		 * @return \Exception|UnauthorizedException|mixed|Exception
		 * @throws UnauthorizedException
		 */
		final function authenticate($data = '')
		{
			return self::certification(self::getClient($data));
		}

		/**
		 * 生成access_token方法
		 *
		 * @return accessToken
		 */
		public function getAccessToken()
		{
			return self::returnMsg(200,'success',self::makeSign());
		}

		/**
		 * 获取用户信息
		 * 
		 * @param Request $request
		 * @return $this
		 * @throws UnauthorizedException
		 */
		public static function getClient($data)
		{
			/* 获取accessToken等参数 */
			if(isset($data['aithorization'])){

				$authorization = $data['aithorization'];    
				$authorization = explode(",",$authorization);     //authorization: USERID xxxx
				/* 判断传递的accessToken是否符合规范 */
				if(count($authorization) <= 1){

					return self::returnMsg(401,'fail','传递的accessToken不符合规范');
				}

				$clientInfo['uid'] = $authorization[1];
				$clientInfo['accessToken'] = $authorization[0];
				return $clientInfo;
			}else{

				return self::returnMsg(401,'fail','accessToken不能为空');
			}
			exit(var_dump($data));
		}

		/**
		 * 获取用户信息后 验证权限
		 *
		 * @return mixed
		 */
		public static function certification($data = [])
		{	
			$data['uid'] = 'uid_' . $data['uid'];
			$data['accessToken'] = self::$accessTokenPrefix . $data['accessToken'];

			$getCacheAccessToken = Cache::get($data['uid']);    //获取缓存access_token
			if(!$getCacheAccessToken) {

				self::checkCache($data['uid'],$data['accessToken']);
			}
			// var_dump($getCacheAccessToken);
			// var_dump($data['accessToken'] != $getCacheAccessToken);
			// exit(var_dump($data['accessToken']));

			if($data['accessToken'] != $getCacheAccessToken){

				return self::returnMsg(401,'fail','accessToken错误');
			}

			return $data;
		}

		/**
		 * 生成签名
		 * _字符开头变量不参与签名
		 *
		 * @return accessToken
		 */
		public static function makeSign()
		{
			/* 生成md5的accessToken */
			$data = Request::instance()->param();
			$accessToken = strtolower(md5('pakfursh'.$data['uid'].$data['utoken']));

			self::makeCache($data['uid'],$accessToken);
			self::makeDb($data['uid'],$accessToken);

			return $accessToken;
		}

		/**
     * 判断accessToken
     *
     * 判断缓存中是否有accessToken，如果不存在就查询数据库
     * @param accessToken
		 */
		protected static function checkCache($uid , $accessToken)
		{	
			/* 获取数据库内的accessToken */
			$uid   = explode('_',$uid);
			$uid   = $uid[1];
			$token = explode('_',$accessToken);
			$token = $token[1];

			$accessToken = Db::table('for_access_token')->where('user_token',$uid)->where('acces_token',$token)->select();

			if(empty($accessToken)){

				return self::returnMsg(401,'field',"accessToken不存在或为空");
			}
			$accessToken[0]['acces_token'] = self::$accessTokenPrefix.$accessToken[0]['acces_token'];

			self::makeCache($accessToken[0]['user_token'],$accessToken[0]['acces_token']);	
		}

		/**
		 * 把数据存进缓存方法
		 *
		 * @param uid 用户id
		 * @param accessToken 用户对应的证书
		 */
		public static function makeCache($uid , $accessToken)
		{
			$time = 60*60*24*30;           //设置缓存过期时间为一个月

			$uid = 'uid_' . $uid;

			Cache::rm($uid);

			Cache::set($uid,$accessToken,$time);
		}

		/**
		 * 将生成的accessToken存进数据库
		 *
		 * @param accessToken
		 * @param uid
		 */
		protected static function makeDb($uid = '' , $accessToken = '')
		{
			$data['acces_token'] = $accessToken;
			$data['user_token'] = $uid;

			$data['user_data'] = time();
			$data['app_id'] = 'pakfursh';

			Db::table('for_access_token')->where('user_token',$uid)->delete();    //删除原先该用户的accessToken
			Db::table('for_access_token')->insert($data);
		} 
		/**
		 * 计算ORDER的MD5签名
		 */
		// private static function getOrderMd5($params = [] , $app_secret = '') {

			// ksort($params);
			// $params['key'] = $app_secret;
			// return strtolower(md5(urldecode(http_build_query($params))));
		// }
	}