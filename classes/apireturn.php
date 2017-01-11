<?php

/**
 * 错误信息控制器
 * @author 夏爽
 */
class apiReturn{
	/**
	 * 错误汇总
	 */
	private static $Msg = array(
		//系统
		'-1'     => '系统错误',
		'0'      => 'ok',
		'001001' => '当前用户未登录',
		'001002' => '参数错误',
		'001003' => '参数不能为空',
		//活动、优惠券
		'002001' => '请输入正确的优惠券码',
		'002002' => '优惠券码不存在',
		'002003' => '优惠券码已过期',
		'002004' => '优惠券码已使用',
		'002005' => '优惠券码无法使用',
		'002006' => '优惠券码类型不存在',
		'002007' => '优惠券不存在',
		'002008' => '该优惠券活动还未开始',
		'002009' => '该优惠券活动已结束',
		'002010' => '该活动已被禁用',
		'002011' => '该优惠券已使用',
		'002012' => '优惠券类型不存在',
		'002013' => '优惠券不合法',
		'002014' => '不满足满减条件',
		'002015' => '优惠券类型不存在',
		'002016' => '活动不存在',
		'002017' => '活动已被禁用',
		'002018' => '活动未开始',
		'002019' => '活动已结束',
		'002020' => '活动不包含优惠券',
		'002021' => '已经领取过红包，让好友分享给你可以继续领取',
		'002022' => '红包已领完',
		'002023' => '领取失败',
		'002024' => '已领取过该好友的红包',
		'002025' => '今天已经领取过优惠券，明天再来吧',
		'002026' => '只能领取一次',
		'002027' => '不满足礼包领取条件',
		'002028' => '礼包类型不存在',
		'002029' => '好友分享的优惠券只能领取一次',
		'002030' => '礼包不存在',
		'002031' => '领取失败',
		'002032' => '已领取过该礼包',
		'002033' => '该礼包已领完',
		'002034' => '已经领取过该优惠券',
		'002035' => '已经帮TA砍过价了',
		'002036' => '活动配置错误',
		'002037' => '砍价失败',
		//订单
		'003001' => '订单分类不存在',
		'003002' => '订单信息不存在',
		'003003' => '请填写退款理由和选择要退款的商品',
		'003004' => '申请失败',
		'003005' => '订单中不包含该商品',
		'003006' => '订单申请失败',
		//购物车
		'004001' => '订单分类不存在',
		//收货地址
		'005001' => '收货地址不存在',
		//商品
		'006001' => '商品不存在',
		'006002' => '商品未参与活动',
		'006003' => '商品已售完',
		//商品
		'007001' => '分类不存在',
		'007002' => '商品库存不足',
		//用户
		'008001' => '用户名或密码错误',
		'008002' => '用户令牌生成失败',
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
	public static function go($code = -1, $data = '', $msg = ''){
		return array(
			'code' => $code,
			'msg'  => empty($msg) ? self::info($code) : $msg,
			'data' => $data,
		);
	}
	
	/**
	 * 获取错误信息
	 */
	public static function getErrorInfo(){
		return self::$Msg;
	}

}