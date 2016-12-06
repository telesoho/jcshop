<?php
require __DIR__ . '/../plugins/vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
class Apic extends IController
{
//    public $layout='site_mini';
    private $log;
    private $securityLogger;
    function init()
    {
        $dateFormat = "Y-m-d h:i:s";
        $output = "[%datetime% ". substr(explode(".", explode(" ", microtime())[0])[1], 0, 3) ."] ".strtolower(__CLASS__).".php(".__LINE__.") [%level_name%]: %message%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $date = date('Y-m-d', time());
        $stream = new StreamHandler(__DIR__ . '/../backup/logs/'.$date.'.log', Logger::DEBUG);
        $stream->setFormatter($formatter);
        $this->log = new Logger('api');
        $this->log->pushHandler($stream);
//        header("Content-type: application/json");
    }
    /**
     * ---------------------------------------------------购物车---------------------------------------------------*
     */
    //购物车商品列表页面
    public function cart()
    {
        $countObj = new CountSum();
        $result   = $countObj->cart_count();
        if(is_string($result))
        {
//            IError::show($result,403);
            $this->log->addError('$result变量错误');
        }
        $query = new IQuery("promotion");
        $query->where = "type = 0 and seller_id = 0 and award_type = 6";
        $result['condition_price'] = $query->find()[0]['condition'];
        foreach ($result['goodsList'] as $key=>$value){
            $result['goodsList'][$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$result['goodsList'][$key]['img']."/w/120/h/120");
        }
        //配送方式
        $result['delivery'] = Api::run('getDeliveryList');
        $this->json_echo($result);
    }
    
    

    /**
     * 购物车结算页面
     */
    public function cart2()
    {
        $id        = IFilter::act(IReq::get('id'),'int');
        $type      = IFilter::act(IReq::get('type'));//goods,product
        $promo     = IFilter::act(IReq::get('promo'));
        $active_id = IFilter::act(IReq::get('active_id'),'int');
        $buy_num   = IReq::get('num') ? IFilter::act(IReq::get('num'),'int') : 1;
        $tourist   = IReq::get('tourist');//游客方式购物
        $code 	   = IFilter::act(IReq::get('code'),'int');
        
        /* 优惠券 */
        if(!empty($code)){
        	if($code<=0 || $code>999999) $this->json_echo(array('error'=>'请输入正确的折扣券号'));
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
        
        //必须为登录用户
        if($tourist === null && $this->user['user_id'] == null)
        {
            if($id == 0 || $type == '')
            {
//                $this->redirect('/simple/login?tourist&callback=/simple/cart2');
                $this->log->addError('必须为登录用户');
            }
            else
            {
                $url  = '/simple/login?tourist&callback=/simple/cart2/id/'.$id.'/type/'.$type.'/num/'.$buy_num;
                $url .= $promo     ? '/promo/'.$promo         : '';
                $url .= $active_id ? '/active_id/'.$active_id : '';
//                $this->redirect($url);
                $this->log->addError('跳转URL'.$url);
            }
        }

        //游客的user_id默认为0
        $user_id = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];

        //计算商品
        $countSumObj 		= new CountSum($user_id);
        $result 			= $countSumObj->cart_count($id,$type,$buy_num,$promo,$active_id);

        if($countSumObj->error)
        {
//            IError::show(403,$countSumObj->error);
            $this->log->addError($countSumObj->error);
        }

        //获取收货地址
        $addressObj  		= new IModel('address');
        $addressList 		= $addressObj->query('user_id = '.$user_id,"*","is_default desc");

        //更新$addressList数据
        foreach($addressList as $key => $val)
        {
            $temp 			= area::name($val['province'],$val['city'],$val['area']);

            $temp_k 		= array_keys($temp);
            if(isset($temp[$val['province']]) && isset($temp[$val['city']]) && isset($temp[$val['area']]))
            {
                $addressList[$key]['province_val'] = in_array($val['province'],$temp_k) ? $temp[$val['province']] : '';
                $addressList[$key]['city_val'] = in_array($val['city'],$temp_k) ? $temp[$val['city']] : '';
                $addressList[$key]['area_val'] = in_array($val['area'],$temp_k) ? $temp[$val['area']] : '';
            }
        }

        //获取习惯方式
        $memberObj 			= new IModel('member');
        $memberRow 			= $memberObj->getObj('user_id = '.$user_id,'custom');
        if(isset($memberRow['custom']) && $memberRow['custom'])
        {
            $this->custom 	= unserialize($memberRow['custom']);
        }
        else
        {
            $this->custom 	= array(
                'payment'  	=> '',
                'delivery' 	=> '',
            );
        }

        //返回值
        $data['gid'] 			= $id;
        $data['type'] 			= $type;
        $data['num'] 			= $buy_num;
        $data['promo'] 			= $promo;
        $data['active_id'] 		= $active_id;
        $data['final_sum'] 		= $result['final_sum'];
        $data['promotion'] 		= $result['promotion'];
        $data['proReduce'] 		= $result['proReduce'];
        $data['sum'] 			= $result['sum'];
        $data['goodsList'] 		= $result['goodsList'];
        $data['count'] 			= $result['count'];
        $data['reduce'] 		= $result['reduce'];
        $data['weight'] 		= $result['weight'];
        $data['freeFreight'] 	= $result['freeFreight'];
        $data['seller'] 		= $result['seller'];
        $data['addressList'] 	= $addressList;
        $data['goodsTax'] 		= $result['tax'];

        //配送方式
        $data['delivery'] 		= Api::run('getDeliveryList');
        //付款方式
        $data['payment'] 		= Api::run('getPaymentList');
        foreach ($data['payment'] as $key=>$value){
            $data['payment'][$key]['paymentprice'] = CountSum::getGoodsPaymentPrice($value['id'],$data['sum']);
        }
        //商品展示
        foreach ($data['goodsList'] as $key => $value){
            if(isset($value['spec_array'])) $data['goodsList'][$key]['spec_array'] = Block::show_spec($value['spec_array']);
            if($data['goodsList'][$key]['img']) $data['goodsList'][$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$data['goodsList'][$key]['img']."/w/500/h/500");
        }
        
		/* 使用优惠券 */
        if(!empty($code)){
	        switch($ticket_data[0]['type']){
	        	//折扣券
	        	case 1 :
	        		$data['sum'] 		= $data['sum'] * $ticket_data[0]['ratio'];
	        		$data['final_sum'] 	= $data['sum'];
	        		$msg 				= '已为您优惠'.($ticket_data[0]['ratio']*10).'折';
	        		break;
	        		//抵扣券
	        	case 2 :
	        		$data['sum'] 		= $data['sum'] - $ticket_data[0]['money'];
	        		$data['final_sum'] 	= $data['sum'];
	        		$msg 				= '已为您优惠'.$ticket_data[0]['money'].'元';
	        		break;
	        }
        }
        
        /* 计算邮费 */
        //满包邮
        $promotion_query 		= new IQuery("promotion");
        $promotion_query->where = "type = 0 and seller_id = 0 and award_type = 6";
        $condition_price 		= $promotion_query->find()[0]['condition'];
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

        //满减规则
        $query = new IQuery("promotion");
        $query->where = "type = 0 and seller_id = 0 and award_type = 6";
        $data['condition_price'] = $query->find()[0]['condition'];
        
        //优惠券
        $data['kicket'] 		= array(
        	'id' 		=> empty($ticket_data[0]['id']) ? '' : $ticket_data[0]['id'], 		//折扣券ID
        	'name' 		=> empty($ticket_data[0]['name']) ? '' : $ticket_data[0]['name'], 	//折扣券名称
        	'msg' 		=> empty($msg) ? '' : $msg,
        	);
        $this->json_echo($data);
    }
    /**
     * ---------------------------------------------------折扣券---------------------------------------------------*
     */
    //折扣券详情 TODO:暂不需要
    public function get_ticket_discount(){
    	$code          				= IFilter::act(IReq::get('code'),'int');//折扣券code
    }
    
    /**
     * ---------------------------------------------------购物车-收货地址---------------------------------------------------*
     */
    /**
     * 获取用户收货地址数据
     */
    public function address_list()
    {
        //游客的user_id默认为0
        $user_id = ($this->user['user_id'] == null) ? 0 : $this->user['user_id'];
        //获取收货地址
        $addressObj  = new IModel('address');
        $addressList = $addressObj->query('user_id = '.$user_id,"*","is_default desc");
        foreach ($addressList as $key => $data){
            $temp = area::name($data['province'],$data['city'],$data['area']);
            $temp_k = array_keys($temp);
            $addressList[$key]['province_val'] = in_array($data['province'],$temp_k) ? $temp[$data['province']] : '';
            $addressList[$key]['city_val'] = in_array($data['city'],$temp_k) ? $temp[$data['city']] : '';
            $addressList[$key]['area_val'] = in_array($data['area'],$temp_k) ? $temp[$data['area']] : '';
        }
        $this->json_echo($addressList);
    }
    //添加和编辑地址
    function address_add()
    {
        $id          = IFilter::act(IReq::get('id'),'int');
        $accept_name = IFilter::act(IReq::get('accept_name'));
        $province    = IFilter::act(IReq::get('province'),'int');
        $city        = IFilter::act(IReq::get('city'),'int');
        $area        = IFilter::act(IReq::get('area'),'int');
        $address     = IFilter::act(IReq::get('address'));
        $zip         = IFilter::act(IReq::get('zip'));
//        $telphone    = IFilter::act(IReq::get('telphone'));
        $mobile      = IFilter::act(IReq::get('mobile'));
        $user_id     = $this->user['user_id'];


        //编辑默认地址
        $is_default = IFilter::act(IReq::get('is_default'),'int');
        if (isset($is_default) && !empty($is_default)){
            if (empty($this->user['user_id'])){
                header("Content-type: application/json");
                echo json_encode(array('msg'=>$this->user['user_id'] . '用户未登录'));
                exit();
            }

            $model = new IModel('address');
            $model->setData(array('is_default' => 0));
            $model->update("user_id = ".$this->user['user_id']);
            $model->setData(array('is_default' => '1'));
            $model->update("id = ".$id." and user_id = ".$this->user['user_id']);
            die(JSON::encode( array('ret'=> true) ));
        }


        //整合的数据
        $sqlData = array(
            'user_id'     => $user_id,
            'accept_name' => $accept_name,
            'zip'         => $zip,
//            'telphone'    => $telphone,
            'province'    => $province,
            'city'        => $city,
            'area'        => $area,
            'address'     => $address,
            'mobile'      => $mobile,
        );

        $checkArray = $sqlData;
        unset($checkArray['zip'],$checkArray['user_id'],$checkArray['area']);
//        unset($checkArray['telphone'],$checkArray['zip'],$checkArray['user_id']);
        foreach($checkArray as $key => $val)
        {
            if(!$val)
            {
                $result = array('result' => false,'msg' => '请仔细填写收货地址');
                die(JSON::encode($result));
            }
        }

        if($user_id)
        {
            $model = new IModel('address');
            if($id)
            {
                $model->setData($sqlData);
                $model->update("id = ".$id." and user_id = ".$user_id);
            }
            else
            {
                $model->setData(array('is_default' => 0));
                $model->update("user_id = ".$this->user['user_id']);
                $sqlData['is_default'] = 1;
                $model->setData($sqlData);
                $id = $model->add();
            }
            $sqlData['id'] = $id;
        }
        //访客地址保存
        else
        {
//            ISafe::set("address",$sqlData);
        }

        $areaList = area::name($province,$city,$area);
        $areaList_k = array_keys($areaList);
        $sqlData['province_val'] = in_array($province,$areaList_k) ? $areaList[$province] : '';
        $sqlData['city_val']     = in_array($city,$areaList_k) ? $areaList[$city] : '';
        $sqlData['area_val']     = in_array($area,$areaList_k) ? $areaList[$area] : '';
        $result = array('data' => $sqlData);

        $this->json_echo($result);
    }
    /**
     * @brief 收货地址删除处理
     */
    public function address_del()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $model = new IModel('address');
        $data = $model->query('id = '.$id.' and user_id = '.$this->user['user_id']);
        if ($data[0]['is_default'] == 1){
            $ret = false;
        } else {
            $model->del('id = '.$id.' and user_id = '.$this->user['user_id']);
            $ret = true;
        }
        $this->json_echo(['ret'=>$ret]);
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
        $model->update("id = ".$id." and user_id = ".$this->user['user_id']);

        $this->json_echo(array('ret'=>true));
    }

    /**
     * ---------------------------------------------------订单---------------------------------------------------*
     */
    /**
     * 获取订单列表
     */
    public function order_list()
    {
        if (IFilter::act(IReq::get('type'),'string') == 'u'){
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
                $temp = explode('or',$temp,2)[1];
            }
        } else {
            $temp = 'user_id = ' . $this->user['user_id'];
        }
        $temp ='(' . $temp . ')';

        $ret0 = Api::run('getOrderList', $temp, 'pay_type != 0 and status != 3 and status != 4'); // 全部订单
        $ret1 = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 1'); // 待支付
        $ret2 = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 2 and distribution_status = 0'); // 待发货
        $ret3 = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 2 and distribution_status in (1,2)'); // 待收货
        $ret4 = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 5 '); // 已完成
        $data['state0'] = $ret0->find();
        $data['state1'] = $ret1->find();
        $data['state2'] = $ret2->find();
        $data['state3'] = $ret3->find();
        $data['state4'] = $ret4->find();
        //支付方式
        $payment = new IQuery('payment');
        $payment->fields = 'id,name,type';
        $payments = $payment->find();
        $items = array();
        foreach($payments as $pay)
        {
            $items[$pay['id']]['name'] = $pay['name'];
            $items[$pay['id']]['type'] = $pay['type'];
        }

        
        $temp = [];
        foreach ($data as $k => $v){
            foreach ($v as $key => $value ){
                $data[$k][$key]['pay_type'] = $items[$value['pay_type']]['name'];
                $data[$k][$key]['orderStatusText'] = Order_Class::orderStatusText(Order_Class::getOrderStatus($value));
                $data[$k][$key]['orderStatusVal'] = Order_Class::getOrderStatus($value);
                $temp2 			= area::name($value['province'],$value['city'],$value['area']);
                $temp_k 		= array_keys($temp);
                $data[$k][$key]['province_val'] 	= in_array($value['province'],$temp_k) ? $temp2[$value['province']] : '';
                $data[$k][$key]['city_val'] 		= in_array($value['city'],$temp_k) ? $temp2[$value['city']] : '';
                $data[$k][$key]['area_val'] 		= in_array($value['area'],$temp_k) ? $temp2[$value['area']] : '';
//                $orderObj = new order_class();
//                $data[$k][$key]['order_info'] = $orderObj->getOrderShow($value['id'],$this->user['user_id']);
                $temp3 = Api::run('getOrderGoodsListByGoodsid',array('#order_id#',$value['id']));
                foreach ($temp3 as $key1 => $value1){
                    $temp3[$key1]['goods_array'] = json_decode($value1['goods_array'],true);
                    $temp3[$key1]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$temp3[$key1]['img']."/w/160/h/160");
                }
                $data[$k][$key]['goodslist'] = $temp3;
                $orderObj = new order_class();
                $data[$k][$key]['order_info'] = $orderObj->getOrderShow($data[$k][$key]['id'],$this->user['user_id']);

//                $data[$k][$key]['goodslist'] = Api::run('getOrderGoodsListByGoodsid',array('#order_id#',$value['id']));
                if (!empty($v)) switch ($k) {

//                case 'state0':
//                    $data[$k][$key]['text'] = '';
                    case 'state1':
                        $data[$k][$key]['text'] = '去支付';
                        break;
                    case 'state2':
                        $data[$k][$key]['text'] = '取消订单';
                        break;
                    case 'state3':
                        $data[$k][$key]['text'] = '查看物流';
                        break;
                    case 'state4':
                        $data[$k][$key]['text'] = '删除订单';
                        break;
                    default:
                        $data[$k][$key]['text'] = 'null';
                }
            }
        }
        $relation 			= array('已完成'=>'删除订单', '等待发货'=>'取消订单', '等待付款'=>'去支付', '已发货' => '查看物流', '已取消'=>'已取消','部分发货'=>'查看物流');
        $relation_k 		= array_keys($relation);
        foreach ($data['state0'] as $key => $value){
            	$data['state0'][$key]['text'] = in_array($value['orderStatusText'],$relation_k) ? $relation[$value['orderStatusText']] : '';
        }
        $this->json_echo($data);
    }
    /**
     * @brief 订单详情
     * @return String
     */
    public function order_detail()
    {
        $id = IFilter::act(IReq::get('id'),'int');

        $orderObj = new order_class();
        $order_info = $orderObj->getOrderShow($id,$this->user['user_id']);

        if(!$order_info)
        {
            IError::show(403,'订单信息不存在');
        }
        $orderStatus = Order_Class::getOrderStatus($order_info);
        if ($orderStatus == 2){$orderStatusT=0;}//待支付
        if ($orderStatus == 4){$orderStatusT=1;}//待发货
        if ($orderStatus == 3 || $orderStatus == 8 || $orderStatus == 11 ){$orderStatusT=2;}//待收货
        if ($orderStatus == 6){$orderStatusT=3;}//待发货

        $order_goods = Api::run('getOrderGoodsListByGoodsid',array('#order_id#',$order_info['id']));
        foreach ($order_goods as $key => $value){
            $order_goods[$key]['goods_array'] = json_decode($value['goods_array'],true);
            $order_goods[$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$order_goods[$key]['img']."/w/160/h/160");
        }
        $data = array('order_info'=>$order_info, 'orderStatus'=>$orderStatusT,"order_step"=>Order_Class::orderStep($order_info), "order_goods"=>$order_goods);

        $this->json_echo($data);
    }

    /**
     * ---------------------------------------------------物流---------------------------------------------------*
     */
    /**
     * ---------------------------------------------------商品---------------------------------------------------*
     */
    //限时购
    public function pro_speed_list(){
        $query = new IQuery("promotion as p");
        $query->join = "left join goods as go on p.condition = go.id";
        $query->fields = "date_format(p.end_time,'%Y%m%d%H%i%s') as end_time,go.is_del,p.name";
        $query->where = "p.type = 1 and p.seller_id = 0 and p.start_time > NOW() group by p.name order by start_time";
        $query->limit = 1;
        $items = $query->find();
        $query2 = new IQuery("promotion as p");
        $query2->join = "left join goods as go on p.condition = go.id";
        $query2->fields = "go.id as goods_id,go.is_del,p.name as pname,p.award_value, go.name,go.sell_price,go.img";
        $query2->where = sprintf("p.type = 1 and p.seller_id = 0 and p.name = '%s'", $items[0]['name']);
        $query2->limit = 6;
        $items2 = $query2->find();
        foreach ($items2 as $key=>$value){
            $items2[$key]['img_thumb'] = IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/230/h/230");
        }
        $items[0]['child'] = $items2;
        $this->json_echo($items[0]);
    }
    //商品展示
    function products_details()
    {
        $goods_id = IFilter::act(IReq::get('id'),'int');

        if(!$goods_id)
        {
            IError::show(403,"传递的参数不正确");
            exit;
        }

        //使用商品id获得商品信息
        $tb_goods = new IModel('goods');
        $goods_info = $tb_goods->getObj('id='.$goods_id." AND is_del=0");
        if(!$goods_info)
        {
            IError::show(403,"这件商品不存在");
            exit;
        }

        //品牌名称
        if($goods_info['brand_id'])
        {
            $tb_brand = new IModel('brand');
            $brand_info = $tb_brand->getObj('id='.$goods_info['brand_id']);
            if($brand_info)
            {
                $goods_info['brand'] = $brand_info['name'];
            }
        }

        //获取商品分类
        $categoryObj = new IModel('category_extend as ca,category as c');
        $categoryList= $categoryObj->query('ca.goods_id = '.$goods_id.' and ca.category_id = c.id','c.id,c.name','ca.id desc',1);
        $categoryRow = null;
        if($categoryList)
        {
            $categoryRow = current($categoryList);
        }
        $goods_info['category'] = $categoryRow ? $categoryRow['id'] : 0;

        //商品图片
        $tb_goods_photo = new IQuery('goods_photo_relation as g');
        $tb_goods_photo->fields = 'p.id AS photo_id,p.img ';
        $tb_goods_photo->join = 'left join goods_photo as p on p.id=g.photo_id ';
        $tb_goods_photo->where =' g.goods_id='.$goods_id;
        $goods_info['photo'] = $tb_goods_photo->find();

        foreach ($goods_info['photo'] as $key => $value){
            $goods_info['photo'][$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/600/h/600");
        }
        $goods_info['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$goods_info['img']."/w/500/h/500");

        //商品是否参加促销活动(团购，抢购)
        $goods_info['promo']     = IReq::get('promo')     ? IReq::get('promo') : '';
        $goods_info['active_id'] = IReq::get('active_id') ? IFilter::act(IReq::get('active_id'),'int') : 0;
        if($goods_info['promo'])
        {
            $activeObj    = new Active($goods_info['promo'],$goods_info['active_id'],$this->user['user_id'],$goods_id);
            $activeResult = $activeObj->data();
            if(is_string($activeResult))
            {
                IError::show(403,$activeResult);
            }
            else
            {
                $goods_info[$goods_info['promo']] = $activeResult;
            }
        }

        //获得扩展属性
        $tb_attribute_goods = new IQuery('goods_attribute as g');
        $tb_attribute_goods->join  = 'left join attribute as a on a.id=g.attribute_id ';
        $tb_attribute_goods->fields=' a.name,g.attribute_value ';
        $tb_attribute_goods->where = "goods_id='".$goods_id."' and attribute_id!=''";
        $goods_info['attribute'] = $tb_attribute_goods->find();

        //购买记录
        $tb_shop = new IQuery('order_goods as og');
        $tb_shop->join = 'left join order as o on o.id=og.order_id';
        $tb_shop->fields = 'count(*) as totalNum';
        $tb_shop->where = 'og.goods_id='.$goods_id.' and o.status = 5';
        $shop_info = $tb_shop->find();
        $goods_info['buy_num'] = 0;
        if($shop_info)
        {
            $goods_info['buy_num'] = $shop_info[0]['totalNum'];
        }

        //购买前咨询
        $tb_refer    = new IModel('refer');
        $refeer_info = $tb_refer->getObj('goods_id='.$goods_id,'count(*) as totalNum');
        $goods_info['refer'] = 0;
        if($refeer_info)
        {
            $goods_info['refer'] = $refeer_info['totalNum'];
        }

        //网友讨论
        $tb_discussion = new IModel('discussion');
        $discussion_info = $tb_discussion->getObj('goods_id='.$goods_id,'count(*) as totalNum');
        $goods_info['discussion'] = 0;
        if($discussion_info)
        {
            $goods_info['discussion'] = $discussion_info['totalNum'];
        }

        //获得商品的价格区间
        $tb_product = new IModel('products');
        $product_info = $tb_product->getObj('goods_id='.$goods_id,'max(sell_price) as maxSellPrice ,max(market_price) as maxMarketPrice');
        if(isset($product_info['maxSellPrice']) && $product_info['maxSellPrice'])
        {
            $goods_info['sell_price']   .= "-".$product_info['maxSellPrice'];
            $goods_info['market_price'] .= "-".$product_info['maxMarketPrice'];
        }

        //获得会员价
        $countsumInstance = new countsum();
        $goods_info['group_price'] = $countsumInstance->getGroupPrice($goods_id,'goods');

        //获取商家信息
        if($goods_info['seller_id'])
        {
            $sellerDB = new IModel('seller');
            $goods_info['seller'] = $sellerDB->getObj('id = '.$goods_info['seller_id']);
        }

        //增加浏览次数
        $visit    = ISafe::get('visit');
        $checkStr = "#".$goods_id."#";
        if($visit && strpos($visit,$checkStr) !== false)
        {
        }
        else
        {
            $tb_goods->setData(array('visit' => 'visit + 1'));
            $tb_goods->update('id = '.$goods_id,'visit');
            $visit = $visit === null ? $checkStr : $visit.$checkStr;
            ISafe::set('visit',$visit);
        }


        //评论
        $commentDB = new IQuery('comment as c');
        $commentDB->join   = 'left join goods as go on c.goods_id = go.id AND go.is_del = 0 left join user as u on u.id = c.user_id';
        $commentDB->fields = 'u.head_ico,u.username,c.*';
        $commentDB->where  = 'c.goods_id = '.$goods_id.' and c.status = 1';
        $commentDB->order  = 'c.id desc';
//        $commentDB->page   = $page;
        $goods_info['comments_data']     = $commentDB->find();

        $goods_info['spec_array'] = json_decode($goods_info['spec_array']);
//        $this->setRenderData($goods_info);
            $favorite = new IQuery('favorite');
            $favorite->where = 'user_id='.$this->user['user_id'].' and rid='.$goods_info['id'];
            $fdata = $favorite->find();
            if (!empty($fdata)){
                $goods_info['is_favorite'] = 1;
            } else {
                $goods_info['is_favorite'] = 0;
            }
            

        $this->json_echo($goods_info);
    }
    /**
     * 商品的相关专辑
     */
    public function products_details_article(){
		/* 获取参数 */
    	$goods_id = IFilter::act(IReq::get('id'),'int');
    	if(empty($goods_id)) IError::show(403,"传递的参数不正确");
    	
    	/* 相关专辑 */
    	$query 				= new IQuery('article as m');
    	$query->join 		= 'left join relation as r on r.article_id = m.id';
    	$query->where 		= 'm.top=0 and m.visibility=1 and r.goods_id='.$goods_id;
    	$query->order 		= 'm.sort desc';
    	$query->fields 		= 'm.id,m.title,m.image';
    	$query->limit 		= 10;
    	$list 				= $query->find();
    	if(!empty($list)){
    		foreach($list as $k => $v){
    			$list[$k]['image'] 	= IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/250/h/127");
    		}
    	}
    	$this->json_echo($list);
    }
    //商品详情的补充信息内容
    public function products_details_other(){
        $goods_id = IFilter::act(IReq::get('id'),'int');
        //商品关联到的专辑
        $goods_query = new IModel('goods');
        $goods_data = $goods_query->getObj('id = ' . $goods_id);
        $relation_query = new IQuery('relation as a');
        $relation_query->join = "right join article as b on a.article_id = b.id and b.category_id = 3";
        $relation_query->fields = "a.goods_id,b.id,b.title,b.image";
        $relation_query->where = "a.goods_id = " . $goods_id;
        $article_data = $relation_query->find();
        //品牌下的商品
        $brands_query = new IQuery('brand as a');
        $brands_query->join = "right join goods as b on a.id = b.brand_id";
        $brands_query->fields = "b.id,b.name,b.img,b.sell_price,b.brand_id";
        $brands_query->where = "b.brand_id = " . $goods_data['brand_id'];
        $brands_query->limit = 6;
        $brand_good_data = $brands_query->find();
        foreach ($brand_good_data as $key => $value){
            $brand_good_data[$key]['img_thumb'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/180/h/180");
        }
        //某品牌下商品数量
        $brands_query->join = "right join goods as b on a.id = b.brand_id";
        $brands_query->fields = "count(*) as nums";
        $brands_query->where = "b.brand_id = " . $goods_data['brand_id'];
        $nums = $brands_query->find()[0]['nums'];

        $brand_model = new IModel('brand');
        $brand_data = $brand_model->getObj('id = ' . $goods_data['brand_id']);
        $brand_data['nums'] = $nums;
        $data = array('article_data'=>$article_data,'brand_good_data'=>$brand_good_data,"brand_data" => $brand_data);
        $this->json_echo($data);
    }
    function tag_goods(){
        $keyword_id = IFilter::act(IReq::get('id'),'int');
        if (empty($keyword_id)){$this->json_echo([]);}
        $keyword = new IQuery('keyword');
        $keyword->where = 'id = ' . $keyword_id;
        $word = $keyword->find()[0]['word'];
        $goods_query = new IQuery('goods');
        $goods_query->where = 'search_words like ' . '"%,' . $word . ',%"';
        $goods_query->order = 'create_time desc';
        $data['new'] = $goods_query->find();

        $commend_goods = new IQuery('commend_goods as co');
        $commend_goods->join = 'left join goods as go on co.goods_id = go.id';
        $commend_goods->where = 'co.commend_id = 3 and go.is_del = 0 AND go.id is not null'.' and go.search_words like ' . '"%,' . $word . ',%"';
        $commend_goods ->fields = 'go.img,go.sell_price,go.name,go.id,go.market_price';
        $commend_goods->limit=3;
        $commend_goods->order='sort asc';
        $data['hot'] = $commend_goods->find();

        $this->json_echo($data);
    }


    /**
     * ---------------------------------------------------专辑---------------------------------------------------*
     */
    //显示专辑列表（首页）
    public function article_list(){
    	/* 获取参数 */
    	$cid 				= IFilter::act(IReq::get('cid'), 'int'); 	//专辑分类ID，选填
    	$page 				= IFilter::act(IReq::get('page'),'int'); 	//当前页码，选填
    	/* 获取数据 */
    	$query 				= new IQuery('article as m');
    	$query->join 		= 'left join article_category as c on c.id=m.category_id';
    	$query->where 		= 'm.top=0 and m.visibility=1 '.(empty($cid) ? '' : ' and m.category_id='.$cid);
    	$query->fields 		= 'm.id,m.title,m.image,m.visit_num,m.category_id,c.icon,c.name as category_name';
    	$query->order 		= 'm.sort asc';
    	$query->page 		= $page>1 ? $page : 1;
    	$query->pagesize 	= 5;
    	$list 				= $query->find();
    	$total_page 		= $query->getTotalPage();
    	if ($page > $total_page) $list = array();
    	if(!empty($list)){
    		//商品列表模型
    		$query_goods 				= new IQuery('goods as m');
    		$query_goods->join 			= 'left join relation as r on r.goods_id=m.id';
    		$query_goods->fields 		= 'm.id,m.name,m.sell_price,m.img';
    		$query_goods->order 		= 'm.sort asc';
    		$query_goods->limit 		= 5;
    		//商品统计模型
    		$query_goods_count 			= new IQuery('goods as m');
    		$query_goods_count->join 	= 'left join relation as r on r.goods_id=m.id';
    		$query_goods_count->fields 	= 'count(m.id) as num';
    		//专辑收藏模型
    		$query_favorite 			= new IQuery('favorite_article');
    		$query_favorite->fields 	= 'count(id) as num';
    		//收藏人数
    		foreach($list as $k => $v){
    			$list[$k]['icon'] 		= empty($v['icon']) ? '' : IWeb::$app->config['image_host'].'/'.$v['icon'];
    			$list[$k]['image'] 		= empty($v['image']) ? '' : IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/750/h/380");
    			//收藏人数
    			$query_favorite->where 	= 'aid='.$v['id'];
    			$count 					= $query_favorite->find();
    			$list[$k]['favorite_num']   = $count[0]['num'];
    			//当前用户是否已收藏
    			if(!empty( $this->user['user_id'] )){
    				$query_favorite->where 	= 'aid='.$v['id'].' and user_id='.$this->user['user_id'];
    				$count 					= $query_favorite->find();
    				$count 					= $count[0]['num'];
    			}else{
    				$count 					= 0;
    			}
    			$list[$k]['is_favorite']  	= $count;
    			//相关商品数量
    			$query_goods_count->where 	= 'm.is_del=0 and r.article_id='.$v['id'];
    			$count 						= $query_goods_count->find();
    			$list[$k]['goods_num'] 		= $count[0]['num'];
    			//相关商品列表
    			$query_goods->where 	= 'm.is_del=0 and r.article_id='.$v['id'];
    			$list[$k]['list'] 		= $query_goods->find();
    			if(!empty($list[$k]['list'])){
    				foreach ($list[$k]['list'] as $k1 => $v1){
    					$list[$k]['list'][$k1]['img'] 	= empty($v1['img']) ? '' : IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/180/h/180");
    				}
    			}
    		}
    	}
    	$this->json_echo($list);
    }
    /**
     * 专辑列表
     */
    public function article_lists(){
    	/* 获取参数 */
    	$cid 				= IFilter::act(IReq::get('cid'), 'int'); 	//专辑分类ID，选填
    	$page 				= IFilter::act(IReq::get('page'),'int'); 	//当前页码，选填
    	/* 获取数据 */
    	$query 				= new IQuery('article as m');
    	$query->where 		= 'top=0 and visibility=1 '.(empty($cid) ? '' : ' and m.category_id='.$cid);
    	$query->fields 		= 'm.id,m.title,m.image,m.visit_num';
    	$query->order 		= 'm.sort asc';
    	$query->page 		= $page>1 ? $page : 1;
    	$query->pagesize 	= 10;
    	$list 				= $query->find();
    	$total_page 		= $query->getTotalPage();
    	if ($page > $total_page) $list = array();
    	if(!empty($list)){
    		//商品列表模型
    		$query_goods 				= new IQuery('goods as m');
    		$query_goods->join 			= 'left join relation as r on r.goods_id=m.id';
    		$query_goods->fields 		= 'm.id,m.name,m.sell_price,m.img';
    		$query_goods->order 		= 'm.sort asc';
    		$query_goods->limit 		= 5;
    		//商品统计模型
    		$query_goods_count 			= new IQuery('goods as m');
    		$query_goods_count->join 	= 'left join relation as r on r.goods_id=m.id';
    		$query_goods_count->fields 	= 'count(m.id) as num';
    		foreach($list as $k => $v){
    			$list[$k]['image'] 		= IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/750/h/380");
    			//相关商品数量
    			$query_goods_count->where 	= 'm.is_del=0 and r.article_id='.$v['id'];
    			$count 						= $query_goods_count->find();
    			$list[$k]['goods_num'] 		= $count[0]['num'];
    			//相关商品列表
    			$query_goods->where 	= 'm.is_del=0 and r.article_id='.$v['id'];
    			$list[$k]['list'] 		= $query_goods->find();
    			if(!empty($list[$k]['list'])){
    				foreach ($list[$k]['list'] as $k1 => $v1){
    					$list[$k]['list'][$k1]['img'] 	= IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/180/h/180");
    				}
    			}
    		}
    	}
    	$this->json_echo($list);
    }
    /**
     * 专辑分类列表
     */
    public function article_category_list(){
    	/* 首页展示的专辑分类 */
    	$query_ac 					= new IQuery('article_category');
    	$query_ac->where 			= 'id in (11,12,15,16,18,19)';
    	//11喵酱推荐/12杂志揭载/15药妆特供/16健康推荐/18居家个护/19吃喝宅乐/
    	$query_ac->fields 			= 'id,name';
    	$query_ac->limit 			= 6;
    	$list_ac 					= $query_ac->find();
    	if(!empty($list_ac)){
    		foreach($list_ac as $k => $v){
    			$list_ac[$k]['image'] = IWeb::$app->config['image_host'] . '/upload/category/article_img/'.$v['id'].'.png';
    		}
    	}
    	/* 特别推荐专辑 */
    	$query_ar 					= new IQuery('article');
    	$query_ar->where 			= 'top=1 and visibility=1';
    	$query_ar->order 			= 'sort desc';
    	$query_ar->limit 			= 3;
    	$query_ar->fields 			= 'id,title,image';
    	$list_ar 					= $query_ar->find();
    	if(!empty($list_ar)){
    		foreach($list_ar as $k => $v){
    			$list_ar[$k]['image'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/738/h/353");
    		}
    	}
    	/* 返回数据 */
		$data 						= array('ac'=>$list_ac,'ar'=>$list_ar);
    	$this->json_echo($data);
    }
    //通过专辑获取相关商品
    public function article_rel_goods()
    {
        $article_id = IFilter::act(IReq::get('id'),'int');
        $article = new IQuery('relation as r');
        $article->join = 'left join goods as go on r.goods_id = go.id';
        $article->where = sprintf('go.is_del = 0 and r.article_id = %s and go.id is not null', $article_id);
        $article->filds = 'go.goods_no as goods_no,go.id as goods_id,go.img,go.name,go.sell_price';
        $article->page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $article->pagesize = 1000;
        $relationList = $article->find();
        $total_page = $article->getTotalPage();
        if ($article->page > $total_page){
            $relationList = [];
        }
        foreach($relationList as $key => $value){
            $relationList[$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/350/h/350");
        }
        $this->json_echo($relationList);
    }
    //专辑添加收藏夹
    function favorite_article_add()
    {
        $article_id = IFilter::act(IReq::get('id'),'int');
        $message  = '';

        if($article_id == 0)
        {
            $message = '文章id值不能为空';
        }
        else if(!isset($this->user['user_id']) || !$this->user['user_id'])
        {
            $message = '请先登录';
        }
        else
        {
            $favoriteObj = new IModel('favorite_article');
            $articleRow    = $favoriteObj->getObj('user_id = '.$this->user['user_id'].' and aid = '.$article_id);
            if($articleRow)
            {
//                $message = '您已经收藏过此件专辑';
                $favoriteObj->del('user_id = '.$this->user['user_id'].' and aid = '.$article_id);
            }
            else
            {
                $catObj = new IModel('article');
                $catRow = $catObj->getObj('id = '.$article_id);
                $cat_id = $catRow ? $catRow['category_id'] : 0;
                $dataArray   = array(
                    'user_id' => $this->user['user_id'],
                    'aid'     => $article_id,
                    'time'    => ITime::getDateTime(),
                    'cat_id'  => $cat_id,
                );
                $favoriteObj->setData($dataArray);
                $favoriteObj->add();
                $message = '收藏成功';

                //商品收藏信息更新
                $articleDB = new IModel('article');
                $articleDB->setData(array("favorite" => "favorite + 1"));
                $articleDB->update("id = ".$article_id,'favorite');
            }
        }
        $result = array(
            'isError' => true,
            'message' => $message,
        );

        $this->json_echo($result);
    }
    
    /**
     * ---------------------------------------------------分类---------------------------------------------------*
     */

    /**
     *一级分类的数据信息
     */
    public function category_top()
    {
    	//一级分类
        $data 							= Api::run('getCategoryListTop');

        foreach ($data as $key => $value){
			//banner图
            if (!empty($value['banner_image'])){
                $data[$key]['banner_image'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['banner_image']."/w/520/h/154");
            }
            //icon
            if (!empty($value['image'])){
                $temp 					= explode(',',$value['image']);
                $data[$key]['image'] 	= empty($temp[0]) ? '' : IWeb::$app->config['image_host'].'/'.$temp[0];
                $data[$key]['image'] 	.= empty($temp[1]) ? '' : ','.IWeb::$app->config['image_host'].'/'.$temp[1];
            }
           	//二级子分类
            $data[$key]['child'] 		= [];
            $second = Api::run('getCategoryByParentid',array('#parent_id#',$value['id']));
            if(!empty($second)) foreach ($second as $k=>$v){
                if (!empty($v['banner_image'])){
                    $second[$k]['banner_image'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['banner_image']."/w/154/h/154");
                }
                if (!empty($v['image'])){
//                    $temp = explode(',',$v['image']);
                    $second[$k]['image'] = IWeb::$app->config['image_host'] . '/' . $v['image'];
                }
            }
            $data[$key]['child'] = $second;
        }
        $this->json_echo($data);
    }

    /**
     * 获取其子类数据信息
     */
    public function category_child()
    {
        $catId = IFilter::act(IReq::get('id'),'int');//分类id
        if($catId == 0){$this->json_echo([]);}
        $goodsObj = search_goods::find(array('category_extend' => goods_class::catChild($catId)),99999);
        //获取汇率
        $siteConfig 		= new Config('site_config');
        $exchange_rate_jp 	= $siteConfig->exchange_rate_jp;
        $ratio 				= ',go.sell_price*'.$exchange_rate_jp.'/go.jp_price as ratio';
        $goodsObj->fields 	.= $ratio;
        $goodsObj->order 	= 'ratio asc';//根据折扣力度排序
        $resultData = $goodsObj->find();
        foreach ($resultData as $key=>$value){
            $resultData[$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/350/h/350");
        }
        $this->json_echo($resultData);
    }
    /**
     * ---------------------------------------------------品牌---------------------------------------------------*
     */
    /**
     * 返回品牌信息
     */
    public function brand_list()
    {
        $data = Api::run('getBrandList');
        foreach ($data as $key=>$value){
            if (!empty($value['logo'])){
                $data[$key]['logo'] = IWeb::$app->config['image_host'] . '/' . $value['logo'];
            }
        }
        $this->json_echo($data);
    }
    
    /**
     * 品牌详情
     */
    public function brand(){
    	/* 接收参数 */
    	$brand_id 					= IFilter::act(IReq::get('id'),'int');
    	$page 						= IFilter::act(IReq::get('page'),'int');
    	 
    	/* 品牌详情 */
    	$queryBrand 				= new IQuery('brand');
    	$queryBrand->where 			= 'id='.$brand_id;
    	$queryBrand->fields 		= 'id,name,logo,description,banner';
    	$queryBrand->limit 			= 1;
    	$data 						= $queryBrand->find();
    	if(empty($data)) $this->json_echo(array('error'=>'品牌不存在'));
    	$data 						= $data[0];
    	$data['logo'] 				= empty($data['logo']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$data['logo'].'/w/160/h/102');
    	$data['banner'] 			= empty($data['banner']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$data['banner'].'/w/750/h/376');
    	
    	/* 相关商品 */
    	$queryGoods 				= new IQuery('goods');
    	$queryGoods->where 			= 'is_del=0 AND brand_id='.$brand_id;
    	$queryGoods->fields 		= 'id,name,img,content,sell_price,jp_price';
    	$queryGoods->page 			= $page<1 ? 1 : $page;
    	$queryGoods->pagesize 		= 10;
    	$queryGoods->order 			= 'sort asc';
    	$dataGoods 					= $queryGoods->find();
    	$total_page 				= $queryGoods->getTotalPage();
    	if ($page > $total_page) $dataGoods = array();
    	if(!empty($dataGoods)){
    		foreach($dataGoods as $k => $v){
    			$dataGoods[$k]['img'] 			= empty($v['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$v['img'].'/w/220/h/220');
    			$dataGoods[$k]['description'] 	= empty($v['content']) ? '' :  mb_substr( trim(strip_tags( str_ireplace('&nbsp;','',htmlspecialchars_decode($v['content']) ) )), 0, 30,'utf-8');
    			unset($dataGoods[$k]['content']);
    		}
    	}
    	//相关商品数量
    	$queryGoodsSum 				= new IQuery('goods');
    	$queryGoodsSum->fields 		= 'count(`id`) as sum';
    	$queryGoodsSum->where 		= 'is_del=0 AND brand_id='.$brand_id;
    	$dataGoodsSum 				= $queryGoodsSum->find();
    	
    	/* 相关专辑 */
    	$queryArticle 				= new IQuery('article AS m');
    	$queryArticle->join 		= 'LEFT JOIN relation AS r ON r.article_id=m.id LEFT JOIN goods AS g ON g.id=r.goods_id';
    	$queryArticle->where 		= 'g.is_del=0 AND m.visibility=1 AND g.brand_id='.$brand_id;
    	$queryArticle->fields 		= 'm.id,m.title,m.visit_num,m.image';
    	$queryArticle->limit 	 	= 5;
    	$queryArticle->order 		= 'm.top desc,m.sort desc';
    	$dataArticle 				= $queryArticle->find();
    	if(!empty($dataArticle)){
    		foreach($dataArticle as $k => $v){
    			$dataArticle[$k]['image'] 	= empty($v['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl('/pic/thumb/img/'.$v['image'].'/w/750/h/380');;
    		}
    	}
    	//相关专辑数量
    	$queryArticleSum 			= new IQuery('article AS m');
    	$queryArticleSum->join 		= 'LEFT JOIN relation AS r ON r.article_id=m.id LEFT JOIN goods AS g ON g.id=r.goods_id';
    	$queryArticleSum->where 	= 'g.is_del=0 AND m.visibility=1 AND g.brand_id='.$brand_id;
    	$queryArticleSum->fields 	= 'count(*) as sum';
    	$dataArticleSum 			= $queryArticleSum->find();
    	
    	/* 返回参数 */
    	$data['goods_sum'] 			= $dataGoodsSum[0]['sum'];
    	$data['article_sum'] 		= $dataArticleSum[0]['sum'];
    	$data['goods_list'] 		= $dataGoods;
    	$data['article_list'] 		= $dataArticle;
        $this->json_echo($data);
    }
    /**
     * ---------------------------------------------------搜索---------------------------------------------------*
     */
    /* 热门关键词 */
    public function search_words(){
    	/* 热门关键词 */
    	$query_keyword 				= new IQuery('keyword');
    	$query_keyword->order 		= 'hot desc,num desc';
    	$query_keyword->fields 		= 'word';
    	$query_keyword->limit 		= 20;
    	$data_keyword 				= $query_keyword->find();
    	
    	$this->json_echo($data_keyword);
    }
	/* 开始搜索 */
    public function search(){
    	/* 接收参数 */
        $word 						= IFilter::act(IReq::get('word'),'string');
        $page 						= IFilter::act(IReq::get('page'),'int');
        if(empty($word)) $this->json_echo(array());
        //关键字处理
        $word_str 					= str_replace(' ',',',$word);
        $word_arr 					= explode(' ',$word);
        
        /* 商品 */
    	$model_keyword 				= new IModel('keyword');
    	$data_keyword 				= $model_keyword->get_count('word in ("'.$word_str.'")','num');
    	if( $data_keyword > 0 ){
    		//关键字搜索次数+1
    		$model_keyword->setData(array('num'=>'num+1'));
    		$model_keyword->update('word in ("'.$word_str.'")',array('num'));
    	}
    	//搜索商品
    	$query_goods 				= new IQuery('goods');
    	$field 						= 'id,name,sell_price,jp_price,market_price,img';
    	$where 						= 'is_del=0 AND (';
    	$order 						= '';
    	foreach($word_arr as $k => $v){
    		$field 					.= ',(`name` LIKE "%'.$v.'%") as name'.$k.',(`search_words` LIKE "%,'.$v.',%") as search'.$k.',(`goods_no` LIKE "%,'.$v.',%") as goods_no'.$k;
    		$where 					.= ' (`name` LIKE "%'.$v.'%") OR (`search_words` LIKE "%,'.$v.',%") OR (`goods_no` LIKE "%,'.$v.',%")';
    		$order 					.= 'name'.$k;
    		if(count($word_arr) != $k+1){
    			$where .= ' OR';
    			$order .= ' AND ';
    		}
    	}
    	$where 						.= ')';
    	$query_goods->where 		= $where;
    	$query_goods->order 		= '(CASE WHEN ('.$order.') THEN 0 ELSE 1 END) asc';
    	$query_goods->fields 		= $field;
    	$query_goods->page 			= empty($page) ? 1 : $page;
    	$query_goods->pagesize 		= 20;
    	$data_goods 				= $query_goods->find();
    	$total_page 				= $query_goods->getTotalPage();
    	if ($page > $total_page) $data_goods = array();
    	if(!empty($data_goods)){
    		foreach($data_goods as $k => $v){
    			$data_goods[$k]['img'] 		= IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['img']."/w/290/h/290");
    		}
    	}
    	
    	/* 专辑 */
    	$query_article 				= new IQuery('article');
		$where 						= 'visibility=1 AND (';
		$field 						= 'id,title,image';
		foreach($word_arr as $k => $v){
			$field 					.= ',(`title` LIKE "%'.$v.'%") as name'.$k.',(`keywords` LIKE "%,'.$v.',%") as search'.$k;
    		$where 					.= ' (`title` LIKE "%'.$v.'%") OR (`keywords` LIKE "%'.$v.'%")';
			if(count($word_arr) != $k+1) $where .= ' OR';
		}
		$where 						.= ')';
    	$query_article->where 		= $where;
    	$query_article->order 		= 'top desc,sort desc';
    	$query_article->fields 		= $field;
    	$query_article->page 		= empty($page) ? 1 : $page;
    	$query_article->pagesize 	= 20;
    	$data_article  				= $query_article->find();
    	$total_page 				= $query_article->getTotalPage();
    	if ($page > $total_page) $data_article = array();
    	if(!empty($data_article)){
    		foreach($data_article as $k => $v){
    			$data_article[$k]['image'] 	= IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/513/h/260");
    		}
    	}
        $this->json_echo(array('goods'=>$data_goods,'article'=>$data_article));
    }
    /**
     * ---------------------------------------------------幻灯片---------------------------------------------------*
     */
    public function banner_list(){
        $banner = Api::run('getBannerList');
        foreach ($banner as $key=>$value){
            $banner[$key]['img'] = IWeb::$app->config['image_host'] . '/' . $value['img'];
        }
        $goods = new IQuery('goods');
        $goods->fields = 'count(*) as nums';
        $nums = $goods->find()[0]['nums'];

        $this->json_echo(['banner'=>$banner,'goods_nums'=>$nums]);
    }

    /**
     * @return string
     */
    public function info()
    {
        $user_id = $this->user['user_id'];

        $userObj       = new IModel('user');
        $where         = 'id = '.$user_id;
        $userRow = $userObj->getObj($where, array('head_ico','username'));

        $memberObj       = new IModel('member');
        $where           = 'user_id = '.$user_id;
        $memberRow = $memberObj->getObj($where);

        $data = array_merge($userRow, $memberRow);
        $this->json_echo($data);
    }
    
    /**
     * 收藏列表
     */
    function favorite_list(){
    	/* 商品收藏 */
        $favorite_query 				= new IQuery('favorite as a');
        $favorite_query->join 			= 'left join goods as go on go.id = a.rid';
        $favorite_query->fields 		= 'a.*,go.id,go.name,go.sell_price,go.market_price,go.img,go.jp_price';

        $favorite_query->where 			= 'user_id = ' . $this->user['user_id'];
        $data1 = $favorite_query->find();
        if($data1) foreach ($data1 as $key=>$value){
        	$data1[$key]['img'] 		= empty($value['img']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/200/h/200");
        }
        /* 专辑收藏 */
        $favorite_a_query 				= new IQuery('favorite_article as a');
        $favorite_a_query->join 		= 'left join article as aa on aa.id = a.aid';
        $favorite_a_query->fields 		= 'a.*,aa.id,aa.title,aa.title,aa.image,aa.content';
        $favorite_a_query->where 		= 'user_id = ' . $this->user['user_id'];
        $data2 = $favorite_a_query->find();
        if($data2) foreach ($data2 as $key=>$value){
        	$data2[$key]['description'] = empty($value['content']) ? '' :  mb_substr( trim(strip_tags( str_ireplace('&nbsp;','',htmlspecialchars_decode($value['content']) ) )), 0, 30,'utf-8');
        	unset($data2[$key]['content']);
        	$data2[$key]['image'] 		= empty($value['image']) ? '' : IWeb::$app->config['image_host'].IUrl::creatUrl("/pic/thumb/img/".$value['image']."/w/210/h/107");
        }
        $this->json_echo(['goods_data'=>$data1,'article_data'=>$data2]);
    }
    function user_credit_info(){
        $user_id     = $this->user['user_id'];
        $user_query = new IQuery('user');
        $user_query->where = 'id = ' . $user_id;
        $data = $user_query->find()[0];
        $image1 = $data['sfz_image1'];
        $image2 = $data['sfz_image2'];
        $data['sfz_image1x'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$image1."/w/110/h/110");
        $data['sfz_image2x'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$image2."/w/110/h/110");
        $data['sfz_image1y'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$image1."/w/281/h/207");
        $data['sfz_image2y'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$image2."/w/281/h/207");
        $this->json_echo($data);
    }
    function qrcode(){
        if(IClient::isWechat() == true){
            require_once __DIR__ . '/../plugins/wechat/wechat.php';
            require_once __DIR__ . '/../plugins/curl/Curl.php';
            $this->wechat = new wechat();
            $curl = new \Wenpeng\Curl\Curl();
            $access_token = $this->wechat->getAccessToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;
            $curl->post(json_encode(['action_name'=>'QR_LIMIT_SCENE','action_info'=>['scene'=>['scene_id'=>'chenbo']]]))->url($url);
            $ret = json_decode($curl->data());
            echo 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ret->ticket);
            echo '<br>';
            echo $ret->url;
//            var_dump($curl->data());
        }
    }
    function recommender_shop_tobe_booked(){
        $recommender = ISession::get('recommender');
        if (empty($recommender)){$this-$this->redirect('index');}
        $data = $this->get_shop_recommender();
        $this->json_echo($data);
    }
    function test(){
        $a = [
            'name' => 'a',
            'price' => '25',
            'order' => [
                [
                    'order_num' => '123',
                    'status' => '已完成',
                    'price' => '55.00',
                    'goods' => [
                        [
                            'goods_price' => '20',
                            'name' => '商品名称',
                            'img'=>'/',
                            'id' => '2',
                            'price' => '520.0'
                        ],
                        [
                            'goods_price' => '20',
                            'name' => '商品名称',
                            'img'=>'/',
                            'id' => '21',
                            'price' => '520.0'
                        ]
                    ]
                ],
                [
                    'order_num' => '123',
                    'status' => '已完成',
                    'price' => '55.00',
                    'goods' => [
                        [
                            'goods_price' => '20',
                            'name' => '商品名称',
                            'img'=>'/',
                            'id' => '22',
                            'price' => '5230.0'
                        ],
                        [
                            'goods_price' => '20',
                            'name' => '商品名称',
                            'img'=>'/',
                            'id' => '121',
                            'price' => '5220.0'
                        ]
                    ]
                ]
            ]
        ];
        $this->json_echo($a);

    }
    function tests(){
        $a=[
            [
                'name' => 'a',
                'price' => '25',
                'order' => [
                    [
                        'order_num' => '123',
                        'status' => '已完成',
                        'price' => '55.00',
                        'goods' => [
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '2',
                                'price' => '520.0'
                            ],
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '21',
                                'price' => '520.0'
                            ]
                        ]
                    ],
                    [
                        'order_num' => '123',
                        'status' => '已完成',
                        'price' => '55.00',
                        'goods' => [
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '22',
                                'price' => '5230.0'
                            ],
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '121',
                                'price' => '5220.0'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'b',
                'price' => '25',
                'order' => [
                    [
                        'order_num' => '123',
                        'status' => '已完成',
                        'price' => '55.00',
                        'goods' => [
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '2',
                                'price' => '520.0'
                            ],
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '21',
                                'price' => '520.0'
                            ]
                        ]
                    ],
                    [
                        'order_num' => '123',
                        'status' => '已完成',
                        'price' => '55.00',
                        'goods' => [
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '22',
                                'price' => '5230.0'
                            ],
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '121',
                                'price' => '5220.0'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'c',
                'price' => '25',
                'order' => [
                    [
                        'order_num' => '123',
                        'status' => '已完成',
                        'price' => '55.00',
                        'goods' => [
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '2',
                                'price' => '520.0'
                            ],
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '21',
                                'price' => '520.0'
                            ]
                        ]
                    ],
                    [
                        'order_num' => '123',
                        'status' => '已完成',
                        'price' => '55.00',
                        'goods' => [
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '22',
                                'price' => '5230.0'
                            ],
                            [
                                'goods_price' => '20',
                                'name' => '商品名称',
                                'img'=>'/',
                                'id' => '121',
                                'price' => '5220.0'
                            ]
                        ]
                    ]
                ]
            ],
        ];
        $this->json_echo($a);
    }
    function recommender_shops_tobe_booked_ajax(){
        $shop_query = new IQuery('shop');
        $shop_query->where = 'recommender = "' . $this->user['user_id'] . '"';
        $ret = $shop_query->find();
        if(!empty($ret)) {
            foreach ($ret as $key => $value){
                $data[$key]['amount_tobe_booked'] = $this->get_amount_tobe_booked($value['identify_id']);
                $data[$key]['identify_id'] = $value['identify_id'];
                $data[$key]['name'] = $value['name'];
                $data[$key]['id'] = $value['id'];
                $data[$key]['address'] = $value['address'];
            }
        }
        $this->json_echo($ret);
    }
    private function json_echo($data){
        echo json_encode($data);
        exit();
    }
}