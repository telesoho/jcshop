<?php
/**
 * @brief 订单模块
 * @class Order
 * @note  后台
 */
require_once __DIR__ . '/../plugins/vendor/autoload.php';
use  Endroid\QrCode\QrCode;
class Order extends IController implements adminAuthorization
{
	public $checkRight  = 'all';
	public $layout='admin';
	function init()
	{

	}
	/**
	 * @brief查看订单
	 */
	public function order_show()
	{
		//获得post传来的值
		$order_id = IFilter::act(IReq::get('id'),'int');
		$data = array();
		if($order_id)
		{
			$order_show = new Order_Class();
			$data = $order_show->getOrderShow($order_id);
			if($data)
			{
		 		//获取地区
		 		$data['area_addr'] = join('&nbsp;',area::name($data['province'],$data['city'],$data['area']));

			 	$this->setRenderData($data);
				$this->redirect('order_show',false);
			}
		}
		if(!$data)
		{
			$this->redirect('order_list');
		}
	}
	/**
	 * @brief查看收款单
	 */
	public function collection_show()
	{
		//获得post传来的收款单id值
		$collection_id = IFilter::act(IReq::get('id'),'int');
		$data = array();
		if($collection_id)
		{
			$tb_collection = new IQuery('collection_doc as c ');
			$tb_collection->join=' left join order as o on c.order_id=o.id left join payment as p on c.payment_id = p.id left join user as u on u.id = c.user_id';
			$tb_collection->fields = 'o.order_no,p.name as pname,o.create_time,p.type,u.username,c.amount,o.pay_time,c.admin_id,c.note';
			$tb_collection->where = 'c.id='.$collection_id;
			$collection_info = $tb_collection->find();
			if($collection_info)
			{
				$data = $collection_info[0];

				$this->setRenderData($data);
				$this->redirect('collection_show',false);
			}
		}
		if(count($data)==0)
		{
			$this->redirect('order_collection_list');
		}
	}
	/**
	 * @brief查看退款单
	 */
	public function refundment_show()
	{
	 	//获得post传来的退款单id值
	 	$refundment_id = IFilter::act(IReq::get('id'),'int');
	 	$data = array();
	 	if($refundment_id)
	 	{
	 		$tb_refundment = new IQuery('refundment_doc as c');
	 		$tb_refundment->join=' left join order as o on c.order_id=o.id left join user as u on u.id = c.user_id';
	 		$tb_refundment->fields = 'o.order_no,o.create_time,u.username,c.*';
	 		$tb_refundment->where = 'c.id='.$refundment_id;
	 		$refundment_info = $tb_refundment->find();
	 		if($refundment_info)
	 		{
	 			$data = current($refundment_info);
	 			$this->setRenderData($data);
	 			$this->redirect('refundment_show',false);
	 		}
	 	}

	 	if(!$data)
		{
			$this->redirect('order_refundment_list');
		}
	}
	/**
	 * @brief查看申请退款单
	 */
	public function refundment_doc_show()
	{
	 	//获得post传来的申请退款单id值
	 	$refundment_id = IFilter::act(IReq::get('id'),'int');
	 	if($refundment_id)
	 	{
	 		$refundsDB = new IModel('refundment_doc');
	 		$data = $refundsDB->getObj('id = '.$refundment_id);
	 		if($data)
	 		{
	 			$this->setRenderData($data);
	 			$this->redirect('refundment_doc_show',false);
	 			return;
	 		}
	 	}

	 	$this->redirect('refundment_list');
	}
	//删除申请退款单
	public function refundment_doc_del()
	{
		//获得post传来的申请退款单id值
		$refundment_id = IFilter::act(IReq::get('id'),'int');
		if(is_array($refundment_id))
		{
			$refundment_id = implode(",",$refundment_id);
		}
		if($refundment_id)
		{
			$tb_refundment_doc = new IModel('refundment_doc');
			$tb_refundment_doc->setData(array('if_del' => 1));
			$tb_refundment_doc->update("id IN ($refundment_id)");
		}

		$logObj = new log('db');
		$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"退款单移除到回收站",'移除的ID：'.$refundment_id));

		$this->redirect('refundment_list');
	}

	/**
	 * @brief更新申请退款单
	 */
	public function refundment_doc_show_save()
	{
		//获得post传来的退款单id值
		$refundment_id = IFilter::act(IReq::get('id'),'int');
		$pay_status = IFilter::act(IReq::get('pay_status'),'int');
		$dispose_idea = IFilter::act(IReq::get('dispose_idea'),'text');

		//获得refundment_doc对象
		$tb_refundment_doc = new IModel('refundment_doc');
		$tb_refundment_doc->setData(array(
			'pay_status'   => $pay_status,
			'dispose_idea' => $dispose_idea,
			'dispose_time' => ITime::getDateTime(),
			'admin_id'     => $this->admin['admin_id'],
		));

		if($refundment_id)
		{
			$tb_refundment_doc->update('id='.$refundment_id);

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"修改了退款单",'修改的ID：'.$refundment_id));
		}
		$this->redirect('refundment_list');
	}
	/**
	 * @brief查看发货单
	 */
	public function delivery_show()
	{
	 	//获得post传来的发货单id值
	 	$delivery_id = IFilter::act(IReq::get('id'),'int');
	 	$data = array();
	 	if($delivery_id)
	 	{
	 		$tb_delivery = new IQuery('delivery_doc as c ');
	 		$tb_delivery->join=' left join order as o on c.order_id=o.id left join delivery as p on c.delivery_type = p.id left join user as u on u.id = c.user_id';
	 		$tb_delivery->fields = 'c.id as id,o.order_no,c.order_id,p.name as pname,o.create_time,u.username,c.name,c.province,c.city,c.area,c.address,c.mobile,c.telphone,c.postcode,c.freight,c.delivery_code,c.time,c.note ';
	 		$tb_delivery->where = 'c.id='.$delivery_id;
	 		$delivery_info = $tb_delivery->find();
	 		if($delivery_info)
	 		{
	 			$data = current($delivery_info);
	 			$data['country'] = join("-",area::name($data['province'],$data['city'],$data['area']));

	 			$this->setRenderData($data);
	 			$this->redirect('delivery_show',false);
	 		}
	 	}

	 	if(!$data)
		{
			$this->redirect('order_delivery_list');
		}
	}
	/**
	 * @brief 支付订单页面collection_doc
	 */
	public function order_collection()
	{
	 	//去掉左侧菜单和上部导航
	 	$this->layout='';
	 	$order_id = IFilter::act(IReq::get('id'),'int');
	 	$data = array();
	 	if($order_id)
	 	{
	 		$order_show = new Order_Class();
	 		$data = $order_show->getOrderShow($order_id);
	 	}
	 	$this->setRenderData($data);
	 	$this->redirect('order_collection');
	}
	/**
	 * @brief 保存支付订单页面collection_doc
	 */
	public function order_collection_doc()
	{
	 	//获得订单号
	 	$order_no = IFilter::act(IReq::get('order_no'));
	 	$note     = IFilter::act(IReq::get('note'));

	 	if(Order_Class::updateOrderStatus($order_no,$this->admin['admin_id'],$note))
	 	{
		 	//生成订单日志
	    	$tb_order_log = new IModel('order_log');
	    	$tb_order_log->setData(array(
	    		'order_id' =>IFilter::act(IReq::get('id'),'int'),
	    		'user' =>$this->admin['admin_name'],
	    		'action' =>'付款',
	    		'result' =>'成功',
	    		'note' =>'订单【'.$order_no.'】付款'.IFilter::act(IReq::get('amount'),'float').'元',
	    		'addtime' => ITime::getDateTime(),
	    	));
	    	$tb_order_log->add();

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"订单更新为已付款","订单号：".$order_no.'，已经确定付款'));
	 		echo '<script type="text/javascript">parent.actionCallback();</script>';
	 	}
	 	else
	 	{
	 		echo '<script type="text/javascript">parent.actionFailCallback();</script>';
	 	}
	}
	/**
	 * @brief 退款单页面
	 */
	public function order_refundment()
	{
		//去掉左侧菜单和上部导航
		$this->layout='';
		$orderId   = IFilter::act(IReq::get('id'),'int');
		$refundsId = IFilter::act(IReq::get('refunds_id'),'int');

		if($orderId)
		{
			$orderDB = new Order_Class();
			$data    = $orderDB->getOrderShow($orderId);

			//已经存退款申请
			if($refundsId)
			{
				$refundsDB  = new IModel('refundment_doc');
				$refundsRow = $refundsDB->getObj('id = '.$refundsId);
				$data['refunds'] = $refundsRow;
			}
			$this->setRenderData($data);
			$this->data = $data;
			$this->redirect('order_refundment');
			return;
		}
		die('订单数据不存在');
	}
	/**
	 * @brief 保存退款单页面
	 */
	public function order_refundment_doc()
	{
		$refunds_id = IFilter::act(IReq::get('refunds_id'),'int');
		$amount   = IFilter::act(IReq::get('amount'),'float');
		$order_id = IFilter::act(IReq::get('id'),'int');
		$order_no = IFilter::act(IReq::get('order_no'));
		$user_id  = IFilter::act(IReq::get('user_id'),'int');
		$order_goods_id = IFilter::act(IReq::get('order_goods_id'),'int'); //要退款的商品,如果是用户已经提交的退款申请此数据为NULL,需要获取出来
		$way = IFilter::act(IReq::get('way'));

		//访客订单不能退款到余额中
		if(!$user_id && $way == "balance")
		{
			die('<script text="text/javascript">parent.actionCallback("游客无法退款");</script>');
		}

		//1,退款单存在更新退款价格
		$tb_refundment_doc = new IModel('refundment_doc');
		if($refunds_id)
		{
			$updateData = array('amount' => $amount);
			$tb_refundment_doc->setData($updateData);
			$tb_refundment_doc->update("id = ".$refunds_id);
		}
		//2,无退款申请单，必须生成退款单
		else
		{
			if(!$order_goods_id)
			{
				die('<script text="text/javascript">parent.actionCallback("请选择要退款的商品");</script>');
			}

			$orderDB = new IModel('order');
			$orderRow= $orderDB->getObj("id = ".$order_id);

			//插入refundment_doc表
			$updateData = array(
				'amount'        => $amount,
				'order_no'      => $order_no,
				'order_id'      => $order_id,
				'admin_id'      => $this->admin['admin_id'],
				'pay_status'    => 2,
				'dispose_time'  => ITime::getDateTime(),
				'dispose_idea'  => '退款成功',
				'user_id'       => $user_id,
				'time'          => ITime::getDateTime(),
				'seller_id'     => $orderRow['seller_id'],
				'order_goods_id'=> join(",",$order_goods_id),
			);
			$tb_refundment_doc->setData($updateData);
			$refunds_id = $tb_refundment_doc->add();
		}

		$result = Order_Class::refund($refunds_id,$this->admin['admin_id'],'admin',$way);
		if(is_string($result))
		{
			$tb_refundment_doc->rollback();
			die('<script text="text/javascript">parent.actionCallback("'.$result.'");</script>');
		}
		else
		{
			//记录操作日志
			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"订单更新为退款",'订单号：'.$order_no));
			die('<script text="text/javascript">parent.actionCallback();</script>');
		}
	}
	/**
	 * @brief 保存订单备注
	 */
	public function order_note()
	{
	 	//获得post数据
	 	$order_id = IFilter::act(IReq::get('order_id'),'int');
	 	$note = IFilter::act(IReq::get('note'),'text');

	 	//获得order的表对象
	 	$tb_order =  new IModel('order');
	 	$tb_order->setData(array(
	 		'note'=>$note
	 	));
	 	$tb_order->update('id='.$order_id);
	 	IReq::set('id',$order_id);
	 	$this->order_show();
	}
	/**
	 * @brief 保存顾客留言
	 */
	public function order_message()
	{
		//获得post数据
		$order_id = IFilter::act(IReq::get('order_id'),'int');
		$user_id = IFilter::act(IReq::get('user_id'),'int');
		$title = IFilter::act(IReq::get('title'));
		$content = IFilter::act(IReq::get('content'),'text');

		//获得message的表对象
		$tb_message =  new IModel('message');
		$tb_message->setData(array(
			'title'=>$title,
			'content' =>$content,
			'time'=> ITime::getDateTime(),
		));
		$message_id = $tb_message->add();
		//获的mess类
		$message = new Mess($user_id);
		$message->writeMessage($message_id);
		IReq::set('id',$order_id);
		$this->order_show();
	}
	/**
	 * @brief 完成或作废订单页面
	 **/
	public function order_complete()
	{
		//去掉左侧菜单和上部导航
		$this->layout='';
		$order_id = IFilter::act(IReq::get('id'),'int');
		$type     = IFilter::act(IReq::get('type'),'int');
		$order_no = IFilter::act(IReq::get('order_no'));

		//oerder表的对象
		$tb_order = new IModel('order');
		$tb_order->setData(array(
			'status'          => $type,
			'completion_time' => ITime::getDateTime(),
		));
		$tb_order->update('id='.$order_id);

		//生成订单日志
		$tb_order_log = new IModel('order_log');
		$action = '作废';
		$note   = '订单【'.$order_no.'】作废成功';

		if($type=='5')
		{
			$action = '完成';
			$note   = '订单【'.$order_no.'】完成成功';

			//完成订单并且进行支付
			Order_Class::updateOrderStatus($order_no);

			//增加用户评论商品机会
			Order_Class::addGoodsCommentChange($order_id);

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"订单更新为完成",'订单号：'.$order_no));
		}
		else
		{
			Order_class::resetOrderProp($order_id);

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"订单更新为作废",'订单号：'.$order_no));
		}

		$tb_order_log->setData(array(
			'order_id' => $order_id,
			'user'     => $this->admin['admin_name'],
			'action'   => $action,
			'result'   => '成功',
			'note'     => $note,
			'addtime'  => ITime::getDateTime(),
		));
		$tb_order_log->add();
		die('success');
	}
	/**
	 * @brief 发货订单页面
	 */
	public function order_deliver()
	{
		//去掉左侧菜单和上部导航
		$this->layout='';
		$order_id = IFilter::act(IReq::get('id'),'int');
		$data = array();
		if($order_id)
		{
			$order_show = new Order_Class();
			$data = $order_show->getOrderShow($order_id);
		}
		$this->setRenderData($data);
		$this->redirect('order_deliver');
	}
	/**
	 * @brief 发货操作
	 */
	public function order_delivery_doc()
	{
	 	//获得post变量参数
	 	$order_id = IFilter::act(IReq::get('id'),'int');

	 	//发送的商品关联
	 	$sendgoods = IFilter::act(IReq::get('sendgoods'));

	 	if(!$sendgoods)
	 	{
	 		die('<script type="text/javascript">parent.actionCallback("请选择要发货的商品");</script>');
	 	}

	 	$result = Order_Class::sendDeliveryGoods($order_id,$sendgoods,$this->admin['admin_id']);

		if($result === true)
		{
			die('<script type="text/javascript">parent.actionCallback();</script>');
		}
		die('<script type="text/javascript">parent.actionCallback("'.$result.'");</script>');
	}
	/**
	 * @brief 保存修改订单
	 */
    public function order_update()
    {
    	//获取必要数据
    	$order_id = IFilter::act(IReq::get('id'),'int');

    	//生成order数据
    	$dataArray                  = array();
    	$dataArray['invoice_title'] = IFilter::act(IReq::get('invoice_title'));
    	$dataArray['invoice']       = IFilter::act(IReq::get('invoice'),'int');
    	$dataArray['pay_type']      = IFilter::act(IReq::get('pay_type'),'int');
    	$dataArray['accept_name']   = IFilter::act(IReq::get('accept_name'));
    	$dataArray['postcode']      = IFilter::act(IReq::get('postcode'));
    	$dataArray['telphone']      = IFilter::act(IReq::get('telphone'));
    	$dataArray['province']      = IFilter::act(IReq::get('province'),'int');
    	$dataArray['city']          = IFilter::act(IReq::get('city'),'int');
    	$dataArray['area']          = IFilter::act(IReq::get('area'),'int');
    	$dataArray['address']       = IFilter::act(IReq::get('address'));
    	$dataArray['mobile']        = IFilter::act(IReq::get('mobile'));
    	$dataArray['discount']      = $order_id ? IFilter::act(IReq::get('discount'),'float') : 0;
    	$dataArray['postscript']    = IFilter::act(IReq::get('postscript'));
    	$dataArray['distribution']  = IFilter::act(IReq::get('distribution'),'int');
    	$dataArray['accept_time']   = IFilter::act(IReq::get('accept_time'));
    	$dataArray['takeself']      = IFilter::act(IReq::get('takeself'));
    	$dataArray['real_freight']  = IFilter::act(IReq::get('real_freight'));
    	$dataArray['note']          = IFilter::act(IReq::get('note'));

		//设置订单持有者
		$username = IFilter::act(IReq::get('username'));
		$userDB   = new IModel('user');
		$userRow  = $userDB->getObj('username = "'.$username.'"');
		$dataArray['user_id'] = isset($userRow['id']) ? $userRow['id'] : 0;

		//拼接要购买的商品或货品数据,组装成固有的数据结构便于计算价格
		$goodsId   = IFilter::act(IReq::get('goods_id'));
		$productId = IFilter::act(IReq::get('product_id'));
		$num       = IFilter::act(IReq::get('goods_nums'));

		$goodsArray  = array();
		$productArray= array();
		if(!$goodsId)
		{
			IError::show("商品信息不存在");
			exit;
		}
    	foreach($goodsId as $key => $goods_id)
    	{
    		$pid = $productId[$key];
    		$nVal= $num[$key];

    		if($pid > 0)
    		{
    			$productArray[$pid] = $nVal;
    		}
    		else
    		{
    			$goodsArray[$goods_id] = $nVal;
    		}
    	}

		//开始算账
		$countSumObj  = new CountSum($dataArray['user_id']);
		$cartObj      = new Cart();
		$goodsResult  = $countSumObj->goodsCount($cartObj->cartFormat(array("goods" => $goodsArray,"product" => $productArray)));
		$orderData   = $countSumObj->countOrderFee($goodsResult,$dataArray['province'],$dataArray['distribution'],$dataArray['pay_type'],$dataArray['invoice'],$dataArray['discount']);
		if(is_string($orderData))
		{
			IError::show(403,$orderData);
			exit;
		}

		//根据商品所属商家不同批量生成订单
		foreach($orderData as $seller_id => $goodsResult)
		{
			//运费自定义
			if(is_numeric($dataArray['real_freight']) && $goodsResult['deliveryPrice'] != $dataArray['real_freight'])
			{
				$goodsResult['orderAmountPrice'] += $dataArray['real_freight'] - $goodsResult['deliveryPrice'];
				$goodsResult['deliveryPrice']     = $dataArray['real_freight'];
			}
			$dataArray['payable_freight']= $goodsResult['deliveryOrigPrice'];
			$dataArray['payable_amount'] = $goodsResult['sum'];
			$dataArray['real_amount']    = $goodsResult['final_sum'];
			$dataArray['real_freight']   = $goodsResult['deliveryPrice'];
			$dataArray['insured']        = $goodsResult['insuredPrice'];
			$dataArray['pay_fee']        = $goodsResult['paymentPrice'];
			$dataArray['taxes']          = $goodsResult['taxPrice'];
			$dataArray['promotions']     = $goodsResult['proReduce'] + $goodsResult['reduce'];
			$dataArray['order_amount']   = $goodsResult['orderAmountPrice'];
			$dataArray['exp']            = $goodsResult['exp'];
			$dataArray['point']          = $goodsResult['point'];

			//商家ID
			$dataArray['seller_id'] = $seller_id;

	    	//生成订单
	    	$orderDB = new IModel('order');

	    	//修改操作
	    	if($order_id)
	    	{
	    		//获取订单信息
	    		$orderRow = $orderDB->getObj('id = '.$order_id);

	    		//修改订单不能加入其他商家产品
	    		if(count($orderData) != 1 || $orderRow['seller_id'] != $seller_id)
	    		{
					IError::show(403,"此订单中不能混入其他商家的商品");
					exit;
	    		}

	    		$orderDB->setData($dataArray);
	    		$orderDB->update('id = '.$order_id);

				//记录日志信息
				$logObj = new log('db');
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"修改了订单信息",'订单号：'.$orderRow['order_no']));
	    	}
	    	//添加操作
	    	else
	    	{
	    		$dataArray['create_time'] = ITime::getDateTime();
	    		$dataArray['order_no']    = Order_Class::createOrderNum();

	    		$orderDB->setData($dataArray);
	    		$order_id = $orderDB->add();

				//记录日志信息
				$logObj = new log('db');
				$logObj->write('operation',array("管理员:".$this->admin['admin_name'],"添加了订单信息",'订单号：'.$dataArray['order_no']));
	    	}

	    	//同步order_goods表
	    	$orderInstance = new Order_Class();
	    	$orderInstance->insertOrderGoods($order_id,$goodsResult['goodsResult']);
		}

    	$this->redirect('order_list');
    }
	/**
	 * @brief 修改订单
	 */
	public function order_edit()
    {
    	$data = array();

    	//获得order_id的值
		$order_id = IFilter::act(IReq::get('id'),'int');
		if($order_id)
		{
			$orderDB = new IModel('order');
			$data    = $orderDB->getObj('id = '.$order_id);
			if(Order_class::getOrderStatus($data) >= 3)
			{
				IError::show(403,"当前订单状态不允许修改");
			}

			$this->orderRow = $data;

			//存在自提点
			if($data['takeself'])
			{
				$takeselfObj = new IModel('takeself');
				$takeselfRow = $takeselfObj->getObj('id = '.$data['takeself']);
				$dataArea    = area::name($takeselfRow['province'],$takeselfRow['city'],$takeselfRow['area']);
				$takeselfRow['province_str'] = $dataArea[$takeselfRow['province']];
				$takeselfRow['city_str']     = $dataArea[$takeselfRow['city']];
				$takeselfRow['area_str']     = $dataArea[$takeselfRow['area']];
				$this->takeself = $takeselfRow;
			}

			//获取订单中的商品信息
			$orderGoodsDB         = new IQuery('order_goods as og');
			$orderGoodsDB->join   = "left join goods as go on og.goods_id = go.id left join products as p on p.id = og.product_id";
			$orderGoodsDB->fields = "go.id,go.name,p.spec_array,p.id as product_id,og.real_price,og.goods_nums,go.goods_no,p.products_no";
			$orderGoodsDB->where  = "og.order_id = ".$order_id;
			$this->orderGoods     = $orderGoodsDB->find();

			//获取用户名
			if($data['user_id'])
			{
				$userDB  = new IModel('user');
				$userRow = $userDB->getObj("id = ".$data['user_id']);
				$this->username = isset($userRow['username']) ? $userRow['username'] : '';
			}
		}
		$this->redirect('order_edit');
    }
    /**
     * @brief 订单列表
     */
    public function order_list()
    {
		//搜索条件
		$search = IReq::get('search');
		$page   = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		
		//条件筛选处理
		list($join,$where) = order_class::getSearchCondition($search);

		//拼接sql
		$orderHandle = new IQuery('order as o');
		$orderHandle->order  = "o.id desc";
		$orderHandle->fields = "o.*,d.name as distribute_name,p.name as payment_name";
		$orderHandle->page   = $page;
		$orderHandle->where  = $where;
		$orderHandle->join   = $join;

		$this->search      = $search;
		$this->orderHandle = $orderHandle;

		$this->redirect("order_list");
    }
    /**
     * @brief 订单删除功能_删除到回收站
     */
    public function order_del()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');

    	//生成order对象
    	$tb_order = new IModel('order');
    	$tb_order->setData(array('if_del'=>1));
    	if($id)
		{
			$id = $tb_order->update(Util::joinStr($id));

			//获取订单编号
			$orderRs   = $tb_order->query('id in ('.$id.')','order_no');
			$orderData = array();
			foreach($orderRs as $val)
			{
				$orderData[] = $val['order_no'];
			}

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"订单移除到回收站内",'订单号：'.join(',',$orderData)));
		}
		$this->redirect('order_list');
    }
	/**
     * @brief 收款单删除功能_删除到回收站
     */
    public function collection_del()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('collection_doc');
    	$tb_order->setData(array('if_del'=>1));
    	if($id)
		{
			$tb_order->update(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"收款单移除到回收站内",'收款单ID：'.join(',',$id)));

			$this->redirect('order_collection_list');
		}
		else
		{
			$this->redirect('order_collection_list',false);
			Util::showMessage('请选择要删除的数据');
		}
    }
	/**
     * @brief 收款单删除功能_删除回收站中的数据，彻底删除
     */
    public function collection_recycle_del()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('collection_doc');
    	if($id)
		{
			$tb_order->del(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"删除回收站内的收款单",'收款单ID：'.join(',',$id)));

			$this->redirect('collection_recycle_list');
		}
		else
		{
			$this->redirect('collection_recycle_list',false);
			Util::showMessage('请选择要删除的数据');
		}
    }
	/**
	 * @brief 还原还款单列表
	 */
    public function collection_recycle_restore()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('collection_doc');
    	$tb_order->setData(array('if_del'=>0));
    	if($id)
		{
			$tb_order->update(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"恢复了回收站内的收款单",'收款单ID：'.join(',',$id)));

			$this->redirect('collection_recycle_list');
		}
		else
		{
			$this->redirect('collection_recycle_list',false);
			Util::showMessage('请选择要还原的数据');
		}
    }
	/**
	 * @brief 退款单删除功能_删除到回收站
	 */
    public function refundment_del()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('refundment_doc');
    	$tb_order->setData(array('if_del'=>1));
    	if(!empty($id))
		{
			$tb_order->update(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"退款单移除到回收站内",'退款单ID：'.join(',',$id)));

			$this->redirect('order_refundment_list');
		}
		else
		{
			$this->redirect('order_refundment_list',false);
			Util::showMessage('请选择要删除的数据');
		}
    }
	/**
	 * @brief 退款单删除功能_删除回收站中的数据，彻底删除
	 */
    public function refundment_recycle_del()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('refundment_doc');
    	if(!empty($id))
		{
			$tb_order->del(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"删除了回收站内的退款单",'退款单ID：'.join(',',$id)));

			$this->redirect('refundment_recycle_list');
		}
		else
		{
			$this->redirect('refundment_recycle_list',false);
			Util::showMessage('请选择要删除的数据');
		}
    }
	/**
	 * @brief 还原还款单列表
	 */
    public function refundment_recycle_restore()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('refundment_doc');
    	$tb_order->setData(array('if_del'=>0));
    	if(!empty($id))
		{
			$tb_order->update(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"还原了回收站内的还款单",'还款单ID：'.join(',',$id)));

			$this->redirect('refundment_recycle_list');
		}
		else
		{
			$this->redirect('refundment_recycle_list',false);
			Util::showMessage('请选择要还原的数据');
		}
    }
    /**
     * @brief 发货单删除功能_删除到回收站
     */
    public function delivery_del()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('delivery_doc');
    	$tb_order->setData(array('if_del'=>1));
    	if(!empty($id))
		{
			$tb_order->update(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"发货单移除到回收站内",'发货单ID：'.join(',',$id)));

			$this->redirect('order_delivery_list');
		}
		else
		{
			$this->redirect('order_delivery_list',false);
			Util::showMessage('请选择要删除的数据');
		}
    }
	/**
     * @brief 发货单删除功能_删除回收站中的数据，彻底删除
     */
    public function delivery_recycle_del()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('delivery_doc');
    	if(!empty($id))
		{
			$tb_order->del(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"删除了回收站中的发货单",'发货单ID：'.join(',',$id)));

			$this->redirect('delivery_recycle_list');
		}
		else
		{
			$this->redirect('delivery_recycle_list',false);
			Util::showMessage('请选择要删除的数据');
		}
    }
	/**
	 * @brief 还原发货单列表
	 */
    public function delivery_recycle_restore()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('delivery_doc');
    	$tb_order->setData(array('if_del'=>0));
    	if(!empty($id))
		{
			$tb_order->update(Util::joinStr($id));

			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"还原了回收站中的发货单",'发货单ID：'.join(',',$id)));

			$this->redirect('delivery_recycle_list');
		}
		else
		{
			$this->redirect('delivery_recycle_list',false);
			Util::showMessage('请选择要还原的数据');
		}
    }
    /**
     * @brief 订单删除功能_删除回收站中的数据，彻底删除
     */
    public function order_recycle_del()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');

    	//生成order对象
    	$tb_order = new IModel('order');

    	if($id)
		{
			$id = is_array($id) ? join(',',$id) : $id;

			Order_class::resetOrderProp($id);

			//删除订单
			$tb_order->del('id in ('.$id.')');

			//记录日志
			$logObj = new log('db');
			$logObj->write('operation',array("管理员:".ISafe::get('admin_name'),"删除回收站中退货单",'退货单ID：'.$id));

			$this->redirect('order_recycle_list');
		}
		else
		{
			$this->redirect('order_recycle_list',false);
			Util::showMessage('请选择要删除的数据');
		}
    }
    /**
	 * @brief 还原订单列表
	 */
    public function order_recycle_restore()
    {
    	//post数据
    	$id = IFilter::act(IReq::get('id'),'int');
    	//生成order对象
    	$tb_order = new IModel('order');
    	$tb_order->setData(array('if_del'=>0));
    	if(!empty($id))
		{
			$tb_order->update(Util::joinStr($id));
			$this->redirect('order_recycle_list');
		}
		else
		{
			$this->redirect('order_recycle_list',false);
			Util::showMessage('请选择要还原的数据');
		}
    }
	/**
	 * @brief 订单打印模板修改
	 */
    public function print_template()
    {
		//获取根目录路径
		$path = $this->getViewPath().$this->getId();

    	//获取 购物清单模板
		$ifile_shop = new IFile($path.'/shop_template.html');
		$arr['ifile_shop']=$ifile_shop->read();
		//获取 配货单模板
		$ifile_pick = new IFile($path."/pick_template.html");
		$arr['ifile_pick']=$ifile_pick->read();

		$this->setRenderData($arr);
		$this->redirect('print_template');
    }
	/**
	 * @brief 订单打印模板修改保存
	 */
    public function print_template_update()
    {
		// 获取POST数据
    	$con_shop = IReq::get("con_shop");
		$con_pick = IReq::get("con_pick");

    	//获取根目录路径
		$path = $this->getViewPath().$this->getId();
    	//保存 购物清单模板
		$ifile_shop = new IFile($path.'/shop_template.html','w');
		if(!($ifile_shop->write($con_shop)))
		{
			$this->redirect('print_template',false);
			Util::showMessage('保存购物清单模板失败！');
		}
		//保存 配货单模板
		$ifile_pick = new IFile($path."/pick_template.html",'w');
		if(!($ifile_pick->write($con_pick)))
		{
			$this->redirect('print_template',false);
			Util::showMessage('保存配货单模板失败！');
		}
		//保存 合并单模板
    	$ifile_merge = new IFile($path."/merge_template.html",'w');
		if(!($ifile_merge->write($con_shop.$con_pick)))
		{
			$this->redirect('print_template',false);
			Util::showMessage('购物清单和配货单模板合并失败！');
		}

		$this->setRenderData(array('where'=>''));
		$this->redirect('order_list');
	}

	//购物单
	public function shop_template()
	{
		$this->layout='print';
		$order_id = IFilter::act( IReq::get('id'),'int' );
		$seller_id= IFilter::act( IReq::get('seller_id'),'int' );

		$tb_order = new IModel('order');
		$where    = $seller_id ? 'id='.$order_id.' and seller_id = '.$seller_id : 'id='.$order_id;
		$data     = $tb_order->getObj($where);
		if(!$data)
		{
			IError::show(403,"您没有权限查阅该订单");
		}

		if($data['seller_id'])
		{
			$sellerObj   = new IModel('seller');
			$config_info = $sellerObj->getObj('id = '.$data['seller_id']);

	     	$data['set']['name']   = isset($config_info['true_name'])? $config_info['true_name'] : '';
	     	$data['set']['phone']  = isset($config_info['phone'])    ? $config_info['phone']     : '';
	     	$data['set']['email']  = isset($config_info['email'])    ? $config_info['email']     : '';
	     	$data['set']['url']    = isset($config_info['home_url']) ? $config_info['home_url']  : '';
		}
		else
		{
			$config = new Config("site_config");
			$config_info = $config->getInfo();

	     	$data['set']['name']   = isset($config_info['name'])  ? $config_info['name']  : '';
	     	$data['set']['phone']  = isset($config_info['phone']) ? $config_info['phone'] : '';
	     	$data['set']['email']  = isset($config_info['email']) ? $config_info['email'] : '';
	     	$data['set']['url']    = isset($config_info['url'])   ? $config_info['url']   : '';
		}

		$data['address']   = join('&nbsp;',area::name($data['province'],$data['city'],$data['area']))."&nbsp;".$data['address'];
		$this->setRenderData($data);
		$this->redirect("shop_template");
	}
	//发货单
	public function pick_template()
	{
		$this->layout='print';
		$order_id = IFilter::act( IReq::get('id'),'int' );
		$seller_id= IFilter::act( IReq::get('seller_id'),'int' );

		$tb_order = new IModel('order');
		$where    = $seller_id ? 'id='.$order_id.' and seller_id = '.$seller_id : 'id='.$order_id;
		$data     = $tb_order->getObj($where);
		if(!$data)
		{
			IError::show(403,"您没有权限查阅该订单");
		}
 		//获取地区
 		$data['address'] = join('&nbsp;',area::name($data['province'],$data['city'],$data['area']))."&nbsp;".$data['address'];

		$this->setRenderData($data);
		$this->redirect('pick_template');
	}
	//合并购物单和发货单
	public function merge_template()
	{
		$this->layout='print';
		$order_id = IFilter::act(IReq::get('id'),'int');
		$seller_id= IFilter::act( IReq::get('seller_id'),'int' );

		$tb_order = new IModel('order');
		$where    = $seller_id ? 'id='.$order_id.' and seller_id = '.$seller_id : 'id='.$order_id;
		$data     = $tb_order->getObj($where);
		if(!$data)
		{
			IError::show(403,"您没有权限查阅该订单");
		}
		if($data['seller_id'])
		{
			$sellerObj   = new IModel('seller');
			$config_info = $sellerObj->getObj('id = '.$data['seller_id']);

	     	$data['set']['name']   = isset($config_info['true_name'])? $config_info['true_name'] : '';
	     	$data['set']['phone']  = isset($config_info['phone'])    ? $config_info['phone']     : '';
	     	$data['set']['email']  = isset($config_info['email'])    ? $config_info['email']     : '';
	     	$data['set']['url']    = isset($config_info['home_url']) ? $config_info['home_url']  : '';
		}
		else
		{
			$config = new Config("site_config");
			$config_info = $config->getInfo();

	     	$data['set']['name']   = isset($config_info['name'])  ? $config_info['name']  : '';
	     	$data['set']['phone']  = isset($config_info['phone']) ? $config_info['phone'] : '';
	     	$data['set']['email']  = isset($config_info['email']) ? $config_info['email'] : '';
	     	$data['set']['url']    = isset($config_info['url'])   ? $config_info['url']   : '';
		}

 		//获取地区
 		$data['address'] = join('&nbsp;',area::name($data['province'],$data['city'],$data['area']))."&nbsp;".$data['address'];

		$this->setRenderData($data);
		$this->redirect("merge_template");
	}
	/**
	 * @brief 添加/修改发货信息
	 */
	public function ship_info_edit()
	{
		// 获取POST数据
    	$id = IFilter::act(IReq::get("sid"),'int');
    	if($id)
    	{
    		$tb_ship   = new IModel("merch_ship_info");
    		$ship_info = $tb_ship->getObj("id=".$id." and seller_id = 0");
    		if($ship_info)
    		{
    			$this->data = $ship_info;
    		}
    		else
    		{
    			die('数据不存在');
    		}
    	}
    	$this->setRenderData($this->data);
		$this->redirect('ship_info_edit');
	}
	/**
	 * @brief 设置发货信息的默认值
	 */
	public function ship_info_default()
	{
		$id = IFilter::act( IReq::get('id'),'int' );
        $default = IFilter::string(IReq::get('default'));
        $tb_merch_ship_info = new IModel('merch_ship_info');
        if($default == 1)
        {
            $tb_merch_ship_info->setData(array('is_default'=>0));
            $tb_merch_ship_info->update("seller_id = 0");
        }
        $tb_merch_ship_info->setData(array('is_default'=>$default));
        $tb_merch_ship_info->update("id = ".$id." and seller_id = 0");
        $this->redirect('ship_info_list');
	}
	/**
	 * @brief 保存添加/修改发货信息
	 */
	public function ship_info_update()
	{
		// 获取POST数据
    	$id = IFilter::act(IReq::get('sid'),'int');
    	$ship_name = IFilter::act(IReq::get('ship_name'));
    	$ship_user_name = IFilter::act(IReq::get('ship_user_name'));
    	$sex = IFilter::act(IReq::get('sex'),'int');
    	$province =IFilter::act(IReq::get('province'),'int');
    	$city = IFilter::act(IReq::get('city'),'int');
    	$area = IFilter::act(IReq::get('area'),'int');
    	$address = IFilter::act(IReq::get('address'));
    	$postcode = IFilter::act(IReq::get('postcode'),'int');
    	$mobile = IFilter::act(IReq::get('mobile'));
    	$telphone = IFilter::act(IReq::get('telphone'));
    	$is_default = IFilter::act(IReq::get('is_default'),'int');

    	$tb_merch_ship_info = new IModel('merch_ship_info');

    	//判断是否已经有了一个默认地址
    	if(isset($is_default) && $is_default==1)
    	{
    		$tb_merch_ship_info->setData(array('is_default' => 0));
    		$tb_merch_ship_info->update('seller_id = 0');
    	}
    	//设置存储数据
    	$arr['ship_name'] = $ship_name;
	    $arr['ship_user_name'] = $ship_user_name;
	    $arr['sex'] = $sex;
    	$arr['province'] = $province;
    	$arr['city'] =$city;
    	$arr['area'] =$area;
    	$arr['address'] = $address;
    	$arr['postcode'] = $postcode;
    	$arr['mobile'] = $mobile;
    	$arr['telphone'] =$telphone;
    	$arr['is_default'] = $is_default;
    	$arr['is_del'] = 1;
    	$arr['seller_id'] = 0;

    	$tb_merch_ship_info->setData($arr);
    	//判断是添加还是修改
    	if($id)
    	{
    		$tb_merch_ship_info->update('id='.$id." and seller_id = 0");
    	}
    	else
    	{
    		$tb_merch_ship_info->add();
    	}
		$this->redirect('ship_info_list');
	}
	/**
	 * @brief 删除发货信息到回收站中
	 */
	public function ship_info_del()
	{
		// 获取POST数据
    	$id = IFilter::act(IReq::get('id'),'int');

		//加载 商家发货点信息
    	$tb_merch_ship_info = new IModel('merch_ship_info');
    	$tb_merch_ship_info->setData(array('is_del' => 0));
		if($id)
		{
			$tb_merch_ship_info->update(Util::joinStr($id)." and seller_id = 0");
			$this->redirect('ship_info_list');
		}
		else
		{
			$this->redirect('ship_info_list',false);
			Util::showMessage('请选择要删除的数据');
		}
	}
	/**
	 * @brief 还原回收站的信息到列表
	 */
	public function recycle_restore()
	{
		// 获取POST数据
    	$id = IFilter::act(IReq::get('id'),'int');
		//加载 商家发货点信息
    	$tb_merch_ship_info = new IModel('merch_ship_info');
    	$tb_merch_ship_info->setData(array('is_del' => 1));
		if($id)
		{
			$tb_merch_ship_info->update(Util::joinStr($id)." and seller_id = 0");
			$this->redirect('ship_recycle_list');
		}
		else
		{
			$this->redirect('ship_recycle_list',false);
		}
	}
	/**
	 * @brief 删除收货地址的信息
	 */
	public function recycle_del()
	{
		// 获取POST数据
    	$id = IFilter::act(IReq::get('id'),'int');
		//加载 商家发货点信息
    	$tb_merch_ship_info = new IModel('merch_ship_info');
		if($id)
		{
			$tb_merch_ship_info->del(Util::joinStr($id).' and seller_id = 0');
			$this->redirect('ship_recycle_list');
		}
		else
		{
			$this->redirect('ship_recycle_list',false);
			Util::showMessage('请选择要删除的数据');
		}
	}

	//快递单背景图片上传
	public function expresswaybill_upload()
	{
		$result = array(
			'isError' => true,
		);

		if(isset($_FILES['attach']['name']) && $_FILES['attach']['name'] != '')
		{
			$photoObj = new PhotoUpload();
			$photo    = $photoObj->run();

			$result['isError'] = false;
			$result['data']    = $photo['attach']['img'];
		}
		else
		{
			$result['message'] = '请选择图片';
		}

		echo '<script type="text/javascript">parent.photoUpload_callback('.JSON::encode($result).');</script>';
	}

	//快递单添加修改
	public function expresswaybill_edit()
	{
		$id = intval(IReq::get('id'));

		$this->expressRow = array();

		//修改模式
		if($id)
		{
			$expressObj       = new IModel('expresswaybill');
			$this->expressRow = $expressObj->getObj('id = '.$id);
		}

		$this->redirect('expresswaybill_edit');
	}

	//快递单添加修改动作
	public function expresswaybill_edit_act()
	{
		$id           = intval(IReq::get('id'));
		$printExpress = IReq::get('printExpress');
		$name         = IFilter::act(IReq::get('express_name'));
		$width        = intval(IReq::get('width'));
		$height       = intval(IReq::get('height'));
		$background   = IFilter::act(IReq::get('printBackground'));
		$background   = ltrim($background,IUrl::creatUrl(''));

		if(!$printExpress)
		{
			$printExpress = array();
		}

		if(!$name)
		{
			die('快递单的名称不能为空');
		}

		$expressObj     = new IModel('expresswaybill');

		$data = array(
			'config'     => serialize($printExpress),
			'name'       => $name,
			'width'      => $width,
			'height'     => $height,
			'background' => $background,
		);

		$expressObj->setData($data);

		//修改模式
		if($id)
		{
			$is_result = $expressObj->update('id = '.$id);
		}
		else
		{
			$is_result = $expressObj->add();
		}
		echo $is_result === false ? '操作失败' : 'success';
	}

	//快递单删除
	public function expresswaybill_del()
	{
		$id = intval(IReq::get('id'));
		$expressObj = new IModel('expresswaybill');
		$expressObj->del('id = '.$id);
		$this->redirect('print_template/tab_index/3');
	}

	//选择快递单打印类型
	public function expresswaybill_template()
	{
		$this->layout = 'print';
		$seller_id    = IFilter::act(IReq::get('seller_id'),'int');

    	//获得order_id的值
		$order_id = IFilter::act(IReq::get('id'),'int');
		$order_id = is_array($order_id) ? join(',',$order_id) : $order_id;

		if(!$order_id)
		{
			$this->redirect('order_list');
			return;
		}

		$ord_class       = new Order_Class();
 		$this->orderInfo = $ord_class->getOrderInfo($order_id,$seller_id);
		$this->redirect('expresswaybill_template');
	}

	//打印快递单
	public function expresswaybill_print()
	{
		$config_conver = array();
		$this->layout  = 'print';

		$order_id     = IFilter::act(IReq::get('order_id'));
		$seller_id    = IFilter::act(IReq::get('seller_id'),'int');
		$express_id   = intval(IReq::get('express_id'));
		$expressObj   = new IModel('expresswaybill');
		$expressRow   = $expressObj->getObj('id = '.$express_id);

		if(empty($expressRow))
		{
			die('不存在此快递单信息');
		}

		$expressConfig     = unserialize($expressRow['config']);
		$expresswaybillObj = new Expresswaybill();

		$config_conver       = $expresswaybillObj->conver($expressConfig,$order_id,$seller_id);
		$this->config_conver = str_replace('trackingLeft','letterSpacing',$config_conver);
		if(!$this->config_conver)
		{
			die('快递单模板配置不正确');
		}
		$this->order_id      = $order_id;
		$this->expressRow    = $expressRow;
		$this->redirect('expresswaybill_print');
	}

	//订单导出excel 参考订单列表
	public function order_report()
	{
		//搜索条件
		$search = IFilter::act(IReq::get('search'),'strict');
		//条件筛选处理
		list($join,$where) = order_class::getSearchCondition($search);
		//拼接sql
		$orderHandle = new IQuery('order as o');
		$orderHandle->order  = "o.id desc";
		$orderHandle->fields = "o.*,d.name as distribute_name,p.name as payment_name";
		$orderHandle->join   = $join;

		//下单时间
		if(!empty($_GET['create_time_start'])) $where .= " and o.create_time >= '".$_GET['create_time_start']." 00:00:00'";
		if(!empty($_GET['create_time_end'])) $where .= " and o.create_time <= '".$_GET['create_time_end']." 23:59:59'";
		$orderHandle->where  = $where;
		$orderList = $orderHandle->find();
		
		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">订单编号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="100">日期</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货人</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货地址</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">电话</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单金额</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付方式</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付状态</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">发货状态</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品编号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品名称</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">日文品名</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">目前价格</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品数量</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">规格</td>';
		$strTable .= '</tr>';
		
		foreach($orderList as $k=>$val){
			$strTable .= '<tr>';
			$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['order_no'].'</td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['create_time'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['accept_name'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.join('&nbsp;',area::name($val['province'],$val['city'],$val['area'])).$val['address'].' &nbsp; </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">&nbsp;'.$val['telphone'].'&nbsp;'.$val['mobile'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['order_amount'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['payment_name'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.Order_Class::getOrderPayStatusText($val).' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.Order_Class::getOrderDistributionStatusText($val).' </td>';

			$orderGoods = Order_class::getOrderGoods($val['id']);
			$strGoods 			= array(
				'goodsno' 		=> '',
				'name' 			=> '',
				'name_jp' 		=> '',
				'sell_price' 	=> '',
				'goods_nums' 	=> '',
				);
			$query 				= new IQuery('goods');
			$query->finds 		= 'id,name,name_jp,sell_price';
			foreach($orderGoods as $v){
				$query->where 				= 'id='.$v['goods_id'];
				$info 						= $query->find();
				$strGoods['goodsno'] 		.= $v['goodsno'].'<br />';
				$strGoods['name'] 			.= $v['name'].'<br />';
				$strGoods['name_jp'] 		.= $info[0]['name_jp'].'<br />';
				$strGoods['sell_price'] 	.= $info[0]['sell_price'].'<br />';
				$strGoods['goods_nums'] 	.= $v['goods_nums'].'<br />';
			}
			//商品编号
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods['goodsno'].' </td>';
			//商品名称
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods['name'].' </td>';
			//日文品名
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods['name_jp'].' </td>';
			//当前价格
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods['sell_price'].' </td>';
			//商品数量
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods['goods_nums'].' </td>';
			
			unset($orderGoods);

			$strTable .= '</tr>';
		}
		$strTable .='</table>';
		unset($orderList);
		$reportObj = new report();
		$reportObj->setFileName('order');
		$reportObj->toDownload($strTable);
		exit();
	}
	function order_settlement(){
        $order_id = IFilter::act(IReq::get('id'),'int');
        $order_model = new IModel('order');
        $real_amount = $order_model->getObj('id = ' . $order_id)['real_amount'];
        $user_id = $order_model->getObj('id = ' . $order_id)['user_id'];
        $order_model->setData(['is_shop_checkout'=>1]);
        $ret = $order_model->update('id = ' . $order_id);
        if ($ret) {
            $user_shop_query = new IQuery('user as a');
            $user_shop_query->join = 'right join shop as b on a.shop_identify_id = b.identify_id';
            $user_shop_query->fields = 'identify_id,amount_available';
            $user_shop_query->where = 'a.id = ' . $user_id;
            if ($user_shop_query->find()){
                $user_shop_data = $user_shop_query->find()[0];
                $identify_id = $user_shop_data['identify_id'];
                $amount_available = $user_shop_data['amount_available'];
                $shop_model = new IModel('shop');
                $shop_model->setData(['amount_available'=>$amount_available+$real_amount]);
                $shop_model->update('identify_id = ' . $identify_id);
            }
        }
        $this->redirect('order_list');
    }
    function order_shop(){
//        $this->shop_query = new IQuery('shop');
        $this->shop_query = new IQuery('shop as a');
        $this->shop_query->join = 'left join  shop_category as b on a.category_id = b.id';
        $this->shop_query->fields = 'a.*,b.name as category_name, b.rebate';

        $page = IFilter::act(IReq::get('page'),'int');
        $this->shop_query->page = !empty($page) ? $page : 1;
//        $shop_query->find();
        $this->redirect('order_shop');
    }
    function order_shop_settlement(){
        $id = IFilter::act(IReq::get('id'),'int');
        $model = new IQuery('model');
        $user_data = $model->query('select * from iwebshop_user where id in (select b.id as user_id from iwebshop_shop as a left join iwebshop_user as b on b.shop_identify_id = a.identify_id where a.id = '.$id.' )');
        $shop_query = new IQuery('shop');
        $shop_query->where = 'id = ' . $id;
        //利率
        $shop_query_rebate = new IQuery('shop as a');
        $shop_query_rebate->join = 'left join shop_category as b on a.category_id=b.id';
        $shop_query_rebate->where = 'a.id = ' . $id;
        $this->rebate = $shop_query_rebate->find()[0]['rebate'];
        if ($shop_query->find()){
            $this->shop_data = $shop_query->find()[0];
            $temp = '';
            foreach ($user_data as $key=>$value){
                $temp .= ' or user_id = ' . $value['id'];
            }
            $temp = explode('or',$temp,2)[1];
            $date_interval = ' and PERIOD_DIFF( date_format( now( ) , \'%Y%m\' ) , date_format( create_time, \'%Y%m\' ) ) =1'; //上个月
            $ret = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 5 ' . $date_interval); // 已完成
            $this->order_data = $ret->find();
        } else {
            $this->shop_data = null;
            $this->order_data = null;
        }

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $nonce_str = $this->generate_password(30);
        $params = array(
            'appid' => 'jmj20161111',
            'mchid' => '1406917802',
            'device_info' => '',
            'nonce_str' => $nonce_str,
            'sign' => '',
            'partner_trade_no' => '321654987',
            'openid' => '',
            'check_name' => '',
            're_user_name' => '',
            'amount' => '',
            'desc' => '',
            'spbill_create_ip' => '',
        );
//        sort($params);
        ksort($params,SORT_STRING);
//        $data = $this->curl_post_ssl('https://api.mch.weixin.qq.com/secapi/pay/refund', 'merchantid=1001000');
        $data = $this->curl_post_ssl('https://api.mch.weixin.qq.com/secapi/pay/refund', $params);
        var_dump($data);
        $this->redirect('order_shop_settlement');
    }
    function order_shop_category(){
        $shop_category_query = new IQuery('shop_category');
        $shop_category_query->page = !empty($page) ? $page : 1;
        $this->shop_category_data = $shop_category_query->find();
        $page = IFilter::act(IReq::get('page'),'int');
        $this->shop_category_pagebar = $shop_category_query->getPageBar();
        $this->redirect('order_shop_category');
    }
    function order_add_shop(){
        $nums = IFilter::act(IReq::get('nums'),'int');
        $shop_model = new IModel('shop');
        $this->shop_category_query = new IQuery('shop_category');
        $count = $shop_model->get_count('');
        if (!empty($nums)){
            $shop_model = new IModel('shop');
            $name = IFilter::act(IReq::get('name'),'string');
            $address = IFilter::act(IReq::get('address'),'string');
            $category_id = IFilter::act(IReq::get('category_id'),'string');
//            var_dump($category_id);exit();
            for ($i=1;$i<=$nums;$i++){
                $shop_model->setData(['name'=>$name . ($count + $i),'register_time'=>date('Y-m-d H:i:s') ,'address'=>$address,'identify_id'=>$i . rand(1000, 9999) . date('is',time()),'category_id'=>$category_id]);
                $ret = $shop_model->add();
                if ($ret){
                    continue;
                } else {
                    return;
                }
            }
            $this->redirect('order_shop');
        }
        $this->redirect('order_add_shop');
    }
    function order_add_shop_category(){
        $id = IFilter::act(IReq::get('id'),'string');
        if ($id){
            $shop_category_query = new IQuery('shop_category');
            $shop_category_query->where = 'id = ' . $id;
            $shop_category_data = $shop_category_query->find();
            $this->shop_category_name = isset($shop_category_data[0]['name']) ? $shop_category_data[0]['name'] : null;
            $this->shop_category_id = isset($shop_category_data[0]['id']) ? $shop_category_data[0]['id'] : null;
        }

        $name = IFilter::act(IReq::get('name'),'string');
        $rebate = IFilter::act(IReq::get('rebate'),'string');
        $shop_category_model = new IModel('shop_category');
        $shop_category_model->setData(['name'=>$name,'rebate'=>$rebate]);
        if (!empty($name) && !empty($rebate)){
            $category_id = IFilter::act(IReq::get('category_id'),'int');
            if (!empty($category_id)){
                $ret = $shop_category_model->update('id = ' . $category_id);
            } else {
                $ret = $shop_category_model->add();
            }
            if ($ret){
                $this->redirect('order_shop_category');
            }
        };
        $this->redirect('order_add_shop_category');
    }
    function order_shop_info(){
        $shop_query = new IQuery('shop');
        $id = IFilter::act(IReq::get('id'),'int');
        $shop_query->where = 'id = ' . $id;
        $this->shop_data = $shop_query->find();
        $this->redirect('order_shop_info');
    }
    function generate_password( $length = 8 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            //// 第一种是使用 substr 截取$chars中的任意一位字符；
            //// 第二种是取字符数组 $chars 的任意元素
            //// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }
    /*
    请确保您的libcurl版本是否支持双向认证，版本高于7.20.1
    */
    function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
    {
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        //以下两种方式需选择一种

        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        //curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        //curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/cert.pem');
        //默认格式为PEM，可以注释
        //curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        //curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/private.pem');

        //第二种方式，两个文件合成一个.pem文件
        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
    function qrcode(){
        $identify_id = IFilter::act(IReq::get('identify_id'),'int');
        $qrCode = new QrCode();
        $qrCode
//            ->setText('http://192.168.0.13:8080/?iid=' . $identify_id)
            ->setText(IWeb::$app->config['image_host1'] . '?iid=' . $identify_id)
            ->setSize(150)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel('')
            ->setLabelFontSize(16)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        header('Content-Type: '.$qrCode->getContentType());
        $qrCode->render();
    }
}