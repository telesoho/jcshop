<?php
require __DIR__.'/../plugins/vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;


class Apib extends IController{

	private static $ERROR = array(
		'TODO' => 'TODO:未完成',
		'9001' => '9001:请求未授权',
		'9002' => '9002:未定义的接口类型',
		'9003' => '9003:重复请求',
		'9004' => '9004:消息解析失败',
		'9005' => '9005:请求参数不正确',
		'9006' => '9006:缺少必须的请求参数',
	);

	private static $log;
	

	private $partner_id, $partner_name, $partner_key;
	private $interfacename;

	function init(){
        // 设置输出日志
        self::$log = new Logger('apib');
        self::$log->useMicrosecondTimestamps(true);
        $log_path = __DIR__ . '/../backup/logs/apib';
        if (!file_exists($log_path)) mkdir($log_path);

		$dateFormat = "Y-m-d h:i:s.u";
		$output     = "[%datetime%] [%level_name%]: %message% %context%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        
        $stream = new StreamHandler($log_path . '/apib_'.date('Y-m-d').'.log');
        $stream->setFormatter($formatter);
        self::$log->pushHandler($stream);		
	}

	// 计算token
	private function toToken($parenter_id, $parenter_key, $api_name, $param) {
		//当前系统时间：格式为yyyy-MM-dd
        $dateStr = date("Y-m-d");
		$paramContent = json_encode($param);
        $tokenStr = $parenter_id. "@". $parenter_key. "@" . $dateStr . "@" . $api_name . "@" . $paramContent;

        $token = strtoupper(md5($tokenStr));
		return $token;
	}
	
    /**
     * 返回json
     * @param $data
     */
	private function exitJSON($data){
		header('Content-type: application/json');
		echo JSON::encode($data);
		exit();
	}

	/**
	 * 取出查询参数
	 * @param $validators 校验器
	 * @return array 查询参数数组
	 */
	private function getRequestParam($api_name, $validators = array()) {
		// 取出请求头
		$headers = apache_request_headers();
		if(!isset($headers['partner']) || !isset($headers['interfacename']) || !isset($headers['token'])) {
			$this->exitError("9001", array(__LINE__, $headers));
		}

		// 接口验证
		$this->interfacename = $headers['interfacename'];
		if($api_name !== $this->interfacename) {
			$this->exitError("9001", array(__LINE__, $headers));
		}

		// 验证合作者ID
		$this->partner_id = $headers['partner'];
		$parenterObj = new IModel("partner");
		$result = $parenterObj->getObj("partner_id = '". $this->partner_id . "'");
		if(!$result) {
			$this->exitError("9001", array(__LINE__, $headers));
		}

		$this->partner_id = $result['partner_id'];
		$this->parenter_key = $result['partner_key'];

		// 验证token
		$token = $headers['token'];

		// 取出请求内容
		$param = @file_get_contents('php://input');
		// $genToken = $this->toToken($this->partner_id, $this->partner_key, $this->interfacename, $param);
		// if($token !== $genToken) {
		// 	$this->exitError("9001", array(__LINE__, $genToken, $headers));
		// }
		
		$paramContent = json_decode($param, true);
		$v = new Validator();
		if(!$v->validate_array($paramContent, $validators))
		{
			$this->exitError("9005", array(__LINE__, "messages" => $v->getErrMsg(), $paramContent, $headers));
		}

		return $paramContent;
	}

	/**
	 * 商品同步	 
		RequestJSON：
		{
		    "ReqType":"2",	// 请求类型：1-同步所有商品，2-同步部分商品
		    "SkuReqs":[		// 同步部分商品时填写
		        "MUJ8358",
		        "XHX3265",
				"CC7034"
		    ]
		}	 
	 */
	public function SkuSynchro() {

		$req = $this->getRequestParam(__FUNCTION__, array("ReqType","SkuReqs"));

		if($req['ReqType'] == 2) {
			$nos = implode(",", $req['SkuReqs']);
			$goodsList = Api::run('getJcshopGoodsInfoForApib', array('#nos#',$nos));
			$this->exitJSON($goodsList);
		} 

		$this->exitError('9005', array(__LINE__, $req));
	}


	/**
	 * 输出错误日志，并以JSON形式返回错误结果
	 */
	private function exitError($errCode, $context = array()) {
		$err['error'] = isset(self::$ERROR[$errCode])?self::$ERROR[$errCode]:$errCode;

		if(isset($context['messages'])) {
			$err['messages'] = $context['messages'];
		}

		self::$log->err($err['error'], $context);

		if(isset($context['statusCode'])) {
			http_response_code($context['statusCode']);
		}

		$this->exitJSON($err);
	}

	/**
	 * 库存同步
	 * 请求：{"ReqType":"2","SkuReqs":["4056800250950","4056800250967","4056800250974"]} 
	 * 返回：[{"SkuNo":"4056800250950","Quantity":"5"},{"SkuNo":"4056800250967","Quantity":"5"},{"SkuNo":"4056800250974","Quantity":"5"}]
	 */
	public function StockSynchro() {
		$req = $this->getRequestParam(__FUNCTION__, array("ReqType", "SkuReqs"));
		if($req['ReqType'] == 2) {
			$nos = implode(",", $req['SkuReqs']);
			$result = Api::run('getJcshopStockForApib', array('#nos#',$nos));
			$this->exitJSON($result);
		} 
		$this->exitError('9006', array(__LINE__, $req));
	}

	/**
	 * 新增订单
	 *
		ReqeustJSON：
		{
			"ConsigneeName": "王彬",
			"OrderTime": "20161016122450",
			"GoodsPrice": "100.0",
			"PostalPrice": "15.0",
			"Tax": "11.9",
			"IdCard": "220181199107XXXXXX",
			"Province": "浙江省",
			"ConsigneeNumber": "15712341234",
			"City": "杭州市",
			"DetailedAddres": "浙商财富中心四号楼506",
			"PayerName": "王彬",
			"PayNo": "sadadad",
			"DeliveryType": "1",
			"District": "西湖区",
			"Favourable": "12",
			"OrderNo": "NS-20161128175723",
			"OrderItems": [
				{
					"BuyPrice": "100",
					"SkuNo": "NS10023",
					"BuyQuantity": "1",
					"Tax": "11.9"
				},
				{
					"BuyPrice": "100",
					"SkuNo": "NS10023",
					"BuyQuantity": "1",
					"Tax": "11.9"
				}
			],
			"Remark": "测试",
			"OrderPrice": "114.9",
			"PayType": "1",
			"Nick": "王彬"
		}
	 *
		ResponseJSON：
		{
			"success": "true",
			"Code": "0000",
			"Message": "XXXXXX"
		}
	 */
	public function AddOrder() {
		// 解析请求参数
		$reqOrder = $this->getRequestParam(__FUNCTION__,
			array(
				"OrderNo" => array('isset', 'size:[1-40]'),
				"OrderTime" => array('isset', 'datetime:YmdHis'), 
				"GoodsPrice" => array('isset', 'number'),
				"PayType" => array('isset', 'regexp:[123]'), 
				"ConsigneeMobile" => array('mobile'),
				"ConsigneeName" => array('size:[1-20]'),
				"PostCode" => array('zip'),
				"IdCard" => array('isset','idcard'),
				"Province" => array('isset','size:[2-40]'),
				"City" => array('isset','size:[2-40]'),
				"District" => array('size:[0-40]'),
				"DetailedAddres" => array('size:[2-256]'),
				"Remark" => array('size:[2-256]'),
				"PostalPrice" => array('number'),
				"Favourable" => array('isset','number'),
				"Duties" => array('isset','number'),
				"GoodsPrice" => array('isset','number'),
				"OrderPrice" => array('isset','number'),
				"DeliveryType" => array('regexp:[123]'),
				"OrderItems" => array('isset','array:[1-]'),
			)
		);

		// 验证请求参数
		try {
			$jcOrder = $this->toJcOrder($reqOrder);
			
		} catch (Exception $e) {
			$this->exitError(strval($e->getCode()), array(__LINE__, 'messages' => $e->getMessage(), $reqOrder));
		}
	
		$this->exitJSON($jcOrder);
	}

	/**
	* 检查订单参数
	* @param $order
	*	ReqeustJSON：
	*	{
	*		"ConsigneeName": "王彬",
	*		"OrderTime": "20161016122450",
	*		"GoodsPrice": "100.0",
	*		"PostalPrice": "15.0",
	*		"Tax": "11.9",
	*		"IdCard": "220181199107XXXXXX",
	*		"Province": "浙江省",
	*		"ConsigneeMobile": "15712341234",
	*		"City": "杭州市",
	*		"DetailedAddres": "浙商财富中心四号楼506",
	*		"PayerName": "王彬",
	*		"PayNo": "sadadad",
	*		"DeliveryType": "1",
	*		"District": "西湖区",
	*		"Favourable": "12",
	*		"OrderNo": "NS-20161128175723",
	*		"OrderItems": [
	*			{
	*				"BuyPrice": "100",
	*				"SkuNo": "NS10023",
	*				"BuyQuantity": "1",
	*				"Tax": "11.9"
	*			},
	*			{
	*				"BuyPrice": "100",
	*				"SkuNo": "NS10023",
	*				"BuyQuantity": "1",
	*				"Tax": "11.9"
	*			}
	*		],
	*		"Remark": "测试",
	*		"OrderPrice": "114.9",
	*		"PayType": "1",
	*		"Nick": "王彬"
	*	}
	* @return array 九猫订单
	*/
	private function toJcOrder($reqOrder) {
		$jcshopOrder = array();
		$retMsg = array();

		// 订单编号
		$field = $reqOrder["OrderNo"];
		$jcOrder['order_no'] = $field;

		// 支付时间
		$field = $reqOrder["OrderTime"];
		$jcOrder['pay_time'] = $field;

		// 支付方式
		$field = $reqOrder["PayType"];
		$jcOrder['pay_type'] = $field;

		// 支付流水号

		// 买家昵称

		// 收货人移动电话
		$field = $reqOrder["ConsigneeMobile"];
		$jcOrder['mobile'] = $field;
		
		// 收货人姓名
		$field = $reqOrder["ConsigneeName"];
		$jcOrder['accept_name'] = $field;

		// 支付人姓名 支付人用于报关不填默认为收货人
		$field = $reqOrder['PayerName'];
		if($field) {
			$jcOrder['payer_name'] = $field;
		} else {
			$jcOrder['payer_name'] = $reqOrder["ConsigneeName"];
		}

		// 支付人身份证号码，支付人为空时为收货人身份证号码
		$field = $reqOrder['IdCard'];
		$jcOrder['sfz_num'] = $field;

		// 邮政编码
		$field = $reqOrder['Postcode'];
		$jcOrder['postcode'] = $field;

		// 省
		$area_ids = area::id($reqOrder['Province'], $reqOrder['City'], $reqOrder['District']);

		if(isset($area_ids[$reqOrder['Province']])) {
			$jcOrder['province'] = $area_ids[$reqOrder['Province']];
		} else {
			$retMsg[] = "【省】". $reqOrder['Province'] ."无法找到";
		}
		if(isset($area_ids[$reqOrder['City']])) {
			$jcOrder['city'] = $area_ids[$reqOrder['City']];
		} else {
			$retMsg[] = "【市】". $reqOrder['City'] ."无法找到";
		}
		if(isset($area_ids[$reqOrder['District']])) {
			$jcOrder['district'] = $area_ids[$reqOrder['District']];
		}
		if($reqOrder['District'] && !isset($area_ids[$reqOrder['District']])){
			$retMsg[] = "【地区】". $reqOrder['District'] ."无法找到";
		}

		// 详细地址
		$field = $reqOrder["DetailedAddres"];
		$jcOrder['address'] = $field;

		// 备注
		$field = $reqOrder["Remark"];
		$jcOrder['postscript'] = $field;
		
		// 邮费
		$field = $reqOrder["PostalPrice"];
		$jcOrder['real_freight'] = $field;

		// 优惠金额
		$field = $reqOrder["Favourable"];
		$jcOrder['promotions'] = $field;

		// 关税税费
		$field = floatval($reqOrder["Duties"]);
		$jcOrder['duties'] = $field;

		// 货值
		$field = floatval($reqOrder["GoodsPrice"]);
		// 应付商品总额
		$jcOrder['payable_amount'] = $field;
		
		// 订单总价
		$field = floatval($reqOrder["OrderPrice"]);
		// 实付商品总额
		$jcOrder['real_amount'] = $field;

		// 发货方式
		$field = $reqOrder["DeliveryType"];
		// 发货方式
		$jcOrder['delivery_type'] = intval($field);

		// 校验订单总价
		if($jcOrder['real_amount'] != 
			$jcOrder['payable_amount'] + $jcOrder['real_freight'] + $jcOrder['duties'] - $jcOrder['promotions'] ) {
				$retMsg[] = "订单总价 ≠ 货值 + 邮费 + 税费 - 优惠金额";
			}
		
		$duties_sum = 0;
		
		// 订单明细
		$orderItems = $reqOrder["OrderItems"];
		if(is_array($orderItems)) {
			$jcOrder['order_goods'] = array();
			$goodsData = array();

			foreach($orderItems as $item) {
				$orderGoods = array();
				// 商品编码
				$SkuNo = $item['SkuNo'];
				if(!Validator::v_size($SkuNo, "[1-50]")) {
					$retMsg[] = "【SkuNo】$SkuNo 不正确" ;
					continue;
				} else {
					$goodsData = Api::run("getGoodsInfoBySkuNo", array("#sku_no#",$SkuNo));
					if($goodsData) {
						$orderGoods['goods_data'] = $goodsData;
					} else {
						$retMsg[] = "【SkuNo】$SkuNo 不存在";
						continue;						
					}
				}
				
				// 购买数量
				$BuyQuantity = intval($item['BuyQuantity']);
				if($BuyQuantity < 1) {
					$retMsg[] = "$sku_no【购买数量】$BuyQuantity 不正确" ;
				} else {
					$orderGoods['goods_nums'] = $BuyQuantity;
				}
				
				// 购买单价
				$BuyPrice = floatval($item['BuyPrice']);
				if($BuyPrice < 0) {
					$retMsg[] = "【购买单价】< 0" ;
				} else {
					$orderGoods['real_price'] = $BuyQuantity;
					$orderGoods['goods_price'] = $goodsData['sell_price'];
				}

				// 商品税费 当前商品税费，已经算过数量
				$Duties = floatval($item['Duties']);
				if($Duties <> $BuyQuantity * $goodsData['duties_rate'] * $BuyPrice) {
					$retMsg[] = "$SkuNo 【商品税费】≠ 【购买数量】X 商品税率" ;
				} else {
					$orderGoods['duties'] = $Duties;
				}

				// 设置商品图片
				$orderGoods['img'] = $goodsData['img'];

				// 设置重量
				$orderGoods['goods_weight'] = $goodsData['weight'];

				// goods_array
				$goods_array = array();
				$goods_array['name'] = $goodsData['name'];
				$goods_array['goodno'] = $goodsData['goods_no'];
				$goods_array['value'] = '';
				$orderGoods['goods_array'] = json_encode($goods_array);

				$jcOrder['order_goods'][] = $orderGoods;
			}
		} else {
			$retMsg[] = "【订单明细】为空";
		}

		if($retMsg) {
			throw new Exception(implode("\r\n", $retMsg), 9005); 
		}

		return $jcOrder;
	}
	
	// 运单同步
	public function PostSynchro() {

		$this->json_echo(self::$ERROR['TODO']);
	}

	// 翻译规格名称
	public function TranslateSpec() {
		set_time_limit(0);
		ini_set("max_execution_time",0);
		$count = 0;
		$specDict = array();
		$query = new IModel("spec");
		$specObjs = $query->query();
		foreach($specObjs as $specObj) {
			$specDict[$specObj['id']] = array();
			$specDict[$specObj['id']]['value'] = common::spec_split($specObj['value']);
			$specDict[$specObj['id']]['note'] = $specObj['note'];
		}
		$productsDB = new IModel("products");

		$ret = $productsDB->getObj("","max(id) as maxid, min(id) as minid");
		$maxid = intVal($ret['maxid']);
		$minid = intVal($ret['minid']);
		$window_size = 1000;

		for($top_id = $minid + $window_size, $bottom_id = $minid; $bottom_id <= $maxid; $bottom_id += $window_size, $top_id += $window_size) {
			$products_list = $productsDB->query("id < $top_id and id >= $bottom_id", "id, spec_array, spec_array_id");
			foreach($products_list as $products) {
				$spec_array = $this->translate($products['spec_array_id'], $specDict);
				$spec_array_db = JSON::encode(JSON::decode($products['spec_array']));
				if($spec_array_db!== $spec_array) {
					$count ++;
					$products['spec_array'] = $spec_array;
					$productsDB->setData($products);
					$productsDB->update("id=" . $products['id']);
				}
			} 
		}
		echo "{$count}个规格翻译完毕";
	}

	// from [{"id":"1","type":"1","name":"color","value":0},{"id":"2","type":"1","name":"size","value":0}]
	// to [{"id":"1","type":"1","name":"color","value":"黑"},{"id":"2","type":"1","name":"size","value":"L"}]
	protected function translate($spec_array_id, $specDict) {
		if(!$spec_array_id) {
			return "";
		}
		$spec_array_val = JSON::decode($spec_array_id, true);
		foreach($spec_array_val as $key => &$spec) {
			$vid = $spec['value'];
			$spec['value'] = $specDict[$spec['id']]['value'][$vid];
			$spec['name'] = $specDict[$spec['id']]['note'];
		}
		return JSON::encode($spec_array_val);
	}

	/**
	 * 把商品主图追加到内容后
	 * 参考batch/add_contnet_img.json
	 */
	public function addContentImages() {
		set_time_limit(0);
		ini_set("max_execution_time",0);

		$basePath = dirname(dirname(__FILE__));
		$req_file = IReq::get("req_file");
		if(!$req_file) {
			$this->exitJSON("ERROR: req_file requried.");
		}

		$realFile = $basePath . DIRECTORY_SEPARATOR . "batch" . DIRECTORY_SEPARATOR . $req_file; 

		$req_json = file_get_contents($realFile);

		$encoding = mb_detect_encoding($req_json, array("ASCII","GB2312","GBK","UTF-8"));

		if($encoding != "UTF-8") {
			$req_json = iconv($encoding, 'UTF-8', $req_json); //将字符串的编码转到UTF-8
		}

		$req = JSON::decode($req_json, true);
		
		$query = new IQuery("goods_photo_relation as gpr");
		$query->join = "left join goods_photo as gp on gp.id = gpr.photo_id";
		$query->fields = "gp.img";

		$goodsDB = new IModel("goods");
		$dolist = array();

		foreach($req['GoodsIds'] as $goods_id) {

			$goodsObj = $goodsDB->getObj("id=$goods_id");
			if(!$goodsObj) {
				continue;
			}

			$query->where = "gpr.goods_id = $goods_id";
			$result = $query->find();
			if(!$result) {
				continue;
			}


			// 设置详情图
			$contentPic = $result;

			//  设置商品详情图
			if($contentPic) {
				$content = $goodsObj['content'];

				foreach($contentPic as $pic) {
					// 如果该图片在详情中没有，则将该链接增加到详情中
					$pic = $pic['img'];
					if(strpos($content, $pic) === false)
					{
						$content .= '<p><img src="/' . $pic . '" alt="" /></p>';
					}
				}
				$goodsObj['content'] = $content;
			}

			$goodsDB->setData($goodsObj);
			$goodsDB->update("id=$goods_id");
			$dolist[] = $goods_id;
		}

		$this->exitJSON($dolist);
	}

	public function testSubQuery() {
		$subQuery = array(
			'g1' => array(
				'table' => 'goods g1',
				'fields' => 'g1.*, gs.delivery_city, gs.duties_rate, gs.delivery_code, gs.ware_house_name',
				'join' => "left join goods_supplier as gs on g1.supplier_id = gs.supplier_id and g1.sku_no = gs.sku_no",
			), 
		);
		$query = new IQuery("order_goods AS og");
		$query->where = "og.order_id = 1";
		$query->subQueries = $subQuery;
		$query->join = "LEFT JOIN @g1 as g ON g.id = og.goods_id ";
		$query->fields = "g.sku_no, g.goods_no, g.name, g.ware_house_name,  g.content, g.delivery_code, g.supplier_id, g.delivery_city,g.duties_rate,"
						."og.*";
		common::print_b($query->getSql());
	}

	public function testApiSubQuery() {
		// 带参数子查询测试
		common::print_b(Api::run("getGoodsProductsInfoBySkuNo", 
			array('params' => array("#sku_no#" => "DHC7700BS", "#supplier_id#" => 1)))
		);
	}


	public function testGoodsProducts() {

		// 'query' => array(
		// 	'name'   => 'goods as go',
		// 	'where'  => 'go.id = #id# and go.is_del = 0',
		// 	'join'	 => 'left join goods_supplier as gs on go.supplier_id = gs.supplier_id and go.sku_no = gs.sku_no',
		// 	'fields' => 'go.name,go.id as goods_id,go.img,go.sell_price,go.point,go.weight,go.store_nums,go.exp,go.goods_no,0 as product_id,go.seller_id,'
		// 				.'gs.duties_rate,gs.ware_house_name,gs.supplier_id,gs.delivery_code',
		// 	'type'   => 'row',
		// )
		$sku_no = IReq::get("sku_no");

		$subQueries = array(
			'gp' => array(
				'table' => 'goods as g',
				'join' => "left join @prod as p on g.id = p.goods_id",
				'fields' => 'ifnull(p.products_no, g.sku_no) as sku_no, ifnull(p.weight, g.weight) as weight, ifnull(p.store_nums, g.store_nums) as store_nums,'
							.'ifnull(p.sell_price, g.sell_price) as sell_price, ifnull(p.market_price, g.market_price) as market_price,'
							.'ifnull(p.jp_price, g.jp_price) as jp_price,'
							.'ifnull(p.cost_price, g.cost_price) as cost_price,'
							.'g.name, g.id as goods_id,g.img, g.point, g.exp, g.goods_no, p.id as products_id, g.seller_id, g.supplier_id, g.is_del',
			),
			'prod' => array(
				'table' => 'products as p',
				'where' => "products_no = '$sku_no'",
			),
		);
		$query = new IQuery("@gp as gp");
		$query->subQueries = $subQueries;
		$query->join = 'left join goods_supplier as gs on gp.supplier_id = gs.supplier_id and gp.sku_no = gs.sku_no';
		$query->fields = 'gp.*, gs.duties_rate,gs.ware_house_name,gs.supplier_id,gs.delivery_code';
		$query->where = "gp.sku_no = '$sku_no'";

		$result = $query->find();

		$this->exitJSON($result);

	}
}