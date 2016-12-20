<?php

/**
 * 活动类库
 */
class Activity{
	/**
	 * 检查活动状态
	 * @param int $aid 活动ID
	 */
	public static function checkStatus($aid=0){
		$modelAti = new IModel('activity');
		$dataAti  = $modelAti->getObj('id='.$aid);
		if(empty($dataAti))
			return apiReturn::go('002016'); //活动不存在
		if($dataAti['status']!=1)
			return apiReturn::go('002017'); //活动禁用
		if($dataAti['start_time']>time())
			return apiReturn::go('002018'); //活动未开始
		if($dataAti['end_time']<time())
			return apiReturn::go('002019'); //活动已结束
		return apiReturn::go('0',$dataAti);
	}
}