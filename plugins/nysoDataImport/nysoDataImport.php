<?php
/**
 * @brief 妮素商品导入插件
 * @author twh
 * @date 2017/1/13
 */
class nysoDataImport extends pluginBase
{

	private $exchange_rate_jp;	// 日币对人民币汇率

	//注册事件
	public function reg()
	{
		//后台管理
		plugin::reg("onSystemMenuCreate",function(){
			$link = "/plugins/nyso_data_import"; // 插件链接必须小写字母，否则后台菜单会出现无法选中的问题
			Menu::$menu["插件"]["插件管理"][$link] = $this->name();
		});

		// 注册妮素登录画面
		plugin::reg("onBeforeCreateAction@plugins@nyso_data_import",function(){
            self::controller()->nyso_data_import = function(){
				$this->redirect("nysoDataImport");
			};
		});

		// 注册商品同步接口
		plugin::reg("onBeforeCreateAction@plugins@nyso_goods_syn",function(){
            self::controller()->nyso_goods_syn = function(){
				$this->nysoGoodsSyn();
			};
		});

		// 注册库存同步接口
		plugin::reg("onBeforeCreateAction@plugins@nyso_stock_syn",function(){
            self::controller()->nyso_stock_syn = function(){
				$this->nysoStockSyn();
			};
		});

		// 注册订单接口
		plugin::reg("onBeforeCreateAction@plugins@nyso_order_syn",function(){
            self::controller()->nyso_order_syn = function(){
				$this->nysoOrderSyn();
			};
		});

	}

	/**
	 * 妮素订单同步
	 * 将包含妮素商品的订单同步到妮素平台，并设置同步标志
	 */
	private function nysoOrderSyn() {
		// 初始化妮素平台接口
		nysochina::init($this->config());

		$query         = new IQuery('order AS o');
		$query->join   = 'LEFT JOIN areas AS a1 ON o.province = a1.area_id '
						.'LEFT JOIN areas AS a2 ON o.city = a2.area_id '
						.'LEFT JOIN areas AS a3 ON o.area = a3.area_id '
						.'LEFT JOIN user AS u on o.user_id = u.id';
		$query->where  = 'o.supplier_id = 1 and not o.syn_flg and pay_status = 1';
		$query->fields = 'a1.area_name as province_name, a2.area_name as city_name, a3.area_name as area_name,'
						.'u.sfz_name as payer_name, u.sfz_num as payer_id_card, o.*';
		$jcOrderList   = $query->find();


		$msg = "";
		foreach($jcOrderList as $jcOrder) {
			try {
				$nysoOrder = $this->toNysoOrder($jcOrder);
				$msg .= nysochina::addOrder($nysoOrder);
			} catch(Exception $e) {
				$msg = $e->getMessage();
				nysochina::$log->err($e->getMessage(), $jcOrder);
			}
		}

		if(!$msg) {
			$msg = "没有符合条件的订单";
		}

		$data['msg'] = $msg;
		$this->redirect("nysoDataImport", $data);
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
	 * @return 妮素订单
	 */
	private function toNysoOrder($jcOrder) {
		$nysoOrder = array();

		$orderNo = "JC-". $jcOrder["order_no"];					// 订单号形式："JC-" + 九猫商城订单号
        $nysoOrder["OrderNo"] = $orderNo;						// 订单号
		$nysoOrder["OrderTime"] = date("YmdHis",strtotime($jcOrder["pay_time"]));	// 下单时间
        $nysoOrder["PayType"] = $this->toNysoPayType($jcOrder["pay_type"]);		// 支付方式
        $nysoOrder["PayNo"] = "";								// 支付流水号
        $nysoOrder["Nick"] = "";								// 买家昵称
		$nysoOrder["ConsigneeNumber"] = $jcOrder["mobile"];		// 收货人电话
		$nysoOrder["ConsigneeName"] = $jcOrder["accept_name"];		// 收货人姓名

        $nysoOrder["PayerName"] = $jcOrder["payer_name"];			// 支付人姓名 支付人用于报关，不填默认为收货人
		$nysoOrder["IdCard"] = $jcOrder["payer_id_card"];			// 支付人身份证号
        $nysoOrder["Province"] = $jcOrder["province_name"];			// 省
        $nysoOrder["City"] = $jcOrder["city_name"];					// 城市
        $nysoOrder["District"] = $jcOrder["area"];					// 区
        $nysoOrder["DetailedAddres"] = $jcOrder["address"];			// 收货详细地址信息

        $nysoOrder["Remark"] = "九猫家订单";							// 备注
		$nysoOrder["PostalPrice"] = $jcOrder["payable_freight"];	// 邮费
        $nysoOrder["Favourable"] = "0";					        	// 优惠金额
		$nysoOrder["Tax"] = $jcOrder["duties"];         				// 商品税费		
		$nysoOrder["GoodsPrice"] = $jcOrder["real_amount"]; 		// 货值
		$nysoOrder["OrderPrice"] = $jcOrder["order_amount"];  		// 订单总价 用户实际交易金额


		// 查询订单商品清单
		$query = new IQuery("order_goods AS og");
		$query->where = "og.order_id =" . $jcOrder["id"];
		$query->join = "LEFT JOIN goods AS g ON g.id = og.goods_id ";
		$query->fields = "g.supplier_sku_no, g.goods_no, g.name, g.ware_house_name,  g.content, g.delivery_code, g.supplier_id, g.delivery_city,g.duties_rate,"
						."og.*";
		$jcItemList    = $query->find();

		$ware_house_name = "";
		$delivery_code = "";

		$items = array();
		foreach($jcItemList as $jcItem) {
			// 订单明细		
			$item["SkuNo"] = $jcItem["supplier_sku_no"];						// 商品编码
			$item["BuyQuantity"] = $jcItem["goods_nums"];			// 购买数量
			$item["Tax"] = $jcItem["duties"];						// 商品关税税费 已经算过数量，单位：元
			$item["BuyPrice"] = $jcItem["real_price"];  			// 未算过数量的购买商品时的单价

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
	 *  商品数据同步
	 */
	public function nysoGoodsSyn() {
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
		
		$this->redirect("nysoDataImport");
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
		
		// 初始化妮素平台接口
		nysochina::init($this->config());
		
		// 通过妮素接口取出商品数据
		$skus_str = IFilter::string(IReq::get('skus'));
		$skus = explode(",",$skus_str);

		$data["skus"] = $skus_str;

		try {
			$result = nysochina::getGoods($skus);
			$goodsList = json_decode($result);
		} catch(Exception $e) {
			$data["msg"] = $e->getMessage();
			$this->redirect("nysoDataImport", $data);
		}

		// 将妮素商品数据导入九猫系统
		$data['msg'] = "";
		foreach($goodsList as $goods) {
			try {
				$data['msg'] .= $this->saveGoods($goods);
			} catch(Exception $e) {
				$data['msg'] .= $e->getMessage();
				nysochina::$log->err($e->getMessage(), $goods);
			}
		}

	}

	/**
	 * 将妮素商品数据转换为jcshop商品数据
	 * @param $goods 妮素商品数据
	 * @return jcshop商品数据
	 */
	private function nyso2jcshopGoods($goods) {
		$jcGoods = array(
			'delivery_city' => $goods->DeliveryCity,	// 发货地
		    'goods_no' => $goods->BarCode,				// 条形码
			'content' => $goods->Details,				// 商品详情
			'weight' => $goods->Weight,					// 重量
			'supplier_sku_no' => $goods->SkuNo,					// 商品SKU
			'duties_rate' => $goods->Rate,				// 税率,非保税区这个字段为0
			'supplier_id' => 1,							// 商品来源, 1=妮素
			'delivery_code' => $goods->DeliveryCode,	// 发货方式：1-报税区 2-香港直邮 4-日本直邮
			'ware_house_name' => $goods->WareHouseName,	// 仓位名
			'sell_price' => $goods->RetailPrice,		// 销售价格
			'market_price' => $goods->RetailPrice,		// 市场价格
			'purchase_price'  => $goods->SettlePrice, 	// 结算价
			'jp_price' => round($goods->RetailPrice * $this->exchange_rate_jp),
			'jp_market_price' => round($goods->RetailPrice * $this->exchange_rate_jp),
			'name'		=> $goods->SkuName,				// 商品名
		);

		return $jcGoods;
	}

	/**
	 * @brief 保存商品数据
	 * @param $goods
	 */
	protected function saveGoods($goods) {
		$jcGoods = $this->nyso2jcshopGoods($goods);

		$goods_obj = new IModel('goods');

		$where = 'supplier_id = 1 and supplier_sku_no= "' . $goods->SkuNo . '"';
		$theGoods = $goods_obj->getObj($where);

		if($theGoods) {
			// 如果已经存在商品，则更新商品信息
			$updateData = array_merge($theGoods, $jcGoods);
			$updateData['is_del'] = '3';	// 下架
			$goods_obj->setData($updateData);
			$goods_obj->update($where);
			return 'UPD:' . print_r($updateData, true);
		} else {
			// 商品不存在，则新增该商品
			$newData = $jcGoods;
			$newData['is_del'] = '2';	// 下架
			$goods_obj->setData($newData);
			$goods_obj->add();
			return "ADD:" . print_r($newData, true);
		}
	}

	/**
	 * 同步商品库存
	 * @param $stock 商品库存
	 */
	protected function updateStock($stock) {

		$goods_obj = new IModel('goods');

		$where = 'supplier_id = 1 and supplier_sku_no= "' . $stock->SkuNo . '"';
		$theGoods = $goods_obj->getObj($where);

		if($theGoods) {
			// 如果已经存在商品，则更新商品信息
			$theGoods['store_nums'] = $stock->Quantity;	// 设置库存
			$goods_obj->setData($theGoods);
			$goods_obj->update($where);
			return 'UPD:' . print_r($theGoods, true) . print_r($stock, true);
		} else {
			// 商品不存在，返回错误
			return "ERROR：商品不存在" . print_r($stock, true);
		}
	}

	/**
	 * 妮素库存同步接口
	 */
	public function nysoStockSyn() {
		// 初始化妮素平台接口
		nysochina::init($this->config());
		
		// 通过妮素接口取出商品库存数据
		$skus_str = IFilter::string(IReq::get('skus'));
		$skus = explode(",",$skus_str);

		$data["skus"] = $skus_str;

		try {
			$result = nysochina::getStocks($skus);
			$stockList = json_decode($result);
		} catch (Exception $e) {
			$data["msg"] = $e->getMessage();
			$this->redirect("nysoDataImport", $data);
		}


		// 将库存保存到九猫数据库
		$data['msg'] = "";
		foreach($stockList as $stock) {
			try {
				$data['msg'] .= $this->updateStock($stock);
			} catch (Exception $e) {
				nysochina::$log->err($e->getMessage(), $stock);
			}
		}

		$this->redirect("nysoDataImport", $data);
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
			'nyso_server'     => array("name" => "服务器地址","type" => "text","pattern" => "required", "value"=>"http://121.41.84.251:9090"),
			'nyso_userkey'     => array("name" => "UserKey","type" => "text","pattern" => "required", "value"=>"jiumaojiatest"),
			'nyso_parenter'     => array("name" => "ParenterId","type" => "text","pattern" => "required", "value"=>"1161_651"),
			"AddOrder" => array("name" => "订单新增接口","type" => "text","pattern" => "required", "value" => "/api/AddOrder.shtml"),
			"PostSynchro" => array("name" => "运单同步接口","type" => "text","pattern" => "required", "value" => "/api/PostSynchro.shtml"),
			"SkuSynchro" => array("name" => "商品同步接口","type" => "text","pattern" => "required", "value" => "/api/SkuSynchro.shtml"),
			"StockSynchro" => array("name" => "库存同步接口","type" => "text","pattern" => "required", "value" => "/api/StockSynchro.shtml"),
		);
	}	
}
