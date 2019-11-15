<?php
	
	namespace app\lib\controller;

	use app\lib\Send;
	/**
	 *异常提示
	 */
	class Exception
	{
		use Send;
		/**
		 * 路由不存在情况
		 */
		public static function miss()
		{	
			return self::returnMsg(404,'fail','当前功能尚不可用');
		}

	}	