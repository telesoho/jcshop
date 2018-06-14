<?php
/**
 * @copyright (c) 2011 aircheng.com
 * @file seller.php
 * @brief 商家API
 * @author chendeshan
 * @date 2014/10/12 13:59:44
 * @version 2.7
 */
class APISeller
{
	//商户信息
	public function getSellerInfo($id)
	{
		$query = new IModel('seller');
		$info  = $query->getObj("id=".$id);
		return $info;
	}

	//获取商户列表
	public function getSellerList()
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('seller');
		$query->where = 'is_del = 0 and is_lock = 0';
		$query->order = 'sort asc';
		$query->page  = $page;
		return $query;
	}

	public function getUserInfo($id)
	{
		$query = new IModel('user');
		$info  = $query->getObj("id=".$id);
		return $info;
	}

	/**
	 * 如果链接中有seller_id_QR参数，以及当前登录用户没有seller_id(推荐商家)
	 * 则登录当前登录用户，否则不做任何处理
	 * @param seller_id_QR
	 */
	public function checkAndSetSeller() {
		// 判断是否是通过扫描二维码进入（链接中包含seller_id_QR参数）
		$seller_id = IFilter::act(IReq::get('seller_id_QR'), 'int');
		$user_id = IWeb::$app->getController()->user['user_id'];
		if($seller_id && $user_id) {
			$userInfo = $this->getUserInfo($user_id);
			if($userInfo && !$userInfo['seller_id']) {
				$sellerInfo = $this->getSellerInfo($seller_id);
				$user = new IModel('user');
				$user->setData(array('seller_id' => $seller_id));
				$user->update("id = $user_id");
			}
		}
	}
}