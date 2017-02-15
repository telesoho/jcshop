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
	
	//---------------------------------------------------主要页面---------------------------------------------------
	/**
	 * 商城主页
	 */
	public function index(){
		$param = $this->checkData(array());
		/* 首页轮播图 */
		$banner = Api::run('getBannerList');
		foreach($banner as $k => $v){
			$banner[$k]['img'] = IWeb::$app->config['image_host'].'/'.$v['img'];
		}
		
		/* 限时购 */
		$modelSpeed = new IModel('activity_speed');
		$infoSpeed  = $modelSpeed->query('type=1 AND status=1 AND start_time<='.time().' AND end_time>='.time(), 'id,start_time,end_time','start_time DESC',1);
		if(!empty($infoSpeed)){
			$infoSpeed 				 = $infoSpeed[0];
			$querySpeedGoods         = new IQuery('activity_speed_access AS m');
			$querySpeedGoods->join   = 'LEFT JOIN goods AS g ON g.id=m.goods_id';
			$querySpeedGoods->where  = 'pid='.$infoSpeed['id'];
			$querySpeedGoods->fields = 'm.sell_price,g.name_de,g.id,g.name,g.name_de,g.img,g.sell_price AS old_price,g.purchase_price';
			$infoSpeed['list']       = $querySpeedGoods->find();
			if(!empty($infoSpeed['list'])){
				foreach($infoSpeed['list'] as $k => $v){
					$infoSpeed['list'][$k]['name'] = empty($v['name_de']) ? $v['name'] : $v['name_de'];
					$infoSpeed['list'][$k]['img']  = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/240/h/240");
				}
			}
		}
		
		/* 专辑列表 */
		$queryArt         = new IQuery('article as m');
		$queryArt->join   = 'left join article_category as c on c.id=m.category_id';
		$queryArt->where  = 'm.top=1 AND m.visibility=1 AND m.category_id NOT IN (3)';
		$queryArt->fields = 'm.id,m.title,m.image,m.visit_num,m.category_id,c.icon,c.name as category_name';
		$queryArt->order  = 'm.sort DESC,m.id DESC';
		$queryArt->limit  = 5;
		$listArt          = $queryArt->find();
		if(!empty($listArt)){
			//商品列表模型
			$queryGoods         = new IQuery('goods as m');
			$queryGoods->join   = 'left join relation as r on r.goods_id=m.id';
			$queryGoods->fields = 'm.id,m.name_de,m.name,m.sell_price,m.purchase_price,m.img';
			$queryGoods->order  = 'sale DESC,visit DESC';
			$queryGoods->group  = 'm.id';
			$queryGoods->limit  = 5;
			//商品统计模型
			$queryGoodsCount         = new IQuery('goods as m');
			$queryGoodsCount->join   = 'left join relation as r on r.goods_id=m.id';
			$queryGoodsCount->fields = 'count(m.id) as num';
			//专辑收藏模型
			$queryFavorite         = new IQuery('favorite_article');
			$queryFavorite->fields = 'count(id) as num';
			//收藏人数
			foreach($listArt as $k => $v){
				$listArt[$k]['icon']  = empty($v['icon']) ? '' : IWeb::$app->config['image_host'].'/'.$v['icon'];
				$listArt[$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/750/h/380");
				//收藏人数
				$queryFavorite->where        = 'aid='.$v['id'];
				$count                       = $queryFavorite->find();
				$listArt[$k]['favorite_num'] = $count[0]['num'];
				//当前用户是否已收藏
				if(!empty($this->user['user_id'])){
					$queryFavorite->where = 'aid='.$v['id'].' and user_id='.$this->user['user_id'];
					$count                = $queryFavorite->find();
					$count                = $count[0]['num'];
				}else{
					$count = 0;
				}
				$listArt[$k]['is_favorite'] = $count;
				//相关商品数量
				$queryGoodsCount->where   = 'm.is_del=0 and r.article_id='.$v['id'];
				$count                    = $queryGoodsCount->find();
				$listArt[$k]['goods_num'] = $count[0]['num'];
				//相关商品列表
				$queryGoods->where   = 'm.is_del=0 and r.article_id='.$v['id'];
				$listArt[$k]['list'] = $queryGoods->find();
				if(!empty($listArt[$k]['list'])){
					/* 计算活动商品价格 */
					$listArt[$k]['list'] = api::run('goodsActivity', $listArt[$k]['list']);
					foreach($listArt[$k]['list'] as $k1 => $v1){
						$listArt[$k]['list'][$k1]['name'] = empty($v1['name_de']) ? $v1['name'] : $v1['name_de'];
						$listArt[$k]['list'][$k1]['img']  = empty($v1['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/180/h/180");
					}
				}
			}
		}
		
		/* 推荐专区 */
		$pro_array = array(
			array('cid' => 126, 'title' => '美妆', 'banner' => IWeb::$app->config['image_host'].'/upload/pro_list/126.png'),
			array('cid' => 134, 'title' => '个护', 'banner' => IWeb::$app->config['image_host'].'/upload/pro_list/134.png'),
			array('cid' => 2, 'title' => '健康', 'banner' => IWeb::$app->config['image_host'].'/upload/pro_list/2.png'),
		);
		$pro_list  = array();
		foreach($pro_array as $v){
			$cid             = goods_class::catChild($v['cid']);
			$queryGo         = new IQuery('goods AS m');
			$queryGo->join   = 'LEFT JOIN category_extend AS c ON c.goods_id=m.id';
			$queryGo->where  = 'm.is_del=0 AND c.category_id IN ('.$cid.')';
			$queryGo->fields = 'm.id,m.name,m.name_de,m.sell_price,m.purchase_price,m.img';
			$queryGo->order  = 'sale DESC,visit DESC';
			$queryGo->group  = 'm.id';
			$queryGo->limit  = 4;
			$listGo          = $queryGo->find();
			if(!empty($listGo)){
				foreach($listGo as $k1 => $v1){
					$listGo[$k1]['name'] = empty($v1['name_de']) ? $v1['name'] : $v1['name_de'];
					$listGo[$k1]['img']  = empty($v1['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/180/h/180");
				}
			}
			$pro_list[] = array(
				'banner'     => $v['banner'],
				'title'      => $v['title'],
				'goods_list' => $listGo,
			);
		}
		
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => array(
			'banner'       => $banner, //轮播图
			'speed'        => $infoSpeed, //限时购
			'article_list' => $listArt, //推荐专辑
			'pro_list'     => $pro_list, //推荐专区
		)));
	}
	
	/**
	 * 主分类：药妆、个护、宠物、健康、零食
	 */
	public function pro_list(){
		/* 获取参数 */
		$param = $this->checkData(array(
			array('tid','int',1,'分类ID[1药妆-2个护-3宠物-4健康-5零食]'),
		));
		$user_id = isset($this->user['user_id']) ? $this->user['user_id'] : 0;
		
		/* 分类 */
		switch($param['tid']){
			case 1: //药妆
				$cid           = 126; //个性美妆
				$aid           = 15; //专辑分类
				$data['title'] = '狗子推荐';
				$data['pic']   = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/gou.png'; //狗子推荐
				break;
			case 2: //个护
				$cid           = 134; //基础护肤
				$aid           = 18; //专辑分类
				$data['title'] = '死鱼推荐';
				$data['pic']   = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/siyu.png'; //死鱼推荐
				break;
			case 3: //宠物
				$cid           = 6; //宠物用品
				$aid           = 17; //专辑分类
				$data['title'] = '腿毛推荐';
				$data['pic']   = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/tui.png'; //腿毛推荐
				break;
			case 4: //健康
				$cid           = 2; //居家药品
				$aid           = 16; //专辑分类
				$data['title'] = 'RURU推荐';
				$data['pic']   = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/ruru.png'; //RURU推荐
				break;
			case 5: //零食
				$cid           = 7; //日式美食
				$aid           = 19; //专辑分类
				$data['title'] = 'K哥推荐';
				$data['pic']   = IWeb::$app->config['image_host'].'/views/mobile/skin/default/image/jmj/product/kge.png'; //K哥推荐
				break;
			default:
				$this->returnJson(array('code' => '007001', 'msg' => $this->errorInfo['007001']));
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
		$queryGoods->fields = 'm.id,m.name,m.sell_price,m.purchase_price,m.img';
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
			$queryGoods->fields = 'm.id,m.name,m.sell_price,m.purchase_price,m.img';
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
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$data));
	}
	//---------------------------------------------------用户---------------------------------------------------
	/**
	 * 用户登陆 TODO 待完善
	 */
	public function login(){
		$param = $this->checkData(array(
			array('username', 'string', 1, '用户名'),
			array('password', 'string', 1, '密码'),
		));
		/* 登陆 */
		$model = new IModel('user');
		$info = $model->getObj('username="'.$param['username'].'"','id');
		if(empty($info)||$param['password']!='12345678') $this->returnJson(array('code'=>'008001','msg'=>$this->errorInfo['008001'])); //用户名或密码错误
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>array('token'=>$this->tokenCreate($info['id']))));
	}
	
	//---------------------------------------------------购物车---------------------------------------------------
	/**
	 * 购物车
	 */
	public function cart(){
		$param = $this->checkData(array());
		/* 购物车计算 */
		$countObj = new CountSum();
		$data     = $countObj->cart_count();
		if(is_string($data)) $this->returnJson(array('code' => '-1', 'msg' => $data));
		/* 购物车商品列表 */
		foreach($data['goodsList'] as $k => $v){
			$data['goodsList'][$k]['img'] = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/120/h/120");
		}
		/* 配送方式 */
		$data['delivery'] = Api::run('getDeliveryList');
		/* 数据返回 */
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => $data));
	}
	
	/**
	 * 购物车商品数量
	 */
	public function cart_count(){
		$param = $this->checkData(array());
		$user_id = $this->tokenCheck();
		/* 购物车中商品数量 */
		$modelCar = new IModel('goods_car');
		$infoCar  = $modelCar->getObj('user_id='.$user_id);
		$car_count = 0;
		if(!empty($infoCar)){
			$car_list               = JSON::decode(str_replace(array('&', '$'), array('"', ','), $infoCar['content']));
			foreach($car_list['goods'] as $k => $v)
				$car_count += $v;
		}
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>array('car_count'=>$car_count)));
	}
	
	/**
	 * 清空购物车
	 */
	public function cart_clear(){
		$param = $this->checkData(array());
		/* 清空购物车 */
		$user_id = $this->tokenCheck();
		$cartObj = new Cart();
		$cartObj->clear();
		$this->returnJson(array('code'=>'0','msg'=>'ok'));
	}
	
	/**
	 * 加入购物车
	 */
	function cart_join(){
		$param          = $this->checkData(array(
			array('goods', 'string', 1, '商品格式<商品ID:数量:类型[goods商品-product货品]>多个商品用英文逗号分割'),
		));
		$param['goods'] = trim($param['goods'], ',');
		//加入购物车
		$cartObj = new Cart();
		foreach(explode(',', $param['goods']) as $k => $v){
			$goods = explode(':', $v);
			if(count($goods)!=3){
				(new IModel('goods_car'))->rollback(); //回滚
				$this->returnJson(array('code' => '004002', 'msg' => $this->errorInfo['004002'])); //参数格式有误
			}
			$rel = $cartObj->add($goods[0], $goods[1], $goods[2]);
			if(!$rel){
				(new IModel('goods_car'))->rollback(); //回滚
				$this->returnJson(array('code' => '001004', 'msg' => $cartObj->getError())); //购物车加入失败
			}
		}
		
		$this->returnJson(array('code' => '0', 'msg' => 'ok'));
	}
	
	/**
	 * 购物车结算页面
	 */
	public function cart2(){
		$param = $this->checkData(array(
			array('id','int',0,'商品ID'),
			array('type','string',0,'商品类型[goods商品购买-product货品购买]'),
			array('num','string',0,'商品数量'),
			array('code','int',0,'优惠券码'),
			array('ticket_aid','int',0,'优惠券ID'),
		));
		//必须为登录用户
		$user_id = isset($this->user['user_id'])&&!empty($this->user['user_id']) ? $this->user['user_id'] : $this->returnJson(array('code'=>'001001','msg'=>$this->errorInfo['001001']));;
		
		//计算商品
		$countSumObj = new CountSum($user_id);
		$result      = $countSumObj->cart_count($param['id'], $param['type'], $param['num']);
		if($countSumObj->error) $this->returnJson(array('code'=>'0','msg'=>$countSumObj->error));
		
		//获取收货地址
		$addressObj  = new IModel('address');
		$addressList = $addressObj->query('user_id = '.$user_id, "*", "is_default desc");
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
		$data['gid']         = $param['id'];
		$data['type']        = $param['type'];
		$data['num']         = $param['num'];
		$data['promo']       = '';
		$data['active_id']   = '';
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
		
		/* 使用优惠券 */
		if(!empty($param['code'])){
			/* 优惠券码 */
			$rel = ticket::calculateCode($data, $param['code']);
			if($rel['code']!='0') $this->returnJson($rel);
			$data = $rel['data'];
		}else if(!empty($param['ticket_aid'])){
			/* 活动优惠券 */
			$rel = ticket::calculateActivity($data, $param['ticket_aid']);
			if($rel['code']!='0') $this->returnJson($rel);
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
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$data));
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
					$infoGoods  = $modelGoods->getObj('id='.$v['goods_id'], 'id as goods_id,name,goods_no,img,sell_price,purchase_price,weight,store_nums');
					//校验
					if($infoGoods['store_nums']<$v['nums']) $this->json_echo(apiReturn::go('007002')); //商品库存不足
					$query         = new IQuery('activity_speed AS m');
					$query->join   = 'LEFT JOIN activity_speed_access AS a ON a.pid=m.id '.
						'LEFT JOIN goods AS g ON g.id=a.goods_id';
					$query->where  = 'g.is_del=0 AND m.type=2 AND m.status=1 AND a.goods_id='.$v['goods_id'];
					$query->fields = 'g.name,g.img,a.id,a.goods_id,g.sell_price,g.purchase_price,a.sell_price AS now_price,a.nums,a.quota,a.delivery,m.type,m.start_time,m.end_time';
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
		$param = $this->checkData(array(
			array('type','int',1,'优惠券类型[1可使用-2已过期]'),
			array('page','int',0,'分页编号'),
		));
		$user_id = isset($this->user['user_id'])&&!empty($this->user['user_id']) ? $this->user['user_id'] : $this->returnJson(array('code'=>'001001','msg'=>$this->errorInfo['001001']));
		/* 可使用优惠券 */
		$query       = new IQuery('activity_ticket as m');
		$query->join = 'LEFT JOIN activity_ticket_access AS a ON a.ticket_id=m.id';
		switch($param['type']){
			//可使用
			case 1:
				$where = 'a.user_id='.$user_id.' AND a.status=1 AND (CASE m.`time_type` WHEN 1 THEN m.`end_time`>='.time().' WHEN 2 THEN (m.`day`*24*60*60+a.`create_time`)>='.time().' END )';
				break;
			//已过期
			case 2:
				$where = 'a.user_id='.$user_id.' AND (a.status!=1 OR (CASE m.`time_type` WHEN 1 THEN m.`end_time`<'.time().' WHEN 2 THEN (m.`day`*24*60*60+a.`create_time`)<'.time().' END ))';
				break;
			default:
				$this->returnJson(array('code'=>'002015','msg'=>$this->errorInfo['002015']));
		}
		$query->where    = $where;
		$query->fields   = 'a.id,m.name,m.start_time,m.end_time,m.type,m.rule,a.create_time,m.day,m.time_type';
		$query->page     = $param['page']<1 ? 1 : $param['page'];
		$query->pagesize = 100;
		$data            = $query->find();
		if($param['page']>$query->getTotalPage()) $data = array();
		if(!empty($data)){
			foreach($data as $k => $v){
				switch($v['time_type']){
					//统一有效期
					case 1:
						$data[$k]['start_time'] = date('m-d H:i', $v['start_time']);
						$data[$k]['end_time']   = date('m-d H:i', $v['end_time']);
						break;
					//领取后有效期
					case 2:
						$data[$k]['start_time'] = date('m-d H:i', $v['create_time']);
						$data[$k]['end_time']   = date('m-d H:i', $v['day']*24*60*60+$v['create_time']);
						break;
				}
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
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$data));
	}
	
	/**
	 * 领取优惠券
	 */
	public function get_ticket(){
		/* 接收参数 */
		$param = $this->checkData(array(
			array('tid','int',1,'优惠券ID'),
		));
		if(!isset($this->user['user_id']) || empty($this->user['user_id'])) $this->json_echo(apiReturn::go('001001'));
		$user_id = $this->user['user_id'];
		
		/* 优惠券详情 */
		$modelTic = new IModel('activity_ticket');
		$infoTic  = $modelTic->getObj('pid=0 AND id='.$param['tid'].' AND end_time>'.time());
		if(empty($infoTic)) $this->returnJson(array('code'=>'002007','msg'=>$this->errorInfo['002007'])); //优惠券不存在或已过期
		
		/* 是否已领取 */
		$modelAcc = new IModel('activity_ticket_access');
		$infoAcc  = $modelAcc->getObj('`from`=0 AND user_id='.$user_id.' AND ticket_id='.$param['tid']);
		if(!empty($infoAcc)) $this->returnJson(array('code'=>'002034','msg'=>$this->errorInfo['002034'])); //已经领取过该优惠券
		
		/* 开始领取 */
		$modelAcc->setData(array('user_id' => $user_id, 'ticket_id' => $param['tid'], 'status' => 1, 'from' => 0, 'create_time' => time(),));
		$rel = $modelAcc->add();
		$rel>0 ? $this->returnJson(array('code'=>'0','msg'=>'恭喜您已领取“'.$infoTic['name'].'”')) : $this->returnJson(array('code'=>'002031','msg'=>$this->errorInfo['002031']));
	}
	
	/**
	 * 领取活动优惠券（随机）
	 */
	public function get_ticket_activity(){
		/* 接收参数 */
		$param = $this->checkData(array(
			array('aid','int',1,'活动ID'),
			array('pid','ing',0,'分享人ID'),
		));
		$user_id = isset($this->user['user_id'])&&!empty($this->user['user_id']) ? $this->user['user_id'] : $this->returnJson(array('code'=>'001001','msg'=>$this->errorInfo['001001']));
		if($user_id==$param['pid']) $param['pid'] = '';
		
		/* 活动详情 */
		$rel = Activity::checkStatus($param['aid']); //检查活动状态
		if($rel['code']!=0) $this->json_echo($rel);
		$dataAti = $rel['data'];
		
		/* 包含的优惠券列表 */
		$queryTck         = new IQuery('activity_ticket');
		$queryTck->where  = 'pid='.$param['aid'];
		$queryTck->fields = 'id,name,type,rule';
		$dataTck          = $queryTck->find();
		if(empty($dataTck)) $this->returnJson(array('code'=>'002020','msg'=>$this->errorInfo['002020'])); //活动不包含优惠券
		$idTck = array(); //优惠券ID
		foreach($dataTck as $k => $v) $idTck[] = $v['id'];
		
		$modelAcc = new IModel('activity_ticket_access');
		/* 是否已领完 */
		$countAcc = $modelAcc->get_count('ticket_id in ('.implode(',', $idTck).')');
		if($countAcc>=$dataAti['num']) $this->returnJson(array('code'=>'002022','msg'=>$this->errorInfo['002022'])); //优惠券已领完
		/* 是否已领取 */
		switch($param['aid']){
			case 1: //新人活动
				$dataAcc = $modelAcc->getObj('`from`='.(empty($param['pid']) ? 0 : $param['pid']).' AND user_id='.$user_id.' AND ticket_id in ('.implode(',', $idTck).')');
				if(!empty($dataAcc)) empty($param['pid']) ? $this->returnJson(array('code'=>'002021','msg'=>$this->errorInfo['002021'])) : $this->returnJson(array('code'=>'002024','msg'=>$this->errorInfo['002024'])); //已领取过优惠券
				/* 开始领取 */
				$dataTckOn = $dataTck[rand(0, count($dataTck)-1)];
				break;
			case 2||3: //圣诞元旦活动
				$where   = 'user_id='.$user_id.' AND ticket_id in ('.implode(',', $idTck).') AND `from`=0';
				$dataAcc = $modelAcc->query($where, '*', 'id desc', 1);
				if(!empty($dataAcc) && strtotime(date('Y-m-d', time()))<=$dataAcc[0]['create_time']) $this->returnJson(array('code'=>'002025','msg'=>$this->errorInfo['002025'])); //今天已经领取过优惠券
				if(!empty($param['pid'])){
					$dataAcc = $modelAcc->getObj('user_id='.$user_id.' AND ticket_id in ('.implode(',', $idTck).') AND `from`!=0');
					if(!empty($dataAcc)) $this->returnJson(array('code'=>'002029','msg'=>$this->errorInfo['002029'])); //好友分享的优惠券只能领取一次
				}
				/* 开始领取 */
				$dataTckOn = rand(1, 5)==1 ? $dataTck[0] : $dataTck[rand(1, count($dataTck)-1)];
				break;
			default:
				$dataAcc = $modelAcc->getObj('user_id='.$user_id.' AND ticket_id in ('.implode(',', $idTck).')');
				if(!empty($dataAcc)) $this->returnJson(array('code'=>'002026','msg'=>$this->errorInfo['002026'])); //只能领取一次
		}
		
		/* 写入数据 */
		$modelAcc->setData(array('user_id' => $user_id, 'ticket_id' => $dataTckOn['id'], 'status' => 1, 'from' => empty($param['pid']) ? 0 : $param['pid'], 'create_time' => time(),));
		$rel = $modelAcc->add();
		if($rel==false) $this->returnJson(array('code'=>'002023','msg'=>$this->errorInfo['002023'])); //领取失败
		
		/* 分享人增加积分 */
		if(!empty($param['pid'])){
			//增加积分次数上限
			$countShare = $modelAcc->get_count('`from`='.(empty($param['pid']) ? 0 : $param['pid']).' AND ticket_id in ('.implode(',', $idTck).')');
			if($countShare<$dataAti['share_num']){
				$modelMember = new IModel('member');
				$modelMember->setData(array('point' => 'point+'.$dataAti['share_score']));
				$modelMember->update('user_id='.$param['pid'], array('point'));
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
		$this->returnJson(array('code'=>'0','msg'=>'恭喜您已领取“'.$dataTckOn['msg'].'”','data'=>$dataTckOn));
	}
	
	/**
	 * ---------------------------------------------------活动---------------------------------------------------*
	 */
	/**
	 * 活动商品列表
	 */
	public function activity_goods_list(){
		$param = $this->checkData(array(
			array('aid','int',0,'活动ID'),
			array('cid','string',0,'分类ID'),
			array('bid','string',0,'品牌ID'),
			array('did','int',0,'推荐ID'),
			array('tag','string',0,'标签'),
			array('page','int',0,'分页'),
			array('pagesize','int',0,'每页条数'),
		));
		$param['pagesize'] = empty($param['pagesize']) ? 20 : $param['pagesize'];
		$data  = Api::run('goodsList', $param);
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$data));
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
				$dataGoods  = $modelGoods->getObj('is_del!=1 AND id='.$dataGrow['did'], 'id as goods_id,name,goods_no,img,sell_price,purchase_price,weight,store_nums');
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
		$queryGoods->fields = 'a.id,a.goods_id,a.min_price,g.name,g.img,g.sell_price,go.purchase_price,m.start_time,m.end_time,m.status';
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
		$queryGoods->fields = 'a.id,a.goods_id,a.min_price,a.rand,g.name,g.img,g.sell_price,g.purchase_price,m.start_time,m.end_time,m.status';
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
		$param = $this->checkData(array(
			array('type', 'int', 1, '活动类型[1限时购-2秒杀]'),
			array('time_id', 'int', 0, '时间段ID'),
			array('page', 'int', 0, '分页编号'),
		));
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
				$listSpeed[$k]['conduct'] = $v['start_time']<=time() ? ($v['end_time']<time() ? 3 : 2) : 1; //1未开始-2正在进行-3已结束
				$param['time_id']         = empty($param['time_id']) ? $v['id'] : $param['time_id'];
			}
		}
		/* 秒杀商品列表 */
		$queryGoods           = new IQuery('activity_speed_access AS m');
		$queryGoods->join     = 'LEFT JOIN goods AS g ON g.id=m.goods_id LEFT JOIN activity_speed AS s ON s.id=m.pid';
		$queryGoods->fields   = 'g.id,g.name,g.store_nums,m.nums,m.sell_price,g.purchase_price,g.img,g.market_price,s.start_time,s.end_time';
		$queryGoods->where    = 'g.is_del=0 AND pid='.$param['time_id'];
		$queryGoods->page     = $param['page']<1 ? 1 : $param['page'];
		$queryGoods->pagesize = 10;
		$listGoods            = $queryGoods->find();
		if($param['page']>$queryGoods->getTotalPage()) $listGoods = array();
		if(!empty($listGoods)){
			foreach($listGoods as $k => $v){
				$listGoods[$k]['img']        = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
				$listGoods[$k]['store_nums'] = $v['nums']<$v['store_nums'] ? $v['nums'] : $v['store_nums'];
				//已售数量
				$queryOrder                 = new IQuery('order AS m');
				$queryOrder->join           = 'LEFT JOIN order_goods AS g ON g.order_id=m.id';
				$queryOrder->fields         = 'sum(g.goods_nums) AS sum';
				$queryOrder->where          = 'm.pay_status=1 AND m.create_time>="'.date('Y-m-d H:i:s', $v['start_time']).'" AND m.create_time<="'.date('Y-m-d H:i:s', $v['end_time']).'" AND g.goods_id='.$v['id'];
				$sum                        = $queryOrder->find();
				$listGoods[$k]['sale_nums'] = empty($sum[0]['sum']) ? 0 : $sum[0]['sum'];
			}
		}
		
		/* 数据返回 */
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => array('now' => $param['time_id'], 'time_list' => $listSpeed, 'goods_list' => $listGoods)));
	}
	
	/**
	 * 圣诞节活动首页
	 */
	public function christmas_index(){
		/* 获取活动热销商品 */
		$query         = new IQuery('goods as m');
		$query->join   = 'LEFT JOIN brand AS b ON b.id=m.brand_id LEFT JOIN commend_goods AS d ON d.goods_id=m.id LEFT JOIN category_extend AS c ON c.goods_id=m.id ';
		$query->fields = 'm.id,m.name,m.sell_price,m.purchase_price,m.original_price,m.img,b.name as brand_name,b.logo as brand_logo';
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
		$queryGoods->fields = 'id,name,sell_price,purchase_price,img';
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
        $image11     = IFilter::act(IReq::get('aaaa'), 'string');
        $image22     = IFilter::act(IReq::get('bbbb'), 'string');
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
                if (file_exists(__DIR__.'/../'.$image1)){
                    $image1 = explode('/',$image1,2)[1];
                } else {
                    $sqlData['media_id1'] = $image1;
                    $url1 = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id=' . $image1;
                    $image1 = common::save_url_image($url1,$dir,1);
                    common::save_wechat_resource($sqlData['media_id1'], $image1);
                }
            }
            if (!empty($image2)){
                if (file_exists(__DIR__.'/../'.$image2)){
                    $image2 = explode('/',$image2,2)[1];
                } else {
                    $sqlData['media_id2'] = $image2;
                    $url2 = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id=' . $image2;
                    $image2 = common::save_url_image($url2,$dir,2);
                    common::save_wechat_resource($sqlData['media_id2'], $image2);
                }
            }

            $sqlData['sfz_image1'] = $image1;
            $sqlData['sfz_image2'] = $image2;
            $sqlData['create_time'] = date('Y-m-d H:i:s',time());



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
		$ret = $model->update("id = ".$id." and user_id = ".$this->user['user_id']);
		if ($ret){
		    $this->json_echo(array('ret' => $ret));
        } else {
		    $this->json_echo(array('ret' => false));
        }
	}
	
	/**
	 * ---------------------------------------------------订单---------------------------------------------------*
	 */
	/**
	 * 订单列表
	 */
	public function order_list(){
		/* 获取数据 */
		$param = $this->checkData(array(
			array('type','string',0,'类型=u店铺主'),
			array('class','int',0,'分类ID[不传参全部订单-1待付款-2待发货-3待收货-4已完成]'),
			array('page','int',0,'分页编号'),
		));
		$user_id  = isset($this->user['user_id']) && !empty($this->user['user_id']) ? $this->user['user_id'] : $this->returnJson(array('code'=>'001001','msg'=>$this->errorInfo['001001']));
		$user = array($user_id);
		
		/* 分类 */
		switch($param['class']){
			//全部订单
			case 0:
				$where = 'pay_type!=0 AND status!=4';
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
				$this->returnJson(array('code'=>'003001','msg'=>$this->errorInfo['003001']));
		}
		
		/* 店铺主 */
		if($param['type']=='u'){
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
		$query->page     = $param['page']<1 ? 1 : $param['page'];
		$query->pagesize = 10;
		$data            = $query->find();
		if($param['page']>$query->getTotalPage()) $data = array();
		if(!empty($data)){
			$relation   = array('已完成' => '删除订单', '正在配货' => '取消订单', '等待付款' => '去支付', '已发货' => '查看物流', '已取消' => '已取消', '部分发货' => '查看物流');
			$relation_k = array_keys($relation);
			foreach($data as $k => $v){
				//评论ID
				$data[$k]['comment_id'] = Comment_Class::is_comment($v['order_no']);
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
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$data));
	}
	
	/**
	 * @brief 订单详情
	 * @return String
	 */
	public function order_detail(){
		/* 接收参数 */
		$param = $this->checkData(array(
			array('id','int',1,'订单ID'),
		));
		$user_id = $this->tokenCheck();
		/* 订单详情 */
		$orderObj   = new order_class();
		$order_info = $orderObj->getOrderShow($param['id'], $user_id);
		if(!$order_info) $this->returnJson(array('code'=>'003002','msg'=>$this->errorInfo['003002'])); //订单不存在
		
		$orderStatus = Order_Class::getOrderStatus($order_info);
		switch($orderStatus){
			case 2: //待支付
				$orderStatusT = 0;
				break;
			case 4: //待发货
				$orderStatusT = 1;
				break;
			case 3:case 8:case 11: //待收货
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
			'comment_id'  => Comment_Class::is_comment($order_info['order_no']),
		);
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$data));
	}
	
	/**
	 * 订单商品列表
	 */
	public function order_goods_list(){
		/* 接收参数 */
		$param = $this->checkData(array(
			array('order_id','int',1,'订单ID'),
		));
		$user_id  = isset($this->user['user_id']) && !empty($this->user['user_id']) ? $this->user['user_id'] : $this->returnJson(array('code'=>'001001','msg'=>$this->errorInfo['001001']));
		//订单详情
		$modelOrder = new IModel('order');
		$infoOrder  = $modelOrder->getObj('id='.$param['oarder_id'].' AND user_id='.$user_id);
		if(empty($infoOrder)) $this->returnJson(array('code'=>'003002','msg'=>$this->errorInfo['003002']));
		/* 订单商品 */
		$goodsList = Api::run('getOrderGoodsListByGoodsid', array('#order_id#', $infoOrder['id']));
		if(!empty($goodsList)){
			foreach($goodsList as $k => $v){
				$goodsList[$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
			}
		}
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$goodsList));
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
		$param   = $this->checkData(array(
			array('id', 'int', 1, '评论ID'),
			array('content', 'string', 0, '评论内容'),
			array('tag_id', 'string', 0, '标签ID[多个使用"英文逗号"分割]'),
			array('image_media_id', 'string', 0, '微信图片ID[多个使用"英文逗号"分割]'),
			array('voice_media_id', 'string', 0, '微信语音ID'),
		));
		$user_id = $this->tokenCheck();
		//检测
		$comment = Comment_Class::can_comment($param['id'], $user_id);
		if(is_string($comment)) $this->returnJson(array('code' => '-1', 'msg' => $comment));
		
		/* 通过微信下载图片和语音 */
		$wechat = new wechat();
		$wechat->setConfig();
		$data = array('image' => array(), 'voice' => '');
		if(!empty($param['image_media_id'])){
			foreach(explode(',', $param['image_media_id']) as $k => $v){
				$rel = $wechat->getMedia($v);
				$rel!=false ? $data['image'][] = $rel : $this->returnJson(array('code' => '003010', 'msg' => $this->errorInfo['003010']));
			}
		}
		if(!empty($param['voice_media_id'])){
			$data['voice'] = $wechat->getMedia($param['voice_media_id']);
			if(!$data['voice']) $this->returnJson(array('code' => '003009', 'msg' => $this->errorInfo['003009']));
		}
		
		/* 开始评论 */
		$modelComment = new IModel("comment");
		$listComment = $modelComment->query('order_no='.$comment['order_no'].' AND status=0');
		$modelComment->setData(array(
			'point'        => 5,
			'contents'     => $param['content'],
			'status'       => 1,
			'comment_time' => ITime::getNow("Y-m-d"),
			'tag'          => $param['tag_id'],
			'image'        => implode(',', $data['image']),
			'voice'        => $data['voice'],
		));
		$rel = $modelComment->update('order_no='.$comment['order_no'].' AND status=0');
		if(!$rel) $this->returnJson(array('code' => '003008', 'msg' => $this->errorInfo['003008']));
		
		/* 更新商品信息 */
		$goods = array();
		foreach($listComment as $k => $v) $goods[] = $v['goods_id'];
		$modelGoods  = new IModel('goods');
		$modelGoods->setData(array(
			'comments' => 'comments + 1',
			'grade'    => 'grade + 5',
		));
		$modelGoods->update('id IN ('.explode(',',$goods).')', array('grade', 'comments'));
		
		$this->returnJson(array('code' => '0', 'msg' => 'ok'));
	}
	
	/**
	 * 评价标签列表
	 */
	public function comment_tag_list(){
		$param = $this->checkData(array());
		$query         = new IQuery('comment_tag');
		$query->where  = 'status=1';
		$query->fields = 'id,name';
		$query->order  = 'sort DESC';
		$query->limit  = 20;
		$list          = $query->find();
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => $list));
	}
	
	/**
	 * 猫粉说
	 */
	public function comment_list(){
		$param = $this->checkData(array(
			array('page', 'int', 0, '分页编号'),
		));
		$user_id = $this->tokenCheck();
		/* 获取数据 */
		$query           = new IQuery('comment as m');
		$query->join     = 'LEFT JOIN user AS u ON u.id=m.user_id';
		$query->where    = 'm.status=1 AND m.image!=""';
		$query->fields   = 'm.id,m.image,m.comment_time,u.username,u.head_ico';
		$query->order    = 'comment_time DESC';
		$query->group    = 'm.order_no';
		$query->page     = $param['page']<=0 ? 1 : $param['page'];
		$query->pagesize = 12;
		$list            = $query->find();
		if($param['page']>$query->getTotalPage()) $list = array();
		$model = new IModel('comment_praise');
		foreach($list as $k => $v){
			$image             = explode(',', $v['image']);
			$list[$k]['cover'] = empty($image) ? '' : IWeb::$app->config['image_host'].'/'.$image[0];
			$list[$k]['num']   = $model->get_count('comment_id='.$v['id']); //点赞数
			$list[$k]['praise']= $model->get_count('comment_id='.$v['id'].' AND user_id='.$user_id); //是否已点赞
		}
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => $list));
	}
	
	/**
	 * 评论详情
	 */
	public function comment_detail(){
		$param   = $this->checkData(array(
			array('comment_id', 'int', 1, '评论ID'),
		));
		$user_id = $this->tokenCheck();
		/* 评论详情 */
		$query         = new IQuery('comment as m');
		$query->join   = 'LEFT JOIN user AS u ON u.id=m.user_id LEFT JOIN goods AS g ON g.id=m.goods_id';
		$query->where  = 'm.status=1 AND m.id='.$param['comment_id'];
		$query->fields = 'm.id,m.goods_id,m.order_no,m.image,m.comment_time,u.username,u.head_ico,m.contents,g.name,g.img as goods_img,g.sell_price,g.jp_price';
		$query->limit  = 1;
		$list          = $query->find();
		if(empty($list)) $this->returnJson(array('code' => '010001', 'msg' => $this->errorInfo['010001'])); //评论不存在
		$list              = Api::run('goodsActivity', $list);
		$data              = $list[0];
		$model             = new IModel('comment_praise');
		$data['num']       = $model->get_count('comment_id='.$param['comment_id']); //点赞数
		$data['praise']    = $model->get_count('comment_id='.$param['comment_id'].' AND user_id='.$user_id); //是否已点赞
		$data['goods_img'] = empty($data['goods_img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$data['goods_img']."/w/500/h/500");
		$data['image']     = explode(',', $data['image']);
		foreach($data['image'] as $k => $v){
			$data['image'][$k] = empty($v) ? '' : IWeb::$app->config['image_host'].'/'.$v;
		}
		//点赞人
		$modelPraise       = new IModel('comment_praise as m,user as u');
		$data['user_list'] = $modelPraise->query('m.user_id=u.id AND comment_id='.$param['comment_id'], 'u.id,u.username,u.head_ico','m.id ASC',10);
		
		/* 人气猫粉说 */
		$queryComment         = new IQuery('comment as m');
		$queryComment->join   = 'LEFT JOIN user AS u ON u.id=m.user_id';
		$queryComment->where  = 'm.status=1 AND m.id!='.$param['comment_id'].' AND m.`image`!=""';
		$queryComment->fields = 'm.id,m.image,m.comment_time,u.username,u.head_ico';
		$queryComment->order  = 'rand()';
		$queryComment->group  = 'm.order_no';
		$queryComment->limit  = 6;
		$listComment          = $query->find();
		foreach($listComment as $k => $v){
			$image                     = explode(',', $v['image']);
			$listComment[$k]['cover']  = empty($image) ? '' : IWeb::$app->config['image_host'].'/'.$image[0];
			$listComment[$k]['num']    = $model->get_count('comment_id='.$v['id']); //点赞数
			$listComment[$k]['praise'] = $model->get_count('comment_id='.$v['id'].' AND user_id='.$user_id); //是否已点赞
		}
		$data['list'] = $listComment;
		
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => $data));
	}
	
	/**
	 * 评论点赞
	 */
	public function comment_praise(){
		$param   = $this->checkData(array(
			array('comment_id', 'int', 1, '评论ID'),
			array('opt', 'int', 1, '操作[1点赞-2取消]'),
		));
		$user_id = $this->tokenCheck();
		/* 检查 */
		if(!in_array($param['opt'],array(1,2))) $this->returnJson(array('code' => '001002', 'msg' => $this->errorInfo['001002']));
		$rel   = (new IModel('comment'))->get_count('id='.$param['comment_id']);
		if(empty($rel)) $this->returnJson(array('code' => '010001', 'msg' => $this->errorInfo['010001'])); //评论不存在
		$modelP = new IModel('comment_praise');
		$num    = $modelP->get_count('comment_id='.$param['comment_id'].' AND user_id='.$user_id);
		if($param['opt']==1 && $num<=0){
			$modelP->setData(array('comment_id' => $param['comment_id'], 'user_id' => $user_id,'create_time'=>time()));
			$rel = $modelP->add();
			if(!$rel) $this->returnJson(array('code' => '001005', 'msg' => $this->errorInfo['001005'])); //操作失败
		}else if($param['opt']==2 && $num>=1){
			$rel = $modelP->del('comment_id='.$param['comment_id'].' AND user_id='.$user_id);
			if(!$rel) $this->returnJson(array('code' => '001005', 'msg' => $this->errorInfo['001005'])); //操作失败
		}
		$this->returnJson(array('code' => '0', 'msg' => 'ok'));
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
		$query2->fields = "go.id as goods_id,go.is_del,p.name as pname,p.award_value, go.name,go.sell_price,go.purchase_price,go.img";
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
		$param = $this->checkData(array(
			array('page', 'int', 0, '分页编号'),
			array('aid', 'int', 0, '活动ID'),
			array('bid', 'string', 0, '品牌ID'),
			array('cid', 'string', 0, '分类ID'),
			array('did', 'int', 0, '推荐ID[1最新-2特价-3热卖-4推荐]'),
			array('tag', 'string', 0, '标签'),
		));
		$data  = Api::run('goodsList', $param);
		$this->returnJson(array('code' => 0, 'msg' => 'ok', 'data' => $data));
	}
	
	/**
	 * 商品详情
	 */
	public function goods_detail(){
		/* 获取参数 */
		$param   = $this->checkData(array(
			array('id', 'int', 1, '商品ID'),
			array('page', 'int', 0, '评论分页'),
		));
		$user_id = $this->tokenCheck();
		
		/* 商品详情 */
		$modelGoods = new IModel('goods');
		$fields     = 'id,name,goods_no,brand_id,sell_price,purchase_price,original_price,jp_price,market_price,store_nums,weight,img,content';
		$dataGoods  = $modelGoods->getObj('is_del=0 AND id='.$param['id'], $fields);
		if(empty($dataGoods)) $this->returnJson(array('code' => '006001', 'msg' => $this->errorInfo['006001'])); //商品不存在
		/* 计算活动商品价格 */
		$dataGoods             = Api::run('goodsActivity', $dataGoods);
		$dataGoods['discount'] = empty($dataGoods['original_price']) ? '' : round($dataGoods['sell_price']/$dataGoods['original_price'], 2)*10; //计算折扣率
		$dataGoods['img']      = IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$dataGoods['img']."/w/500/h/500");
		
		/* 相关货号 */
		$modelPro = new IModel('products');
		$products = $modelPro->query('goods_id='.$param['id'], 'id,products_no,spec_array,store_nums,sell_price,weight');
		if(!empty($products)){
			foreach($products as $k => $v){
				$products[$k]['spec_array'] = json_decode($v['spec_array']);
			}
		}
		$dataGoods['products'] = $products;
		
		/* 商品图 */
		$queryPhoto         = new IQuery('goods_photo_relation as g');
		$queryPhoto->join   = 'left join goods_photo as p on p.id=g.photo_id ';
		$queryPhoto->where  = ' g.goods_id='.$param['id'];
		$queryPhoto->fields = 'p.id AS photo_id,p.img ';
		$listPhoto          = $queryPhoto->find();
		foreach($listPhoto as $k => $v){
			$dataGoods['photo'][$k] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/600/h/600");
		}
		
		/* 相关评论 */
		$queryComment           = new IQuery('comment as m');
		$queryComment->join     = 'LEFT JOIN user as u ON u.id=m.user_id';
		$queryComment->where    = 'status=1 AND goods_id='.$param['id'];
		$queryComment->fields   = 'm.id,m.contents,m.recontents,m.recomment_time,m.tag,m.image,m.voice,m.user_id,u.username,u.head_ico';
		$queryComment->page     = $param['page']<=0 ? 1 : $param['page'];
		$queryComment->pagesize = 10;
		$commetList             = $queryComment->find();
		if($param['page']>$queryComment->getTotalPage()) $commetList = array();
		if(!empty($commetList)){
			$modelTag = new IModel('comment_tag');
			foreach($commetList as $k => $v){
				//语音
				$commetList[$k]['voice'] = empty($v['voice']) ? '' : IWeb::$app->config['image_host'].'/'.$v['voice'];
				//评论图片
				$image = explode(',', $v['image']);
				if(!empty($image)){
					foreach($image as $k1 => $v1) $image[$k1] = empty($v1) ? '' : IWeb::$app->config['image_host'].'/'.$v1;
				}
				$commetList[$k]['image'] = $image;
				//标签
				$tag     = array();
				$listTag = $modelTag->query('id IN ('.$v['tag'].') AND status=1', 'name');
				foreach($listTag as $k1 => $v1) $tag[] = $v1['name'];
				$commetList[$k]['tag'] = $tag;
			}
		}
		$dataGoods['comment_list'] = $commetList;
		
		/* 相关专辑 */
		$queryArt                  = new IQuery('article as m');
		$queryArt->join            = 'left join relation as r on r.article_id = m.id';
		$queryArt->where           = 'm.top=0 and m.visibility=1 and r.goods_id='.$param['id'];
		$queryArt->order           = 'm.sort desc';
		$queryArt->fields          = 'm.id,m.title,m.image';
		$queryArt->group           = 'm.id';
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
			$queryGoods->fields         = 'id,name,sell_price,purchase_price,img';
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
		$checkStr = "#".$param['id']."#";
		if($visit && strpos($visit, $checkStr)!==false){
		}else{
			$modelGoods->setData(array('visit' => 'visit + 1'));
			$modelGoods->update('id = '.$param['id'], 'visit');
			$visit = $visit===null ? $checkStr : $visit.$checkStr;
			ISafe::set('visit', $visit);
		}
		
		/* 相关推荐商品 */
		$dataGoods['related_list'] = array();
		$modelCat                  = new IModel('category_extend');
		$infoCat                   = $modelCat->getObj('goods_id='.$param['id']);
		if(!empty($infoCat)){
			$queryCat         = new IQuery('category');
			$queryCat->where  = 'id!='.$infoCat['category_id'].' AND parent_id=(SELECT `parent_id` From category WHERE `id`='.$infoCat['category_id'].' limit 1)';
			$queryCat->fields = 'id';
			$listCat          = $queryCat->find();
			$cids             = array();
			foreach($listCat as $k => $v)
				$cids[] = $v['id'];
			$queryGoods                = new IQuery('goods AS m');
			$queryGoods->join          = 'LEFT JOIN category_extend AS c ON c.goods_id=m.id';
			$queryGoods->where         = 'm.is_del=0 AND c.category_id IN ('.implode(',', $cids).')';
			$queryGoods->fields        = 'm.id,m.name,m.sell_price,m.market_price,m.img';
			$queryGoods->order         = 'm.sale DESC,m.visit DESC';
			$queryGoods->group         = 'm.id';
			$queryGoods->limit         = 10;
			$dataGoods['related_list'] = $queryGoods->find();
			if(!empty($dataGoods['related_list'])){
				$dataGoods['related_list'] = Api::run('goodsActivity', $dataGoods['related_list']);
				foreach($dataGoods['related_list'] as $k => $v){
					$dataGoods['related_list'][$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$v['img'].'/w/500/h/500');
				}
			}
		}
		
		/* 是否参与限时活动（限时购） */
		$querySpeed         = new IQuery('activity_speed as m');
		$querySpeed->join   = 'LEFT JOIN activity_speed_access AS a ON a.pid=m.id';
		$querySpeed->where  = 'm.type=1 AND a.goods_id='.$param['id'].' AND m.start_time<='.time().' AND m.end_time>='.time().' AND status=1';
		$querySpeed->fields = 'a.id,a.goods_id,a.sell_price,a.nums,a.quota,a.delivery,m.type,m.start_time,m.end_time';
		$listSpeed          = $querySpeed->find();
		$dataGoods['speed'] = empty($listSpeed) ? array() : array('start_time' => $listSpeed[0]['start_time'], 'end_time' => $listSpeed[0]['end_time']);
		
		/* 记录用户操作 */
		
		/* 返回参数 */
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => $dataGoods));
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
		$queryGoods->fields   = 'm.id,m.name,m.sell_price,m.purchase_price,m.img,m.market_price,m.jp_price';
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
		$brands_query->fields = "b.id,b.name,b.img,b.sell_price,b.purchase_price,b.brand_id";
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
		$commend_goods->fields = 'go.img,go.sell_price,go.purchase_price,go.name,go.id,go.market_price';
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
		$param = $this->checkData(array(
			array('cid', 'int', 0, '专辑分类ID[3视频特辑]'),
			array('page', 'int', 0, '分页编号'),
		));
		/* 获取数据 */
		$query           = new IQuery('article as m');
		$query->join     = 'left join article_category as c on c.id=m.category_id';
		$query->where    = 'm.visibility=1 '.(empty($param['cid']) ? ' AND m.category_id NOT IN (3)' : ' AND m.category_id='.$param['cid']);
		$query->fields   = 'm.id,m.title,m.image,m.visit_num,m.category_id,c.icon,c.name as category_name';
		$query->order    = 'm.sort desc,m.id desc';
		$query->group    = 'm.id';
		$query->page     = $param['page']>1 ? $param['page'] : 1;
		$query->pagesize = 5;
		$list            = $query->find();
		if($param['page']>$query->getTotalPage()) $list = array();
		if(!empty($list)){
			//商品列表模型
			$queryGoods         = new IQuery('goods as m');
			$queryGoods->join   = 'left join relation as r on r.goods_id=m.id';
			$queryGoods->fields = 'm.id,m.name,m.sell_price,m.purchase_price,m.img';
			$queryGoods->order  = 'sale DESC,visit DESC';
			$queryGoods->group  = 'm.id';
			$queryGoods->limit  = 5;
			//商品统计模型
			$queryGoodsCount         = new IQuery('goods as m');
			$queryGoodsCount->join   = 'left join relation as r on r.goods_id=m.id';
			$queryGoodsCount->fields = 'count(m.id) as num';
			$queryGoodsCount->group  = 'm.id';
			//专辑收藏模型
			$queryFavorite         = new IQuery('favorite_article');
			$queryFavorite->fields = 'count(id) as num';
			//收藏人数
			foreach($list as $k => $v){
				$list[$k]['icon']  = empty($v['icon']) ? '' : IWeb::$app->config['image_host'].'/'.$v['icon'];
				$list[$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/750/h/380");
				//收藏人数
				$queryFavorite->where    = 'aid='.$v['id'];
				$count                    = $queryFavorite->find();
				$list[$k]['favorite_num'] = $count[0]['num'];
				//当前用户是否已收藏
				if(!empty($this->user['user_id'])){
					$queryFavorite->where = 'aid='.$v['id'].' and user_id='.$this->user['user_id'];
					$count                 = $queryFavorite->find();
					$count                 = $count[0]['num'];
				}else{
					$count = 0;
				}
				$list[$k]['is_favorite'] = $count;
				//相关商品数量
				$queryGoodsCount->where = 'm.is_del=0 and r.article_id='.$v['id'];
				$count                    = $queryGoodsCount->find();
				$list[$k]['goods_num']    = $count[0]['num'];
				//相关商品列表
				$queryGoods->where = 'm.is_del=0 and r.article_id='.$v['id'];
				$list[$k]['list']   = $queryGoods->find();
				if(!empty($list[$k]['list'])){
					/* 计算活动商品价格 */
					$list[$k]['list'] = api::run('goodsActivity', $list[$k]['list']);
					foreach($list[$k]['list'] as $k1 => $v1){
						$list[$k]['list'][$k1]['img'] = empty($v1['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/180/h/180");
					}
				}
			}
		}
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$list));
	}
	
	/**
	 * 专辑分类列表 TODO 待废弃
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
		$article->filds    = 'go.goods_no as goods_no,go.id as goods_id,go.img,go.name,go.sell_price,go.purchase_price';
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
	//---------------------------------------------------视频---------------------------------------------------
	/**
	 * 视频列表
	 */
	public function video_list(){
		/* 获取参数 */
		$param   = $this->checkData(array(
			array('cid', 'int', 0, '视频分类ID'),
			array('page', 'int', 0, '分页编号'),
		));
		$user_id = $this->tokenCheck();
		/* 分类列表 */
		$modelCat = new IModel('video_category');
		$listCat  = $modelCat->query('status=1', 'id,name', 'sort DESC');
		/* 视频列表 */
		$query           = new IQuery('video');
		$query->where    = 'status=1'.(empty($param['cid']) ? '' : ' AND cat_id='.$param['cid']);
		$query->fields   = 'id,url,cat_id,title,hits,img';
		$query->order    = 'id DESC';
		$query->page     = $param['page']<=0 ? 1 : $param['page'];
		$query->pagesize = 10;
		$list            = $query->find();
		if($param['page']>$query->getTotalPage()) $list = array();
		$model = new IModel('video_collect');
		foreach($list as $k => $v){
			$list[$k]['img']        = empty($v['img']) ? '' : IWeb::$app->config['image_host'].'/'.$v['img'];
			$list[$k]['collect']    = $model->get_count('video_id='.$v['id']); //收藏人数
			$list[$k]['is_collect'] = $model->get_count('video_id='.$v['id'].' AND user_id='.$user_id); //是否以收藏
		}
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => array('video_list' => $list, 'cat_list' => $listCat)));
	}
	
	/**
	 * 视频详情
	 */
	public function video_datail(){
		/* 获取参数 */
		$param   = $this->checkData(array(
			array('video_id', 'int', 1, '视频ID'),
		));
		$user_id = $this->tokenCheck();
		/* 获取视频详情 */
		$modelVideo = new IModel('video');
		$info       = $modelVideo->getObj('status=1 AND id='.$param['video_id'], 'id,url,hits,img,title,content,goods');
		if(empty($info)) $this->returnJson(array('code' => '011001', 'msg' => $this->errorInfo['011001']));
		$info['img'] 	    = empty($info['img']) ? '' : IWeb::$app->config['image_host'].'/'.$info['img'];
		//收藏
		$modelCollect       = new IModel('video_collect');
		$info['collect']    = $modelCollect->get_count('video_id='.$param['video_id']);
		$info['is_collect'] = $modelCollect->get_count('video_id='.$param['video_id'].' AND user_id='.$user_id);
		
		//相关商品
		if(!empty($info['goods'])){
			$queryGoods         = new IQuery('goods AS m');
			$queryGoods->join   = 'LEFT JOIN category_extend AS c ON c.goods_id=m.id';
			$queryGoods->where  = 'm.is_del=0 AND m.id IN ('.$info['goods'].')';
			$queryGoods->fields = 'm.id,m.name,m.img,m.sell_price,m.jp_price,c.category_id';
			$queryGoods->order  = 'm.sale DESC,m.visit DESC';
			$info['goods_list'] = $queryGoods->find();
			
			if(!empty($info['goods_list'])){
				$info['goods_list'] = Api::run('goodsActivity',$info['goods_list']);
				//搭配商品
				$cids = array();
				foreach($info['goods_list'] as $k => $v){
					$info['goods_list'][$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
					if(!empty($v['category_id'])) $cids[] = $v['category_id'];
				}
				//同类商品
				$queryGoods->where    = 'm.is_del=0 AND c.category_id IN ('.implode(',', array_unique($cids)).')';
				$queryGoods->limit    = 10;
				$info['related_list'] = $queryGoods->find();
				if(!empty($info['related_list'])){
					$info['goods_list'] = Api::run('goodsActivity',$info['goods_list']);
					foreach($info['related_list'] as $k => $v){
						$info['related_list'][$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
					}
				}
			}
		}
		
		/* 观看次数增加 */
		$modelVideo->setData(array('hits' => 'hits+1'));
		$modelVideo->update('id='.$param['video_id'], array('hits'));
		
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => $info));
	}
	
	/**
	 * 视频收藏
	 */
	public function video_collect(){
		/* 获取参数 */
		$param   = $this->checkData(array(
			array('video_id', 'int', 1, '视频ID'),
			array('opt', 'int', 1, '操作[1收藏-2取消]'),
		));
		$user_id = $this->tokenCheck();
		
		/* 检查 */
		if(!in_array($param['opt'],array(1,2))) $this->returnJson(array('code' => '001002', 'msg' => $this->errorInfo['001002']));
		$model = new IModel('video');
		$rel   = $model->get_count('id='.$param['video_id']);
		if(empty($rel)) $this->returnJson(array('code' => '011001', 'msg' => $this->errorInfo['011001'])); //视频不存在
		$modelColl = new IModel('video_collect');
		$num       = $modelColl->get_count('video_id='.$param['video_id'].' AND user_id='.$user_id);
		if($param['opt']==1 && $num<=0){
			$modelColl->setData(array('video_id' => $param['video_id'], 'user_id' => $user_id,'create_time'=>time()));
			$rel = $modelColl->add();
			if(!$rel) $this->returnJson(array('code' => '001005', 'msg' => $this->errorInfo['001005'])); //操作失败
		}else if($param['opt']==2 && $num>=1){
			$rel = $modelColl->del('video_id='.$param['video_id'].' AND user_id='.$user_id);
			if(!$rel) $this->returnJson(array('code' => '001005', 'msg' => $this->errorInfo['001005'])); //操作失败
		}
		$this->returnJson(array('code' => '0', 'msg' => 'ok'));
	}
	
	//---------------------------------------------------场景馆---------------------------------------------------
	/**
	 * 场景馆列表
	 */
	public function scene_list(){
		/* 获取参数 */
		$param   = $this->checkData(array(
			array('page', 'int', 0, '分页编号'),
		));
		$user_id = $this->tokenCheck();
		/* 获取数据 */
		$query           = new IQuery('scene');
		$query->where    = 'status=1';
		$query->fields   = 'id,title,cover,visit';
		$query->order    = 'sort DESC,id DESC';
		$query->page     = $param['page']<=0 ? 1 : $param['page'];
		$query->pagesize = 10;
		$list            = $query->find();
		if($param['page']>$query->getTotalPage()) $list = array();
		$modelPar           = new IModel('scene_praise');
		foreach($list as $k => $v){
			$list[$k]['cover']  = empty($v['cover']) ? '' : IWeb::$app->config['image_host'].'/'.$v['cover'];
			$list[$k]['praise'] = $modelPar->get_count('type=1 AND scene_id='.$v['id']);
			$list[$k]['is_praise'] = $modelPar->get_count('type=1 AND scene_id='.$v['id'].' AND user_id='.$user_id);
		}
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$list));
	}
	
	/**
	 * 场景馆详情
	 */
	public function scene_detail(){
		/* 获取参数 */
		$param   = $this->checkData(array(
			array('scene_id', 'int', 1, '场景馆ID'),
		));
		$user_id = $this->tokenCheck();
		/* 获取数据 */
		$modelScene = new IModel('scene as m,user as u');
		$info       = $modelScene->getObj('u.id=m.user_id AND status=1 AND m.id='.$param['scene_id'], 'm.id,m.title,m.content,m.img,m.visit,u.username,u.head_ico');
		if(empty($info)) $this->returnJson(array('code' => '012001', 'msg' => $this->errorInfo['012001']));
		$info['img'] = empty($info['img']) ? '' : IWeb::$app->config['image_host'].'/'.$info['img'];
		//喜欢/没兴趣
		$modelPra     = new IModel('scene_praise');
		$info['good'] = $modelPra->get_count('type=1 AND scene_id='.$param['scene_id']); //喜欢
		$info['bad']  = $modelPra->get_count('type=2 AND scene_id='.$param['scene_id']); //没兴趣
		//相关商品
		$queryGoods         = new IQuery('scene_goods as m');
		$queryGoods->join   = 'LEFT JOIN goods as g ON g.id=m.goods_id';
		$queryGoods->where  = 'g.is_del=0 AND m.scene_id='.$info['id'];
		$queryGoods->fields = 'g.id,g.name,g.goods_no,g.sell_price,g.store_nums,g.jp_price,g.img,m.coord_x,m.coord_y';
		$info['goods_list'] = $queryGoods->find();
		if(!empty($info['goods_list'])){
			$info['goods_list'] = Api::run('goodsActivity', $info['goods_list']);
			$modelFor           = new IModel('favorite');
			foreach($info['goods_list'] as $k => $v){
				$count                         = $modelFor->get_count('user_id='.$user_id.' AND rid='.$v['id']);
				$dataGoods['is_favorite']      = !empty($count) ? 1 : 0; //是否已收藏
				$info['goods_list'][$k]['img'] = empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/500/h/500");
			}
		}
		//其他场景馆
		$query         = new IQuery('scene');
		$query->where  = 'status=1 AND id!='.$param['scene_id'];
		$query->fields = 'id,title,cover,visit';
		$query->order  = 'rand()';
		$query->limit  = 5;
		$info['list']  = $query->find();
		foreach($info['list'] as $k => $v){
			$info['list'][$k]['cover'] = empty($v['cover']) ? '' : IWeb::$app->config['image_host'].'/'.$v['cover'];
		}
		
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => $info));
	}
	/**
	 * 场景馆点赞
	 */
	public function scene_praise(){
		/* 获取参数 */
		$param   = $this->checkData(array(
			array('scene_id', 'int', 1, '场景馆ID'),
			array('opt', 'int', 1, '操作[1喜欢-2没感觉]'),
		));
		$user_id = $this->tokenCheck();
		
		/* 检查 */
		if(!in_array($param['opt'],array(1,2))) $this->returnJson(array('code' => '001002', 'msg' => $this->errorInfo['001002']));
		$rel   = (new IModel('scene'))->get_count('id='.$param['scene_id']);
		if(empty($rel)) $this->returnJson(array('code' => '012001', 'msg' => $this->errorInfo['012001'])); //场景馆不存在
		$modelColl = new IModel('scene_praise');
		$num       = $modelColl->get_count('scene_id='.$param['scene_id'].' AND user_id='.$user_id.' AND type='.$param['opt']);
		if($num>=1) $this->returnJson(array('code' => '012002', 'msg' => $this->errorInfo['012002'])); //重复操作
		//进行操作
		$modelColl->setData(array('scene_id' => $param['scene_id'], 'user_id' => $user_id,'type'=>$param['opt']));
		$rel = $modelColl->add();
		if(!$rel) $this->returnJson(array('code' => '001005', 'msg' => $this->errorInfo['001005'])); //操作失败
		
		$this->returnJson(array('code' => '0', 'msg' => 'ok','data'=>$modelColl->get_count('scene_id='.$param['scene_id'].' AND type='.$param['opt'])));
	}
	
	//---------------------------------------------------分类---------------------------------------------------
	
	/**
	 *一级分类的数据信息 TODO 待废弃
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
	 * 获取其子类数据信息 //TODO 待废弃
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
			$fields = 'm.id,m.name,m.sell_price,m.purchase_price,m.jp_price,m.market_price,m.img';
			$order  = 'c.rank ASC';
		}else{
			//通常进入
			$join   = 'LEFT JOIN category_extend AS c ON c.goods_id=m.id';
			$where  = 'm.is_del=0 AND c.category_id in ('.$catId.')'.(empty($bid) ? '' : ' AND m.brand_id='.$bid);
			$fields = 'm.id,m.name,m.sell_price,m.purchase_price,m.jp_price,m.market_price,m.img';
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
		$param = $this->checkData(array(
			array('cat_id','int',0,'分类ID'),
		));
		/* 获取数据 */
		$data = $this->get_cat($param['cat_id']);
		/* 数据返回 */
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$data));
	}
	function get_cat($pid){
		$queryCat         = new IQuery('category');
		$queryCat->where  = 'parent_id='.$pid;
		$queryCat->fields = 'id,parent_id,name,image';
		$queryCat->order  = 'sort DESC';
		$listCat          = $queryCat->find();
		if(!empty($listCat)){
			foreach($listCat as $k => $v){
				$listCat[$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].'/'.$v['image'];
				$listCat[$k]['list'] = $this->get_cat($v['id']);
			}
		}
		return $listCat;
	}
	
	
	/**
	 * ---------------------------------------------------品牌---------------------------------------------------*
	 */
	/**
	 * 品牌列表
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
		$param = $this->checkData(array(
			array('id','int',1,'品牌ID'),
			array('page','int',0,'分页编号'),
		));
		
		/* 品牌详情 */
		$queryBrand         = new IQuery('brand');
		$queryBrand->where  = 'id='.$param['id'];
		$queryBrand->fields = 'id,name,logo,description,banner';
		$queryBrand->limit  = 1;
		$data               = $queryBrand->find();
		if(empty($data)) $this->returnJson(array('code' => '009001','msg'=>$this->errorInfo['009001']));
		$data           = $data[0];
		$data['logo']   = empty($data['logo']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$data['logo'].'/w/160/h/102');
		$data['banner'] = empty($data['banner']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$data['banner'].'/w/750/h/376');
		
		/* 相关商品 */
		$queryGoods           = new IQuery('goods');
		$queryGoods->where    = 'is_del=0 AND brand_id='.$param['id'];
		$queryGoods->fields   = 'id,name,img,content,sell_price,purchase_price,jp_price';
		$queryGoods->page     = $param['page']<1 ? 1 : $param['page'];
		$queryGoods->pagesize = 10;
		$queryGoods->order    = 'sort asc';
		$dataGoods            = $queryGoods->find();
		if($param['page']>$queryGoods->getTotalPage()) $dataGoods = array();
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
		$queryGoodsSum->where  = 'is_del=0 AND brand_id='.$param['id'];
		$dataGoodsSum          = $queryGoodsSum->find();
		
		/* 相关专辑 */
		$queryArticle         = new IQuery('article AS m');
		$queryArticle->join   = 'LEFT JOIN relation AS r ON r.article_id=m.id LEFT JOIN goods AS g ON g.id=r.goods_id';
		$queryArticle->where  = 'g.is_del=0 AND m.visibility=1 AND g.brand_id='.$param['id'];
		$queryArticle->fields = 'm.id,m.title,m.visit_num,m.image';
		$queryArticle->limit  = 5;
		$queryArticle->order  = 'm.top desc,m.sort desc';
		$queryArticle->group = 'm.id';
		$dataArticle          = $queryArticle->find();
		if(!empty($dataArticle)){
			foreach($dataArticle as $k => $v){
				$dataArticle[$k]['image'] = empty($v['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$v['image'].'/w/750/h/380');
			}
		}
		//相关专辑数量
		$queryArticleSum         = new IQuery('article AS m');
		$queryArticleSum->join   = 'LEFT JOIN relation AS r ON r.article_id=m.id LEFT JOIN goods AS g ON g.id=r.goods_id';
		$queryArticleSum->where  = 'g.is_del=0 AND m.visibility=1 AND g.brand_id='.$param['id'];
		$queryArticleSum->fields = 'count(*) as sum';
		$dataArticleSum          = $queryArticleSum->find();
		
		/* 返回参数 */
		$data['goods_sum']    = $dataGoodsSum[0]['sum'];
		$data['article_sum']  = $dataArticleSum[0]['sum'];
		$data['goods_list']   = $dataGoods;
		$data['article_list'] = $dataArticle;
		$this->returnJson(array('code'=>'0','msg'=>'ok','data'=>$data));
	}
	
	/**
	 * 品牌列表首页
	 */
	public function brand_list_index(){
		/* 接收参数 */
		$param = $this->checkData(array());
		/* 品牌榜 */
		$ids              = array(1, 2, 3, 4, 5, 6); //1药妆-5零食-3宠物-7母婴-6生活
		$queryCat         = new IQuery('brand_category');
		$queryCat->where  = 'id IN ('.implode(',', $ids).')';
		$queryCat->fields = 'id,name,img';
		$listCat          = $queryCat->find();
		if(!empty($listCat)){
			$queryBrand         = new IQuery('brand');
			$queryBrand->fields = 'id,name,img';
			$queryBrand->order  = 'sort DESC';
			$queryBrand->limit  = 9;
			foreach($listCat as $k => $v){
				$queryBrand->where   = 'top=1 AND img IS NOT NULL AND category_ids LIKE "%,'.$v['id'].',%"';
				$listCat[$k]['list'] = $queryBrand->find();
				if(!empty($listCat[$k]['list'])){
					foreach($listCat[$k]['list'] as $k1 => $v1){
						$listCat[$k]['list'][$k1]['img']  = empty($v1['img']) ? '' : IWeb::$app->config['image_host'].'/'.$v1['img'];
					}
				}
				$listCat[$k]['img']  = empty($v['img']) ? '' : IWeb::$app->config['image_host'].'/'.$v['img'];
			}
		}
		/* 全部品牌 */
		$queryCat         = new IQuery('brand_category');
		$queryCat->where  = '';
		$queryCat->fields = 'id,name';
		$listAll          = $queryCat->find();
		if(!empty($listAll)){
			$queryBrand         = new IQuery('brand');
			$queryBrand->fields = 'id,name,logo';
			$queryBrand->order  = 'sort DESC';
			$queryBrand->limit  = 1000;
			foreach($listAll as $k => $v){
				$queryBrand->where   = 'logo IS NOT NULL AND category_ids LIKE "%,'.$v['id'].',%"';
				$listAll[$k]['list'] = $queryBrand->find();
				if(empty($listAll[$k]['list'])){
					unset($listAll[$k]);
					continue;
				}
				foreach($listAll[$k]['list'] as $k1 => $v1){
					$listAll[$k]['list'][$k1]['logo'] = IWeb::$app->config['image_host'].'/'.$v1['logo'];
				}
			}
		}
		/* 返回参数 */
		$this->returnJson(array('code' => '0', 'msg' => 'ok', 'data' => array(
			'cat' => $listCat,
			'all' => $listAll,
		)));
	}
	/**
	 *
	 */
	
	
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
		$field       = 'id,name,sell_price,purchase_price,jp_price,market_price,img';
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
	 */ // TODO 待废弃
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
		$query->fields = 'g.id,g.name,g.sell_price,g.purchase_price,g.img';
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
		$favorite_query->fields = 'a.*,go.id,go.name,go.sell_price,go.purchase_price,go.market_price,go.img,go.jp_price';
		
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

    /**
     * User: chenbo
     * 返回json
     * @param $data
     */
	private function json_echo($data){
		echo json_encode($data);
		exit();
	}

    /**
     * User: chenbo
     * 订单颜色标注
     */
	function color_status(){
        $order_id = IFilter::act(IReq::get('id'), 'int');
        $color_status = IFilter::act(IReq::get('color_status'), 'int');
        $order_model = new IModel('order');
        $order_model->setData(['color_status'=>$color_status]);
        $ret = $order_model->update('id = ' . $order_id);
        if ($ret){
            $this->json_echo(['ret'=>true,'msg'=>'标注成功']);
        } else {
            $this->json_echo(['ret'=>false,'msg'=>$order_id.'-'.$color_status]);
        }

    }

    /**
     * User: chenbo
     * 消息的推送
     * @return string
     */
    function send_wechat_message_by_type(){
        $id                  = IFilter::act(IReq::get('id'), 'int');
        $type                = IFilter::act(IReq::get('type'), 'string');
        $order_id            = IFilter::act(IReq::get('order_id'), 'int');
        $wechat_access_token = common::get_wechat_access_token();
        if (empty($wechat_access_token)) die(json_encode(['ret'=>false,'msg'=>'无法获取wechat access_token']));
        $oauth_user_model = new IModel('oauth_user');
        $data             = $oauth_user_model->getObj("user_id = $id");
        if (empty($data)) die(json_encode(['ret'=>false,'msg'=>'不存在该用户的openid']));
        $oauth_user_id = $data['oauth_user_id'];
        switch ($type){
            case 'shiming': //实名信息的推送
                $order_model = new IModel('order');
                $ret         = $order_model->getObj('id = ' . $order_id);
                if (!empty($ret)){
                    $order_no = $ret['order_no'];
                } else {
                    echo json_decode(['ret'=>false,'msg'=>'不存在该订单'.$order_id]);
                    return;
                }
                $wechat_ret = wechats::send_message_template($oauth_user_id, 'shiming', ['order_no' => $order_no]);
                break;
        }
        if ($wechat_ret){
            $ret = json_encode(['ret'=>true,'msg'=>$oauth_user_id]);
            echo $ret;
        } else {
            $ret = json_encode(['ret'=>false,'msg'=>'开发人员查看日志错误信息']);
            echo $ret;
        }
        return;
    }

    /**
     * User: chenbo
     * 将用户实名信息复制到收货信息
     */
    function sfz_to_address_image(){
        $id            = IFilter::act(IReq::get('id'), 'int');
        $data          = common::get_user_data($id);
        $address_model = new IModel('address');
        $address_model->setData(['sfz_image1'=>$data['sfz_image1'],'sfz_image2'=>$data['sfz_image2']]);
        $ret = $address_model->update('user_id = ' . $id . ' and accept_name="' . $data['sfz_name'] . '"');
        if ($ret){
            die(json_encode(['ret'=>true]));
        } else {
            die(json_encode(['ret'=>false]));
        }
    }

    /**
     * User: chenbo
     * 恢复照片
     */
    function restore_image(){
        $id          = IFilter::act(IReq::get('user_id'), 'int');
        $accept_name = IFilter::act(IReq::get('accept_name'), 'string');
        $order_no    = IFilter::act(IReq::get('order_no'), 'int');
        if (!empty($order_no)){
            if ($data = common::get_order_data(null,$order_no)){
                $sfz_image1 = $data['sfz_image1'];
                $sfz_image2 = $data['sfz_image2'];
                $msg = common::restore_wechat_resources($sfz_image1);
                $msg .= common::restore_wechat_resources($sfz_image2);
                die(json_encode(['ret'=>true,'msg'=>$msg]));
            } else {
                die(json_encode(['ret'=>false,'msg'=>$msg]));
            }
        }
        if (!empty($accept_name)){
            $where = "user_id = $id and accept_name = '$accept_name'";
            if ($data = common::get_address_data($where)){
                $sfz_image1 = $data['sfz_image1'];
                $sfz_image2 = $data['sfz_image2'];
                $msg = common::restore_wechat_resources($sfz_image1);
                $msg .= common::restore_wechat_resources($sfz_image2);
                die(json_encode(['ret'=>true,'msg'=>$msg]));
            } else {
                die(json_encode(['ret'=>false,'msg'=>$msg]));
            }
        }
        if (!empty($id)){
            if ($data = common::get_user_data($id)){
                $sfz_image1 = $data['sfz_image1'];
                $sfz_image2 = $data['sfz_image2'];
                $msg = common::restore_wechat_resources($sfz_image1);
                $msg .= common::restore_wechat_resources($sfz_image2);
                die(json_encode(['ret'=>true,'msg'=>$msg]));
            } else {
                die(json_encode(['ret'=>false,'msg'=>$msg]));
            }
        }
    }

    /**
     * User: chenbo
     * 用户获取分享成功所得的优惠券
     */
    function get_share_ticket(){
        $sponsor  = IFilter::act(IReq::get('sponsor'));
        $friends  = IFilter::act(IReq::get('friends'));
        $share_no = IFilter::act(IReq::get('share_no'));
        $user_id  = $this->user['user_id'];
        if ($sponsor === 'false'){ //普通用户
            if ($friends === 'true'){ //已经关注
                $ret     = ticket::if_exsites_ticket($user_id, $share_no);
                if ($ret) $this->json_echo(['ret'=>true,'msg'=>'你已经领取过了<br>《个人中心》->《我的优惠券》<br>中查看优惠券']);
                $open_id   = common::get_wechat_open_id($user_id);
                $user_data = common::get_user_data($user_id);
                $ret       = ticket::create_ticket($user_id, 'share',$share_no);
                if ($ret){
                    wechats::send_message_template($open_id,'receive',['ticket_name'=>'满288抵扣优惠券','username'=>$user_data['username']]);
                    $this->json_echo(['ret'=>true,'msg'=>'《个人中心》->《我的优惠券》<br>中查看优惠券']);
                } else {
                    common::log_write("$user_id 优惠券生成失败,ticket_id:$ticket_id,from:$from");
                    $this->json_echo(['ret'=>false,'msg'=>'优惠券生成失败']);
                }
            } else {
                $this->json_echo(['ret'=>false,'msg'=>'扫码关注领取']);
            }
        }
        $user_id             = $this->user['user_id'];
        $user_data           = common::get_user_data($user_id);
        $open_id             = common::get_wechat_open_id($user_id);
        //好友领取记录
        $activity_ticket_access_query        = new IQuery('activity_ticket_access');
        $activity_ticket_access_query->where = "remark = '$share_no'";
        $data                                = $activity_ticket_access_query->find();
        $num                                 = count($data);
        common::log_write($activity_ticket_access_query->getSql(),'ERROR');
//        if($user_id==='24'){$num=5;}
        if ($num > 4){
//            赠送优惠券
            $ret = ticket::create_ticket($user_id, 'share', $share_no);
            if ($ret){
                wechats::send_message_template($open_id,'receive',['ticket_name'=>'满288抵扣优惠券','username'=>$user_data['username']]);
                $this->json_echo(['ret'=>true,'msg'=>'《个人中心》->《我的优惠券》<br>中查看优惠券']);
            } else {
                common::log_write("$user_id 优惠券生成失败,ticket_id:$ticket_id,from:$from");
                $this->json_echo(['ret'=>false,'msg'=>'优惠券生成失败']);
            }
        } else {
            $num2 = 5-$num;
            $this->json_echo(['ret'=>false,'msg'=>"您无法领取礼品优惠券<br>$num 位好友领取你的红包成功<br>还需要$num2 位好友分享获得红包"]);
        }
    }

    /**
     * User: chenbo
     * 获取物流信息
     * @return string
     */
    function get_logistic_info(){
        $order_no            = IFilter::act(IReq::get('order_no'));
        $order_query         = new IQuery('order as a');
        $order_query->join   = 'left join delivery_doc as b on a.id = b.order_id';
        $order_query->fields = 'a.*,b.delivery_code,b.delivery_type';
        $order_query->where  = "order_no = $order_no";
        $data                = $order_query->find();
        if (empty($data)){
            common::log_write($order_query->getSql(), 'ERROR','logistic');
            $this->returnJson(array('code' => '-1', 'msg' => '订单未发货', 'data' => null));
        }
        xlobo::init();
        $data           = xlobo::get_logistic_info([$data[0]['delivery_code']])[0];
        $billCode       = $data->BillCode;
        $businessNo     = $data->BusinessNo;
        $billStatusList = $data->BillStatusList;
        $ret            = array_map(function ($v) {
            return (array)$v;
        }, $billStatusList);
        $this->returnJson(array('code' => '0', 'msg' => '查询物流信息成功', 'data' => ['type' => 'xlobo', 'name'=>'贝海国际物流', 'order_no' => $order_no, 'data' => $ret]));
    }

    /**
     * User: chenbo
     * 三天后过期提醒
     */
    function tip_coupon_expires(){
        set_time_limit(0);
        $activity_ticket_access_query = new IQuery('activity_ticket_access as a');
        $activity_ticket_access_query->fields = 'a.*, b.`name`,b.end_time,b.time_type,b.day,c.oauth_user_id';
        $activity_ticket_access_query->join = "left join activity_ticket AS b ON a.ticket_id = b.id left join oauth_user AS c ON a.user_id = c.user_id";
        $activity_ticket_access_query->where = "(
		time_type = 2
		AND ADDDATE(
			FROM_UNIXTIME(a.create_time, '%Y-%m-%d'),
			b.`day`
		) = CURDATE()
	)
OR (
	NOW() < FROM_UNIXTIME(b.end_time)
	AND NOW() > FROM_UNIXTIME(b.start_time)
	AND time_type = 1
	AND DATEDIFF(
		CURDATE(),
		FROM_UNIXTIME(b.end_time, '%Y-%m-%d')
	) = 3
)";
        $data = $activity_ticket_access_query->find();
        $i = 0;
        foreach ($data as $v) {
            if ($v['time_type'] === '1'){
                $end_time = date('Y-m-d H:i:s', $v['end_time']);
            } elseif ($v['time_type'] === '2'){
//                $end_time = date('Y-m-d H:i:s', $v['end_time']);
                $end_time = date('Y-m-d H:i:s', strtotime("+ ".$v['day']." day", $v['create_time']));
            }
            $oauth_user_id = $v['oauth_user_id'];
            $oauth_user_id = 'orEYdw0X44crd6F3MOdXES6Hfpig';
            $ret = wechats::send_message_template($oauth_user_id, 'tip_coupon_expires', ['coupon_name'=>$v['name'], 'end_time'=>$end_time]);
            if ($ret){
                $i +=1;
                continue;
            } else{
                $v[] = __FUNCTION__;
                common::log_write(print_r($v), 'ERROR', 'wechat');
                return;
            }
        }
        common::print_b($i);
        return;
    }

    /**
     * User: chenbo
     * 所有会员提醒加个人号
     */
    function all_member_message(){
        set_time_limit(0);
        $user_query        = new IQuery('user as a');
        $user_query->join  = 'left join oauth_user as b on a.id=b.user_id';
        $user_query->where = "a.id IN (12,24,51)";
        $user_data         = $user_query->find();
        $i = 0;
        foreach ($user_data as $k=>$v){
            $ret = wechats::send_message_template($v['oauth_user_id'],'member',['number'=>1000000+$v['id'],'create_time'=>$v['datetime']]);
            if ($ret){
                $i++;
                continue;
            } else {
                $v[] = __FUNCTION__;
                common::log_write(print_r($v), 'ERROR', 'wechat');
                return;
            }
        }
        common::print_b($i);
        return;
    }
    function fourty_message(){

    }
}