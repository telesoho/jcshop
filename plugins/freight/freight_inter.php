<?PHP
/**
 * @brief 物流开发接口
 */
interface freight_inter
{
	/**
	 * @brief 物流快递轨迹查询
	 * @param $ShipperCode string 物流公司快递号
	 * @param $LogisticCode string 快递单号
	 */
	public function line($ShipperCode,$LogisticCode);

	/**
	 * @brief 处理返回数据统一数据格式
	 * @param $result 结果处理
	 * @return array 通用的结果集 array('result' => 'success或者fail','data' => array( array('time' => '时间','station' => '地点'),......),'reason' => '失败原因')
	 */
	public function response($result);
}