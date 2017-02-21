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

    private static $apiKey = array(
        "AddOrder"          =>  array("partner" => "nyso_parenter", "key" => "nyso_userkey"),
        "PostSynchro"       =>  array("partner" => "nyso_parenter", "key" => "nyso_userkey"),
        "SkuSynchro"        =>  array("partner" => "nyso_parenter", "key" => "nyso_userkey"),
        "StockSynchro"      =>  array("partner" => "nyso_parenter", "key" => "nyso_userkey"),
        "searchOrder"       =>  array("partner" => "nyso_supParenter", "key" => "nyso_supUserKey"),
        "orderDelivery"     =>  array("partner" => "nyso_supParenter", "key" => "nyso_supUserKey"),
        "supGoodsSynchro"   =>  array("partner" => "nyso_supParenter", "key" => "nyso_supUserKey"),
        "supStockSynchro"   =>  array("partner" => "nyso_supParenter", "key" => "nyso_supUserKey"),
    );

    public static $config = array(
        'nyso_server'       =>  "http://121.41.84.251:9090",
        'nyso_userkey'      =>  "jiumaojiatest",
        'nyso_supUserKey'   =>  "b306f5829b6045f8a10efacebcd5b5c1",
        'nyso_parenter'     =>  "1161_651",

        // 妮素平台电子订单接口
        "AddOrder"          =>  "/api/AddOrder.shtml",
        "PostSynchro"       =>  "/api/PostSynchro.shtml",
        "SkuSynchro"        =>  "/api/SkuSynchro.shtml",
        "StockSynchro"      =>  "/api/StockSynchro.shtml",

        // 妮素供应商平台接口
        "searchOrder"       =>  "/api/sup/searchOrder.shtml",
        "orderDelivery"     =>  "/api/sup/orderDelivery.shtml",
        "supGoodsSynchro"   =>  "/api/sup/supGoodsSynchro.shtml",
        "supStockSynchro"   =>  "/api/sup/supStockSynchro.shtml",

        // 是否输出调试信息
        "debug"             => "false",
    );

    public static $log ;

    public static function config() {
        return self::$config;
    }

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
     * 妮素API的统一调用接口
     * @param $api_name 接口名称
     * @param $req 接口参数
     * @return 妮素API返回的JSON结果
     * @throw  Exception
     */
    public static function run($api_name, $req) {
        $url = self::$config['nyso_server'] . self::$config[$api_name];
        return self::doQuery($url, $req, "" , $api_name);
    }

    // 根据查询到的妮素订单生成订单号
    public static function getOrderId($nysoOrder) {
        $orderTime = date_create_from_format("Y-m-d H:i:s", $nysoOrder['OrderTime']);
        $mobile = $nysoOrder['ConsigneeNumber'];
        $orderId = "NS" . $orderTime->format("YmdHis") . substr($mobile, -4);
        return $orderId;
    }


    /*=====================================================================================
     *       妮素商品          妮素订单
     * 妮素 ==========> 九猫 ============> 妮素
     * 电子订单接口部分，用于从妮素平台拉取商品及向妮素推送九猫平台订单
     * 
     *======================================================================================*/
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

    /**
     * 返回json
     * @param $data
     */
	private static function exitJSON($data){
		header('Content-type: application/json');
		echo json_encode($data);
		exit();
	}

    /*===================================================================================
     * 私有函数
     *==================================================================================*/
	/**
	 * 执行接口API
	 * @param api 接口URL
	 * @param param 参数
	 * @param info 信息
	 * @param apiname 接口名
	 */
	private static function doQuery($api, $param, $info, $apiname) {
		//当前系统时间：格式为yyyy-MM-dd
        $dateStr = date("Y-m-d");
		$paramContent = json_encode($param);
        $apiPartner = self::$config[self::$apiKey[$apiname]['partner']];
        $apiKey = self::$config[self::$apiKey[$apiname]['key']];
        $tokenStr = $apiKey . $dateStr . $apiname . $paramContent;

        $token = strtoupper(md5($tokenStr));

        $headerParam[] = "parenter:$apiPartner";
        $headerParam[] = "interfacename:$apiname";
        $headerParam[] = "token:$token";

        try {
            $response = self::jsonPost($api, $paramContent, $headerParam);
            if(self::$config['debug'] == "true") {
                $context['api'] = $api;
                $context['param'] = $param;
                $context['header'] = $headerParam;
                $context['info'] = $info;
                $context['response'] = $response;
                self::$log->info($api, $context);
            }
            return $response;
        } catch(Exception $e) {         
            // 输出错误日志            
            $context['api'] = $api;
            $context['param'] = $param;
            $context['header'] = $headerParam;
            $context['tokenStr'] = $tokenStr;
            $context['info'] = $info;
            self::$log->err($e->getMessage(), $context);
           
            throw $e;
        }
	}

    /**
     * 根据仓库名取得对应的发货方式
     */
    private static function getDeliveryType ($wareHouseName) {
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
	private static function jsonPost($url, $paramContent, $header) {
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

        $result = json_decode($response, true);

        if(isset($result['success']) && !$result['success'] ) {
            throw new Exception(print_r($result, true));
        }
        return $result;
	}
     
}