<?php
require_once __DIR__ . '/../plugins/vendor/autoload.php';
use Endroid\QrCode\QrCode;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
/**
 * @brief 公共方法集合
 * @class Common
 * @note  公开方法集合适用于整个系统
 */
class Common{


    /**
     * 记录用户操作
     * @param int $type
     * @param int $did
     * @return bool
     */
    public static function record($type = 0, $did = 0){
        /* 获取当前用户 */
        $user_id = IWeb::$app->getController()->user['user_id'];
        if(empty($type) || empty($did) || empty($user_id)) return false;
        /* 记录操作 */
        $model = new IModel('record');
        $model->setData(array('user_id' => $user_id, 'type' => $type, 'did' => $did, 'create_time' => time()));
        $rel = $model->add();
        return $rel>0 ? true : false;
    }

    /**
     * 调试-数据库写入
     * @param $data
     */
    public static function dblog($data){
        //数据库
        $data  = json_encode($data);
        $model = new IModel('log_debug');
        $model->setData(array('msg' => $data, 'date' => date('Y-m-d H:i:s', time())));
        return $model->add();
    }
	
	/**
	 * 请求url
	 * @param string $url 请求地址
	 * @param array $body 传输内容
	 * @param string $method 传输方式
	 * @param array $headers http头信息
	 * @return bool 失败返回false
	 */
	public static function curl_http($url,$body='',$method='DELETE',$headers=array()){
		//初始化curl会话
		$ch 			= curl_init();
		/* Curl 设置参数 */
		curl_setopt_array($ch,array(
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_0, 	//强制使用 HTTP/1.0
			CURLOPT_USERAGENT 		=> 'toqi.net', 		//伪装浏览器
			CURLOPT_CONNECTTIMEOUT 	=> 30, 		//最长等待时间
			CURLOPT_TIMEOUT 		=> 30, 		//执行的最长秒数
			CURLOPT_RETURNTRANSFER 	=> true, 	//文件流的形式返回，而不是直接输出
			CURLOPT_ENCODING 		=> '', 		//发送所有支持的编码类型
			CURLOPT_SSL_VERIFYPEER 	=> false, 	//不返回SSL证书验证请求的结果
			CURLOPT_HEADER 			=> false, 	//不把头文件的信息作为数据流输出
			CURLOPT_URL 			=> $url, 	//请求的url地址
			CURLOPT_HTTPHEADER 		=> $headers,//设置http头信息
			CURLINFO_HEADER_OUT 	=> true, 	//发送请求的字符串
		));
		//设置传输方式
		switch($method){
			case 'POST':
				curl_setopt($ch,CURLOPT_POST,TRUE);
				if(!empty($body))
					curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
				break;
			case 'DELETE':
				curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'DELETE');
				if(!empty($body))
					$url=$url.'?'.str_replace('amp;', '', http_build_query($body));
		}
		//执行会话
		$response 		= curl_exec($ch);
		//获取curl会话信息
		$httpinfo 		= curl_getinfo($ch);
		//关闭curl会话
		curl_close($ch);
		return $response;
	}
	
	/**
	 * CURL下载文件 成功返回文件名，失败返回false
	 * @param $url
	 * @param string $savePath
	 * @return bool|string
	 * @author Zou Yiliang
	 */
	public static function downFile($url, $savePath = './upload')
	{
		//$url = 'http://www.baidu.com/img/bdlogo.png';
		/*
		HTTP/1.1 200 OK
		Connection: close
		Content-Type: image/jpeg
		Content-disposition: attachment; filename="cK4q4fLsp7YOlaqxluDOafB.jpg"
		Date: Sun, 18 Jan 2015 16:56:32 GMT
		Cache-Control: no-cache, must-revalidate
		Content-Length: 963704
		*/
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch, CURLOPT_HEADER, TRUE);    //需要response header
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);    //需要response body
		
		$response = curl_exec($ch);
		
		//分离header与body
		$header = '';
		$body = '';
		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
			$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE); //头信息size
			$header = substr($response, 0, $headerSize);
			$body = substr($response, $headerSize);
		}
		
		curl_close($ch);
		
		//文件名
		$arr = array();
		if (preg_match('/filename="(.*?)"/', $header, $arr)) {
			
			$file = date('Ym') . '/' . $arr[1];
			$fullName = rtrim($savePath, '/') . '/' . $file;
			
			//创建目录并设置权限
			$basePath = dirname($fullName);
			if (!file_exists($basePath)) {
				@mkdir($basePath, 0777, true);
				@chmod($basePath, 0777);
			}
			
			if (file_put_contents($fullName, $body)) {
				return ltrim($fullName,'.');
			}
		}
		
		return false;
	}
	

    /**
     * @brief 获取评价分数
     * @param $grade float 分数
     * @param $comments int 评论次数
     * @return float
     */
    public static function gradeWidth($grade, $comments = 1){
        return $comments==0 ? 0 : round($grade/$comments);
    }

    /**
     * @brief 获取用户状态
     * @param $status int 状态代码
     * @return string
     */
    public static function userStatusText($status){
        $mapping = array('1' => '正常', '2' => '删除', '3' => '锁定');
        return isset($mapping[$status]) ? $mapping[$status] : '';
    }

    /**
     * 获取本地版本信息
     * @return String
     */
    public static function getLocalVersion(){
        return include(IWeb::$app->getBasePath().'docs/version.php');
    }
    /**
     * User: chenbo
     * 日志写
     * @param $info
     */
    public static function log_write($info, $type='INFO'){
        $log = new Logger('debugger');
        $log_path = __DIR__ . '/../backup/logs/b2b2c';
        if (!file_exists($log_path)) mkdir($log_path);
        switch ($type){
            case 'DEBUG':
                $log->pushHandler(new StreamHandler($log_path . '/'.date('Y-m-d').'-DEBUG.log', Logger::DEBUG));
                $log->addInfo($info);
            case 'INFO':
                $log->pushHandler(new StreamHandler($log_path . '/'.date('Y-m-d').'-INFO.log', Logger::INFO));
                $log->addInfo($info);
            default:
                $log->pushHandler(new StreamHandler($log_path . '/'.date('Y-m-d').'-WARNING.log', Logger::WARNING));
                $log->addInfo($info);
        }
    }
    /**
     * User: chenbo
     * post请求
     * @param $url
     * @param $jsonStr
     * @return array
     */
    public static function http_post_json($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return array($httpCode, $response);
    }
    /**
     * User: chenbo
     * 获取用户access_token
     * @return mixed
     */
    public static function get_wechat_access_token(){
        require_once __DIR__.'/../plugins/wechat/wechat.php';
        $wechat = new wechat();
        $access_token = $wechat->getAccessToken();
        return $access_token;
    }
    /**
     * User: chenbo
     * 添加用户扫码关注
     */
    public static function add_qrcode_follow($scene_id,$open_id){
        if (!empty($scene_id)){
            $follow_query = new IQuery('follow');
            $follow_query->where = 'open_id = "' . $open_id.'"';
            $follow_data = $follow_query->find();
            if (!empty($follow_data)){
                $follow_model = new IModel('follow');
                $follow_model->setData(['scene_id'=>$scene_id ,'follow_time'=>date('Y-m-d H:i:s',time())]);
                $follow_model->update('open_id = "' . $open_id . '"');
            } else {
                $follow_model = new IModel('follow');
                $follow_model->setData(['scene_id'=>$scene_id, 'open_id'=>$open_id ,'follow_time'=>date('Y-m-d H:i:s',time())]);
                $follow_model->add();
            }
        } else {
            $follow_model = new IModel('follow');
            $follow_model->setData(['unsubscribe_time'=>date('Y-m-d H:i:s',time())]);
            $follow_model->update('open_id="'.$open_id.'"');
        }
    }

    public static function jssdk(){
        if(IClient::isWechat() == true){
            require_once __DIR__ . '/../plugins/wechat/wechat.php';
            $wechat = new wechat();
            $wechat->setConfig();
            $wechat->config['wechat_jsApiSDK'] = 1;
            $wechat->jsApiSDK();
            return true;
        } else {
            return false;
        }
    }
    static public function save_url_image($url,$dirname, $type = ''){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOBODY, 0);    //对body进行输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $package = curl_exec($ch);
        $httpinfo = curl_getinfo($ch);

        curl_close($ch);
        $media = array_merge(array('mediaBody' => $package), $httpinfo);

        //求出文件格式
        preg_match('/\w\/(\w+)/i', $media["content_type"], $extmatches);
        $fileExt = $extmatches[1];
        $fileExt = 'jpg';
        $filename = time().rand(100,999).$type.".{$fileExt}";
        if(!file_exists($dirname)){
            mkdir($dirname,0777,true);
        }
        file_put_contents($dirname.'/'.$filename,$media['mediaBody']);
        return $dirname.'/'.$filename;
    }
}