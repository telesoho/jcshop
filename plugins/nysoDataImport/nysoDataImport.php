<?php
/**
 * @brief 妮素商品导入插件
 * @author twh
 * @date 2017/1/13
 */
class nysoDataImport extends pluginBase
{

	const NYSO_SUPPLIER_ID = 1;
	const NYSO_ORDER_PRE = "JM-";
	private static $CALLBACK_OK = array("success" => true);
	private static $CALLBACK_NG  = array("success" => false);

	private $exchange_rate_jp;	// 日币对人民币汇率
	private $pluginDir;

	
	private $data = array('error' => '', 'info' => '');

	// 订单状态 1生成订单,2支付订单,3取消订单(客户触发),4作废订单(管理员触发),5完成订单,6退款(订单完成后),7部分退款(订单完成后)
	private static $nysoOrderStatus2JcOrderStatus = array (
		'TRADE_STATUS_DELIVERY_WAIT' => 2,
		'TRADE_STATUS_DELIVERY_FINISHED' => 5,
		'TRADE_STATUS_FINISHED' => 5,
	);

	// searchOrder验证器
	private	$order_validator = array(
				"OrderTime" => array('isset', 'datetime:Y-m-d H:i:s'),
				"ConsigneeName" => array('size:[1-20]'),
				"ConsigneeMobile" => array('mobile'),
				"IdCard" => array('isset','idcard'),
				"Province" => array('isset','size:[2-40]'),
				"City" => array('isset','size:[2-40]'),
				"District" => array('size:[0-40]'),
				"DetailedAddres" => array('size:[2-256]'),
				"Remark" => array('size:[0-256]'),
				"PostalTotal" => array('number'), //邮费
				"OrderTotal" => array('isset','number'),// 订单总价(单位：元)
				"SettleTotal" => array('isset', 'number'), //结算价
				"DiscountTotal" => array('isset','number'), //优惠金额(单位：元)
				"TaxFee" => array('isset','number'),// 总税额(单位：元)
				"WmsName" => array('isset', 'size:[1-40]'),//供应商仓库名称
				"OrderStatus" => array('regexp:(TRADE_STATUS_DELIVERY_WAIT|TRADE_STATUS_DELIVERY_FINISHED|TRADE_STATUS_FINISHED)'), // 订单状态
				"PostId" => array('size[1-40]'),//运单号
				"LogisticName" => array('size[1-40]'),//物流方式
				"SendTime" => array('datetime:Y-m-d H:i:s'),//发货时间
				'OrderItems' => array('isset','array:[1-]'),//订单商品明细
			);

	//注册事件
	public function reg()
	{
		//后台管理
		plugin::reg("onSystemMenuCreate",function(){
			$link = "/plugins/nyso_data_import"; // 插件链接必须小写字母，否则后台菜单会出现无法选中的问题
			Menu::$menu["插件"]["插件管理"][$link] = $this->name();
		});

		// 注册妮素平台接口画面
		plugin::reg("onBeforeCreateAction@plugins@nyso_data_import",function(){
            self::controller()->nyso_data_import = function(){
				$this->redirect("nysoDataImport", $this->data);
			};
		});

		// 注册妮素商品同步接口
		plugin::reg("onBeforeCreateAction@plugins@nyso_goods_syn",function(){
            self::controller()->nyso_goods_syn = function(){
				$this->pluginDir= $this->path();				
				$this->nyso_goods_syn();
			};
		});

		// 注册妮素商品库存同步接口
		plugin::reg("onBeforeCreateAction@nyso@nyso_stock_syn",function(){
            self::controller()->nyso_stock_syn = function(){
				$this->nyso_stock_syn();
			};
		});

		// 注册妮素运单同步接口
		plugin::reg("onBeforeCreateAction@nyso@nyso_post_syn",function(){
            self::controller()->nyso_post_syn = function(){
				$this->nyso_post_syn();
			};
		});

		// 注册妮素异步订单同步接口
		plugin::reg("onBeforeCreateAction@nyso@nyso_order_asyn",function(){
            self::controller()->nyso_order_asyn = function(){
				$this->nyso_order_asyn();
			};
		});

		// 异步订单同步事件
		plugin::reg("onBeforeCreateAction@nyso@OrderAsynNotify", function(){
			self::controller()->OrderAsynNotify = function() {
				// 初始化妮素平台接口
				nysochina::init($this->config());

				$param = $this->getRequestParam("OrderAsynNotify");

				// 订单流水号，去除妮素订单前缀
				$orderNo = str_replace(self::NYSO_ORDER_PRE, "", $param['OrderNo']);

				/*
				0000	成功
				9004	消息解析失败
						具体错误信息
				9999	参数不能为空
						具体错误信息
				*/
				$code = $param['Code'];
				$message = $param['Message'];
				
				if($code === "0000") {
					try{
						$orderDB = new IModel("order as o");
						$updateOrder['supplier_syn_date'] = date('Y-m-d H:i:s', time());
						$orderDB->setData($updateOrder);
						$orderDB->update("order_no = '$orderNo'");
					}catch(Exception $e) {
						$this->error($e->getMessage(), $param);
						$this->exitJSON(self::$CALLBACK_NG);
					}
				} else {
					$this->error("妮素返回订单错误信息", $param);
					$this->exitJSON(self::$CALLBACK_NG);
				}

				$this->info("妮素订单处理完毕", $param);
				$this->exitJSON(self::$CALLBACK_OK);
			};
		});

		// 妮素API测试接口
		plugin::reg("onBeforeCreateAction@nyso@nyso_api",function(){
            self::controller()->nyso_api = function(){
				// 初始化妮素平台接口
				nysochina::init($this->config());

				$api_name = IReq::get("api_name");
				$req_json = IReq::get("req_json");
				
				$encoding = mb_detect_encoding($req_json, array("ASCII","GB2312","GBK","UTF-8"));
				
				if($encoding != "UTF-8") {
					$req_json = iconv($encoding, 'UTF-8', $req_json); //将字符串的编码转到UTF-8
				}

				nysochina::$log->info("req_json", array($req_json));

				$req = json_decode($req_json, true);

				nysochina::$log->info("req", array($req));

				if(json_last_error()) {
					$this->exitJSON(json_last_error_msg());
				}else {
					try{
						$output = nysochina::run($api_name, $req);
						nysochina::$log->info("output", array($output));						
					} catch(Exception $e) {
						$this->exitJSON($e->getMessage());
					}
					$this->exitJSON($output);
				}
			};
		});

		plugin::reg("onBeforeCreateAction@nyso@sup_goods_syn",function(){
           self::controller()->sup_goods_syn = function(){
				// 初始化妮素平台接口
				nysochina::init($this->config());
				$obj = Api::run("getGoodsInfoBySkuNo2", array('params' => array("#sku_no#" => "4902806314946-1")));
				$this->exitJSON($obj);
			};
		});


		plugin::reg("onBeforeCreateAction@nyso@run_api", function() {
			self::controller()->run_api = function () {
				set_time_limit(0);
				ini_set("max_execution_time",0);

				// 初始化妮素平台接口
				nysochina::init($this->config());
				
				$api_name = IReq::get("api_name");
				if(!$api_name) {
					$this->exitJSON("ERROR: api_name requried.");
				}

				$req_file = IReq::get("req_file");
				if(!$req_file) {
					$this->exitJSON("ERROR: req_file requried.");
				}
				$req_json = file_get_contents($req_file);

				$encoding = mb_detect_encoding($req_json, array("ASCII","GB2312","GBK","UTF-8"));
				
				if($encoding != "UTF-8") {
					$req_json = iconv($encoding, 'UTF-8', $req_json); //将字符串的编码转到UTF-8
				}

				if(nysochina::$config['debug'] == "true") {
					nysochina::$log->info("req_json", array($api_name, $req_json));
				}

				$req = json_decode($req_json, true);

				if(nysochina::$config['debug'] == "true") {
					nysochina::$log->info("req", array($api_name, $req));
				}

				if(json_last_error()) {
					$this->exitJSON(json_last_error_msg());
				}else {
					try{
						$reqOutput = nysochina::run($api_name, $req);
						switch($api_name) {
						case "searchOrder":
							$this->processSearchOrder($reqOutput);						
							break;
						case "orderDelivery":
							break;
						case "supGoodsSynchro":
							break;
						case "supStockSynchro":
							break;
						}
					} catch(Exception $e) {
						$this->exitJSON($e->getMessage());
					}
				}
			};
		});
	}


	/**
	 * 处理妮素供应商订单
	 */
	private function processSearchOrder($reqOutput) {
		$orders = $reqOutput['result']['Orders'];
		foreach($orders as $key => $order) {
			nysochina::$log->info("妮素订单", array($key => $order));
			$jcOrder = $this->nysoOrder2JcOrder($order);

			if($jcOrder) {
				// 保存订单
				nysochina::$log->info("转换后九猫订单", array($key => $jcOrder));
				$this->saveJcOrder($jcOrder);
			}
		}
	}


	// 计算token
	private function toToken($partner_key, $api_name, $param) {
		//当前系统时间：格式为yyyy-MM-dd
        $dateStr = date("Y-m-d");
        $tokenStr = $partner_key . $dateStr . $api_name . $param;

        $token = strtoupper(md5($tokenStr));
		return $token;
	}

	/**
	 * 取出查询参数
	 * @param $validators 校验器
	 * @return array 查询参数数组
	 */
	private function getRequestParam($api_name, $validators = array()) {


		$partner_key = nysochina::getApiKey($api_name);
		
		// 取出请求头
		$headers = apache_request_headers();
		if(!isset($headers['interfacename']) || !isset($headers['token'])) {
			$this->error("接口验证失败", array(__LINE__, $headers));
			$this->exitJSON(self::$CALLBACK_NG);			
		}

		// 接口验证
		$interfacename = $headers['interfacename'];
		if($api_name != $interfacename) {
			$this->error("接口验证失败", array(__LINE__,$api_name, $headers));
			$this->exitJSON(self::$CALLBACK_NG);
		}

		$token = strtoupper($headers['token']);

		// 取出请求内容
		$param = @file_get_contents('php://input');
		$genToken = $this->toToken($partner_key, $interfacename, $param);
		if($token !== $genToken) {
			$this->error("接口验证失败", array(__LINE__, $genToken, $headers, $param));
			$this->exitJSON(self::$CALLBACK_NG);
		}
		
		$paramContent = JSON::decode($param, true);
		$v = new Validator();
		if(!$v->validate_array($paramContent, $validators))
		{
			$this->error("参数验证失败", array(__LINE__, "messages" => $v->getErrMsg(), $paramContent, $headers));
			$this->exitJSON(self::$CALLBACK_NG);
		}

		return $paramContent;
	}

	// 保存订单
	private function saveJcOrder($jcOrder) {
		$this->exitJSON($jcOrder);
	}



	/**
	 * 妮素订单转换为九猫订单
	 * @param $nysoOrder 妮素订单数据
	 * @return array 九猫订单数组
	 */
	private function nysoOrder2JcOrder($nysoOrder) {
		$retMsg = array();
		$jcOrder = array();

		// 校验参数
		$v = new Validator();
		if(!$v->validate_array($nysoOrder, $this->order_validator))
		{
			nysochina::$log->err("验证失败", array(__LINE__, 'messages'=>$v->getErrMsg(), $nysoOrder));
			print_r($v->getErrMsg());
			return null;
		}

		$jcOrder['order_no'] = nysochina::getOrderNo($nysoOrder);
		$jcOrder['mobile'] = $nysoOrder['ConsigneeNumber'];
		$jcOrder['accept_name'] = $nysoOrder['ConsigneeName'];
		$jcOrder['sfz_num'] = $nysoOrder['IdCard'];


		// 省
		$area_ids = area::id($nysoOrder['Province'], $nysoOrder['City'], $nysoOrder['District']);

		if(isset($area_ids[$nysoOrder['Province']])) {
			$jcOrder['province'] = $area_ids[$nysoOrder['Province']];
		} else {
			$retMsg[] = "【省】". $nysoOrder['Province'] ."无法找到";
		}
		if(isset($area_ids[$nysoOrder['City']])) {
			$jcOrder['city'] = $area_ids[$nysoOrder['City']];
		} else {
			$retMsg[] = "【市】". $nysoOrder['City'] ."无法找到";
		}
		if(isset($area_ids[$nysoOrder['District']])) {
			$jcOrder['district'] = $area_ids[$nysoOrder['District']];
		}
		if($nysoOrder['District'] && !isset($area_ids[$nysoOrder['District']])){
			$retMsg[] = "【地区】". $nysoOrder['District'] ."无法找到";
		}

		// 详细地址
		$jcOrder['address'] = $nysoOrder["DetailedAddres"];

		// 备注
		$jcOrder['postscript'] = $nysoOrder["Remark"];
		
		// 邮费
		$jcOrder['real_freight'] = $nysoOrder["PostalTotal"];

		// 订单总价(单位：元)
		$jcOrder['order_amount'] = $nysoOrder["OrderTotal"];

		// 结算金额(单位：元)
		$jcOrder['settle_total'] = $nysoOrder['SettleTotal'];

		// 优惠金额(单位：元)
		$jcOrder['promotions'] = $nysoOrder['DiscountTotal'];

		// 关税税费
		$jcOrder['duties'] = $nysoOrder["TaxFee"];

		// 仓库名称
		$jcOrder['ware_house_name'] = $nysoOrder['WmsName'];

		// 订单状态
		/*订单状态 1生成订单,2支付订单,3取消订单(客户触发),4作废订单(管理员触发),5完成订单,6退款(订单完成后),7部分退款(订单完成后)
		TRADE_STATUS_DELIVERY_WAIT|TRADE_STATUS_DELIVERY_FINISHED|TRADE_STATUS_FINISHED
		*/
		$jcOrder['status'] = self::$nysoOrderStatus2JcOrderStatus[$nysoOrder['OrderStatus']];

		// TODO: 如果存在运单校验运单
		if($nysoOrder['PostId']) {
			$jcOrder['delivery_doc.delivery_code'] = $nysoOrder['PostId'];	//运单单号
			$jcOrder['delivery_doc.delivery_type'] = $nysoOrder['LogisticName'];	//物流方式
			$jcOrder['delivery_doc.time'] = $nysoOrder['SendTime']; // 发货时间
		}


		// 处理订单中的商品
		foreach($nysoOrder['OrderItems'] as $orderItem) {
			// 1. 根据商品sku_no查询对应商品
			$goodsObj = Api::run("getGoodsProductsInfoBySkuNo", 
				array('params' => array("#sku_no#" => $orderItem['SupGoodsNo'], "#supplier_id#" => 1)));

			if($goodsObj) {
				$jcGoodsOrder['goods_id'] = $goodsObj['goods_id'];
				$jcGoodsOrder['product_id'] = $goodsObj['product_id'];
				
				$jcGoodsOrder['goods_nums'] = $orderItem['BuyQty'];
				$jcGoodsOrder['real_price'] = $orderItem['BugPrice'];

			} else {
				// 商品不存在，出错返回NULL
				nysochina::$log->err("验证失败", array(__LINE__, 'messages'=>"订单商品不存在", $orderItem));
				return null;
			}
		}


		return $jcOrder;
	}



	/**
	 * 输出错误日志，并以JSON形式返回错误结果
	 */
	private function exitError($errMsg, $context = array()) {
		$this->error($errMsg, $context);
		$this->exitJSON($this->data);
	}

    /**
     * 输出JSON并退出
     * @param $data
     */
	private function exitJSON($data){
		header('Content-type: application/json');
		echo JSON::encode($data);
		exit();
	}

	/**
	 * 打印信息并退出
	 */
	private function exitMSG($data) {
		print_r($data);
		exit();
	}


	/**
	 * 异步方式添加妮素订单
	 * 将包含妮素商品的订单同步到妮素平台，并设置同步标志
	 */
	private function nyso_order_asyn() {
		set_time_limit(0);
		ini_set("max_execution_time",0);

		// 初始化妮素平台接口
		nysochina::init($this->config());

		$query         = new IQuery('order AS o');
		$query->join   = 'LEFT JOIN areas AS a1 ON o.province = a1.area_id '
						.'LEFT JOIN areas AS a2 ON o.city = a2.area_id '
						.'LEFT JOIN areas AS a3 ON o.area = a3.area_id '
						.'LEFT JOIN user AS u on o.user_id = u.id';
		$query->where  = 'o.supplier_id = 1 and isnull(supplier_syn_date) and pay_status = 1 and status = 2 and if_del <> 1';
		$query->fields = 'a1.area_name as province_name, a2.area_name as city_name, a3.area_name as area_name,'
						.'u.sfz_name as payer_name, u.sfz_num as payer_id_card, o.*';
		$jcOrderList   = $query->find();

		if(count($jcOrderList) < 1) {
			$this->info("没有发现需要同步的妮素订单");
			$this->exitMSG($this->data);
		}

		foreach($jcOrderList as $jcOrder) {
			try {
				$nysoOrder = $this->toNysoOrder($jcOrder);
				$ret = nysochina::run("AddOrderKafkaTemp", $nysoOrder);
				$this->info("同步消息已经发送", $ret);
			}
			catch(Exception $e) {
				$this->error($e->getMessage() . "=>" .$jcOrder['order_no'], $jcOrder);
				continue;
			}
		}

		$this->exitJSON($this->data);
	}

	/**
	* 将九猫平台支付方式转换为妮素支付方式
	*  □ 九猫平台的支付方式：
	0	货到付款
	1	预存款
	2	网银在线
	3	中国银联
	4	中国银联手机网站支付
	5	中国银联B2B企业支付
	6	银联在线
	7	腾讯财付通
	8	快钱
	9	支付宝担保交易
	10	支付宝即时到帐
	11	支付宝网银直连
	12	贝宝
	13	微信移动支付
	14	微信二维码支付
	15	线下支付
	16	支付宝手机网站支付
	* □ 妮素平台支付方式
	1	支付宝
	2	微信
	3	银联
	4	易付宝
	5	盛付通
	*/
	private function toNysoPayType($jcPayType) {
		$nysoPayType = "0";
		switch($jcPayType) {
		case "3":
		case "4":
		case "5":
		case "6":
			$nysoPayType = "3"; // 银联
			break;
		case "9":
		case "10":
		case "11":
		case "16":
			$nysoPayType = "1";	// 支付宝
			break;
		case "13":
		case "14":
			$nysoPayType = "2";	// 微信
			break;
		}
		return $nysoPayType;
	}

	/**
	 * 将九猫订单转为妮素订单
	 * @param $jcOrder 九猫订单
	 * @return array 妮素订单
	 */
	private function toNysoOrder($jcOrder) {
		$nysoOrder = array();

		$orderNo = self::NYSO_ORDER_PRE . $jcOrder["order_no"];					// 订单号形式："JM-" + 九猫商城订单号
        $nysoOrder["OrderNo"] = $orderNo;						// 订单号
		$nysoOrder["OrderTime"] = date("YmdHis",strtotime($jcOrder["pay_time"]));	// 下单时间
        $nysoOrder["PayType"] = $this->toNysoPayType($jcOrder["pay_type"]);		// 支付方式
        $nysoOrder["PayNo"] = "";								// 支付流水号
        $nysoOrder["Nick"] = "";								// 买家昵称
		$nysoOrder["ConsigneeNumber"] = $jcOrder["mobile"];		// 收货人电话
		$nysoOrder["ConsigneeName"] = $jcOrder["accept_name"];		// 收货人姓名

        $nysoOrder["PayerName"] = $jcOrder["accept_name"];			// 支付人姓名 统一使用收货人姓名
		$nysoOrder["IdCard"] = $jcOrder["sfz_num"];					// 支付人身份证号 统一使用收货人身份证
        $nysoOrder["Province"] = $jcOrder["province_name"];			// 省
        $nysoOrder["City"] = $jcOrder["city_name"];					// 城市
        $nysoOrder["District"] = $jcOrder["area"];					// 区
        $nysoOrder["DetailedAddres"] = $jcOrder["address"];			// 收货详细地址信息

        $nysoOrder["Remark"] = "九猫家订单";							// 备注

		$nysoOrder["PostalPrice"] = $jcOrder["real_freight"];		// 邮费
		$nysoOrder["Tax"] = $jcOrder["duties"];         			// 商品税费
		$nysoOrder["GoodsPrice"] = $jcOrder["payable_amount"]; 		// 货值
		if(floatVal($jcOrder['order_amount']) === 0) {
			$nysoOrder["Favourable"] = $jcOrder["promotions"];			// 优惠金额
			$nysoOrder["OrderPrice"] = $jcOrder["order_amount"];  		// 订单总价 用户实际交易金额
		} else {
			// 由于妮素平台不允许订单金额为0的订单,把订单总价设定为应付邮费
			$nysoOrder["Favourable"] = 0;			// 优惠金额
			$nysoOrder["OrderPrice"] = $nysoOrder["GoodsPrice"] + $nysoOrder["Tax"] + $nysoOrder["PostalPrice"];    // 订单总价 为 应付邮费
		}


		// 查询订单商品清单
		$subQuery = array(
			'g1' => array(
				'table' => 'goods g1',
				'fields' => 'g1.*, gs.delivery_city, gs.duties_rate, gs.delivery_code, gs.ware_house_name',
				'join' => "left join goods_supplier as gs on g1.supplier_id = gs.supplier_id and g1.sku_no = gs.sku_no",
			), 
		);
		$query = new IQuery("order_goods AS og");
		$query->where = "og.order_id =" . $jcOrder["id"];
		$query->subQueries = $subQuery;
		$query->join = "LEFT JOIN @g1 as g ON g.id = og.goods_id ";
		$query->fields = "g.sku_no, g.goods_no, g.name, g.ware_house_name,  g.content, g.delivery_code, g.supplier_id, g.delivery_city,g.duties_rate,"
						."og.*";
		$jcItemList    = $query->find();

		$ware_house_name = "";
		$delivery_code = "";

		$items = array();
		foreach($jcItemList as $jcItem) {
			// 订单明细		
			$item["SkuNo"] = $jcItem["sku_no"];						// 商品编码
			$item["BuyQuantity"] = $jcItem["goods_nums"];			// 购买数量
			$item["Tax"] = $jcItem["duties"];						// 商品关税税费 已经算过数量，单位：元
			$item["BuyPrice"] = $jcItem["goods_price"];  			// 未算过数量的购买商品时的单价

			// 验证订单是否合法
			if($jcItem["supplier_id"] !== "1") {
				$e = new Exception("订单包含了非妮素平台的商品");
				nysochina::$log->err($e->getMessage(), $jcOrder);
				throw $e;
			}

			if($ware_house_name && $jcItem["ware_house_name"] !== $ware_house_name) {
				$e = new Exception("订单中存在不在同一仓库的商品");
				nysochina::$log->err($e->getMessage(), $jcOrder);
				throw $e;
			}
			
			if($delivery_code && $jcItem["delivery_code"] !== $delivery_code) {
				$e = new Exception("订单中存在发货方式不一致的商品");
				nysochina::$log->err($e->getMessage(), $jcOrder);
				throw $e;				
			}

			$ware_house_name = $jcItem["ware_house_name"] ;
			$delivery_code = $jcItem["delivery_code"] ;

			$items[] = $item;
		}

        $nysoOrder["OrderItems"] = $items;

		// 订单的发货方式根据订单中的商品所在仓库来决定，妮素平台会验证发货方式与仓位是否一致。
        $nysoOrder["DeliveryType"] = $delivery_code;		// 发货方式 1-保税区发货 2-香港直邮 4--日本直邮 默认为1

		return $nysoOrder;
	}

	/**
	 *  妮素商品数据导入/同步
	 */
	public function nyso_goods_syn() {
		set_time_limit(0);
		ini_set("max_execution_time",0);

		if(!class_exists('ZipArchive'))
		{
			die('服务器环境中没有安装zip扩展，无法使用此功能');
		}

		if(extension_loaded('mbstring') == false)
		{
			die('服务器环境中没有安装mbstring扩展，无法使用此功能');
		}

		$this->seller_id = self::controller()->seller ? self::controller()->seller['seller_id'] : 0;

		//处理上传
		$uploadInstance = new IUpload(9999999,array('zip'));
		$uploadCsvDir   = 'runtime/cvs/'.date('YmdHisu');
		$uploadInstance->setDir($uploadCsvDir);
		$result = $uploadInstance->execute();

		if(!isset($result['csvPacket']))
		{
			die('请上传指定大小的csv数据包');
		}

		if(($packetData = current($result['csvPacket'])) && $packetData['flag'] != 1)
		{
			$message = $uploadInstance->errorMessage($packetData['flag']);
			die($message);
		}

		$zipPath = $packetData['fileSrc'];
		$zipDir  = dirname($zipPath);
		$imageDir= IWeb::$app->config['upload'] . '/goods_pic';
		file_exists($imageDir) ? '' : IFile::mkdir($imageDir);
		$this->imageDir = $imageDir;

		//解压缩包
		$zipObject = new ZipArchive();
		$zipObject->open($zipPath);
		$isExtract = $zipObject->extractTo($zipDir);
		$zipObject->close();

		if($isExtract == false)
		{
			$message = '解压缩到目录'.$zipDir.'失败！';
			die($message);
		}

		// 从配置文件中取出日币汇率
		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		$this->exchange_rate_jp = isset($site_config['exchange_rate_jp'])?floatval($site_config['exchange_rate_jp']):1.0;

		$this->nysoGoodsCsvImport($zipDir, $imageDir);


		//清理csv文件数据
		IFile::rmdir($uploadCsvDir,true);
		$data['info'] = "导入完毕";
		$this->redirect("nysoDataImport", $data);
	}

	private function nysoGoodsCsvImport($zipDir, $imageDir) {

		$goodsFilename = $zipDir . "/goods.csv";

		if(!file_exists($goodsFilename)) {
			die("无法找到goods.csv文件");
			return false;
		}

		require_once($this->pluginDir.'nysoGoodsCsvHelper.php');

		$config = array('csvFile' => $goodsFilename,
			'targetImagePath' => $imageDir,
			'exchange_rate_jp' => $this->exchange_rate_jp,
			);

		$goodsCsvHelper = new nysoGoodsCsvHelper($config);
		
		//从csv中解析数据
		$collectData = $goodsCsvHelper->collect();

		$this->processGoodsData($collectData);
		
	}


	// 将商品数据插入数据库
	private function processGoodsData($p_collect_data) {

		$titleToCols = array(
			'brand.name' 	 	=> '品牌名',
			'supplier_id' 		=> '供应商ID',
			'goods_supplier.sku_no'	=> '供应商货号',
			'auto_syn'			=> '自动同步',
			'goods_no'	 		=> '商品JAN编码',
			'name'       		=> '商品名称',
			'category.name'   	=> '商品类目',
			'sell_price' 		=> '销售价格',
			'jp_market_price' 	=> '日本市场价格',
			'store_nums' 		=> '库存数量',
			'content'    		=> '商品详情',
			'reset_img'        	=> '重设图片',
			'spec_array' 		=> '销售属性',
			'weight'     		=> '物流重量',
			'name_jp'	 		=> '商品名称日文',
			'content_jp'		=> '商品详情日文',
			'is_del'     		=> '状态',  /*0:上架 1：删除 2：下架 3:待审*/
			'jp_price'   		=> '日本价格',
			'is_zh_title' 		=> '商品标题是中文',
			'is_zh_content' 	=> '商品详情是中文',
			'tag' 				=> '标签',
			'new_item' 			=> '最新商品',
			'hot_item' 			=> '热卖商品',
			'recommend_item' 	=> '推荐商品',
		);

		//实例化商品
		$goodsDB     		= new IModel('goods');
		$goodsSupplierDB    = new IModel('goods_supplier');
		$photoRelationDB 	= new IModel('goods_photo_relation');
		$photoDB         	= new IModel('goods_photo');
		$cateExtendDB    	= new IModel('category_extend');
		$brandDB 		 	= new IModel('brand');
		$commendDB			= new IModel('commend_goods');
		$wareHouseDB 		= new IModel('ware_house');

		//默认图片路径
		$default_img 		= 'upload/goods_pic/nopic.jpg';

		
		// 初始化妮素平台接口
		nysochina::init($this->config());
		
		//插入商品表
		foreach($p_collect_data as $key => $val)
		{

			// 供应商编号
			$supplier_id = trim($val[$titleToCols['supplier_id']]);
			if('1' != $supplier_id) {
				$this->error("供应商编号必须为1（妮素）：" . JSON::encode($val));
				continue;
			}

			// 自动同步标志
			$auto_syn = trim($val[$titleToCols['auto_syn']]);

			// 供应商货号
			$sku_no = trim($val[$titleToCols['goods_supplier.sku_no']]);
			if('' === $sku_no) {
				$this->error("供应商货号sku_no不能为空：" . JSON::encode($val));
				continue;
			}

			$goodsSupplierObj = array();
			$wareHouseObj = array();

			// 自动同步，则先根据sku_no从妮素平台读取商品数据
			if($auto_syn) {
				// 从接口读取商品
				try{
					$goodsSupplierApi = $this->getGoodsFromApi($sku_no);
				} catch(Exception $e) {
					$this->error('从妮素API读取数据失败[' . $sku_no . ']：'. $e->getMessage());
					continue;
				}
				// $jcGoodsSupplierData = array(
				// 	'barcode' 			=> $goods['BarCode'],		// 条形码
				// 	'supplier_id'	 	=> '1',						// 妮素固定为1
				// 	'sku_no' 			=> $goods['SkuNo'],			// 商品SKU
				// 	'sku_name' 			=> $goods['SkuName'],		// 商品名
				// 	'details'			=> $goods['Details'],		// 商品详情
				// 	'settle_price'		=> $goods['SettlePrice'],	// 结算价格
				// 	'retail_price'  	=> 	$goods['RetailPrice'],	// 参考价
				// 	'sale_type'			=> $goods['SaleType'],		// 销售类型
				// 	'weight'			=> $goods['Weight'],		// 重量
				// 	'delivery_code'		=> $goods['DeliveryCode'],	// 发货方式：1-报税区 2-香港直邮 4-日本直邮
				// 	'duties_rate'   	=> $goods['Rate'],			// 税率,非保税区这个字段为0
				// 	'delivery_city' 	=> $goods['DeliveryCity'],	// 发货地
				// 	'ware_house_name' 	=> $goods['WareHouseName'], // 仓库名
				// );
				
				if($goodsSupplierApi) 
				{
					// 更新供应商货品表
					$where = "supplier_id = $supplier_id and sku_no = '$sku_no'";
					$goodsSupplierObj = $goodsSupplierDB->getObj($where);
					if($goodsSupplierObj) {
						// 将API读取的数据保存到数据库
						$goodsSupplierDB->setData($goodsSupplierApi);
						$goodsSupplierDB->update("supplier_id = $supplier_id and sku_no = '$sku_no'");
					} else {
						$goodsSupplierDB->setData($goodsSupplierApi);
						$goodsSupplierDB->add("supplier_id = $supplier_id and sku_no = '$sku_no'");
					}

					// 数据库里没有对应的仓库名，则增加该仓库
					$wareHouseName = $goodsSupplierApi['ware_house_name'];
					$supplierId = $goodsSupplierApi['supplier_id'];
					$wareHouseObj = $wareHouseDB->getObj("supplier_id=$supplier_id and ware_house_name='$wareHouseName'");
					if(!$wareHouseObj) {
						$newWareHouse = array();
						$newWareHouse['ware_house_name'] = $wareHouseName;
						$newWareHouse['supplier_id'] = $supplierId;
						$wareHouseDB->setData($newWareHouse);
						$newWareHouse['id'] = $wareHouseDB->add();
						$wareHouseObj = $newWareHouse;
					}

					// 计算商品表数据
					$calGoodsData = $this->calGoodsFromSupplierData($goodsSupplierApi);
				}
			}

			if(!$goodsSupplierObj) {
				$where = "supplier_id = $supplier_id and sku_no = '$sku_no'";
				$goodsSupplierObj = $goodsSupplierDB->getObj($where);
				if(!$goodsSupplierObj) {
					$this->error("商品数据在数据库中不存在:$where");
					continue;
				}				
			}

			if(!$wareHouseObj) {
				$wareHouseName = $goodsSupplierObj['ware_house_name'];
				$where = "supplier_id=$supplier_id and ware_house_name='$wareHouseName'";
				$wareHouseObj = $wareHouseDB->getObj($where);
				if(!$goodsSupplierObj) {
					$this->error("仓库数据不存在:$where");
					continue;
				}
			}

			// 查询商品是否存在, 如果商品存在，则$goodsObj是该商品记录，否则为false
			$where = "supplier_id = $supplier_id and sku_no = '$sku_no'";
			$goodsObj = $goodsDB->getObj($where);

			//处理CSV数据
			$theCsvData = array(
				'seller_id'    => $this->seller_id,
				'ware_house_id' => $wareHouseObj['id'],
			);

			$theCsvData['sku_no'] = $sku_no;
			$theCsvData['supplier_id'] = $supplier_id;

			// 品牌名
			$field = trim($val[$titleToCols['brand.name']]);
			if('' !== $field) {
				$brandName = IFilter::act($field, "string");
				$brandObj = $brandDB->getObj("name = '$brandName'" );
				if($brandObj) {
					$theCsvData['brand_id'] = $brandObj['id'];
				}
			}
			
			// 商品JAN编码
			$field = trim($val[$titleToCols['goods_no']]);
			if('' !== $field) {
				$theCsvData['goods_no'] = IFilter::act($field, "string", 20);
			}

			// 如果商品名称不为空，则修改商品名
			$field = trim($val[$titleToCols['name']]);
			if('' !== $field) {
				$theCsvData['name'] = IFilter::act($field, "string");
			}


			// 如果日本市场价格不为空，则修改日本市场价格
			$field = trim($val[$titleToCols['jp_market_price']]);
			if('' !== $field) {
				$jp_market_price = IFilter::act($field,'float');
				$theCsvData['jp_market_price']  = $jp_market_price;
				$sell_price = $jp_market_price / $this->exchange_rate_jp;
				$theCsvData['sell_price']  =$sell_price;
				if ($sell_price <= 200) {
					$theCsvData['market_price']  = $theCsvData['sell_price'] * 2 ;
				} else {
					$theCsvData['market_price']  = $theCsvData['sell_price']* 1.5 ;
				}
			}
			
			//直接修改商品售价
			$field = trim($val[$titleToCols['sell_price']]);
			if('' !== $field) {
				$sell_price = IFilter::act($field,'float');
				$theCsvData['sell_price']  = $sell_price;
			}

			// 处理库存数
			$field = trim($val[$titleToCols['store_nums']]);
			if('' !== $field) {
				$theCsvData['store_nums'] = IFilter::act($field,'int');
			}

			// 商品详情
			$field = trim($val[$titleToCols['content']], '"\' ');
			if('' !== $field) {
				$theCsvData['content'] = IFilter::addSlash($field);
			}


			//处理商品关键词
			$tag = trim($val[$titleToCols['tag']]);
			if($tag != ''){
				$theCsvData['search_words'] = ','.$tag.',';
				keywords::add($tag);
			}
			
			
			// 销售属性

			// 商家编码

			// 物流重量
			$field = trim($val[$titleToCols['weight']]);
			if('' !== $field) {
				$weight = IFilter::act($field, "string");
				if(strpos($weight, " Kg")) {
					$weight = str_replace(" Kg", "", $weight);
					$weight = round(floatval($weight) * 1000);
				} else {
					$weight = round(floatval($weight));
				}
				$theCsvData['weight'] = $weight;
			}

			// 商品名称日文
			$field = trim($val[$titleToCols['name_jp']], '"\' ');
			if('' !== $field) {
				$theCsvData['name_jp'] = IFilter::act($field, "string");
				if(!isset($theCsvData['name']) && ($goodsObj?'' == $goodsObj['name']:true)) {
					$theCsvData['name'] = $theCsvData['name_jp'];
				}
			}

			// 商品详情日文
			$field = trim($val[$titleToCols['content_jp']], '"\' ');
			if('' !== $field) {
				$theCsvData['content_jp'] = IFilter::addSlash($field);
				if(!isset($theCsvData['content']) && ($goodsObj?'' == $goodsObj['content']:true)) {
					$theCsvData['content'] = $theCsvData['content_jp'];
				}
			}

			// 处理上下架状态
			$field = trim($val[$titleToCols['is_del']]);
			if('' !== $field) {
				$theCsvData['is_del'] = IFilter::act($field,'int');
				switch($theCsvData['is_del']) 
				{
				case 0:
					$theCsvData['up_time'] = ITime::getDateTime();
					$theCsvData['down_time'] = ITime::getDateTime('0000-00-00 00:00:00');
					break;
				case 1:
					$theCsvData['up_time'] = ITime::getDateTime('0000-00-00 00:00:00');
					$theCsvData['down_time'] = ITime::getDateTime();
					break;
				case 2:
					$theCsvData['up_time'] = ITime::getDateTime('0000-00-00 00:00:00');
					$theCsvData['down_time'] = ITime::getDateTime();
					break;
				}
			}

			// 日本价格
			$field = trim($val[$titleToCols['jp_price']]);
			if('' !== $field) {
				$theCsvData['jp_price'] = IFilter::act($field,'int');
			}

			// 有中文商品标题
			$field = trim($val[$titleToCols['is_zh_title']]);
			if('' !== $field) {
				$theCsvData['is_zh_title'] = IFilter::act($field, 'bool');
			}

			// 有中文商品详情
			$field = trim($val[$titleToCols['is_zh_content']]);
			if('' !== $field) {
				$theCsvData['is_zh_content'] = IFilter::act($field, 'bool');
			}

			// 主图
			$mainPic = array();
			// 详情图
			$contentPic = array();

			$field = trim($val[$titleToCols['reset_img']]);
			if('' !== $field) {
				$barcode = $goodsSupplierObj['barcode'];
				$goodsImgDir = $this->imageDir . "/nyso_pics/" . $barcode;

				if(is_dir($goodsImgDir)) {

					// 以升序排序 - 默认
					$img_files = scandir($goodsImgDir);

					foreach($img_files as $file) {
						if($file != '.' && $file != '..'){
							$source_file =  $goodsImgDir . "/" . $file;
							if(is_file($source_file)) {
								if(strpos($file, "_pr_")) {
									// 商品详情图
									$contentPic[] = $source_file;
								} else {
									// 商品轮播图
									$mainPic[] = $source_file;
								}
							}
						}
					}
				}

				// 设置商品主图
				if($mainPic) {
					$theCsvData['img'] = $mainPic[0];
				}else{
					//不存在图片时，保存默认图片
					$theCsvData['img'] 	= $default_img;
				}
			}

			$theGoodsData = array();

			if(isset($calGoodsData)) {
				// 如果自动同步则合并根据供应商表数据填写出的商品表数据
				$theGoodsData = array_merge($calGoodsData, $theCsvData);
			} else {
				$theGoodsData = $theCsvData;
			}

			//  设置商品详情图
			if($contentPic) {
				if(isset($theGoodsData['content'])){
					$content = $theGoodsData['content'];
				} else {
					$content = "";
				}
				foreach($contentPic as $pic) {
					// 如果该图片在详情中没有，则将该链接增加到详情中
					if(strpos($content, $pic) === false)
					{
						$content .= '<p><img src="/' . $pic . '" alt="" /></p>';
					}
				}
				$theGoodsData['content'] = $content;
			}

			if($goodsObj) {
				// 商品已经存在
				// 更新GOODS表数据
				$updateData = $theGoodsData;

				$updateData['sku_no'] = $sku_no;

				$goodsDB->setData($theGoodsData);
				
				$where = "supplier_id = $supplier_id and sku_no = '$sku_no'";
				$qret = $goodsDB->update($where);

				if( $qret === false) {
					$this->error($where . ":" . JSON::encode($updateData));
					continue;
				}
				
				$goods_id = $goodsObj["id"];

			} else {
				// 商品不存在
				// 插入GOODS表数据
				$insertData = $theGoodsData;

				$insertData['sku_no'] = $sku_no;
				$insertData['supplier_id'] = $supplier_id;

				$goodsDB->setData($insertData);
				$goods_id = $goodsDB->add();

				if(false === $goods_id){
					$this->error("Insert error:" . JSON::encode($insertData));
					continue;
				}
			}
			
			//处理商品促销
			$goods_commend 			= array(); //1:最新商品 2:特价商品 3:热卖商品 4:推荐商品
			$new_item 				= trim($val[$titleToCols['new_item']]);
			$hot_item 				= trim($val[$titleToCols['hot_item']]);
			$recommend_item 		= trim($val[$titleToCols['recommend_item']]);
			if($new_item == 1) $goods_commend[] = 1;
			if($hot_item == 1) $goods_commend[] = 3;
			if($recommend_item == 1) $goods_commend[] = 4;
			if(!empty($goods_commend)){
				$commendDB->del('goods_id = '.$goods_id);
				foreach($goods_commend as $v){
					$commendDB->setData(array('goods_id' => $goods_id,'commend_id' => $v));
					$commendDB->add();
				}
			}

			// 如果存在分类名，则商品是否已经与该分类关联，如果没有关联，则将其关联
			$field = trim($val[$titleToCols['category.name']]);
			if('' !== $field) {

				// 查找与分类名相同的分类ID
				$cids = $this->getCategoryIds($field, $this->seller_id);

				foreach($cids as $cid) {
					// 查询商品是否与分类关联
					$ret = $cateExtendDB->get_count("goods_id =$goods_id and category_id =$cid");
					if($ret == 0) {
						// 将商品与分类关联
						$cateExtendDB->setData(array('goods_id' => $goods_id,'category_id' => $cid));
						$cateExtendDB->add();
					}
				}
			}

			//图片后续处理
			$field = trim($val[$titleToCols['reset_img']]);
			if('' !== $field) {
				// 如果已经存在图片，则处理商品图片关联
				if($mainPic) {
					$photoRelationDB->del("goods_id = $goods_id");
					foreach($mainPic as $photoFile) {
						if(!is_file($photoFile))
						{
							continue;
						}
						$md5Code = md5_file($photoFile);
						$photoRow= $photoDB->getObj("id = '$md5Code'");
						if(!$photoRow || !is_file($photoRow['img']))
						{
							// 如果数据库中找不到对应的图片或者原来的图片已经不存在
							$photoDB->del("id = '$md5Code'");
							$photoDB->setData(array("id" => $md5Code,"img" => $photoFile));
							$photoDB->add();
						}
					
						// 关联商品图
						$photoRelationDB->setData(array('goods_id' => $goods_id,'photo_id' => $md5Code));
						$photoRelationDB->add();
					}
				}else{
					//默认图片
					$md5Code 		= md5_file($default_img);
					$photoRow 		= $photoDB->getObj('id = "'.$md5Code.'"');
					if(!$photoRow || !is_file($photoRow['img'])){
						$photoDB->setData(array("id" => $md5Code,"img" => $default_img));
						$photoDB->add();
					}
					// 关联商品图
					$photoRelationRow 		= $photoRelationDB->getObj('goods_id="'.$goods_id.'" and photo_id="'.$md5Code.'"');
					if(empty($photoRelationRow)){
						$photoRelationDB->setData(array('goods_id' => $goods_id,'photo_id' => $md5Code));
						$photoRelationDB->add();
					}
					
				}

			}
		}
	}
	// 取得$seller_id与$parent_id类目下相同的类目名的所有类目ID
	private function getCategoryIds($name, $seller_id, $goods_id=null, $parent_id=null) {

		$ret = [];
		$category = new IModel('category');

		$theCat = $category->query('name ="' . $name . '" and seller_id=' . $seller_id);
		foreach($theCat as $catId){
			$ret[] = $catId['id']; 
		}

		return $ret;
	}
	/**
	 * 将妮素商品数据转换为jcshop商品数据
	 * @param $goods 妮素商品数据
	 * @return array jcshop商品数据
	 */
	private function nyso2jcGoodsSupplier($goods) {
		$jcGoodsSupplierData = array(
			'barcode' 			=> $goods['BarCode'],		// 条形码
			'supplier_id'	 	=> '1',						// 妮素固定为1
			'sku_no' 			=> $goods['SkuNo'],			// 商品SKU
			'sku_name' 			=> $goods['SkuName'],		// 商品名
			'details'			=> $goods['Details'],		// 商品详情
			'settle_price'		=> $goods['SettlePrice'],	// 结算价格
			'retail_price'  	=> 	$goods['RetailPrice'],	// 参考价
			'sale_type'			=> $goods['SaleType'],		// 销售类型
			'weight'			=> $goods['Weight'],		// 重量
			'delivery_code'		=> $goods['DeliveryCode'],	// 发货方式：1-报税区 2-香港直邮 4-日本直邮
			'duties_rate'   	=> $goods['Rate'],			// 税率,非保税区这个字段为0
			'delivery_city' 	=> $goods['DeliveryCity'],	// 发货地
			'ware_house_name' 	=> $goods['WareHouseName'], // 仓库名
		);

		return $jcGoodsSupplierData;
	}

	/**
	 * 根据供应商数据填写对应的Goods表数据
	 * @param $goodsSupplierData goods_supplier表数据
	 * @return array goods表数据
	 */
	private function calGoodsFromSupplierData($goodsSupplierData) {
		$goodsData = array(
			'goods_no'			=> $goodsSupplierData['barcode'],			// 商品JAN
			'name'				=> $goodsSupplierData['sku_name'],			// 商品名
			'content' 			=> $goodsSupplierData['details'],			// 商品详情
			'weight' 			=> $goodsSupplierData['weight'],			// 重量
			'sell_price' 		=> $goodsSupplierData['retail_price'],		// 销售价格
			'market_price' 		=> $goodsSupplierData['retail_price'],		// 市场价格
			'purchase_price' 	=> $goodsSupplierData['settle_price'], 		// 结算价
			'jp_price' 			=> round($goodsSupplierData['retail_price'] * $this->exchange_rate_jp),
			'jp_market_price' 	=> round($goodsSupplierData['retail_price'] * $this->exchange_rate_jp),
			'supplier_id'		=> $goodsSupplierData['supplier_id'],
			'sku_no'			=> $goodsSupplierData['sku_no'],
			'is_del'			=> 2,										// 默认为下架状态
		);
		return $goodsData;
	}

	/**
	 * 通过妮素API读取商品数据
	 * @param $sku_no 妮素商品货号
	 * @return array goods_supplier表数据, goods表数据
	 */
	protected function getGoodsFromApi($sku_no) {
		// 通过妮素接口取出商品数据

		$goodsList = nysochina::getGoods(array($sku_no));

		if(!$goodsList) {
			throw new Exception("从妮素读取{$sku_no}商品数据失败");
		}

		// 将妮素商品数据转换为JCSHOP商品数据
		return $this->nyso2jcGoodsSupplier($goodsList[0]);
	}

	/**
	 * 同步商品库存
	 * @param $stock 商品库存
	 */
	protected function updateStock($stock) {

		$goodsObj = new IModel('goods');

		$where = 'supplier_id = 1 and sku_no= "' . $stock['SkuNo'] . '"';
		$theGoods = $goodsObj->getObj($where);

		if($theGoods) {
			// 如果已经存在商品，则更新商品信息
			$theGoods['store_nums'] = $stock["Quantity"];	// 设置库存
			$goodsObj->setData($theGoods);
			$goodsObj->update($where);
			$this->info($stock['SkuNo'] . "库存跟新成功", $stock);
		} else {
			// 商品不存在，返回错误
			$this->error($stock['SkuNo'] . "商品不存在" . $stock);
		}
	}

	/**
	 * 妮素库存同步接口
	 */
	public function nyso_stock_syn() {
		set_time_limit(0);
		ini_set("max_execution_time",0);

		// 初始化妮素平台接口
		nysochina::init($this->config());
		
		// 从数据库取出所有妮素商品
		$query = new IQuery("goods as go");
		$query->fields = "go.sku_no";
		$query->where = "go.supplier_id = 1";
		$sku_no_list = $query->find();

		foreach($sku_no_list as $sku_no) {
			try {
				// 通过妮素接口取出商品库存数据
				$stockList = nysochina::getStocks(array($sku_no['sku_no']));

				// 将库存保存到九猫数据库
				foreach($stockList as $stock) {
					$this->updateStock($stock);
				}
			}
			catch (Exception $e) {
				$this->error( $sku_no['sku_no'] . $e->getMessage(),$sku_no);
				continue;
			}
		}
		$this->exitJSON($this->data);
	}

	// 妮素运单同步
	public function nyso_post_syn() {
		set_time_limit(0);
		ini_set("max_execution_time",0);

		// 初始化妮素平台接口
		nysochina::init($this->config());
		
		// 读取所有已经推到妮素平台，但还没有运单信息的妮素订单
		$orderQuery = new IQuery("order");
		$orderQuery->where = "not ISNULL(supplier_syn_date) and distribution_status = 0 and if_del <> 1 and status = 2 and supplier_id = " . self::NYSO_SUPPLIER_ID;
		$orderQuery->fields = "order_no";
		$orders = $orderQuery->find();
		
		if(!$orders) {
			$this->info("没有找到需要同步的妮素云单");
			// 以JSON形式输出执行结果
			$this->exitJSON($this->data);
		}

		$nysoOrders = array();
		foreach($orders as $order) {
			$nysoOrders[] = self::NYSO_ORDER_PRE . $order['order_no'];
		}


		try {
			// 通过妮素运单API读取指定订单的运单信息
			$nysoPosts = nysochina::getPosts($nysoOrders);
		}
		catch (Exception $e) {
			$this->error( $e->getMessage(),$nysoOrders);
			// 以JSON形式输出执行结果
			$this->exitJSON($this->data);			
		}


		// 对每个运单做如下处理
		$orderNos = array();
		foreach($nysoPosts as $post){
			try {			
				// 将妮素运单数据转换为九猫运单数据
				$orderNos[] = $this->nysoPost2DeliveryDoc($post);

			}
			catch (Exception $e) {
				$this->error($e->getMessage(),$post);
				continue;
			}
		}

		// 以JSON形式输出执行结果
		$size = count($orderNos);
		$this->info("有{$size}个订单的运单同步成功", $orderNos);		
		$this->exitJSON($this->data);
	}

	// 将妮素运单数据转换为九猫运单数据
	//     {
	//         "SendDate":"2016-10-20 12:00:00.0",
	//         "PostNo":"807896321329",
	//         "OrderNo":"JM-2014O20610196365",
	//         "PostCode":"yuantong", // 圆通
	//     }
	protected function nysoPost2DeliveryDoc($nysoPost) {
		$jcOrderNo = str_replace(self::NYSO_ORDER_PRE, "", $nysoPost['OrderNo']);
		$orderDB = new IModel("order");
		$orderObj = $orderDB->getObj("order_no = '$jcOrderNo' and supplier_id = 1 ");


		$freightId = $this->nysoPostCodet2freightId($nysoPost['PostCode']);
		$deliveryDocData = array();

		$deliveryDocData['order_id'] = $orderObj['id'];
		$deliveryDocData['user_id'] = $orderObj['user_id'];
		$deliveryDocData['name'] = $orderObj['accept_name'];
		$deliveryDocData['postcode'] = $orderObj['postcode'];
		$deliveryDocData['telphone'] = $orderObj['telphone'];
		$deliveryDocData['country'] = $orderObj['country'];
		$deliveryDocData['province'] = $orderObj['province'];
		$deliveryDocData['city'] = $orderObj['city'];
		$deliveryDocData['area'] = $orderObj['area'];
		$deliveryDocData['address'] = $orderObj['address'];

		$deliveryDocData['mobile'] = $orderObj['mobile'];
		$deliveryDocData['time'] = $nysoPost['SendDate'];	// 货单发送时间	
		$deliveryDocData['freight'] = $orderObj['real_freight'];
		$deliveryDocData['delivery_code'] = $nysoPost['PostNo'];	// 物流单号
		$deliveryDocData['delivery_type'] = 5;				// 物流方式 对应deliver表的：1号货仓
		$deliveryDocData['note'] = $orderObj['postscript'];
		$deliveryDocData['freight_id'] = $freightId;


		// 将九猫运单数据保存到 delivery_doc
		// $orderDB = new IModel("order");
		$deliveryDocDB = new IModel("delivery_doc");
		$deliveryDocObj = $deliveryDocDB->getObj("order_id = " . $deliveryDocData['order_id'] . " and '" . $deliveryDocData['delivery_code'] ."'");

		if($deliveryDocObj) {
			$deliveryDocDB->setData($deliveryDocData);
			$deliveryDocDB->update("id=" . $deliveryDocObj['id']);
		} else {
			$deliveryDocDB->setData($deliveryDocData);
			$deliveryDocDB->add();
		}

		// 修改order表
		$orderObj['supplier_syn_date'] =  $nysoPost['SendDate'];	// 发货日期
		$orderObj['distribution_status'] = '1'; //配送状态 0：未发送,1：已发送,2：部分发送
		$orderDB->setData($orderObj);
		$orderDB->update("id = ". $orderObj['id']);

		return $jcOrderNo;
	}

	// 将妮素PostCode转为对应的快递公司ID
	protected function nysoPostCodet2freightId($nysoPostCode) {
		$nysoPostCodeDef = array(
			"shentong" => "STO",//申通
			"yunda" => "YD",//韵达
			"yuantong" => "YTO",//圆通
			"zhongtong" => "ZTO",//中通
			"ems" => "EMS",//EMS
			"shunfeng" => "SF",//顺丰
			"huitongkuaidi" => "HTKY",//百世快递
			"youzheng" => "YZPY",//中国邮政 邮政平邮/小包
			"debangwuliu" => "DBL",//德邦
			"guotongkuaidi" => "GTO",//国通
			"kuaiyuntong" => "ZTKY",//快运通 中铁快运
		);
		if(!isset($nysoPostCodeDef[$nysoPostCode]))
		{
			return 0;
		}

		$freightType = $nysoPostCodeDef[$nysoPostCode];
		$freightCompanyDB = new IModel("freight_company");
		$freightCompanyObj = $freightCompanyDB->getObj("freight_type = '$freightType'");
		if(!$freightCompanyObj) {
			return 0;
		}
		return $freightCompanyObj['id'];
	}

	// 输出INFO日志
	protected function info($msg, $obj = null){
		if(!isset($this->data['info']))
		{
			$this->data['info'] = "";
		}
		$this->data['info'] .= JSON::encode(array('msg' => $msg, 'obj'=>$obj));
		nysochina::$log->info($msg, array('obj'=>$obj));
	}

	// 输出错误日志
	protected function error($msg, $obj = null){
		if(!isset($this->data['error']))
		{
			$this->data['error'] = "";
		}
		$this->data['error'] .= JSON::encode(array('msg' => $msg, 'obj'=>$obj));
		nysochina::$log->err($msg, array('obj' => $obj));
	}

	/**
	 * @brief 插件名字
	 * @return string
	 */
	public static function name()
	{
		return "妮素数据同步插件";
	}

	/**
	 * @brief 插件描述
	 * @return string
	 */
	public static function description()
	{
		return "妮素商品数据导入到九猫商城系统中，管理后台可以用";
	}

	//插件默认配置
	public static function configName()
	{
		return 	array(
			'nyso_server'       => array("name" => "服务器地址","type" => "text","pattern" => "required", "value"=>"http://121.41.84.251:9090"),
			'nyso_parenter'     => array("name" => "ParenterId","type" => "text","pattern" => "required", "value"=>"1161_651"),
			'nyso_userkey'      => array("name" => "UserKey","type" => "text","pattern" => "required", "value"=>"jiumaojiatest"),
			'nyso_supParenter'  => array("name" => "供货商ID","type" => "text","pattern" => "required", "value"=>"830"),
			'nyso_supUserKey'   => array('name' => "供货商Key", "type" => "text", "pattern" => "required", "value" => "b7309622ccb4476db49102a4b89a5bea"),

			// 妮素平台订单接口
			"AddOrder" => array("name" => "订单新增接口","type" => "text","pattern" => "required", "value" => "/api/AddOrder.shtml"),
			"PostSynchro" => array("name" => "运单同步接口","type" => "text","pattern" => "required", "value" => "/api/PostSynchro.shtml"),
			"SkuSynchro" => array("name" => "商品同步接口","type" => "text","pattern" => "required", "value" => "/api/SkuSynchro.shtml"),
			"StockSynchro" => array("name" => "库存同步接口","type" => "text","pattern" => "required", "value" => "/api/StockSynchro.shtml"),
	        "AddOrderKafkaTemp" =>  array("name" => "异步新增订单接口","type" => "text","pattern" => "required", "value" => "/api/AddOrderKafkaTemp.shtml"),

	        // 妮素平台供应商接口
			"searchOrder" => array("name" => "供应商订单抓取接口","type" => "text","pattern" => "required", "value" => "/api/sup/searchOrder.shtml"),
			"orderDelivery" => array("name" => "供应商订单发货接口","type" => "text","pattern" => "required", "value" => "/api/sup/orderDelivery.shtml"),
			"supGoodsSynchro" => array("name" => "供应商商品同步接口","type" => "text","pattern" => "required", "value" => "/api/sup/supGoodsSynchro.shtml"),
			"supStockSynchro" => array("name" => "供应商库存同步接口","type" => "text","pattern" => "required", "value" => "/api/sup/supStockSynchro.shtml"),


			// 调试标志
			"debug" => array("name" => "接口调试信息",
				"type" => "select","value" => array("不输出调试信息" => "false", "输出调试信息" => "true")),
		);
	}	
}
