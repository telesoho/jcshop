<?php
/**
 * @copyright (c) 2011 aircheng.com
 * @file goods.php
 * @author chendeshan
 * @date 2011-9-30 13:49:22
 * @version 0.6
 */
class APIGoods
{
	/**
	 * 活动商品价格
	 * @param array $data 商品数据，必须包含商品id
	 * @return array
	 */
	public function goodsActivity($data=array(),$key='id'){
		//如果直接包含ID则为单个商品
		if(isset($data[$key])){
			$data 			= array($data);
			$is_array 		= 1;
		}
		//商品ID集
		$ids 				= array();
		foreach($data as $k => $v)
			$ids[] 			= $v[$key];
		if(empty($ids)) return $data; //不包含id时直接返回
		/* 获取活动详情 */
		$query 				= new IQuery('goods as m');
		$query->join 		= 'LEFT JOIN activity AS a ON a.id=m.activity';
		$query->where 		= 'm.id IN ('.implode(',',$ids).')';
		$query->fields 		= 'm.id,m.sell_price,m.activity,a.start_time,a.end_time,a.ratio,a.status';
		$list 				= $query->find();
		if(!empty($list)){
			$aGoods 	= array(); //参与活动的商品
			$aids 		= array(); //需要结束的活动
			foreach($list as $k => $v){
				if($v['activity']>0){
					if($v['start_time']<=time() && $v['end_time']>=time() && $v['status']==1 && $v['ratio']>0 && $v['ratio']<1){
						$aGoods[$v['id']] = array('ratio'=>$v['ratio'], 'sell_price'=>$v['sell_price']);
					}
					if($v['end_time']<time()){
						$aids[] 	= $v['activity'];
					}
				}
			}
			//结束活动
			if(!empty($aids)){
				$model = new IModel('goods');
				$model->setData(array('activity'=>0));
				$model->update('activity IN ('.implode(',',$aids).')');
			}
			//更新价格
			$goods_ratio = (new Config('jmj_config'))->goods_ratio; //全场折扣率
			$goods_ratio = $goods_ratio>1||$goods_ratio<=0 ? 1 : $goods_ratio;
			foreach($data as $k => $v){
				if(!isset($v['sell_price'])) continue;
				if( isset($aGoods[$v[$key]]) ){
					//活动打折商品
					$data[$k]['sell_price'] 	= round($v['sell_price']*$aGoods[$v[$key]]['ratio'],2);
				}else{
					//全场打折
					$data[$k]['sell_price'] 	= round($v['sell_price']*$goods_ratio,2);
				}
			}

		}
		return isset($is_array)&&$is_array==1 ? $data[0] : $data;
	}

	//获取全部商品特价活动
	public function getSaleList()
	{
		$promoDB   = new IModel('promotion');
		$promoList = $promoDB->query("is_close = 0 and award_type = 7","*","sort asc");
		$goodsDB   = new IModel('goods');

		foreach($promoList as $key => $val)
		{
			$intro = JSON::decode($val['intro']);
			$intro = array_keys($intro);
			$intro = join(",",$intro);
			$promoList[$key]['goodsList'] = $goodsDB->query("id in (".$intro.") and is_del = 0","id,name,sell_price,sort,img,market_price,sale","sort asc");
		}
		return $promoList;
	}

	//根据id获取单个商品特价活动
	public function getSaleRow($id)
	{
		$promoDB  = new IModel('promotion');
		$promoRow = $promoDB->getObj("is_close = 0 and award_type = 7 and id = {$id}");
		if($promoRow)
		{
			$intro = JSON::decode($promoRow['intro']);
			$intro = array_keys($intro);
			$intro = join(",",$intro);
			$goodsDB = new IModel('goods');
			$promoRow['goodsList'] = $goodsDB->query("id in (".$intro.") and is_del = 0","id,name,sell_price,sort,img,market_price","sort asc");
		}
		return $promoRow;
	}
}