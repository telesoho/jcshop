<?php
/**
 * @brief 营销模块
 * @class Market
 * @note  后台
 */
class Market extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
	public $layout = 'admin';

	function init()
	{

	}
	
	/**
	 * 限时活动列表
	 */
	public function activity_speed_list(){
		$this->redirect('activity_speed_list');
	}
	
	/**
	 * 限时活动添加
	 */
	public function activity_speed_add(){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			/* 更新主表 */
			switch($_POST['type']){
				case 1: //限时够
					switch($_POST['time_type']){
						case 1:
							$startTime = strtotime($_POST['time'].' 11:00:00');
							$endTime   = strtotime($_POST['time'].' 15:59:59');
							break;
						case 2:
							$startTime = strtotime($_POST['time'].' 16:00:00');
							$endTime   = strtotime($_POST['time'].' 21:59:59');
							break;
						case 3:
							$startTime = strtotime($_POST['time'].' 22:00:00');
							$endTime   = strtotime(date('Y-m-d', 60*60*24+strtotime($_POST['time'])).' 10:59:59');
							break;
						default:
							exit('类型不存在');
					}
					break;
				case 2: //秒杀
					$startTime = strtotime($_POST['start_time']);
					$endTime   = strtotime($_POST['end_time']);
					break;
			}
			$model = new IModel('activity_speed');
			$model->setData(array(
				'type' => $_POST['type'],
				'start_time'  => $startTime,
				'end_time'    => $endTime,
				'status'      => $_POST['status'],
				'update_time' => time(),
				'create_time' => time(),
			));
			$rel = $model->add();
			if(!$rel) exit('更新失败！');
			/* 更新副表 */
			$modelSpeed = new IModel('activity_speed_access');
			foreach($_POST['goods_id'] as $k => $v){
				$modelSpeed->setData(array(
					'pid'        => $rel,
					'goods_id'   => $_POST['goods_id'][$k],
					'sell_price' => $_POST['goods_sell_price'][$k],
					'nums'       => $_POST['goods_nums'][$k],
					'quota'      => $_POST['goods_quota'][$k],
					'delivery'   => isset($_POST['goods_delivery'][$k])&&$_POST['goods_delivery'][$k]==1 ? 1 : 0,
				));
				$modelSpeed->add();
			}
			$this->redirect('activity_speed_list');
		}
		
		/* 视图 */
		$this->redirect('activity_speed_add');
	}
	
	/**
	 * 限时活动编辑
	 */
	public function activity_speed_edit(){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			/* 更新主表 */
			switch($_POST['type']){
				case 1: //限时够
					switch($_POST['time_type']){
						case 1:
							$startTime = strtotime($_POST['time'].' 11:00:00');
							$endTime   = strtotime($_POST['time'].' 15:59:59');
							break;
						case 2:
							$startTime = strtotime($_POST['time'].' 16:00:00');
							$endTime   = strtotime($_POST['time'].' 21:59:59');
							break;
						case 3:
							$startTime = strtotime($_POST['time'].' 22:00:00');
							$endTime   = strtotime(date('Y-m-d', 60*60*24+strtotime($_POST['time'])).' 10:59:59');
							break;
						default:
							exit('类型不存在');
					}
					break;
				case 2: //秒杀
					$startTime = strtotime($_POST['start_time']);
					$endTime   = strtotime($_POST['end_time']);
					break;
			}
			$model = new IModel('activity_speed');
			$model->setData(array(
				'type' => $_POST['type'],
				'start_time'  => $startTime,
				'end_time'    => $endTime,
				'status'      => $_POST['status'],
				'update_time' => time(),
			));
			$rel = $model->update('id='.$_POST['id']);
			if(!$rel) exit('更新失败！');
			/* 更新副表 */
			$modelSpeed = new IModel('activity_speed_access');
			$modelSpeed->del('pid='.$_POST['id']);
			foreach($_POST['goods_id'] as $k => $v){
				$modelSpeed->setData(array(
					'pid'        => $_POST['id'],
					'goods_id'   => $_POST['goods_id'][$k],
					'sell_price' => $_POST['goods_sell_price'][$k],
					'nums'       => $_POST['goods_nums'][$k],
					'quota'      => $_POST['goods_quota'][$k],
					'delivery'   => isset($_POST['goods_delivery'][$k])&&$_POST['goods_delivery'][$k]==1 ? 1 : 0,
				));
				$modelSpeed->add();
			}
			
			$this->redirect('activity_speed_list');
		}
		/* 限时购详情 */
		$id         = IFilter::act(IReq::get('id'), 'int');//活动ID
		$modelSpeed = new IModel('activity_speed');
		$infoSpeed  = $modelSpeed->getObj('id='.$id);
		if(!empty($infoSpeed)){
			$queryAcc          = new IQuery('activity_speed_access AS m');
			$queryAcc->join    = 'LEFT JOIN goods AS g ON g.id=m.goods_id';
			$queryAcc->where   = 'pid='.$id;
			$queryAcc->fields  = 'm.*,g.name,g.img,g.sell_price AS now_sell_price,g.is_del,g.goods_no,g.store_nums';
			$infoSpeed['list'] = $queryAcc->find();
		}
		/* 视图 */
		$this->setRenderData(array('data' => $infoSpeed));
		$this->redirect('activity_speed_edit');
	}
	
	/**
	 * 砍价刀列表
	 */
	public function activity_bargain_list(){
		/* 模板赋值 */
		$this->redirect('activity_bargain_list');
	}

	/**
	 * 活动列表
	 */
	public function activity_list(){
		/* 模板赋值 */
		$this->redirect('activity_list');
	}
	
	/**
	 * 活动新增
	 */
	public function activity_add(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			foreach($_POST as $k => $v)
				if(empty($v)) exit('数据填写不完整');
			if(!isset($_POST['type']) || empty(implode(',',$_POST['type'])))
				exit('至少包含一种活动');
			/* 活动 */
			$dataActivity 				= array(
				'status' 		=> 1,
				'type' 			=> implode(',',$_POST['type']), //包含活动类型[1优惠券随机领取-2消费成长]
				'name' 			=> $_POST['name'], //活动名称
				'num' 			=> $_POST['num'], //优惠券总数
				'share_score' 	=> $_POST['share_score'], //分享获得积分数
				'share_num' 	=> $_POST['share_num'], //分享积分次数
				'start_time' 	=> strtotime($_POST['start_time']), //分享获得积分数
				'end_time' 		=> strtotime($_POST['end_time']), //分享获得积分数
				'create_time' 	=> time(), //创建时间
			);
			$modelActivity 				= new IModel('activity');
			$modelActivity->setData($dataActivity);
			$activity_id 				= $modelActivity->add();
			if($activity_id == false) exit('数据写入错误');
			$modelTicket 				= new IModel('activity_ticket');
			/* 优惠券随机领取活动 */
			if(in_array(1,$_POST['type'])){
				foreach($_POST['ticket_name'] as $k => $v){
					$dataTicket 		= array(
						'pid' 			=> $activity_id, //活动ID，0为普通优惠券
						'name' 			=> $_POST['ticket_name'][$k], //优惠券名称
						'type' 			=> $_POST['ticket_type'][$k], //优惠券类型
						'rule' 			=> $_POST['ticket_rule'][$k], //优惠规则
						'create_time' 	=> time(),
						'start_time' 	=> strtotime($_POST['ticket_start_time'][$k]),
						'end_time' 		=> strtotime($_POST['ticket_end_time'][$k]),
					);
					$modelTicket->setData($dataTicket);
					$modelTicket->add();
				}
			}
			/* 消费成长活动 */
			if(in_array(2,$_POST['type'])){
				$modelGrow 				= new IModel('activity_grow');
				foreach($_POST['grow_money'] as $k => $v){
					$did 				= $_POST['goods_id'][$k];
					//优惠券
					if( $_POST['grow_type'][$k]==1 ){
						$dataTicket 		= array(
							'pid' 			=> 0, //活动ID，0为普通优惠券
							'name' 			=> $_POST['grow_ticket_name'][$k], //优惠券名称
							'type' 			=> $_POST['grow_ticket_type'][$k], //优惠券类型
							'rule' 			=> $_POST['grow_ticket_rule'][$k], //优惠规则
							'create_time' 	=> time(),
							'start_time' 	=> strtotime($_POST['grow_ticket_start_time'][$k]),
							'end_time' 		=> strtotime($_POST['grow_ticket_end_time'][$k]),
						);
						$modelTicket->setData($dataTicket);
						$did 				= $modelTicket->add();
					}
					$dataGrow 			= array(
						'pid' 			=> $activity_id, //活动ID
						'grow' 			=> $_POST['grow_money'][$k], //成长值
						'type' 			=> $_POST['grow_type'][$k], //礼品类型[1优惠券-2商品]
						'did' 			=> $did, //礼品ID
					);
					$modelGrow->setData($dataGrow);
					$modelGrow->add();
				}
			}
				
			/* 成功跳转 */
			$this->redirect('activity_list');
		}
		$this->redirect('activity_add');
	}
	/**
	 * 编辑活动商品
	 */
	public function activity_goods_edit(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
// 			exit(json_encode( $_POST ));
			/* 商品添加、删除操作 */
			if(isset($_POST['play'])){
				$modelGoods 			= new IModel('goods');
				switch($_POST['play']){
					case 'add':
						$goods_ids 		= array();
						foreach($_POST['data'] as $k => $v)
							$goods_ids[] 	= $v['goods_id'];
						$modelGoods->setData(array('activity'=>$_POST['aid']));
						$rel 			= $modelGoods->update('activity=0 AND id IN ('.implode(',',$goods_ids).')');
						exit(json_encode( $rel>0 ? array('code'=>'0','msg'=>'有'.$rel.'条数据添加成功') : array('code'=>'100','data'=>'数据写入错误') ));
						break;
					case 'del':
						$modelGoods->setData(array('activity'=>0));
						$rel 			= $modelGoods->update('id='.$_POST['data']);
						exit(json_encode( $rel>0 ? array('code'=>'0','msg'=>'删除成功') : array('code'=>'100','data'=>'删除失败') ));
						break;
				}
			}
			/* 正常提交 */
			if(!isset($_POST['ratio']) || $_POST['ratio']<=0 || $_POST['ratio']>=1)
				exit('请正确配置折扣率');
			$model 						= new IModel('activity');
			$model->setData(array('ratio'=>$_POST['ratio']));
			$model->update('id='.$_POST['id']);
			/* 成功跳转 */
			$this->redirect('activity_list');
		}
		/* 获取参数 */
		$aid        					= IFilter::act(IReq::get('id'),'int');//活动ID
		if(empty($aid)) exit('ID不存在');
		$model 							= new IModel('activity');
		$data 							= $model->getObj('id='.$aid);
		if(empty($data)) exit('活动不存在');
		
		/* 获取活动商品 */
		$query 							= new IQuery('goods');
		$query->where 					= 'activity='.$aid;
		$query->fields 					= 'id,goods_no,img,name,is_del';
		$data['list'] 					= $query->find();
		
		/* 模板赋值 */
		$this->setRenderData(array('data'=>$data));
		$this->redirect('activity_goods_edit',false);
	}

	/**
	 * 优惠券码批量添加
	 */
	public function ticket_discount_add(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			/* 检查参数 */
			$_POST['start_time'] 		= strtotime($_POST['start_time']); 	//开始时间
			$_POST['end_time'] 			= strtotime($_POST['end_time']); 	//结束时间
			if($_POST['end_time']<=time() || $_POST['start_time']>=$_POST['end_time'])  exit( '有效时间不合理' );
			if($_POST['num']>500) exit( '每次请勿生成超过500张' );
			switch ($_POST['type']){
				case 1:
					if($_POST['ratio']<=0 || $_POST['ratio']>=1) exit( '折扣比例必须为0~1之间的数值' );
					break;
				case 2:
					if($_POST['money']<=0 || $_POST['money']>3000) exit( '请输入合理的抵扣金额' );
					break;
				default:
					exit( '优惠券类型不存在' );
			}
			/* 清空未使用但已过期优惠券 */
			$model 						= new IModel('ticket_discount');
			$model->del('status!=2 AND end_time<'.time());
			
			/* 生成折扣券 */
			$num 						= 0;
			$count 						= $model->get_count('');
			if($count+$_POST['num'] >= 899999) exit( '所有号段已用完，请扩展号段' );
			while( $num<$_POST['num'] ){
				//检查没有重复
				$rand 					= rand(100000,999999);
				$info 					= $model->getObj('code='.$rand,'id');
				if( !empty($info) ) continue;
				//写入数据库
				$model->setData(array(
					'name' 			=> $_POST['name'],
					'code' 			=> $rand,
					'type' 			=> $_POST['type'],
					'ratio' 		=> $_POST['ratio'],
					'money' 		=> $_POST['money'],
					'status' 		=> 1,
					'start_time' 	=> $_POST['start_time'],
					'end_time' 		=> $_POST['end_time'],
					'create_time' 	=> time(),
				));
				$model->add();
				$num++;
			}
			$this->redirect('ticket_discount_list');
		}
		$this->redirect('ticket_discount_add');
	}
	
	/**
	 * 优惠券码删除
	 */
	function ticket_discount_del(){
		//接收参数
		$id        			= IFilter::act(IReq::get('id'),'int');
		if(empty($id)){
			$this->redirect('ticket_discount_list',false);
			Util::showMessage('请选择要删除的id值');exit();
		}
		//执行删除
		$ticketObj 			= new IModel('ticket_discount');
		$where 				= is_array($id) ? ' id in ('.join(',',$id).')' : 'id = '.$id;
		$ticketObj->del($where);
		$this->redirect('ticket_discount_list',false);
		Util::showMessage('删除成功');exit();
	}
	
	/**
	 * 优惠券码-输出excel表格
	 */
	function ticket_discount_excel()
	{
	
		$excelStr = '<table width="500" border="1"><tr>
					<th style="text-align:center;font-size:12px;width:120px;">卷码</th>
					<th style="text-align:center;font-size:12px;width:120px;">名称</th>
					<th style="text-align:center;font-size:12px;width:120px;">类型</th>
					<th style="text-align:center;font-size:12px;width:120px;">优惠内容</th>
					<th style="text-align:center;font-size:12px;width:120px;">状态</th>
					<th style="text-align:center;font-size:12px;width:120px;">开始时间</th>
					<th style="text-align:center;font-size:12px;width:120px;">结束时间</th></tr>';
	
		$query_ticket 		 	= new IQuery('ticket_discount');
		$query_ticket->limit 	= 10000;
		$data_ticket 			= $query_ticket->find();
		$text_type 				= array('','折扣券','抵扣券');
		$text_status 			= array('','未使用','已使用');
		foreach($data_ticket as $key => $val)
		{
			$content 			= '';
			switch($val['type']){
				case 1:
					$content 	= '打'.($val['ratio']*10).'折';
					break;
				case 2:
					$content 	= '抵'.$val['money'].'元';
					break;
			}
			$excelStr 			.='<tr>';
			$excelStr 			.='<td style="text-align:left;font-size:12px;">'.$val['code'].'</td>';
			$excelStr 			.='<td style="text-align:left;font-size:12px;">'.$val['name'].'</td>';
			$excelStr 			.='<td style="text-align:left;font-size:12px;">'.$text_type[$val['type']].'</td>';
			$excelStr 			.='<td style="text-align:left;font-size:12px;">'.$content.'</td>';
			$excelStr 			.='<td style="text-align:left;font-size:12px;">'.$text_status[$val['status']].'</td>';
			$excelStr 			.='<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['start_time']).'</td>';
			$excelStr 			.='<td style="text-align:left;font-size:12px;">'.date('Y-m-d H:i',$val['end_time']).'</td>';
			$excelStr 			.='</tr>';
		}
		$excelStr 				.='</table>';
		$reportObj 				= new report();
		$reportObj->setFileName('优惠券');
		$reportObj->toDownload($excelStr);
		exit();
	
	}
	
	
	//修改代金券状态is_close和is_send
	function ticket_status()
	{
		$status    = IFilter::act(IReq::get('status'));
		$id        = IFilter::act(IReq::get('id'),'int');
		$ticket_id = IFilter::act(IReq::get('ticket_id'));

		if(!empty($id) && $status != null && $ticket_id != null)
		{
			$ticketObj = new IModel('prop');
			if(is_array($id))
			{
				foreach($id as $val)
				{
					$where = 'id = '.$val;
					$ticketRow = $ticketObj->getObj($where,$status);
					if($ticketRow[$status]==1)
					{
						$ticketObj->setData(array($status => 0));
					}
					else
					{
						$ticketObj->setData(array($status => 1));
					}
					$ticketObj->update($where);
				}
			}
			else
			{
				$where = 'id = '.$id;
				$ticketRow = $ticketObj->getObj($where,$status);
				if($ticketRow[$status]==1)
				{
					$ticketObj->setData(array($status => 0));
				}
				else
				{
					$ticketObj->setData(array($status => 1));
				}
				$ticketObj->update($where);
			}
			$this->redirect('ticket_more_list/ticket_id/'.$ticket_id);
		}
		else
		{
			$this->ticket_id = $ticket_id;
			$this->redirect('ticket_more_list',false);
			Util::showMessage('请选择要修改的id值');
		}
	}

	//[代金券]添加,修改[单页]
	function ticket_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$ticketObj       = new IModel('ticket');
			$where           = 'id = '.$id;
			$this->ticketRow = $ticketObj->getObj($where);
		}
		$this->redirect('ticket_edit');
	}

	//[代金券]添加,修改[动作]
	function ticket_edit_act()
	{
		$id        = IFilter::act(IReq::get('id'),'int');
		$ticketObj = new IModel('ticket');

		$dataArray = array(
			'name'      => IFilter::act(IReq::get('name','post')),
			'value'     => IFilter::act(IReq::get('value','post')),
			'start_time'=> IFilter::act(IReq::get('start_time','post')),
			'end_time'  => IFilter::act(IReq::get('end_time','post')),
			'point'     => IFilter::act(IReq::get('point','post')),
		);

		$ticketObj->setData($dataArray);
		if($id)
		{
			$where = 'id = '.$id;
			$ticketObj->update($where);
		}
		else
		{
			$ticketObj->add();
		}
		$this->redirect('ticket_list');
	}

	//[代金券]生成[动作]
	function ticket_create()
	{
		$propObj   = new IModel('prop');
		$prop_num  = intval(IReq::get('num'));
		$ticket_id = intval(IReq::get('ticket_id'));

		if($prop_num && $ticket_id)
		{
			$prop_num  = ($prop_num > 5000) ? 5000 : $prop_num;
			$ticketObj = new IModel('ticket');
			$where     = 'id = '.$ticket_id;
			$ticketRow = $ticketObj->getObj($where);

			for($item = 0; $item < intval($prop_num); $item++)
			{
				$dataArray = array(
					'condition' => $ticket_id,
					'name'      => $ticketRow['name'],
					'card_name' => 'T'.IHash::random(8),
					'card_pwd'  => IHash::random(8),
					'value'     => $ticketRow['value'],
					'start_time'=> $ticketRow['start_time'],
					'end_time'  => $ticketRow['end_time'],
				);

				//判断code码唯一性
				$where = 'card_name = \''.$dataArray['card_name'].'\'';
				$isSet = $propObj->getObj($where);
				if(!empty($isSet))
				{
					$item--;
					continue;
				}
				$propObj->setData($dataArray);
				$propObj->add();
			}
			$logObj = new Log('db');
			$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"生成了代金券","面值：".$ticketRow['value']."元，数量：".$prop_num."张"));
		}
		$this->redirect('ticket_list');
	}

	//[代金券]删除
	function ticket_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$ticketObj = new IModel('ticket');
			$propObj   = new IModel('prop');
			$propRow   = $propObj->getObj(" `type` = 0 and `condition` = {$id} and (is_close = 2 or (is_userd = 0 and is_send = 1)) ");

			if($propRow)
			{
				$this->redirect('ticket_list',false);
				Util::showMessage('无法删除代金券，其下还有正在使用的代金券');
				exit;
			}

			$where = "id = {$id} ";
			$ticketRow = $ticketObj->getObj($where);
			if($ticketObj->del($where))
			{
				$where = " `type` = 0 and `condition` = {$id} ";
				$propObj->del($where);

				$logObj = new Log('db');
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"删除了一种代金券","代金券名称：".$ticketRow['name']));
			}
			$this->redirect('ticket_list');
		}
		else
		{
			$this->redirect('ticket_list',false);
			Util::showMessage('请选择要删除的id值');
		}
	}
	

	//[代金券详细]删除
	function ticket_more_del()
	{
		$id        = IFilter::act(IReq::get('id'),'int');
		$ticket_id = IFilter::act(IReq::get('ticket_id'),'int');
		if($id)
		{
			$ticketObj = new IModel('ticket');
			$ticketRow = $ticketObj->getObj('id = '.$ticket_id);
			$logObj    = new Log('db');
			$propObj   = new IModel('prop');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"批量删除了实体代金券","代金券名称：".$ticketRow['name']."，数量：".count($id)));
			}
			else
			{
				$where = 'id = '.$id;
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"删除了1张实体代金券","代金券名称：".$ticketRow['name']));
			}
			$propObj->del($where);
			$this->redirect('ticket_more_list/ticket_id/'.$ticket_id);
		}
		else
		{
			$this->ticket_id = $ticket_id;
			$this->redirect('ticket_more_list',false);
			Util::showMessage('请选择要删除的id值');
		}
	}

	//[代金券详细] 列表
	function ticket_more_list()
	{
		$this->ticket_id = IFilter::act(IReq::get('ticket_id'),'int');
		$this->redirect('ticket_more_list');
	}

	//[代金券] 输出excel表格
	function ticket_excel()
	{
		//代金券excel表存放地址
		$ticket_id = IFilter::act(IReq::get('id'));

		if($ticket_id)
		{
			$excelStr = '<table><tr><th>名称</th><th>卡号</th><th>密码</th><th>面值</th>
			<th>已被使用</th><th>是否关闭</th><th>是否发送</th><th>开始时间</th><th>结束时间</th></tr>';

			$propObj = new IModel('prop');
			$where   = 'type = 0';
			$ticket_id_array = is_array($ticket_id) ? $ticket_id : array($ticket_id);

			//当代金券数量没有时不允许备份excel
			foreach($ticket_id_array as $key => $tid)
			{
				if(statistics::getTicketCount($tid) == 0)
				{
					unset($ticket_id_array[$key]);
				}
			}

			if($ticket_id_array)
			{
				$id_num_str = join('","',$ticket_id_array);
			}
			else
			{
				$this->redirect('ticket_list',false);
				Util::showMessage('实体代金券数量为0张，无法备份');
				exit;
			}

			$where.= ' and `condition` in("'.$id_num_str.'")';

			$propList = $propObj->query($where,'*','`condition` asc',10000);
			foreach($propList as $key => $val)
			{
				$is_userd = ($val['is_userd']=='1') ? '是':'否';
				$is_close = ($val['is_close']=='1') ? '是':'否';
				$is_send  = ($val['is_send']=='1') ? '是':'否';

				$excelStr.='<tr>';
				$excelStr.='<td>'.$val['name'].'</td>';
				$excelStr.='<td>'.$val['card_name'].'</td>';
				$excelStr.='<td>'.$val['card_pwd'].'</td>';
				$excelStr.='<td>'.$val['value'].' 元</td>';
				$excelStr.='<td>'.$is_userd.'</td>';
				$excelStr.='<td>'.$is_close.'</td>';
				$excelStr.='<td>'.$is_send.'</td>';
				$excelStr.='<td>'.$val['start_time'].'</td>';
				$excelStr.='<td>'.$val['end_time'].'</td>';
				$excelStr.='</tr>';
			}
			$excelStr.='</table>';

			$ticketFile = "ticket_".join("_",$ticket_id_array);
			$reportObj = new report($ticketFile);
			$reportObj->toDownload($excelStr);
		}
		else
		{
			$this->redirect('ticket_list',false);
			Util::showMessage('请选择要操作的文件');
		}
	}

	//[代金券]获取代金券数据
	function getTicketList()
	{
		$ticketObj  = new IModel('ticket');
		$ticketList = $ticketObj->query();
		echo JSON::encode($ticketList);
	}

	//[促销活动] 添加修改 [单页]
	function pro_rule_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$promotionObj = new IModel('promotion');
			$where = 'id = '.$id;
			$this->promotionRow = $promotionObj->getObj($where);
		}
		$this->redirect('pro_rule_edit');
	}

	//[促销活动] 添加修改 [动作]
	function pro_rule_edit_act()
	{
        $id           = IFilter::act(IReq::get('id'),'int');
		$user_group   = IFilter::act(IReq::get('user_group','post'));
		$promotionObj = new IModel('promotion');
		if(is_string($user_group))
		{
			$user_group_str = $user_group;
		}
		else
		{
			$user_group_str = ",".join(',',$user_group).",";
		}

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'condition'  => IFilter::act(IReq::get('condition','post')),
			'is_close'   => IFilter::act(IReq::get('is_close','post')),
			'start_time' => IFilter::act(IReq::get('start_time','post')),
			'end_time'   => IFilter::act(IReq::get('end_time','post')),
			'intro'      => IFilter::act(IReq::get('intro','post')),
			'award_type' => IFilter::act(IReq::get('award_type','post')),
			'type'       => 0,
			'user_group' => $user_group_str,
			'award_value'=> IFilter::act(IReq::get('award_value','post')),
		);

		$promotionObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$promotionObj->update($where);
		}
		else
		{
			$promotionObj->add();
		}
		$this->redirect('pro_rule_list');
	}

	//[促销活动] 删除
	function pro_rule_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$promotionObj = new IModel('promotion');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$promotionObj->del($where);
			$this->redirect('pro_rule_list');
		}
		else
		{
			$this->redirect('pro_rule_list',false);
			Util::showMessage('请选择要删除的促销活动');
		}
	}

	//[限时抢购]添加,修改[单页]
	function pro_speed_edit()
	{
		$name = IFilter::act(IReq::get('name'),'string');
//		$id = IFilter::act(IReq::get('id'),'int');
		if($name)
		{
			$promotionObj = new IModel('promotion');
			$where = 'name = "'.$name . '"';
//			$where = 'id = '.$id;
            $promotionQuery = $promotionObj->query($where);
//			$promotionRow = $promotionObj->getObj($where);
			if(empty($promotionQuery))
			{
				$this->redirect('pro_speed_list');
			}

            for ($i=0;$i<count($promotionQuery);$i++){
                //id集合
                $temp_ids[] = $promotionQuery[$i]['id'];

                //促销商品
                $goodsObj = new IModel('goods');
                $goodsRow = $goodsObj->getObj('id = '.$promotionQuery[$i]['condition'],'id,name,sell_price,img');
                if($goodsRow)
                {
                    $goodsRow['award_value'] = $promotionQuery[$i]['award_value'];
                    $result[] = array(
                        'isError' => false,
                        'data'    => $goodsRow,
                    );
                }
                else
                {
                    $result[] = array(
                        'isError' => true,
                        'message' => '关联商品被删除，请重新选择要抢购的商品',
                    );
                }
            }

            $promotionQuery['goodsRow'] = JSON::encode($result);
			$this->promotionRow = $promotionQuery;
			$this->temp_ids = $temp_ids;
		}
//		var_dump($this->promotionRow);
		$this->redirect('pro_speed_edit');
	}

	//[限时抢购]添加,修改[动作]
    function pro_speed_edit_act(){
        $id = IFilter::act(IReq::get('id'),'int');
        $condition_new   = IFilter::act(IReq::get('condition','post'));
        $award_value_new = IFilter::act(IReq::get('award_value','post'));
        $user_group  = IFilter::act(IReq::get('user_group','post'));
        $temp_ids  = IFilter::act(IReq::get('temp_ids','post'));
        for ($i=0;$i<count($condition_new);$i++){
            $condition = $condition_new[$i];
            $award_value = $award_value_new[$i];
            if(is_string($user_group))
            {
                $user_group_str = $user_group;
            }
            else
            {
                $user_group_str = ",".join(',',$user_group).",";
            }

            $dataArray = array(
//                'id'         => $id,
                'name'       => IFilter::act(IReq::get('name','post')),
                'condition'  => $condition,
                'award_value'=> $award_value,
                'is_close'   => IFilter::act(IReq::get('is_close','post')),
                'start_time' => IFilter::act(IReq::get('start_time','post')),
                'end_time'   => IFilter::act(IReq::get('end_time','post')),
                'intro'      => IFilter::act(IReq::get('intro','post')),
                'type'       => 1,
                'award_type' => 0,
                'user_group' => $user_group_str,
            );

            if(!$condition || !$award_value)
            {
                $this->promotionRow = $dataArray;
                $this->redirect('pro_speed_edit',false);
                Util::showMessage('请添加促销的商品，并为商品填写价格');
            }

            $proObj = new IModel('promotion');
            $proObj->setData($dataArray);
            if($temp_ids[$i])
            {
                $where = 'id = '.$temp_ids[$i];
                $proObj->update($where);
            }
            else
            {
                $proObj->add();
            }
        }
        $this->redirect('pro_speed_list');
    }
	function pro_speed_edit_act_a()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		$condition   = IFilter::act(IReq::get('condition','post'));
		$award_value = IFilter::act(IReq::get('award_value','post'));
		$user_group  = IFilter::act(IReq::get('user_group','post'));

		if(is_string($user_group))
		{
			$user_group_str = $user_group;
		}
		else
		{
			$user_group_str = ",".join(',',$user_group).",";
		}

		$dataArray = array(
			'id'         => $id,
			'name'       => IFilter::act(IReq::get('name','post')),
			'condition'  => $condition,
			'award_value'=> $award_value,
			'is_close'   => IFilter::act(IReq::get('is_close','post')),
			'start_time' => IFilter::act(IReq::get('start_time','post')),
			'end_time'   => IFilter::act(IReq::get('end_time','post')),
			'intro'      => IFilter::act(IReq::get('intro','post')),
			'type'       => 1,
			'award_type' => 0,
			'user_group' => $user_group_str,
		);

		if(!$condition || !$award_value)
		{
			$this->promotionRow = $dataArray;
			$this->redirect('pro_speed_edit',false);
			Util::showMessage('请添加促销的商品，并为商品填写价格');
		}

		$proObj = new IModel('promotion');
		$proObj->setData($dataArray);
		if($id)
		{
			$where = 'id = '.$id;
			$proObj->update($where);
		}
		else
		{
			$proObj->add();
		}
		$this->redirect('pro_speed_list');
	}

	//[限时抢购]删除
	function pro_speed_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$propObj = new IModel('promotion');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$where .= ' and type = 1';
			$data = $propObj->getObj($where);
            $where = 'type=1 and name="' . $data['name'] . '"';
			$propObj->del($where);

			$this->redirect('pro_speed_list');
		}
		else
		{
			$this->redirect('pro_speed_list',false);
			Util::showMessage('请选择要删除的id值');
		}
	}

	//[团购]添加修改[单页]
	function regiment_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if($id)
		{
			$regimentObj = new IModel('regiment');
			$where       = 'id = '.$id;
			$regimentRow = $regimentObj->getObj($where);
			if(!$regimentRow)
			{
				$this->redirect('regiment_list');
			}

			//促销商品
			$goodsObj = new IModel('goods');
			$goodsRow = $goodsObj->getObj('id = '.$regimentRow['goods_id']);

			$result = array(
				'isError' => false,
				'data'    => $goodsRow,
			);
			$regimentRow['goodsRow'] = JSON::encode($result);
			$this->regimentRow = $regimentRow;
		}
		$this->redirect('regiment_edit');
	}

	//[团购]添加修改[动作]
	function regiment_edit_act()
	{
		$id      = IFilter::act(IReq::get('id'),'int');
		$goodsId = IFilter::act(IReq::get('goods_id'),'int');

		$dataArray = array(
			'id'        	=> $id,
			'title'     	=> IFilter::act(IReq::get('title','post')),
			'start_time'	=> IFilter::act(IReq::get('start_time','post')),
			'end_time'  	=> IFilter::act(IReq::get('end_time','post')),
			'is_close'      => IFilter::act(IReq::get('is_close','post')),
			'intro'     	=> IFilter::act(IReq::get('intro','post')),
			'goods_id'      => $goodsId,
			'store_nums'    => IFilter::act(IReq::get('store_nums','post')),
			'limit_min_count' => IFilter::act(IReq::get('limit_min_count','post'),'int'),
			'limit_max_count' => IFilter::act(IReq::get('limit_max_count','post'),'int'),
			'regiment_price'=> IFilter::act(IReq::get('regiment_price','post')),
			'sort'          => IFilter::act(IReq::get('sort','post')),
		);

		$dataArray['limit_min_count'] = $dataArray['limit_min_count'] <= 0 ? 1 : $dataArray['limit_min_count'];
		$dataArray['limit_max_count'] = $dataArray['limit_max_count'] <= 0 ? $dataArray['store_nums'] : $dataArray['limit_max_count'];

		if($goodsId)
		{
			$goodsObj = new IModel('goods');
			$where    = 'id = '.$goodsId;
			$goodsRow = $goodsObj->getObj($where);

			//处理上传图片
			if(isset($_FILES['img']['name']) && $_FILES['img']['name'] != '')
			{
				$uploadObj = new PhotoUpload();
				$photoInfo = $uploadObj->run();
				$dataArray['img'] = $photoInfo['img']['img'];
			}
			else
			{
				$dataArray['img'] = $goodsRow['img'];
			}

			$dataArray['sell_price'] = $goodsRow['sell_price'];
		}
		else
		{
			$this->regimentRow = $dataArray;
			$this->redirect('regiment_edit',false);
			Util::showMessage('请选择要关联的商品');
		}

		$regimentObj = new IModel('regiment');
		$regimentObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$regimentObj->update($where);
		}
		else
		{
			$regimentObj->add();
		}
		$this->redirect('regiment_list');
	}

	//[团购]删除
	function regiment_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$regObj = new IModel('regiment');
			if(is_array($id))
			{
				$id    = join(',',$id);
			}
			$where = ' id in ('.$id.')';
			$regObj->del($where);
			$this->redirect('regiment_list');
		}
		else
		{
			$this->redirect('regiment_list',false);
			Util::showMessage('请选择要删除的id值');
		}
	}

	//账户余额记录
	function account_list()
	{
		$page       = intval(IReq::get('page')) ? IReq::get('page') : 1;
		$event      = intval(IReq::get('event'));
		$startDate  = IFilter::act(IReq::get('startDate'));
		$endDate    = IFilter::act(IReq::get('endDate'));

		$where      = "event != 3";
		if($startDate)
		{
			$where .= " and time >= '{$startDate}' ";
		}

		if($endDate)
		{
			$temp   = $endDate.' 23:59:59';
			$where .= " and time <= '{$temp}' ";
		}

		if($event)
		{
			$where .= " and event = $event ";
		}

		$accountObj = new IQuery('account_log');
		$accountObj->where = $where;
		$accountObj->order = 'id desc';
		$accountObj->page  = $page;

		$this->accountObj  = $accountObj;
		$this->event       = $event;
		$this->startDate   = $startDate;
		$this->endDate     = $endDate;
		$this->accountList = $accountObj->find();

		$this->redirect('account_list');
	}

	//后台操作记录
	function operation_list()
	{
		$page       = intval(IReq::get('page')) ? IReq::get('page') : 1;
		$startDate  = IFilter::act(IReq::get('startDate'));
		$endDate    = IFilter::act(IReq::get('endDate'));

		$where      = "1";
		if($startDate)
		{
			$where .= " and datetime >= '{$startDate}' ";
		}

		if($endDate)
		{
			$temp   = $endDate.' 23:59:59';
			$where .= " and datetime <= '{$temp}' ";
		}

		$operationObj = new IQuery('log_operation');
		$operationObj->where = $where;
		$operationObj->order = 'id desc';
		$operationObj->page  = $page;

		$this->operationObj  = $operationObj;
		$this->startDate     = $startDate;
		$this->endDate       = $endDate;
		$this->operationList = $operationObj->find();

		$this->redirect('operation_list');
	}

	//清理后台管理员操作日志
	function clear_log()
	{
		$type  = IReq::get('type');
		$month = intval(IReq::get('month'));
		if(!$month)
		{
			die('请填写要清理日志的月份');
		}

		$diffSec = 3600*24*30*$month;
		$lastTime= strtotime(date('Y-m')) - $diffSec;
		$dateStr = date('Y-m',$lastTime);

		switch($type)
		{
			case "account":
			{
				$logObj = new IModel('account_log');
				$logObj->del("time <= '{$dateStr}'");
				$this->redirect('account_list');
				break;
			}
			case "operation":
			{
				$logObj = new IModel('log_operation');
				$logObj->del("datetime <= '{$dateStr}'");
				$this->redirect('operation_list');
				break;
			}
			default:
				die('缺少类别参数');
		}
	}

	//修改结算账单
	public function bill_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$billDB = new IModel('bill');
		$this->billRow = $billDB->getObj('id = '.$id);
		$this->redirect('bill_edit');
	}

	//结算单修改
	public function bill_update()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$pay_content = IFilter::act(IReq::get('pay_content'));
		$is_pay = IFilter::act(IReq::get('is_pay'),'int');

		if($id)
		{
			$data = array(
				'admin_id' => $this->admin['admin_id'],
				'pay_content' => $pay_content,
				'is_pay' => $is_pay,
			);

			$billDB = new IModel('bill');

			$data['pay_time'] = ($is_pay == 1) ? ITime::getDateTime() : "";

			$billRow= $billDB->getObj('id = '.$id);
			if(isset($billRow['order_ids']) && $billRow['order_ids'])
			{
				//更新订单商品关系表中的结算字段
				$orderDB = new IModel('order');
				$orderIdArray = explode(',',$billRow['order_ids']);
				foreach($orderIdArray as $key => $val)
				{
					$orderDB->setData(array('is_checkout' => $is_pay));
					$orderDB->update('id = '.$val);
				}
			}

			$billDB->setData($data);
			$billDB->update('id = '.$id);
		}
		$this->redirect('bill_list');
	}

	//结算单删除
	public function bill_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if($id)
		{
			$billDB = new IModel('bill');
			$billDB->del('id = '.$id.' and is_pay = 0');
		}

		$this->redirect('bill_list');
	}

	//导出用户统计数据
	public function user_report(){
		$start = IFilter::act(IReq::get('start'));
		$end   = IFilter::act(IReq::get('end'));

		$_date = statistics::dateParse($start,$end);//处理起始和终止时间
		$startTime = strtotime($_date[0]); //开始时间戳
		$endTime = strtotime($_date[1]); //结束时间戳


		$dayNum = 1+($endTime-$startTime)/86400; //时间段内的天数


		$strTable ='<table style="width:475px;" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="width:120px;font-size:12px;padding:4px;text-align:center;">日期</td>';
		$strTable .= '<td width="*" style="font-size:12px;padding:4px;text-align:center;">用户名</td>';
		$strTable .= '<td style="width:40px;font-size:12px;padding:4px;text-align:center;">总数</td>';
		$strTable .= '</tr>';

		$memberQuery= new IQuery('member as m');
		$memberQuery->join = 'left join user as u on m.user_id=u.id';
		$memberQuery->fields = 'u.username,m.time';

		for($i=0;$i<$dayNum;$i++){
			//构建sql语句
			$endTime = $startTime+86400;
			$where = "m.time between '".date('Y-m-d',$startTime)."' and '".date('Y-m-d',$endTime)."'";
			$startTime = $endTime;
			$memberQuery->where = $where;
			$memberList = $memberQuery->find();
			$count = count($memberList);
			if ($count>0){
				foreach($memberList as $k=>$val){
					$strTable .= '<tr>';
					$strTable .= '<td style="font-size:12px;padding:4px;">'.$val['time'].' </td>';
					$strTable .= '<td style="text-align:left;font-size:12px;padding:4px">'.$val['username'].' </td>';
					if ($count>1) {
						$strTable .= '<td style="text-align:center;font-size:12px;padding:4px;line-height:'.$count.'em;" rowspan='.$count.' >'.$count.'</td>';
					}elseif($count==1) {
						$strTable .= '<td style="text-align:center;font-size:12px;padding:4px">'.$count.'</td>';
					}
					$strTable .= '</tr>';
					$count=0;
				}
			}
		}

		$strTable .='</table>';
		$reportObj = new report();
		$reportObj->setFileName('user');
		$reportObj->toDownload($strTable);
		exit();
	}

	//导出人均消费数据
	public function spanding_report(){
		$start = IFilter::act(IReq::get('start'));
		$end   = IFilter::act(IReq::get('end'));

		//获取时间段
		$_date       = statistics::dateParse($start,$end);
		$startArray = explode('-',$_date[0]);
		$endArray   = explode('-',$_date[1]);
		$startCondition = $startArray[0].'-'.$startArray[1].'-'.$startArray[2];
		$endCondition   = $endArray[0].'-'.$endArray[1].'-'.($endArray[2]+1);

		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;width:120px;" >日期</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;">人均消费金额</td>';
		$strTable .= '</tr>';

		$db = new IQuery('collection_doc');
		$db->fields = 'sum(amount)/count(*) as count,`time`';
		$db->where  = 'pay_status = 1';
		$db->group   = "DATE_FORMAT(`time`,'Y-%m-%d') having `time` >= '{$startCondition}' and `time` < '{$endCondition}'";
		$spandingList = $db->find();
		foreach($spandingList as $k=>$val){
			$strTable .= '<tr>';
			$strTable .= '<td style="text-align:center;font-size:12px;">'.$val['time'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['count'].' </td>';
			$strTable .= '</tr>';
		}
		$strTable .='</table>';

		$reportObj = new report();
		$reportObj->setFileName('spanding');
		$reportObj->toDownload($strTable);
		exit();
	}

	//导出销售数据
	public function amount_report(){
		$start = IFilter::act(IReq::get('start'));
		$end   = IFilter::act(IReq::get('end'));

		//获取时间段
		$_date = statistics::dateParse($start,$end);
		$startCondition = $_date[0];
		$endCondition   = $_date[1];
		$result         = array();

		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="150px">完成订单日期</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单量</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品销售额</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品销售成本</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品销售毛利</td>';
		$strTable .= '</tr>';

		$orderDB   = new IModel('order');
		$orderList = $orderDB->query(" `completion_time` between '{$startCondition}' and '{$endCondition}' "," DATE_FORMAT(`completion_time`,'%Y-%m-%d') as ctime,id ","id asc");
		if($orderList)
		{
			//组合订单ID
			$ids = array();
			foreach($orderList as $key => $val)
			{
				if(!isset($ids[$val['ctime']]))
				{
					$ids[$val['ctime']] = array();
				}
				$ids[$val['ctime']][] = $val['id'];
			}

			//获取订单数据
			$db        = new IQuery('order_goods as og');
			$db->join  = "left join goods as go on go.id = og.goods_id left join products as p on p.id = og.product_id ";
			$db->fields= "og.*,go.cost_price as go_cost,p.cost_price as p_cost";
			$db->order = "og.order_id asc";
			foreach($ids as $ctime => $idArray)
			{
				$db->where = "og.order_id in (".join(',',$idArray).") and og.is_send = 1";
				$orderList = $db->find();

				$result[$ctime] = array("orderNum" => count($idArray),"goods_sum" => 0,"goods_cost" => 0,"goods_diff" => 0);
				foreach($orderList as $key => $val)
				{
					$result[$ctime]['goods_sum']  += $val['real_price'] * $val['goods_nums'];
					$cost = $val['p_cost'] ? $val['p_cost'] : $val['go_cost'];
					$result[$ctime]['goods_cost'] += $cost * $val['goods_nums'];
				}
				$result[$ctime]['goods_diff'] += $result[$ctime]['goods_sum'] - $result[$ctime]['goods_cost'];
			}
		}

		foreach($result as $ctime => $val)
		{
			$strTable .= '<tr>';
			$strTable .= '<td style="text-align:center;font-size:12px;">'.$ctime.' </td>';
			$strTable .= '<td style="text-align:center;font-size:12px;">'.$val['orderNum'].' </td>';
			$strTable .= '<td style="text-align:center;font-size:12px;">'.$val['goods_sum'].' </td>';
			$strTable .= '<td style="text-align:center;font-size:12px;">'.$val['goods_cost'].' </td>';
			$strTable .= '<td style="text-align:center;font-size:12px;">'.$val['goods_diff'].' </td>';
			$strTable .= '</tr>';
		}
		$strTable .='</table>';

		$reportObj = new report();
		$reportObj->setFileName('amount');
		$reportObj->toDownload($strTable);
		exit();
	}

	//[特价商品]添加,修改[单页]
	function sale_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$promotionObj = new IModel('promotion');
			$where = 'id = '.$id.' and award_type = 7';
			$this->promotionRow = $promotionObj->getObj($where);
			if(!$this->promotionRow)
			{
				IError::show("信息不存在");
			}
		}
		$this->redirect('sale_edit');
	}

	//[特价商品]添加,修改[动作]
	function sale_edit_act()
	{
		$id           = IFilter::act(IReq::get('id'),'int');
		$award_value  = IFilter::act(IReq::get('award_value'),'int');
		$type         = IFilter::act(IReq::get('type'));
		$is_close     = IFilter::act(IReq::get('is_close','post'));
		$intro        = array();//商品ID => 促销金额(或者折扣率)

		$proObj = new IModel('promotion');
		if($id)
		{
			//获取旧数据和原始价格
			$proRow = $proObj->getObj("id = ".$id);
			if(!$proRow)
			{
				IError::show('特价活动不存在');
			}

			if($proRow['is_close'] == 0)
			{
				$tempUpdate = JSON::decode($proRow['intro']);
				if($tempUpdate)
				{
					foreach($tempUpdate as $gid => $g_discount)
					{
						goods_class::goodsDiscount($gid,$g_discount,"constant","add");
					}
				}
			}
		}

		switch($type)
		{
			case 2:
			{
				$category = IFilter::act(IReq::get('category'),'int');
				if(!$category)
				{
					IError::show(403,'商品分类信息没有设置');
				}
				$condition = join(",",$category);
				$goodsData = Api::run("getCategoryExtendList",array("#categroy_id#",$condition),500);
				foreach($goodsData as $key => $val)
				{
					$intro[$val['id']] = $val['sell_price'] - $val['sell_price']*$award_value/100;
				}
			}
			break;

			case 3:
			{
				$gid = IFilter::act(IReq::get('goods_id'),'int');
				if(!$gid)
				{
					IError::show(403,'商品信息没有设置');
				}
				$condition   = join(",",$gid);
				$goodsDB     = new IModel('goods');
				$goodsData   = $goodsDB->query('id in ('.$condition.')');
				$goods_price = IFilter::act(IReq::get('goods_price'),'float');

				foreach($goodsData as $key => $val)
				{
					if(isset( $goods_price[$val['id']] ))
					{
						$intro[$val['id']] = $val['sell_price'] - $goods_price[$val['id']];
					}
				}
			}
			break;

			case 4:
			{
				$condition = IFilter::act(IReq::get('brand_id'),'int');
				if(!$condition)
				{
					IError::show(403,'品牌信息没有设置');
				}
				$goodsDB   = new IModel('goods');
				$goodsData = $goodsDB->query("brand_id = ".$condition,"*","sort asc",500);
				foreach($goodsData as $key => $val)
				{
					$intro[$val['id']] = $val['sell_price'] - $val['sell_price']*$award_value/100;
				}
			}
			break;
		}

		if(!$intro)
		{
			IError::show(403,'商品信息不存在，请确定你选择的条件有商品');
		}

		//去掉重复促销的商品
		$proData = $proObj->query("award_type = 7 and id != ".$id);
		foreach($proData as $key => $val)
		{
			$temp  = JSON::decode($val['intro']);
			$intro = array_diff_key($intro,$temp);
		}

		if(!$intro)
		{
			IError::show(403,'商品不能重复设置特价');
		}

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'condition'  => $condition,
			'award_value'=> $award_value,
			'is_close'   => $is_close,
			'start_time' => ITime::getDateTime(),
			'intro'      => JSON::encode($intro),
			'type'       => $type,
			'award_type' => 7,
			'sort'       => IFilter::act(IReq::get('sort'),'int'),
		);

		$proObj->setData($dataArray);
		if($id)
		{
			$where = 'id = '.$id;
			$proObj->update($where);
		}
		else
		{
			$proObj->add();
		}

		//开启
		if($is_close == 0)
		{
			$tempUpdate = $intro;
			if($tempUpdate)
			{
				foreach($tempUpdate as $gid => $g_discount)
				{
					goods_class::goodsDiscount($gid,$g_discount,"constant","reduce");
				}
			}
		}
		$this->redirect('sale_list');
	}

	//[特价商品]删除
	function sale_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$proObj = new IModel('promotion');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = ' id = '.$id;
			}
			$where .= ' and award_type = 7 ';

			//恢复特价商品价格
			$proList = $proObj->query($where);
			foreach($proList as $key => $val)
			{
				if($val['is_close'] == 0)
				{
					$tempUpdate = JSON::decode($val['intro']);
					if($tempUpdate)
					{
						foreach($tempUpdate as $gid => $g_discount)
						{
							goods_class::goodsDiscount($gid,$g_discount,"constant","add");
						}
					}
				}
			}
			$proObj->del($where);
			$this->redirect('sale_list');
		}
		else
		{
			$this->redirect('sale_list',false);
			Util::showMessage('请选择要删除的id值');
		}
	}
}