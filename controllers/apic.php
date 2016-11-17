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
        $countSumObj = new CountSum($user_id);
        $result = $countSumObj->cart_count($id,$type,$buy_num,$promo,$active_id);

        if($countSumObj->error)
        {
//            IError::show(403,$countSumObj->error);
            $this->log->addError($countSumObj->error);
        }

        //获取收货地址
        $addressObj  = new IModel('address');
        $addressList = $addressObj->query('user_id = '.$user_id,"*","is_default desc");

        //更新$addressList数据
        foreach($addressList as $key => $val)
        {
            $temp = area::name($val['province'],$val['city'],$val['area']);
            if(isset($temp[$val['province']]) && isset($temp[$val['city']]) && isset($temp[$val['area']]))
            {
                $addressList[$key]['province_val'] = $temp[$val['province']];
                $addressList[$key]['city_val']     = $temp[$val['city']];
                $addressList[$key]['area_val']     = $temp[$val['area']];
            }
        }

        //获取习惯方式
        $memberObj = new IModel('member');
        $memberRow = $memberObj->getObj('user_id = '.$user_id,'custom');
        if(isset($memberRow['custom']) && $memberRow['custom'])
        {
            $this->custom = unserialize($memberRow['custom']);
        }
        else
        {
            $this->custom = array(
                'payment'  => '',
                'delivery' => '',
            );
        }

        //返回值
        $data['gid']= $id;
        $data['type']= $type;
        $data['num']= $buy_num;
        $data['promo']= $promo;
        $data['active_id']= $active_id;
        $data['final_sum']= $result['final_sum'];
        $data['promotion']= $result['promotion'];
        $data['proReduce']= $result['proReduce'];
        $data['sum']= $result['sum'];
        $data['goodsList']= $result['goodsList'];
        $data['count']= $result['count'];
        $data['reduce']= $result['reduce'];
        $data['weight']= $result['weight'];
        $data['freeFreight']= $result['freeFreight'];
        $data['seller']= $result['seller'];
        $data['addressList']= $addressList;
        $data['goodsTax']= $result['tax'];

        //配送方式
        $data['delivery'] = Api::run('getDeliveryList');
        //付款方式
        $data['payment'] = Api::run('getPaymentList');
        foreach ($data['payment'] as $key=>$value){
            $data['payment'][$key]['paymentprice'] = CountSum::getGoodsPaymentPrice($value['id'],$data['sum']);
        }
        //商品展示
        foreach ($data['goodsList'] as $key => $value){
            if(isset($value['spec_array'])) $data['goodsList'][$key]['spec_array'] = Block::show_spec($value['spec_array']);
        }

        $this->json_echo($data);
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
            $addressList[$key]['province_val'] =$temp[$data['province']];
            $addressList[$key]['city_val'] =$temp[$data['city']];
            $addressList[$key]['area_val'] =$temp[$data['area']];
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
        unset($checkArray['zip'],$checkArray['user_id']);
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
        $sqlData['province_val'] = $areaList[$province];
        $sqlData['city_val']     = $areaList[$city];
        $sqlData['area_val']     = $areaList[$area];
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
        $ret0 = Api::run('getOrderList',$this->user['user_id'], 'pay_type != 0 '); // 全部订单
        $ret1 = Api::run('getOrderList',$this->user['user_id'], 'pay_type != 0 and status = 1 and pay_type != 0'); // 待支付
        $ret2 = Api::run('getOrderList',$this->user['user_id'], 'pay_type != 0 and status = 2 and distribution_status = 0'); // 待发货
        $ret3 = Api::run('getOrderList',$this->user['user_id'], 'pay_type != 0 and status = 2 and distribution_status = 1'); // 待收货
        $ret4 = Api::run('getOrderList',$this->user['user_id'], 'pay_type != 0 and status = 5 '); // 已完成
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
                $temp2 = area::name($value['province'],$value['city'],$value['area']);
                $data[$k][$key]['province_val'] =$temp2[$value['province']];
                $data[$k][$key]['city_val'] =$temp2[$value['city']];
                $data[$k][$key]['area_val'] =$temp2[$value['area']];
//                $orderObj = new order_class();
//                $data[$k][$key]['order_info'] = $orderObj->getOrderShow($value['id'],$this->user['user_id']);
                $temp3 = Api::run('getOrderGoodsListByGoodsid',array('#order_id#',$value['id']));
                foreach ($temp3 as $key1 => $value1){
                    $temp3[$key1]['goods_array'] = json_decode($value1['goods_array'],true);
                }
                $data[$k][$key]['goodslist'] = $temp3;

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
        $relation = array('已完成'=>'删除订单', '等待发货'=>'取消订单', '等待付款'=>'去支付', '已发货' => '查看物流', '已取消'=>'已取消');
        foreach ($data['state0'] as $key => $value){
            $data['state0'][$key]['text'] = $relation[$value['orderStatusText']];
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
    function tag_hot_list(){
        $keyword_query = new IQuery('keyword');
        $keyword_query->where = 'hot = 1';
//        $keyword_query->order = 'order asc';
        $keyword_query->limit = 10;
        $data = $keyword_query->find();
        $this->json_echo($data);
    }


    /**
     * ---------------------------------------------------专辑---------------------------------------------------*
     */
    //显示专辑列表（首页）
    public function article_list(){
        if (empty($this->user['user_id'])){$this->json_echo([]);}
        $goods_query = new IQuery("goods");
        /*视频专辑*/
        $category = 3;
        $article_query = new IQuery('article');
        $article_query->fields = 'id,title,image,visit_num,favorite,category_id';
        $article_query->where = ' category_id = ' . $category;
        $article_data_spzj = $article_query->find();

        /*特别专辑*/
        $categorys = ['10','11','12','13','14'];
        $where = '';
        for ($i=0;$i<count($categorys);$i++){
            if ($i==count($categorys)-1){
                $where .= 'category_id = '.$categorys[$i];
            } else {
                $where .= 'category_id = '.$categorys[$i].' or ';
            }
        }
        $article_query = new IQuery('article');
        $article_query->fields = 'id,title,image,visit_num,favorite,category_id';
        $article_query->where = $where;
        $article_query->limit = 10;
        $article_data_tbtj =$article_query->find();
//        $category_query = new IQuery("article_category");
//        foreach ($article_data_tbtj as $key=>$value){
//            $category_query->where = 'id = ' . $value['category_id'];
//            $temp = $category_query->find();
//            $article_data_tbtj[$key]['category_name'] = $temp[0]['name'];
//            $article_data_tbtj[$key]['article_type'] = 'tbtj';
//        }

        /*图文专辑*/
        $category_query = new IQuery("article_category");
        $category_query->where = 'parent_id = 1';
        $category_query->fields='id,name';
        $category_data = $category_query->find();
        $visit_article_id = ISession::get('visit_article_id');
        if (!empty( $visit_article_id )){
            $visit_num = explode(',',$visit_article_id)[1];
            $xb = explode(',',$visit_article_id)[1];
            $visit_article_id = explode(',',$visit_article_id)[0];
            ISession::clear('visit_article_id');
        } else {
            $visit_num = ISession::get('visit_num');
        }
        $xb = ISession::get('xb');
        if (empty($visit_num)){
            $x = 97;
            $article_query = new IQuery('article');
            $article_query->fields = 'id,title,image,visit_num,favorite,category_id';
            for ($i=0;$i<count($category_data);$i++){
                $article_query->where = 'category_id = ' . $category_data[$i]['id'];
                ISession::set(chr($x), $article_query->find());
                $x++;
            }
            ISession::set('visit_num',1);
            ISession::set('xb',1);
            $visit_num = ISession::get('visit_num');
            $xb = ISession::get('visit_num');
        } else {
            ISession::set('visit_num', $visit_num+1);
            ISession::set('xb',$xb + 1);
        }
        $x = 97;
        $article_data_twzj = [];
        for ($i=0;$i<count($category_data);$i++){
            switch (chr($x)){
                case 'a': // 4
                    $start = 4*($visit_num-1);
                    $length = 4;
                    $temp = ISession::get(chr($x));
                    $splice = array_splice($temp, $start, $length);
                    if (empty($splice)){
                        ISession::set('visit_num',2);
                        $visit_num = 1;
                        $splice = array_splice($temp, 0, 4);
                    }
                    $article_data_twzj = array_merge($article_data_twzj, $splice);
                    break;
                default: // 1
                    $start = $xb-1;
                    $length = 1;
                    $temp = ISession::get(chr($x));
                    $splice = array_splice($temp, $start, $length);
                    if (empty($splice)){
                        ISession::set('xb',2);
                        $xb = 1;
                        $splice = array_splice($temp, 0, 1);
                    }
                    $article_data_twzj = array_merge($article_data_twzj, $splice);
            }
            $x++;
        }
        $page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $favorite_article = new IQuery('favorite_article');
        if ($page == 1 ){
            if ( ISession::get('is_first') || ISession::get('tbtj_visited') ){
                $data = $article_data_tbtj;
            } else {
                $data = $article_data_twzj;
            }
        } else {
            $data = $article_data_twzj;
        }
        //专辑4*1*1*1中后三者为空时的随机填补
        if ($visit_article_id){
            $if_find = false;
            for ($i=0;$i<count($data);$i++){
                if ($data[$i]['id'] == $visit_article_id){
                    $temp = $data[$i];
                    $data[$i] = $data[0];
                    $data[0] = $temp;
                    $if_find = true;
                }
            }
            if (!$if_find){
                $article_query->where = 'id = ' . $visit_article_id;
                $temp = $data[0];
                $data[0] = $article_query->find()[0];
                $data[count($data)] = $temp;
            }
        }
        if(!empty($article_data_spzj)) array_push($data, $article_data_spzj[array_rand($article_data_spzj,1)]);
        //返回数据格式化
        foreach ($data as $k=>$v){
            //用户是否对专辑点赞
            $favorite_article->where = 'user_id='.$this->user['user_id'].' and aid='.$v['id'];
            $fdata = $favorite_article->find();
            if (!empty($fdata)){
                $data[$k]['is_favorite'] = 1;
            } else {
                $data[$k]['is_favorite'] = 0;
            }
            //专辑封面的缩略图
            $data[$k]['image'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['image']."/w/750/h/380");
            //专辑所在分类的名称
            $category_query->where = 'id = ' . $data[$k]['category_id'];
            $temp = $category_query->find();
            $data[$k]['category_name'] = $temp[0]['name'];
            //专辑关联商品的数量
            $relation = new IQuery('relation as r');
            $relation->join = 'left join goods as go on r.goods_id = go.id';
            $relation->where = sprintf('go.is_del = 0 and r.article_id = %s and go.id is not null', $v['id']);
            $data[$k]['nums'] = count($relation->find());

//            $data[$k]['visit_num_n'] = $visit_num;
            $data[$k]['xb'] = $xb;
//            $data[$k]['goods_list'] = [];

            $article = new IQuery('relation as r');
            $article->join = 'left join goods as go on r.goods_id = go.id';
            $article->where = sprintf('go.is_del = 0 and r.article_id = %s and go.id is not null', $data[$k]['id']);
            $article->filds = 'go.goods_no as goods_no,go.id as goods_id,go.img,go.name,go.sell_price';
//            $article->limit = 3;
            $relationList = $article->find();
            foreach ($relationList as $key => $value){
                $relationList[$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/180/h/180");
            }
            $data[$k]['goods_list'] = $relationList;

        }

        //专辑中的随机推荐三个关联商品
//        for ($i=0;$i<3;$i++){
//            $a = rand(0,7);
//            $article = new IQuery('relation as r');
//            $article->join = 'left join goods as go on r.goods_id = go.id';
//            $article->where = sprintf('go.is_del = 0 and r.article_id = %s and go.id is not null', $data[$a]['id']);
//            $article->filds = 'go.goods_no as goods_no,go.id as goods_id,go.img,go.name,go.sell_price';
//            $article->limit = 3;
//            $relationList = $article->find();
//            foreach ($relationList as $key => $value){
//                $relationList[$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/180/h/180");
//            }
//            $data[$a]['goods_list'] = $relationList;
//        }



//        echo $visit_num;
//        echo '<a href="http://192.168.0.156:8080/index.php?controller=site&action=article_detail&id='.$data[0]['id'].'">aa</a>';
//        var_dump($data);
//        ISession::clear('visit_num');

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
        $article->pagesize = 4;
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
        $data = Api::run('getCategoryListTop');
        foreach ($data as $key => $value){
            $temp2 = IWeb::$app->config['image_host'] . '/upload/category_icon/' . $value['id'] . '_0.png,';
            $temp2 .= IWeb::$app->config['image_host'] . '/upload/category_icon/' . $value['id'] . '_1.png';
            $data[$key]['image'] = $temp2;

            if (!empty($value['banner_image'])){
                $data[$key]['banner_image'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['banner_image']."/w/520/h/154");
            }
            if (!empty($value['image'])){
                $temp = explode(',',$value['image']);
                $data[$key]['image'] = IWeb::$app->config['image_host'] . '/' . $temp[0].','.IWeb::$app->config['image_host'] . '/' . $temp[1];
            }
            $data[$key]['child'] = [];
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
     * ---------------------------------------------------搜索---------------------------------------------------*
     */

    public function search(){
        $word = IFilter::act(IReq::get('word'),'string');
        $goods_query = new IQuery('goods');
        $goods_query->where = "name like '%,".$word.",%'";
//        echo $goods_query->getSql();
        $goods_data = $goods_query->find();
        var_dump($goods_data);
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
    function favorite_list(){
        $favorite_query = new IQuery('favorite as a');
        $favorite_query->join = 'left join goods as go on go.id = a.rid';
        $favorite_query->fields = 'a.*,go.id,go.name,go.sell_price,go.market_price,go.img';

        $favorite_query->where = 'user_id = ' . $this->user['user_id'];
        $data1 = $favorite_query->find();
        if($data1) foreach ($data1 as $key=>$value){
            if (!empty($value['img'])){
                $data1[$key]['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['img']."/w/200/h/200");
            }
        }
        $favorite_a_query = new IQuery('favorite_article as a');
        $favorite_a_query->join = 'left join article as aa on aa.id = a.aid';
        $favorite_a_query->fields = 'a.*,aa.id,aa.title,aa.title,aa.image,aa.description';
        $favorite_a_query->where = 'user_id = ' . $this->user['user_id'];
        $data2 = $favorite_a_query->find();
        if($data2) foreach ($data2 as $key=>$value){
            if (!empty($value['image'])){
                $temp = explode(',',$value['image']);
                $data2[$key]['image'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$value['image']."/w/210/h/107");
            }
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
        $data['sfz_image1'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$image1."/w/110/h/110");
        $data['sfz_image2'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$image2."/w/110/h/110");
        $data['sfz_image1x'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$image1."/w/281/h/207");
        $data['sfz_image2x'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$image2."/w/281/h/207");
        $this->json_echo($data);
    }

    private function json_echo($data){
        echo json_encode($data);
        exit();
    }
}