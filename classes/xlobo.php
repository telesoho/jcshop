<?php
require_once  __DIR__ . '/../plugins/vendor/autoload.php';
use Curl\Curl;
/**
 * Created by PhpStorm.
 * User: chenbo
 * Date: 2016/12/26
 * Time: 13:15
 */
class xlobo
{
    private static $APPKEY;
    private static $SecretKey;
    private static $AccessToken;
    private static $ServerUrl;

    public static function init(){
        $jmj_config = new Config('jmj_config');
        if ($jmj_config->xlobo){
            self::$APPKEY = '16cc3c0e-76b5-4085-83a1-0c2bc3478ee3';
            self::$SecretKey = 'APvYM8Mt5Xg1QYvker67VplTPQRx28Qt/XPdY9D7TUhaO3vgFWQ71CRZ/sLZYrn97w==';
            self::$AccessToken = 'AD30N4p75N4UKcG0lGwiXXAUGTD60PSbFGoaw9R84s7QoXuv8XhBTad3yO3yiUS+rw==';
            self::$ServerUrl = 'http://bill.open.xlobo.com/api/router/rest';
        } else {
            self::$APPKEY      = '68993573-E38D-4A8A-A263-055C401F9369';
            self::$SecretKey   = 'APvYM8Mt5Xg1QYvker67VplTPQRx28Qt/XPdY9D7TUhaO3vgFWQ71CRZ/sLZYrn97w==';
            self::$AccessToken = 'ACiYUZ6aKC48faYFD6MpvbOf73BdE9OV5g15q1A6Ghs+i/XIawq/9RHJCzc6Y3UNxA==';
            self::$ServerUrl   = 'http://116.228.41.2:8082/api/router/rest';
        }
    }

    public static function requests($method, $data){
        $sign = self::sign($data);
        $url  = self::$ServerUrl;
        $curl = new \Curl\Curl();
        $params = array(
            'method'       => $method,
            'v'            => '1.0',
            'msg_param'    => json_encode($data),
            'client_id'    => self::$APPKEY,
            'sign'         => $sign,
            'access_token' => self::$AccessToken
        );
        $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded; charset=GBK');
        $ret = $curl->post($url, $params);
        if ($ret === false) {
            IError::show_normal('服务器未响应'.self::$ServerUrl);
        }
        if (isset($ret->ErrorCode)){
            IError::show_normal($ret->resourceValue.self::$ServerUrl.'<br>'.print_r($params,true));
        }
        return $ret;
    }
    public static function sign($data){
        $sign = md5(base64_encode(strtolower(self::$SecretKey . json_encode($data) . self::$SecretKey)));
        return $sign;
    }
    public static function create_logistic_single($order_id, $sendgoods){
        $order_query = new IQuery('order as a');
        $order_query->join = ' LEFT JOIN user AS c ON a.user_id = c.id';
        $order_query->where = 'a.id = ' . $order_id;
        $order_query->fields = 'a.id as order_id,a.*,c.*';
        $data = $order_query->find();
        if (empty($data)) return ['msg'=>'无该订单数据'];
        $data = $data[0];
        if (empty($data['postcode'])) $data['postcode'] = '310000';
        $order_goods_query = new IQuery('order_goods as a');
        $order_goods_query->join = 'left join goods as b on a.goods_id = b.id';
        $order_goods_query->where = 'a.order_id = ' . $data['order_id'] . ' and a.goods_id in (' . join(',',$sendgoods) . ')';
        $order_goods_data = $order_goods_query->find();
        if (empty($order_goods_data)) return ['msg' => '无该订单关联数据'];
        $goods_total_weight = 0;
        $goods_arrays = [];
        $goods_ids = [];
        foreach ($order_goods_data as $k=>$v){
            $goods_ids[] = $v['goods_id'];
            if ($v['purchase_price'] == 0.00 || empty($v['purchase_price'])) IError::show_normal($v['goods_id'] . '无进货价');
            $temp = json_decode($v['goods_array']);
            $goods_total_weight += $v['goods_weight'];
            $goods_arrays[] = array(
                'CategoryId' => self::get_goods_xlobo($v['goods_id']),
                'CategoryVersion' => date('Y-m-d H:i:s', time()),
                'Count' => $v['goods_nums'],
                'UnitPrice' => $v['purchase_price'],
//                'UnitPrice' => $v['real_price'],
                'ProductName' => $temp->name,
                'Brand' => self::get_brand($temp->goodsno),
                'Model' => null,
                'Specification' => null,
                'Size' => null
            );
        }
        if (count($goods_arrays) > 10) return ['msg' => '该订单商品数量超过10件'];

        $goods_ids = join('',$goods_ids);
        $params = array(
            'BusinessNo' => $data['order_no'].$goods_ids,
            'Weight' => $goods_total_weight,
            'Insure' => null,
//            'IsRePacking' => 1,
//            'IsPreTax' => 0,
            'IsRecTax' => 0,
            'Comment' => $data['note'],
            'LogisticId' => 32,
            'LogisticVersion' => date('Y-m-d H:i:s', time()),
            'LineTypeId' => 1,
//            'IsContainTax' => null,
            // 发件人信息
            'BillSenderInfo' => array(
                'Name' => '九猫家',
                'Address' => '地址',
                'Phone' => '18152002558',
                'OtherPhone' =>'18152002559'
            ),
            // 面单收件人信息
            'BillReceiverInfo' => array(
                'Name' => $data['accept_name'],
                'Province' => self::get_area_name($data['province']),
                'City' => self::get_area_name($data['city']),
                'District' => self::get_area_name($data['area']),
                'Address' => $data['address'],
                'Phone' => $data['mobile'],
                'OtherPhone' => $data['mobile'],
                'Email' => null,
                'PostCode' => $data['postcode'],
                'IdCode' => $data['sfz_num'],
            ),
            // 渠道信息
            'BillSupplyInfo' => array(
                'OrderCode' => $data['order_no'],
                'TradingNo' => $data['order_no'],
                'ChannelName' => null,
            ),
            // 货物信息
            // 型号、规格、材质不能同时为空，
            // 最多只能有10个货物，包含10个
            'BillCategoryList' => $goods_arrays
        );
        $ret = self::requests('xlobo.labels.createNoVerification',$params);
        return $ret;
    }
    /**
     * User: chenbo
     * 获取面单打印信息
     * @param $billcodes_array
     * @return string
     */
    public static function get_logistic_single_a4($billcodes_array){
        $billcodes_array = array_unique($billcodes_array);
        $params = array(
            'BillCodes' => $billcodes_array
        );
        $ret = self::requests('xlobo.labels.file.getFileA4', $params);
        if (is_array($ret)){
            return $ret;
        }
        $ret = $ret->Result[0]->BillPdfLabel;
        return $ret;
    }

    /**
     * User: chenbo
     * 获取物流信息
     * @param $billcodes_array
     * @return mixed
     */
    public static function get_logistic_info($billcodes_array){
        $params = array(
            'BillCodes' => $billcodes_array
        );
        $ret = self::requests('xlobo.status.get', $params);
        return $ret->Result;
    }

    /**
     * User: chenbo
     * 上传身份证
     * @param $sfz_name
     * @param $phone
     * @param $sfz_num
     * @param $sfz_image1_path
     * @param $sfz_image2_path
     * @return string
     */
    public static function add_idcard($sfz_name, $phone, $sfz_num, $sfz_image1_path, $sfz_image2_path, $billcode = null){
        $sfz_image1 = base64_encode(file_get_contents(__DIR__ . '/../' .$sfz_image1_path));
        $sfz_image2 = base64_encode(file_get_contents(__DIR__ . '/../' .$sfz_image2_path));
        $data = array(
            'RequestId'    => '951357',
            'Name'         => $sfz_name,
            'Phone'        => $phone,
            'IdCode'       => $sfz_num,
            'FrontPicture' => $sfz_image1,
            'BackPicture'  => $sfz_image2,
            'BillCode'     => $billcode,
        );
        $ret = self::requests('xlobo.idcard.add', $data);
        if (isset($ret->ErrorCount) && $ret->ErrorCount > 0){
            $info = print_r($ret->ErrorInfoList, true);
            IError::show_normal($info);
        }
        return $ret;
    }

    /**
     * User: chenbo
     * 获取贝海分类数据
     * @return string
     */
    public static function get_catalogue(){
        $ret = self::requests('xlobo.catalogue.get',[]);
        $ret = $ret->Result->Categorys;
        return $ret;
    }

    /**
     * User: chenbo
     * 获取货站数据
     * @return string
     */
    public static function get_hub(){
        $ret = self::requests('xlobo.hub.get',[]);
        $ret = $ret->Result->LogisticInfoList;
        return $ret;
    }

    public static function get_brand($goods_no){
        $goods_query = new IQuery('goods as a');
        $goods_query->join = 'left join brand as b on a.brand_id = b.id';
        $goods_query->where = 'goods_no = "' . $goods_no . '"';
        $goods_data = $goods_query->find();
        if ($goods_data){
            return $goods_data[0]['name'];
        } else {
            return 'no brand';
        }
    }

    /**
     * User: chenbo
     * 获取商品在贝海中分类
     * @param $goods_id
     * @return bool
     */
    public static function get_goods_xlobo($goods_id){
        $category_extend = new IQuery('category_extend as a');
        $category_extend->join = 'left join category as b on a.category_id = b.id';
        $category_extend->where = 'a.goods_id = ' . $goods_id;
        $data = $category_extend->find();
//        echo $goods_id;
        if ($data){
            return $data[0]['xlobo'];
        } else {
            return 5;
        }
    }

    public static function get_area_name($id){
        $areas_query = new IQuery('areas');
        $areas_query->where = 'area_id = ' . $id;
        $data = $areas_query->find();
        if ($data){
            return $data[0]['area_name'];
        } else {
            return null;
        }
    }
    public static function get_goods_store($goods_no){
        $ret = self::requests('xlobo.fbx.queryinventorybysku', ['BusinessNo'=>'','SkuNos'=>$goods_no]);
        if ($ret->ErrorCount > 0){
            $info = print_r($ret->ErrorInfoList, true);
            IError::show_normal($info);
        }
        return $ret->Result->InventoryInfos;
    }
}