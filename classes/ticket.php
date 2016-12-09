<?php
/**
 * 优惠券类库
 */
class ticket
{
	/**
	 * 优惠券码code计算价格
	 */
	public static function calculateCode($data, $code){
		/* 校验优惠券码 */
		$rel 							= self::checkCode($code);
		if($rel['code']>0) return $rel;
		$ticket_data 					= $rel['data'];
		
		/* 计算优惠 */
		switch($ticket_data['type']){
			//折扣券
			case 1 :
				$data['sum'] 			= $data['sum'] * $ticket_data['ratio'];
				$data['final_sum'] 		= $data['sum'];
				$msg 					= ($ticket_data['ratio']*10).'折优惠券';
				break;
				//抵扣券
			case 2 :
				$data['sum'] 			= $data['sum'] - $ticket_data['money'];
				$data['final_sum'] 		= $data['sum'];
				$msg 					= '抵'.$ticket_data['money'].'元优惠券';
				break;
			default:
				return apireturn::go('002006');
		}
		
		/* 计算邮费 */
		//满包邮
		$promotion_query 				= new IQuery("promotion");
		$promotion_query->where 		= "type = 0 and seller_id = 0 and award_type = 6";
		$condition_price 				= $promotion_query->find()[0]['condition'];
		if ($data['sum'] >= $condition_price){
			$data['delivery_money'] 	= 0;
		} else {
			//首重价格
			$data['delivery_money'] 	= $data['delivery'][0]['first_price'];
			//续重价格
			if($data['weight'] > $data['delivery'][0]['first_weight']){
				$data['delivery_money'] += ceil(($data['weight']-$data['delivery'][0]['first_weight'])/$data['delivery'][0]['second_weight'])*$data['delivery'][0]['second_price'];
			}
			$data['sum'] += $data['delivery_money'];
		}
        /* 优惠券 */
        $data['kicket'] 		= array(
        	'kicket_did' 		=> $ticket_data['id'], 	//优惠券码ID
        	'kicket_aid'		=> '',	//优惠券ID
        	'name' 				=> $ticket_data['name'], 	//优惠券名称
        	'msg' 				=> $msg,
        );
		return apireturn::go('0',$data);
	}
	
	/**
	 * 活动优惠券计算价格
	 */
	public static function calculateActivity($data, $ticket_aid){
		/* 校验优惠券 */
		$rel 						= self::checkActivity($ticket_aid);
		if($rel['code']>0) return $rel;
		$ticket_data 				= $rel['data'];
		
		/* 优惠券类型 */
		switch ($ticket_data['type']){
			//满减券
			case 1:
				//优惠券规则
				$rule 					= explode(',',$ticket_data['rule']);
				if(count($rule)!=2 || $rule[0]<=0 || $rule[1]<=0)
					return apireturn::go('002013');
				if($data['sum'] < $rule[0])
					return apireturn::go('002014');
				//计算优惠
				$data['sum'] 			= $data['sum'] - $rule[1];
				$msg 					= '满'.$rule[0].'减'.$rule[1].'优惠券';
				break;
			default:
				return apireturn::go('002012');
		}
		
		/* 计算邮费 */
        if ($data['sum'] >= $data['condition_price']){
        	$data['delivery_money'] 	= 0; //满金额包邮
        } else {
        	//首重价格
        	$data['delivery_money'] 	= $data['delivery'][0]['first_price'];
        	//续重价格
        	if($data['weight'] > $data['delivery'][0]['first_weight']){
        		$data['delivery_money'] += ceil(($data['weight']-$data['delivery'][0]['first_weight'])/$data['delivery'][0]['second_weight'])*$data['delivery'][0]['second_price'];
        	}
        	$data['sum'] 		+= $data['delivery_money'];
        }
		/* 优惠券 */
		$data['kicket'] 		= array(
			'kicket_did' 		=> '', 	//优惠券码ID
			'kicket_aid'		=> $ticket_aid,	//优惠券ID
			'name' 				=> $ticket_data['name'], 	//优惠券名称
			'msg' 				=> $msg,
		);
		return apireturn::go('0',$data);
	}
	
	/**
	 * 最终优惠券码code计算价格
	 */
	public static function finalCalculateCode($data, $ticket_did, $postage){
		$model_ticket 				= new IModel('ticket_discount');
		$data_ticket 				= $model_ticket->getObj('`start_time`<'.time().' AND `end_time`>'.time().' AND `status`=1 AND `id`='.$ticket_did,'type,ratio,money');
		if( empty($data_ticket ))
			return apireturn::go('002002');
		//折扣券已使用状态
		$model_ticket->setData(array('status'=>2,'user_id'=>$user_id));
		$model_ticket->update('`id`='.$ticket_did);
		
		/* 计算优惠价格 */
		switch($data_ticket['type']){
			//折扣券
			case 1 :
				$data['sum'] 		= $data['sum']*$data_ticket['ratio'];
				break;
				//抵扣券
			case 2 :
				$data['sum'] 		= $data['sum']-$data_ticket['money'];
				break;
		}
		/* 计算邮费 */
		if ($data['sum'] >= $postage['condition']){
			$data['deliveryPrice'] 			= 0;
		} else {
			//首重价格
			$data['deliveryPrice'] 			= $postage['delivery']['first_price'];
			//续重价格
			if($data['weight'] > $postage['delivery']['first_weight']){
				$data['deliveryPrice'] 		+= ceil(($data['weight']-$postage['delivery']['first_weight'])/$postage['delivery']['second_weight'])*$postage['delivery']['second_price'];
			}
		}
		
	}
	
	/**
	 * 最终优惠券码code计算价格
	 */
	public static function finalCalculateActivity($data, $ticket_aid, $postage){
		/* 校验优惠券 */
		$rel 						= self::checkActivity($ticket_aid);
		if($rel['code']>0) return $rel;
		$ticket_data 				= $rel['data'];
		
		/* 优惠券类型 */
		switch ($ticket_data['type']){
			//满减券
			case 1:
				//优惠券规则
				$rule 					= explode(',',$ticket_data['rule']);
				if(count($rule)!=2 || $rule[0]<=0 || $rule[1]<=0)
					return apireturn::go('002013');
				if($data['sum'] < $rule[0])
					return apireturn::go('002014');
				//计算优惠
				$data['sum'] 			= $data['sum'] - $rule[1];
				break;
			default:
				return apireturn::go('002012');
		}
		
		/* 计算邮费 */
		if ($data['sum'] >= $postage['condition']){
			$data['deliveryPrice'] 			= 0;
		} else {
			//首重价格
			$data['deliveryPrice'] 			= $postage['delivery']['first_price'];
			//续重价格
			if($data['weight'] > $postage['delivery']['first_weight']){
				$data['deliveryPrice'] 		+= ceil(($data['weight']-$postage['delivery']['first_weight'])/$postage['delivery']['second_weight'])*$postage['delivery']['second_price'];
			}
		}
		return apireturn::go('0',$data);
	}

	/**
	 * 验证优惠券码
	 */
	public static function checkCode($code=0){
		/* 优惠券 */
		if(empty($code) || $code<=0 || $code>999999) return apireturn::go('002001');
		/* 获取折扣券数据 */
		$query 				 		= new IQuery('ticket_discount');
		$query->where 				= 'code='.$code;
		$query->fields 				= 'id,name,type,ratio,money,start_time,end_time,status';
		$query->limit 				= 1;
		$ticket_data 				= $query->find();
		if(empty($ticket_data))
			return apireturn::go('002002');
		$ticket_data 				= $ticket_data[0];
		if($ticket_data['start_time']>time() || $ticket_data['end_time']<time())
			return apireturn::go('002003');
		if($ticket_data['status'] == 2)
			return apireturn::go('002004');
		if($ticket_data['status'] != 1)
			return apireturn::go('002005');
		return apireturn::go('0',$ticket_data);
	}
	
	/**
	 * 校验活动优惠券
	 */
	public static function checkActivity($ticket_aid){
		$user_id 					= IWeb::$app->getController()->user['user_id'];
		/* 获取优惠券信息 */
		$query 						= new IQuery('activity as m');
		$query->join 				= 'LEFT JOIN activity_ticket AS t ON t.pid=m.id LEFT JOIN activity_ticket_access AS a ON a.ticket_id=t.id';
		$query->where 				= 'a.id='.$ticket_aid.' AND a.user_id='.$user_id;
		$query->fields 				= 'm.start_time,m.end_time,m.status as astatus,t.name,t.type,t.rule,a.status';
		$query->limit 				= 1;
		$data 						= $query->find();
		//判断优惠券是否存在
		if( empty($data) )
			return apireturn::go('002007');
		$data 						= $data[0];
		if( $data['start_time'] > time() )
			return apireturn::go('002008');
		if( $data['end_time'] < time() )
			return apireturn::go('002009');
		if( $data['astatus'] != 1 )
			return apireturn::go('002010');
		if( $data['status'] != 1 )
			return apireturn::go('002011');
		return apireturn::go('0',$data);
	}
	
	/**
	 * 获取配送、包邮
	 */
	public static function postage(){
		//满包邮
		$promotion_query 			= new IQuery("promotion");
		$promotion_query->where = "type = 0 and seller_id = 0 and award_type = 6";
		$condition_price 			= $promotion_query->find()[0]['condition'];
		//配送方式
		$delivery 					= Api::run('getDeliveryList');
		return array('condition'=>$condition_price, 'delivery'=>$delivery);
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