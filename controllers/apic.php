
<?php
/**
 * @copyright Copyright(c) 2011 aircheng.com
 * @file Simple.php
 * @brief
 * @author webning
 * @date 2011-03-22
 * @version 0.6
 * @note
 */
/**
 * @brief Simple
 * @class Simple
 * @note
 */
class Apic extends IController
{
//    public $layout='site_mini';

    function init()
    {

    }

    /**
     *获取商品分类数据
     */
    public function getCategoryListTop()
    {
        $data = Api::run('getCategoryListTop');
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
    //[列表页]商品
    public function pro_list()
    {
        $this->catId = IFilter::act(IReq::get('cat'),'int');//分类id

        if($this->catId == 0)
        {
            IError::show(403,'缺少分类ID');
        }

        //查找分类信息
        $catObj       = new IModel('category');
        $this->catRow = $catObj->getObj('id = '.$this->catId);

        if($this->catRow == null)
        {
            IError::show(403,'此分类不存在');
        }

        //获取子分类
        $this->childId = goods_class::catChild($this->catId);
        $data = array($this->catId, $this->catRow, $this->childId);
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
    //获取热卖商品
    public function getCommendHot()
    {
        $data = Api::run('getCommendHot',8);
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
    //获取品牌
    public function getBrandList()
    {
        $data = Api::run('getBrandList');
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
    //获取分类下的商品
    public function getCategoryExtendList()
    {
        $first_id = IFilter::act(IReq::get('id'),'int');
        $data = Api::run('getCategoryExtendList',array('#categroy_id#',$first_id),8);
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }

    //购物车页面及商品价格计算
    public function cart()
    {
        //开始计算购物车中的商品价格
        $countObj = new CountSum();
        $result   = $countObj->cart_count();

        if(is_string($result))
        {
            IError::show($result,403);
        }

        //返回值
//        $this->final_sum = $result['final_sum'];
//        $this->promotion = $result['promotion'];
//        $this->proReduce = $result['proReduce'];
//        $this->sum       = $result['sum'];
//        $this->goodsList = $result['goodsList'];
//        $this->count     = $result['count'];
//        $this->reduce    = $result['reduce'];
//        $this->weight    = $result['weight'];
        header("Content-type: application/json");
        echo json_encode($result);
        exit();
    }
}