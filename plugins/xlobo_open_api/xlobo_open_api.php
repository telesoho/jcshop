<?php
require_once  __DIR__ . '/../vendor/autoload.php';
use Monolog\Logger;

/**
 * @brief 贝海OpenAPI接口
 * @author twh
 * @date 2018/6/8
 */
class xlobo_open_api extends pluginBase
{
	private $pluginDir;

	private $data = array('error' => '', 'info' => '');

	//注册事件
	public function reg()
	{
		// 添加后台管理菜单
		plugin::reg("onSystemMenuCreate",function(){
			$link = "/plugins/xlobo_open_api"; // 插件链接必须小写字母，否则后台菜单会出现无法选中的问题
			Menu::$menu["插件"]["插件管理"][$link] = $this->name();
		});

		// 增加插件画面显示接口
		plugin::reg("onBeforeCreateAction@plugins@xlobo_open_api",function(){
            self::controller()->xlobo_open_api = function(){
				$this->redirect("xlobo_open_api", $this->data);
			};
		});

		// 获取物流信息
		plugin::reg("onBeforeCreateAction@plugins@xlobo_logistic_info",function(){
            self::controller()->xlobo_logistic_info = function(){
				$billcodes_array = explode(",", IReq::get('billcodes'));
				$params = array(
					'BillCodes' => $billcodes_array
				);
				$this->run('xlobo.status.get', $params);
			};
		});


		// API测试接口
		plugin::reg("onBeforeCreateAction@plugins@xlobo_run", function()
		{
			self::controller()->xlobo_run = function() 
			{
				$api_name = IReq::get("api_name");
				$req_json = IReq::get("req_json");
		
				$req = $this->json_decode($req_json);

				$this->run($api_name, $req);
			};
		});
	}

	private function json_decode($req_json) {
		$encoding = mb_detect_encoding($req_json, array("ASCII","GB2312","GBK","UTF-8"));

		if($encoding != "GBK") {
			$req_json = iconv($encoding, 'GBK', $req_json); //将字符串的编码转到GBK
		}

		$req = json_decode($req_json, true);

		if(json_last_error()) {
			$this->exitJSON(json_last_error_msg());
		}

		return $req;
	}

	/**
	 * 运行贝海接口
	 */
	private function run($api_name, $req) {
		try{
			// 初始化平台接口
			xloboapi::init($this->config());
			xloboapi::$log->debug("req", $req);
			$output = xloboapi::requests($api_name, $req);
			xloboapi::$log->debug("output", array($output));
			$this->exitJSON($output);
		} catch(Exception $e) {
			xloboapi::$log->error("执行接口出错", array($e->getMessage()));
			$this->exitJSON($e->getMessage());
		}
	}

	/**
	 * 输出错误日志，并以JSON形式返回错误结果
	 */
	private function exitError($errMsg, $context = array()) {
		$this->error($errMsg, $context);
		$this->exitJSON($this->data);
	}

    /**
     * 输出JSON并退出
     * @param $data
     */
	private function exitJSON($data){
		header('Content-type: application/json');
		echo JSON::encode($data);
		exit();
	}

	/**
	 * 打印信息并退出
	 */
	private function exitMSG($data) {
		print_r($data);
		exit();
	}
	
	// 输出INFO日志
	protected function info($msg, $obj = null){
		if(!isset($this->data['info']))
		{
			$this->data['info'] = "";
		}
		$this->data['info'] .= JSON::encode(array('msg' => $msg, 'obj'=>$obj));
		xloboapi::$log->info($msg, array('obj'=>$obj));
	}

	// 输出错误日志
	protected function error($msg, $obj = null){
		if(!isset($this->data['error']))
		{
			$this->data['error'] = "";
		}
		$this->data['error'] .= JSON::encode(array('msg' => $msg, 'obj'=>$obj));
		xloboapi::$log->err($msg, array('obj' => $obj));
	}

	/**
	 * @brief 插件名字
	 * @return string
	 */
	public static function name()
	{
		return "贝海OpenAPI接口";
	}

	/**
	 * @brief 插件描述
	 * @return string
	 */
	public static function description()
	{
		return "贝海OpenAPI接口";
	}

	//插件默认配置
	public static function configName()
	{
		return 	array(
			'api_server'       => array("name" => "服务器地址","type" => "text","pattern" => "required", "value"=>"http://114.80.87.216:8082/api/router/rest"),
			'access_token'     => array("name" => "access_token","type" => "text","pattern" => "required", "value"=>"ACiYUZ6aKC48faYFD6MpvbOf73BdE9OV5g15q1A6Ghs+i/XIawq/9RHJCzc6Y3UNxA=="),
			'client_secret'      => array("name" => "client_secret","type" => "text","pattern" => "required", "value"=>"APvYM8Mt5Xg1QYvker67VplTPQRx28Qt/XPdY9D7TUhaO3vgFWQ71CRZ/sLZYrn97w=="),
			'client_id'      => array("name" => "client_id","type" => "text","pattern" => "required", "value"=>"68993573-E38D-4A8A-A263-055C401F9369"),
			'version'  => array("name" => "version","type" => "text","pattern" => "required", "value"=>"1.0"),

			"log_level" => array(
				"name" => "日志输出级别",
				"type" => "select",
				"value" => array(
					"DEBUG (100): Detailed debug information." => Logger::DEBUG,
					"INFO (200): Interesting events. Examples: User logs in, SQL logs." => Logger::INFO,
					"NOTICE (250): Normal but significant events." => Logger::NOTICE,
					"WARNING (300): Exceptional occurrences that are not errors." => Logger::WARNING,
					"ERROR (400): Runtime errors" => Logger::ERROR,
					"CRITICAL (500): Critical conditions. " => Logger::CRITICAL,
					"ALERT (550): Action must be taken immediately" => Logger::ALERT,
					"EMERGENCY (600): Emergency: system is unusable." => Logger::EMERGENCY
				),
			)
		);
	}	
}
