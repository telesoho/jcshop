<?php
/**
 * @copyright (c) 2015 aircheng.com
 * @file wechat.php
 * @brief 微信API接口
 * @date 2016/2/19 10:42:25
 * @version 4.4
 */
class wechat extends pluginBase
{
	//微信API地址
	const SERVER_URL = "https://api.weixin.qq.com/cgi-bin";
	private $sslConfig = array(
	    "ssl"=>array(
	        "verify_peer"=>false,
	        "verify_peer_name"=>false,
	    ),
	);

	//配置数组
	public $config = array(
		'wechat_Token'     => '',
		'wechat_AppID'     => '',
		'wechat_AppSecret' => '',
		'wechat_AutoLogin' => '',
		'wechat_jsApiSDK'  => '',
	);
	
	//小程序配置
	public $config_cx    = array(
		'appId'     => 'wx1c0deb97f2677b84',
		'appSecret' => 'abd4cce3ce6895da29144874e0ad92ca',
	);

	//令牌存活时间
	private static $accessTokenTime = 5000;
	private static $jsapiTicketTime = 5000;

	//微信发送的消息XML的array形式
	public $msgObject = null;

	//获取配置参数
	private  function initConfig(){
		//缺少SSL组件
		if(!extension_loaded("OpenSSL")){
			$this->setError = "您的环境缺少OpenSSL组件，这是调用微信API所必须的";
			return false;
		}
		//获取参数配置
		$siteConfigObj = $this->config();
		if(isset($siteConfigObj['wechat_Token']) && isset($siteConfigObj['wechat_AppID']) && isset($siteConfigObj['wechat_AppSecret'])){
			$this->config['wechat_Token']     = $siteConfigObj['wechat_Token'];
			$this->config['wechat_AppID']     = $siteConfigObj['wechat_AppID'];
			$this->config['wechat_AppSecret'] = $siteConfigObj['wechat_AppSecret'];
			$this->config['wechat_AutoLogin'] = $siteConfigObj['wechat_AutoLogin'];
			$this->config['wechat_jsApiSDK']  = $siteConfigObj['wechat_jsApiSDK'];
			return true;
		}else{
			$this->setError("微信配置信息不完全，参数【TOKEN】【AppID】【AppSecret】必须填写完整");
			return false;
		}
	}
	
	/**
	 * 设置配置
	 */
	public function setConfig(){
		return $this->initConfig();
	}
	
	/**
	 * 小程序登陆
	 */
	public function loginCx($code){
		$rel  = $this->getOauthAccessTokenCx($code);
		common::dblog(array(11,$rel));
		$unId = $this->bindUser($rel);
		common::dblog(array(22,$unId));
		$this->login($unId);
	}

	/**
	 * @brief 获取access_token令牌
	 * @param boolean $fresh 是否刷新令牌
	 */
	public function getAccessToken($fresh = false){
		$cacheObj = new ICache();
		$accessTokenTime = $cacheObj->get('accessTokenTime');

		//延续使用
		if($accessTokenTime && time() - $accessTokenTime < self::$accessTokenTime && $fresh == false){
			$accessToken = $cacheObj->get('accessToken');
			if($accessToken){
				return $accessToken;
			}else{
				$cacheObj->del('accessTokenTime');
				return $this->getAccessToken();
			}
		}else{
			//重新获取令牌
			$urlparam = array(
				'grant_type=client_credential',
				'appid='.$this->config['wechat_AppID'],
				'secret='.$this->config['wechat_AppSecret'],
			);
			$apiUrl = self::SERVER_URL."/token?".join("&",$urlparam);
			$json   = file_get_contents($apiUrl,false,stream_context_create($this->sslConfig));
			$result = JSON::decode($json);
			if($result && isset($result['access_token']) && isset($result['expires_in'])){
				$cacheObj->set('accessTokenTime',time());
				$cacheObj->set('accessToken',$result['access_token']);
				return $result['access_token'];
			}else{
				die($json);
			}
		}
	}

	//获取openid
	public static function getOpenId(){
		return ISession::get('wechat_openid');
	}

	//设置openid
	public static function setOpenId($openid){
		ISession::set('wechat_openid',$openid);
	}

    //处理微信服务器的请求接口
    public function response(){
    	$code  = IReq::get('code');
    	$state = IReq::get('state');
	
    	//oauth回调处理
    	if($code && $state){
			$result = $this->getOauthAccessToken($code);
			if($result){
				//保存openid为其他wechat应用使用
				$this->setOpenId($result['openid']);

				//是否自动登录
				if($this->config['wechat_AutoLogin'] == 1)
				{
					$unId = $this->bindUser($result);
					$this->login($unId);
				}
				header('location: http://'.$_SERVER['HTTP_HOST'].urldecode($state));
			}
    	}else{
			//微信推送处理
	    	if($this->checkSignature()){
		    	//第一次验证
		    	if($echostr = IReq::get('echostr')){
					die($echostr);
		    	}else{
					//相应其他的请求
		    		$postXML = file_get_contents("php://input");
		    		//微信推送的post数据信息
		    		if($postXML){
		    			//保存消息对象
						$this->msgObject = $postObj = simplexml_load_string($postXML, 'SimpleXMLElement', LIBXML_NOCDATA);
						//事件推送相应
						if(isset($postObj->Event)){
							$this->eventCatch($postObj);
						}else if(isset($postObj->MsgId)){
							//普通消息推送相应
							$this->msgCatch($postObj);
						}
		    		}
		    	}
		    	die('success');
	    	}else{
	    		die('本次请求非微信客户端发起');
	    	}
    	}
    }

	/**
	 * @brief 提交信息
	 * @param string $submitUrl 提交的URL
	 * @param array $postData 提交数据
	 * @return string 返回的结果字符串
	 */
    public function submit($submitUrl,$postData = null){
		//提交菜单
		$curl = curl_init($submitUrl);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
		curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$postData);// post传输数据
		$responseText = curl_exec($curl);
		curl_close($curl);
		return $responseText;
    }

	/**
	 * 验证推送消息真实性
	 * @return boolean true or false
	 */
	public function checkSignature(){
        $signature = IReq::get('signature');
        $timestamp = IReq::get('timestamp');
        $nonce     = IReq::get('nonce');

		$tmpArr = array($this->config['wechat_Token'],$timestamp,$nonce);
		sort($tmpArr,SORT_STRING);
		$tmpStr = sha1(join($tmpArr));

		if($tmpStr == $signature){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @brief 根据code获取oauth登录令牌和openid
	 * @param string $code
	 */
	private function getOauthAccessToken($code){
		if(!IValidate::check("^[\w\-]+$",$code)){
			die("CODE码非法");
		}

		$urlparam = array(
			'appid='.$this->config['wechat_AppID'],
			'secret='.$this->config['wechat_AppSecret'],
			'code='.$code,
			'grant_type=authorization_code',
		);
		$apiUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?".join("&",$urlparam);
		$json   = file_get_contents($apiUrl,false,stream_context_create($this->sslConfig));
		$result = JSON::decode($json);
		if(!isset($result['openid'])){
			throw new IException("根据code【".$code."】值未获取到open_id码:{$json}");
		}
		return $result;
	}
	
	/**
	 * 小程序，根据code获取oauth登录令牌和openid
	 * @param string $code
	 */
	private function getOauthAccessTokenCx($code){
		if(!IValidate::check("^[\w\-]+$",$code)){
			die("CODE码非法");
		}
		
		$urlparam = array(
			'appid='.$this->config_cx['appId'],
			'secret='.$this->config_cx['appSecret'],
			'js_code='.$code,
			'grant_type=authorization_code',
		);
		$apiUrl = "https://api.weixin.qq.com/sns/jscode2session?".join("&",$urlparam);
		$json   = file_get_contents($apiUrl,false,stream_context_create($this->sslConfig));
		$result = JSON::decode($json);
		if(!isset($result['openid'])){
			throw new IException("根据code【".$code."】值未获取到open_id码:{$json}");
		}
		return $result;
	}

	/**
	 * @brief 更新菜单
	 * @param json $menuData 菜单数据 {"button":[{"name":"名称","sub_button":[{"type":"view","name":"子菜单名称","url":"http://www.aircheng.com"}]}]}
	 * @return array("errcode" => 0,"errmsg" => "ok")
	 */
	public function setMenu(){
		$this->initConfig();
		$menuData = trim(IReq::get('menuData'));

		//URL静默登录替换处理
		$menuData = $this->urlCallback($menuData);
		$accessToken = $this->getAccessToken();
		$urlparam = array(
			'access_token='.$accessToken,
		);
		$apiUrl = self::SERVER_URL."/menu/create?".join("&",$urlparam);
		$json   = $this->submit($apiUrl,$menuData);
		$array  = JSON::decode($json);

		$result = array('result' => 'success');
		if($array['errcode'] != 0){
			$result = array('result' => 'fail','msg' => $array['errmsg']);
		}
		die(JSON::encode($result));
	}

	//URL静默登录替换处理
	private function urlCallback($menuData){
		return preg_replace_callback('|(?<="url":").*?(?=")|',array($this,"converUrl"),$menuData);
	}

	/**
	 * @brief 获取自定义菜单
	 * @return array
	 */
	public function getMenu(){
		$this->initConfig();
		$accessToken = $this->getAccessToken();
		$urlparam = array(
			'access_token='.$accessToken,
		);
		$apiUrl = self::SERVER_URL."/menu/get?".join("&",$urlparam);
		$json   = file_get_contents($apiUrl,false,stream_context_create($this->sslConfig));
		$result = JSON::decode($json);
		$json   =  isset($result['menu']) ? $result['menu'] : null;

		if($json){
			$result = array('result' => 'success','data' => JSON::encode($json));
		}else{
			$result = array('result' => 'fail','msg' => '获取菜单失败:'.$result['errmsg']);
		}
		die(JSON::encode($result));
	}

	/**
	 * @brief 获取用户的基本信息
	 * @param array $userData
	 * {
			"access_token": "****",
			"expires_in": 7200,
			"refresh_token": "****",
			"openid": "****",
			"scope": "snsapi_userinfo"
	 * }
	 * @return array
	 */
	public function getUserInfo($oauthAccess){
		$openid      = $oauthAccess['openid'];
		$accessToken = $oauthAccess['access_token'];
		$scope       = $oauthAccess['scope'];
		$urlparam    = array(
			'access_token='.$accessToken,
			'openid='.$openid,
		);

		//根据不同的授权类型运行不同的接口
		if($scope == 'snsapi_userinfo'){
			$apiUrl = "https://api.weixin.qq.com/sns/userinfo?";
		}else{
			$apiUrl = self::SERVER_URL."/user/info?";
		}

		$apiUrl .= join("&",$urlparam);
		$json    = file_get_contents($apiUrl,false,stream_context_create($this->sslConfig));
		if(strpos($json,"access_token is invalid")){
			$this->oauthLogin();
			return;
		}
		return JSON::decode($json);
	}

	/**
	 * @brief 转换URL
	 * @param string $url 跳转的URL参数
	 */
	public function converUrl($url){
		//preg_replace_callback 回调的是数组参数
		if(is_array($url)){
			foreach($url as $key => $val){
				return $this->converUrl($val);
			}
		}else{
			//伪静态路径
			if(strpos($url,"/") === 0){
				return IUrl::getHost().IUrl::creatUrl($url);
			}
			return $url;
		}
	}

	//获取oauth登录的回调
	public function getOauthCallback(){
		return IUrl::getHost().IUrl::creatUrl("/block/wechat");
	}

	/**
	 * @brief 绑定微信账号到用户系统
	 * @param array $oauthAccess
	 * {
			"access_token": "****",
			"expires_in": 7200,
			"refresh_token": "****",
			"openid": "****",
			"scope": "snsapi_userinfo"
	 * }
	 */
	public function bindUser($oauthAccess){
		if(!isset($oauthAccess['openid'])){
			throw new IException("未获取到用户的OPENID数据");
		}

		//获取微信用户信息
		$wechatUser = $this->getUserInfo($oauthAccess);
		if(isset($wechatUser['errmsg'])){
			throw new IException("获取用户信息失败！".$wechatUser['errmsg']);
		}

		/**
		 * 获取个人信息结果(昵称，头像，性别)
		 * 如果是"snsapi_base"模式且必须关注才可以获取完整信息；
		 * 如果是"snsapi_userinfo"模式可以直接全部获取
		 */
		$unId = $oauthAccess['openid'];

		//当公众号和开发平台有多个应用会存在此 unionid,此时需要开放这里
		//$unId = isset($wechatUser['unionid']) ? $wechatUser['unionid'] : $unId;

		$username = substr($oauthAccess['openid'],-8);
		if(isset($wechatUser['nickname'])){
			//有个别微信用户头像是二进制图片，需要过滤掉
			$wechatName= trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u',"",$wechatUser['nickname']));
			$username  = $wechatName ? IFilter::act($wechatName) : $username;
		}
		$sex        = isset($wechatUser['sex'])        ? $wechatUser['sex']                  : "";
		$ico        = isset($wechatUser['headimgurl']) ? trim($wechatUser['headimgurl'],"0") : "";

		if(isset($wechatUser['subscribe']) && $wechatUser['subscribe'] == 1){
			//关注公众账号的处理写到这里...
		}else{
			//未关注公众账号的处理写到这里...
		}

		//检查用户信息
		$tempDB   = new IModel('oauth_user as ou,user as u');
		$oauthRow = $tempDB->getObj("ou.oauth_user_id = '".$unId."' and ou.oauth_id = 5 and ou.user_id = u.id");

		if($oauthRow){
			//已经关注,更新最新的用户数据
			if(isset($wechatUser['subscribe']) && $wechatUser['subscribe'] == 1){
				$user_id   = $oauthRow['user_id'];
				$userDB    = new IModel('user');
		    	$userCount = $userDB->getObj("username = '{$username}' and id != {$user_id}",'count(*) as num');

		    	//没有重复的用户名
		    	if($userCount['num'] == 0){
		    	}
		    	else{
		    		//随即分配一个用户名
		    		$username = $username.rand(1000,9999);
		    	}

				//更新user表
				$userDB->setData(array(
					'username' => $username,
					'head_ico' => $ico,
				));
				$userDB->update('id='.$user_id);

				//更新member表
				$memberDB = new IModel('member');
				$memberDB->setData(array(
					'sex' => $sex,
				));
				$memberDB->update('user_id = '.$user_id);
			}
		}else{
			$userDB    = new IModel('user');
	    	$userCount = $userDB->getObj("username = '{$username}' ",'count(*) as num');

	    	//没有重复的用户名
	    	if($userCount['num'] == 0){

	    	}else{
	    		//随即分配一个用户名
	    		$username = $username.rand(1000,9999);
	    	}

			//插入user表
			$userDB->setData(array(
				'username' => $username,
				'password' => md5(time()),
				'head_ico' => $ico,
			));
			$user_id = $userDB->add();

			//插入member表
			$memberDB = new IModel('member');
			$memberDB->setData(array(
				'user_id' => $user_id,
				'time'    => ITime::getDateTime(),
				'sex'     => $sex,
			));
			$memberDB->add();

			//插入oauth_user关系表
			$oauthUserDB = new IModel('oauth_user');
			$oauthUserDB->del("oauth_user_id = '".$unId."'");
			$oauthUserData = array(
				'oauth_user_id' => $unId,
				'oauth_id'      => 5,
				'user_id'       => $user_id,
				'datetime'      => ITime::getDateTime(),
			);
			$oauthUserDB->setData($oauthUserData);
			$oauthUserDB->add();
		}
		return $unId;
	}

	/**
	 * @brief 登录用户系统
	 * @param string $unId 唯一ID标识
	 */
	public function login($unId){
		$oauthUserDB = new IModel('oauth_user');
		$oauthRow = $oauthUserDB->getObj("oauth_user_id = '".$unId."' and oauth_id = 5");
		$userRow  = array();
		if($oauthRow){
			$userDB = new IModel('user');
			$userRow = $userDB->getObj('id = '.$oauthRow['user_id']);
		}

		if(!$userRow){
			$oauthUserDB->del("oauth_user_id = '".$unId."' and oauth_id = 5");
			die('无法获取微信用户与商城的绑定信息，请重新关注公众账号');
		}

		$user = plugin::trigger("isValidUser",array($userRow['username'],$userRow['password']));
		if($user){
			plugin::trigger("userLoginCallback",$user);
		}else{
			die('<h1>该用户'.$userRow['username'].'被移至回收站内无法进行登录</h1>');
		}
	}

	/**
	 * @breif oauth路径处理
	 * @param string $url 网址路径
	 * @param string $snsType 登录授权方式：snsapi_base (不弹出授权页面，直接跳转，只能获取用户openid), snsapi_userinfo (弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息)
	 * @return string 处理后oauth的URL
	 */
	public function oauthUrl($url,$snsType = "snsapi_userinfo"){
//		$url = $this->converUrl($url);
		$urlArr = parse_url($url);
		$url = urlencode((isset($urlArr['path']) ? $urlArr['path'] : '').(isset($urlArr['query']) ? '?'.$urlArr['query'] : ''));
		if(strlen($url)>120) $url = substr($url,0,120);
		$urlparam = array(
			'appid='.$this->config['wechat_AppID'],
			'redirect_uri='.urlencode($this->getOauthCallback()),
			'response_type=code',
			'scope='.$snsType,
			'state='.$url,//urlencode($url),
			'connect_redirect=1',
		);
		$apiUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?".join("&",$urlparam)."#wechat_redirect";
		return $apiUrl;
	}

	/**
	 * @brief 微信事件推送处理接口
	 * @param $postObj 微信消息Array形式
	 */
	public function eventCatch($postObj){
		switch($postObj->Event){
			//开始订阅
			case "subscribe":{
			    $this->add_qrcode_follow($postObj->EventKey,$postObj->FromUserName);
				$this->textReplay('亲爱的喵们！ 
 
欢迎来到九猫家微信服务号～
 
九猫家汇集药妆、个护、零食、健康、宠物用品等上万种商品。我们承诺官方直供、东京直邮，全场100%正品，同时努力实现日本同价！
 
人气护肤精华面霜、健康用品热门零食，还有喵汪主子们的食粮，想要的全都有   戳右边→<a href="http://m.jiumaojia.com/site/ticket_gain">领取58元红包</a>

立刻点击下方“九猫大百货”逛逛逛吧~！
 
偷偷告诉你：添加九猫个人微信：jiumaojia001 加入九猫VIP群，群里发放优惠券哦~');

			}
			break;

			//取消订阅
			case "unsubscribe":{
                $this->add_qrcode_follow(null,$postObj->FromUserName);
            }
			break;

			//点击菜单跳转链接时的事件推送
			case "VIEW":{

			}
			break;

			//点击菜单拉取消息时的事件推送
			case "CLICK":{

			}
			break;

            //点击菜单拉取消息时的事件推送
            case "SCAN": {
                $this->textReplay('亲爱的喵们！ 

欢迎来到九猫家微信服务号～

点击右边蓝字获取<a href="http://mp.weixin.qq.com/s/tq_fJ_u28Bt8m41vL0gwgQ">你的2017开年关键词</a>
 
九猫家汇集药妆、个护、零食、健康、宠物用品等上万种商品。我们承诺官方直供、东京直邮，全场100%正品，同时努力实现日本同价！

人气护肤精华面霜、健康用品热门零食，还有喵汪主子们的食粮，想要的全都有   戳右边→<a href="http://m.jiumaojia.com/site/ticket_gain">领取58元红包</a>

立刻点击下方“九猫大百货”逛逛逛吧~！

偷偷告诉你：添加九猫个人微信：jiumaojia001 加入九猫VIP群，群里发放优惠券哦~');
            }
                break;
		}
	}

	/**
	 * @brief 微信普通消息处理接口
	 * @param string $postObj 微信消息Array形式
	 */
	public function msgCatch($postObj){
		if (!isset($postObj->Content) || empty($postObj->Content)) exit(' ');
		switch($postObj->MsgType){
			//自动回复
			default:{
				//数据库内容
				$info = (new IModel('activity_response'))->getObj('request="'.$postObj->Content.'"');
				if(empty($info)){
					switch($postObj->Content){
						case '运势':
							$fortune = array(
								0  => 'PVnzI3WXGfWhBMkJMil6JgV-wCxB4WAtupGlfbRnsGI',
								1  => 'PVnzI3WXGfWhBMkJMil6Josmld8fbURax0tsSHSTeS0',
								2  => 'PVnzI3WXGfWhBMkJMil6Jp2NdwA4-RxjML-Rw6uEoX0',
								3  => 'PVnzI3WXGfWhBMkJMil6JgkwKXpATTCkZ18u1XzicxU',
								4  => 'PVnzI3WXGfWhBMkJMil6JmpayR0p1-ucCWYtWsM3czI',
								5  => 'PVnzI3WXGfWhBMkJMil6JqEIB4hSwn1SEOd0pd95Tvc',
								6  => 'PVnzI3WXGfWhBMkJMil6JgnZ5lMPItKywHzRpUY5-3A',
								7  => 'PVnzI3WXGfWhBMkJMil6Jhr8dPTL1C3gK7hKK8VVMEU',
								8  => 'PVnzI3WXGfWhBMkJMil6JlyPvvtT7dHyJS18Wc4HVdI',
								9  => 'PVnzI3WXGfWhBMkJMil6JtZCxV4BtJmw6eDuerKNgHk',
								10 => 'PVnzI3WXGfWhBMkJMil6Jnl9F9EKL-J76mHqUJv99UA',
								11 => 'PVnzI3WXGfWhBMkJMil6JqEglE-9-3JK4dpQcACjxCo',
								12 => 'PVnzI3WXGfWhBMkJMil6Jp7WdrdQJTBMLhmZv_g9TfI',
								13 => 'PVnzI3WXGfWhBMkJMil6Jo6ID3_KvdwUA98NhSwTh_4',
								14 => 'PVnzI3WXGfWhBMkJMil6JvQfBmcifSOrrKPG4asmuDI',
								15 => 'PVnzI3WXGfWhBMkJMil6JhCb8IRNuPak2ZR9M6Ibbbo',
								16 => 'PVnzI3WXGfWhBMkJMil6Js83r8tOdvGBwt73drlvszE',
								17 => 'PVnzI3WXGfWhBMkJMil6Jntu40MVVWsCAXfA1iCKiAI',
								18 => 'PVnzI3WXGfWhBMkJMil6JscIOpObLSAA6vW1tX069GU',
								19 => 'PVnzI3WXGfWhBMkJMil6Juc5m4JE9KA3hhpxJ9a1jLQ',
								20 => 'PVnzI3WXGfWhBMkJMil6JlrVNm3ddkGB8vl5gjEqM8Y',
								21 => 'PVnzI3WXGfWhBMkJMil6JkC2ml9CWAsRyStohviNeI0',
								22 => 'PVnzI3WXGfWhBMkJMil6Js3WVCpMx7zNZM8lvpDjGLk',
							);
							$this->imageReplay($fortune[rand(0,22)]);
							break;
						default:
							$this->textReplay('喵～有什么问题添加九猫客服微信：jiumaojia001 告诉我吧');
					}
				}
				switch($info['type']){
					case 3: //单图文
						$this->newsReplay(array($info));
						break;
				}
			}
		}
	}

	/**
	 * @brief 微信文字类型回复
	 * @param $content string 发送终端用户的文本消息
	 */
    public function textReplay($content){
    	$postObj = $this->msgObject;
		$replyContent = '<xml><ToUserName><![CDATA['.$postObj->FromUserName.']]></ToUserName><FromUserName><![CDATA['.$postObj->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.$content.']]></Content></xml>';
		die($replyContent);
    }
	
	/**
	 * 微信图片类型回复
	 * @param $content string 发送终端用户的图片编号
	 */
	public function imageReplay($content){
		$postObj      = $this->msgObject;
		$replyContent = '<xml><ToUserName><![CDATA['.$postObj->FromUserName.']]></ToUserName><FromUserName><![CDATA['.$postObj->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[image]]></MsgType><Image><MediaId><![CDATA['.$content.']]></MediaId></Image></xml>';
		die($replyContent);
	}
	
	/**
	 * 微信单图文类型回复
	 * @param $content string 发送终端用户的文本消息
	 */
	public function newsReplay($data){
		$postObj      = $this->msgObject;
		$replyContent = '<xml><ToUserName><![CDATA['.$postObj->FromUserName.']]></ToUserName><FromUserName><![CDATA['.$postObj->ToUserName.']]></FromUserName><CreateTime>'.time().'</CreateTime>
		<MsgType><![CDATA[news]]></MsgType><Content><![CDATA[]]></Content><ArticleCount>'.count($data).'</ArticleCount><Articles>';
		foreach($data as $k => $v){
			$replyContent .= '<item><Title><![CDATA['.$v['title'].']]></Title>
            <Description><![CDATA['.$v['content'].']]></Description>
            <PicUrl><![CDATA['.$v['pic'].']]></PicUrl>
            <Url><![CDATA['.$v['url'].']]></Url></item>';
		}
		$replyContent .= '</Articles><FuncFlag>0</FuncFlag></xml>';
		die($replyContent);
	}

	/**
	 * @brief 获取jsapi_ticket令牌
	 * @param $fresh 是否刷新令牌
	 */
    public function jsapiTicket($fresh = false){
		$cacheObj = new ICache();
		$jsapiTicketTime = $cacheObj->get('jsapiTicketTime');

		//延续使用
		if($jsapiTicketTime && time() - $jsapiTicketTime < self::$jsapiTicketTime && $fresh == false){
			$jsapiTicket = $cacheObj->get('jsapiTicket');
			if($jsapiTicket){
				return $jsapiTicket;
			}else{
				$cacheObj->del('jsapiTicketTime');
				return $this->jsapiTicket();
			}
		}else{
			//重新获取令牌
			$accessToken = $this->getAccessToken();
			$urlparam = array(
				'type=jsapi',
				'access_token='.$accessToken,
			);
			$apiUrl = self::SERVER_URL."/ticket/getticket?".join("&",$urlparam);
			$json   = file_get_contents($apiUrl,false,stream_context_create($this->sslConfig));
			$result = JSON::decode($json);
			if($result && isset($result['ticket']) && isset($result['expires_in'])){
				$cacheObj->set('jsapiTicketTime',time());
				$cacheObj->set('jsapiTicket',$result['ticket']);
				return $result['ticket'];
			}else{
				die($json);
			}
		}
    }

	/**
	 * @brief jsApi签名字符串
	 * @param $noncestr 随机字符串
	 * @param $time 时间
	 * @return 返回字符串签名
	 */
    public function jsApiSignature($noncestr,$time){
    	$jsapi_ticket = $this->jsapiTicket();
    	$url          = IUrl::getHost().IUrl::getUri();
    	$tmpArr       = array(
			"noncestr=".$noncestr,
			"jsapi_ticket=".$jsapi_ticket,
			"timestamp=".$time,
			"url=".$url,
    	);
		sort($tmpArr,SORT_STRING);
		$tmpStr = sha1(join("&",$tmpArr));
		return $tmpStr;
    }

	/**
	 * @brief 是否调用jsApiSDK
	 * @see http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
	 */
    public function jsApiSDK()
    {
    	if($this->config['wechat_jsApiSDK'] == 0)
    	{
			return;
    	}

		$appID    = $this->config['wechat_AppID'];
		$noncestr = rand(1000000,9999999);
		$time     = time();
		$signature= $this->jsApiSignature($noncestr,$time);

		if(IUrl::scheme() == 'https')
		{
echo <<< OEF
<script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
OEF;
		}
		else
		{
echo <<< OEF
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
OEF;
		}

echo <<< OEF
<script type="text/javascript">
wx.config({
	debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	appId: "{$appID}", // 必填，公众号的唯一标识
	timestamp: $time, // 必填，生成签名的时间戳
	nonceStr: "{$noncestr}", // 必填，生成签名的随机串
	signature: "{$signature}",// 必填，签名，见附录1
	jsApiList: [
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'onMenuShareQZone',
        'hideMenuItems',
        'showMenuItems',
        'hideAllNonBaseMenuItem',
        'showAllNonBaseMenuItem',
        'translateVoice',
        'startRecord',
        'stopRecord',
        'onVoiceRecordEnd',
        'playVoice',
        'onVoicePlayEnd',
        'pauseVoice',
        'stopVoice',
        'uploadVoice',
        'downloadVoice',
        'chooseImage',
        'previewImage',
        'uploadImage',
        'downloadImage',
        'getNetworkType',
        'openLocation',
        'getLocation',
        'hideOptionMenu',
        'showOptionMenu',
        'closeWindow',
        'scanQRCode',
        'chooseWXPay',
        'openProductSpecificView',
        'addCard',
        'chooseCard',
        'openCard'
	] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
});
</script>
OEF;
    }

	//插件注册
	public function reg()
	{
		if(IClient::isWechat() == true)
		{
			if($this->initConfig() == false)
			{
				throw new IException($this->getError());
			}
			plugin::reg("onCreateView",$this,"oauthLogin");
			plugin::reg("onFinishView",$this,"jsApiSDK");
		}

		plugin::reg("onBeforeCreateAction@block@wechat",function(){
			if($this->initConfig() == false)
			{
				throw new IException($this->getError());
			}
			self::controller()->wechat = function(){$this->response();};
		});

		plugin::reg("onBeforeCreateAction@plugins@wechat_menu",function(){
			self::controller()->wechat_menu = function(){$this->redirect("wechat_menu",array("wechatConfig" => $this->config));};
		});

		plugin::reg("onBeforeCreateAction@plugins@getWechatMenu",function(){
			self::controller()->getWechatMenu = function(){$this->getMenu();};
		});

		plugin::reg("onBeforeCreateAction@plugins@setWechatMenu",function(){
			self::controller()->setWechatMenu = function(){$this->setMenu();};
		});

		plugin::reg("onSystemMenuCreate",function(){
			$link = "/plugins/wechat_menu";
			Menu::$menu["插件"]["插件管理"][$link] = $this->name();
		});
	}

	//插件名称
	public static function name()
	{
		return "微信插件";
	}

	//插件描述
	public static function description()
	{
		return "微信免登录免注册，微信支付，公众账号菜单定制，js-sdk对接";
	}

	//插件默认配置
	public static function configName()
	{
		return 	array(
			'wechat_Token'     => array("name" => "Token(令牌)","type" => "text","pattern" => "required"),
			'wechat_AppID'     => array("name" => "AppID","type" => "text","pattern" => "required"),
			'wechat_AppSecret' => array("name" => "AppSecret","type" => "text","pattern" => "required"),
			'wechat_AutoLogin' => array("name" => "微信用户自动注册登录","type" => "select","value" => array("关闭" => 0, "开启" => 1)),
			'wechat_jsApiSDK'  => array("name" => "微信JS-SDK","type" => "select","value" => array("关闭" => 0, "开启" => 1)),
		);
	}

	/**
	 * @brief 进行oauth静默登录
	 */
	public function oauthLogin()
	{
		$openid = $this->getOpenId();
		if(!$openid || ($this->config['wechat_AutoLogin'] == 1 && plugin::trigger('getUser') == null) )
		{
			//oauth地址获取openid可以支付
			$url = $this->oauthUrl(IUrl::getUrl());

			header('location: '.$url);
		}
	}

    /**
     * User: chenbo
     * 添加用户扫码关注
     */
	public function add_qrcode_follow($scene_id,$open_id){
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
}