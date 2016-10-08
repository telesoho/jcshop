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
}
