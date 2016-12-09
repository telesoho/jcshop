<?php
/**
 * 错误信息控制器
 * @author 夏爽
 */
class Apireturn
{
	/**
	 * 错误汇总
	 */
 	private static $Msg = array(
 		//系统
 		'-1' 			=> '系统错误',
 		'0' 			=> 'ok',
 		'001001' 		=> '当前用户未登录',
 		//优惠券
		'002001'		=>'请输入正确的优惠券码',
		'002002'		=>'优惠券码不存在',
		'002003'		=>'优惠券码已过期',
		'002004'		=>'优惠券码已使用',
		'002005'		=>'优惠券码无法使用',
		'002006'		=>'优惠券码类型不存在',
		'002007'		=>'优惠券不存在',
		'002008' 		=>'该优惠券活动还未开始',
		'002009' 		=>'该优惠券活动已结束',
		'002010' 		=>'该活动已被禁用',
		'002011' 		=>'该优惠券已使用',
		'002012' 		=>'优惠券类型不存在',
		'002013' 		=>'优惠券不合法',
		'002014' 		=>'不满足满减条件',
		'002015' 		=>'优惠券类型不存在',
		'002016' 		=>'活动不存在',
		'002017' 		=>'活动已被禁用',
		'002018' 		=>'活动未开始',
		'002019' 		=>'活动已结束',
		'002020' 		=>'活动出现错误',
		'002021' 		=>'已经领取过优惠券',
 	);
 	
 	/**
 	 * 获取错误信息
 	 * @param int $code 错误编号
 	 * @return string
 	 */
 	public static function info($code = null){
 		return isset(self::$Msg[$code]) ? self::$Msg[$code] : '错误码不存在';
 	}
 	
 	/**
 	 * 接口统一返回参数
 	 */
 	public static function go($code=-1,$data=''){
 		return array(
 			'code' 	=> $code,
 			'msg' 	=> self::info($code),
 			'data' 	=> $data,
 		);
 	}
 	
}