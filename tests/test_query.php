<?php

/** 
* @param String $var   要查找的变量 
* @param Array  $scope 要搜寻的范围 
* @param String        变量名称 
*/  
function get_variable_name(&$var, $scope=null){  
  
    $scope = $scope==null? $GLOBALS : $scope; // 如果没有范围则在globals中找寻  
  
    // 因有可能有相同值的变量,因此先将当前变量的值保存到一个临时变量中,然后再对原变量赋唯一值,
    // 以便查找出变量的名称,找到名字后,将临时变量的值重新赋值到原变量  
    $tmp = $var;  
      
    $var = 'tmp_value_'.mt_rand();  
    $name = array_search($var, $scope, true); // 根据值查找变量名称  

    $var = $tmp;
    return $name;  
}
class IQuery
{
	private $dbo      = null;
	private $sql      = array('table'=>'','fields'=>'*','where'=>'','join'=>'','group'=>'','having'=>'','order'=>'','limit'=>'limit 5000');
	private $tablePre = '';
	public  $paging   = null;//分页类库
    private $subQueries = array();  // 子查询
	private $params = array();

    /**
     * @brief 构造函数
     * @param string $name     表名/子查询
     */
	public function __construct($name)
	{
		$this->tablePre = "iwebshop_";
		if(is_array($name)) {
            $this->subQueries = $name;
		} else {
			$this->table = $name;
		}
		/// $this->dbo=IDBFactory::getDB();
	}
    /**
     * @brief 给表添加表前缀
     * @param string $name 可以是多个表名用逗号(,)分开
     */
	public function setTable($name)
	{
		$re = '/\s+(union)\s+/i';
		if(preg_match($re, $name, $matches))
		{
			// 如果包含有union，不做处理
			$this->sql['table'] = $name;
			return ;
		}		
		if(strpos($name,',') === false)
		{
			$this->sql['table']= $this->tablePre.trim($name);
		}
		else
		{
			$tables = explode(',',$name);
			foreach($tables as $key=>$value)
			{
				$tables[$key] = $this->tablePre.trim($value);
			}
			$this->sql['table'] = implode(',',$tables);
		}
	}
    /**
     * @brief 取得表前缀
     * @return String 表前缀
     */
    public function getTablePre()
    {
        return $this->tablePre;
    }
    /**
     * @brief 设置where子句数据
     * @return String
     */
    public function setWhere($str)
    {
    	if($str)
    	{
    		$exp = array('/from\s+(\S+)(?=$|\s+where)/i','/(\w+)(?=\s+as\s+\w+(,|\)|\s))/i');
    		$rep = array("from {$this->tablePre}$1 ","{$this->tablePre}$1 ");
    		$this->sql['where'] = 'where '.preg_replace($exp,$rep,$str);
    	}
    }
	/**
	 * @brief 设置子查询
	 * @param $query 子查询IQuery
	 */
	public function setSubQueries($query)
	{
		if(is_array($query))
		{
            foreach($query as $aname => $subq) {
                
                $q = new IQuery("");
                foreach($subq as $k => $v){
                    $q->$k = $v;
                }
    			$this->subQueries["@".$aname] = "(" . $q->getSql() . ")";
            }
		}
	}

	// 设定SQL参数
	// $query->params = array('#sku_no#' => '233232');
	public function setParams($params) {
		if(is_array($params)) {
			$this->params = $params;
		}
	}


    /**
     * @brief 取得where子句数据
     * @return String
     */
    public function getWhere()
    {
    	return ltrim($this->sql['where'],'where ');
    }
    /**
     * @brief 实现属性的直接存
     * @param string $name
     * @param string $value
     */
    private function setJoin($str)
    {
		$this->sql['join'] = preg_replace('/(\w+)(?=\s+as\s+\w+(,|\)|\s))/i',"{$this->tablePre}$1 ",$str);
    }
	public function __set($name,$value)
	{
		switch($name)
		{
			case 'params':$this->setParams($value);break;
			case 'subQueries':$this->setSubQueries($value);break;
			case 'table':$this->setTable($value);break;
			case 'fields':$this->sql['fields'] = $value;break;
			case 'where':$this->setWhere($value);break;
			case 'join':$this->setJoin($value);break;
			case 'group':$this->sql['group'] = 'GROUP BY '.$value;break;
			case 'having':$this->sql['having'] = 'having '.$value;break;
			case 'order':$this->sql['order'] = 'order by '.$value;break;
			case 'limit':$value == 'all' ? ($this->sql['limit'] = '') : ($this->sql['limit'] = 'limit '.$value);break;
            case 'page':$this->sql['page'] =intval($value); break;
            case 'pagesize':$this->sql['pagesize'] =intval($value); break;
            case 'pagelength':$this->sql['pagelength'] =intval($value); break;
			case 'cache':
			{
				$this->dbo->cache = $value;
			}
			break;
			case 'debug':
			{
				$this->dbo->debug = $value;
			}
			break;
			case 'log':
			{
				$this->dbo->log = $value;
			}
			break;
		}
	}
    /**
     * @brief 实现属性的直接取
     * @param mixed $name
     * @return String
     */
	public function __get($name)
	{
		if(isset($this->sql[$name]))return $this->sql[$name];
	}

    public function __isset($name)
    {
        if(isset($this->sql[$name]))return true;
    }
    /**
     * @brief 取得查询结果
     * @return array
     */
	public function find()
	{
		$sql    = $this->getSql();
		$result = array();

		//分页SQL处理
        if($this->page)
        {
			$pagesize     = isset($this->pagesize)  ? intval($this->pagesize)  :20;
            $pagelength   = isset($this->pagelength)? intval($this->pagelength):10;
			$this->paging = new IPaging($sql,$pagesize,$pagelength,$this->dbo);
			$result       = $this->paging->getPage($this->page);
		}
		else
        {
        	//SQL语句count类型的去掉limit
        	if(strpos($this->fields,"count(") === false)
        	{
        		$sql .= $this->limit ? " ".$this->limit : "";
        	}
            $result = $this->dbo->query($sql);
        }
        return $result;
	}
	/**
	 * @brief 分页展示
	 * @param string $url   点击分页按钮要跳转的URL地址，如果为空表示当前URL地址
	 * @param string $attrs URL后接参数
	 * @return string pageBar的对应HTML代码
	 */
    public function getPageBar($url='',$attrs='')
    {
        return $this->paging->getPageBar($url,$attrs);
    }
    public function getTotalPage(){
        return $this->paging->totalpage;
    }

	/**
	 * @brief 获取原生态的SQL
	 * @return string sql语句
	 */
    public function getSql()
    {
        $sql = "select $this->fields from $this->table $this->join $this->where $this->group $this->having $this->order";
        if($this->subQueries) {
            foreach($this->subQueries as $k => $q) {
				$sql = strtr($sql, array(
					$this->tablePre . $k => $q,
					$k => $q,
				));
            }
        }
		if($this->params) {
			$sql = strtr($sql, $this->params);				
		}
    	return $sql;
    }

    function query($sql = ''){
        $result = $this->dbo->query($sql);
        return $result;
    }
}

$subQuery = array(
    'sa' => array(
        'table' => 'goods g',
        'fields' => '*',
        'join' => "left join order_goods as og on og.goods_id = g.id",
        'where' => 'a = b and 1=1 ',
    ),
    'sb' => array(
        'table' => 'goods g',
        'fields' => '*',
        'where' => 'a = b and 1=1 ',
    ),    
);

// $query = new IQuery("@sa as sa, goods as g");
// $query->subQueries = $subQuery;
// $query->join = "left join @sa on sa.goods_id = sq.goods_id";
// $query->where = ' sq.c = b';

// print $query->getSql();

// $subQuery = array(
//     'g1' => array(
//         'table' => 'goods g1',
//         'fields' => 'g1.*, gs.delivery_city, gs.duties_rate, gs.delivery_code, gs.ware_house_name',
//         'join' => "left join goods_supplier as gs on g1.supplier_id = gs.supplier_id and g1.sku_no = gs.sku_no",
//     ), 
// );		
// $query = new IQuery("order_goods AS og");
// $query->where = "og.order_id = 1";
// $query->subQueries = $subQuery;
// $query->join = "LEFT JOIN @g1 as g ON g.id = og.goods_id ";
// $query->fields = "g.sku_no, g.goods_no, g.name, g.ware_house_name,  g.content, g.delivery_code, g.supplier_id, g.delivery_city,g.duties_rate,"
//                 ."og.*";
// print $query->getSql();


$query = new IQuery("(@a union all @b) as ab");
$query->subQueries = array(
	'a' => array(
		'table' => 'goods g1',
		'where' => 'id >2 and id < 10',
	),
	'b' => array(
		'table' => 'goods g1',
		'where' => 'id > 10 and id < 100',
	)
);

print $query->getSql();
