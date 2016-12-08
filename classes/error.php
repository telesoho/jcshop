<?php
/**
 * 错误信息控制器
 * @author 夏爽
 */
class Error
{
	/* 错误汇总  */		//错误信息属性，001001  前三位是控制器编码，后三位是错误编码
 	private $Msg = array(
 		//全局
		'001000'		=>'系统错误',
		'001001'		=>'服务器时间异常',
		'001002'		=>'签名错误',
		'001003'		=>'参数校验失败',
		'001005'		=>'系统提示:',
		'001006'		=>'未知错误:',
		'001010'		=>'必填项不能为空',
		'001020' 		=>'token无效，请重新登录',
 	);
 	
 	/**
 	 * 获取错误信息
 	 * @param string $controller 控制器名
 	 * @return multitype:multitype:string  |number
 	 */
 	public static function errorInfo($code = null){
 		return $this->Msg[$code];
 	}
 	
 	
}