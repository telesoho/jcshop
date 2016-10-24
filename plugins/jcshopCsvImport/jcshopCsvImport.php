<?php
/**
 * @brief CSV商品导入插件
 * @author nswe
 * @date 2016/3/9 0:42:24
 */
class jcshopCsvImport extends pluginBase
{
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

	/**
	 * @brief 开始运行
	 */
	public function csvImport($returnUrl)
	{
		set_time_limit(0);
		ini_set("max_execution_time",0);
		$seller_id = self::controller()->seller ? self::controller()->seller['seller_id'] : 0;

		$csvType  = IReq::get('csvType');
		$category = IFilter::act(IReq::get('category'),'int');
		$pluginDir= $this->path();

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

		//实例化商品
		$goodsObject     = new IModel('goods');
		$photoRelationDB = new IModel('goods_photo_relation');
		$photoDB         = new IModel('goods_photo');
		$cateExtendDB    = new IModel('category_extend');

		$dirHandle = opendir($zipDir);
		while(false !== ($fileName = readdir($dirHandle)))
		{
			if(strpos($fileName,'.csv') !== false)
			{
				//创建解析对象
				switch($csvType)
				{
					case "jcshop":
					{
						include_once($pluginDir.'jcshopPacketHelper.php');
						$helperInstance = new jcshopPacketHelper($zipDir.'/'.$fileName,$imageDir);
						$titleToCols    = jcshopTitleToColsMapping::$mapping;
					}
					break;

					default:
					{
						$message = "请选择csv数据包的格式";
						die($message);
					}
				}
				//从csv中解析数据
				$collectData = $helperInstance->collect();

				//插入商品表
				foreach($collectData as $key => $val)
				{
					$collectImage = isset($val[$titleToCols['img']]) ? $val[$titleToCols['img']] : '';

					//有图片处理
					if($collectImage)
					{
						//图片拷贝
						foreach($collectImage as $from => $to)
						{
							$from = str_replace("\\","/",$from);
							$to   = str_replace("\\","/",$to);
							if(!is_file($from))
							{
								continue;
							}
							IFile::xcopy($from,$to);
						}
					}

					//处理商品详情图片
					$toDir = IUrl::creatUrl().dirname($to);
					$goodsContent = preg_replace("|src=\".*?(?=/contentPic/)|","src=\"$toDir",trim($val[$titleToCols['content']],"'\""));

					//插入GOODS表数据
					$insertData = array(
						'name'         => IFilter::act(trim($val[$titleToCols['name']],'"\'')),
						'goods_no'     => goods_class::createGoodsNo(),
						'sell_price'   => IFilter::act($val[$titleToCols['sell_price']],'float'),
						'market_price' => IFilter::act($val[$titleToCols['sell_price']],'float'),
						'up_time'      => ITime::getDateTime(),
						'create_time'  => ITime::getDateTime(),
						'store_nums'   => IFilter::act($val[$titleToCols['store_nums']],'int'),
						'content'      => IFilter::addSlash($goodsContent),
						'img'          => isset($val['mainPic']) ? current($val['mainPic']) : '',
						'seller_id'    => $seller_id,
						'weight'       => $val[$titleToCols['weight']],
					);
					$goodsObject->setData($insertData);
					$goods_id = $goodsObject->add();

					//货品处理
					if(isset($val['products']) && $val['products'])
					{
						$goodsSpec = array();
						foreach($val['products'] as $k => $pVal)
						{
							//整理goods表spec_array数据
							foreach($pVal['spec_array'] as $specVal)
							{
								if(!isset( $goodsSpec[$specVal['id']] ))
								{
									$goodsSpec[$specVal['id']] = array(
										'id'    => $specVal['id'],
										'name'  => $specVal['name'],
										'type'  => $specVal['type'],
										'value' => array(),
									);
								}

								if(!in_array(array($specVal['tip'] => $specVal['value']),$goodsSpec[$specVal['id']]['value']))
								{
									$goodsSpec[$specVal['id']]['value'][] = array($specVal['tip'] => $specVal['value']);
								}
							}

							//更新插入products表
							$products_no = $insertData['goods_no'].'-'.($k+1);
							$weight      = $insertData['weight'];
							$productsDB  = new IModel('products');
							$productsDB->setData(array(
								'goods_id'    => $goods_id,
								'products_no' => $products_no,
								'spec_array'  => JSON::encode($pVal['spec_array']),
								'store_nums'  => $pVal['store_nums'],
								'market_price'=> $pVal['sell_price'],
								'sell_price'  => $pVal['sell_price'],
								'cost_price'  => $pVal['sell_price'],
								'weight'      => $weight,
							));
							$productsDB->add();
						}

						//更新商品表
						$goodsObject->setData(array( 'spec_array' => JSON::encode($goodsSpec) ));
						$goodsObject->update("id = ".$goods_id);
					}

					//处理商品分类
					if($category)
					{
						foreach($category as $catId)
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
		}
		//清理csv文件数据
		IFile::rmdir($uploadCsvDir,true);
		$this->redirect($returnUrl);
	}

	/**
	 * @brief 插件名字
	 * @return string
	 */
	public static function name()
	{
		return "九猫商品CSV数据导入";
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

