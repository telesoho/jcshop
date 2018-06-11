<?php
/**
 * @copyright Copyright(c) 2014 aircheng.com
 * @file freight_facade.php
 * @author nswe
 * @date 2014/4/18 16:22:33
 * @version 1.0.0
 */

/**
 * @class freight_facade
 * @brief 快递跟踪接口类
 */
class freight_facade
{
	//使用的物流接口
	public static $freightInterface = 'hqepay';
	CONST XLOBO = 'XLOBO'; 			// 贝海国际速递

	/**
	 * @brief 开始快递跟踪
	 * @param $ShipperCode    string 物流公司编码
	 * @param $LogisticCode   string 物流单号
	 */
	public static function line($ShipperCode,$LogisticCode)
	{
		if ($ShipperCode == self::XLOBO) {
			self::$freightInterface = "xloboapi";
		} else {
			self::$freightInterface = "hqepay";
		}

		if( $freightObj = self::createObject() ) {
			return $freightObj->line($ShipperCode,$LogisticCode);
		}
	}

	/**
	 * @brief 创建物流接口实力
	 * @return object 快递跟踪类实例
	 */
	private static function createObject()
	{
		//类库路径
		$basePath   = IWeb::$app->getBasePath().'plugins/freight/';

		//配置参数
		$siteConfig = new Config('site_config');

		// 加载接口类
		require_once($basePath . self::$freightInterface . '.php');

		// 读取配置文件信息，并创建接口对象
		return new self::$freightInterface($siteConfig->getInfo()[self::$freightInterface]);
	}
}
