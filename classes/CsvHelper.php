<?php
/**
 * @brief jcshop csv data help abstract
 * @date 2016/10/13 23:15:30
 * @author twh
 */
abstract class CsvHelper
{
	//csv file convert array data
	protected $dataLine;

	//csv separator
	protected $separator = "\t";

	protected $version;

	protected $titleArrayEn;

	protected $titleArrayCn;

	protected $config;

	protected $dataTitle;


	/**
	 * constructor,open the csv packet date file
	 * @param string $csvFile csv file name
	 * @param string $targetImagePath create csv image path
	 */
	public function __construct($config)
	{
		$this->config = $config;
		$csvFile = $config['csvFile'];
		$this->dataTitle = $config['dataTitle'];


		if(!preg_match('|^[\w\-]+$|',basename($csvFile,'.csv')))
		{
			throw new Exception('the csv file name must use english');
		}

		if(!file_exists($csvFile))
		{
			throw new Exception('the csv file is not exists!');
		}

		if(IString::isUTF8(file_get_contents($csvFile)) == false)
		{
			die("CSV文件编码格式错误，必须修改为UTF-8格式");
		}

		//read csv file into dataLine array
		setlocale(LC_ALL, 'en_US.UTF-8');
		$fileHandle = fopen($csvFile,'r');

		while($tempRow = fgetcsv($fileHandle,0,$this->separator))
		{
			$this->dataLine[] = $tempRow;
		}

		if(!$this->dataLine)
		{
			throw new Exception('the csv file is empty!');
		}
		$this->dataLine[0][0] = IString::clearBom($this->dataLine[0][0]);

		// CSV版本判断
		$this->version = $this->dataLine[0][0];
		if($this->version == "version 1.00") {
			$this->titleArrayEn = $this->dataLine[1];
			$this->titleArrayCn = $this->dataLine[2];
			unset($this->dataLine[2]);
			unset($this->dataLine[1]);
			unset($this->dataLine[0]);
		}
	}

	/**
	 * the mapping with column's num
	 * @param array $dataLine csv line array
	 * @param array $titleArray csv title
	 * @return array key and cols mapping
	 */
	protected function getColumnNum($titleArray)
	{
		$titleMapping  = array();
		foreach($titleArray as $name)
		{			
			$findKey = array_search($name,$this->titleArrayCn);
			if($findKey !== false)
			{
				$titleMapping[$findKey] = $name;
			}
		}
		if(!$titleMapping)
		{
			throw new Exception('can not find the mapping colum');
		}
		return $titleMapping;
	}
	/**
	 * get data from csv file
	 * @return array
	 */
	public function collect()
	{
		$mapping  = $this->getColumnNum($this->getDataTitle());

		$result    = array();
		$temp      = array();

		foreach($this->dataLine as $lineNum => $lineContent)
		{
			// var_dump($lineContent);
			foreach($mapping as $key => $title)
			{
				$temp[$title] = $this->runCallback($lineContent[$key],$title);
			}
			$result[] = $temp;
		}
		return $result;
	}

	/**
	 * 运行回调函数
	 * 回调函数示例：
	 * 
	 *	public function getColumnCallback()
	 * {
	*		// 设置回调函数
	*		return array("品牌" =>"brandCallback");
	*	}
	*	protected function brandCallback($content) {
	*		var_dump("品牌回调函数：", $content);
	*	}	 
	* @return mix
	 */
	public function runCallback($content,$title)
	{
		$configCallback = $this->getColumnCallback();

		if(isset($configCallback[$title]))
		{
			return call_user_func(array($this,$configCallback[$title]),$content);
		}
		return $content;
	}


	/**
	 * get useful column in csv file
	 * @return array
	 */
	public function getDataTitle() 
	{
		return $this->dataTitle;
	}
}
