<?php

include_once(__DIR__.'/nysoPacketHelperAbstract.php');

/**
 * @brief the Jcshop data packet dispose
 * @data 2013-8-30 15:32:44
 * @author nswe
 */
class nysoGoodsCsvHelper extends nysoPacketHelperAbstract
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
			"品牌名",
			"供应商ID",
			"供应商货号",
			"自动同步",
			"商品JAN编码",
			"商品名称",
			"商品类目",
			"销售价格",
			"日本市场价格",
			"库存数量",
			"商品详情",
			"重设图片",
			"销售属性",
			"商家编码",
			"物流重量",
			"商品名称日文",
			"商品详情日文",
			"状态",
			"日本价格",
			"商品标题是中文",
			"商品详情是中文",
			"标签",
			"最新商品",
			"热卖商品",
			"推荐商品"
			);
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

