<?php
/**
 * @brief 用户中心模块
 * @class Ucenter
 * @note  前台
 */
require_once __DIR__ . '/../plugins/vendor/autoload.php';
use  Endroid\QrCode\QrCode;
class Ucenter extends IController implements userAuthorization
{
	public $layout = 'ucenter';

	public function init()
	{

	}
    public function index()
    {
    	//获取用户基本信息
		$user = Api::run('getMemberInfo',$this->user['user_id']);

		//获取用户各项统计数据
		$statistics = Api::run('getMemberTongJi',$this->user['user_id']);

		//获取用户站内信条数
		$msgObj = new Mess($this->user['user_id']);
		$msgNum = $msgObj->needReadNum();

		//获取用户代金券
		$propIds = trim($user['prop'],',');
		$propIds = $propIds ? $propIds : 0;
		$propData= Api::run('getPropTongJi',$propIds);

		$this->setRenderData(array(
			"user"       => $user,
			"statistics" => $statistics,
			"msgNum"     => $msgNum,
			"propData"   => $propData,
		));

        $user_query = new IQuery('user as a');
        $user_query->join = 'right join shop as b on a.id = b.own_id';
        $user_query->where = 'a.id = ' . $this->user['user_id'];
        $this->user_shop_data = $user_query->find();

        $user_query = new IQuery('user');
        $user_query->where = 'id = ' . $this->user['user_id'];
        $this->user_data = $user_query->find();

        $this->initPayment();
        $this->redirect('index');
    }
    function recommender_shop(){
        $user_id   = $this->user['user_id'];
        $memberObj = new IModel('member','balance');
        $where     = 'user_id = '.$user_id;
        $this->memberRow = $memberObj->getObj($where);
        $this->redirect('recommender_shop');
    }
    /*获取待ru'zhang*/
    private function get_amount_tobe_booked(){
        $shop_query = new IQuery('shop');
        $user_query = new IQuery('user');
        $shop_query->where = 'own_id = ' . $this->user['user_id'];
        if (empty($shop_data = $shop_query->find())){
            return '0.00';
        } else {
            $shop_data = $shop_data[0];
        }
        $temp = '( user_id = ' . $this->user['user_id'];
        if ($shop_data){
            $user_query->where = 'shop_identify_id = ' . $shop_data['identify_id'];
            $user_data = $user_query->find();
            foreach ($user_data as $key=>$value){
                $temp .= ' or user_id = ' . $value['id'];
            }
        }
        $temp .= ')';
        $temp .= ' and seller_id = ' . $shop_data['identify_id'];
        $where = $temp . ' and pay_type != 0 and status = 5 and is_shop_checkout = 0';
        $order_query = new IQuery('order');
        $order_query->where = $where;
        $order_query->fields = 'sum(real_amount) as amount_tobe_booked';
        $this->amount_tobe_booked = $order_query->find()[0]['amount_tobe_booked'];
        $this->amount_tobe_booked = empty($this->amount_tobe_booked) ? '0.00' : $this->amount_tobe_booked;

        $shop_category = new IQuery('shop_category');
        $shop_category->where = 'id = ' . $shop_data['category_id'];
        $shop_category_data = $shop_category->find();
        $this->amount_tobe_booked = $this->amount_tobe_booked * $shop_category_data[0]['rebate'];
        return;
    }

	//[用户头像]上传
	function user_ico_upload()
	{
		$result = array(
			'isError' => true,
		);

		if(isset($_FILES['attach']['name']) && $_FILES['attach']['name'] != '')
		{
			$photoObj = new PhotoUpload();
			$photo    = $photoObj->run();

			if($photo['attach']['img'])
			{
				$user_id   = $this->user['user_id'];
				$user_obj  = new IModel('user');
				$dataArray = array(
					'head_ico' => $photo['attach']['img'],
				);
				$user_obj->setData($dataArray);
				$where  = 'id = '.$user_id;
				$isSuss = $user_obj->update($where);

				if($isSuss !== false)
				{
					$result['isError'] = false;
					$result['data'] = IUrl::creatUrl().$photo['attach']['img'];
					ISafe::set('head_ico',$dataArray['head_ico']);
				}
				else
				{
					$result['message'] = '上传失败';
				}
			}
			else
			{
				$result['message'] = '上传失败';
			}
		}
		else
		{
			$result['message'] = '请选择图片';
		}
		echo '<script type="text/javascript">parent.callback_user_ico('.JSON::encode($result).');</script>';
	}

    /**
     * @brief 我的订单列表
     */
    public function order()
    {
        $this->initPayment();
        $this->redirect('order');

    }
    public function order_u()
    {
//        $this->initPayment();
        $this->redirect('order_u');

    }
    /**
     * @brief 初始化支付方式
     */
    private function initPayment()
    {
        $payment = new IQuery('payment');
        $payment->fields = 'id,name,type';
        $payments = $payment->find();
        $items = array();
        foreach($payments as $pay)
        {
            $items[$pay['id']]['name'] = $pay['name'];
            $items[$pay['id']]['type'] = $pay['type'];
        }
        $this->payments = $items;
    }
    /**
     * @brief 订单详情
     * @return String
     */
    public function order_detail()
    {
        $id = IFilter::act(IReq::get('id'),'int');

        $orderObj = new order_class();
        $this->order_info = $orderObj->getOrderShow($id,$this->user['user_id']);

        if(!$this->order_info)
        {
        	IError::show(403,'订单信息不存在');
        }
        $orderStatus = Order_Class::getOrderStatus($this->order_info);
        $this->setRenderData(array('orderStatus' => $orderStatus));
        $this->redirect('order_detail',false);
    }

    //操作订单状态
	public function order_status()
	{
		$op    = IFilter::act(IReq::get('op'));
		$id    = IFilter::act( IReq::get('order_id'),'int' );
		$model = new IModel('order');

		switch($op)
		{
			case "cancel":
			{
				$model->setData(array('status' => 3));
				if($model->update("id = ".$id." and distribution_status = 0 and status = 1 and user_id = ".$this->user['user_id']))
				{
					order_class::resetOrderProp($id);
				}
			}
			break;

			case "confirm":
			{
				$model->setData(array('status' => 5,'completion_time' => ITime::getDateTime()));
				if($model->update("id = ".$id." and distribution_status = 1 and user_id = ".$this->user['user_id']))
				{
					$orderRow = $model->getObj('id = '.$id);

					//确认收货后进行支付
					Order_Class::updateOrderStatus($orderRow['order_no']);

		    		//增加用户评论商品机会
		    		Order_Class::addGoodsCommentChange($id);

		    		//确认收货以后直接跳转到评论页面
		    		$this->redirect('evaluation');
				}
			}
			break;
		}
//		$this->redirect("order_detail/id/$id");
		$this->redirect("order");
	}
    /**
     * @brief 我的地址
     */
    public function address()
    {
		//取得自己的地址
		$query = new IQuery('address');
        $query->where = 'user_id = '.$this->user['user_id'];
		$address = $query->find();
		$areas   = array();

		if($address)
		{
			foreach($address as $ad)
			{
				$temp = area::name($ad['province'],$ad['city'],$ad['area']);
				if(isset($temp[$ad['province']]) && isset($temp[$ad['city']]) && isset($temp[$ad['area']]))
				{
					$areas[$ad['province']] = $temp[$ad['province']];
					$areas[$ad['city']]     = $temp[$ad['city']];
					$areas[$ad['area']]     = $temp[$ad['area']];
				}
			}
		}

		$this->areas = $areas;
		$this->address = $address;
        $this->redirect('address');
    }
    /**
     * @brief 收货地址管理
     */
	public function address_edit()
	{
		$id          = IFilter::act(IReq::get('id'),'int');
		$accept_name = IFilter::act(IReq::get('accept_name'),'name');
		$province    = IFilter::act(IReq::get('province'),'int');
		$city        = IFilter::act(IReq::get('city'),'int');
		$area        = IFilter::act(IReq::get('area'),'int');
		$address     = IFilter::act(IReq::get('address'));
		$zip         = IFilter::act(IReq::get('zip'),'zip');
		$telphone    = IFilter::act(IReq::get('telphone'),'phone');
		$mobile      = IFilter::act(IReq::get('mobile'),'mobile');
		$default     = IReq::get('is_default')!= 1 ? 0 : 1;
        $user_id     = $this->user['user_id'];

		$model = new IModel('address');
		$data  = array('user_id'=>$user_id,'accept_name'=>$accept_name,'province'=>$province,'city'=>$city,'area'=>$area,'address'=>$address,'zip'=>$zip,'telphone'=>$telphone,'mobile'=>$mobile,'is_default'=>$default);

        //如果设置为首选地址则把其余的都取消首选
        if($default==1)
        {
            $model->setData(array('is_default' => 0));
            $model->update("user_id = ".$this->user['user_id']);
        }

		$model->setData($data);

		if($id == '')
		{
			$model->add();
		}
		else
		{
			$model->update('id = '.$id);
		}
		$this->redirect('address');
	}
    /**
     * @brief 收货地址删除处理
     */
	public function address_del()
	{
		$id = IFilter::act( IReq::get('id'),'int' );
		$model = new IModel('address');
		$model->del('id = '.$id.' and user_id = '.$this->user['user_id']);
		$this->redirect('address');
	}
    /**
     * @brief 设置默认的收货地址
     */
    public function address_default()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $default = IFilter::act(IReq::get('is_default'));
        $model = new IModel('address');
        if($default == 1)
        {
            $model->setData(array('is_default' => 0));
            $model->update("user_id = ".$this->user['user_id']);
        }
        $model->setData(array('is_default' => $default));
        $model->update("id = ".$id." and user_id = ".$this->user['user_id']);
        $this->redirect('address');
    }
    /**
     * @brief 退款申请页面
     */
    public function refunds_update()
    {
        $order_goods_id = IFilter::act( IReq::get('order_goods_id'),'int' );
        $order_id       = IFilter::act( IReq::get('order_id'),'int' );
        $user_id        = $this->user['user_id'];
        $content        = IFilter::act(IReq::get('content'),'text');
        $message        = '';

        if(!$content || !$order_goods_id)
        {
        	$message = "请填写退款理由和选择要退款的商品";
	        $this->redirect('refunds',false);
	        Util::showMessage($message);
        }

        $orderDB      = new IModel('order');
        $orderRow     = $orderDB->getObj("id = ".$order_id." and user_id = ".$user_id);
        $refundResult = Order_Class::isRefundmentApply($orderRow,$order_goods_id);

        //判断退款申请是否有效
        if($refundResult === true)
        {
			//退款单数据
    		$updateData = array(
				'order_no'       => $orderRow['order_no'],
				'order_id'       => $order_id,
				'user_id'        => $user_id,
				'time'           => ITime::getDateTime(),
				'content'        => $content,
				'seller_id'      => $orderRow['seller_id'],
				'order_goods_id' => join(",",$order_goods_id),
			);

    		//写入数据库
    		$refundsDB = new IModel('refundment_doc');
    		$refundsDB->setData($updateData);
    		$refundsDB->add();

    		$this->redirect('refunds');
        }
        else
        {
        	$message = $refundResult;
	        $this->redirect('refunds',false);
	        Util::showMessage($message);
        }
    }
    /**
     * @brief 退款申请删除
     */
    public function refunds_del()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $model = new IModel("refundment_doc");
        $model->del("id = ".$id." and user_id = ".$this->user['user_id']);
        $this->redirect('refunds');
    }
    /**
     * @brief 查看退款申请详情
     */
    public function refunds_detail()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $refundDB = new IModel("refundment_doc");
        $refundRow = $refundDB->getObj("id = ".$id." and user_id = ".$this->user['user_id']);
        if($refundRow)
        {
        	//获取商品信息
        	$orderGoodsDB   = new IModel('order_goods');
        	$orderGoodsList = $orderGoodsDB->query("id in (".$refundRow['order_goods_id'].")");
        	if($orderGoodsList)
        	{
        		$refundRow['goods'] = $orderGoodsList;
        		$this->data = $refundRow;
        	}
        	else
        	{
	        	$this->redirect('refunds',false);
	        	Util::showMessage("没有找到要退款的商品");
        	}
        	$this->redirect('refunds_detail');
        }
        else
        {
        	$this->redirect('refunds',false);
        	Util::showMessage("退款信息不存在");
        }
    }
    /**
     * @brief 查看退款申请详情
     */
	public function refunds_edit()
	{
		$order_id = IFilter::act(IReq::get('order_id'),'int');
		if($order_id)
		{
			$orderDB  = new IModel('order');
			$orderRow = $orderDB->getObj('id = '.$order_id.' and user_id = '.$this->user['user_id']);
			if($orderRow)
			{
				$this->orderRow = $orderRow;
				$this->redirect('refunds_edit');
				return;
			}
		}
		$this->redirect('refunds');
	}

    /**
     * @brief 建议中心
     */
    public function complain_edit()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $title = IFilter::act(IReq::get('title'),'string');
        $content = IFilter::act(IReq::get('content'),'string' );
        $user_id = $this->user['user_id'];
        $model = new IModel('suggestion');
        $model->setData(array('user_id'=>$user_id,'title'=>$title,'content'=>$content,'time'=>ITime::getDateTime()));
        if($id =='')
        {
            $model->add();
        }
        else
        {
            $model->update('id = '.$id.' and user_id = '.$this->user['user_id']);
        }
        $this->redirect('complain');
    }
    //站内消息
    public function message()
    {
    	$msgObj = new Mess($this->user['user_id']);
    	$msgIds = $msgObj->getAllMsgIds();
    	$msgIds = $msgIds ? $msgIds : 0;
		$this->setRenderData(array('msgIds' => $msgIds,'msgObj' => $msgObj));
    	$this->redirect('message');
    }
    /**
     * @brief 删除消息
     * @param int $id 消息ID
     */
    public function message_del()
    {
        $id = IFilter::act( IReq::get('id') ,'int' );
        $msg = new Mess($this->user['user_id']);
        $msg->delMessage($id);
        $this->redirect('message');
    }
    public function message_read()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $msg = new Mess($this->user['user_id']);
        echo $msg->writeMessage($id,1);
    }

    //[修改密码]修改动作
    function password_edit()
    {
    	$user_id    = $this->user['user_id'];

    	$fpassword  = IReq::get('fpassword');
    	$password   = IReq::get('password');
    	$repassword = IReq::get('repassword');

    	$userObj    = new IModel('user');
    	$where      = 'id = '.$user_id;
    	$userRow    = $userObj->getObj($where);

		if(!preg_match('|\w{6,32}|',$password))
		{
			$message = '密码格式不正确，请重新输入';
		}
    	else if($password != $repassword)
    	{
    		$message  = '二次密码输入的不一致，请重新输入';
    	}
    	else if(md5($fpassword) != $userRow['password'])
    	{
    		$message  = '原始密码输入错误';
    	}
    	else
    	{
    		$passwordMd5 = md5($password);
	    	$dataArray = array(
	    		'password' => $passwordMd5,
	    	);

	    	$userObj->setData($dataArray);
	    	$result  = $userObj->update($where);
	    	if($result)
	    	{
	    		ISafe::set('user_pwd',$passwordMd5);
	    		$message = '密码修改成功';
	    	}
	    	else
	    	{
	    		$message = '密码修改失败';
	    	}
		}

    	$this->redirect('password',false);
    	Util::showMessage($message);
    }

    //[个人资料]展示 单页
    function info()
    {
    	$user_id = $this->user['user_id'];

    	$userObj       = new IModel('user');
    	$where         = 'id = '.$user_id;
    	$this->userRow = $userObj->getObj($where);

    	$memberObj       = new IModel('member');
    	$where           = 'user_id = '.$user_id;
    	$this->memberRow = $memberObj->getObj($where);
    	$this->redirect('info');
    }

    //[个人资料] 修改 [动作]
    function info_edit_act()
    {
		$email     = IFilter::act( IReq::get('email'),'string');
		$mobile    = IFilter::act( IReq::get('mobile'),'string');

    	$user_id   = $this->user['user_id'];
    	$memberObj = new IModel('member');
    	$where     = 'user_id = '.$user_id;

		if($email)
		{
			$memberRow = $memberObj->getObj('user_id != '.$user_id.' and email = "'.$email.'"');
			if($memberRow)
			{
				IError::show('邮箱已经被注册');
			}
		}

		if($mobile)
		{
			$memberRow = $memberObj->getObj('user_id != '.$user_id.' and mobile = "'.$mobile.'"');
			if($memberRow)
			{
				IError::show('手机已经被注册');
			}
		}

    	//地区
    	$province = IFilter::act( IReq::get('province','post') ,'string');
    	$city     = IFilter::act( IReq::get('city','post') ,'string' );
    	$area     = IFilter::act( IReq::get('area','post') ,'string' );
    	$areaArr  = array_filter(array($province,$city,$area));

    	$dataArray       = array(
    		'email'        => $email,
    		'true_name'    => IFilter::act( IReq::get('true_name') ,'string'),
    		'sex'          => IFilter::act( IReq::get('sex'),'int' ),
    		'birthday'     => IFilter::act( IReq::get('birthday') ),
    		'zip'          => IFilter::act( IReq::get('zip') ,'string' ),
    		'qq'           => IFilter::act( IReq::get('qq') , 'string' ),
    		'contact_addr' => IFilter::act( IReq::get('contact_addr'), 'string'),
    		'mobile'       => $mobile,
    		'telephone'    => IFilter::act( IReq::get('telephone'),'string'),
    		'area'         => $areaArr ? ",".join(",",$areaArr)."," : "",
    	);

    	$memberObj->setData($dataArray);
    	$memberObj->update($where);
    	$this->info();
    }

    //[账户余额] 展示[单页]
    function withdraw()
    {
    	$user_id   = $this->user['user_id'];

    	$memberObj = new IModel('member','balance');
    	$where     = 'user_id = '.$user_id;
    	$this->memberRow = $memberObj->getObj($where);
    	$this->redirect('withdraw');
    }

	//[账户余额] 提现动作
    function withdraw_act()
    {
    	$user_id = $this->user['user_id'];
    	$amount  = IFilter::act( IReq::get('amount','post') ,'float' );
    	$message = '';

    	$dataArray = array(
    		'name'   => IFilter::act( IReq::get('name','post') ,'string'),
    		'note'   => IFilter::act( IReq::get('note','post'), 'string'),
			'amount' => $amount,
			'user_id'=> $user_id,
			'time'   => ITime::getDateTime(),
    	);

		$mixAmount = 0;
		$memberObj = new IModel('member');
		$where     = 'user_id = '.$user_id;
		$memberRow = $memberObj->getObj($where,'balance');

		//提现金额范围
		if($amount <= $mixAmount)
		{
			$message = '提现的金额必须大于'.$mixAmount.'元';
		}
		else if($amount > $memberRow['balance'])
		{
			$message = '提现的金额不能大于您的帐户余额';
		}
		else
		{
	    	$obj = new IModel('withdraw');
	    	$obj->setData($dataArray);
	    	$obj->add();
	    	$this->redirect('withdraw');
		}

		if($message != '')
		{
			$this->memberRow = array('balance' => $memberRow['balance']);
			$this->withdrawRow = $dataArray;
			$this->redirect('withdraw',false);
			Util::showMessage($message);
		}
    }

    //[账户余额] 提现详情
    function withdraw_detail()
    {
    	$user_id = $this->user['user_id'];

    	$id  = IFilter::act( IReq::get('id'),'int' );
    	$obj = new IModel('withdraw');
    	$where = 'id = '.$id.' and user_id = '.$user_id;
    	$this->withdrawRow = $obj->getObj($where);
    	$this->redirect('withdraw_detail');
    }

    //[提现申请] 取消
    function withdraw_del()
    {
    	$id = IFilter::act( IReq::get('id'),'int');
    	if($id)
    	{
    		$dataArray   = array('is_del' => 1);
    		$withdrawObj = new IModel('withdraw');
    		$where = 'id = '.$id.' and user_id = '.$this->user['user_id'];
    		$withdrawObj->setData($dataArray);
    		$withdrawObj->update($where);
    	}
    	$this->redirect('withdraw');
    }

    //[余额交易记录]
    function account_log()
    {
    	$user_id   = $this->user['user_id'];

    	$memberObj = new IModel('member');
    	$where     = 'user_id = '.$user_id;
    	$this->memberRow = $memberObj->getObj($where);
    	$this->redirect('account_log');
    }

    //[收藏夹]备注信息
    function edit_summary()
    {
    	$user_id = $this->user['user_id'];

    	$id      = IFilter::act( IReq::get('id'),'int' );
    	$summary = IFilter::act( IReq::get('summary'),'string' );

    	//ajax返回结果
    	$result  = array(
    		'isError' => true,
    	);

    	if(!$id)
    	{
    		$result['message'] = '收藏夹ID值丢失';
    	}
    	else if(!$summary)
    	{
    		$result['message'] = '请填写正确的备注信息';
    	}
    	else
    	{
	    	$favoriteObj = new IModel('favorite');
	    	$where       = 'id = '.$id.' and user_id = '.$user_id;

	    	$dataArray   = array(
	    		'summary' => $summary,
	    	);

	    	$favoriteObj->setData($dataArray);
	    	$is_success = $favoriteObj->update($where);

	    	if($is_success === false)
	    	{
	    		$result['message'] = '更新信息错误';
	    	}
	    	else
	    	{
	    		$result['isError'] = false;
	    	}
    	}
    	echo JSON::encode($result);
    }

    //[收藏夹]删除
    function favorite_del()
    {
    	$user_id = $this->user['user_id'];
    	$id      = IReq::get('id');

		if(!empty($id))
		{
			$id = IFilter::act($id,'int');

			$favoriteObj = new IModel('favorite');

			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = 'user_id = '.$user_id.' and id in ('.$idStr.')';
			}
			else
			{
				$where = 'user_id = '.$user_id.' and id = '.$id;
			}

			$favoriteObj->del($where);
			$this->redirect('favorite');
		}
		else
		{
			$this->redirect('favorite',false);
			Util::showMessage('请选择要删除的数据');
		}
    }

    //[我的积分] 单页展示
    function integral()
    {
    	/*获取积分增减的记录日期时间段*/
    	$this->historyTime = IFilter::string( IReq::get('history_time','post') );
    	$defaultMonth = 3;//默认查找最近3个月内的记录

		$lastStamp    = ITime::getTime(ITime::getNow('Y-m-d')) - (3600*24*30*$defaultMonth);
		$lastTime     = ITime::getDateTime('Y-m-d',$lastStamp);

		if($this->historyTime != null && $this->historyTime != 'default')
		{
			$historyStamp = ITime::getDateTime('Y-m-d',($lastStamp - (3600*24*30*$this->historyTime)));
			$this->c_datetime = 'datetime >= "'.$historyStamp.'" and datetime < "'.$lastTime.'"';
		}
		else
		{
			$this->c_datetime = 'datetime >= "'.$lastTime.'"';
		}

    	$memberObj         = new IModel('member');
    	$where             = 'user_id = '.$this->user['user_id'];
    	$this->memberRow   = $memberObj->getObj($where,'point');
    	$this->redirect('integral',false);
    }

    //[我的积分]积分兑换代金券 动作
    function trade_ticket()
    {
    	$ticketId = IFilter::act( IReq::get('ticket_id','post'),'int' );
    	$message  = '';
    	if(intval($ticketId) == 0)
    	{
    		$message = '请选择要兑换的代金券';
    	}
    	else
    	{
    		$nowTime   = ITime::getDateTime();
    		$ticketObj = new IModel('ticket');
    		$ticketRow = $ticketObj->getObj('id = '.$ticketId.' and point > 0 and start_time <= "'.$nowTime.'" and end_time > "'.$nowTime.'"');
    		if(empty($ticketRow))
    		{
    			$message = '对不起，此代金券不能兑换';
    		}
    		else
    		{
	    		$memberObj = new IModel('member');
	    		$where     = 'user_id = '.$this->user['user_id'];
	    		$memberRow = $memberObj->getObj($where,'point');

	    		if($ticketRow['point'] > $memberRow['point'])
	    		{
	    			$message = '对不起，您的积分不足，不能兑换此类代金券';
	    		}
	    		else
	    		{
	    			//生成红包
					$dataArray = array(
						'condition' => $ticketRow['id'],
						'name'      => $ticketRow['name'],
						'card_name' => 'T'.IHash::random(8),
						'card_pwd'  => IHash::random(8),
						'value'     => $ticketRow['value'],
						'start_time'=> $ticketRow['start_time'],
						'end_time'  => $ticketRow['end_time'],
						'is_send'   => 1,
					);
					$propObj = new IModel('prop');
					$propObj->setData($dataArray);
					$insert_id = $propObj->add();

					//更新用户prop字段
					$memberArray = array('prop' => "CONCAT(IFNULL(prop,''),'{$insert_id},')");
					$memberObj->setData($memberArray);
					$result = $memberObj->update('user_id = '.$this->user["user_id"],'prop');

					//代金券成功
					if($result)
					{
						$pointConfig = array(
							'user_id' => $this->user['user_id'],
							'point'   => '-'.$ticketRow['point'],
							'log'     => '积分兑换代金券，扣除了 -'.$ticketRow['point'].'积分',
						);
						$pointObj = new Point;
						$pointObj->update($pointConfig);
					}
	    		}
    		}
    	}

    	//展示
    	if($message != '')
    	{
    		$this->integral();
    		Util::showMessage($message);
    	}
    	else
    	{
    		$this->redirect('redpacket');
    	}
    }

    /**
     * 余额付款
     * T:支付失败;
     * F:支付成功;
     */
    function payment_balance()
    {
    	$urlStr  = '';
    	$user_id = intval($this->user['user_id']);

    	$return['attach']     = IReq::get('attach');
    	$return['total_fee']  = IReq::get('total_fee');
    	$return['order_no']   = IReq::get('order_no');
    	$return['return_url'] = IReq::get('return_url');
    	$sign                 = IReq::get('sign');
    	if(stripos($return['order_no'],'recharge') !== false)
    	{
    		IError::show(403,'余额支付方式不能用于在线充值');
    	}

    	if(floatval($return['total_fee']) < 0 || $return['order_no'] == '' || $return['return_url'] == '' || $return['attach'] == '')
    	{
    		IError::show(403,'支付参数不正确');
    	}

		$paymentDB  = new IModel('payment');
		$paymentRow = $paymentDB->getObj('class_name = "balance" ');
		$pkey       = Payment::getConfigParam($paymentRow['id'],'M_PartnerKey');

    	//md5校验
    	ksort($return);
		foreach($return as $key => $val)
		{
			$urlStr .= $key.'='.urlencode($val).'&';
		}

		$encryptKey = isset(IWeb::$app->config['encryptKey']) ? IWeb::$app->config['encryptKey'] : 'iwebshop';
		$urlStr .= $pkey.$encryptKey;
		if($sign != md5($urlStr))
		{
			IError::show(403,'数据校验不正确');
		}

    	$memberObj = new IModel('member');
    	$memberRow = $memberObj->getObj('user_id = '.$user_id);

    	if(empty($memberRow))
    	{
    		IError::show(403,'用户信息不存在');
    	}

    	if($memberRow['balance'] < $return['total_fee'])
    	{
    		IError::show(403,'账户余额不足');
    	}

		//检查订单状态
		$orderObj = new IModel('order');
		$orderRow = $orderObj->getObj('order_no  = "'.$return['order_no'].'" and pay_status = 0 and status = 1 and user_id = '.$user_id);
		if(!$orderRow)
		{
			IError::show(403,'订单号【'.$return['order_no'].'】已经被处理过，请查看订单状态');
		}

		//扣除余额并且记录日志
		$logObj = new AccountLog();
		$config = array(
			'user_id'  => $user_id,
			'event'    => 'pay',
			'num'      => $return['total_fee'],
			'order_no' => str_replace("_",",",$return['attach']),
		);
		$is_success = $logObj->write($config);
		if(!$is_success)
		{
			IError::show(403,$logObj->error ? $logObj->error : '用户余额更新失败');
		}

		$return['is_success'] = $is_success ? 'T' : 'F';
    	ksort($return);

    	//返还的URL地址
		$responseUrl = '';
		foreach($return as $key => $val)
		{
			$responseUrl .= $key.'='.urlencode($val).'&';
		}

		$returnUrl = urldecode($return['return_url']);
		if(stripos($returnUrl,'?') === false)
		{
			$returnJumpUrl = $returnUrl.'?'.$responseUrl;
		}
		else
		{
			$returnJumpUrl = $returnUrl.'&'.$responseUrl;
		}

		//计算要发送的md5校验
		$encryptKey = isset(IWeb::$app->config['encryptKey']) ? IWeb::$app->config['encryptKey'] : 'iwebshop';
		$urlStrMD5  = md5($responseUrl.$pkey.$encryptKey);

		//拼接进返还的URL中
		$returnJumpUrl.= 'sign='.$urlStrMD5;

		//同步通知
    	header('location:'.$returnJumpUrl);
    }

    //我的代金券
    function redpacket()
    {
		$member_info = Api::run('getMemberInfo',$this->user['user_id']);
		$propIds     = trim($member_info['prop'],',');
		$propIds     = $propIds ? $propIds : 0;
		$this->setRenderData(array('propId' => $propIds));
		$this->redirect('redpacket');
    }

    //申请开店
    function shop_register(){
        $license = IFilter::act(IReq::get('license'),'string');
        $name = IFilter::act(IReq::get('name'),'string');
        $address = IFilter::act(IReq::get('address'),'string');
        $shop_model = new IModel('shop');
        if ($shop_model->getObj('own_id = '.$this->user['user_id'])){$this->redirect('shop_index');}
        if ($license){
            $license_query = new IQuery('license');
            $license_query->where = ' license = "' . $license . '" and status = 0';
            $license_data = $license_query->find()[0];
            if ($license_data){
                if (!$shop_model->getObj('own_id = '.$this->user['user_id'])){
                    $identify = rand(000, 999) . date('His',time());
                    $shop_model->setData(['name'=>$name,'address'=>$address,'own_id'=>$this->user['user_id'],'register_license'=>$license,'register_time'=>date('Y-m-d H:i:s', time()),'identify_id'=>$identify]);
                    $ret = $shop_model->add();
                    if ($ret){
                        $license_model = new IModel('license');
                        $license_model->setData(['status'=>1]);
                        $license_model->update('license = "' . $license . '"');
                    }
                }
            } else {
                IError::show('');
            }
        }
        $this->redirect('shop_register');
    }
    function shop_index(){
        $shop_query = new IQuery('shop');
        $shop_query->where = 'own_id = ' . $this->user['user_id'];
        $shop_data = $shop_query->find()[0];
        $shop_data['identify_qrcode'] = IWeb::$app->config['image_host1'] . '/ucenter/qrcode/identify_id/' . $shop_data['identify_id'];
        $this->shop_data = $shop_data;
        $this->redirect('shop_index');
    }
    function qrcode(){
        $identify_id = IFilter::act(IReq::get('identify_id'),'int');
        $qrCode = new QrCode();
        $qrCode
            ->setText(IWeb::$app->config['image_host1'] . '?iid=' . $identify_id)
            ->setSize(150)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabel($identify_id)
            ->setLabelFontSize(16)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        header('Content-Type: '.$qrCode->getContentType());
        $qrCode->render();
    }
    function account_amount(){

    }
    //收益
    function shop_income(){
        $shop_query = new IQuery('shop');
        $shop_query->where = 'own_id = ' . $this->user['user_id'];
        $user_shop_data = $shop_query->find()[0];
        $this->user_shop_data = $user_shop_data;

        $memberObj = new IModel('member','balance');
        $where     = 'user_id = '.$this->user['user_id'];
        $this->memberRow = $memberObj->getObj($where);

        if ($this->user_shop_data){

            //待入账金额
            $this->get_amount_tobe_booked();
            //账户余额
            $shop_query = new IQuery('shop');
            $shop_query->where = 'own_id = ' . $this->user['user_id'];
            $this->amount_available = $shop_query->find()[0]['amount_available'];
        }
        $this->redirect('shop_income');
    }
    //账户余额
    function shop_balance(){
        $this->redirect('shop_balance');
    }
    //待入账金额
    function shop_amount_tobe_booked(){
//        $this->get_amount_tobe_booked();
        $shop_query = new IQuery('shop');
        $user_query = new IQuery('user');
        $shop_query->where = 'own_id = ' . $this->user['user_id'];
        $shop_data = $shop_query->find()[0];
        $temp = '';
        if ($shop_data){
            $user_query->where = 'shop_identify_id = ' . $shop_data['identify_id'];
            $user_data = $user_query->find();
            foreach ($user_data as $key=>$value){
                $temp .= ' or user_id = ' . $value['id'];
            }
            if (!empty($temp)) $temp = explode('or',$temp,2)[1];
        }
        if (!empty($temp)){
            $temp = '(' . $temp . ')';
            $date_interval = ' and PERIOD_DIFF( date_format( now( ) , \'%Y%m\' ) , date_format( create_time, \'%Y%m\' ) ) =1'; //上个月
            $this->last_month_distribute_order_ret = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 2 and (distribution_status = 0 or distribution_status = 1)' . $date_interval)->find(); // 待发货 待收货
            $date_interval = ' and DATE_FORMAT( completion_time, \'%Y%m\' ) = DATE_FORMAT( CURDATE( ) , \'%Y%m\' )'; //本月
            $this->complete_order_ret = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 5 ' . $date_interval)->find(); // 已完成
            $date_interval = ' and DATE_FORMAT( create_time, \'%Y%m\' ) = DATE_FORMAT( CURDATE( ) , \'%Y%m\' )'; //本月
            $this->distribute_order_ret = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 2 and (distribution_status = 0 or distribution_status = 1)' . $date_interval)->find(); // 待发货 待收货
        }
        $this->redirect('shop_amount_tobe_booked');
    }
    //累计收益
    function shop_accumulated_income(){
        $this->redirect('shop_accumulated_income');
    }
    //编辑店铺信息
    function shop_edit(){
        $name = IFilter::act(IReq::get('name'),'string');
        $description = IFilter::act(IReq::get('description'),'string');
        $address = IFilter::act(IReq::get('address'),'string');
        $phone = IFilter::act(IReq::get('phone'),'string');
        $identify_id = IFilter::act(IReq::get('id'),'int');
        if ($name){
            $shop_model = new IModel('shop');
            $shop_model->setData(['name' => $name, 'description' => $description, 'address' => $address, 'phone'=>$phone]);
            $ret = $shop_model->update('identify_id = ' . $identify_id.' and own_id = ' .$this->user['user_id']);
            if ($ret) $this->redirect('shop_user/id/' . $identify_id);
        }
        $shop_query = new IQuery('shop');
        $shop_query->where = 'identify_id = ' . $identify_id . ' and own_id = ' .$this->user['user_id'];
        $this->shop_data = $shop_query->find();
        if (empty($this->shop_data)) $this->redirect('index');
        $this->redirect('shop_edit');
    }
    //用户店铺信息
    function shop_user(){
        $identify_id = IFilter::act(IReq::get('id'),'int');
        $shop_query = new IQuery('shop');
        $shop_query->where = 'identify_id = ' . $identify_id . ' and own_id = ' .$this->user['user_id'];
        $this->shop_data = $shop_query->find();
        if (empty($this->shop_data)) $this->redirect('index');
        $this->identify_id = $identify_id;
        $this->redirect('shop_user');
    }
    function settlement_info(){
        $this->redirect('settlement_info');
    }

    /**
     * 合伙人待入账信息
     */
    function recommender_shops_tobe_booked(){
        $this->shop_nums = $this->get_associate_shop_nums();
        $this->redirect('recommender_shops_tobe_booked');
    }

    /**
     * 合伙人旗下店铺
     */
    function recommender_associate_shop(){
        $shop_query = new IQuery('shop as a');
        $shop_query->join = 'right join user as b on a.own_id = b.id';
        $shop_query->where = 'a.recommender = ' . $this->user['user_id'];
        $this->data = $shop_query->find();
        $this->redirect('recommender_associate_shop');
    }
    private function get_associate_shop_nums(){
        $shop_query = new IQuery('shop');
        $shop_query->where = 'recommender = ' . $this->user['user_id'];
        $ret = $shop_query->find();
        if (empty($ret)){
            return false;
        } else {
            return count($ret);
        }
    }
}