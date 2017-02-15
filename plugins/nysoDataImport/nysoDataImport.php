<?php
/**
 * @brief 妮素商品导入插件
 * @author twh
 * @date 2017/1/13
 */
class nysoDataImport extends pluginBase
{

	private $exchange_rate_jp;	// 日币对人民币汇率
	private $pluginDir;

	private $msg = array('error'=>'', 'info' => '');

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
				$this->pluginDir= $this->path();				
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
		$query->where  = 'o.supplier_id = 1 and isnull(supplier_syn_date) and pay_status = 1';
		$query->fields = 'a1.area_name as province_name, a2.area_name as city_name, a3.area_name as area_name,'
						.'u.sfz_name as payer_name, u.sfz_num as payer_id_card, o.*';
		$jcOrderList   = $query->find();

		if(count($jcOrderList) < 1) {
			$this->info("没有发现妮素订单");
			$this->redirect("nysoDataImport", $this->msg);		
		}

		foreach($jcOrderList as $jcOrder) {
			try {
				$nysoOrder = $this->toNysoOrder($jcOrder);
				nysochina::addOrder($nysoOrder);
				$orderDB = new IModel("order as o");
				$updateOrder['supplier_syn_date'] = date('Y-m-d H:i:s', time());
				$orderDB->setData($updateOrder);
				$orderDB->update("id=" . $jcOrder['id']);
				$this->info($jcOrder['order_no'] . "订单同步成功", $jcOrder);
			} 
			catch(Exception $e) {
				$this->error($e->getMessage() . "=>" .$jcOrder['order_no'], $jcOrder);
				continue;
			}
		}

		$this->redirect("nysoDataImport", $this->msg);
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
		$query->join = "LEFT JOIN " 
						."(select g1.*, gs.delivery_city, gs.duties_rate, gs.delivery_code, gs.ware_house_name" 
						." from goods AS g1 left join goods_supplier as gs on g1.supplier_id = gs.supplier_id and g1.sku_no = gs.sku_no) as g "
						." ON g.id = og.goods_id ";
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
		
	}

	// private function synNyso() {
	// 	// 初始化妮素平台接口
	// 	nysochina::init($this->config());
		
	// 	// 通过妮素接口取出商品数据
	// 	$skus_str = IFilter::string(IReq::get('skus'));
	// 	$skus = explode(",",$skus_str);

	// 	$data["skus"] = $skus_str;

	// 	try {
	// 		$result = nysochina::getGoods($skus);
	// 		$goodsList = json_decode($result);
	// 	} catch(Exception $e) {
	// 		$data["msg"] = $e->getMessage();
	// 		$this->redirect("nysoDataImport", $data);
	// 	}

	// 	// 将妮素商品数据导入九猫系统
	// 	$data['msg'] = "";
	// 	foreach($goodsList as $goods) {
	// 		try {
	// 			$data['msg'] .= $this->saveGoods($goods);
	// 		} catch(Exception $e) {
	// 			$data['msg'] .= $e->getMessage();
	// 			nysochina::$log->err($e->getMessage(), $goods);
	// 		}
	// 	}
		
	// }

	// 将商品数据插入数据库
	private function processGoodsData($collectData) {

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
		$goodsObject     	= new IModel('goods');
		$goodsSupplierDB    = new IModel('goods_supplier');
		$photoRelationDB 	= new IModel('goods_photo_relation');
		$photoDB         	= new IModel('goods_photo');
		$cateExtendDB    	= new IModel('category_extend');
		$brandDB 		 	= new IModel('brand');
		$commendDB			= new IModel('commend_goods');
		
		//默认图片路径
		$default_img 		= 'upload/goods_pic/nopic.jpg';

		
		// 初始化妮素平台接口
		nysochina::init($this->config());
		
		//插入商品表
		foreach($collectData as $key => $val)
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
		
			// 商品JAN编码
			$field = trim($val[$titleToCols['goods_no']]);
			if(!$auto_syn && '' === $field) {
				$this->error("不设定自动同步标志时商品JAN编码不能为空：" . JSON::encode($val));
				continue;
			}
			
			$goods_no = IFilter::act($field, "string", 20);

			if($auto_syn) {
				// 从接口读取商品
				try{
					$goodsSupplierData = $this->getGoodsFromApi($sku_no);
				} catch(Exception $e) {
					$this->error('从妮素API读取数据失败[' . $sku_no . ']：'. $e->getMessage());
					continue;
				}

				$goods_no = $goodsSupplierData['goods_no'];

				$the_goods_supplier = $goodsSupplierDB->getObj("supplier_id = 1 and sku_no = '". $sku_no . "'");

				if($the_goods_supplier) {
					// 商品已经存在，更新表数据
					$updateData = $goodsSupplierData;

					$goodsSupplierDB->setData($updateData);
					
					$where = "supplier_id = 1 and sku_no = '". $sku_no . "'";

					$qret = $goodsSupplierDB->update($where);

					if( $qret === false) {
						$this->error($where . ":" . JSON::encode($updateData));
						continue;
					}
					
				} else {
					// 商品不存在，插入表数据
					$insertData = $goodsSupplierData;

					$insertData['sku_no'] = $sku_no;

					$goodsSupplierDB->setData($insertData);
					$qret = $goodsSupplierDB->add();

					if(false === $qret){
						$this->error("Insert error:" . JSON::encode($insertData));
						continue;
					}
				}

			} else {
				// 从供应商表中读取妮素商品数据
				$goodsSupplierData = $goodsSupplierDB->getObj("supplier_id = 1 and sku_no = '". $sku_no);
			}

			// 计算商品表数据
			$calGoodsData = $this->calGoodsFromSupplierData($goodsSupplierData);

			//处理CSV数据
			$theCsvData = array(
				'seller_id'    => $this->seller_id,
			);
			
			$theCsvData['goods_no'] = $goods_no;
			$theCsvData['supplier_id'] = $supplier_id;


			// 品牌名
			$field = trim($val[$titleToCols['brand.name']]);
			if('' !== $field) {
				$brandName = IFilter::act($field, "string");
				$brandObj = $brandDB->getObj("name = '" . $brandName . "'" );
				if($brandObj) {
					$theCsvData['brand_id'] = $brandObj['id'];
				}
			}

			// 查询商品是否存在, 如果商品存在，则$the_goods是该商品记录，否则为false
			$where = "goods_no = ". "'". $goods_no . "' and supplier_id = 1"; // supplier_id = 1：妮素
			$the_goods = $goodsObject->getObj($where);

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
			$tag 					= trim($val[$titleToCols['tag']]);
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
				if(!isset($theCsvData['name']) && ($the_goods?'' == $the_goods['name']:true)) {
					$theCsvData['name'] = $theCsvData['name_jp'];
				}
			}

			// 商品详情日文
			$field = trim($val[$titleToCols['content_jp']], '"\' ');
			if('' !== $field) {
				$theCsvData['content_jp'] = IFilter::addSlash($field);
				if(!isset($theCsvData['content']) && ($the_goods?'' == $the_goods['content']:true)) {
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

			$field = trim($val[$titleToCols['reset_img']]);
			if('' !== $field) {

				$goodsImgDir = $this->imageDir . "/" . "mainPic" . "/" . $goods_no;

				if(is_dir($goodsImgDir)) {

					// 以升序排序 - 默认
					$img_files = scandir($goodsImgDir);

					foreach($img_files as $file) {
						if($file != '.' && $file != '..'){
							$source_file =  $goodsImgDir . "/" . $file;
							if(is_file($source_file)) {
								$mainPic[] = $source_file;
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

			if($auto_syn) {
				// 如果自动同步则合并根据供应商表数据填写出的商品表数据
				$theGoodsData = array_merge($calGoodsData, $theCsvData);
			} else {
				$theGoodsData = $theCsvData;
			}

			if( $the_goods ) {
				// 商品已经存在
				// 更新GOODS表数据
				$updateData = $theGoodsData;

				$updateData['goods_no'] = $goods_no;

				$goodsObject->setData($theGoodsData);
				
				$where = "goods_no = ". "'". $goods_no . "'";

				$qret = $goodsObject->update($where);

				if( $qret === false) {
					$this->error($where . ":" . JSON::encode($updateData));
					continue;
				}
				
				$goods_id = $the_goods["id"];

			} else {
				// 商品不存在
				// 插入GOODS表数据
				$insertData = $theGoodsData;

				$insertData['goods_no'] = $goods_no;

				$goodsObject->setData($insertData);
				$goods_id = $goodsObject->add();

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

				// 如果用户选择了分类，则查找需要追加的分类
				if($this->category)
				{
					foreach($this->category as $catId)
					{
						$cids[] = $catId;
					}
				}

				foreach($cids as $cid) {
					// 查询商品是否与分类关联
					$ret = $cateExtendDB->get_count('goods_id =' . $goods_id . ' and category_id =' . $cid);
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
					$photoRelationDB->del('goods_id = '.$goods_id);
					foreach($mainPic as $photoFile) {
						if(!is_file($photoFile))
						{
							continue;
						}
						$md5Code = md5_file($photoFile);
						$photoRow= $photoDB->getObj('id = "'.$md5Code.'"');
						if(!$photoRow || !is_file($photoRow['img']))
						{
							// 如果数据库中找不到对应的图片或者原来的图片已经不存在
							$photoDB->del('id = "'.$md5Code.'"');
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

	/**
	 * 将妮素商品数据转换为jcshop商品数据
	 * @param $goods 妮素商品数据
	 * @return jcshop商品数据
	 */
	private function nyso2jcGoodsSupplier($goods) {
		$jcGoodsSupplierData = array(
			'goods_no' 			=> $goods->BarCode,			// 条形码
			'supplier_id'	 	=> '1',						// 妮素固定为1
			'sku_no' 			=> $goods->SkuNo,			// 商品SKU
			'sku_name' 			=> $goods->SkuName,			// 商品名
			'details'			=> $goods->Details,			// 商品详情
			'settle_price'		=> $goods->SettlePrice,		// 结算价格
			'retail_price'  	=> 	$goods->RetailPrice,	// 参考价
			'sale_type'			=> $goods->SaleType,		// 销售类型
			'weight'			=> $goods->Weight,			// 重量
			'delivery_code'		=> $goods->DeliveryCode,	// 发货方式：1-报税区 2-香港直邮 4-日本直邮
			'duties_rate'   	=> $goods->Rate,			// 税率,非保税区这个字段为0
			'delivery_city' 	=> $goods->DeliveryCity,	// 发货地
			'ware_house_name' 	=> $goods->WareHouseName, 	// 仓位名
		);

		return $jcGoodsSupplierData;
	}

	/**
	 * 根据供应商数据填写对应的Goods表数据
	 * @param $goodsSupplierData goods_supplier表数据
	 * @return goods表数据
	 */
	private function calGoodsFromSupplierData($goodsSupplierData) {
		$goodsData = array(
			'goods_no'			=> $goodsSupplierData['goods_no'],			// 商品JAN
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
	 * @return goods_supplier表数据, goods表数据
	 */
	protected function getGoodsFromApi($sku_no) {
		// 通过妮素接口取出商品数据

		$result = nysochina::getGoods(array($sku_no));
		$goodsList = json_decode($result);

		// 将妮素商品数据转换为JCSHOP商品数据
		return $this->nyso2jcGoodsSupplier($goodsList[0]);
	}

	// /**
	//  * @brief 保存商品数据
	//  * @param $goods
	//  */
	// protected function saveGoods($goods) {
	// 	$jcGoods = $this->nyso2jcshopGoods($goods);

	// 	$goods_obj = new IModel('goods');

	// 	$where = 'supplier_id = 1 and sku_no= "' . $goods->SkuNo . '"';
	// 	$theGoods = $goods_obj->getObj($where);

	// 	if($theGoods) {
	// 		// 如果已经存在商品，则更新商品信息
	// 		$updateData = array_merge($theGoods, $jcGoods);
	// 		$updateData['is_del'] = '3';	// 下架
	// 		$goods_obj->setData($updateData);
	// 		$goods_obj->update($where);
	// 		$this->info($goods->SkuNo . "更新商品成功" , $updateData);
	// 	} else {
	// 		// 商品不存在，则新增该商品
	// 		$newData = $jcGoods;
	// 		$newData['is_del'] = '2';	// 下架
	// 		$goods_obj->setData($newData);
	// 		$goods_obj->add();
	// 		$this->info($goods->SkuNo . "新增商品成功" , $newData);
	// 	}
	// }

	/**
	 * 同步商品库存
	 * @param $stock 商品库存
	 */
	protected function updateStock($stock) {

		$goods_obj = new IModel('goods');

		$where = 'supplier_id = 1 and sku_no= "' . $stock->SkuNo . '"';
		$theGoods = $goods_obj->getObj($where);

		if($theGoods) {
			// 如果已经存在商品，则更新商品信息
			$theGoods['store_nums'] = $stock->Quantity;	// 设置库存
			$goods_obj->setData($theGoods);
			$goods_obj->update($where);
			$this->info($stock->SkuNo . "库存跟新成功", $stock);
		} else {
			// 商品不存在，返回错误
			$this->error($stock->SkuNo . "商品不存在" . $stock);
		}
	}

	/**
	 * 妮素库存同步接口
	 */
	public function nysoStockSyn() {
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
				$result = nysochina::getStocks(array($sku_no['sku_no']));
				$stockList = json_decode($result);

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

		$this->redirect("nysoDataImport", $this->msg);
	}

	// 输出INFO日志
	protected function info($msg, $obj = null){
		$this->msg['info'] .= $msg . "\r\n";
		nysochina::$log->info($msg, array('obj'=>$obj));
	}

	// 输出错误日志
	protected function error($msg, $obj = null){
		$this->msg['error'] .= $msg . "\r\n";
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