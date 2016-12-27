<?php

/**
 * 物流类
 * @author 夏爽
 */
class logistics{
	private $username = 'jiujiamao@qq.com'; //用户名
	private $password = '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92'; //用户名
	private $url      = 'http://api.yi-ex.com/';//'http://lab.yyox.com:81/'; //接口地址
	private $api      = array( //接口方法
		'warehouseStock'  => 'warehouseStock', //商家囤货商品库存查询 added 20150727
		'createInventory' => 'createInventory', //商家发货预报
		'inboundStatus'   => 'inboundStatus', //商家包裹入库状态查询
		'createOrder'     => 'createOrder', //通知转运公司发货 创建订单
		'orderStatus'     => 'orderStatus', //获取转运状态
		'taxConfirm'      => 'taxConfirm', //通知邮客物流公司扣税金继续派送 added 20150626
		'track'           => 'track', //查询tracking
	);
	
	/**
	 * 初始化
	 */
	public function __construct(){
		/* 接口地址 */
		foreach($this->api as $k => $v){
			$this->api[$k] = $this->url.$v;
		}
	}
	
	/**
	 * 商家发货预报
	 * @param array $param array('oversea_express_no'=>'快递单号','ware_house_id'=>'仓库编号','goods_list'=>'商品列表')
	 */
	public function createInventory($param){
		/* 商品详情 */
		$query         = new IQuery('goods AS m');
		$query->join   = 'LEFT JOIN category_extend AS e ON e.goods_id=m.id LEFT JOIN category AS c ON c.id=e.category_id LEFT JOIN brand AS b ON b.id=m.brand_id';
		$query->fields = 'm.id,m.goods_no,m.name,m.name_jp,m.sell_price,c.name AS cname,b.name AS bname';
		$query->limit  = 1;
		$goodsList     = array(); //商品数据
		foreach($param['goods_list'] as $k => $v){
			$query->where = 'goods_no='.$v['goods_no'];
			$info         = $query->find();
			if(empty($info)) return array('code' => 1, 'msg' => '商品不存在');
			/* 更新活动商品价格 */
			$info        = Api::run('goodsActivity', $info);
			$info        = $info[0];
			$goodsList[] = array(
				'sku_id'         => $info['goods_no'], // 海淘商品编号，字符串 20位 （选填）
				'upc'            => $info['goods_no'], // 国际商品编号,字符串 20以内 （选填）电商客户有的UPC的话，请传递
				'name'           => $info['name_jp'], // 商品名（原始） , 字符串 40 fix 20150313
				'cny_name'       => $info['name'], // 商品名中文名, 字符串 40 add 20150313
				'category'       => empty($info['cname']) ? '日常用品' : $info['cname'], // 产品分类 字符串 ， 80字符 必填*
				'brand'          => $info['bname'], // 产品品牌 字符串， 80字符
				'spec'           => $info['name_jp'], // 商品规格, 字符串 255
				'price_unit'     => "CNY", // 货币单位 字符串10位以内,USD:美元, EUR:欧元, JPY:日元, AUD:澳元//mod 20150612 必填*
				'declared_price' => $info['sell_price'], // 单价 申报单价 浮点数10位保留两位小数,美国仓库请传递美元价格，澳洲仓库请传递澳元价格，同理其他仓库传递对应的币种价格//mod 20150612 必填*
				'count'          => $v['goods_num'] // 商品总数, int 必填*
			);
		}
		
		/* 接口参数 */
		$data['param'] = array(
			'oversea_express_no' => $param['oversea_express_no'], // 商家发货快递编号，字符串， 40位以内 必填*
			'ware_house_id'      => $param['ware_house_id'], // 海外仓库编号, 字符串， 40位 必填* 字典具体询问邮客物流人员
			'merchant_order_no'  => '', // 来件商家订单编号， 字符串， 40位（选填）
			'value_added'        => array(),//包裹增值服务 字典具体询问邮客物流人员
			'username'           => $this->username, //邮客物流注册账号 必填*
			'password'           => $this->password, //sha256 加密后的密码 必填*
			'goods_list'         => $goodsList,
		);
		/* 调用接口 */
		$rel = $this->curlHttp($this->api['createInventory'], json_encode($data), 'POST', array('Content-Type:application/json'));
		$rel = json_decode($rel,true);
		
		/* 写入数据库 */
		if($rel['code']==0){
			//写入主表
			$modelInv = new IModel('logistics_inventory');
			$modelInv->setData(array(
				'oversea_express_no' => $param['oversea_express_no'],
				'ware_house_id'      => $param['ware_house_id'],
				'status'             => 1,
				'create_time'        => time(),
				'update_time'        => time(),
			));
			$invId = $modelInv->add();
			if(!$invId) return array('code' => 0, 'msg' => '发送成功，但数据写入失败');
			//写入副表
			$modelAcc = new IModel('logistics_inventory_access');
			foreach($goodsList as $k => $v){
				$modelAcc->setData(array(
					'pid'      => $invId,
					'goods_no' => $v['sku_id'],
					'num'      => $v['count'],
				));
				$modelAcc->add();
			}
		}
		
		/* 返回参数 */
		return array('code' => $rel['code'], 'msg' => $rel['message']);
	}
	
	/**
	 * 入库状态查询
	 */
	public function inboundStatus($id=0){
		$model = new IModel('logistics_inventory');
		$info = $model->getObj('id='.$id);
		
		$data['param'] = array(
			'oversea_express_no' => $info['oversea_express_no'], // 商家发货快递编号，字符串， 40位以内
			'username'           => $this->username, //邮客物流注册账号
			'password'           => $this->password, // 加密后的密码
		);
		
		/* 调用接口 */
		$rel = $this->curlHttp($this->api['inboundStatus'], json_encode($data), 'POST', array('Content-Type:application/json'));
		$rel = json_decode($rel);
		var_dump($rel);exit();
		
		//返回参数
		$rel = array(
			'code'    => 0, // 提交结果，无错误返回0，否则返回1
			'message' => "", // 错误信息字符串，无错误返回可以为空
			'data'    => array(
				'status'     => 2, // 入库状态： 0，未预报已入库， 1预报的未入库， 2已预报已入库， 3入库异常（包裹商品和预报不符合）
				'goods_list' => array(
					array( // 包裹商品信息，暂时可选
						'upc'    => '123456789', // 国际商品编号,字符串 20以内
						'sku_id' => '123456789', // 海淘商品编号，字符串 20位 add 20150610
						'count'  => 5, // 商品总数, int
					),
				),
			),
		);
	}
	
	/**
	 * 创建订单（通知仓库发货）
	 */
	public function createOrder($order_id){
		/* 获取订单信息 */
		$queryOrder         = new IQuery('order AS m');
		$queryOrder->join   = 'LEFT JOIN user AS u ON u.id=m.user_id';
		$queryOrder->where  = 'm.id='.$order_id;
		$queryOrder->fields = '*';
		$queryOrder->limit  = 1;
		$infoOrder          = $queryOrder->find();
		if(empty($infoOrder)) return false; //空订单返回false
		$infoOrder = $infoOrder[0];
		/* 订单商品 */
		$queryGoods         = new IQuery('order_goods AS m');
		$queryGoods->join   = 'LEFT JOIN goods AS g ON g.id=m.goods_id';
		$queryGoods->where  = 'order_id='.$order_id;
		$queryGoods->order  = 'm.id ASC';
		$queryGoods->fields = 'm.goods_nums,m.real_price,g.goods_no';
		$listGoods          = $queryGoods->find();
		$goodsList          = array(); //商品列表
		foreach($listGoods as $k => $v){
			$goodsList[] = array(
				'sku_id'             => $v['goods_no'], // 海淘商品编号，字符串 20位 仓库会根据sku去拣货 （ sku根据包裹入库回调传递的值）必填*
				'upc'                => $v['goods_no'], // 国际商品编号,字符串 20以内 请将包裹入库回调传递的upc再传递过来 必填*
				'price_unit'         => 'CNY', // 货币单位 字符串10位以内,USD:美元, EUR:欧元, JPY:日元, AUD:澳元//mod 20150612
				'declared_price'     => $v['real_price'], // 单价 申报单价 浮点数10位保留两位小数,美国仓库请传递美元价格，澳洲仓库请传递澳元价格，同理其他仓库传递对应的币种价格//mod 20150612
				'count'              => $v['goods_nums'], // 商品总数, int
				'oversea_express_no' => '', // 商家发货单号，必须传空字符串
			);
		}
		/* 获取地区 */
		$area = area::name($infoOrder['province'], $infoOrder['city'], $infoOrder['area']);
		
		/* 发送的数据 */
		$data['param'] = array(
			'buyer_name'                => $infoOrder['accept_name'], // 买家(收货人)姓名，字符串， 20位以内
			'buyer_idcard'              => empty($infoOrder['sfz_num']) ? 'xxx' : $infoOrder['sfz_num'], // 买家(收货人)身份证号码， 18位字符串
			'buyer_idcard_frontend'     => $infoOrder['sfz_image1'], // 买家(收货人)身份证照片正面，字符串， 255
			'buyer_idcard_backend'      => $infoOrder['sfz_image2'], // 买家(收货人)身份证照片背面，字符串， 255
			'buyer_mobile'              => $infoOrder['mobile'], // 买家(收货人)手机号码20位字符串
			'buyer_province'            => $area[$infoOrder['province']], // 买家(收货人)省份， 20位字符串
			'buyer_city'                => $area[$infoOrder['city']], // 买家(收货人)城市， 20位字符串
			'buyer_district'            => $area[$infoOrder['area']], // 买家(收货人)区， 20位字符串
			'buyer_adress'              => $infoOrder['address'], // 买家(收货人)详细地址， 40位字符串
			'buyer_zipcode'             => $infoOrder['postcode'], // 买家(收货人)邮编 6位数字
			'value_added'               => array(),// 订单增值服务
			'warehouse_id'              => 'JPB', // 海外仓库编号, 字符串， 40位
			'username'                  => $this->username, //邮客物流注册账号
			'password'                  => $this->password, // 加密后的密码
			'customer_ref_no'           => $infoOrder['order_no'], //客户内单号，可用来防止重复下单,如果发现已有这个客户内单号创建了订单，则返回之前创建的订单号，字符串， 40位 add20150610
			'api_order_type'            => 'SKU', //字典类型（可选，默认为sku商品拣货（ SKU）） 包裹转运类型（ api订单类型），决定了订单是包裹原箱转运（ PKG），还是sku商品拣货（ SKU，包括包裹sku拣货） add20150818
			'client_identifier'         => 'ABCD', //客户的顾客识别号/系统标识(目前仅用来区分不同的系统用) add20150907 选填
			'reserve_no'                => '', //国际单号 add20151124 选填，自行清关的系统提供的国际段单号
			'inland_dest_code '         => '',//目的地编码 add 20160428
			'paint_marker'              => '', //大头笔编号 add 20160428
			'sender_name'               => '九猫家', //发货人姓名 选填， 45位字符串 add20151125
			'sender_detailaddress'      => '',// 发货人详细地址（包括省市区） 选填， 200位字符串 add20151125
			'sender_mobile'             => '', // 发货人电话 选填， 45位字符串 add20151125
			'sender_original_post'      => '',// 发货人邮编 选填， 45位字符串 add20151125
			'sender_mail'               => '', // 发货人邮箱地址 选填， 100位字符串 add20151125
			'pickup_picture_url '       => '', // 商家分拣单图片url，可用作商家独立分拣单图片用
			'package_type '             => '',// 包装种类，仅对部分电商有用，字典类型（可选，默认为ORG, 没有对包装改变的需要（ ORG）） （ CLEAN，需要将包裹换箱，换成没有任何标识的箱子，并且配合发货人名来实现出库面单上显示发件人名） add20151203
			'client_customer_id '       => $infoOrder['username'], //String 用户在电商系统的用户id add20150313
			'client_create_order_date ' => $infoOrder['create_time'], // String 用户在电商系统的下单时间 add20150313
			'goods_list'                => $goodsList,//商品列表
		);
		
		/* 调用接口 */
		$rel = $this->curlHttp($this->api['createOrder'], json_encode($data), 'POST', array('Content-Type:application/json'));
		
		var_dump($rel);
//		exit();
		/* 返回参数 */
		$rel = array(
			'code'    => 0, // 提交结果，无错误返回0，否则返回1
			'message' => "", // 错误信息字符串，无错误返回可以为空
			'data'    => array(
				'transfer_id' => "", // 邮客物流公司的转运单号，字符串20位以内
			),
		);
	}
	
	/**
	 * 获取转运状态
	 */
	public function orderStatus($transfer_id = ''){
		/* 发送的数据 */
		$data['param'] = array(
			'transfer_id' => $transfer_id, // 邮客物流公司的转运单号，字符串20位以内
		);
		
		/* 调用接口 */
		$rel = $this->curlHttp($this->api['orderStatus'], json_encode($data), 'POST', array('Content-Type:application/json'));
		var_dump($rel);
		exit();
	}
	
	/**
	 * 入库回调
	 */
	public function responsesInventory(){
		/* 接收参数 */
		$data['param'] = array(
			'ware_house_id'      => 'ORB', // 海外仓库编号, 字符串， 40位 //add 20150611
			'oversea_express_no' => '01234567890123456789', // 商家发货快递编号，字符串， 40位以内
			'status'             => 2, // 入库状态： 0，未预报已入库， 1预报的未入库， 2已预报已入库， 3入库异常（包裹商品和预报不符合）
			'client_identifier'  => 'ABCD', //客户的顾客识别号 //add 20150617
			'inbound_weight'     => 1.23, //包裹入库重量 //add 20150617
			'goods_list'         => array( // 包裹商品信息，暂时可选
				array(
					'upc'    => '123456789', // 国际商品编号,字符串 20以内
					'sku_id' => '123456789', // 海淘商品编号，字符串 20位 add 20150610
					'count'  => 5 // 商品总数, int
				),
				array(
					'upc'    => '123456789', // 国际商品编号,字符串 20以内
					'sku_id' => '123456789', // 海淘商品编号，字符串 20位 add 20150610
					'count'  => 5 // 商品总数, int
				),
			),
		);
		/* 写入数据库 */
	}
	
	/**
	 * 转运状态变更回调
	 */
	public function responsesStatus(){
		/* 接收参数 */
		$data['param'] = array(
			'transfer_id'              => '', // 邮客物流公司的转运单号，字符串20位以内
			'customer_ref_no'          => 'xxxxxxxxxssssssss', //客户内单号 add 20160129
			'status'                   => 3, // 转运状态 0 未出库 1发往国内 2递交航空公司 3抵达国内 4已转国内快递 5用户已签收// modify20150608 细化了状态
			'oversea_in_time '         => '2014-01-01 00:00:00', // 海外入库时间
			'oversea_out_time '        => '2014-01-01 00:00:00', // 海外出库（发往国内）时间
			'oversea_on_transfer_time' => "2015-12-18 23:30:00", //上航班时间 added 20151224
			'inland_in_time '          => '2014-01-01 00:00:00', // 抵达国内时间
			'inland_out_time '         => '2014-01-01 00:00:00', // 国内出库时间（转国内快递）
			'buyer_sign_time'          => null, // 用户签收时间
			'inland_express_id '       => 'ems', // 国内快递公司编码 字符串 20位
			'inland_express_no '       => '123456789', // 国内快递编号 字符串 40位
			'weight '                  => '1.52', // 转运包裹重量 单位 kg (可为空)
			'volume '                  => '1.5*1.6*1.8', // 尺寸 单位 mm (可为空)
			'totalfee '                => '25.21', // 费用 单位 人民币 (可为空)
			'air_take_off '            => '洛杉矶机场', //起飞地(可为空) add 20150828
			'airlines '                => '某某航空公司', //航空公司名称(可为空) add 20150828
			'flight '                  => 'XXX航班号', //航班号(可为空) add 20150828
			'master_waybill_no '       => '618-93625652', //主单号(可为空) add 20151110
			'api_order_type '          => 'P' //订单的类型，包裹转运 （ P）， SKU转运 （ S）（囤货），包裹SKU转运（ PS）
		);
		
		/* 返回参数 */
		$rel = array(
			'code'    => 0, // 提交结果，无错误返回0，否则返回1
			'message' => "", // 错误信息字符串，无错误返回可以为空
		);
	}
	
	/**
	 * 请求url
	 * @param string $url 请求地址
	 * @param array $body 传输内容
	 * @param string $method 传输方式
	 * @param array $headers http头信息
	 * @return bool 失败返回false
	 */
	private function curlHttp($url, $body = '', $method = 'DELETE', $headers = array()){
		//初始化curl会话
		$ch = curl_init();
		/* Curl 设置参数 */
		curl_setopt_array($ch, array(
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_0,    //强制使用 HTTP/1.0
			CURLOPT_USERAGENT      => 'toqi.net',        //伪装浏览器
			CURLOPT_CONNECTTIMEOUT => 30,        //最长等待时间
			CURLOPT_TIMEOUT        => 30,        //执行的最长秒数
			CURLOPT_RETURNTRANSFER => true,    //文件流的形式返回，而不是直接输出
			CURLOPT_BINARYTRANSFER => true, //返回原生的（Raw）输出
			CURLOPT_ENCODING       => '',        //发送所有支持的编码类型
			CURLOPT_SSL_VERIFYPEER => false,    //不返回SSL证书验证请求的结果
			CURLOPT_HEADER         => false,    //不把头文件的信息作为数据流输出
			CURLOPT_URL            => $url,    //请求的url地址
			CURLOPT_HTTPHEADER     => $headers,//设置http头信息
			CURLINFO_HEADER_OUT    => true,    //发送请求的字符串
		));
		//设置传输方式
		switch($method){
			case 'POST':
				curl_setopt($ch, CURLOPT_POST, TRUE);
				if(!empty($body))
					curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
				break;
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if(!empty($body))
					$url = $url.'?'.str_replace('amp;', '', http_build_query($body));
		}
		//执行会话
		$response = curl_exec($ch);
		//获取curl会话信息
		$httpinfo = curl_getinfo($ch);
		//关闭curl会话
		curl_close($ch);
		return $response;
	}
	
}
