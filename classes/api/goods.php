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
		/* 获取活动详情 */
		$query         = new IQuery('goods as m');
		$query->join   = 'LEFT JOIN activity AS a ON a.id=m.activity';
		$query->where  = 'm.id IN ('.implode(',', $ids).')';
		$query->fields = 'm.id,m.sell_price,m.activity,a.start_time,a.end_time,a.ratio,a.status';
		$list          = $query->find();
		if(!empty($list)){
			$aGoods = array(); //参与活动的商品
			$aids   = array(); //需要结束的活动
			foreach($list as $k => $v){
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
			$jmj_config           = new Config('jmj_config');
			$goods_ratio          = $jmj_config->goods_ratio; //全场折扣率
			$goods_ratio_original = $jmj_config->goods_ratio_original; //原价显示扣率
			$goods_ratio          = $goods_ratio>1 || $goods_ratio<=0 ? 1 : $goods_ratio;
			foreach($data as $k => $v){
				//修改原价
				if(isset($v['original_price']))
					$data[$k]['original_price'] = round($v['original_price']*$goods_ratio_original, 2);
				//修改销售价格
				if(!isset($v['sell_price'])) continue;
				if(isset($aGoods[$v[$key]])){
					//活动打折商品
					$data[$k]['sell_price'] = round($v['sell_price']*$aGoods[$v[$key]]['ratio'], 2);
				}else{
					//全场打折
					$data[$k]['sell_price'] = round($v['sell_price']*$goods_ratio, 2);
				}
			}
		}
		return isset($is_array) && $is_array==1 ? $data[0] : $data;
	}

	/**
	 * 获取商品列表
	 * @return array
	 */
	public function goodsList($param = array()){
		/* 接收参数 */
		$fields   = isset($param['fields']) ? $param['fields'] : 'm.id,m.name,m.sell_price,m.original_price,m.img,m.activity,m.jp_price,m.market_price,b.name AS brand_name,b.logo AS brand_logo';//查询字段，选填
		$pagesize = isset($param['pagesize']) ? $param['pagesize'] : 20;//每页显示条数，选填
		$page     = isset($param['page']) ? $param['page'] : '';//分页，选填
		$aid      = isset($param['aid']) ? $param['aid'] : ''; //活动ID，选填
		$cid      = isset($param['cid']) ? $param['cid'] : ''; //分类ID，选填
		$bid      = isset($param['bid']) ? $param['bid'] : ''; //品牌ID，选填
		$did      = isset($param['did']) ? $param['did'] : ''; //推荐ID，选填
		$tag      = isset($param['tag']) ? $param['tag'] : ''; //标签，选填

		/* 获取下级分类 */
		$cid = goods_class::catChild($cid);

		/* 获取数据 */
		$query           = new IQuery('goods as m');
		$query->join     = 'LEFT JOIN category_extend AS c ON c.goods_id=m.id '.
			'LEFT JOIN brand AS b ON b.id=m.brand_id '.
			'LEFT JOIN commend_goods AS d ON d.goods_id=m.id';
		$query->where    = 'm.is_del=0'.
			(empty($aid) ? '' : ' AND m.activity='.$aid). //活动ID
			(empty($cid) ? '' : ' AND c.category_id IN ('.$cid.')'). //分类ID
			(empty($bid) ? '' : ' AND m.brand_id IN ('.$bid.')'). //品牌ID
			(empty($did) ? '' : ' AND d.commend_id='.$did). //推荐ID
			(empty($tag) ? '' : ' AND m.search_words LIKE "%,'.$tag.',%"'); //标签
		$query->fields   = $fields;
		$query->order    = 'm.sale desc,m.visit desc';
		$query->group    = 'm.id';
		$query->page     = $page<1 ? 1 : $page;
		$query->pagesize = $pagesize;
		$data            = $query->find();
		$totalPage       = $query->getTotalPage();
		if($page>$totalPage) $data = array();
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