<?php
/**
 * @copyright (c) 2011 aircheng.com
 * @file controller_class.php
 * @brief 控制器类,控制action动作,渲染页面
 * @author chendeshan
 * @date 2010-12-16
 * @update 2016/4/13 18:29:42
 * @version 4.4
 */

/**
 * @class IController
 * @brief 控制器
 */
class IController extends IControllerBase
{
	public $extend = '.html';                          //模板扩展名
	public $theme;                                     //主题方案名称
	public $skin;                                      //皮肤方案名称
	public $layout;                                    //布局方案名称
	public $defaultActions = array();                  //默认action对应关系,array(ID => 类名或对象引用)
	public $error          = array();                  //错误信息内容

	protected $app;                                    //隶属于APP的对象
	protected $ctrlId;                                 //控制器ID标识符
	protected $defaultLayoutPath = 'layouts';          //默认布局目录

	private $action;                                   //当前action对象
	private $defaultAction       = 'index';            //默认执行的action动作
	private $renderData          = array();            //渲染的数据
	protected $errorInfo = array(); //接口错误编码

	/**
	 * @brief 构造函数
	 * @param string $app    上一级APP对象
	 * @param string $ctrlId 控制器ID标识符
	 */
	public function __construct($app,$controllerId)
	{
		$this->app    = $app;
		$this->ctrlId = $controllerId;
	}

	/**
	 * @brief 生成验证码
	 * @return image图像
	 */
	public function getCaptcha()
	{
		//清空布局
		$this->layout = '';

		//配置参数
		$width      = IReq::get('w') ? IReq::get('w') : 130;
		$height     = IReq::get('h') ? IReq::get('h') : 45;
		$wordLength = IReq::get('l') ? IReq::get('l') : 5;
		$fontSize   = IReq::get('s') ? IReq::get('s') : 25;

		//创建验证码
		$ValidateObj = new Captcha();
		$ValidateObj->width  = $width;
		$ValidateObj->height = $height;
		$ValidateObj->maxWordLength = $wordLength;
		$ValidateObj->minWordLength = $wordLength;
		$ValidateObj->fontSize      = $fontSize;
		$ValidateObj->CreateImage($text);

		//设置验证码
		ISafe::set('captcha',$text);
	}

	/**
	 * @brief 获取当前控制器的id标识符
	 * @return 控制器的id标识符
	 */
	public function getId()
	{
		return $this->ctrlId;
	}

	/**
 * @brief 初始化controller对象
 */
	public function init()
	{
	}
	
	/**
	 * 运行前的初始化
	 */
	public function initRun(){
		//获取接口错误编码
		$this->errorInfo = apiReturn::getErrorInfo();
		
		/* 接受参数 */
		$param = array(
			'token' => IFilter::act(IReq::get('token')),
		);
		
		if(!empty($param['token'])){
			//数据库验证
			$allow_time = (new Config('jmj_config'))->token_allow_time; //token过期时间
			$modelToken = new IModel('user_token');
			$info       = $modelToken->getObj('token="'.$param['token'].'"');

			if(empty($info) || empty($info['play_time']) || $info['play_time']>time() || ($info['play_time']+$allow_time)<time()){
				exit(json_encode(apiReturn::go('001001'))); //令牌已过期，需重新登陆
			}
			//更新操作时间
			$modelToken->setData(array('play_time' => time(),));
			$modelToken->update('user_id='.$info['user_id']);

			$modelUser  = new IModel('user');
			$infoUser   = $modelUser->getObj('id='.$info['user_id'], 'id,username,password,head_ico');
			$this->user = array(
				'username' => $infoUser['username'],
				'user_pwd' => $infoUser['password'],
				'user_id'  => $infoUser['id'],
				'head_ico' => $infoUser['head_ico'],
			);
		}
	}

	/**
	 * @brief 获取当前action对象
	 * @return object 返回当前action对象
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @brief 执行action方法
	 * @param string $actionId 动作actionID
	 */
	public function run($actionId = '')
	{
		//开启缓冲区
		ob_start();

		header("content-type:text/html;charset=".$this->app->charset);

		//初始化控制器
		$this->init();

		//创建action对象
		IInterceptor::run("onBeforeCreateAction",$this,$actionId);
		$actionObj = $this->createAction($actionId);
		IInterceptor::run("onCreateAction",$this,$actionObj);
		//运行前的初始化
		$this->initRun();
		$actionObj->run();
		IInterceptor::run("onFinishAction",$this,$actionObj);
		//处理缓冲区
		ob_end_flush();
	}

	/**
	 * @brief 创建action动作
	 * @param string $actionId 动作actionID
	 * @return object 返回action动作对象
	 */
	public function createAction($actionId = '')
	{
		//获取action的标识符
		$actionId = $actionId ? $actionId : $this->defaultAction;

		/*创建action对象流程
		 *1,配置动作
		 *2,控制器内部动作
		 *3,视图动作*/

		//1,配置动作
		if(isset($this->defaultActions[$actionId]))
		{
			//自定义类名
			$class = $this->defaultActions[$actionId];
			$this->action = is_object($class) ? $class : new $class($this,$actionId);
		}
		//2,控制器内部动作
		else if(method_exists($this,$actionId) || is_callable($this->$actionId))
		{
			$this->action = new IInlineAction($this,$actionId);
		}
		//3,视图动作
		else
		{
			$this->action = new IViewAction($this,$actionId);
		}
		return $this->action;
	}

	/**
	 * @brief 渲染
	 * @param string          $view   要渲染的视图文件
	 * @param string or array $data   要渲染的数据
	 * @param boolean         $return 是否直接返回模板视图
	 * @return 渲染出来的数据
	 */
	public function render($view,$data=null,$return=false)
	{
		$output = $this->renderView($view,$data,$return);
		if($output === false)
		{
			return false;
		}

		if($return)
		{
			return $output;
		}
		echo $output;
	}

	/**
	 * @brief 渲染出静态文字
	 * @param string $text 要渲染的静态数据
	 * @param bool $return 输出方式 值: true:返回; false:直接输出;
	 * @return string 静态数据
	 */
	public function renderText($text,$return=false)
	{
		$text = $this->tagResolve($text);
		if($return)
		{
			return $text;
		}
		echo $text;
	}

	/**
	 * @brief 获取当前主题下的皮肤路径
	 * @return string 皮肤路径
	 */
	public function getSkinPath()
	{
		$skin = $this->getSkinDir();
		if($skin)
		{
			return $this->getViewPath().$this->app->defaultSkinDir.DIRECTORY_SEPARATOR.$skin.DIRECTORY_SEPARATOR;
		}
		return $this->getViewPath().$this->app->defaultSkinDir.DIRECTORY_SEPARATOR;
	}

	/**
	 * @brief 获取layout文件路径(无扩展名)
	 * @return string layout路径
	 */
	public function getLayoutFile()
	{
		if(!$this->layout)
			return false;

		return $this->getViewPath().$this->defaultLayoutPath.DIRECTORY_SEPARATOR.$this->layout;
	}

	/**
	 * @brief 获取当前主题下的模板路径
	 * @return string 模板路径
	 */
	public function getViewPath()
	{
		$theme = $this->getThemeDir();
		if($theme)
		{
			return $this->app->getViewPath().$theme.DIRECTORY_SEPARATOR;
		}
		return $this->app->getViewPath();
	}

	/**
	 * @brief 取得视图文件路径(无扩展名)
	 * @param string $viewName 视图文件名
	 * @return string 视图文件路径
	 */
	public function getViewFile($viewName)
	{
		return $this->getViewPath().strtolower($this->ctrlId).DIRECTORY_SEPARATOR.$viewName;
	}

    /**
     * @brief 获取当前控制器所属的theme方案
     *        在App的config中可以配置theme => array('客户端' => array("主题方案" => "皮肤方案"))
     * @return String theme方案名称
     */
	public function getThemeDir()
	{
		if(!$this->theme)
		{
			$client    = $this->app->clientType;
			$themeList = isset($this->app->config['theme']) ? $this->app->config['theme'] : null;
			if($themeList && isset($themeList[$client]) && is_array($themeList[$client]) && $themeList[$client])
			{
				foreach($themeList[$client] as $theme => $skin)
				{
					$tryPath = $this->app->getViewPath().$theme.DIRECTORY_SEPARATOR.strtolower($this->getId());
					if(is_dir($tryPath))
					{
						$this->theme = $theme;
						break;
					}
				}
			}
		}
		return $this->theme;
	}

    /**
     * @brief 获取当前控制器所属的skin方案
     *        在App的config中可以配置theme => array('客户端' => array("主题方案" => "皮肤方案"))
     * @return String skin方案名称
     */
	public function getSkinDir()
	{
		if(!$this->skin)
		{
			$theme = $this->getThemeDir();
			if($theme)
			{
				$client    = $this->app->clientType;
				$themeList = isset($this->app->config['theme']) ? $this->app->config['theme'] : null;
				$this->skin = $themeList[$client][$theme];
			}
		}
		return $this->skin;
	}

	/**
	 * @brief 获取WEB模板路径
	 * @return string 返回WEB路径格式
	 */
	public function getWebViewPath()
	{
		return $this->app->getWebViewPath().$this->getThemeDir()."/";
	}

	/**
	 * @brief 获取WEB皮肤路径
	 * @return string 返回WEB路径格式
	 */
	public function getWebSkinPath()
	{
		return $this->getWebViewPath().$this->app->defaultSkinDir."/".$this->getSkinDir()."/";
	}

	/**
	 * @brief 获取要渲染的数据
	 * @return array 渲染的数据
	 */
	public function getRenderData()
	{
		return $this->renderData;
	}

	/**
	 * @brief 设置要渲染的数据
	 * @param array $data 渲染的数据数组
	 */
	public function setRenderData($data)
	{
		if(is_array($data))
			$this->renderData = array_merge($this->renderData,$data);
	}

	/**
	 * @brief 视图重定位
	 * @param string $next     下一步要执行的动作或者路径名,注:当首字符为'/'时，则支持跨控制器操作
	 * @param bool   $location 是否重定位 true:是 false:否
	 */
	public function redirect($nextUrl, $location = true, $data = null)
	{
		//绝对地址直接跳转
		if(strpos($nextUrl,'http') === 0)
		{
			header('location: '.$nextUrl);
		}
		//伪静态路径
		else
		{
			//获取当前的action动作
			$actionId = IReq::get('action');
			if($actionId === null)
			{
				$actionId = $this->defaultAction;
			}

			//分析$nextAction 支持跨控制器跳转
			$nextUrl = strtr($nextUrl,'\\','/');

			//不跨越控制器redirect
			if($nextUrl[0] != '/')
			{
				//重定跳转定向
				if($actionId!=$nextUrl && $location == true)
				{
					$locationUrl = IUrl::creatUrl('/'.$this->ctrlId.'/'.$nextUrl);
					header('location: '.$locationUrl);
				}
				//非重定向,直接引入本控制器内的视图模板
				else
				{
					$this->action = new IViewAction($this,$nextUrl);
					$this->action->run();
				}
			}
			//跨越控制器redirect
			else
			{
				$urlArray   = explode('/',$nextUrl,4);
				$ctrlId     = isset($urlArray[1]) ? $urlArray[1] : '';
				$nextAction = isset($urlArray[2]) ? $urlArray[2] : '';

				//url参数
				if(isset($urlArray[3]))
				{
					$nextAction .= '/'.$urlArray[3];
				}
				$locationUrl = IUrl::creatUrl('/'.$ctrlId.'/'.$nextAction);
				header('location: '.$locationUrl);
			}
		}
	}

	/**
	 * @brief 设置错误信息
	 * @param string $errorMsg 错误信息内容
	 * @param string $errorNo  错误信息编号
	 */
	public function setError($errorMsg,$errorNo = "")
	{
		if($errorNo)
		{
			$this->error[$errorNo] = $errorMsg;
		}
		else
		{
			$this->error[] = $errorMsg;
		}
	}

	/**
	 * @brief 获取单条错误信息
	 * @return string 错误信息内容
	 */
	public function getError()
	{
		return $this->error ? current($this->error) : "";
	}

	/**
	 * @brief 获取全部错误信息
	 * @return array 全部错误信息内容
	 */
	public function getAllError()
	{
		return $this->error;
	}
	
	
	/**
	 * 数据安全校验
	 * @param array $PostData
	 * @param array $Fields
	 * @return array
	 */
	protected function checkData($param = array()){
		$postData = array_merge($_POST, $_GET);
		if(isset($postData['getapiinfo']) && $postData['getapiinfo']==='1') $this->returnJson(array('code' => 0, 'msg' => 'ok', 'data' => array('param'=>$param,'error'=>$this->errorInfo)));
		//获取接口信息
		$backData = array(); //返回参数
		foreach($param as $k => $v){
			$backData[$v[0]] = IFilter::act(IReq::get($v[0]), $v[1]);
			//不能为空
			if($v[2]==1 && empty($backData[$v[0]]))
				$this->returnJson(array('code' => '001003', 'msg' => $v[3].'不能为空'));
		}
		return $backData;
	}
	
	/**
	 * 返回json数据
	 */
	public function returnJson($data = array()){
		/* 参数 */
		$base     = array('code' => '-1', 'msg' => '系统错误', 'time' => date('Y-m-d H:i:s', time()), 'apiurl' => IUrl::getUrl(), 'explain' => '', 'data' => '');
		$backData = array_merge($base, $data);
		/* 生产环境 */
		if((new Config('jmj_config'))->production==true){
			//删除接口说明
			unset($backData['explain']);
			//记录接口调用日志 TODO
		}
		/* 返回参数 */
		die(json_encode($backData));
	}
	
	/**
	 * 生成用户令牌
	 * @param  int $uid 用户ID
	 * @return string 用户令牌
	 */
	protected function tokenCreate($uid){
		//用户是否存在
		$modelUser = new IModel('user');
		$infoUser  = $modelUser->getObj('id='.$uid);
		if(empty($infoUser)) $this->returnJson(array('code' => '001003', 'msg' => $this->errorInfo['001003']));
		
		//令牌生成
		/* 生成令牌 */
		$data = array(
			'token'       => md5($uid.time().(new Config('jmj_config'))->auth_key_data), //生成令牌
			'play_time'   => time(), //操作时间
			'update_time' => time(), //更新时间
		);
		/* 写入数据 */
		$modelToken = new IModel('user_token');
		$infoToken  = $modelToken->getObj('user_id='.$uid);
		if(!empty($infoToken)){
			//更新Token
			$modelToken->setData(array_merge($data, array(
				'nums' => 'nums+1', //登陆次数
			)));
			$rel = $modelToken->update('user_id='.$uid, array('nums'));
			if($rel>0) return $data['token'];
		}else{
			//创建Token
			$modelToken->setData(array_merge($data, array(
				'user_id'     => $uid,
				'nums'        => 1, //登陆次数
				'create_time' => time(),
			)));
			$rel = $modelToken->add();
			return $data['token'];
		}
		$this->returnJson(array('code' => '008002', 'msg' => $this->errorInfo['008002']));
	}
	
	/**
	 * 令牌合法性验证，并返回uid
	 * @param  string $token 用户令牌
	 * @return int
	 */
	protected function tokenCheck(){
		/* 接受参数 */
		$param = array(
			'token' => IFilter::act(IReq::get('token')),
		);
		
		if(!empty($param['token'])){
			//数据库验证
			$allow_time = (new Config('jmj_config'))->token_allow_time; //token过期时间
			$modelToken = new IModel('user_token');
			$info       = $modelToken->getObj('token="'.$param['token'].'"');
			
			if(empty($info) || empty($info['play_time']) || $info['play_time']>time() || ($info['play_time']+$allow_time)<time()){
				$this->returnJson(array('code'=>'001001','msg'=>$this->errorInfo['001001'])); //令牌已过期，需重新登陆
			}
			//更新操作时间
			$modelToken->setData(array('play_time' => time(),));
			$modelToken->update('user_id='.$info['user_id']);
			
			$modelUser  = new IModel('user');
			$infoUser   = $modelUser->getObj('id='.$info['user_id'], 'id,username,password,head_ico');
			$this->user = array(
				'username' => $infoUser['username'],
				'user_pwd' => $infoUser['password'],
				'user_id'  => $infoUser['id'],
				'head_ico' => $infoUser['head_ico'],
			);
			return $this->user['user_id'];
		}else{
			return isset($this->user['user_id'])&&!empty($this->user['user_id']) ? $this->user['user_id'] : $this->returnJson(array('code' => '001001', 'msg' => $this->errorInfo['001001']));
		}
	}
	
}
