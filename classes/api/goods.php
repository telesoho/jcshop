<?php

/**
 * @copyright (c) 2011 aircheng.com
 * @file goods.php
 * @author chendeshan
 * @date 2011-9-30 13:49:22
 * @version 0.6
 */
class APIGoods{
	/**
	 * 活动商品价格
	 * @param array $data 商品数据，必须包含商品id
	 * @param string $key 商品ID
	 * @return array
	 */
	public function goodsActivity($data = array(), $key = 'id'){
		//如果直接包含ID则为单个商品
		if(isset($data[$key])){
			$data     = array($data);
			$is_array = 1;
		}
		//商品ID集
		$ids = array();
		foreach($data as $k => $v)
			$ids[] = $v[$key];
		if(empty($ids)) return $data; //不包含id时直接返回
		
		//获取配置
		$jmj_config            = new Config('jmj_config');
		$goods_ratio           = $jmj_config->goods_ratio; //全场折扣率
		$goods_ratio_original  = $jmj_config->goods_ratio_original; //原价显示扣率
		$goods_ratio_delivery  = $jmj_config->goods_ratio_delivery; //商品价格重量比率计算是否包邮
		$goods_weight_delivery = $jmj_config->goods_weight_delivery; //商品重量大于此数值时不包邮，-1为关闭
		$goods_ratio           = $goods_ratio>1 || $goods_ratio<=0 ? 1 : $goods_ratio;
		
		$user_id     = IWeb::$app->getController()->user['user_id'];
		$listGoods   = array(); //商品原数据
		$bGoods      = array(); //参与砍价刀的商品
		$sGoods      = array(); //参与限时活动的商品
		$aGoods      = array(); //参与活动的商品
		$aids        = array(); //需要结束的活动
		$deliveryYes = array(); //包邮商品列表
		$deliveryNo  = array(); //不包邮商品列表
		
		/* 砍价刀 */
		$queryBargain         = new IQuery('activity_bargain AS m');
		$queryBargain->join   = 'LEFT JOIN activity_bargain_access AS a ON a.pid=m.id '.
			'LEFT JOIN goods AS g ON g.id=a.goods_id';
		$queryBargain->where  = 'a.goods_id IN ('.implode(',', $ids).') AND m.start_time<='.time().' AND m.end_time>='.time().' AND status=1';
		$queryBargain->fields = 'a.id,a.goods_id,a.nums,g.sell_price,m.start_time,m.end_time';
		$listBargain          = $queryBargain->find();
		if(!empty($listBargain)){
			$queryOrder         = new IQuery('order AS m');
			$queryOrder->join   = 'LEFT JOIN order_goods AS g ON g.order_id=m.id';
			$queryOrder->fields = 'count(*) AS sum';
			foreach($listBargain as $k => $v){
				$where = 'm.pay_status=1 AND m.pay_time>="'.date('Y-m-d H:i:s', $v['start_time']).'" AND m.pay_time<="'.date('Y-m-d H:i:s', $v['end_time']).'" AND g.goods_id='.$v['goods_id'];
				//限购数量已售完
				$queryOrder->where = $where;
				$sum               = $queryOrder->find();
				if($sum[0]['sum']>=$v['nums']) continue;
				
				//当前用户已购买过
				$queryOrder->where = $where.' AND m.user_id='.$user_id;
				$sum               = $queryOrder->find();
				if($sum[0]['sum']>=1) continue;
				
				if(isset($bGoods[$v['goods_id']]) && !empty($bGoods[$v['goods_id']])){
					//同种活动对比结束时间早的先显示
					if($bGoods[$v['goods_id']]['type']==$v['type']){
						if($bGoods[$v['goods_id']]['end_time']<$v['end_time'])
							continue;
					}
				}
				$queryBuser             = new IQuery('activity_bargain_user');
				$queryBuser->where      = 'byuser='.$user_id.' AND pid='.$v['id'];
				$queryBuser->fields     = 'SUM(`money`) AS sum';
				$sum                    = $queryBuser->find();
				$v['sell_price']        = $v['sell_price']-(empty($sum[0]['sum']) ? 0 : $sum[0]['sum']);
				$v['sell_price']        = $v['sell_price']<=0 ? 0 : $v['sell_price'];
				$bGoods[$v['goods_id']] = $v;
			}
		}
		
		/* 限时活动（限时购） */
		$querySpeed         = new IQuery('activity_speed as m');
		$querySpeed->join   = 'LEFT JOIN activity_speed_access AS a ON a.pid=m.id';
		$querySpeed->where  = 'm.type=1 AND a.goods_id IN ('.implode(',', $ids).') AND m.start_time<='.time().' AND m.end_time>='.time().' AND status=1';
		$querySpeed->fields = 'a.id,a.goods_id,a.sell_price,a.nums,a.quota,a.delivery,m.type,m.start_time,m.end_time';
		$listSpeed          = $querySpeed->find();
		if(!empty($listSpeed)){
			$queryOrder         = new IQuery('order AS m');
			$queryOrder->join   = 'LEFT JOIN order_goods AS g ON g.order_id=m.id';
			$queryOrder->fields = 'count(*) AS sum';
			foreach($listSpeed as $k => $v){
				$where = 'm.pay_status=1 AND m.create_time>="'.date('Y-m-d H:i:s', $v['start_time']).'" AND m.create_time<="'.date('Y-m-d H:i:s', $v['end_time']).'" AND g.goods_id='.$v['goods_id'];
				//限购数量已售完
				$queryOrder->where = $where;
				$sum               = $queryOrder->find();
				if($sum[0]['sum']>=$v['nums']) continue;
				
				//当前用户已限购完
				if($v['quota']>0){
					$queryOrder->where = $where.' AND m.user_id='.$user_id;
					$sum               = $queryOrder->find();
					if($sum[0]['sum']>=$v['quota']) continue;
				}
				
				if(isset($sGoods[$v['goods_id']]) && !empty($sGoods[$v['goods_id']])){
					//同种活动对比结束时间早的先显示
					if($sGoods[$v['goods_id']]['type']==$v['type']){
						if($sGoods[$v['goods_id']]['end_time']<$v['end_time'])
							continue;
					}
				}
				$sGoods[$v['goods_id']] = $v;
			}
			//限时活动中的包邮商品
			foreach($sGoods as $k => $v){
				if($v['delivery']==1) $deliveryYes[] = $v['goods_id'];
			}
		}
		
		/* 获取活动详情 */
		$query         = new IQuery('goods as m');
		$query->join   = 'LEFT JOIN activity AS a ON a.id=m.activity';
		$query->where  = 'm.id IN ('.implode(',', $ids).')';
		$query->fields = 'm.id,m.sell_price,m.activity,m.weight,a.start_time,a.end_time,a.ratio,a.status';
		$list          = $query->find();
		if(!empty($list)){
			//检索
			foreach($list as $k => $v){
				$listGoods[$v['id']] = $v; //存商品原数据
				/* 是否包邮 */ //商品(重量/价格)比重、超过一定重量时包邮
				if($v['weight']<=0 || ($v['sell_price']/$v['weight']<=$goods_ratio_delivery && $goods_ratio_delivery!=-1) || ($v['weight']>$goods_weight_delivery && $goods_weight_delivery!=-1)){
					$deliveryNo[] = $v['id'];
				}
				//是否参与活动
				if($v['activity']>0){
					if($v['start_time']<=time() && $v['end_time']>=time() && $v['status']==1 && $v['ratio']>0 && $v['ratio']<1){
						$aGoods[$v['id']] = array('ratio' => $v['ratio'], 'sell_price' => $v['sell_price']);
					}
					if($v['end_time']<time()){
						$aids[] = $v['activity'];
					}
				}
			}
			//结束活动 TODO 先不结束
//			if(!empty($aids)){
//				$model = new IModel('goods');
//				$model->setData(array('activity' => 0));
//				$model->update('activity IN ('.implode(',', $aids).')');
//			}
			//更新价格
			foreach($data as $k => $v){
				//修改原价
				if(isset($v['original_price']))
					$data[$k]['original_price'] = round($v['sell_price']*$goods_ratio_original, 2);
				//修改销售价格
				if(!isset($v['sell_price'])) continue;
			}
		}
		/* 更新数据 */
		foreach($data as $k => $v){
			//是否包邮
			$data[$k]['delivery'] = in_array($v[$key], $deliveryYes) ? 3 : (in_array($v[$key], $deliveryNo) ? 2 : 1);
			//修改原价
			if(isset($v['original_price']))
				$data[$k]['original_price'] = round($listGoods[$v[$key]]['sell_price']*$goods_ratio_original, 2);
			//修改销售价格
			if(isset($v['sell_price'])){
				if(isset($bGoods[$v[$key]])){
					//砍价刀
					$data[$k]['sell_price'] = $bGoods[$v[$key]]['sell_price'];
				}elseif(isset($sGoods[$v[$key]])){
					//限时活动商品
					$data[$k]['sell_price'] = $sGoods[$v[$key]]['sell_price'];
				}elseif(isset($aGoods[$v[$key]])){
					//活动打折商品
					$data[$k]['sell_price'] = round($listGoods[$v[$key]]['sell_price']*$aGoods[$v[$key]]['ratio'], 2);
				}else{
					//全场打折
					$data[$k]['sell_price'] = round($listGoods[$v[$key]]['sell_price']*$goods_ratio, 2);
				}
			}
		}
		return isset($is_array) && $is_array==1 ? $data[0] : $data;
	}
	
	/**
	 * 计算商品邮费
	 * @param array $data 商品数据，必须包含商品id
	 * @param string $key 商品ID
	 * @param int $is_delivery 是否包邮[0正常计算-1包邮-2不包邮]
	 * @return int
	 */
	public function goodsDelivery($data, $key = 'id', $is_delivery = 0){
		$goods1 = array('money' => 0, 'weight' => 0, 'count' => 0); //满减商品
		$goods2 = array('money' => 0, 'weight' => 0, 'count' => 0); //不包邮商品
		
		/* 判断商品是否包邮 */
		$data = Api::run('goodsActivity', $data, $key);
		foreach($data as $k => $v){
			if($v['delivery']==1){
				$goods1['money'] += ($v['sell_price']-$v['reduce'])*$v['count'];
				$goods1['weight'] += $v['weight']*$v['count'];
				$goods1['count']++;
			}elseif($v['delivery']==2){
				$goods2['money'] += ($v['sell_price']-$v['reduce'])*$v['count'];
				$goods2['weight'] += $v['weight']*$v['count'];
				$goods2['count']++;
			}
		}
		
		/* 包邮金额 */
		$condition_price = (new Config('jmj_config'))->condition_price; //包邮金额，[0=全场无条件包邮,-1=关闭包邮]
		
		/* 物流运费规则 */
		$delivery = Api::run('getDeliveryList');
		$delivery = $delivery[0];
		
		/* 计算邮费 */
		if($condition_price==0 || $is_delivery==1 || ($goods1['count']==0 && $goods2['count']==0)){ //全场包邮
			$deliveryPrice = 0;
		}elseif($condition_price==-1 || $is_delivery==2){ //没有开启满包邮
			//首重价格
			$deliveryPrice = $delivery['first_price'];
			//续重价格
			if($goods1['weight']+$goods2['weight']>$delivery['first_weight']){
				$deliveryPrice += ceil(($goods1['weight']+$goods2['weight']-$delivery['first_weight'])/$delivery['second_weight'])*$delivery['second_price'];
			}
		}elseif($goods1['money']>=$condition_price){ //包邮商品满足包邮条件
			$deliveryPrice = 0; //包邮
			if($goods2['count']>0){
				//首重价格
				$deliveryPrice = $delivery['first_price'];
				//续重价格
				if($goods2['weight']>$delivery['first_weight']){
					$deliveryPrice += ceil(($goods2['weight']-$delivery['first_weight'])/$delivery['second_weight'])*$delivery['second_price'];
				}
			}
		}else{
			//首重价格
			$deliveryPrice = $delivery['first_price'];
			//续重价格
			if($goods1['weight']+$goods2['weight']>$delivery['first_weight']){
				$deliveryPrice += ceil(($goods1['weight']+$goods2['weight']-$delivery['first_weight'])/$delivery['second_weight'])*$delivery['second_price'];
			}
		}
		
		return $deliveryPrice;
	}
	
	/**
	 * 获取商品列表
	 * @return array
	 */
	public function goodsList($param = array()){
		/* 接收参数 */
		$param             = array(
			'fields'   => isset($param['fields']) ? $param['fields'] : '',
			'pagesize' => isset($param['pagesize']) ? $param['pagesize'] : '', //每页显示条数
			'page'     => isset($param['page']) ? $param['page'] : 1,//分页，选填
			'aid'      => isset($param['aid']) ? $param['aid'] : '', //活动ID，选填
			'bid'      => isset($param['bid']) ? $param['bid'] : '', //品牌ID，选填
			'cid'      => isset($param['cid']) ? $param['cid'] : '', //分类ID，选填
			'did'      => isset($param['did']) ? $param['did'] : '', //推荐ID，选填
			'tag'      => isset($param['tag']) ? $param['tag'] : '', //标签，选填
		);
		$param['fields']   = !empty($param['firlds']) ? $param['firlds'] : 'm.id,m.name,m.sell_price,m.original_price,m.img,m.activity,m.jp_price,m.market_price,b.name AS brand_name,b.logo AS brand_logo';//查询字段，选填
		$param['pagesize'] = !empty($param['pagesize']) ? $param['pagesize'] : 20;
		
		/* 获取下级分类 */
		$cid = '';
		foreach(explode(',', $param['cid']) as $k => $v){
			$cid .= goods_class::catChild($v);
			if(count(explode(',', $param['cid']))!=$k+1) $cid .= ',';
		}
		$param['cid'] = implode(',', array_unique(explode(',', $cid)));
		
		/* 获取数据 */
		$query           = new IQuery('goods as m');
		$query->join     = 'LEFT JOIN category_extend AS c ON c.goods_id=m.id '.
			'LEFT JOIN brand AS b ON b.id=m.brand_id '.
			'LEFT JOIN commend_goods AS d ON d.goods_id=m.id';
		$query->where    = 'm.is_del=0'.
			(empty($param['aid']) ? '' : ' AND m.activity='.$param['aid']). //活动ID
			(empty($param['cid']) ? '' : ' AND c.category_id IN ('.$param['cid'].')'). //分类ID
			(empty($param['bid']) ? '' : ' AND m.brand_id IN ('.$param['bid'].')'). //品牌ID
			(empty($param['did']) ? '' : ' AND d.commend_id='.$param['did']). //推荐ID
			(empty($param['tag']) ? '' : ' AND m.search_words LIKE "%,'.$param['tag'].',%"'); //标签
		$query->fields   = $param['fields'];
		$query->order    = 'm.sale desc,m.visit desc'; //默认销量排序
		$query->group    = 'm.id';
		$query->page     = $param['page']<1 ? 1 : $param['page'];
		$query->pagesize = $param['pagesize'];
		$data            = $query->find();
		if($param['page']>$query->getTotalPage()) $data = array();
		if(!empty($data)){
			/* 计算活动商品价格 */
			$data = api::run('goodsActivity', $data);
			foreach($data as $k => $v){
				$data[$k]['diff_price'] = $v['original_price']-$v['sell_price']; //差价
				$data[$k]['brand_logo'] = empty($v['brand_logo']) ? '' : IWeb::$app->config['image_host'].'/'.$v['brand_logo'];
				$data[$k]['img']        = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
			}
		}
		/* 返回数据 */
		return $data;
	}
	
	//获取全部商品特价活动
	public function getSaleList(){
		$promoDB   = new IModel('promotion');
		$promoList = $promoDB->query("is_close = 0 and award_type = 7", "*", "sort asc");
		$goodsDB   = new IModel('goods');
		
		foreach($promoList as $key => $val){
			$intro                        = JSON::decode($val['intro']);
			$intro                        = array_keys($intro);
			$intro                        = join(",", $intro);
			$promoList[$key]['goodsList'] = $goodsDB->query("id in (".$intro.") and is_del = 0", "id,name,sell_price,sort,img,market_price,sale", "sort asc");
		}
		return $promoList;
	}
	
	//根据id获取单个商品特价活动
	public function getSaleRow($id){
		$promoDB  = new IModel('promotion');
		$promoRow = $promoDB->getObj("is_close = 0 and award_type = 7 and id = {$id}");
		if($promoRow){
			$intro                 = JSON::decode($promoRow['intro']);
			$intro                 = array_keys($intro);
			$intro                 = join(",", $intro);
			$goodsDB               = new IModel('goods');
			$promoRow['goodsList'] = $goodsDB->query("id in (".$intro.") and is_del = 0", "id,name,sell_price,sort,img,market_price", "sort asc");
		}
		return $promoRow;
	}
}