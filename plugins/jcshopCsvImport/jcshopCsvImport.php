<?php
/**
 * @brief CSV商品导入插件
 * @author nswe
 * @date 2016/3/9 0:42:24
 */
class jcshopCsvImport extends pluginBase
{
	private $seller_id;
	private $csvType;
	private $category;
	private $pluginDir;
	private $log;
	private $exchange_rate_jp;
	private $imageDir;

	private $goodsCsvHelper;

	//注册事件
	public function reg()
	{
		//后台管理
		plugin::reg("onSystemMenuCreate",function(){
			$link = "/plugins/jcshop_csv_import";
			Menu::$menu["插件"]["插件管理"][$link] = $this->name();
		});

		plugin::reg("onBeforeCreateAction@plugins@jcshop_csv_import",function(){
			self::controller()->jcshop_csv_import = function(){$this->redirect("jcshopDataImport");};
		});
		plugin::reg("onBeforeCreateAction@plugins@doJcshopCsvImport",function(){
			self::controller()->doJcshopCsvImport = function(){$this->csvImport("jcshopDataImport");};
		});
	}

	/**
	 * @brief 开始运行
	 */
	public function csvImport($returnUrl)
	{
		set_time_limit(0);
		ini_set("max_execution_time",0);
		$this->seller_id = self::controller()->seller ? self::controller()->seller['seller_id'] : 0;

		$this->csvType  = IReq::get('csvType');
		$this->category = IFilter::act(IReq::get('category'),'int');
		$this->pluginDir= $this->path();

		$this->log = new IFileLog("jcshopCsvImport.log");

		if(!class_exists('ZipArchive'))
		{
			die('服务器环境中没有安装zip扩展，无法使用此功能');
		}

		if(extension_loaded('mbstring') == false)
		{
			die('服务器环境中没有安装mbstring扩展，无法使用此功能');
		}

		//处理上传
		$uploadInstance = new IUpload(9999999,array('zip'));
		$uploadCsvDir   = 'runtime/cvs/'.date('YmdHis');
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

		// 加载site_config文件并读取日币对人民币汇率
		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		$this->exchange_rate_jp = isset($site_config['exchange_rate_jp'])?floatval($site_config['exchange_rate_jp']):1.0;

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

		if($this->csvType !== "jcshop") {
			die("请选择九猫商品数据包");
		}


		$this->goodsCsvImport($zipDir, $imageDir);

// 		$this->productsCsvImport($zipDir, $imageDir);

		$data['msg'] = "导入完毕";
		//清理csv文件数据
		IFile::rmdir($uploadCsvDir,true);
		$this->redirect($returnUrl, $data);
	}

	protected function log($msg) {
		$this->log->write($msg);
	}

	protected function error($msg, $context=null){
		$context_msg = print_r($context, true);
		$this->log->write("ERROR: $msg \n [$context_msg ]" );
	}
	
	private function goodsCsvImport($zipDir, $imageDir) {

		$goodsFilename = $zipDir . "/goods.csv";

		if(!file_exists($goodsFilename)) {
			die("无法找到goods.csv文件");
			return false;
		}


		/* goods.csv */
		require_once($this->pluginDir.'jcshopGoodsCsvHelper.php');


		$config = array('csvFile' => $goodsFilename,
			'targetImagePath' => $imageDir,
			'exchange_rate_jp' => $this->exchange_rate_jp,
			);

		$this->goodsCsvHelper = new jcshopGoodsCsvHelper($config);

		
		//从csv中解析数据
		$collectData = $this->goodsCsvHelper->collect();


		$this->processGoodsData($collectData);

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

	// 将商品数据插入数据库
	private function processGoodsData($collectData) {

		$titleToCols = array(
			'brand.name' 	 	=> '品牌名',
			'goods_no'	 		=> '商品JAN编码',
			'name'       		=> '商品名称',
			'category.name'   	=> '商品类目',
			'sell_price' 		=> '销售价格',
			'jp_market_price' 	=> '日本市场价格',
			'store_nums' 		=> '库存数量',
			'content'    		=> '商品详情',
			'reset_img'        	=> '重设图片',
			'spec' 				=> '规格',
			'spec_jp' 			=> '规格日文',
			'sku_no'			=> '规格编号',
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
		$productObject      = new IModel('product');
		$photoRelationDB 	= new IModel('goods_photo_relation');
		$photoDB         	= new IModel('goods_photo');
		$cateExtendDB    	= new IModel('category_extend');
		$brandDB 		 	= new IModel('brand');
		$commendDB			= new IModel('commend_goods');
		
		//默认图片路径
		$default_img 		= 'upload/goods_pic/nopic.jpg';
		
		
		//插入商品表
		foreach($collectData as $key => $val)
		{
			//更新GOODS表数据
			$theData = array(
				'seller_id'    => $this->seller_id,
			);
		
			// 商品JAN编码
			$field = trim($val[$titleToCols['goods_no']]);
			if('' === $field) {
				$this->error("商品JAN编码为空：" . JSON::encode($val));
				continue;				
			}

			// 规格编号
			$field = trim($val[$titleToCols['sku_no']]);
			if('' === $field) {
				$this->error("规格编号为空：" . JSON::encode($val));
				continue;				
			}
			
			$goods_no = IFilter::act($field, "string", 20);
			$theData['goods_no'] = $goods_no;

			// 品牌名
			$field = trim($val[$titleToCols['brand.name']]);
			if('' !== $field) {
				$brandName = IFilter::act($field, "string");
				$brandObj = $brandDB->getObj("name = '" . $brandName . "'" );
				if($brandObj) {
					$theData['brand_id'] = $brandObj['id'];
				}
			}

			// 查询商品是否存在, 如果商品存在，则$the_goods是该商品记录，否则为false
			$where = "goods_no = '$goods_no' and supplier_id = 0"; // supplier_id = 0：九猫
			$the_goods = $goodsObject->getObj($where);

			// 如果商品名称不为空，则修改商品名
			$field = trim($val[$titleToCols['name']]);
			if('' !== $field) {
				$theData['name'] = IFilter::act($field, "string");
			}


			// 如果日本市场价格不为空，则修改日本市场价格
			$field = trim($val[$titleToCols['jp_market_price']]);
			if('' !== $field) {
				$jp_market_price = IFilter::act($field,'float');
				$theData['jp_market_price']  = $jp_market_price;
				$sell_price = $jp_market_price / $this->exchange_rate_jp;
				$theData['sell_price']  =$sell_price;
				if ($sell_price <= 200) {
					$theData['market_price']  = $theData['sell_price'] * 2 ;
				} else {
					$theData['market_price']  = $theData['sell_price']* 1.5 ;					
				}
			}
			
			//直接修改商品售价
			$field = trim($val[$titleToCols['sell_price']]);
			if('' !== $field) {
				$sell_price = IFilter::act($field,'float');
				$theData['sell_price']  = $sell_price;
			}

			// 处理库存数
			$field = trim($val[$titleToCols['store_nums']]);
			if('' !== $field) {
				$theData['store_nums'] = IFilter::act($field,'int');
			}

			// 商品详情
			$field = trim($val[$titleToCols['content']], '"\' ');
			if('' !== $field) {
				$theData['content'] = IFilter::addSlash($field);
			}


			//处理商品关键词
			$tag 					= trim($val[$titleToCols['tag']]);
			if($tag != ''){
				$theData['search_words'] = ','.$tag.',';
				keywords::add($tag);
			}

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
				$theData['weight'] = $weight;
			}

			// 商品名称日文
			$field = trim($val[$titleToCols['name_jp']], '"\' ');
			if('' !== $field) {
				$theData['name_jp'] = IFilter::act($field, "string");
				if(!isset($theData['name']) && ($the_goods?'' == $the_goods['name']:true)) {
					$theData['name'] = $theData['name_jp'];
				}
			}

			// 商品详情日文
			$field = trim($val[$titleToCols['content_jp']], '"\' ');
			if('' !== $field) {
				$theData['content_jp'] = IFilter::addSlash($field);
				if(!isset($theData['content']) && ($the_goods?'' == $the_goods['content']:true)) {
					$theData['content'] = $theData['content_jp'];
				}
			}

			// 处理上下架状态
			$field = trim($val[$titleToCols['is_del']]);
			if('' !== $field) {
				$theData['is_del'] = IFilter::act($field,'int');
				switch($theData['is_del']) 
				{
				case 0:
					$theData['up_time'] = ITime::getDateTime();
					$theData['down_time'] = ITime::getDateTime('0000-00-00 00:00:00');
					break;
				case 1:
					$theData['up_time'] = ITime::getDateTime('0000-00-00 00:00:00');
					$theData['down_time'] = ITime::getDateTime();
					break;
				case 2:
					$theData['up_time'] = ITime::getDateTime('0000-00-00 00:00:00');
					$theData['down_time'] = ITime::getDateTime();
					break;
				}
			}

			// 日本价格
			$field = trim($val[$titleToCols['jp_price']]);
			if('' !== $field) {
				$theData['jp_price'] = IFilter::act($field,'int');
			}

			// 有中文商品标题
			$field = trim($val[$titleToCols['is_zh_title']]);
			if('' !== $field) {
				$theData['is_zh_title'] = IFilter::act($field, 'bool');
			}

			// 有中文商品详情
			$field = trim($val[$titleToCols['is_zh_content']]);
			if('' !== $field) {
				$theData['is_zh_content'] = IFilter::act($field, 'bool');
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
					$theData['img'] = $mainPic[0];
				}else{
					//不存在图片时，保存默认图片
					$theData['img'] 	= $default_img;
				}
			}


			if( $the_goods ) {
				// 商品已经存在
				// 更新GOODS表数据
				$updateData = $theData;

				$updateData['goods_no'] = $goods_no;

				$goodsObject->setData($theData);
				
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
				$insertData = $theData;

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

			// 处理日文规格
			$specs = $val[$titleToCols['spec_jp']];
			$productsDB = new IModel("products");

			foreach($specs as $k => $spec) {
				$spec_json = JSON::encode($spec['spec']);
				$where = "goods_id=$goods_id and spec_array_id='$spec_json'"; 
				$theProduct = $productsDB->getObj($where);

				if($theProduct) {
					// 更改
					$product['spec_array_id'] = JSON::encode($spec['spec']);
					$product['spec_array'] = JSON::encode($this->goodsCsvHelper->specId2SpecVal($spec['spec']));
					if(isset($spec['jp_price']) && $spec['jp_price']) {
						$product['jp_price'] = IFilter::act($spec['jp_price'],'float');;
						$sell_price = $product['jp_price'] / $this->exchange_rate_jp;
						$product['sell_price']  = $sell_price;
						if ($sell_price <= 200) {
							$product['market_price']  = $product['sell_price'] * 2 ;
						} else {
							$product['market_price']  = $product['sell_price']* 1.5 ;
						}
					}
					$product['store_nums'] = 5;
					$product['weight'] = 100;					
					$productsDB->setData($product);
					$productsDB->update($where);
				} else {
					// 增加记录
					$product['products_no'] = "$goods_no-$k" ;
					$product['goods_id'] = $goods_id;
					$product['spec_array_id'] = JSON::encode($spec['spec']);
					$product['spec_array'] = JSON::encode($this->goodsCsvHelper->specId2SpecVal($spec['spec']));
					if(isset($spec['jp_price']) && $spec['jp_price']) {
						$product['jp_price'] = IFilter::act($spec['jp_price'],'float');;
						$sell_price = $product['jp_price'] / $this->exchange_rate_jp;
						$product['sell_price']  = $sell_price;
						if ($sell_price <= 200) {
							$product['market_price']  = $product['sell_price'] * 2 ;
						} else {
							$product['market_price']  = $product['sell_price']* 1.5 ;
						}
					}
					$product['store_nums'] = 5;
					$product['weight'] = 100;
					
					$productsDB->setData($product);
					$productsDB->add();
				}
			}
		}
	}

	// TODO:
	private function productsCsvImport($zipDir, $imageDir) {
		
		$productsFilename = $zipDir . "/products.csv";

		if(!file_exists($productsFilename)) {
			return false;
		}

		/* products.csv */
		include_once($pluginDir.'jcshopProductsCsvHelper.php');
		$productsCsvHelper = new jcshopProductsCsvHelper($productsFilename ,$imageDir);

	}


	/**
	 * @brief 插件名字
	 * @return string
	 */
	public static function name()
	{
		return "九猫商品数据导入";
	}

	/**
	 * @brief 插件描述
	 * @return string
	 */
	public static function description()
	{
		return "九猫CSV数据包直接导入到iWebShop系统中，管理后台可以用";
	}
}
