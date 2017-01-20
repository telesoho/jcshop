<?php

/**
 * 优惠券类库
 */
class ticket{
	/**
	 * 订单确认-优惠券码code计算价格
	 */
	public static function calculateCode($data, $code){
		/* 校验优惠券码 */
		$rel = self::checkCode($code);
		if($rel['code']>0) return $rel;
		$ticket_data = $rel['data'];
		$data['final_sum'] = $data['sum'];
		
		/* 计算优惠 */
		switch($ticket_data['type']){
			//折扣券
			case 1 :
				$money       = $data['sum']*$ticket_data['ratio'];
				$data['sum'] = $money<=0 ? 0 : $money;
				$msg         = ($ticket_data['ratio']*10).'折优惠券';
				break;
			//抵扣券
			case 2 :
				$money       = $data['sum']-$ticket_data['money'];
				$data['sum'] = $money<=0 ? 0 : $money;
				$msg         = '抵'.$ticket_data['money'].'元优惠券';
				break;
			//包邮抵扣券
			case 3 :
				$money       = $data['sum']-$ticket_data['money'];
				$data['sum'] = $money<=0 ? 0 : $money;
				$msg         = '抵'.$ticket_data['money'].'元优惠券（包邮）';
				$data['is_delivery'] = 1; //包邮
				break;
			default:
				return apiReturn::go('002006');
		}
		
		/* 计算商品优惠后价格 */
		$data['goodsList'] = self::goodsPrice($data['goodsList'], $data['final_sum']-$data['sum'], $data['final_sum']);
		
		/* 优惠券 */
		$data['ticket'] = array(
			'ticket_did' => $ticket_data['id'],    //优惠券码ID
			'ticket_aid' => '',    //优惠券ID
			'name'       => $ticket_data['name'],    //优惠券名称
			'msg'        => $msg,
		);
		return apiReturn::go('0', $data);
	}
	
	/**
	 * 订单确认-优惠券计算价格
	 */
	public static function calculateActivity($data, $ticket_aid){
		/* 校验优惠券 */
		$rel = self::checkActivity($ticket_aid);
		if($rel['code']>0) return $rel;
		$ticket_data = $rel['data'];
		$msg         = '';
		
		/* 优惠券类型 */
		switch($ticket_data['type']){
			//满减券
			case 1:
				//优惠券规则
				$rule = explode(',', $ticket_data['rule']);
				if(count($rule)!=2 || $rule[0]<=0 || $rule[1]<=0)
					return apiReturn::go('002013');
				if($data['sum']<$rule[0])
					return apiReturn::go('002014');
				//计算优惠
				$money       = $data['sum']-$rule[1];
				$data['sum'] = $money<=0 ? 0 : $money;
				$msg         = '满'.$rule[0].'减'.$rule[1].'优惠券';
				break;
			//无门槛券
			case 2:
				//计算优惠
				$money       = $data['sum']-$ticket_data['rule'];
				$data['sum'] = $money<=0 ? 0 : $money;
				$msg         = $ticket_data['rule'].'元，无门槛券';
				break;
			//折扣券
			case 3:
				//计算优惠
				$money       = $data['sum']*$ticket_data['rule'];
				$data['sum'] = $money<=0 ? 0 : $money;
				$msg         = ($ticket_data['rule']*10).'折，折扣券';
				break;
			//商务合作券
			case 4:
				$data['is_delivery'] = 2; //不包邮
				//计算优惠
				$money       = $data['sum']-$ticket_data['rule'];
				$data['sum'] = $money<=0 ? 0 : $money;
				$msg         = $ticket_data['rule'].'元，商务合作券（不包邮）';
				break;
			//包邮券
			case 5:
				$data['is_delivery'] = 1; //包邮
				break;
			//税值券
			case 6:
//     			break;
			default:
				return apiReturn::go('002012');
		}
		
		/* 计算商品优惠后价格 */
		$data['goodsList'] = self::goodsPrice($data['goodsList'], $data['final_sum']-$data['sum'], $data['final_sum']);
		
		/* 优惠券 */
		$data['ticket'] = array(
			'ticket_did' => '',    //优惠券码ID
			'ticket_aid' => $ticket_aid,    //优惠券ID
			'name'       => $ticket_data['name'],    //优惠券名称
			'msg'        => $msg,
		);
		return apiReturn::go('0', $data);
	}
	
	/**
	 * 下订单-优惠券码code计算价格
	 */
	public static function finalCalculateCode($data, $ticket_did){
		/* 校验优惠券 */
		$model_ticket = new IModel('ticket_discount');
		$data_ticket  = $model_ticket->getObj('`start_time`<'.time().' AND `end_time`>'.time().' AND `status`=1 AND `id`='.$ticket_did, 'type,ratio,money');
		if(empty($data_ticket))
			return apiReturn::go('002002');
		//折扣券已使用状态
		$user_id = IWeb::$app->getController()->user['user_id'];
		$model_ticket->setData(array('status' => 2, 'user_id' => $user_id));
		$model_ticket->update('`id`='.$ticket_did);
		
		/* 计算优惠价格 */
		switch($data_ticket['type']){
			//折扣券
			case 1 :
				$data['sum'] = $data['sum']*$data_ticket['ratio'];
				break;
			//抵扣券
			case 2 :
				$data['sum'] = $data['sum']-$data_ticket['money'];
				break;
			//包邮抵扣券
			case 3 :
				$data['sum'] = $data['sum']-$data_ticket['money'];
				$data['is_delivery'] = 1; //包邮
				break;
		}
		/* 计算商品优惠后价格 */
		$data['goodsResult']['goodsList'] = self::goodsPrice($data['goodsResult']['goodsList'], $data['final_sum']-$data['sum'], $data['final_sum']);
		
		return apiReturn::go('0', $data);
	}
	
	/**
	 * 下订单-优惠券计算价格
	 */
	public static function finalCalculateActivity($data, $ticket_aid){
		/* 校验优惠券 */
		$rel = self::checkActivity($ticket_aid);
		if($rel['code']>0) return $rel;
		$ticket_data = $rel['data'];
		
		/* 优惠券类型 */
		switch($ticket_data['type']){
			//满减券
			case 1:
				//优惠券规则
				$rule = explode(',', $ticket_data['rule']);
				if(count($rule)!=2 || $rule[0]<=0 || $rule[1]<=0)
					return apiReturn::go('002013');
				if($data['sum']<$rule[0])
					return apiReturn::go('002014');
				//计算优惠
				$money       = $data['sum']-$rule[1];
				$data['sum'] = $money<=0 ? 0 : $money;
				break;
			//无门槛券
			case 2:
				//计算优惠
				$money       = $data['sum']-$ticket_data['rule'];
				$data['sum'] = $money<=0 ? 0 : $money;
				break;
			//折扣券
			case 3:
				//计算优惠
				$money       = $data['sum']*$ticket_data['rule'];
				$data['sum'] = $money<=0 ? 0 : $money;
				break;
			//商务合作券
			case 4:
				$data['is_delivery'] = 2; //不包邮
				//计算优惠
				$money       = $data['sum']-$ticket_data['rule'];
				$data['sum'] = $money<=0 ? 0 : $money;
				break;
			//包邮券
			case 5:
				$data['is_delivery'] = 1; //包邮
				break;
			//税值券
			case 6:
//     			break;
			default:
				return apiReturn::go('002012');
		}
		/* 计算商品优惠后价格 */
		$data['goodsResult']['goodsList'] = self::goodsPrice($data['goodsResult']['goodsList'], $data['final_sum']-$data['sum'], $data['final_sum']);
		
		/* 优惠券改为已使用 */
		$model_ticket = new IModel('activity_ticket_access');
		$model_ticket->setData(array('status' => 2));
		$model_ticket->update('`id`='.$ticket_aid);
		
		return apiReturn::go('0', $data);
	}
	
	/**
	 * 计算商品优惠价格
	 * @param array $data 商品列表
	 * @param int $lessenMoney 优惠金额
	 * @param int $totleMoney 总金额
	 */
	private static function goodsPrice($data, $lessenMoney, $totleMoney){
		if($lessenMoney<=0 || $totleMoney<=0) return $data;
		foreach($data as $k => $v){
			$data[$k]['reduce'] = round(($v['sell_price']/$totleMoney*$lessenMoney), 2);
		}
		return $data;
	}
	
	/**
	 * 验证优惠券码
	 */
	public static function checkCode($code = 0){
		/* 优惠券 */
		if(empty($code) || $code<=0 || $code>999999) return apiReturn::go('002001');
		/* 获取折扣券数据 */
		$query         = new IQuery('ticket_discount');
		$query->where  = 'code='.$code;
		$query->fields = 'id,name,type,ratio,money,start_time,end_time,status';
		$query->limit  = 1;
		$ticket_data   = $query->find();
		if(empty($ticket_data))
			return apiReturn::go('002002');
		$ticket_data = $ticket_data[0];
		if($ticket_data['start_time']>time() || $ticket_data['end_time']<time())
			return apiReturn::go('002003');
		if($ticket_data['status']==2)
			return apiReturn::go('002004');
		if($ticket_data['status']!=1)
			return apiReturn::go('002005');
		return apiReturn::go('0', $ticket_data);
	}
	
	/**
	 * 校验活动优惠券
	 */
	public static function checkActivity($ticket_aid){
		$user_id = IWeb::$app->getController()->user['user_id'];
		/* 获取优惠券信息 */
		$query         = new IQuery('activity_ticket as m');
		$query->join   = 'LEFT JOIN activity_ticket_access AS a ON a.ticket_id=m.id';
		$query->where  = 'a.id='.$ticket_aid.' AND a.user_id='.$user_id;
		$query->fields = 'm.start_time,m.end_time,m.name,m.type,m.rule,a.status';
		$query->limit  = 1;
		$data          = $query->find();
		//判断优惠券是否存在
		if(empty($data))
			return apiReturn::go('002007');
		$data = $data[0];
		if($data['start_time']>time())
			return apiReturn::go('002008');
		if($data['end_time']<time())
			return apiReturn::go('002009');
		if($data['status']!=1)
			return apiReturn::go('002011');
		return apiReturn::go('0', $data);
	}
	
	/**
	 * 获取配送、包邮
	 */
	public static function postage(){
		//满包邮
		$promotion_query        = new IQuery("promotion");
		$promotion_query->where = "type = 0 and seller_id = 0 and award_type = 6";
		$condition_price        = $promotion_query->find()[0]['condition'];
		//配送方式
		$delivery = Api::run('getDeliveryList');
		return array('condition' => $condition_price, 'delivery' => $delivery);
	}
	
	/**
	 * @brief 获取代金券状态数值
	 * @param array $ticketRow 代金券数据
	 * @return int 状态码 -1:已使用;-2:已禁用;-3:临时锁定;-4:已过期;1:可使用;
	 */
	public static function status($ticketRow){
		if($ticketRow['is_userd']==1){
			return -1;
		}
		
		if($ticketRow['is_close']==1){
			return -2;
		}
		
		if($ticketRow['is_close']==2){
			return -3;
		}
		
		if(ITime::getDateTime()>$ticketRow['end_time']){
			return -4;
		}
		return 1;
	}
	
	/**
	 * @brief 获取代金券的状态文字
	 * @param int $status 状态码
	 * @return string 状态文字
	 */
	public static function statusText($status){
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