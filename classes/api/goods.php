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
	public function goodsActivity($data=array()){
		//如果直接包含ID则为单个商品
		if(isset($data['id']))
			$data 			= array($data);
		//商品ID集
		$ids 				= array();
		foreach($data as $k => $v)
			$ids[] 			= $v['id'];
		if(empty($ids)) return $data; //不包含id时直接返回
		/* 获取活动详情 */
		$query 				= new IQuery('goods as m');
		$query->join 		= 'LEFT JOIN activity AS a ON a.id=m.activity';
		$query->where 		= 'm.id IN ('.implode(',',$ids).') AND a.start_time<='.time().' AND a.end_time>='.time().' AND a.status=1';
		$query->fields 		= 'm.id,m.sell_price,m.activity,a.start_time,a.end_time,a.ratio,a.status';
		$list 				= $query->find();
		if(!empty($list)){
			$aGoods 	= array(); //参与活动的商品
			foreach($list as $k => $v){
				$aGoods[$v['id']] = array('ratio'=>$v['ratio'], 'sell_price'=>$v['sell_price']);
			}
			foreach($data as $k => $v){
				if( isset($aGoods[$v['id']]) )
					$data[$k]['sell_price'] 	= round($aGoods[$v['id']]['sell_price']*$aGoods[$v['id']]['ratio'],2);
			}
		}
		return $data;
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