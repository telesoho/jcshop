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

	const SPEC_NAME_KEYS = "spec_name_keys";

	// 规格值数组
	public $spec_vals = array();
	public $spec_jp_vals = array();

	// 规格名
	public $spec_names = array('color' => '颜色','size' => '尺寸');

	// 规格表对象
	public $spec_objs  = array();

	/**
	 * override abstract function
	 * @return array
	 */
	public function getDataTitle()
	{
		return array(
			"品牌名","商品JAN编码","商品名称","商品类目","销售价格","日本市场价格","库存数量","商品详情","重设图片",
			"规格日文","规格","规格编号","物流重量","商品详情日文","商品名称日文","状态",
			"日本价格", "商品标题是中文","商品详情是中文","标签","最新商品","热卖商品","推荐商品");
	}

	/**
	 * override abstruact function
	 * @return array
	 */
	public function getColumnCallback()
	{
		// 设置回调函数
		return array(
			"规格日文" => "spec_jp_callback",
		);
	}

	/**
	 * 规格名
	 */
	protected function spec_jp_callback($content) {
		if(!$content) {
			return array();
		}

		// 返回值
		$ret_val = array();

		// 从JSON中解析规格
		$specs = json_decode($content, true);


		// // 如果有规格名定义,则读取规格名
		// $this->spec_names = array();
		// if(isset($specs[self::SPEC_NAME_KEYS])) {
		// 	$spec_names = $specs[SPEC_NAME_KEYS];
		// 	// 从规格值数组中删除规格定义
		// 	unset($specs[self::SPEC_NAME_KEYS]);
		// } else {
		// 	// 没有规格名定义，则设定默认的规格名color，size
		// 	$spec_names = array('color' => '颜色','size' => '尺寸');
		// }

		// 读取数据库中的规格定义
		$specDB = new IModel("spec");

		foreach($this->spec_names as $spec_name => $spec_show_name) {
			$this->spec_objs[$spec_name] = $specDB->getObj("name='$spec_name'");
			
			// 如果规格不存在
			if(!$this->spec_objs[$spec_name]) {
				// 新增规格记录
				$new_spec = array (
					'name' => $spec_name,
					'note' => $spec_show_name,
					'value' => '',
					'value_jp' => '',
				);
				$specDB->setData($new_spec);
				$new_spec['id'] = $specDB->add();
				$this->spec_objs[$spec_name] = $new_spec;
			}

			// 解析规格值
			$this->spec_jp_vals[$spec_name] = common::spec_split($this->spec_objs[$spec_name]['value_jp']);
			$this->spec_vals[$spec_name] = common::spec_split($this->spec_objs[$spec_name]['value']);
		}
		
		
		// 处理规格值 输出为：[{"id":"6","type":"1","name":"color","value":14},{"id":"7","type":"1","name":"size","value":18}]
		foreach($specs as $key => $spec) {
			$product = array();
			$skuProps = array();
			foreach($spec as $k => $v) {
				// 是否是规格
				if(isset($this->spec_objs[$k])){
					// 日文规格值不存在时,插入规格
					$vindex = array_search($v, $this->spec_jp_vals[$k]);
					if($vindex === false) {
						$this->spec_jp_vals[$k][] = $v;
						// 将日文规格作为中文规格
						$this->spec_vals[$k][] = $v;
						$vindex = count($this->spec_jp_vals[$k]) - 1;
					}

					$skuProp['id'] = $this->spec_objs[$k]['id'];
					$skuProp['type'] = "1"; // 1：文字类型， 2：图片类型
					$skuProp['name'] = $this->spec_objs[$k]['name'];
					$skuProp['value'] = $vindex;
					$skuProps[] = $skuProp;

				} else {
					// 价格
					if($k == "jp_price") {
						$product[$k] = intval(str_replace(",", "", $v));
					} else {
						$product[$k] = $v;
					}
				}
			}
			$product['spec'] = $skuProps;
			$ret_val[$key] = $product;			
		}


		// 保存规格数组
		foreach($this->spec_objs as $k => $s) {
			$s['value'] = common::to_spec($this->spec_vals[$k]);
			$s['value_jp'] = common::to_spec($this->spec_jp_vals[$k]);
			$specDB->setData($s);
			$specDB->update("id=" . $s['id']);
		}

		return $ret_val;
	}

	/**
	 * 将[{"id":"6","type":"1","name":"color","value":14},{"id":"7","type":"1","name":"size","value":18}]
	 * 转换为[{"id":"6","type":"1","name":"颜色","value":"黑"},{"id":"7","type":"1","name":"尺寸","value":"L"}]
	 */
	public function specId2SpecVal($specs) {
		foreach($specs as $key => $spec) {
			$specs[$key]['value'] = $this->spec_vals[$spec['name']][$spec['value']];
			$specs[$key]['name'] = $this->spec_names[$spec['name']];
		}
		return $specs;
	}

	//整合采集信息
	public function collect()
	{
		// 拷贝商品详情页面图片
		$this->copyDetailImage();

		$ret =  parent::collect();

		return $ret;
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

