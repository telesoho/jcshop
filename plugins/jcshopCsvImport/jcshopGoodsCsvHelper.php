<?php

include_once(__DIR__.'/jcshopPacketHelperAbstract.php');

/**
 * @brief the Jcshop data packet dispose
 * @data 2013-8-30 15:32:44
 * @author nswe
 */
class jcshopGoodsCsvHelper extends jcshopPacketHelperAbstract
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
		return array(
			"品牌名","商品JAN编码","商品名称","商品类目","日本市场价格","库存数量","商品详情","图片",
			"销售属性","商家编码","物流重量","商品详情日文","商品名称日文","状态",
			"日本价格", "商品标题是中文","商品详情是中文");
	}

	/**
	 * override abstruact function
	 * @return array
	 */
	public function getColumnCallback()
	{
		// 设置回调函数
		return array();
	}

	/**
	 * 销售属性 回调函数
     * 将：{"尺寸":"S","颜色":"绿"}
	 * 转换为：[{"id":"1","type":"1","value":"S","name":"尺寸"},{"id":"2","type":"1","value":"绿","name":"颜色"}]
	 */
	protected function spec_array_callback($content) {
		if(!$content) 
			return "";

		try{

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
		}
		catch(Exception $e){
			$this->error($e->getMessage());
			return "";
		}
		return $content;
	}

	//整合采集信息
	public function collect()
	{
		// 拷贝商品详情页面图片
		$this->copyDetailImage();
		return  parent::collect();
	}

	//拷贝商品详情图片
	public function copyDetailImage()
	{

		if(is_dir($this->sourceImagePath . DIRECTORY_SEPARATOR .'pictures'))
		{
			IFile::xcopy($this->sourceImagePath. DIRECTORY_SEPARATOR .'pictures',$this->targetImagePath . DIRECTORY_SEPARATOR . 'mainPic',true);
		}
	}

}

