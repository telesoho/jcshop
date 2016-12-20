<?php

/**
 * @brief 公共方法集合
 * @class Common
 * @note  公开方法集合适用于整个系统
 */
class Common{
	
	/**
	 * 记录用户操作
	 *
	 */
	public static function record($type = 0, $did = 0){
		/* 获取当前用户 */
		$user_id = IWeb::$app->getController()->user['user_id'];
		if(empty($type) || empty($did) || empty($user_id)) return false;
		/* 记录操作 */
		$model = new IModel('record');
		$model->setData(array('user_id' => $user_id, 'type' => $type, 'did' => $did, 'create_time' => time()));
		$rel = $model->add();
		return $rel>0 ? true : false;
	}
	
	/**
	 * 调试-数据库写入
	 * @param $data
	 */
	public static function dblog($data){
		//数据库
		$data  = json_encode($data);
		$model = new IModel('log_debug');
		$model->setData(array('msg' => $data, 'date' => date('Y-m-d H:i:s', time())));
		return $model->add();
	}
	
	/**
	 * @brief 获取评价分数
	 * @param $grade float 分数
	 * @param $comments int 评论次数
	 * @return float
	 */
	public static function gradeWidth($grade, $comments = 1){
		return $comments==0 ? 0 : round($grade/$comments);
	}
	
	/**
	 * @brief 获取用户状态
	 * @param $status int 状态代码
	 * @return string
	 */
	public static function userStatusText($status){
		$mapping = array('1' => '正常', '2' => '删除', '3' => '锁定');
		return isset($mapping[$status]) ? $mapping[$status] : '';
	}
	
	/**
	 * 获取本地版本信息
	 * @return String
	 */
	public static function getLocalVersion(){
		return include(IWeb::$app->getBasePath().'docs/version.php');
	}
}