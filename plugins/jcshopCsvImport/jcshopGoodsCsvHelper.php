<?php

include_once($pluginDir.'jcshopPacketHelperAbstract.php');

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
		return array("品牌名","商品JAN编码","商品名称","商品类目","销售价格","库存数量","商品详情","图片","销售属性","商家编码","物流重量");
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


	//整合采集信息
	public function collect()
	{
		// 拷贝商品详情页面图片
		$this->copyDetailImage();

		$result = parent::collect();

		// 逐个处理商品信息
		foreach($result as $goodsKey => $goodsRow)
		{
			//处理图片包括主图
			$mainPic = array();

			// 商品JAN
			$jan = $goodsRow["商品JAN编码"];

			$goodsImgDir = $this->targetImagePath . "/" . "mainPic" . "/" . $jan;


			if(is_dir($goodsImgDir)) {

				$handle = opendir($goodsImgDir);

				while($file = readdir($handle))
				{
					if($file != '.' && $file != '..'){
						// 并将图片文件名改为图片内容的MD5码
						// $ext = pathinfo($file, PATHINFO_EXTENSION);
						$source_file =  $goodsImgDir . "/" . $file;
						// $target_file = $goodsImgDir . "/" . md5_file($source_file) . "." . $ext ;

						// if($source_file !== $target_file) {
						// 	copy($source_file,$target_file);
						// 	unlink($source_file);
						// }
						$mainPic[] = $source_file;
					}
				}
			}

			//赋值商品主图
			$result[$goodsKey]['mainPic'] = $mainPic;
		}

		return $result;
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
