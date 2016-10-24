<?php
/**
 * @brief jcshop data packet help abstract
 * @date 2016/10/13 23:15:30
 * @author twh
 */
abstract class jcshopPacketHelperAbstract
{
	//csv source image path
	protected $sourceImagePath;

	//csv target image path
	protected $targetImagePath;

	//csv file convert array data
	protected $dataLine;

	//csv separator
	protected $separator = ",";

	/**
	 * constructor,open the csv packet date file
	 * @param string $csvFile csv file name
	 * @param string $targetImagePath create csv image path
	 */
	public function __construct($csvFile,$targetImagePath)
	{
		if(!preg_match('|^[\w\-]+$|',basename($csvFile,'.csv')))
		{
			throw new Exception('the csv file name must use english');
		}

		if(!file_exists($csvFile))
		{
			throw new Exception('the csv file is not exists!');
		}

		if(!is_dir($targetImagePath))
		{
			throw new Exception('the save csv image dir is not exists!');
		}

		if(IString::isUTF8(file_get_contents($csvFile)) == false)
		{
			die("zip包里面的CSV文件编码格式错误，必须修改为UTF-8格式");
		}

		//read csv file into dataLine array
		setlocale(LC_ALL, 'en_US.UTF-8');
		$fileHandle = fopen($csvFile,'r');
		while($tempRow = fgetcsv($fileHandle,0,$this->separator))
		{
			$this->dataLine[] = $tempRow;
		}

		$this->sourceImagePath = dirname($csvFile).'/'.basename($csvFile,'.csv');
		$this->targetImagePath = $targetImagePath;

		if(!$this->dataLine)
		{
			throw new Exception('the csv file is empty!');
		}
		$this->dataLine[0][0] = IString::clearBom($this->dataLine[0][0]);
	}
	/**
	 * delete useless line until csv title position
	 * @param array $dataLine csv line array
	 * @param array $title csv title
	 * @return array
	 */
	protected function seekStartLine(&$dataLine,$title)
	{
		foreach($dataLine as $lineNum => $lineContent)
		{
			unset($dataLine[$lineNum]);
			if(in_array(current($title),$lineContent))
			{
				break;
			}
		}
		return $dataLine;
	}
	/**
	 * the mapping with column's num
	 * @param array $dataLine csv line array
	 * @param array $titleArray csv title
	 * @return array key and cols mapping
	 */
	protected function getColumnNum(&$dataLine,$titleArray)
	{
		$titleMapping  = array();
		foreach($dataLine as $key => $colsArray)
		{
			//find the csv title line
			if(in_array(current($titleArray),$colsArray))
			{
				foreach($titleArray as $name)
				{
					$findKey = array_search($name,$colsArray);
					if($findKey !== false)
					{
						$titleMapping[$findKey] = $name;
					}
				}
				break;
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
		$mapping  = $this->getColumnNum($this->dataLine,$this->getDataTitle());
		$dataLine = $this->seekStartLine($this->dataLine,$this->getDataTitle());

		$result    = array();
		$temp      = array();

		foreach($dataLine as $lineNum => $lineContent)
		{
			foreach($mapping as $key => $title)
			{
				$temp[$title] = $this->runCallback($lineContent[$key],$title);
			}
			$result[] = $temp;
		}
		return $result;
	}
	/**
	 * run title callback function
	 * @return mix
	 */
	public function runCallback($content,$title)
	{
		$configCallback = $this->getTitleCallback();
		if(isset($configCallback[$title]))
		{
			return call_user_func(array($this,$configCallback[$title]),$content);
		}
		return $content;
	}
	/**
	 * get data image path
	 * @return string
	 */
	public function getImagePath()
	{
		return $this->imagePath;
	}

	/**
	 * get useful column in csv file
	 * @return array
	 */
	abstract public function getDataTitle();
	/**
	 * get function config from title callback
	 * @return array
	 */
	abstract public function getTitleCallback();
}