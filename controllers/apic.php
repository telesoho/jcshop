<?php
require __DIR__.'/../plugins/vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Apic extends IController{
	//    public $layout='site_mini';
	private $log;
	private $securityLogger;
	
	function init(){
		
		
		$dateFormat = "Y-m-d h:i:s";
		$output     = "[%datetime% ".substr(explode(".", explode(" ", microtime())[0])[1], 0, 3)."] ".strtolower(__CLASS__).".php(".__LINE__.") [%level_name%]: %message%\n";
		$formatter  = new LineFormatter($output, $dateFormat);
		$date       = date('Y-m-d', time());
		$stream     = new StreamHandler(__DIR__.'/../backup/logs/'.$date.'.log', Logger::DEBUG);
		$stream->setFormatter($formatter);
		$this->log = new Logger('api');
		$this->log->pushHandler($stream);
		//        header("Content-type: application/json");
	}
	
	
	/**
	 * ---------------------------------------------------主要页面---------------------------------------------------*
	 */
	/**
	 * 主分类：药妆、个护、宠物、健康、零食
	 */
	public function pro_list(){
		/* 获取参数 */
		$tid     = IFilter::act(IReq::get('tid'), 'int'); //分类ID
		$user_id = isset($this->user['user_id']) ? $this->user['user_id'] : 0;
		
		/* 分类 */
		switch($tid){
			case 1: //药妆
				$cid         = 126; //个性美妆
				$aid         = 15; //专辑分类
				$data['pic'] = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/gou.png';
				break;
			case 2: //个护
				$cid         = 134; //基础护肤
				$aid         = 18; //专辑分类
				$data['pic'] = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/nai.png';
				break;
			case 3: //宠物
				$cid         = 6; //宠物用品
				$aid         = 17; //专辑分类
				$data['pic'] = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/tui.png';
				break;
			case 4: //健康
				$cid         = 2; //居家药品
				$aid         = 16; //专辑分类
				$data['pic'] = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/xi.png';
				break;
			case 5: //零食
				$cid         = 7; //日式美食
				$aid         = 19; //专辑分类
				$data['pic'] = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/yi.png';
				break;
			default:
				$this->json_echo(apiReturn::go('007001'));
		}
		//商品分类
		$cids = goods_class::catChild($cid);
		/* 商品分类 */
		$queryCat         = new IQuery('category');
		$queryCat->where  = 'visibility=1 AND parent_id='.$cid;
		$queryCat->fields = 'id,name,image';
		$queryCat->limit  = 10;
		$queryCat->order  = 'sort desc';
		$listCat          = $queryCat->find();
		foreach($listCat as $k => $v){
			$listCat[$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].'/'.$v['image'];
		}
		
		/* 商品列表 */
		$queryGoods         = new IQuery('goods AS m');
		$queryGoods->join   = 'LEFT JOIN commend_goods AS d ON d.goods_id=m.id LEFT JOIN category_extend AS c ON c.goods_id=m.id';
		$queryGoods->fields = 'm.id,m.name,m.sell_price,m.img';
		$queryGoods->limit  = 6;
		$queryGoods->group  = 'm.id';
		for($i = 1; $i<=3; $i++){
			switch($i){
				case 1: //最新品
					$queryGoods->where = 'm.is_del=0 AND c.category_id IN ('.$cids.') AND commend_id=1';
					$queryGoods->order = 'm.sale desc,m.visit desc';
					break;
				case 2: //最热卖
					$queryGoods->where = 'm.is_del=0 AND c.category_id IN ('.$cids.') AND commend_id=3';
					$queryGoods->order = 'm.sale desc,m.visit desc';
					break;
				case 3: //推荐
					$queryGoods->where = 'm.is_del=0 AND c.category_id IN ('.$cids.') AND commend_id=4';
					$queryGoods->order = 'm.sale desc,m.visit desc';
					break;
			}
			$listGoods = $queryGoods->find();
			if(!empty($listGoods)){
				/* 计算活动商品价格 */
				$listGoods = api::run('goodsActivity', $listGoods);
				foreach($listGoods as $k => $v){
					$listGoods[$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$v['img'].'/w/220/h/220');
				}
			}
			$data['goods_list'.$i] = $listGoods;
		}
		
		/* 专辑 */
		$queryArt         = new IQuery('article as m');
		$queryArt->join   = 'left join article_category as c on c.id=m.category_id';
		$queryArt->where  = 'm.top=0 and m.visibility=1 and m.category_id='.$aid;
		$queryArt->fields = 'm.id,m.title,m.visit_num,m.favorite,m.image,c.name';
		$queryArt->order  = 'm.sort desc';
		$queryArt->limit  = 2;
		$listArt          = $queryArt->find();
		if(!empty($listArt)){
			//商品模型
			$queryGoods         = new IQuery('goods as m');
			$queryGoods->join   = 'left join relation as r on r.goods_id=m.id';
			$queryGoods->fields = 'm.id,m.name,m.sell_price,m.img';
			$queryGoods->order  = 'm.sale desc,m.visit desc';
			$queryGoods->limit  = 5;
			//收藏模型
			$queryFavo        = new IQuery('favorite_article');
			$queryFavo->field = 'count(id)';
			//相关商品数量模型
			$queryGoodsCount         = new IQuery('goods as m');
			$queryGoodsCount->join   = 'left join relation as r on r.goods_id=m.id';
			$queryGoodsCount->fields = 'COUNT(*) AS count';
			foreach($listArt as $k => $v){
				$listArt[$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/750/h/380");
				//是否已收藏
				$listArt[$k]['is_favorite'] = 0;
				if(!empty($user_id)){
					$queryFavo->where = 'aid='.$v['id'].' and user_id='.$user_id;
					$data_favorite    = $queryFavo->find();
					if(!empty($data_favorite)) $listArt[$k]['is_favorite'] = 1;
				}
				//相关商品
				$queryGoods->where = 'm.is_del=0 and r.article_id='.$v['id'];
				$listGoods         = $queryGoods->find();
				if(!empty($listGoods)){
					/* 计算活动商品价格 */
					$listGoods = api::run('goodsActivity', $listGoods);
					foreach($listGoods as $k1 => $v1){
						$listGoods[$k1]['img'] = empty($v1['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/240/h/240");
					}
				}
				$queryGoodsCount->where     = 'm.is_del=0 and r.article_id='.$v['id'];
				$goodsCount                 = $queryGoodsCount->find();
				$listArt[$k]['goods_count'] = $goodsCount[0]['count'];
				$listArt[$k]['goods_list']  = $listGoods;
			}
		}
		
		/* 品牌 */
		$queryBcat         = new IQuery('brand_category');
		$queryBcat->where  = 'goods_category_id in ('.$cids.')';
		$queryBcat->fields = 'id';
		$queryBcat->limit  = 1000;
		$listBcat          = $queryBcat->find();
		$listBrand         = array();
		if(!empty($listBcat)){
			//关联的品牌
			$queryBrand = new IQuery('brand');
			$where      = 'logo is not null ';
			if(!empty($listBcat)){
				$where .= 'AND (';
				foreach($listBcat as $k => $v){
					$where .= 'category_ids like "%,'.$v['id'].',%"';
					if(count($listBcat)-1!=$k) $where .= ' OR ';
				}
				$where .= ')';
			}
			$queryBrand->where  = $where;
			$queryBrand->fields = 'id,name,logo,url';
			$queryBrand->order  = 'sort desc';
			$queryBrand->limit  = 20;
			$listBrand          = $queryBrand->find();
			if(!empty($listBrand)){
				foreach($listBrand as $k => $v){
					$listBrand[$k]['logo'] = empty($v['logo']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['logo']."/w/200/h/120");
				}
			}
		}
		
		/* 返回参数 */
		$data['category_list'] = $listCat; //分类列表
		$data['article_list']  = $listArt; //文章列表
		$data['brand_list']    = $listBrand; //品牌列表
		$this->json_echo(apiReturn::go('0', $data));
	}
	
	/**
	 * ---------------------------------------------------购物车---------------------------------------------------*
	 */
	//购物车商品列表页面
	public function cart(){
		$countObj = new CountSum();
		$result   = $countObj->cart_count();
		if(is_string($result)){
			//            IError::show($result,403);
			$this->log->addError('$result变量错误');
		}
		$query                     = new IQuery("promotion");
		$query->where              = "type = 0 and seller_id = 0 and award_type = 6";
		$result['condition_price'] = $query->find()[0]['condition'];
		foreach($result['goodsList'] as $key => $value){
			$result['goodsList'][$key]['img'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$result['goodsList'][$key]['img']."/w/120/h/120");
		}
		//配送方式
		$result['delivery'] = Api::run('getDeliveryList');
		$this->json_echo($result);
	}
	
	/**
	 * 购物车结算页面
	 */
	public function cart2(){
		$id        = IFilter::act(IReq::get('id'), 'int'); //商品ID
		$type      = IFilter::act(IReq::get('type'));//goods,product
		$promo     = IFilter::act(IReq::get('promo'));
		$active_id = IFilter::act(IReq::get('active_id'), 'int');
		$buy_num   = IReq::get('num') ? IFilter::act(IReq::get('num'), 'int') : 1;
		//         $tourist   				= IReq::get('tourist');//游客方式购物
		$code       = IFilter::act(IReq::get('code'), 'int');
		$ticket_aid = IFilter::act(IReq::get('ticket_aid'), 'int');
		
		//必须为登录用户
		if(!isset($this->user['user_id']) || $this->user['user_id']==null) $this->json_echo(apiReturn::go('001001'));
		
		$user_id = $this->user['user_id'];
		//计算商品
		$countSumObj = new CountSum($user_id);
		$result      = $countSumObj->cart_count($id, $type, $buy_num, $promo, $active_id);
		if($countSumObj->error) $this->json_echo(apiReturn::go('-1', '', $countSumObj->error));
		
		//获取收货地址
		$addressObj  = new IModel('address');
		$addressList = $addressObj->query('user_id = '.$user_id, "*", "is_default desc");
		
		//更新$addressList数据
		foreach($addressList as $key => $val){
			$temp   = area::name($val['province'], $val['city'], $val['area']);
			$temp_k = array_keys($temp);
			if(isset($temp[$val['province']]) && isset($temp[$val['city']]) && isset($temp[$val['area']])){
				$addressList[$key]['province_val'] = in_array($val['province'], $temp_k) ? $temp[$val['province']] : '';
				$addressList[$key]['city_val']     = in_array($val['city'], $temp_k) ? $temp[$val['city']] : '';
				$addressList[$key]['area_val']     = in_array($val['area'], $temp_k) ? $temp[$val['area']] : '';
			}
		}
		
		//获取习惯方式
		$memberObj = new IModel('member');
		$memberRow = $memberObj->getObj('user_id = '.$user_id, 'custom');
		if(isset($memberRow['custom']) && $memberRow['custom']){
			$this->custom = unserialize($memberRow['custom']);
		}else{
			$this->custom = array('payment' => '', 'delivery' => '',);
		}
		
		//返回值
		$data['gid']         = $id;
		$data['type']        = $type;
		$data['num']         = $buy_num;
		$data['promo']       = $promo;
		$data['active_id']   = $active_id;
		$data['final_sum']   = $result['sum'];//原价//$result['final_sum'];
		$data['promotion']   = $result['promotion'];
		$data['proReduce']   = $result['proReduce'];
		$data['sum']         = $result['sum'];
		$data['goodsList']   = $result['goodsList'];
		$data['count']       = $result['count'];
		$data['reduce']      = $result['reduce'];
		$data['weight']      = $result['weight'];
		$data['freeFreight'] = $result['freeFreight'];
		$data['seller']      = $result['seller'];
		$data['addressList'] = $addressList;
		$data['goodsTax']    = $result['tax'];
		$data['is_delivery'] = 0; //是否包邮[0正常计算-1包邮-2不包邮]
		
		//配送方式
		$data['delivery'] = Api::run('getDeliveryList');
		//付款方式
		$data['payment'] = Api::run('getPaymentList');
		foreach($data['payment'] as $key => $value){
			$data['payment'][$key]['paymentprice'] = CountSum::getGoodsPaymentPrice($value['id'], $data['sum']);
		}
		//商品展示
		foreach($data['goodsList'] as $key => $value){
			if(isset($value['spec_array'])) $data['goodsList'][$key]['spec_array'] = Block::show_spec($value['spec_array']);
			if($data['goodsList'][$key]['img']) $data['goodsList'][$key]['img'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$data['goodsList'][$key]['img']."/w/500/h/500");
		}
		
		//满包邮规则
		$query                   = new IQuery("promotion");
		$query->where            = "type = 0 and seller_id = 0 and award_type = 6";
		$data['condition_price'] = $query->find()[0]['condition'];
		
		/* 使用优惠券 */
		if(!empty($code)){
			/* 优惠券码 */
			$rel = ticket::calculateCode($data, $code);
			if($rel['code']>0) $this->json_echo($rel);
			$data = $rel['data'];
		}else if(!empty($ticket_aid)){
			/* 活动优惠券 */
			$rel = ticket::calculateActivity($data, $ticket_aid);
			if($rel['code']>0) $this->json_echo($rel);
			$data = $rel['data'];
		}else{
			/* 优惠券 */
			$data['ticket'] = array(
				'ticket_did' => '',    //优惠券码ID
				'ticket_aid' => '',    //优惠券ID
				'name'       => '',    //优惠券名称
				'msg'        => '',
			);
		}
		/* 计算邮费 */
		$data['delivery_money'] = Api::run('goodsDelivery',$data['goodsList'],'goods_id',$data['is_delivery']);
		
		$this->json_echo(apiReturn::go('0', $data));
	}
	
	/**
	 * 活动商品订单结算
	 */
	public function order_activity(){
		/* 接收参数 */
		$pargam               = array(
			'goods_list' => IReq::get('goods_list'), //商品{[{'goods_id':1,'nums':2},{'goods_id':3,'nums':1}]}
			'colse'      => IFilter::act(IReq::get('colse'), 'int'), //是否结算
			'atype'      => IFilter::act(IReq::get('atype'), 'int'), //活动类型[1秒杀]
			'address_id' => IFilter::act(IReq::get('address_id'), 'int'), //收货地址ID
		);
		$user_id              = !isset($this->user['user_id']) || empty($this->user['user_id']) ? $this->json_echo(apiReturn::go('001001')) : $this->user['user_id'];
//		echo $pargam['goods_list'];
		$pargam['goods_list'] = json_decode($pargam['goods_list'], true);
//		var_dump($pargam['goods_list']) ;exit();
		if(empty($pargam['goods_list']) || empty($pargam['atype'])) $this->json_echo(apiReturn::go('001002'));
		$data['goods_list']  = array();
		$data['totle_money'] = 0;
		
		//活动类型
		switch($pargam['atype']){
			case 1: //秒杀
				foreach($pargam['goods_list'] as $k => $v){
					//商品详情
					$modelGoods = new IModel('goods');
					$infoGoods  = $modelGoods->getObj('id='.$v['goods_id'], 'id as goods_id,name,goods_no,img,sell_price,weight,store_nums');
					//校验
					if($infoGoods['store_nums']<$v['nums']) $this->json_echo(apiReturn::go('007002')); //商品库存不足
					$query         = new IQuery('activity_speed AS m');
					$query->join   = 'LEFT JOIN activity_speed_access AS a ON a.pid=m.id '.
						'LEFT JOIN goods AS g ON g.id=a.goods_id';
					$query->where  = 'g.is_del=0 AND m.type=2 AND m.status=1 AND a.goods_id='.$v['goods_id'];
					$query->fields = 'g.name,g.img,a.id,a.goods_id,g.sell_price,a.sell_price AS now_price,a.nums,a.quota,a.delivery,m.type,m.start_time,m.end_time';
					$query->limit  = 1;
					$info          = $query->find();
					if(empty($info)) $this->json_echo(apiReturn::go('006002', '', '"'.$infoGoods['name'].'"未参与活动'));
					$info               = $info[0];
					if($info['start_time']>time()) $this->json_echo(apiReturn::go('002018', '', '"'.$infoGoods['name'].'"活动未开始'));
					if($info['end_time']<time()) $this->json_echo(apiReturn::go('002019', '', '"'.$infoGoods['name'].'"活动已结束'));
					$queryOrder         = new IQuery('order AS m');
					$queryOrder->join   = 'LEFT JOIN order_goods AS g ON g.order_id=m.id';
					$queryOrder->fields = 'count(*) AS sum';
					$where              = 'm.pay_status=1 AND m.pay_time>="'.date('Y-m-d H:i:s', $info['start_time']).'" AND m.pay_time<="'.date('Y-m-d H:i:s', $info['end_time']).'" AND g.goods_id='.$info['goods_id'];
					//限购数量已售完
					$queryOrder->where = $where;
					$sum               = $queryOrder->find();
					if($sum[0]['sum']>$info['nums']+$v['nums']) $this->json_echo(apiReturn::go('006003', '', '"'.$infoGoods['name'].'"剩余数量不足'));
					//当前用户已购完
					if($info['quota']>0){
						$queryOrder->where = $where.' AND m.user_id='.$user_id;
						$sum               = $queryOrder->find();
						if($sum[0]['sum']>$info['quota']+$v['nums']) $this->json_echo(apiReturn::go('006003', '', '您已超出"'.$infoGoods['name'].'"限购数量'));
					}
					$info['img']          = empty($info['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$info['img']."/w/500/h/500");
					$info['count']         = $v['nums']; //商品数量
					$info['product_id'] = 0; //货号
					$info['seller_id']  = 0; //卖家ID
					$info['reduce']     = $info['sell_price']-$info['now_price']; //优惠金额
					
					$data['goods_list'][] = $info;
					$data['totle_money'] += $info['now_price']*$v['nums'];
				}
				break;
			default:
				$this->json_echo(apiReturn::go('007001'));
		}
		
		/* 收货地址 */
		$modelAddress         = new IModel('address');
		$data['address_list'] = $modelAddress->query('user_id='.$user_id, "*", "is_default desc");
		foreach($data['address_list'] as $k => $v){
			$temp   = area::name($v['province'], $v['city'], $v['area']);
			$temp_k = array_keys($temp);
			if(isset($temp[$v['province']]) && isset($temp[$v['city']]) && isset($temp[$v['area']])){
				$data['address_list'][$k]['province_val'] = in_array($v['province'], $temp_k) ? $temp[$v['province']] : '';
				$data['address_list'][$k]['city_val']     = in_array($v['city'], $temp_k) ? $temp[$v['city']] : '';
				$data['address_list'][$k]['area_val']     = in_array($v['area'], $temp_k) ? $temp[$v['area']] : '';
			}
		}
		$data['delivery_money'] = 0; //邮费
		
		/* 如果是订单确认，到这里结束 */
		if($pargam['colse']!=1) $this->json_echo(apiReturn::go('0', $data));
		
		/* 开始生成订单 */
		//查询收货地址
		$modelAdr = new IModel('address');
		$dataAdr  = $modelAdr->getObj('id='.$pargam['address_id'].' AND user_id='.$user_id);
		if(empty($dataAdr)) $this->json_echo(apiReturn::go('005001')); //收货地址不存在
		//生成的订单数据
		$dataArray = array(
			'order_no'        => Order_Class::createOrderNum(),
			'user_id'         => $user_id,
			'pay_type'        => 13, //微信支付
			'distribution'    => 1, //配送方式
			'payable_amount'  => $data['totle_money'], //商品价格
			'real_amount'     => $data['totle_money'], //实付商品价格
			'payable_freight' => $data['delivery_money'], //运费价格
			'real_freight'    => $data['delivery_money'], //实付运费价格
			'order_amount'    => $data['totle_money'], //订单总价
			'accept_name'     => $dataAdr['accept_name'], //收货人姓名
			'postcode'        => $dataAdr['zip'], //邮编
			'province'        => $dataAdr['province'], //省ID
			'city'            => $dataAdr['city'], //市ID
			'area'            => $dataAdr['area'], //区ID
			'address'         => $dataAdr['address'], //详细地址
			'mobile'          => $dataAdr['mobile'], //手机
			'pay_time'        => time(), //付款时间
			'create_time'     => time(),
			'note' => '活动商品购买',
			'type_source' => 1, //单个商品购买
		);
		//订单写入
		$modelOrder = new IModel('order');
		$modelOrder->setData($dataArray);
		$order_id = $modelOrder->add();
		if(!$order_id) $this->json_echo(apiReturn::go('003006'));
		//订单商品写入
		$orderInstance = new Order_Class();
		$orderInstance->insertOrderGoods($order_id, array('goodsList' => $data['goods_list']));
		/* 数据返回 */
		$this->json_echo(apiReturn::go('0',array('order_id'=>$order_id)));
	}
	
	
	/**
	 * ---------------------------------------------------优惠券---------------------------------------------------*
	 */
	/**
	 * 我的优惠券列表
	 */
	public function ticket_list_my(){
		/* 接收参数 */
		$type    = IFilter::act(IReq::get('type'), 'int');//[1可使用-2已过期]
		$page    = IFilter::act(IReq::get('page'), 'int');//分页编号
		$user_id = $this->user['user_id'];
		if(empty($user_id)) $this->json_echo(apiReturn::go('001001'));
		/* 可使用优惠券 */
		$query       = new IQuery('activity_ticket as m');
		$query->join = 'LEFT JOIN activity_ticket_access AS a ON a.ticket_id=m.id';
		switch($type){
			//可使用
			case 1:
				$where = 'a.user_id='.$user_id.' AND a.status=1 AND m.end_time>='.time();
				break;
			//已过期
			case 2:
				$where = 'a.user_id='.$user_id.' AND (a.status!=1 OR m.end_time<'.time().')';
				break;
			default:
				$this->json_echo(apiReturn::go('002015'));
		}
		$query->where    = $where;
		$query->fields   = 'a.id,m.name,m.start_time,m.end_time,m.type,m.rule';
		$query->page     = $page<1 ? 1 : $page;
		$query->pagesize = 100;
		$data            = $query->find();
		$totalPage       = $query->getTotalPage();
		if($page>$totalPage) $data = array();
		if(!empty($data)){
			foreach($data as $k => $v){
				$data[$k]['start_time'] = date('m-d', $v['start_time']);
				$data[$k]['end_time']   = date('m-d', $v['end_time']);
				switch($v['type']){
					//满减券
					case 1 :
						$rule               = explode(',', $v['rule']);
						$data[$k]['msg']    = '满'.$rule[0].'减'.$rule[1];
						$data[$k]['detail'] = $data[$k]['msg'].'满减券';
						break;
					//无门槛券
					case 2:
						$data[$k]['msg']    = '抵'.$v['rule'].'元';
						$data[$k]['detail'] = $v['rule'].'元无门槛券';
						break;
					//折扣券
					case 3:
						$data[$k]['msg']    = '全场'.($v['rule']*10).'折';
						$data[$k]['detail'] = ($v['rule']*10).'折折扣券';
						break;
					//商务合作券
					case 4:
						$data[$k]['msg']    = '抵'.$v['rule'].'元（不包邮）';
						$data[$k]['detail'] = $v['rule'].'元商务合作券';
						break;
					//包邮券
					case 5:
						$data[$k]['msg']    = '包邮券';
						$data[$k]['detail'] = '全场无上限包邮';
						break;
					//税值券
					case 6:
						$data[$k]['msg']    = '税值券';
						$data[$k]['detail'] = '税值券';
						break;
				}
			}
		}
		$this->json_echo(apiReturn::go('0', $data));
	}
	
	/**
	 * 领取优惠券
	 */
	public function get_ticket(){
		/* 接收参数 */
		$tid = IFilter::act(IReq::get('tid'), 'int');//优惠券ID，必填
		if(!isset($this->user['user_id']) || empty($this->user['user_id'])) $this->json_echo(apiReturn::go('001001'));
		$user_id = $this->user['user_id'];
		
		/* 优惠券详情 */
		$modelTic = new IModel('activity_ticket');
		$infoTic  = $modelTic->getObj('pid=0 AND id='.$tid.' AND end_time>'.time());
		if(empty($infoTic)) $this->json_echo(apiReturn::go('002007')); //优惠券不存在
		
		/* 是否已领取 */
		$modelAcc = new IModel('activity_ticket_access');
		$infoAcc  = $modelAcc->getObj('`from`=0 AND user_id='.$user_id.' AND ticket_id='.$tid);
		if(!empty($infoAcc)) $this->json_echo(apiReturn::go('002034')); //已经领取过该优惠券
		
		/* 开始领取 */
		$modelAcc->setData(array('user_id' => $user_id, 'ticket_id' => $tid, 'status' => 1, 'from' => 0, 'create_time' => time(),));
		$rel = $modelAcc->add();
		$this->json_echo(apiReturn::go($rel>0 ? '0' : '002031', '', '恭喜您已领取“'.$infoTic['name'].'”'));
	}
	
	/**
	 * 领取活动优惠券（随机）
	 */
	public function get_ticket_activity(){
		/* 接收参数 */
		$aid = IFilter::act(IReq::get('aid'), 'int');//活动ID，必填
		$pid = IFilter::act(IReq::get('pid'), 'int');//分享人ID，选填
		if(!isset($this->user['user_id']) || empty($this->user['user_id'])) $this->json_echo(apiReturn::go('001001'));
		$user_id = $this->user['user_id'];
		if($user_id==$pid) $pid = '';
		
		/* 活动详情 */
		$rel = Activity::checkStatus($aid); //检查活动状态
		if($rel['code']!=0) $this->json_echo($rel);
		$dataAti = $rel['data'];
		
		/* 包含的优惠券列表 */
		$queryTck         = new IQuery('activity_ticket');
		$queryTck->where  = 'pid='.$aid;
		$queryTck->fields = 'id,name,type,rule';
		$dataTck          = $queryTck->find();
		if(empty($dataTck)) $this->json_echo(apiReturn::go('002020')); //活动不包含优惠券
		$idTck = array(); //优惠券ID
		foreach($dataTck as $k => $v) $idTck[] = $v['id'];
		
		$modelAcc = new IModel('activity_ticket_access');
		/* 是否已领完 */
		$countAcc = $modelAcc->get_count('ticket_id in ('.implode(',', $idTck).')');
		if($countAcc>=$dataAti['num']) $this->json_echo(apiReturn::go('002022')); //优惠券已领完
		/* 是否已领取 */
		switch($aid){
			case 1: //新人活动
				$dataAcc = $modelAcc->getObj('`from`='.(empty($pid) ? 0 : $pid).' AND user_id='.$user_id.' AND ticket_id in ('.implode(',', $idTck).')');
				if(!empty($dataAcc)) $this->json_echo(apiReturn::go(empty($pid) ? '002021' : '002024')); //已领取过优惠券
				/* 开始领取 */
				$dataTckOn = $dataTck[rand(0, count($dataTck)-1)];
				break;
			case 2||3: //圣诞元旦活动
				$where   = 'user_id='.$user_id.' AND ticket_id in ('.implode(',', $idTck).') AND `from`=0';
				$dataAcc = $modelAcc->query($where, '*', 'id desc', 1);
				if(!empty($dataAcc) && strtotime(date('Y-m-d', time()))<=$dataAcc[0]['create_time']) $this->json_echo(apiReturn::go('002025')); //今天已经领取过优惠券
				if(!empty($pid)){
					$dataAcc = $modelAcc->getObj('user_id='.$user_id.' AND ticket_id in ('.implode(',', $idTck).') AND `from`!=0');
					if(!empty($dataAcc)) $this->json_echo(apiReturn::go('002029')); //好友分享的优惠券只能领取一次
				}
				/* 开始领取 */
				$dataTckOn = rand(1, 5)==1 ? $dataTck[0] : $dataTck[rand(1, count($dataTck)-1)];
				break;
			default:
				$dataAcc = $modelAcc->getObj('user_id='.$user_id.' AND ticket_id in ('.implode(',', $idTck).')');
				if(!empty($dataAcc)) $this->json_echo(apiReturn::go('002026')); //只能领取一次
		}
		
		/* 写入数据 */
		$modelAcc->setData(array('user_id' => $user_id, 'ticket_id' => $dataTckOn['id'], 'status' => 1, 'from' => empty($pid) ? 0 : $pid, 'create_time' => time(),));
		$rel = $modelAcc->add();
		if($rel==false) $this->json_echo(apiReturn::go('002023')); //领取失败
		
		/* 分享人增加积分 */
		if(!empty($pid)){
			//增加积分次数上限
			$countShare = $modelAcc->get_count('`from`='.(empty($pid) ? 0 : $pid).' AND ticket_id in ('.implode(',', $idTck).')');
			if($countShare<$dataAti['share_num']){
				$modelMember = new IModel('member');
				$modelMember->setData(array('point' => 'point+'.$dataAti['share_score']));
				$modelMember->update('user_id='.$pid, array('point'));
			}
		}
		
		/* 返回参数 */
		switch($dataTckOn['type']){
			//满减券
			case 1 :
				$rule             = explode(',', $dataTckOn['rule']);
				$dataTckOn['msg'] = '满'.$rule[0].'减'.$rule[1].'券';
				break;
			//无门槛券
			case 2:
				$dataTckOn['msg'] = '抵扣券'.$dataTckOn['rule'].'元';
				break;
			//折扣券
			case 3:
				$dataTckOn['msg'] = ($dataTckOn['rule']*10).'折券';
				break;
			//商务合作券
			case 4:
				$dataTckOn['msg'] = '商务合作券'.$dataTckOn['rule'].'元（不包邮）';
				break;
			//包邮券
			case 5:
				$dataTckOn['msg'] = '包邮券';
				break;
			//税值券
			case 6:
				$dataTckOn['msg'] = '税值券';
				break;
		}
		$this->json_echo(apiReturn::go('0', $dataTckOn, '恭喜您已领取“'.$dataTckOn['msg'].'”'));
	}
	
	/**
	 * ---------------------------------------------------活动---------------------------------------------------*
	 */
	/**
	 * 活动商品列表
	 */
	public function activity_goods_list(){
		$param = array(
			'pagesize' => 20, //每页显示条数
			'page'     => IFilter::act(IReq::get('page'), 'int'),//分页，选填
			'aid'      => IFilter::act(IReq::get('aid'), 'int'), //活动ID，选填
			'cid'      => IFilter::act(IReq::get('cid')), //分类ID，选填
			'bid'      => IFilter::act(IReq::get('bid')), //品牌ID，选填
			'did'      => IFilter::act(IReq::get('did'), 'int'), //推荐ID，选填
			'tag'      => IFilter::act(IReq::get('tag')), //标签，选填
		);
		$data  = Api::run('goodsList', $param);
		$this->json_echo(apiReturn::go('0', $data));
	}
	
	/**
	 * 领取消费成长礼品
	 */
	public function activity_grow_gift(){
		/* 接收参数 */
		$aid = IFilter::act(IReq::get('aid'), 'int'); //活动ID，必填
		$gid = IFilter::act(IReq::get('gid'), 'int'); //礼品ID，必填
		$did = IFilter::act(IReq::get('did'), 'int'); //收货地址ID，选填
		if(!isset($this->user['user_id']) || empty($this->user['user_id'])) $this->json_echo(apiReturn::go('001001'));
		$user_id = $this->user['user_id'];
		
		/* 活动详情 */
		$rel = Activity::checkStatus($aid); //检查活动状态
		if($rel['code']!=0) $this->json_echo($rel);
		$dataAti = $rel['data'];
		
		/* 是否已领取过 */
		$modelRec = new IModel('activity_grow_record');
		$dataRec  = $modelRec->getObj('user_id='.$user_id.' AND grow_id='.$gid);
		if(!empty($dataRec)) $this->json_echo(apiReturn::go('002032')); //已领取过礼包
		
		/* 获取礼品列表 */
		$queryGrow        = new IQuery('activity_grow');
		$queryGrow->where = 'pid='.$aid;
		$queryGrow->order = 'grow asc,id asc';
		$listGrow         = $queryGrow->find();
		foreach($listGrow as $k => $v){
			if($v['id']==$gid) $dataGrow = $v;
		}
		if(!isset($dataGrow) || empty($dataGrow)) $this->json_echo(apiReturn::go('002030')); //礼包不存在
		
		/* 计算活动期间内的消费金额 */
		$queryOrder         = new IQuery('order');
		$queryOrder->where  = 'user_id='.$user_id.' AND `pay_type`!=0 AND `status` IN (2,5) AND `pay_time`>="'.date('Y-m-d H:i:s', $dataAti['start_time']).'" AND `pay_time`<="'.date('Y-m-d H:i:s', $dataAti['end_time']).'"';
		$queryOrder->fields = 'SUM(`real_amount`) AS sum';
		$dataOrder          = $queryOrder->find();
		$sumMoney           = empty($dataOrder) ? 0 : floor($dataOrder[0]['sum']);
		if($dataGrow['grow']>$sumMoney) $this->json_echo(apiReturn::go('002027')); //不满足礼包领取条件
		
		switch($dataGrow['type']){
			/* 领取优惠券 */
			case 1:
				//检测礼包
				$modelTic = new IModel('activity_ticket');
				$dataTic  = $modelTic->getObj('id='.$dataGrow['did']);
				if(empty($dataTic)) $this->json_echo(apiReturn::go('002029')); //礼品不存在
				//记录领取记录
				$modelRec->setData(array('user_id' => $user_id, 'grow_id' => $gid, 'create_time' => time(),));
				$rel = $modelRec->add();
				if($rel>0){
					$modelAcc = new IModel('activity_ticket_access');
					$modelAcc->setData(array('user_id' => $user_id, 'ticket_id' => $dataGrow['did'], 'status' => 1, 'create_time' => time(),));
					$rel = $modelAcc->add();
					if($rel>0) $this->json_echo(apiReturn::go('0', '', '恭喜您已成功领取“'.$dataTic['name'].'”')); //领取成功
				}
				$modelRec->rollback(); //事务回滚
				$this->json_echo(apiReturn::go('002031')); //领取失败
				break;
			/* 领取商品 */
			case 2:
				//商品信息
				$modelGoods = new IModel('goods');
				$dataGoods  = $modelGoods->getObj('is_del!=1 AND id='.$dataGrow['did'], 'id as goods_id,name,goods_no,img,sell_price,weight,store_nums');
				if(empty($dataGoods)) $this->json_echo(apiReturn::go('005001')); //商品不存在
				if($dataGoods['store_nums']<1) $this->json_echo(apiReturn::go('002033')); //商品库存不足已领完
				$dataGoods['product_id'] = 0; //货号
				$dataGoods['count']      = 1; //商品数量
				$dataGoods['seller_id']  = 0; //卖家ID
				$dataGoods['reduce']     = $dataGoods['sell_price'];
				//查询收货地址
				$modelAdr = new IModel('address');
				$dataAdr  = $modelAdr->getObj('id='.$did.' AND user_id='.$user_id);
				if(empty($dataAdr)) $this->json_echo(apiReturn::go('005001')); //收货地址不存在
				
				//生成的订单数据
				$dataArray = array('order_no'        => Order_Class::createOrderNum(), 'user_id' => $user_id, 'pay_type' => 13, //微信支付
								   'distribution'    => 1, //配送方式
								   'payable_amount'  => 0, //商品价格
								   'real_amount'     => 0, //实付商品价格
								   'payable_freight' => 0, //运费价格
								   'real_freight'    => 0, //实付运费价格
								   'order_amount'    => 0, //订单总价
								   'accept_name'     => $dataAdr['accept_name'], //收货人姓名
								   'postcode'        => $dataAdr['zip'], //邮编
								   'province'        => $dataAdr['province'], //省ID
								   'city'            => $dataAdr['city'], //市ID
								   'area'            => $dataAdr['area'], //区ID
								   'address'         => $dataAdr['address'], //详细地址
								   'mobile'          => $dataAdr['mobile'], //手机
								   'pay_time'        => time(), //付款时间
								   'create_time'     => time(), 'note' => '活动商品赠送', 'type_source' => 1, //单个商品购买
				);
				//记录领取记录
				$modelRec->setData(array('user_id' => $user_id, 'grow_id' => $gid, 'create_time' => time(),));
				$rel = $modelRec->add();
				if($rel>0){
					//生成订单插入order表中
					$orderObj = new IModel('order');
					$orderObj->setData($dataArray);
					$order_id = $orderObj->add();
					if($order_id>0){
						//将订单中的商品插入到order_goods表
						$orderInstance = new Order_Class();
						$orderInstance->insertOrderGoods($order_id, array('goodsList' => array($dataGoods)));
						//直接免单
						$rel = Order_Class::updateOrderStatus($dataArray['order_no']);
						if($rel>0) $this->json_echo(apiReturn::go('0', '', '恭喜您已成功领取“'.$dataGoods['name'].'”')); //领取成功
					}
				}
				$modelRec->rollback(); //事务回滚
				$this->json_echo(apiReturn::go('002031')); //领取失败
				break;
			default:
				$this->json_echo(apiReturn::go('002028')); //礼包类型不存在
		}
	}
	
	/**
	 * 获取活动消费金额
	 */
	public function activity_grow_val(){
		/* 获取参数 */
		$aid = IFilter::act(IReq::get('aid'), 'int'); //活动ID，必填
		if(!isset($this->user['user_id']) || empty($this->user['user_id'])) $this->json_echo(apiReturn::go('001001'));
		$user_id = $this->user['user_id'];
		
		/* 活动详情 */
		$rel = Activity::checkStatus($aid); //检查活动状态
		if($rel['code']!=0) $this->json_echo($rel);
		$dataAti = $rel['data'];
		
		/* 计算活动期间内的消费金额 */
		$queryOrder         = new IQuery('order');
		$queryOrder->where  = 'user_id='.$user_id.' AND `pay_type`!=0 AND `status` IN (2,5) AND `pay_time`>="'.date('Y-m-d H:i:s', $dataAti['start_time']).'" AND `pay_time`<="'.date('Y-m-d H:i:s', $dataAti['end_time']).'"';
		$queryOrder->fields = 'SUM(`real_amount`) AS sum';
		$dataOrder          = $queryOrder->find();
		$dataOrder          = empty($dataOrder) ? 0 : floor($dataOrder[0]['sum']);
		
		/* 礼品列表 */
		$queryGrow         = new IQuery('activity_grow');
		$queryGrow->where  = 'pid='.$aid;
		$queryGrow->fields = 'id,grow,type';
		$queryGrow->order  = 'grow asc,id asc';
		$dataGrow          = $queryGrow->find();
		if(!empty($dataGrow)){
			$modelRec = new IModel('activity_grow_record');
			foreach($dataGrow as $k => $v){
				if($dataOrder<$v['grow']){
					$dataGrow[$k]['is_play'] = 1; //1不能领
				}else{
					$rel                     = $modelRec->getObj('user_id='.$user_id.' AND grow_id='.$v['id']);
					$dataGrow[$k]['is_play'] = empty($rel) ? 2 : 3; //2可以领-3已领取
				}
			}
		}
		
		/* 返回数据 */
		$this->json_echo(apiReturn::go('0', array('money' => $dataOrder, 'list' => $dataGrow)));
	}
	
	/**
	 * 砍价刀商品列表
	 */
	public function activity_bargain_list(){
		/* 获取参数 */
		$param   = array(
			'activity_id' => IFilter::act(IReq::get('activity_id'), 'int'), //砍价刀活动ID，必填
			'page'        => IFilter::act(IReq::get('page'), 'int'), //砍价刀活动ID，必填
		);
		
		/* 商品列表 */
		$queryGoods           = new IQuery('activity_bargain AS m');
		$queryGoods->join     = 'LEFT JOIN activity_bargain_access AS a ON a.pid=m.id '.
			'LEFT JOIN goods AS g ON g.id=a.goods_id';
		$queryGoods->where    = 'g.is_del=0 AND m.id='.$param['activity_id'];
		$queryGoods->fields   = 'g.id,g.name,g.img';
		$queryGoods->order    = 'g.sale DESC,g.visit DESC';
		$queryGoods->page     = $param['page']<1 ? 1 : $param['page'];
		$queryGoods->pagesize = 10;
		$listGoods            = $queryGoods->find();
		if($param['page']>$queryGoods->getTotalPage()) $listGoods = array();
		if(!empty($listGoods)){
			foreach($listGoods as $k => $v){
				$listGoods[$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
			}
		}
		
		/* 数据返回 */
		$this->json_echo(apiReturn::go('0', $listGoods));
	}
	
	/**
	 * 砍价刀商品详情
	 */
	public function activity_bargain_detail(){
		/* 获取参数 */
		$param   = array(
			'activity_id' => IFilter::act(IReq::get('activity_id'), 'int'), //砍价刀活动ID，必填
			'goods_id'    => IFilter::act(IReq::get('goods_id'), 'int'), //砍价刀活动ID，必填
			'user_id'     => IFilter::act(IReq::get('user_id'), 'int'), //邀请人ID，必填
		);
		$user_id = !empty($param['user']) ? $param['user'] : (isset($this->user['user_id']) && !empty($this->user['user_id']) ? $this->user['user_id'] : $this->json_echo(apiReturn::go('001001')));
		
		/* 商品详情 */
		$queryGoods         = new IQuery('activity_bargain AS m');
		$queryGoods->join   = 'LEFT JOIN activity_bargain_access AS a ON a.pid=m.id '.
			'LEFT JOIN goods AS g ON g.id=a.goods_id';
		$queryGoods->where  = 'g.is_del=0 AND m.id='.$param['activity_id'].' AND a.goods_id='.$param['goods_id'];
		$queryGoods->fields = 'a.id,a.goods_id,a.min_price,g.name,g.img,g.sell_price,m.start_time,m.end_time,m.status';
		$infoGoods          = $queryGoods->find();
		if(empty($infoGoods)) $this->json_echo(apiReturn::go('006001'));
		$infoGoods        = $infoGoods[0];
		$infoGoods['img'] = empty($infoGoods['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$infoGoods['img']."/w/500/h/500");
		/* 砍价金额 */
		$queryPrice                = new IQuery('activity_bargain_user');
		$queryPrice->where         = 'pid='.$infoGoods['id'].' AND byuser='.$user_id;
		$queryPrice->fields        = 'SUM(`money`) AS exempt_price,count(*) AS count';
		$rel                       = $queryPrice->find();
		$infoGoods['exempt_price'] = empty($rel[0]['exempt_price']) ? 0 : $rel[0]['exempt_price'];
		$infoGoods['count']        = empty($rel[0]['count']) ? 0 : $rel[0]['count'];
		$now_price = $infoGoods['sell_price']- $infoGoods['exempt_price'];
		$infoGoods['now_price']    = $now_price<=0 ? 0 : $now_price;
		$infoGoods['ratio']        = $infoGoods['now_price']==0 ? 1 : ($infoGoods['min_price']/$infoGoods['now_price']-0.1>=1 ? 0.9 : $infoGoods['min_price']/$infoGoods['now_price']-0.1);
		/* 砍价记录 */
		$queryBargain         = new IQuery('activity_bargain_user AS m');
		$queryBargain->join   = 'LEFT JOIN user AS u ON u.id=m.touser';
		$queryBargain->where  = 'pid='.$infoGoods['id'].' AND byuser='.$user_id;
		$queryBargain->fields = 'm.id,u.username,m.money,m.create_time';
		$infoGoods['list']    = $queryBargain->find();
		
		/* 数据返回 */
		$this->json_echo(apiReturn::go('0', $infoGoods));
	}
	
	/**
	 * 砍价
	 */
	public function activity_bargain_start(){
		/* 获取参数 */
		$param   = array(
			'activity_id' => IFilter::act(IReq::get('activity_id'), 'int'), //砍价刀活动ID，必填
			'goods_id'    => IFilter::act(IReq::get('goods_id'), 'int'), //砍价刀活动ID，必填
			'user_id'     => IFilter::act(IReq::get('user_id'), 'int'), //邀请人ID，必填
		);
		$user_id = isset($this->user['user_id']) && !empty($this->user['user_id']) ? $this->user['user_id'] : $this->json_echo(apiReturn::go('001001'));
		
		/* 校验 */
		$queryGoods         = new IQuery('activity_bargain AS m');
		$queryGoods->join   = 'LEFT JOIN activity_bargain_access AS a ON a.pid=m.id '.
			'LEFT JOIN goods AS g ON g.id=a.goods_id';
		$queryGoods->where  = 'g.is_del=0 AND m.id='.$param['activity_id'].' AND a.goods_id='.$param['goods_id'];
		$queryGoods->fields = 'a.id,a.goods_id,a.min_price,a.rand,g.name,g.img,g.sell_price,m.start_time,m.end_time,m.status';
		$infoGoods          = $queryGoods->find();
		if(empty($infoGoods))
			$this->json_echo(apiReturn::go('006001'));
		$infoGoods = $infoGoods[0];
		if($infoGoods['status']!=1)
			$this->json_echo(apiReturn::go('002017')); //活动未开始
		if($infoGoods['start_time']>time())
			$this->json_echo(apiReturn::go('002018')); //活动未开始
		if($infoGoods['end_time']<time())
			$this->json_echo(apiReturn::go('002019')); //活动已结束
		//是否已砍过价
		$modelBargain = new IModel('activity_bargain_user');
		$rel          = $modelBargain->getObj('pid='.$infoGoods['id'].' AND byuser='.$param['user_id'].' AND touser='.$user_id);
		if(!empty($rel))
			$this->json_echo(apiReturn::go('002035')); //已砍过价
		
		/* 进行砍价 */
		$rand = explode(',', $infoGoods['rand']);
		if(count($rand)!=2)
			$this->json_echo(apiReturn::go('002036')); //活动配置错误
		//已砍价金额
		$queryPrice         = new IQuery('activity_bargain_user');
		$queryPrice->where  = 'pid='.$infoGoods['id'].' AND byuser='.$param['user_id'];
		$queryPrice->fields = 'SUM(`money`) AS exempt_price';
		$rel                = $queryPrice->find();
		$exempt_price       = empty($rel[0]['exempt_price']) ? 0 : $rel[0]['exempt_price'];
		$possible_price     = floor($infoGoods['sell_price']-$infoGoods['min_price']-$exempt_price);
		$possible_price     = $possible_price<0 ? 0 : $possible_price;
		$rand[0]            = $possible_price<$rand[0] ? $possible_price : $rand[0];
		$rand[1]            = $possible_price<$rand[1] ? $possible_price : $rand[1];
		$money              = $possible_price<=0 ? 0.1 : rand($rand[0], $rand[1]);
		//写入
		$modelBargain->setData(array(
			'pid'         => $infoGoods['id'],
			'byuser'      => $param['user_id'],
			'touser'      => $user_id,
			'money'       => $money,
			'create_time' => time(),
		));
		$rel = $modelBargain->add();
		$this->json_echo(apiReturn::go($rel>0 ? '0' : '002037'));
	}
	
	/**
	 * 秒杀商品列表
	 */
	public function activity_speed_list(){
		/* 接收参数 */
		$param = array(
			'type'    => IFilter::act(IReq::get('type'), 'int'), //活动类型[1限时购-2秒杀]，必填
			'time_id' => IFilter::act(IReq::get('time_id'), 'int'), //活动类型[1限时购-2秒杀]，必填
			'page'    => IFilter::act(IReq::get('page'), 'int'), //分页编号
		);
		/* 秒杀时间段列表 */
		$time               = strtotime(date('Y-m-d', time()));
		$querySpeed         = new IQuery('activity_speed');
		$querySpeed->where  = 'type='.$param['type'].' AND status=1 AND start_time>='.$time.' AND end_time<='.($time+(60*60*24));
		$querySpeed->fields = 'id,start_time,end_time';
		$querySpeed->order  = 'start_time ASC';
		$querySpeed->limit  = 3;
		$listSpeed          = $querySpeed->find();
		if(!empty($listSpeed)){
			foreach($listSpeed as $k => $v){
				$listSpeed[$k]['now'] = $flag = 0;
				if(($k==0 && time()<$v['start_time']) || time()>$v['start_time']){
					if($flag==0){
						$listSpeed[$k]['now'] = $flag = 1;
						$param['time_id']     = empty($param['time_id']) ? $v['id'] : $param['time_id'];
					}
				}
			}
		}
		/* 秒杀商品列表 */
		$queryGoods           = new IQuery('activity_speed_access AS m');
		$queryGoods->join     = 'LEFT JOIN goods AS g ON g.id=m.goods_id';
		$queryGoods->fields   = 'g.id,g.name,g.store_nums,m.nums,m.sell_price,g.img';
		$queryGoods->where    = 'g.is_del=0 AND pid='.$param['time_id'];
		$queryGoods->page     = $param['page']<1 ? 1 : $param['page'];
		$queryGoods->pagesize = 10;
		$listGoods            = $queryGoods->find();
		if($param['page']>$queryGoods->getTotalPage()) $listGoods = array();
		if(!empty($listGoods)){
			foreach($listGoods as $k => $v){
				$listGoods[$k]['img']        = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
				$listGoods[$k]['store_nums'] = $v['nums']<$v['store_nums'] ? $v['nums'] : $v['store_nums'];
			}
		}
		
		var_dump(array('time_list' => $listSpeed, 'goods_list' => $listGoods));exit();
		/* 数据返回 */
		$this->json_echo(apiReturn::go('0', array('time_list' => $listSpeed, 'goods_list' => $listGoods)));
	}
	
	/**
	 * 圣诞节活动首页
	 */
	public function christmas_index(){
		/* 获取活动热销商品 */
		$query         = new IQuery('goods as m');
		$query->join   = 'LEFT JOIN brand AS b ON b.id=m.brand_id LEFT JOIN commend_goods AS d ON d.goods_id=m.id LEFT JOIN category_extend AS c ON c.goods_id=m.id ';
		$query->fields = 'm.id,m.name,m.sell_price,m.original_price,m.img,b.name as brand_name,b.logo as brand_logo';
		$query->order  = 'm.sale desc,m.visit desc';
		$query->group  = 'm.id';
		$data          = array();
		for($i = 1; $i<=8; $i++){
			$limit = 3;
			switch($i){
				case 1: //排行商品
					$limit = 10;
					$where = 'm.is_del=0';
					break;
				case 2: //大牌专场
					$where = 'm.is_del=0 AND b.id IN (26,32,56,74,78,82,100)'; //"资生堂","花王","嘉娜宝","ROSETTE","dhc","高丝","小林制药"
					break;
				case 3: //聚划算
					$where = 'm.is_del=0 AND m.activity=3';
					break;
				case 4: //狗子推荐
					$where = 'm.is_del=0 AND d.commend_id=4 AND c.category_id IN ('.goods_class::catChild(126).')';
					break;
				case 5: //奶糖推荐
					$where = 'm.is_del=0 AND d.commend_id=4 AND c.category_id IN ('.goods_class::catChild(134).')';
					break;
				case 6: //腿毛推荐
					$where = 'm.is_del=0 AND d.commend_id=4 AND c.category_id IN ('.goods_class::catChild(6).')';
					break;
				case 7: //昔君推荐
					$where = 'm.is_del=0 AND d.commend_id=4 AND c.category_id IN ('.goods_class::catChild(2).')';
					break;
				case 8: //一哥推荐
					$where = 'm.is_del=0 AND d.commend_id=4 AND c.category_id IN ('.goods_class::catChild(7).')';
					break;
			}
			$query->where    = $where;
			$query->limit    = $limit;
			$data['list'.$i] = $query->find();
			if(!empty($data['list'.$i])){
				/* 计算活动商品价格 */
				$data['list'.$i] = api::run('goodsActivity', $data['list'.$i]);
				foreach($data['list'.$i] as $k => $v){
					$data['list'.$i][$k]['diff_price'] = $v['original_price']-$v['sell_price']; //差价
					$data['list'.$i][$k]['img']        = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
					$data['list'.$i][$k]['brand_logo'] = empty($v['brand_logo']) ? '' : IWeb::$app->config['image_host'].'/'.$v['brand_logo'];
				}
			}
		}
		/* 专场分类 */
		$data['cat'] = array(array('id' => 1, 'name' => '药妆', 'cid' => 126,), array('id' => 2, 'name' => '个护', 'cid' => 134,), array('id' => 3, 'name' => '宠物', 'cid' => 6,), array('id' => 4, 'name' => '健康', 'cid' => 2,), array('id' => 5, 'name' => '零食', 'cid' => 7,),);
		/* 数据返回 */
		$this->json_echo(apiReturn::go('0', $data));
	}
	
	/**
	 * 大牌专区
	 */
	public function christmas_brand_list(){
		$brand_ids = '26,32,56,74,78,82,100'; //需要显示的品牌ID
		/* 品牌列表 */
		$queryBrand         = new IQuery('brand');
		$queryBrand->where  = 'id IN ('.$brand_ids.') AND logo IS NOT NULL';
		$queryBrand->fields = 'id,name,logo';
		$queryBrand->limit  = 6;
		$listBrand          = $queryBrand->find();
		foreach($listBrand as $k => $v){
			//logo
			$listBrand[$k]['log'] = empty($v['logo']) ? '' : IWeb::$app->config['image_host'].'/'.$v['logo'];
		}
		
		/* 品牌下的最热商品3个 */
		$queryGoods         = new IQuery('goods');
		$queryGoods->where  = 'is_del=0 AND brand_id IN ('.$brand_ids.')';
		$queryGoods->fields = 'id,name,sell_price,img';
		$queryGoods->order  = 'sale DESC,visit DESC';
		$queryGoods->limit  = 3;
		$listGoods          = $queryGoods->find();
		/* 更新活动商品价格 */
		$listGoods = Api::run('goodsActivity', $listGoods);
		foreach($listGoods as $k => $v){
			$listGoods[$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
		}
		
		/* 返回参数 */
		$data = array('goods_list' => $listGoods, //商品列表
					  'brand_list' => $listBrand, //品牌列表
		);
		$this->json_echo(apiReturn::go('0', $data));
	}
	
	/**
	 * ---------------------------------------------------购物车-收货地址---------------------------------------------------*
	 */
	/**
	 * 获取用户收货地址数据
	 */
	public function address_list(){
		//游客的user_id默认为0
		$user_id = ($this->user['user_id']==null) ? 0 : $this->user['user_id'];
		//获取收货地址
		$addressObj  = new IModel('address');
		$addressList = $addressObj->query('user_id = '.$user_id, "*", "is_default desc");
		foreach($addressList as $key => $data){
			$temp                              = area::name($data['province'], $data['city'], $data['area']);
			$temp_k                            = array_keys($temp);
			$addressList[$key]['province_val'] = in_array($data['province'], $temp_k) ? $temp[$data['province']] : '';
			$addressList[$key]['city_val']     = in_array($data['city'], $temp_k) ? $temp[$data['city']] : '';
			$addressList[$key]['area_val']     = in_array($data['area'], $temp_k) ? $temp[$data['area']] : '';
		}
		$this->json_echo($addressList);
	}

    //添加和编辑地址
    function address_add(){
        $id          = IFilter::act(IReq::get('id'), 'int');
        $accept_name = IFilter::act(IReq::get('accept_name'));
        $province    = IFilter::act(IReq::get('province'), 'int');
        $city        = IFilter::act(IReq::get('city'), 'int');
        $area        = IFilter::act(IReq::get('area'), 'int');
        $address     = IFilter::act(IReq::get('address'));
        $zip         = IFilter::act(IReq::get('zip'));
        $mobile      = IFilter::act(IReq::get('mobile'));
        $card        = IFilter::act(IReq::get('card'));
        $image1      = IFilter::act(IReq::get('aaa'), 'string');
        $image2      = IFilter::act(IReq::get('bbb'), 'string');
        $user_id     = $this->user['user_id'];

        //编辑默认地址
        $is_default = IFilter::act(IReq::get('is_default'), 'int');
        if(isset($is_default) && !empty($is_default)){
            if(empty($this->user['user_id'])){
                header("Content-type: application/json");
                echo json_encode(array('msg' => $this->user['user_id'].'用户未登录'));
                exit();
            }

            $model = new IModel('address');
            $model->setData(array('is_default' => 0));
            $model->update("user_id = ".$this->user['user_id']);
            $model->setData(array('is_default' => '1'));
            $model->update("id = ".$id." and user_id = ".$this->user['user_id']);
            die(JSON::encode(array('ret' => true)));
        }

        //整合的数据
        $sqlData = array('user_id'  => $user_id, 'accept_name' => $accept_name, 'zip' => $zip, //			'telphone'    => $telphone,
                         'province' => $province, 'city' => $city, 'area' => $area, 'address' => $address, 'mobile' => $mobile,'sfz_num' => $card);

        $checkArray = $sqlData;
        unset($checkArray['zip'], $checkArray['user_id'], $checkArray['area']);
        //        unset($checkArray['telphone'],$checkArray['zip'],$checkArray['user_id']);
        foreach($checkArray as $key => $val){
            if(!$val){
                $result = array('result' => false, 'msg' => '请仔细填写收货地址');
                die(JSON::encode($result));
            }
        }

        if($user_id){

            //            添加收货人的实名信息
            $access_token = common::get_wechat_access_token();
            $dir  = isset(IWeb::$app->config['upload']) ? IWeb::$app->config['upload'] : 'upload';
            $dir .= '/sfz_image';
            if (!empty($image1)){
                $url1 = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id=' . $image1;
                $image1 = common::save_url_image($url1,$dir,1);
            } else {
                $image1 = IFilter::act(IReq::get('image_saved1'),'string');
            }
            if (!empty($image2)){
                $url2 = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id=' . $image2;
                $image2 = common::save_url_image($url2,$dir,2);
            } else {
                $image2 = IFilter::act(IReq::get('image_saved2'),'string');
            }

            $sqlData['sfz_image1'] = $image1;
            $sqlData['sfz_image2'] = $image2;


            $model = new IModel('address');
            if($id){
                $model->setData($sqlData);
                $model->update("id = ".$id." and user_id = ".$user_id);
            }else{
                $model->setData(array('is_default' => 0));
                $model->update("user_id = ".$this->user['user_id']);
                $sqlData['is_default'] = 1;
                $model->setData($sqlData);
                $id = $model->add();
            }
            $sqlData['id'] = $id;
        }//访客地址保存
        else{
            //            ISafe::set("address",$sqlData);
        }

        $areaList                = area::name($province, $city, $area);
        $areaList_k              = array_keys($areaList);
        $sqlData['province_val'] = in_array($province, $areaList_k) ? $areaList[$province] : '';
        $sqlData['city_val']     = in_array($city, $areaList_k) ? $areaList[$city] : '';
        $sqlData['area_val']     = in_array($area, $areaList_k) ? $areaList[$area] : '';
        $result                  = array('data' => $sqlData);

        $this->json_echo($result);
    }
	
	/**
	 * @brief 收货地址删除处理
	 */
	public function address_del(){
		$id    = IFilter::act(IReq::get('id'), 'int');
		$model = new IModel('address');
		$data  = $model->query('id = '.$id.' and user_id = '.$this->user['user_id']);
		if($data[0]['is_default']==1){
			$ret = false;
		}else{
			$model->del('id = '.$id.' and user_id = '.$this->user['user_id']);
			$ret = true;
		}
		$this->json_echo(['ret' => $ret]);
	}
	
	/**
	 * @brief 设置默认的收货地址
	 */
	public function address_default(){
		$id      = IFilter::act(IReq::get('id'), 'int');
		$default = IFilter::act(IReq::get('is_default'));
		$model   = new IModel('address');
		if($default==1){
			$model->setData(array('is_default' => 0));
			$model->update("user_id = ".$this->user['user_id']);
		}
		$model->setData(array('is_default' => $default));
		$model->update("id = ".$id." and user_id = ".$this->user['user_id']);
		$model->update("id = ".$id." and user_id = ".$this->user['user_id']);
		
		$this->json_echo(array('ret' => true));
	}
	
	/**
	 * ---------------------------------------------------订单---------------------------------------------------*
	 */
	/**
	 * 订单列表
	 */
	public function order_list(){
		/* 获取数据 */
		$type  = IFilter::act(IReq::get('type'), 'string'); //类型为u时，店铺主
		$class = IFilter::act(IReq::get('class'), 'int'); //分类ID[0全部订单-1待付款-2待发货-3待收货-4已完成]
		$page  = IFilter::act(IReq::get('page'), 'int'); //分页编号
		if($this->user['user_id']==null) $this->json_echo(apiReturn::go('001001'));
		$user = array($this->user['user_id']);
		
		/* 分类 */
		switch($class){
			//全部订单
			case 0:
				$where = 'pay_type!=0 AND status!=3 AND status!=4';
				break;
			//待付款
			case 1:
				$where = 'pay_type!=0 AND status=1';
				break;
			//待发货
			case 2:
				$where = 'pay_type!=0 AND status=2 AND distribution_status=0';
				break;
			//待收货
			case 3:
				$where = 'pay_type!=0 AND status=2 AND distribution_status in (1,2)';
				break;
			//已完成
			case 4:
				$where = 'pay_type!=0 AND status=5';
				break;
			default:
				$this->json_echo(apiReturn::go('003001'));
		}
		
		/* 店铺主 */
		if($type=='u'){
			$queryUser         = new IQuery('user as m');
			$queryUser->join   = 'LEFT JOIN shop AS a ON a.identify_id=m.shop_identify_id';
			$queryUser->where  = 'own_id='.$this->user['user_id'];
			$queryUser->fields = 'm.id';
			$dataUser          = $queryUser->find();
			if(!empty($dataUser)){
				foreach($dataUser as $k => $v){
					$user[] = $v['id'];
				}
			}
		}
		
		/* 查询订单 */
		$query           = new IQuery('order');
		$query->where    = 'if_del=0 AND '.$where.' AND user_id IN ('.implode(',', array_unique($user)).')';
		$query->fields   = 'id,order_no,order_amount,status,pay_type,distribution_status';
		$query->order    = 'id desc';
		$query->page     = $page<1 ? 1 : $page;
		$query->pagesize = 10;
		$data            = $query->find();
		$totalPage       = $query->getTotalPage();
		if($page>$totalPage) $data = array();
		if(!empty($data)){
			$relation   = array('已完成' => '删除订单', '等待发货' => '取消订单', '等待付款' => '去支付', '已发货' => '查看物流', '已取消' => '已取消', '部分发货' => '查看物流');
			$relation_k = array_keys($relation);
			foreach($data as $k => $v){
				//订单状态
				$data[$k]['orderStatusVal']  = Order_Class::getOrderStatus($v);
				$data[$k]['orderStatusText'] = Order_Class::orderStatusText($data[$k]['orderStatusVal']);
				
				//按键名称
				$data[$k]['text'] = in_array($data[$k]['orderStatusText'], $relation_k) ? $relation[$data[$k]['orderStatusText']] : '';
				//商品列表
				$data[$k]['goodslist'] = Api::run('getOrderGoodsListByGoodsid', array('#order_id#', $v['id']));
				foreach($data[$k]['goodslist'] as $k1 => $v1){
					$data[$k]['goodslist'][$k1]['goods_array'] = json_decode($v1['goods_array'], true);
					$data[$k]['goodslist'][$k1]['img']         = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/160/h/160");
				}
				//订单扩展数据
				//     			$data[$k]['order_info'] 			= (new order_class())->getOrderShow($data[$k]['id'],$this->user['user_id']);
			}
		}
		
		$this->json_echo(apiReturn::go('0', $data));
	}
	
	/**
	 * @brief 订单详情
	 * @return String
	 */
	public function order_detail(){
		/* 接收参数 */
		$id = IFilter::act(IReq::get('id'), 'int');
		
		/* 订单详情 */
		$orderObj   = new order_class();
		$order_info = $orderObj->getOrderShow($id, $this->user['user_id']);
		if(!$order_info) $this->json_echo(apiReturn::go('003002')); //订单不存在
		
		$orderStatus = Order_Class::getOrderStatus($order_info);
		switch($orderStatus){
			case 2: //待支付
				$orderStatusT = 0;
				break;
			case 4: //待发货
				$orderStatusT = 1;
				break;
			case 3 || 8 || 11: //待收货
				$orderStatusT = 2;
				break;
			case 6: //已完成
				$orderStatusT = 3;
				break;
		}
		
		/* 订单商品 */
		$order_goods = Api::run('getOrderGoodsListByGoodsid', array('#order_id#', $order_info['id']));
		foreach($order_goods as $k => $v){
			$order_goods[$k]['goods_array'] = json_decode($v['goods_array'], true);
			$order_goods[$k]['img']         = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$order_goods[$k]['img']."/w/160/h/160");
		}
		$data = array(
			'order_info'  => $order_info, //订单详情
			'orderStatus' => $orderStatusT, //订单状态
			"order_step"  => Order_Class::orderStep($order_info), //订单流向
			"order_goods" => $order_goods, //订单商品
			'is_refunds'  => Order_Class::isRefundmentApply($order_info)==true ? 1 : 0, //是否允许退款
		);
		$this->json_echo($data);
	}
	
	/**
	 * 订单商品列表
	 */
	public function order_goods_list(){
		/* 接收参数 */
		$order_id = IFilter::act(IReq::get('order_id'), 'int');
		$user_id  = isset($this->user['user_id']) && !empty($this->user['user_id']) ? $this->user['user_id'] : $this->json_echo(apiReturn::go('001001'));
		if(empty($order_id)) $this->json_echo(apiReturn::go('001002')); //缺少参数
		//订单详情
		$modelOrder = new IModel('order');
		$infoOrder  = $modelOrder->getObj('id='.$order_id.' AND user_id='.$user_id);
		if(empty($infoOrder)) $this->json_echo(apiReturn::go('003002'));
		/* 订单商品 */
		$goodsList = Api::run('getOrderGoodsListByGoodsid', array('#order_id#', $infoOrder['id']));
		if(!empty($goodsList)){
			foreach($goodsList as $k => $v){
				$goodsList[$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
			}
		}
		$this->json_echo(apiReturn::go('0', $goodsList));
	}
	
	/**
	 * 申请退款
	 */
	public function order_refunds(){
		/* 接受参数 */
		$goods_id = IFilter::act(IReq::get('goods_id')); //商品ID
		$order_id = IFilter::act(IReq::get('order_id'), 'int'); //订单ID
		$content  = IFilter::act(IReq::get('content'), 'text'); //退款理由
		$user_id  = isset($this->user['user_id']) && !empty($this->user['user_id']) ? $this->user['user_id'] : $this->json_echo(apiReturn::go('001001'));
		if(empty(trim($content)) || empty($goods_id)) $this->json_echo(apiReturn::go('003003')); //请填写退款理由和选择要退款的商品
		
		/* 检测退款 */
		$modelOrder = new IModel('order');
		$infoOrder  = $modelOrder->getObj("id=".$order_id." AND user_id=".$user_id);
		if(empty($infoOrder)) $this->json_echo(apiReturn::go('003002'));
		//订单商品列表
		$queryGoods         = new IQuery('order_goods');
		$queryGoods->where  = 'order_id='.$order_id;
		$queryGoods->fields = 'id,goods_id';
		$listGoods          = $queryGoods->find();
		$goodsIds           = array();
		foreach($listGoods as $k => $v){
			$goodsIds[] = $v['goods_id'];
		}
		$goodsIds = array_intersect($goodsIds, explode(',', $goods_id));
		if(empty($goodsIds)) $this->json_echo(apiReturn::go('003005'));
		//是否可退款
		$rel = Order_Class::isRefundmentApply($infoOrder, $goodsIds);
		if($rel!==true) $this->json_echo(apiReturn::go('-1', '', $rel));
		
		/* 写入 */
		$updateData   = array(
			'order_no'       => $infoOrder['order_no'],
			'order_id'       => $order_id,
			'user_id'        => $user_id,
			'time'           => ITime::getDateTime(),
			'content'        => $content,
			'seller_id'      => $infoOrder['seller_id'],
			'order_goods_id' => implode(',',$goodsIds),
		);
		$modelRefunds = new IModel('refundment_doc');
		$modelRefunds->setData($updateData);
		$rel = $modelRefunds->add();
		/* 结果 */
		$this->json_echo(apiReturn::go($rel>0 ? '0' : '003004'));
	}
	/**
	 * 订单评论
	 */
	public function comment_add(){
		$id      = IFilter::act(IReq::get('id'), 'int');
		$content = IFilter::act(IReq::get("contents"));
		if(!$id || !$content){
			IError::show(403, "填写完整的评论内容");
		}
		
		if(!isset($this->user['user_id']) || !$this->user['user_id']){
			IError::show(403, "未登录用户不能评论");
		}
		
		$data = array(
			'point'        => IFilter::act(IReq::get('point'), 'float'),
			'contents'     => $content,
			'status'       => 1,
			'comment_time' => ITime::getNow("Y-m-d"),
		);
		
		if($data['point']==0){
			IError::show(403, "请选择分数");
		}
		
		$result = Comment_Class::can_comment($id, $this->user['user_id']);
		if(is_string($result)){
			IError::show(403, $result);
		}
		
		$tb_comment = new IModel("comment");
		$tb_comment->setData($data);
		$re = $tb_comment->update("id={$id}");
		
		if($re){
			$commentRow = $tb_comment->getObj('id = '.$id);
			
			//同步更新goods表,comments,grade
			$goodsDB = new IModel('goods');
			$goodsDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$goodsDB->update('id = '.$commentRow['goods_id'], array('grade', 'comments'));
			
			//同步更新seller表,comments,grade
			$sellerDB = new IModel('seller');
			$sellerDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$sellerDB->update('id = '.$commentRow['seller_id'], array('grade', 'comments'));
			$this->redirect("/site/comments_list/id/".$commentRow['goods_id']);
		}else{
			IError::show(403, "评论失败");
		}
	}
	
	/**
	 * ---------------------------------------------------物流---------------------------------------------------*
	 */
	/**
	 * ---------------------------------------------------商品---------------------------------------------------*
	 */
	//限时购
	public function pro_speed_list(){
		$query          = new IQuery("promotion as p");
		$query->join    = "left join goods as go on p.condition = go.id";
		$query->fields  = "date_format(p.end_time,'%Y%m%d%H%i%s') as end_time,go.is_del,p.name";
		$query->where   = "p.type = 1 and p.seller_id = 0 and p.start_time > NOW() group by p.name order by start_time";
		$query->limit   = 1;
		$items          = $query->find();
		$query2         = new IQuery("promotion as p");
		$query2->join   = "left join goods as go on p.condition = go.id";
		$query2->fields = "go.id as goods_id,go.is_del,p.name as pname,p.award_value, go.name,go.sell_price,go.img";
		$query2->where  = sprintf("p.type = 1 and p.seller_id = 0 and p.name = '%s'", $items[0]['name']);
		$query2->limit  = 6;
		$items2         = $query2->find();
		foreach($items2 as $key => $value){
			$items2[$key]['img_thumb'] = IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/230/h/230");
		}
		$items[0]['child'] = $items2;
		$this->json_echo($items[0]);
	}
	
	/**
	 * 商品列表
	 */
	public function goods_list(){
		/* 接收参数 */
		$page = IFilter::act(IReq::get('page'), 'int');//分页，选填
		$aid  = IFilter::act(IReq::get('aid'), 'int'); //活动ID，选填
		$cid  = IFilter::act(IReq::get('cid')); //分类ID，选填
		$bid  = IFilter::act(IReq::get('bid')); //品牌ID，选填
		$did  = IFilter::act(IReq::get('did'), 'int'); //推荐ID，选填
		$tag  = IFilter::act(IReq::get('tag')); //标签，选填
		
		/* 获取下级分类 */
		if(!empty($cid)){
			$queryCat         = new IQuery('category');
			$queryCat->where  = 'visibility=1 AND parent_id IN ('.$cid.')';
			$queryCat->fields = 'id';
			$dataCat          = $queryCat->find();
			if(!empty($dataCat)){
				foreach($dataCat as $k => $v){
					$cid .= ','.$v['id'];
				}
			}
		}
		
		/* 获取数据 */
		$query           = new IQuery('goods as m');
		$query->join     = 'LEFT JOIN category_extend AS c ON c.goods_id=m.id '.'LEFT JOIN brand AS b ON b.id=m.brand_id '.'LEFT JOIN commend_goods AS d ON d.goods_id=m.id';
		$query->where    = 'm.is_del=0'.(empty($aid) ? '' : ' AND m.activity='.$aid). //活动ID
			(empty($cid) ? '' : ' AND c.category_id IN ('.$cid.')'). //分类ID
			(empty($bid) ? '' : ' AND m.brand_id IN ('.$bid.')'). //品牌ID
			(empty($did) ? '' : ' AND d.commend_id='.$did). //推荐ID
			(empty($tag) ? '' : ' AND m.search_words LIKE "%,'.$tag.',%"'); //标签
		$query->fields   = 'm.id,m.name,m.sell_price,m.original_price,m.img,m.activity,m.jp_price,m.market_price,b.name AS brand_name,b.logo AS brand_logo';
		$query->order    = 'm.sale desc,m.visit desc';
		$query->group    = 'm.id';
		$query->page     = $page<1 ? 1 : $page;
		$query->pagesize = 20;
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
		$this->json_echo(apiReturn::go('0', $data));
	}
	
	/**
	 * 商品详情
	 */
	public function goods_detail(){
		/* 获取参数 */
		$goods_id = IFilter::act(IReq::get('id'), 'int'); //商品ID
		$user_id  = isset($this->user['user_id']) && !empty($this->user['user_id']) ? $this->user['user_id'] : 0;
		
		/* 商品详情 */
		$modelGoods = new IModel('goods');
		$fields     = 'id,name,goods_no,brand_id,sell_price,original_price,jp_price,market_price,store_nums,weight,img,content';
		$dataGoods  = $modelGoods->getObj('is_del=0 AND id='.$goods_id, $fields);
		if(empty($dataGoods)) $this->json_echo(apiReturn::go('006001')); //商品不存在
		/* 计算活动商品价格 */
		$dataGoods             = Api::run('goodsActivity', $dataGoods);
		$dataGoods['discount'] = empty($dataGoods['original_price']) ? '' : round($dataGoods['sell_price']/$dataGoods['original_price'], 2)*10; //计算折扣率
		$dataGoods['img']      = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$dataGoods['img']."/w/500/h/500");
		
		/* 商品图 */
		$queryPhoto         = new IQuery('goods_photo_relation as g');
		$queryPhoto->join   = 'left join goods_photo as p on p.id=g.photo_id ';
		$queryPhoto->where  = ' g.goods_id='.$goods_id;
		$queryPhoto->fields = 'p.id AS photo_id,p.img ';
		$listPhoto          = $queryPhoto->find();
		foreach($listPhoto as $k => $v){
			$dataGoods['photo'][$k] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/600/h/600");
		}
		
		/* 相关专辑 */
		$queryArt                  = new IQuery('article as m');
		$queryArt->join            = 'left join relation as r on r.article_id = m.id';
		$queryArt->where           = 'm.top=0 and m.visibility=1 and r.goods_id='.$goods_id;
		$queryArt->order           = 'm.sort desc';
		$queryArt->fields          = 'm.id,m.title,m.image';
		$queryArt->limit           = 10;
		$dataGoods['article_list'] = $queryArt->find();
		if(!empty($dataGoods['article_list'])){
			foreach($dataGoods['article_list'] as $k => $v){
				$dataGoods['article_list'][$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/250/h/127");
			}
		}
		
		/* 品牌信息 */
		$modelBrand         = new IModel('brand');
		$dataGoods['brand'] = $modelBrand->getObj('id='.$dataGoods['brand_id'], 'id,name,logo,description');
		if(!empty($dataGoods['brand'])){
			$dataGoods['brand']['logo'] = empty($dataGoods['brand']['logo']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$dataGoods['brand']['logo'].'/w/160/h/102');
			//品牌商品
			$queryGoods                  = new IQuery('goods');
			$queryGoods->where           = 'is_del=0 AND brand_id='.$dataGoods['brand_id'];
			$queryGoods->fields          = 'count(*) AS count';
			$count                       = $queryGoods->find();
			$dataGoods['brand']['count'] = $count[0]['count'];
			//商品列表
			$queryGoods->order          = 'sale DESC,visit DESC';
			$queryGoods->limit          = 10;
			$queryGoods->fields         = 'id,name,sell_price,img';
			$dataGoods['brand']['list'] = $queryGoods->find();
			foreach($dataGoods['brand']['list'] as $k => $v){
				$dataGoods['brand']['list'][$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
			}
		}else{
			$dataGoods['brand'] = (object)array();
		}
		
		/* 是否药品 */
		$cid               = goods_class::catChild(2);
		$queryCat          = new IQuery('category_extend');
		$queryCat->where   = 'goods_id='.$dataGoods['id'].' AND category_id IN ('.$cid.')';
		$queryCat->limit   = 1;
		$infoCat           = $queryCat->find();
		$dataGoods['drug'] = empty($infoCat) ? 0 : 1;
		
		/* 是否已收藏 */
		$queryFor                 = new IQuery('favorite');
		$queryFor->where          = 'user_id='.$user_id.' AND rid='.$dataGoods['id'];
		$infoFav                  = $queryFor->find();
		$dataGoods['is_favorite'] = !empty($infoFav) ? 1 : 0;
		
		/* 增加浏览次数 */
		$visit    = ISafe::get('visit');
		$checkStr = "#".$goods_id."#";
		if($visit && strpos($visit, $checkStr)!==false){
		}else{
			$modelGoods->setData(array('visit' => 'visit + 1'));
			$modelGoods->update('id = '.$goods_id, 'visit');
			$visit = $visit===null ? $checkStr : $visit.$checkStr;
			ISafe::set('visit', $visit);
		}
		
		/* 记录用户操作 */
		
		/* 返回参数 */
		$this->json_echo(apiReturn::go('0', $dataGoods));
	}
	
	/**
	 * 更多商品
	 */
	public function goods_more(){
		/* 获取参数 */
		$tid  = IFilter::act(IReq::get('tid'), 'int'); //商品分类ID
		$mid  = IFilter::act(IReq::get('mid'), 'int'); //推荐分类ID
		$page = IFilter::act(IReq::get('page'), 'int'); //推荐分类ID
		
		/* 商品分类 */
		switch($tid){
			case 1:
				$cid   = goods_class::catChild(126);
				$name1 = '药妆';
				$name2 = '狗子推荐';
				break;
			case 2:
				$cid   = goods_class::catChild(134);
				$name1 = '个护';
				$name2 = '奶瓶推荐';
				break;
			case 3:
				$cid   = goods_class::catChild(6);
				$name1 = '宠物';
				$name2 = '腿毛推荐';
				break;
			case 4:
				$cid   = goods_class::catChild(2);
				$name1 = '健康';
				$name2 = '昔君推荐';
				break;
			case 5:
				$cid   = goods_class::catChild(7);
				$name1 = '零食';
				$name2 = '一哥推荐';
				break;
			default:
				$this->json_echo(apiReturn::go('007001'));
		}
		/* 推荐分类 */
		switch($mid){
			case 1:
				$title = $name1.'-最新品';
				$where = 'm.is_del=0 AND c.category_id IN ('.$cid.')';
				$order = 'm.id desc';
				break;
			case 2:
				$title = $name1.'-最热卖';
				$where = 'm.is_del=0 AND c.category_id IN ('.$cid.')';
				$order = 'm.sale desc,m.visit desc';
				break;
			case 3:
				$title = $name2;
				$where = 'm.is_del=0 AND c.category_id IN ('.$cid.') AND commend_id=4';
				$order = 'm.sale desc,m.visit desc';
				break;
			default:
				$this->json_echo(apiReturn::go('007001'));
		}
		/* 商品列表 */
		$queryGoods           = new IQuery('goods AS m');
		$queryGoods->join     = 'LEFT JOIN commend_goods AS d ON d.goods_id=m.id LEFT JOIN category_extend AS c ON c.goods_id=m.id';
		$queryGoods->fields   = 'm.id,m.name,m.sell_price,m.img,m.market_price,m.jp_price';
		$queryGoods->where    = $where;
		$queryGoods->order    = $order;
		$queryGoods->page     = $page<1 ? 1 : $page;
		$queryGoods->pagesize = 20;
		$queryGoods->group    = 'm.id';
		$listGoods            = $queryGoods->find();
		$totalPage            = $queryGoods->getTotalPage();
		if($page>$totalPage) $listGoods = array();
		if(!empty($listGoods)){
			/* 计算活动商品价格 */
			$listGoods = api::run('goodsActivity', $listGoods);
			foreach($listGoods as $k => $v){
				$listGoods[$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$v['img'].'/w/220/h/220');
			}
		}
		
		/* 返回参数 */
		$this->json_echo(array('title' => $title, 'goods_list' => $listGoods));
	}
	
	/**
	 * 商品详情 TODO 待删除
	 */
	public function products_details(){
		/* 获取参数 */
		$goods_id = IFilter::act(IReq::get('id'), 'int'); //商品ID
		if(!$goods_id){
			IError::show(403, "传递的参数不正确");
			exit();
		}
		
		//使用商品id获得商品信息
		$tb_goods   = new IModel('goods');
		$goods_info = $tb_goods->getObj('id='.$goods_id." AND is_del=0");
		/* 计算活动商品价格 */
		$goods_info = api::run('goodsActivity', $goods_info);
		if(!$goods_info){
			IError::show(403, "这件商品不存在");
			exit();
		}
		
		//品牌名称
		if($goods_info['brand_id']){
			$tb_brand   = new IModel('brand');
			$brand_info = $tb_brand->getObj('id='.$goods_info['brand_id']);
			if($brand_info){
				$goods_info['brand'] = $brand_info['name'];
			}
		}
		
		//获取商品分类
		$categoryObj  = new IModel('category_extend as ca,category as c');
		$categoryList = $categoryObj->query('ca.goods_id = '.$goods_id.' and ca.category_id = c.id', 'c.id,c.name', 'ca.id desc', 1);
		$categoryRow  = null;
		if($categoryList){
			$categoryRow = current($categoryList);
		}
		$goods_info['category'] = $categoryRow ? $categoryRow['id'] : 0;
		
		/* 商品图 */
		$tb_goods_photo         = new IQuery('goods_photo_relation as g');
		$tb_goods_photo->fields = 'p.id AS photo_id,p.img ';
		$tb_goods_photo->join   = 'left join goods_photo as p on p.id=g.photo_id ';
		$tb_goods_photo->where  = ' g.goods_id='.$goods_id;
		$goods_info['photo']    = $tb_goods_photo->find();
		foreach($goods_info['photo'] as $key => $value){
			$goods_info['photo'][$key]['img'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/600/h/600");
		}
		$goods_info['img'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$goods_info['img']."/w/500/h/500");
		
		//商品是否参加促销活动(团购，抢购)
		$goods_info['promo']     = IReq::get('promo') ? IReq::get('promo') : '';
		$goods_info['active_id'] = IReq::get('active_id') ? IFilter::act(IReq::get('active_id'), 'int') : 0;
		if($goods_info['promo']){
			$activeObj    = new Active($goods_info['promo'], $goods_info['active_id'], $this->user['user_id'], $goods_id);
			$activeResult = $activeObj->data();
			if(is_string($activeResult)){
				IError::show(403, $activeResult);
			}else{
				$goods_info[$goods_info['promo']] = $activeResult;
			}
		}
		
		//获得扩展属性
		$tb_attribute_goods         = new IQuery('goods_attribute as g');
		$tb_attribute_goods->join   = 'left join attribute as a on a.id=g.attribute_id ';
		$tb_attribute_goods->fields = ' a.name,g.attribute_value ';
		$tb_attribute_goods->where  = "goods_id='".$goods_id."' and attribute_id!=''";
		$goods_info['attribute']    = $tb_attribute_goods->find();
		
		//购买记录
		$tb_shop               = new IQuery('order_goods as og');
		$tb_shop->join         = 'left join order as o on o.id=og.order_id';
		$tb_shop->fields       = 'count(*) as totalNum';
		$tb_shop->where        = 'og.goods_id='.$goods_id.' and o.status = 5';
		$shop_info             = $tb_shop->find();
		$goods_info['buy_num'] = 0;
		if($shop_info){
			$goods_info['buy_num'] = $shop_info[0]['totalNum'];
		}
		
		//购买前咨询
		$tb_refer            = new IModel('refer');
		$refeer_info         = $tb_refer->getObj('goods_id='.$goods_id, 'count(*) as totalNum');
		$goods_info['refer'] = 0;
		if($refeer_info){
			$goods_info['refer'] = $refeer_info['totalNum'];
		}
		
		//网友讨论
		$tb_discussion            = new IModel('discussion');
		$discussion_info          = $tb_discussion->getObj('goods_id='.$goods_id, 'count(*) as totalNum');
		$goods_info['discussion'] = 0;
		if($discussion_info){
			$goods_info['discussion'] = $discussion_info['totalNum'];
		}
		
		//获得商品的价格区间
		$tb_product   = new IModel('products');
		$product_info = $tb_product->getObj('goods_id='.$goods_id, 'max(sell_price) as maxSellPrice ,max(market_price) as maxMarketPrice');
		if(isset($product_info['maxSellPrice']) && $product_info['maxSellPrice']){
			$goods_info['sell_price'] .= "-".$product_info['maxSellPrice'];
			$goods_info['market_price'] .= "-".$product_info['maxMarketPrice'];
		}
		
		//获得会员价
		$countsumInstance          = new countsum();
		$goods_info['group_price'] = $countsumInstance->getGroupPrice($goods_id, 'goods');
		
		//获取商家信息
		if($goods_info['seller_id']){
			$sellerDB             = new IModel('seller');
			$goods_info['seller'] = $sellerDB->getObj('id = '.$goods_info['seller_id']);
		}
		
		//增加浏览次数
		$visit    = ISafe::get('visit');
		$checkStr = "#".$goods_id."#";
		if($visit && strpos($visit, $checkStr)!==false){
		}else{
			$tb_goods->setData(array('visit' => 'visit + 1'));
			$tb_goods->update('id = '.$goods_id, 'visit');
			$visit = $visit===null ? $checkStr : $visit.$checkStr;
			ISafe::set('visit', $visit);
		}
		
		//评论
		$commentDB         = new IQuery('comment as c');
		$commentDB->join   = 'left join goods as go on c.goods_id = go.id AND go.is_del = 0 left join user as u on u.id = c.user_id';
		$commentDB->fields = 'u.head_ico,u.username,c.*';
		$commentDB->where  = 'c.goods_id = '.$goods_id.' and c.status = 1';
		$commentDB->order  = 'c.id desc';
		//        $commentDB->page   = $page;
		$goods_info['comments_data'] = $commentDB->find();
		
		$goods_info['spec_array'] = json_decode($goods_info['spec_array']);
		//        $this->setRenderData($goods_info);
		
		/* 是否已收藏 */
		$favorite                  = new IQuery('favorite');
		$favorite->where           = 'user_id='.$this->user['user_id'].' and rid='.$goods_info['id'];
		$fdata                     = $favorite->find();
		$goods_info['is_favorite'] = !empty($fdata) ? 1 : 0;
		
		$this->json_echo($goods_info);
	}
	
	/**
	 * 商品的相关专辑 TODO 待删除
	 */
	public function products_details_article(){
		/* 获取参数 */
		$goods_id = IFilter::act(IReq::get('id'), 'int');
		if(empty($goods_id)) IError::show(403, "传递的参数不正确");
		
		/* 相关专辑 */
		$query         = new IQuery('article as m');
		$query->join   = 'left join relation as r on r.article_id = m.id';
		$query->where  = 'm.top=0 and m.visibility=1 and r.goods_id='.$goods_id;
		$query->order  = 'm.sort desc';
		$query->fields = 'm.id,m.title,m.image';
		$query->limit  = 10;
		$list          = $query->find();
		if(!empty($list)){
			foreach($list as $k => $v){
				$list[$k]['image'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/250/h/127");
			}
		}
		$this->json_echo($list);
	}
	
	//商品详情的补充信息内容
	public function products_details_other(){
		$goods_id = IFilter::act(IReq::get('id'), 'int');
		//商品关联到的专辑
		$goods_query            = new IModel('goods');
		$goods_data             = $goods_query->getObj('id = '.$goods_id);
		$relation_query         = new IQuery('relation as a');
		$relation_query->join   = "right join article as b on a.article_id = b.id and b.category_id = 3";
		$relation_query->fields = "a.goods_id,b.id,b.title,b.image";
		$relation_query->where  = "a.goods_id = ".$goods_id;
		$article_data           = $relation_query->find();
		//品牌下的商品
		$brands_query         = new IQuery('brand as a');
		$brands_query->join   = "right join goods as b on a.id = b.brand_id";
		$brands_query->fields = "b.id,b.name,b.img,b.sell_price,b.brand_id";
		$brands_query->where  = "b.brand_id = ".$goods_data['brand_id'];
		$brands_query->limit  = 6;
		$brand_good_data      = $brands_query->find();
		foreach($brand_good_data as $key => $value){
			$brand_good_data[$key]['img_thumb'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/180/h/180");
		}
		//某品牌下商品数量
		$brands_query->join   = "right join goods as b on a.id = b.brand_id";
		$brands_query->fields = "count(*) as nums";
		$brands_query->where  = "b.brand_id = ".$goods_data['brand_id'];
		$nums                 = $brands_query->find()[0]['nums'];
		
		$brand_model        = new IModel('brand');
		$brand_data         = $brand_model->getObj('id = '.$goods_data['brand_id']);
		$brand_data['nums'] = $nums;
		$data               = array('article_data' => $article_data, 'brand_good_data' => $brand_good_data, "brand_data" => $brand_data);
		$this->json_echo($data);
	}
	
	function tag_goods(){
		$keyword_id = IFilter::act(IReq::get('id'), 'int');
		if(empty($keyword_id)){
			$this->json_echo([]);
		}
		$keyword            = new IQuery('keyword');
		$keyword->where     = 'id = '.$keyword_id;
		$word               = $keyword->find()[0]['word'];
		$goods_query        = new IQuery('goods');
		$goods_query->where = 'search_words like '.'"%,'.$word.',%"';
		$goods_query->order = 'create_time desc';
		$data['new']        = $goods_query->find();
		
		$commend_goods         = new IQuery('commend_goods as co');
		$commend_goods->join   = 'left join goods as go on co.goods_id = go.id';
		$commend_goods->where  = 'co.commend_id = 3 and go.is_del = 0 AND go.id is not null'.' and go.search_words like '.'"%,'.$word.',%"';
		$commend_goods->fields = 'go.img,go.sell_price,go.name,go.id,go.market_price';
		$commend_goods->limit  = 3;
		$commend_goods->order  = 'sort asc';
		$data['hot']           = $commend_goods->find();
		
		$this->json_echo($data);
	}
	
	
	/**
	 * ---------------------------------------------------专辑---------------------------------------------------*
	 */
	//显示专辑列表（首页）
	public function article_list(){
		/* 获取参数 */
		$cid  = IFilter::act(IReq::get('cid'), 'int');    //专辑分类ID，选填
		$page = IFilter::act(IReq::get('page'), 'int');    //当前页码，选填
		/* 获取数据 */
		$query           = new IQuery('article as m');
		$query->join     = 'left join article_category as c on c.id=m.category_id';
		$query->where    = 'm.top=0 and m.visibility=1 '.(empty($cid) ? '' : ' and m.category_id='.$cid);
		$query->fields   = 'm.id,m.title,m.image,m.visit_num,m.category_id,c.icon,c.name as category_name';
		$query->order    = 'm.sort desc,m.id desc';
		$query->page     = $page>1 ? $page : 1;
		$query->pagesize = 5;
		$list            = $query->find();
		$total_page      = $query->getTotalPage();
		if($page>$total_page) $list = array();
		if(!empty($list)){
			//商品列表模型
			$query_goods         = new IQuery('goods as m');
			$query_goods->join   = 'left join relation as r on r.goods_id=m.id';
			$query_goods->fields = 'm.id,m.name,m.sell_price,m.img';
			$query_goods->order  = 'm.sort asc';
			$query_goods->limit  = 5;
			//商品统计模型
			$query_goods_count         = new IQuery('goods as m');
			$query_goods_count->join   = 'left join relation as r on r.goods_id=m.id';
			$query_goods_count->fields = 'count(m.id) as num';
			//专辑收藏模型
			$query_favorite         = new IQuery('favorite_article');
			$query_favorite->fields = 'count(id) as num';
			//收藏人数
			foreach($list as $k => $v){
				$list[$k]['icon']  = empty($v['icon']) ? '' : IWeb::$app->config['image_host'].'/'.$v['icon'];
				$list[$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/750/h/380");
				//收藏人数
				$query_favorite->where    = 'aid='.$v['id'];
				$count                    = $query_favorite->find();
				$list[$k]['favorite_num'] = $count[0]['num'];
				//当前用户是否已收藏
				if(!empty($this->user['user_id'])){
					$query_favorite->where = 'aid='.$v['id'].' and user_id='.$this->user['user_id'];
					$count                 = $query_favorite->find();
					$count                 = $count[0]['num'];
				}else{
					$count = 0;
				}
				$list[$k]['is_favorite'] = $count;
				//相关商品数量
				$query_goods_count->where = 'm.is_del=0 and r.article_id='.$v['id'];
				$count                    = $query_goods_count->find();
				$list[$k]['goods_num']    = $count[0]['num'];
				//相关商品列表
				$query_goods->where = 'm.is_del=0 and r.article_id='.$v['id'];
				$list[$k]['list']   = $query_goods->find();
				if(!empty($list[$k]['list'])){
					/* 计算活动商品价格 */
					$list[$k]['list'] = api::run('goodsActivity', $list[$k]['list']);
					foreach($list[$k]['list'] as $k1 => $v1){
						$list[$k]['list'][$k1]['img'] = empty($v1['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/180/h/180");
					}
				}
			}
		}
		$this->json_echo($list);
	}
	
	/**
	 * 专辑分类列表
	 */
	public function article_category_list(){
		/* 首页展示的专辑分类 */
		$query_ac         = new IQuery('article_category');
		$query_ac->where  = 'id IN (11,12,15,16,18,19)'; //11喵酱推荐/12杂志揭载/15药妆特供/16健康推荐/18居家个护/19吃喝宅乐/
		$query_ac->fields = 'id,name';
		$query_ac->limit  = 6;
		$list_ac          = $query_ac->find();
		if(!empty($list_ac)){
			foreach($list_ac as $k => $v){
				$list_ac[$k]['image'] = IWeb::$app->config['image_host'].'/upload/category/article_img/'.$v['id'].'.png';
			}
		}
		/* 特别推荐专辑 */
//		$query_ar         = new IQuery('article');
//		$query_ar->where  = 'top=1 and visibility=1';
//		$query_ar->order  = 'sort desc';
//		$query_ar->limit  = 3;
//		$query_ar->fields = 'id,title,image';
//		$list_ar          = $query_ar->find();
//		if(!empty($list_ar)){
//			foreach($list_ar as $k => $v){
//				$list_ar[$k]['image'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/738/h/353");
//			}
//		}
		/* 返回数据 */
		$data = array('ac' => $list_ac, 'ar' => array());
		$this->json_echo($data);
	}
	
	/**
	 * 好货推荐
	 */
	public function article_good(){
		$tid = IFilter::act(IReq::get('tid'), 'int'); //分类ID
		switch($tid){
			case 1: //狗子推荐
				$aid = 15; //专辑分类
				break;
			case 2: //奶糖推荐
				$aid = 18; //专辑分类
				break;
			case 3: //腿毛推荐
				$aid = 17; //专辑分类
				break;
			case 4: //昔君推荐
				$aid = 16; //专辑分类
				break;
			case 5: //一哥推荐
				$aid = 19; //专辑分类
				break;
			default:
				$this->json_echo(apiReturn::go('007001'));
		}
		$_GET['cid'] = $aid;
		$this->article_list();
	}
	
	//通过专辑获取相关商品
	public function article_rel_goods(){
		$article_id        = IFilter::act(IReq::get('id'), 'int');
		$article           = new IQuery('relation as r');
		$article->join     = 'left join goods as go on r.goods_id = go.id';
		$article->where    = sprintf('go.is_del = 0 and r.article_id = %s and go.id is not null', $article_id);
		$article->filds    = 'go.goods_no as goods_no,go.id as goods_id,go.img,go.name,go.sell_price';
		$article->page     = IReq::get('page') ? IFilter::act(IReq::get('page'), 'int') : 1;
		$article->pagesize = 1000;
		$relationList      = $article->find();
		$total_page        = $article->getTotalPage();
		if($article->page>$total_page){
			$relationList = [];
		}
		/* 计算活动商品价格 */
		$relationList = api::run('goodsActivity', $relationList);
		foreach($relationList as $key => $value){
			$relationList[$key]['img'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/350/h/350");
		}
		$this->json_echo($relationList);
	}
	
	//专辑添加收藏夹
	function favorite_article_add(){
		$article_id = IFilter::act(IReq::get('id'), 'int');
		$message    = '';
		
		if($article_id==0){
			$message = '文章id值不能为空';
		}else if(!isset($this->user['user_id']) || !$this->user['user_id']){
			$message = '请先登录';
		}else{
			$favoriteObj = new IModel('favorite_article');
			$articleRow  = $favoriteObj->getObj('user_id = '.$this->user['user_id'].' and aid = '.$article_id);
			if($articleRow){
				//                $message = '您已经收藏过此件专辑';
				$favoriteObj->del('user_id = '.$this->user['user_id'].' and aid = '.$article_id);
			}else{
				$catObj    = new IModel('article');
				$catRow    = $catObj->getObj('id = '.$article_id);
				$cat_id    = $catRow ? $catRow['category_id'] : 0;
				$dataArray = array('user_id' => $this->user['user_id'], 'aid' => $article_id, 'time' => ITime::getDateTime(), 'cat_id' => $cat_id,);
				$favoriteObj->setData($dataArray);
				$favoriteObj->add();
				$message = '收藏成功';
				
				//商品收藏信息更新
				$articleDB = new IModel('article');
				$articleDB->setData(array("favorite" => "favorite + 1"));
				$articleDB->update("id = ".$article_id, 'favorite');
			}
		}
		$result = array('isError' => true, 'message' => $message,);
		
		$this->json_echo($result);
	}
	
	/**
	 * ---------------------------------------------------分类---------------------------------------------------*
	 */
	
	/**
	 *一级分类的数据信息
	 */
	public function category_top(){
		//一级分类
		$data = Api::run('getCategoryListTop');
		
		foreach($data as $key => $value){
			//banner图
			if(!empty($value['banner_image'])){
				$data[$key]['banner_image'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$value['banner_image']."/w/520/h/154");
			}
			//icon
			if(!empty($value['image'])){
				$temp                = explode(',', $value['image']);
				$data[$key]['image'] = empty($temp[0]) ? '' : IWeb::$app->config['image_host'].'/'.$temp[0];
				$data[$key]['image'] .= empty($temp[1]) ? '' : ','.IWeb::$app->config['image_host'].'/'.$temp[1];
			}
			//二级子分类
			$data[$key]['child'] = [];
			$second              = Api::run('getCategoryByParentid', array('#parent_id#', $value['id']));
			if(!empty($second)) foreach($second as $k => $v){
				if(!empty($v['banner_image'])){
					$second[$k]['banner_image'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['banner_image']."/w/154/h/154");
				}
				if(!empty($v['image'])){
					//                    $temp = explode(',',$v['image']);
					$second[$k]['image'] = IWeb::$app->config['image_host'].'/'.$v['image'];
				}
			}
			$data[$key]['child'] = $second;
		}
		$this->json_echo($data);
	}
	
	/**
	 * 获取其子类数据信息
	 */
	public function category_child(){
		/* 获取参数 */
		$catId = IFilter::act(IReq::get('id'), 'int'); //分类id
		$cosme = IFilter::act(IReq::get('cosme'), 'int'); //是否排行榜页面
		$page  = IFilter::act(IReq::get('page'), 'int'); //分页编号
		$bid   = IFilter::act(IReq::get('bid'), 'int'); //品牌ID
		if($catId==0) $this->json_echo(array());
		
		/* 获取下级分类 */
		$queryCat         = new IQuery('category');
		$queryCat->where  = 'visibility=1 AND parent_id='.$catId;
		$queryCat->fields = 'id';
		$dataCat          = $queryCat->find();
		if(!empty($dataCat)){
			foreach($dataCat as $k => $v){
				$catId .= ','.$v['id'];
			}
		}
		
		/* 获取数据 */
		$query = new IQuery('goods as m');
		if($cosme==1){
			//cosme排行榜进入
			$join   = 'LEFT JOIN cosme AS c ON c.goods_id=m.id';
			$where  = 'm.is_del=0 AND c.type in ('.$catId.')'.(empty($bid) ? '' : ' AND m.brand_id='.$bid);
			$fields = 'm.id,m.name,m.sell_price,m.jp_price,m.market_price,m.img';
			$order  = 'c.rank ASC';
		}else{
			//通常进入
			$join   = 'LEFT JOIN category_extend AS c ON c.goods_id=m.id';
			$where  = 'm.is_del=0 AND c.category_id in ('.$catId.')'.(empty($bid) ? '' : ' AND m.brand_id='.$bid);
			$fields = 'm.id,m.name,m.sell_price,m.jp_price,m.market_price,m.img';
			$order  = 'm.sale DESC,m.visit DESC';
		}
		$query->join     = $join;
		$query->where    = $where;
		$query->fields   = $fields;
		$query->order    = $order;
		$query->page     = $page<1 ? 1 : $page;
		$query->pagesize = 1000;
		$resultData      = $query->find();
		$totalPage       = $query->getTotalPage();
		if($page>$totalPage) $resultData = array();
		if(!empty($resultData)){
			/* 计算活动商品价格 */
			$resultData = api::run('goodsActivity', $resultData);
			foreach($resultData as $k => $v){
				$resultData[$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/350/h/350");
			}
		}
		$this->json_echo($resultData);
	}
	
	/**
	 * 分类列表
	 */
	public function category_list(){
		/* 获取参数 */
		$param = array(
			'cat_id' => IFilter::act(IReq::get('cat_id'), 'int'), //分类id
		);
		/* 获取数据 */
		$data = $this->get_cat($param['cat_id']);
		/* 数据返回 */
		$this->json_echo(apiReturn::go('0', $data));
	}
	function get_cat($pid){
		$queryCat         = new IQuery('category');
		$queryCat->where  = 'parent_id='.$pid;
		$queryCat->fields = 'id,parent_id,name,image';
		$queryCat->order  = 'sort DESC';
		$listCat          = $queryCat->find();
		if(!empty($listCat)){
			foreach($listCat as $k => $v){
				$listCat[$k]['list'] = $this->get_cat($v['id']);
			}
		}
		return $listCat;
	}
	
	
	/**
	 * ---------------------------------------------------品牌---------------------------------------------------*
	 */
	/**
	 * 返回品牌信息
	 */
	public function brand_list(){
		$data = Api::run('getBrandList');
		foreach($data as $key => $value){
			if(!empty($value['logo'])){
				$data[$key]['logo'] = IWeb::$app->config['image_host'].'/'.$value['logo'];
			}
		}
		$this->json_echo($data);
	}
	
	/**
	 * 品牌详情
	 */
	public function brand(){
		/* 接收参数 */
		$brand_id = IFilter::act(IReq::get('id'), 'int');
		$page     = IFilter::act(IReq::get('page'), 'int');
		
		/* 品牌详情 */
		$queryBrand         = new IQuery('brand');
		$queryBrand->where  = 'id='.$brand_id;
		$queryBrand->fields = 'id,name,logo,description,banner';
		$queryBrand->limit  = 1;
		$data               = $queryBrand->find();
		if(empty($data)) $this->json_echo(array('error' => '品牌不存在'));
		$data           = $data[0];
		$data['logo']   = empty($data['logo']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$data['logo'].'/w/160/h/102');
		$data['banner'] = empty($data['banner']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$data['banner'].'/w/750/h/376');
		
		/* 相关商品 */
		$queryGoods           = new IQuery('goods');
		$queryGoods->where    = 'is_del=0 AND brand_id='.$brand_id;
		$queryGoods->fields   = 'id,name,img,content,sell_price,jp_price';
		$queryGoods->page     = $page<1 ? 1 : $page;
		$queryGoods->pagesize = 10;
		$queryGoods->order    = 'sort asc';
		$dataGoods            = $queryGoods->find();
		$total_page           = $queryGoods->getTotalPage();
		if($page>$total_page) $dataGoods = array();
		if(!empty($dataGoods)){
			/* 计算活动商品价格 */
			$dataGoods = api::run('goodsActivity', $dataGoods);
			foreach($dataGoods as $k => $v){
				$dataGoods[$k]['img']         = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$v['img'].'/w/220/h/220');
				$dataGoods[$k]['description'] = empty($v['content']) ? '' : mb_substr(trim(strip_tags(str_ireplace('&nbsp;', '', htmlspecialchars_decode($v['content'])))), 0, 30, 'utf-8');
				unset($dataGoods[$k]['content']);
			}
		}
		//相关商品数量
		$queryGoodsSum         = new IQuery('goods');
		$queryGoodsSum->fields = 'count(`id`) as sum';
		$queryGoodsSum->where  = 'is_del=0 AND brand_id='.$brand_id;
		$dataGoodsSum          = $queryGoodsSum->find();
		
		/* 相关专辑 */
		$queryArticle         = new IQuery('article AS m');
		$queryArticle->join   = 'LEFT JOIN relation AS r ON r.article_id=m.id LEFT JOIN goods AS g ON g.id=r.goods_id';
		$queryArticle->where  = 'g.is_del=0 AND m.visibility=1 AND g.brand_id='.$brand_id;
		$queryArticle->fields = 'm.id,m.title,m.visit_num,m.image';
		$queryArticle->limit  = 5;
		$queryArticle->order  = 'm.top desc,m.sort desc';
		$queryArticle->group = 'm.id';
		$dataArticle          = $queryArticle->find();
		if(!empty($dataArticle)){
			foreach($dataArticle as $k => $v){
				$dataArticle[$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$v['image'].'/w/750/h/380');;
			}
		}
		//相关专辑数量
		$queryArticleSum         = new IQuery('article AS m');
		$queryArticleSum->join   = 'LEFT JOIN relation AS r ON r.article_id=m.id LEFT JOIN goods AS g ON g.id=r.goods_id';
		$queryArticleSum->where  = 'g.is_del=0 AND m.visibility=1 AND g.brand_id='.$brand_id;
		$queryArticleSum->fields = 'count(*) as sum';
		$dataArticleSum          = $queryArticleSum->find();
		
		/* 返回参数 */
		$data['goods_sum']    = $dataGoodsSum[0]['sum'];
		$data['article_sum']  = $dataArticleSum[0]['sum'];
		$data['goods_list']   = $dataGoods;
		$data['article_list'] = $dataArticle;
		$this->json_echo($data);
	}
	/**
	 * ---------------------------------------------------搜索---------------------------------------------------*
	 */
	/* 热门关键词 */
	public function search_words(){
		/* 热门关键词 */
		$query_keyword         = new IQuery('keyword');
		$query_keyword->order  = 'hot desc,num desc';
		$query_keyword->fields = 'word';
		$query_keyword->limit  = 20;
		$data_keyword          = $query_keyword->find();
		
		$this->json_echo($data_keyword);
	}
	
	/* 开始搜索 */
	public function search(){
		/* 接收参数 */
		$word = IFilter::act(IReq::get('word'), 'string');
		$page = IFilter::act(IReq::get('page'), 'int');
		if(empty($word)) $this->json_echo(array());
		//关键字处理
		$word_str = str_replace(' ', ',', $word);
		$word_arr = explode(' ', $word);
		
		/* 商品 */
		$model_keyword = new IModel('keyword');
		$data_keyword  = $model_keyword->get_count('word in ("'.$word_str.'")', 'num');
		if($data_keyword>0){
			//关键字搜索次数+1
			$model_keyword->setData(array('num' => 'num+1'));
			$model_keyword->update('word in ("'.$word_str.'")', array('num'));
		}
		//搜索商品
		$query_goods = new IQuery('goods');
		$field       = 'id,name,sell_price,jp_price,market_price,img';
		$where       = 'is_del=0 AND (';
		$order       = '';
		foreach($word_arr as $k => $v){
			$field .= ',(`name` LIKE "%'.$v.'%") as name'.$k.',(`search_words` LIKE "%,'.$v.',%") as search'.$k.',(`goods_no`="'.$v.'") as goods_no'.$k;
			$where .= ' (`name` LIKE "%'.$v.'%") OR (`search_words` LIKE "%,'.$v.',%") OR (`goods_no`="'.$v.'")';
			$order .= 'name'.$k;
			if(count($word_arr)!=$k+1){
				$where .= ' OR';
				$order .= ' AND ';
			}
		}
		$where .= ')';
		$query_goods->where    = $where;
		$query_goods->order    = '(CASE WHEN ('.$order.') THEN 0 ELSE 1 END) asc';
		$query_goods->fields   = $field;
		$query_goods->page     = empty($page) ? 1 : $page;
		$query_goods->pagesize = 20;
		$data_goods            = $query_goods->find();
		$total_page            = $query_goods->getTotalPage();
		if($page>$total_page) $data_goods = array();
		if(!empty($data_goods)){
			/* 计算活动商品价格 */
			$data_goods = api::run('goodsActivity', $data_goods);
			foreach($data_goods as $k => $v){
				$data_goods[$k]['img'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/290/h/290");
			}
		}
		
		/* 专辑 */
		$query_article = new IQuery('article');
		$where         = 'visibility=1 AND (';
		$field         = 'id,title,image';
		foreach($word_arr as $k => $v){
			$field .= ',(`title` LIKE "%'.$v.'%") as name'.$k.',(`keywords` LIKE "%,'.$v.',%") as search'.$k;
			$where .= ' (`title` LIKE "%'.$v.'%") OR (`keywords` LIKE "%'.$v.'%")';
			if(count($word_arr)!=$k+1) $where .= ' OR';
		}
		$where .= ')';
		$query_article->where    = $where;
		$query_article->order    = 'top desc,sort desc';
		$query_article->fields   = $field;
		$query_article->page     = empty($page) ? 1 : $page;
		$query_article->pagesize = 20;
		$data_article            = $query_article->find();
		$total_page              = $query_article->getTotalPage();
		if($page>$total_page) $data_article = array();
		if(!empty($data_article)){
			foreach($data_article as $k => $v){
				$data_article[$k]['image'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/513/h/260");
			}
		}
		$this->json_echo(array('goods' => $data_goods, 'article' => $data_article));
	}
	
	/**
	 * ---------------------------------------------------幻灯片---------------------------------------------------*
	 */
	public function banner_list(){
		$banner = Api::run('getBannerList');
		foreach($banner as $key => $value){
			$banner[$key]['img'] = IWeb::$app->config['image_host'].'/'.$value['img'];
		}
		$goods         = new IQuery('goods');
		$goods->fields = 'count(*) as nums';
		$nums          = $goods->find()[0]['nums'];
		
		$this->json_echo(['banner' => $banner, 'goods_nums' => $nums]);
	}
	/**
	 * ---------------------------------------------------排行榜---------------------------------------------------*
	 */
	/**
	 * 排行榜
	 */
	public function cosme(){
		/* 排行榜模型 */
		$query         = new IQuery('cosme as m');
		$query->join   = 'LEFT JOIN goods AS g ON g.id=m.goods_id';
		$query->fields = 'g.id,g.name,g.sell_price,g.img';
		$query->order  = 'm.rank asc';
		$query->limit  = 3;
		$name          = array(1 => '上周热销榜', 2 => '美容热销榜', 3 => '美容护理榜');
		$data          = array();
		for($i = 1; $i<=3; $i++){
			$query->where = 'g.is_del=0 AND m.type='.$i;
			$list         = $query->find();
			if(!empty($list)){
				/* 计算活动商品价格 */
				$list = api::run('goodsActivity', $list);
				foreach($list as $k => $v){
					$list[$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/220/h/220");
				}
			}
			//返回数据
			$data['cosme'.$i] = array('name' => $name[$i], //榜单名称
									  'type' => $i, //榜单类型
									  'list' => $list, //商品列表
			);
		}
		$this->json_echo($data);
	}
	
	/**
	 * 个人中心
	 * @return string
	 */
	public function info(){
		$user_id = $this->user['user_id'];
		
		$userObj = new IModel('user');
		$where   = 'id = '.$user_id;
		$userRow = $userObj->getObj($where, array('head_ico', 'username'));
		
		$memberObj = new IModel('member');
		$where     = 'user_id = '.$user_id;
		$memberRow = $memberObj->getObj($where);
		
		$data = array_merge($userRow, $memberRow);
		$this->json_echo($data);
	}
	
	/**
	 * 收藏列表
	 */
	function favorite_list(){
		/* 商品收藏 */
		$favorite_query         = new IQuery('favorite as a');
		$favorite_query->join   = 'left join goods as go on go.id = a.rid';
		$favorite_query->fields = 'a.*,go.id,go.name,go.sell_price,go.market_price,go.img,go.jp_price';
		
		$favorite_query->where = 'user_id = '.$this->user['user_id'];
		$data1                 = $favorite_query->find();
		if(!empty($data1)){
			/* 计算活动商品价格 */
			$data1 = api::run('goodsActivity', $data1);
			foreach($data1 as $key => $value){
				$data1[$key]['img'] = empty($value['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/200/h/200");
			}
		}
		
		/* 专辑收藏 */
		$favorite_a_query         = new IQuery('favorite_article as a');
		$favorite_a_query->join   = 'left join article as aa on aa.id = a.aid';
		$favorite_a_query->fields = 'a.*,aa.id,aa.title,aa.title,aa.image,aa.content';
		$favorite_a_query->where  = 'user_id = '.$this->user['user_id'];
		$data2                    = $favorite_a_query->find();
		if($data2) foreach($data2 as $key => $value){
			$data2[$key]['description'] = empty($value['content']) ? '' : mb_substr(trim(strip_tags(str_ireplace('&nbsp;', '', htmlspecialchars_decode($value['content'])))), 0, 30, 'utf-8');
			unset($data2[$key]['content']);
			$data2[$key]['image'] = empty($value['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$value['image']."/w/210/h/107");
		}
		$this->json_echo(['goods_data' => $data1, 'article_data' => $data2]);
	}
	
	function user_credit_info(){
		$user_id             = $this->user['user_id'];
		$user_query          = new IQuery('user');
		$user_query->where   = 'id = '.$user_id;
		$data                = $user_query->find()[0];
		$image1              = $data['sfz_image1'];
		$image2              = $data['sfz_image2'];
		$data['sfz_image1x'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$image1."/w/110/h/110");
		$data['sfz_image2x'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$image2."/w/110/h/110");
		$data['sfz_image1y'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$image1."/w/281/h/207");
		$data['sfz_image2y'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$image2."/w/281/h/207");
		$this->json_echo($data);
	}
	
	function qrcode(){
		$id = IFilter::act(IReq::get('id'), 'int');
		if(IClient::isWechat()==true){
			require_once __DIR__.'/../plugins/wechat/wechat.php';
			require_once __DIR__.'/../plugins/curl/Curl.php';
			$this->wechat = new wechat();
			$curl         = new \Wenpeng\Curl\Curl();
			$access_token = $this->wechat->getAccessToken();
			$url          = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
			$curl->post(json_encode(['action_name' => 'QR_LIMIT_SCENE', 'action_info' => ['scene' => ['scene_id' => $id]]]))->url($url);
			$ret = json_decode($curl->data());
			echo '<img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ret->ticket).'">';
			echo '<br>';
			echo $ret->url;
			//            var_dump($curl->data());
		}
	}
	
	/**
	 * 推荐人旗下店铺的订单信息
	 */
	function recommender_shops_tobe_booked(){
		$shop_query                      = new IQuery('shop');
		$shop_query->where               = 'recommender = "'.$this->user['user_id'].'"';
		$ret                             = $shop_query->find();
		$temp_goods_total_price          = 0;
		$temp_goods_tobe_booked          = 0;
		$partner_goods_total_price       = 0;
		$partner_goods_total_tobe_booked = 0;
		if(!empty($ret)){
			foreach($ret as $key => $value){
				$data[$key]['identify_id'] = $value['identify_id'];
				$data[$key]['name']        = $value['name'];
				$data[$key]['id']          = $value['id'];
				$data[$key]['address']     = $value['address'];
				$data[$key]['head_ico']    = $this->get_user_head_ico($value['own_id']);
				$data[$key]['orders']      = $this->get_shop_orders($value['id'], true);
				foreach($data[$key]['orders'] as $k => $v){
					$temp_goods_total_price += $v['goods_total_price'];
					$temp_goods_tobe_booked += $v['goods_total_tobe_booked'];
				}
				$data[$key]['goods_total_price']  = $temp_goods_total_price;
				$data[$key]['amount_tobe_booked'] = $temp_goods_tobe_booked;
				$partner_goods_total_tobe_booked += $temp_goods_tobe_booked;
				$partner_goods_total_price += $temp_goods_total_price;
				$temp_goods_total_price = 0;
				$temp_goods_tobe_booked = 0;
			}
			$data[0]['partner_goods_total_tobe_booked'] = $partner_goods_total_tobe_booked;
			$data[0]['partner_goods_total_price']       = $partner_goods_total_price;
		}
		$this->json_echo($data);
	}
	
	/**
	 * 店铺待入账信息
	 */
	function shop_tobe_booked(){
		$shop_query        = new IQuery('shop');
		$shop_query->where = 'own_id = "'.$this->user['user_id'].'"';
		$ret               = $shop_query->find();
		$data              = [];
		if(!empty($ret)){
			$data['identify_id'] = $ret[0]['identify_id'];
			$data['name']        = $ret[0]['name'];
			$data['id']          = $ret[0]['id'];
			$data['address']     = $ret[0]['address'];
			$data['orders']      = $this->get_shop_orders($ret[0]['id'], false);
			$temp                = 0;
			foreach($data['orders'] as $k => $v){
				$temp += $v['goods_total_tobe_booked'];
			}
			$data['shop_tobe_booked_price'] = $temp;
		}
		$this->json_echo($data);
	}
	
	/**
	 * 获取某个店铺下的订单信息
	 * @param $shop_id
	 * @param $if_partner
	 * @return array
	 */
	function get_shop_orders($shop_id, $if_partner){
		$shop_query = new IQuery('shop');
		if($if_partner){
			$shop_query->where = 'id = '.$shop_id.' and recommender = '.$this->user['user_id'];
		}else{
			$shop_query->where = 'id = '.$shop_id;
		}
		$shop_data = $shop_query->find();
		$temp      = '';
		if($shop_data){
			$user_query        = new IQuery('user');
			$user_query->where = 'shop_identify_id = '.$shop_data[0]['identify_id'];
			$user_data         = $user_query->find();
			foreach($user_data as $key => $value){
				$temp .= ' or user_id = '.$value['id'];
			}
			$temp = explode('or', $temp, 2)[1];
		}
		if(!empty($temp)){
			$temp = '('.$temp.')';
		}else{
			$temp = '( user_id = '.$this->user['user_id'].')';
		}
		if($if_partner){
			$temp .= ' and is_recommender_checkout = 0 and seller_id = '.$shop_data[0]['identify_id'];
		}else{
			$temp .= ' and is_shop_checkout = 0 and seller_id = '.$shop_data[0]['identify_id'];
		}
		$date_interval                   = ' and PERIOD_DIFF( date_format( now( ) , \'%Y%m\' ) , date_format( create_time, \'%Y%m\' ) ) =1'; //上个月
		$last_month_distribute_order_ret = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 2 and (distribution_status = 0 or distribution_status = 1)'.$date_interval)->find(); // 待发货 待收货
		$date_interval                   = ' and DATE_FORMAT( completion_time, \'%Y%m\' ) = DATE_FORMAT( CURDATE( ) , \'%Y%m\' )'; //本月
		$complete_order_ret              = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 5 '.$date_interval)->find(); // 已完成
		$date_interval                   = ' and DATE_FORMAT( create_time, \'%Y%m\' ) = DATE_FORMAT( CURDATE( ) , \'%Y%m\' )'; //本月
		$distribute_order_ret            = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 2 and (distribution_status = 0 or distribution_status = 1)'.$date_interval)->find(); // 待发货 待收货
		$merge_data                      = array_merge($last_month_distribute_order_ret, $complete_order_ret, $distribute_order_ret);
		foreach($merge_data as $k => $value){
			$temp              = Api::run('getOrderGoodsListByGoodsid', array('#order_id#', $value['id']));
			$goods_total_price = 0;
			foreach($temp as $key => $good){
				$goods_total_price += $good['real_price']*$good['goods_nums'];
				$good_info               = JSON::decode($good['goods_array']);
				$temp[$key]['good_info'] = $good_info;
				$temp[$key]['img']       = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$temp[$key]['img']."/w/160/h/160");
			}
			$shop_category                       = new IQuery('shop_category');
			$shop_category->where                = 'id = '.$shop_data[0]['category_id'];
			$shop_category_data                  = $shop_category->find();
			$merge_data[$k]['goods_total_price'] = $goods_total_price;
			$merge_data[$k]['goods_list']        = $temp;
			$merge_data[$k]['orderStatusText']   = Order_Class::orderStatusText(Order_Class::getOrderStatus($value));
			if($merge_data[$k]['orderStatusText']=='已完成'){
				$merge_data[$k]['goods_total_tobe_booked'] = $goods_total_price*$shop_category_data[0]['rebate'];
			}else{
				$merge_data[$k]['goods_total_tobe_booked'] = '0.00';
			}
		}
		return $merge_data;
	}
	
	/**
	 * 当前登陆用户的店铺待入账金额
	 * @return string
	 */
	private function get_amount_tobe_booked(){
		$shop_query        = new IQuery('shop');
		$user_query        = new IQuery('user');
		$shop_query->where = 'own_id = '.$this->user['user_id'];
		if(empty($shop_data = $shop_query->find())){
			$shop_data = false;
		}else{
			$shop_data = $shop_data[0];
		}
		$temp = '( user_id = '.$this->user['user_id'];
		if($shop_data){
			$user_query->where = 'shop_identify_id = '.$shop_data['identify_id'];
			$user_data         = $user_query->find();
			foreach($user_data as $key => $value){
				$temp .= ' or user_id = '.$value['id'];
			}
		}
		$temp .= ')';
		$where               = $temp.' and pay_type != 0 and status = 5 and is_shop_checkout = 0';
		$order_query         = new IQuery('order');
		$order_query->where  = $where;
		$order_query->fields = 'sum(real_amount) as amount_tobe_booked';
		$amount_tobe_booked  = $order_query->find()[0]['amount_tobe_booked'];
		$amount_tobe_booked  = empty($amount_tobe_booked) ? '0.00' : $amount_tobe_booked;
		if(!empty($shop_data)){
			$shop_category        = new IQuery('shop_category');
			$shop_category->where = 'id = '.$shop_data['category_id'];
			$shop_category_data   = $shop_category->find();
			$amount_tobe_booked   = $amount_tobe_booked*$shop_category_data[0]['rebate'];
		}
		return $amount_tobe_booked;
	}
	
	/**
	 * 店铺收益明显数据
	 */
	function get_shop_settlement_info(){
		$shop_identify_id = ISession::get('shop_identify_id');
		$year             = IFilter::act(IReq::get('year'), 'int');
		$month            = IFilter::act(IReq::get('month'), 'int');
		
		$ret                      = []; //店铺收益明细数据
		$ret['orders']            = [];
		$shop_total_rebate_amount = 0.00;
		$shop_total_goods_amount  = 0.00;
		
		$shop_query        = new IQuery('shop');
		$shop_query->where = 'identify_id = '.$shop_identify_id;
		$shop_data         = $shop_query->find();
		$ret['name']       = $shop_data[0]['name'];
		
		$shop_settlement_query        = new IQuery('settlement_shop');
		$shop_settlement_query->where = 'seller_id = '.$shop_identify_id.' and date_format( settlement_time, \'%Y%m\' ) ='.$year.$month;
		
		$shop_settlement_data = $shop_settlement_query->find();
		$order_query          = new IQuery('order');
		foreach($shop_settlement_data as $k => $v){
			$order_query->where          = 'id = '.$v['order_id'];
			$order_data                  = $order_query->find()[0];
			$order_data['rebate_amount'] = $v['rebate_amount'];
			$shop_total_rebate_amount += $v['rebate_amount'];
			$shop_total_goods_amount += $v['goods_amount'];
			$ret['orders'][] = $order_data;
		}
		$ret['shop_total_rebate_amount'] = $shop_total_rebate_amount;
		$ret['shop_total_goods_amount']  = $shop_total_goods_amount;
		
		foreach($ret['orders'] as $k => $v){
			$temp = Api::run('getOrderGoodsListByGoodsid', array('#order_id#', $v['id']));
			foreach($temp as $key => $good){
				$good_info               = JSON::decode($good['goods_array']);
				$temp[$key]['good_info'] = $good_info;
				$temp[$key]['img']       = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$temp[$key]['img']."/w/160/h/160");
			}
			$ret['orders'][$k]['goods_list']      = $temp;
			$ret['orders'][$k]['orderStatusText'] = Order_Class::orderStatusText(Order_Class::getOrderStatus($v));
		}
		
		$this->json_echo($ret);
	}
	
	/**
	 * 获取合伙人的收益明细数据
	 */
	function get_recommender_settlement_info(){
		$year                            = IFilter::act(IReq::get('year'), 'int');
		$month                           = IFilter::act(IReq::get('month'), 'int');
		$recommender_total_rebate_amount = 0.00; //收益总金额
		$recommender_total_goods_amount  = 0.00; //商品总金额
		$ret                             = []; //合伙人收益明细
		$shop_query                      = new IQuery('shop');
		$shop_query->where               = 'recommender = '.$this->user['user_id'];
		$shop_data                       = $shop_query->find();
		foreach($shop_data as $key => $value){
			$settlement_shop_query         = new IQuery('settlement_recommender');
			$settlement_shop_query->where  = 'recommender_id = '.$this->user['user_id'].' and date_format( settlement_time, \'%Y%m\' ) ='.$year.$month;
			$settlement_shop_data          = $settlement_shop_query->find();
			$ret[$key]['name']             = $value['name'];
			$ret[$key]['amount_available'] = $value['amount_available'];
			$order_query                   = new IQuery('order');
			$ret[$key]['orders']           = [];
			foreach($settlement_shop_data as $k => $v){
				$order_query->where          = 'id = '.$v['order_id'];
				$order_data                  = $order_query->find()[0];
				$order_data['rebate_amount'] = $v['rebate_amount'];
				$recommender_total_rebate_amount += $v['rebate_amount'];
				$recommender_total_goods_amount += $v['goods_amount'];
				$ret[$key]['orders'][] = $order_data;
			}
			foreach($ret[$key]['orders'] as $k => $v){
				$temp              = Api::run('getOrderGoodsListByGoodsid', array('#order_id#', $v['id']));
				$goods_total_price = 0; //商品总金额
				foreach($temp as $key => $good){
					$goods_total_price += $good['real_price']*$good['goods_nums'];
					$good_info               = JSON::decode($good['goods_array']);
					$temp[$key]['good_info'] = $good_info;
					$temp[$key]['img']       = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$temp[$key]['img']."/w/160/h/160");
				}
				$ret[$key]['orders'][$k]['goods_total_price'] = $goods_total_price;
				//                $recommender_total_goods_amount += $goods_total_price;
				$ret[$key]['orders'][$k]['goods_list']      = $temp;
				$ret[$key]['orders'][$k]['orderStatusText'] = Order_Class::orderStatusText(Order_Class::getOrderStatus($v));
			}
		}
		$ret[0]['recommender_total_goods_amount']  = $recommender_total_goods_amount;
		$ret[0]['recommender_total_rebate_amount'] = $recommender_total_rebate_amount;
		$this->json_echo($ret);
	}
	
	/**
	 *
	 */
	public function wechat_cx_login(){
//		$param = array(
//			'code' => IFilter::act(IReq::get('code')),
//		);
		@session_start();
		$session = session_id();
		common::dblog($session);
		$wechat = new wechat();
		$wechat->login('orEYdw67mbwIn8_cvNS1i8gTGpNo');
		$user  = $this->user['user_id'];
	}
	
	/**
	 * @brief 获取用户头像
	 * @param $user_id
	 * @return bool
	 */
	public function get_user_head_ico($user_id){
		$user_query        = new IQuery('user');
		$user_query->where = 'id = '.$user_id;
		$ret               = $user_query->find();
		if(!empty($ret[0])){
			return $ret[0]['head_ico'];
		}else{
			return false;
		}
	}
	
	/**
	 * @param $data
	 */
	public function testa(){
		var_dump($this->user);
	}
	
	private function json_echo($data){
		echo json_encode($data);
		exit();
	}
	
}