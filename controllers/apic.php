
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
            $this->log->addError('msg', $result);
        }
        echo json_encode($result);
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