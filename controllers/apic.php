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
//        $data['payment'] = Api::run('getPaymentList');
//        foreach ($data['payment'] as $key=>$value){
//            $data['payment'][$key]['paymentprice'] = CountSum::getGoodsPaymentPrice($value['id'],$data['sum']);
//        }
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
        unset($checkArray['telphone'],$checkArray['zip'],$checkArray['user_id']);
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
                $data[$k][$key]['goodslist'] = Api::run('getOrderGoodsListByGoodsid',array('#order_id#',$value['id']));
            }
        }
        $data['state0']['text'] = '去支付';
        $data['state1']['text'] = '取消订单';
        $data['state2']['text'] = '查看物流';
        $data['state3']['text'] = '删除订单';
        $data['state4']['text'] = '去支付';
//        var_dump($data);
        header("Content-type: application/json");
        echo json_encode($data);
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
    /**
     * ---------------------------------------------------专辑---------------------------------------------------*
     */
    /**
     * ---------------------------------------------------分类---------------------------------------------------*
     */

    /**
     *商品的分类数据
     */
    public function getCategoryListTop()
    {
        $data = Api::run('getCategoryListTop');
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
    /**
     * 通过分类ID返回分类信息（包括其子类）
     */
    public function pro_list()
    {
        $this->catId = IFilter::act(IReq::get('cat'),'int');//分类id
        if($this->catId == 0)
        {
//            IError::show(403,'缺少分类ID');
            $this->log->addError('缺少分类ID');
        }
        //查找分类信息
        $catObj       = new IModel('category');
        $this->catRow = $catObj->getObj('id = '.$this->catId);
        if($this->catRow == null)
        {
//            IError::show(403,'此分类不存在');
            $this->log->addError('此分类不存在');
        }
        //获取子分类
        $this->childId = goods_class::catChild($this->catId);
        $data = array($this->catId, $this->catRow, $this->childId);
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
    //获取分类下的分类数据
    public function getCategoryByParentid()
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
    public function getBrandList()
    {
        $data = Api::run('getBrandList');
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
    /**
     * ---------------------------------------------------搜索---------------------------------------------------*
     */
}