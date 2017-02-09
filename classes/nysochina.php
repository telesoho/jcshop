<?php
require_once  __DIR__ . '/../plugins/vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

/**
 * 妮素商品同步接口
 * Auth: twh
 * Date: 2017/01/14
 */
class nysochina
{
    // 根据仓库名称取得对应的发货方式
    private static $WareHouseName2DeliverType = array(
        "国内4仓" => "5",    //国内发货
        "国内5仓" => "5",    //国内发货
        "国内9仓" => "5",    //国内发货
        "国内10仓" => "5",   //国内发货
        "国内13仓" => "5",   //国内发货
        "海外1仓" => "4",    //海外直邮
        "海外2仓" => "4",    //海外直邮
        "海外13仓" => "4",   //海外直邮
        "海外16仓" => "4",   //海外直邮
        "香港1仓" => "2",    //香港直邮
        "香港2仓" => "2",    //香港直邮
        "香港9仓" => "2",    //香港直邮
        "香港13仓" => "2",   //香港直邮
        "香港14仓" => "2",   //香港直邮
        "国内8仓" => "2",    //香港直邮
        "保税1仓" => "1",    //包税区
        "保税7仓" => "1",    //包税区
        "保税9仓" => "1",    //包税区
        "保税10仓" => "1",   //包税区
        "保税12仓" => "1",   //包税区
        "保税13仓" => "1",   //包税区
        "保税16仓" => "1",   //包税区
    );

    private static $config = array(
        'nyso_server'       =>  "http://121.41.84.251:9090",
        'nyso_userkey'      =>  "jiumaojiatest",
        'nyso_parenter'     =>  "1161_651",
        "AddOrder"          =>  "/api/AddOrder.shtml",
        "PostSynchro"       =>  "/api/PostSynchro.shtml",
        "SkuSynchro"        =>  "/api/SkuSynchro.shtml",
        "StockSynchro"      =>  "/api/StockSynchro.shtml",
    );

    public static $log ;


    /**
     * 初始化函数
     * @param $config
     */
    public static function init($config = null){
        if ($config){
            self::$config = $config;
        }

        // 设置输出日志
        self::$log = new Logger('nysochina');
        self::$log->useMicrosecondTimestamps(true);
        $log_path = __DIR__ . '/../backup/logs/nysochina';
        if (!file_exists($log_path)) mkdir($log_path);

        $dateFormat = "Y-m-d H:i:s.u";
        $output = "[%datetime%] %level_name%:%message% %context%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        
        $stream = new StreamHandler($log_path . '/'.date('Y-m-d').'.log');
        $stream->setFormatter($formatter);
        self::$log->pushHandler($stream);
    }

    /**
     * 商品数据同步
     * @param $skus SKU列表，如果为NULL，则同步全部商品数据
     * 使用示例：
     * nysochina::getGoods() //同步全部商品数据
     * nysochina::getGoods(array("MUJ8358", "XHX3265","CC7034")); //取出指定SKU的商品数据
     */
    public static function getGoods($skus = null) {
        $apiName = "SkuSynchro";
        $url = self::$config['nyso_server'] . self::$config[$apiName];
        if ($skus) {
            $param["ReqType"] = "2";
            $param["SkuReqs"] = $skus;
        } else {
    		$param["ReqType"] = "1";
        }
		return self::doQuery($url, $param, "getGoods", $apiName);
    }

	/**
	 * 库存同步接口
     * @skus SKU列表，如果为NULL，则取出全部商品库存
	 */
	public static function getStocks($skus = null) {
        $apiName = "StockSynchro";
        $url = self::$config['nyso_server'] . self::$config[$apiName];
        if ($skus) {
            $param["ReqType"] = "2";
            $param["StockReqs"] = $skus;
        } else {
    		$param["ReqType"] = "1";
        }
		return self::doQuery($url, $param, "getStocks", $apiName);
	}

	/**
	 * 测试运单同步接口
     * @orderNos 订单列表
	 */
	public static function getPosts($orderNos) {
        $apiName = "PostSynchro";
        $url = self::$config['nyso_server'] . self::$config[$apiName];        
        $param["OrderNosReqs"] = $orderNos;
		return self::doQuery($url, $param, "getPosts", $apiName);
	}

    public static function testGoodsSyn(){
		return nysochina::getGoods(array("FAN5852"));
    }

    public static function testPostsSyn() {
        return nysochina::getPosts(array(
            "KA-20170116144016",
            "KA-20170116143700",
            "KA-20170116142843",
            "KA-20170116102711",
        ));
    }

    // public static function testOrder(){
	// 	// $orderNo = "KA-" . date("YmdHis");
	// 	$orderNo = "KA-20170116";
    //     $param["OrderNo"] = $orderNo;
    //     $param["OrderTime"] = "20161016122450";

    //     $item["BuyQuantity"] ="2";
    //     $item["SkuNo"] = "FAN5852";
    //     $item["Tax"] = "23.8";
	// 	$item["BuyPrice"] = "100";  // 实际用户购买时的价格，即下单价？
    //     $items[] = $item;

    //     $param["OrderItems"] = $items;
    //     $param["PostalPrice"] = "20";   // 邮费
    //     $param["GoodsPrice"] = "200.0"; // 货值
    //     $param["Nick"] = "啊啊啊啊啊啊啊";
    //     $param["OrderPrice"] = "220";  // 订单总价
    //     $param["Tax"] = "23.8";         // 商品税费
    //     $param["City"] = "北京";
    //     $param["PayType"] = "1";
    //     $param["PayerName"] = "穆国峰";
    //     $param["Province"] = "北京";
    //     $param["DetailedAddres"] = "北京北京朝阳区望京soho";
    //     $param["Remark"] = "";
    //     $param["ConsigneeName"] = "穆国峰";
    //     $param["ConsigneeNumber"] = "15712341234";
    //     $param["Favourable"] = "23.8";        // 优惠金额
    //     $param["IdCard"] = "230404198812150116";
    //     $param["PayNo"] = "sadadad";
    //     $param["District"] = "朝阳区";
    //     $param["DeliveryType"] = "1";

	// 	return self::addOrder($param);
    // }

	/**
	 * 订单新增接口
     * @param $order 订单详情
	 */
	public static function addOrder($order) {
        $apiName = "AddOrder";
        $url = self::$config['nyso_server'] . self::$config[$apiName];
        $param = $order;
        return self::doQuery($url, $param, "addOrder", $apiName);
	}

	/**
	 * 执行接口API
	 * @param api 接口URL
	 * @param param 参数
	 * @param info 信息
	 * @param apiname 接口名
	 */
	public static function doQuery($api, $param, $info, $apiname) {
		//当前系统时间：格式为yyyy-MM-dd
        $dateStr = date("Y-m-d");
		$paramContent = json_encode($param);
        $tokenStr = self::$config['nyso_userkey'] . $dateStr . $apiname . $paramContent;

        $token = strtoupper(md5($tokenStr));

        $headerParam[] = "parenter:" . self::$config['nyso_parenter'];
        $headerParam[] = "interfacename:" . $apiname;
        $headerParam[] = "token:" . $token;

        try {
            $response = self::jsonPost($api, $paramContent, $headerParam);
            $q['api'] = $api;
            $q['param'] = $param;
            $q['header'] = $headerParam;
            self::$log->info($response, $q);
            return $response;
        } catch(Exception $e) {
            // 输出错误日志            
            $err['api'] = $api;
            $err['param'] = $param;
            $err['header'] = $headerParam;
            self::$log->err($e->getMessage(), $err);
            throw $e;
        }
	}

    /**
     * 根据仓库名取得对应的发货方式
     */
    public static function getDeliveryType ($wareHouseName) {
        return self::$WareHouseName2DeliverType[$wareHouseName];
    }

	/**
	 * 向指定 URL 发送POST方法的请求
	 * 
	 * @param url
	 *            发送请求的 URL
	 * @param paramContent
	 *            请求参数，请求参数应该是 name1=value1&name2=value2 的形式。
	 * @return 所代表远程资源的响应结果
	 */
	public static function jsonPost($url, $paramContent, $header) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $header[] = "accept:*/*";
        $header[] = "Content-Type:text/plain; charset=utf-8";
        $header[] = "connection:Keep-Alive";

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1;SV1)");

        // 发送POST请求必须设置如下两行
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramContent);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (false === $response) {
            throw new Exception(curl_error($ch), $httpCode);           
        }

        curl_close($ch);

        $result = json_decode($response);
        
        if( isset($result->{'success'}) && $result->{'success'} == false ) {
            throw new Exception($result->{'Message'}, $result->{'Code'});
        }

        return $response;
	}
}