<?php
/**
 * @copyright (c) 2011 aircheng.com
 * 警报类
 * @author 夏爽
 */

class Alarm
{
	/**
	 * 错误记录
	 * @param array $error 错误信息
	 */
	public static function log($error){
		/* 写入数据库 */
		$model 				= new IModel('log_error');
		$model->setData($error);
		$model->add();
	}
	
	
	
	
	
}