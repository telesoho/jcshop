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
		$imageDir= IWeb::$app->config['upload'].'/'.date('Y/m/d');
		file_exists($imageDir) ? '' : IFile::mkdir($imageDir);

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

		// $this->productsCsvImport($zipDir, $imageDir);
		
		//清理csv文件数据
		IFile::rmdir($uploadCsvDir,true);
		$this->redirect($returnUrl);
	}

	//注册事件
	public function reg()
	{
		//后台管理
		plugin::reg("onSystemMenuCreate",function(){
			$link = "/plugins/jcshopCsvImport";
			Menu::$menu["插件"]["插件管理"][$link] = $this->name();
		});

		plugin::reg("onBeforeCreateAction@plugins@jcshopCsvImport",function(){
			self::controller()->jcshopCsvImport = function(){$this->redirect("jcshopCsvImport");};
		});
		plugin::reg("onBeforeCreateAction@plugins@csvImport",function(){
			self::controller()->csvImport = function(){$this->csvImport("jcshopCsvImport");};
		});
	}

	
	private function goodsCsvImport($zipDir, $imageDir) {


		$goodsFilename = $zipDir . "/goods.csv";

		if(!file_exists($goodsFilename)) {
			return false;
		}

		/* goods.csv */
		include_once($pluginDir.'jcshopGoodsCsvHelper.php');
		$goodsCsvHelper = new jcshopGoodsCsvHelper($goodsFilename ,$imageDir);

		//从csv中解析数据
		$collectData = $goodsCsvHelper->collect();


		$this->processGoodsData($collectData);

	}

	// 将商品数据插入数据库
	private function processGoodsData($collectData) {

		$titleToCols = array(
			'name'       => '商品名称',
			'goods_no'	 => '商品JAN编码',
			'sell_price' => '销售价格',
			'store_nums' => '库存数量',
			'content'    => '商品详情',
			'img'        => '图片',
			'spec_array' => '销售属性',
			'weight'     => '物流重量',
		);		

		//实例化商品
		$goodsObject     = new IModel('goods');
		$photoRelationDB = new IModel('goods_photo_relation');
		$photoDB         = new IModel('goods_photo');
		$cateExtendDB    = new IModel('category_extend');

		//插入商品表
		foreach($collectData as $key => $val)
		{
			//插入GOODS表数据
			$insertData = array(
				'name'         => IFilter::act(trim($val[$titleToCols['name']],'"\'')),
				'goods_no'     => IFilter::act(trim($val[$titleToCols['goods_no']])),
				'sell_price'   => IFilter::act($val[$titleToCols['sell_price']],'float'),
				'market_price' => IFilter::act($val[$titleToCols['sell_price']],'float'),
				'is_del'       => '3',  /* 1：删除 2：下架 3:待审*/
				'up_time'      => ITime::getDateTime('0000-00-00 00:00:00'),
				'down_time'    => ITime::getDateTime(),
				'create_time'  => ITime::getDateTime(),
				'store_nums'   => IFilter::act($val[$titleToCols['store_nums']],'int'),
				'content'      => IFilter::addSlash(trim($val[$titleToCols['content']])),
				'img'          => isset($val['mainPic']) ? current($val['mainPic']) : '',
				'seller_id'    => $this->seller_id,
				'weight'       => $val[$titleToCols['weight']],
			);

			// var_dump($insertData);

			$goodsObject->setData($insertData);
			$goods_id = $goodsObject->add();


			//处理商品分类
			if($this->category)
			{
				foreach($this->category as $catId)
				{
					$cateExtendDB->setData(array('goods_id' => $goods_id,'category_id' => $catId));
					$cateExtendDB->add();
				}
			}

			//处理商品图片
			if(isset($val['mainPic']) && $val['mainPic'])
			{
				foreach($val['mainPic'] as $photoFile)
				{
					if(!is_file($photoFile))
					{
						continue;
					}
					$md5Code = md5_file($photoFile);
					$photoRow= $photoDB->getObj('id = "'.$md5Code.'"');
					if(!$photoRow || !is_file($photoRow['img']))
					{
						$photoDB->del('id = "'.$md5Code.'"');

						$photoDB->setData(array("id" => $md5Code,"img" => $photoFile));
						$photoDB->add();
					}
					$photoRelationDB->setData(array('goods_id' => $goods_id,'photo_id' => $md5Code));
					$photoRelationDB->add();
				}
			}
		}
	}

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
