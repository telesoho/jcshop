<?php

include_once($pluginDir.'jcshopPacketHelperAbstract.php');

/**
 * @brief the Jcshop data packet dispose
 * @data 2013-8-30 15:32:44
 * @author nswe
 */
class jcshopPacketHelper extends jcshopPacketHelperAbstract
{
	//csv separator
	protected $separator = "	";

	//SKU cache
	private $skuCache = array();

	/**
	 * override abstract function
	 * @return array
	 */
	public function getDataTitle()
	{
		return array("品牌名","商品JAN编码","商品名称","商品类目","销售价格","库存数量","商品详情","图片","规格","规格编号","物流重量");
	}
	/**
	 * override abstruact function
	 * @return array
	 */
	public function getTitleCallback()
	{
		// 设置回调函数
		return array("销售属性" =>"spec_array_callback");
	}

	/**
	 * 销售属性 回调函数
     * 将：{"尺寸":"S","颜色":"绿"}
	 * 转换为：[{"id":"1","type":"1","value":"S","name":"尺寸"},{"id":"2","type":"1","value":"绿","name":"颜色"}]
	 */
	protected function spec_array_callback($content) {
		if(!$content) 
			return "";

		$skus = JSON::decode($content); 

		$skuProps = array();
		$index = 0;
		foreach($skus as $key => $val) {
			$skuProp['id'] = ++$index;
			$skuProp['type'] = "1"; // 1：文字类型， 2：图片类型
			$skuProp['name'] = $key;
			$skuProp['value'] = $val;
			$skuProps[] = $skuProp;
		}
		$content = JSON::encode($skuProps);
		return $content;
	}
	/**
	 * column callback function
	 * @param string $content data content
	 * @return string
	 */
	protected function newImageCallback($content)
	{
		$record    = array();
		$content   = explode(';',trim($content,'"'));

		if(!$content)
		{
			return '';
		}

		$return  = array();
		foreach($content as $key => $val)
		{
			if($val)
			{
				$imageName = current(explode(':',$val));

				if(in_array($imageName,$record))
				{
					continue;
				}
				$record[] = $imageName;

				if(stripos($imageName,'http://') === 0)
				{
					$imageMd5 = md5($imageName);
					file_put_contents($this->sourceImagePath .'/'. $imageMd5.'.tbi',file_get_contents($imageName));
					$imageName = $imageMd5;
				}
				$source = $this->sourceImagePath .'/'. $imageName.'.tbi';
				if(!is_file($source))
				{
					$source = $this->sourceImagePath .'/'. $imageName.'.jpg';
				}
				$target = $this->targetImagePath .'/'. $imageName.'.jpg';
				$return[$source] = $target;
			}
		}
		return $return;
	}

	/**
	 * @brief 获取自定义SKU
	 * @param int 宝贝类目
	 * @param int 属性ID
	 * @return object
	 */
	public function attrAPI($cat_id,$pid)
	{
		$postData = array(
			"method" => "ItempropsGetRequest",
			"cat_id" => $cat_id,
			"pid"    => $pid,
		);
		return $this->apiCall($postData);
	}

	/**
	 * @brief 获取标准淘宝SKU
	 * @param int 宝贝类目
	 * @param string sku串
	 * @return object
	 */
	public function propAPI($cat_id,$pvs)
	{
		$postData = array(
			"method" => "ItempropvaluesGetRequest",
			"cat_id" => $cat_id,
			"pvs"    => $pvs,
		);
		return $this->apiCall($postData);
	}

	//整合采集信息
	public function collect()
	{
		//拷贝商品详情页面图片
		$this->copyDetailImage();

		$result = parent::collect();
		foreach($result as $goodsKey => $goodsRow)
		{
			//自定义的属性值
			$customPropData = array();
			if($goodsRow['自定义属性值'])
			{
				$customPropData[] = $goodsRow['自定义属性值'];
			}

			if($goodsRow['销售属性别名'])
			{
				$customPropData[] = $goodsRow['销售属性别名'];
			}

			//替换自定义属性,CVS中已经存在值，只需要解析prop_id名字即可
			if($customPropData)
			{
				foreach($customPropData as $customData)
				{
					$apiAttr    = array();
					$customAttr = explode(';',trim($customData,";"));
					foreach($customAttr as $cval)
					{
						$cArray = explode(":",$cval);
						$from   = $cArray[0].":".$cArray[1];
						$to     = $cArray[0].":".$cArray[2];

						//自定义属性每个商品都是不同的，需要更新
						if(isset($this->skuCache[$from]))
						{
							list($prop_name,$old_prop_val) = explode(":",$this->skuCache[$from]);
							$this->skuCache[$from] = $prop_name.":".$cArray[2];
						}
						else
						{
							$apiAttr[$from] = $to;
						}
					}

					//是否调用淘宝API接口获取属性名字
					if($apiAttr)
					{
						foreach($apiAttr as $attrFrom => $attrTo)
						{
							$attrId    = current(explode(":",$attrTo));
							$apiResult = $this->attrAPI($goodsRow['宝贝类目'],$attrId);
							$this->skuCache[$attrFrom] = str_replace($attrId,$apiResult->item_props->item_prop->name,$attrTo);
						}
					}
				}
			}

			//SKU规格数据
			if($goodsRow['宝贝属性'])
			{
				$apiAttr   = array();
				$attrArray = explode(";",trim($goodsRow['宝贝属性'],";"));
				foreach($attrArray as $aKey => $aValue)
				{
					if(!isset($this->skuCache[$aValue]))
					{
						$apiAttr[] = $aValue;
					}
				}

				//是否调用淘宝API接口获取SKU数据,键值对形式prop_id => prop_value
				if($apiAttr)
				{
					$apiResult = $this->propAPI($goodsRow['宝贝类目'],join(";",$apiAttr));
					if(isset($apiResult->prop_values) && $apiResult->prop_values->prop_value)
					{
						$apiResult->prop_values->prop_value = is_array($apiResult->prop_values->prop_value) ? $apiResult->prop_values->prop_value : array($apiResult->prop_values->prop_value);
						foreach($apiResult->prop_values->prop_value as $propValue)
						{
							$from = $propValue->pid.":".$propValue->vid;
							$to   = $propValue->prop_name.":".$propValue->name;
							$this->skuCache[$from] = $to;
						}
					}
				}
			}

			//SKU编码解码
			if($this->skuCache)
			{
				$result[$goodsKey]['宝贝属性'] = strtr($result[$goodsKey]['宝贝属性'],$this->skuCache);
			}

			//用户自定义ID串和键值对
			if($goodsRow['用户输入ID串'] && $goodsRow['用户输入名-值对'])
			{
				$customName  = explode(",",trim($goodsRow['用户输入ID串'],";"));
				$customValue = explode(",",trim($goodsRow['用户输入名-值对'],";"));
				foreach($customName as $cIndex => $cvalue)
				{
					$apiResult = $this->attrAPI($goodsRow['宝贝类目'],$cvalue);
					$replace   = $apiResult->item_props->item_prop->name.":".$customValue[$cIndex];
					$result[$goodsKey]['宝贝属性'] = preg_replace("|{$cvalue}:[\-\d]+|",$replace,$result[$goodsKey]['宝贝属性']);
				}
			}

			//处理图片包括主图和规格图片
			$mainPic = array();
			$specPic = array();
			if(isset($goodsRow['新图片']))
			{
				//原CVS里面的图片信息结构
				$picData = explode(";",trim($goodsRow['新图片'],";"));

				//生成图片路径下载关系
				$result[$goodsKey]['新图片'] = $this->newImageCallback($goodsRow['新图片']);

				foreach($picData as $picKey => $picVal)
				{
					$picMd5 = current(explode(":",$picVal));
					$picPath= current($result[$goodsKey]['新图片']);

					//判断图片是否是规格数据
					preg_match("/(?<=:)\d+:[\-\d]+(?=\|)/",$picVal,$picMatch);
					if($picMatch)
					{
						$specPic[$picMatch[0]] = $picPath;
					}
					else
					{
						$mainPic[] = $picPath;
					}

					//同步更新下一张图片
					next($result[$goodsKey]['新图片']);
				}
			}

			//处理货品规格数据
			if($goodsRow['销售属性组合'])
			{
				//去掉可能的货品货号
				$customValue = explode(",",trim($goodsRow['用户输入名-值对'],";"));
				if($customValue)
				{
					foreach($customValue as $v)
					{
						$goodsRow['销售属性组合'] = str_replace(":{$v}:","::",$goodsRow['销售属性组合']);
					}
				}

				//生成货品数据信息products
				$product  = array();
				$specData = explode(";",$goodsRow['销售属性组合']);
				foreach($specData as $key => $val)
				{
					$specMixArray = explode(":",$val);

					//带有价格:库存:编号:规格名:规则值
					if(count($specMixArray) == 5)
					{
						list($price,$store) = $specMixArray;
						$product[] = array(
							'sell_price'   => $price,
							'store_nums'   => $store,
							'spec_array'   => array(),
						);
					}
					$specItem   = array_slice($specMixArray,-2);
					$specString = join(":",$specItem);

					//被拆分的数据属于规格
					if(isset($this->skuCache[$specString]))
					{
						list($specName,$specValue) = explode(":",$this->skuCache[$specString]);
						list($skuId,$skuVal)       = $specItem;
						$index                     = count($product) - 1;
						$specRow                   = array(
							"id"    => $skuId,
							"type"  => "1",
							"value" => $specValue,
							"name"  => $specName,
							"tip"   => "",
						);

						if($specPic && isset($specPic[$specString]))
						{
							$specRow['tip']  = $specValue;
							$specRow['type'] = "2";
							$specRow['value']= $specPic[$specString];
						}
						$product[$index]['spec_array'][] = $specRow;
					}
				}
				//赋值货品
				$result[$goodsKey]['products'] = $product;
			}

			//赋值商品主图
			$result[$goodsKey]['mainPic'] = $mainPic;
		}
		return $result;
	}

	//拷贝商品详情图片
	public function copyDetailImage()
	{
		if(is_dir($this->sourceImagePath.'/contentPic'))
		{	
			IFile::xcopy($this->sourceImagePath.'/contentPic',$this->targetImagePath.'/contentPic',true,"GBK");
		}
	}

	//石塔API接口调用
	private function apiCall($postData)
	{
		$ch = curl_init("http://api.aircheng.com/taobao/index.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		if(!$result)
		{
			die("采集人数过多，请稍后再试...".curl_error($ch));
		}

		$result = json_decode($result,false);
		if(isset($result->msg) && $result->msg)
		{
			die($result->msg);
		}
		curl_close($ch);
		return $result;
	}
}
/**
 * @brief taobao title to iwebshop cols mapping
 * @date 2013-9-7 12:22:11
 * @author nswe
 */
class jcshopTitleToColsMapping
{
	/**
	 * taobao title to iwebshop cols mapping
	 */
	public static $mapping = array(
		'name'       => '宝贝名称',
		'sell_price' => '宝贝价格',
		'store_nums' => '宝贝数量',
		'content'    => '宝贝描述',
		'img'        => '新图片',
		'attr'       => '宝贝属性',
		'spec'       => '销售属性组合',
		'weight'     => '物流重量',
	);
}