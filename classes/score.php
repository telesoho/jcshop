<?php
/**
 * 积分类
 * @author 夏爽
 */

class Score
{
	/**
	 * 购买完成，店铺主增加积分
	 * @param int $uid 用户ID
	 * @param string $orderNo 订单号
	 * @return bool
	 */
    public static function incPay($uid, $orderNo){
    	/* 当前用户所绑定店铺 */
    	$query 						= new IQuery('user as m');
    	$query->join 				= 'LEFT JOIN order AS o ON o.user_id=m.id LEFT JOIN shop AS s ON s.identify_id=m.shop_identify_id';
    	$query->where 				= 'm.id='.$uid.' AND o.order_no='.$orderNo;
    	$query->fields 				= 's.own_id,o.real_amount';
    	$query->limit 				= 1;
    	$info 						= $query->find();
    	if(!empty($info) && !empty($info[0]['own_id']) && $info[0]['own_id']!=$uid){
    		$info 					= $info[0];
    		//积分比例配置
    		$siteConfig 			= new Config('site_config');
    		$score_goods_rate 		= $siteConfig->score_goods_rate; //商品返金额比例
    		$score_rate 			= $siteConfig->score_rate; //积分和金额兑换比例
    		/* 店铺主增加积分 */
    		$model 					= new IModel('member');
    		$model->setData(array('point'=>'`point`+'.$info['real_amount']*$score_goods_rate*$score_rate));
    		$rel 					= $model->update('user_id='.$info['own_id'],array('point'));
    		if( $rel>0 ) return true;
    	}
    	return false;
    }
	
	
	
}