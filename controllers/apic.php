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
    }
    /**
     * ---------------------------------------------------购物车---------------------------------------------------*
     */
    //购物车商品列表页面
    public function cart()
    {
        header("Content-type: application/json");
        //开始计算购物车中的商品价格
        $countObj = new CountSum();
        $result   = $countObj->cart_count();

        if(is_string($result))
        {
//            IError::show($result,403);
            $this->log->addError('$result变量错误');
        }
        echo json_encode($result);
        exit();
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

        header("Content-type: application/json");
        echo json_encode($data);
        exit();
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

        header("Content-type: application/json");
        echo json_encode($addressList);
        exit();
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
            $model->setData($sqlData);
            if($id)
            {
                $model->update("id = ".$id." and user_id = ".$user_id);
            }
            else
            {
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



        header("Content-type: application/json");
        echo json_encode($result);
        exit();
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
        header("Content-type: application/json");
        echo json_encode(array('ret'=>$ret));
        exit();
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
        header("Content-type: application/json");
        echo json_encode(array('ret'=>true));
        exit();
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
//        $data['pagebar'] = $ret->getPageBar();
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
        $relation = array('已完成'=>'删除订单', '等待发货'=>'取消订单', '等待付款'=>'去支付', '已发货' => '查看物流');
        foreach ($data['state0'] as $key => $value){
            $data['state0'][$key]['text'] = $relation[$value['orderStatusText']];
        }
//        var_dump($data);
        header("Content-type: application/json");
        echo json_encode($data, true);
        exit();
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
        header("Content-type: application/json");
        echo json_encode($data, true);
        exit();
    }

    /**
     * ---------------------------------------------------物流---------------------------------------------------*
     */
    /**
     * ---------------------------------------------------商品---------------------------------------------------*
     */
    /**
     * 返回热卖商品
     */
    public function getCommendHot()
    {
        $data = Api::run('getCommendHot',8);
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
    //获取分类下的商品
    public function getCategoryExtendList()
    {
        $first_id = IFilter::act(IReq::get('id'),'int');
        $data = Api::run('getCategoryExtendList',array('#categroy_id#',$first_id),8);
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
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
        $items[0]['child'] = $items2;
        header("Content-type: application/json");
        echo json_encode($items[0]);
        exit();
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
        header("Content-type: application/json");
        echo json_encode($goods_info);
        exit();
//        $this->redirect('products');
    }
    /**
     * ---------------------------------------------------专辑---------------------------------------------------*
     */
    //显示专辑列表（首页）
    public function article_list(){
        $type = IFilter::act(IReq::get('type'),'int');
        $query = new IQuery("article as ar");
//        $page = 1;
        $query->page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $query->pagesize = 3;
        $query->join = "left join article_category as ac on ac.id = ar.category_id";
        switch ($type){
            case 1:
                $query->where = "ar.category_id = " . $type;
                break;
            case 2:
                $query->where = "ar.category_id = " . $type;
                break;
            case 3:
                $query->where = "ar.category_id = " . $type;
                break;
            default:
                break;
        }
        $query->order = "ar.sort asc,ar.id desc";
        $query->fields = "ar.id,ar.title,ar.content,ar.create_time,ar.top,ar.style,ar.color,ar.sort,ar.visibility,ar.category_id,ar.image,ac.name";
        $items = $query->find();
        foreach ($items as $key => $value){
            $items[$key]['nums'] = count(Api::run('getArticleGoods',array("#article_id#",$value['id'])));
            $items[$key]['totalpage'] = $query->getTotalPage();
        }
        header("Content-type: application/json");
        echo json_encode($items);
        exit();
    }
    //通过专辑获取相关商品
    public function article_rel_goods()
    {
        $article_id = IFilter::act(IReq::get('id'),'int');
        $article = new IQuery('relation as r');
        $article->join = 'left join goods as go on r.goods_id = go.id';
        $article->where = sprintf('r.article_id = %s and go.id is not null', $article_id);
        $article->filds = 'go.goods_no as goods_no,go.id as goods_id,go.img,go.name,go.sell_price';
        $article->page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
        $article->pagesize = 4;
        $relationList = $article->find();
        $total_page = $article->getTotalPage();
        if ($article->page > $total_page){
            $relationList = [];
        }
        header("Content-type: application/json");
        echo json_encode($relationList);
        exit();
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
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }

    /**
     * 获取其子类数据信息
     */
    public function category_child()
    {
        $first_id = IFilter::act(IReq::get('id'),'int');
        $data = Api::run('getCategoryByParentid',array('#parent_id#',$first_id));
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
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
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
    /**
     * ---------------------------------------------------搜索---------------------------------------------------*
     */


    /**
     * ---------------------------------------------------幻灯片---------------------------------------------------*
     */
    public function banner_list(){
        $banner = Api::run('getBannerList');
        header("Content-type: application/json");
        echo json_encode($banner);
        exit();
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
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
}