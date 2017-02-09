<?php
/**
 * @copyright Copyright(c) 2014 aircheng.com
 * @file area.php
 * @brief 省市地区调用函数
 * @author nswe
 * @date 2014/8/6 20:46:52
 * @version 2.6
 * @note
 */

 /**
 * @class area
 * @brief 省市地区调用函数
 */
class area
{
	/**
	 * @brief 根据传入的地域ID获取地域名称，获取的名称是根据ID依次获取的
	 * @param int 地域ID 匿名参数可以多个id
	 * @return array
	 */
	public static function name()
	{
		$result     = array();
		$paramArray = func_get_args();
		$areaDB     = new IModel('areas');
		$areaData   = $areaDB->query("area_id in (".trim(join(',',$paramArray),",").")");

		foreach($areaData as $key => $value)
		{
			$result[$value['area_id']] = $value['area_name'];
		}
		//====
		$result_k 		= array_keys($result);
		foreach($paramArray as $k => $v){
			if(!in_array($v,$result_k)) $result[$v] = '';
		}
		//====
		return $result;
	}

	/**
	 * 根据地名获取ID
	 * id('省', '市', '区', '县' ...)
	 */
	public static function id() {
		$result     = array();
		$paramArray = func_get_args();

		$areaDB = new IModel('areas');
		$parent_id = '0';
		foreach($paramArray as $area_name) {
			$area = $areaDB->getObj("parent_id = $parent_id and area_name = '$area_name'", "area_id");
			if($area) {
				$result[$area_name] = $area['area_id'];
				$parent_id = $area['area_id'];
			} else {
				break;
			}
		}
		return $result;
	}
}