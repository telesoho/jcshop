<?php
/**
 * 优惠券类库
 */
class ticket
{
	/**
	 * 优惠券码code计算价格
	 */
	public function calculateCode(){
		
	}
	
	/**
	 * 
	 */
	public function checkCode($code){
		/* 优惠券 */
		if(!empty($code)){
			if($code<=0 || $code>999999){
				$this->json_echo(array('error'=>'请输入正确的折扣券号'));
			}
			/* 获取折扣券数据 */
			$query 				 		= new IQuery('ticket_discount');
			$query->where 				= 'code='.$code;
			$query->fields 				= 'id,name,type,ratio,money,start_time,end_time,status';
			$query->limit 				= 1;
			$ticket_data 				= $query->find();
			if(empty($ticket_data)) $this->json_echo(array('error'=>'折扣券不存在'));
			if($ticket_data[0]['start_time']>time() || $ticket_data[0]['end_time']<time()) $this->json_echo(array('error'=>'折扣券已过期'));
			if($ticket_data[0]['status'] == 2) $this->json_echo(array('error'=>'折扣券已使用'));
			if($ticket_data[0]['status'] != 1) $this->json_echo(array('error'=>'折扣券无法使用'));
		}
	}
	/**
	 * 活动优惠券计算价格
	 */
	public function calculateActivity(){
		
	}
	
	
	
	
	/**
	 * @brief 获取代金券状态数值
	 * @param array $ticketRow 代金券数据
	 * @return int 状态码 -1:已使用;-2:已禁用;-3:临时锁定;-4:已过期;1:可使用;
	 */
	public static function status($ticketRow)
	{
    	if($ticketRow['is_userd']==1)
    	{
    		return -1;
    	}

    	if($ticketRow['is_close']==1)
    	{
			return -2;
    	}

    	if($ticketRow['is_close']==2)
    	{
    		return -3;
    	}

    	if(ITime::getDateTime() > $ticketRow['end_time'])
    	{
    		return -4;
    	}
    	return 1;
	}

	/**
	 * @brief 获取代金券的状态文字
	 * @param int $status 状态码
	 * @return string 状态文字
	 */
	public static function statusText($status)
	{
		$mapping = array(
			"-1" => "已使用",
			"-2" => "已禁用",
			"-3" => "临时锁定",
			"-4" => "已过期",
			"1"  => "可使用",
		);
		return isset($mapping[$status]) ? $mapping[$status] : "未知";
	}
}