
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
    //购物车页面及商品价格计算
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
     * @return string
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
                $this->redirect($url);
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
     * ---------------------------------------------------订单---------------------------------------------------*
     */
    /**
     * 获取订单列表
     */
    public function getOrderList()
    {
        $data = [];
        $ret = Api::run('getOrderList',$this->user['user_id']);
        $data['data'] = $ret->find();
        $data['pagebar'] = $ret->getPageBar();
        $payment = new IQuery('payment');
        $payment->fields = 'id,name,type';
        $payments = $payment->find();
        $items = array();
        foreach($payments as $pay)
        {
            $items[$pay['id']]['name'] = $pay['name'];
            $items[$pay['id']]['type'] = $pay['type'];
        }
        foreach ($data['data'] as $key => $value){
            $data['data'][$key]['pay_type'] = $items[$value['pay_type']]['name'];
            $data['data'][$key]['orderStatusText'] = Order_Class::orderStatusText(Order_Class::getOrderStatus($value));
        }

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