<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>dsdhilujhklj</title>
	<title>dsdhilujhklj</title>
	<link type="image/x-icon" href="<?php echo IUrl::creatUrl("")."favicon.ico";?>" rel="icon">
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/mui.css";?>" />
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/common.css";?>" />
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/icons-extra.css";?>" />
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/app/jiumao.css";?>" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
	<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/mui.js";?>"></script>

</head>
<body class="index">
<!--loading页开始-->
	<div id="loading">
		<div class="spinner">
			<div class="spinner-container container1">
				<div class="circle1"></div>
				<div class="circle2"></div>
				<div class="circle3"></div>
				<div class="circle4"></div>
			</div>
			<div class="spinner-container container2">
				<div class="circle1"></div>
				<div class="circle2"></div>
				<div class="circle3"></div>
				<div class="circle4"></div>
			</div>
			<div class="spinner-container container3">
				<div class="circle1"></div>
				<div class="circle2"></div>
				<div class="circle3"></div>
				<div class="circle4"></div>
			</div>
		</div>
	</div>
	<!--loading页结束-->


	<?php $msg = IReq::get('msg') ? IReq::get('msg') : '发生错误'?>
<div class="error wrapper clearfix">
	<table class="form_table prompt_3 f_l">
		<col width="250px" />
		<col />
		<tr>
			<th valign="top"><img src="<?php echo $this->getWebSkinPath()."images/front/cry.gif";?>" width="122" height="98" /></th>
			<td>
				<p class="mt_10"><strong class="f14 gray"><?php echo htmlspecialchars($msg,ENT_QUOTES);?></strong></p>
				<p class="gray">您可以：</p>
				<p class="gray">1.检查刚才的输入</p>
				<p class="gray">2.到<a class="blue" href="<?php echo IUrl::creatUrl("/site/help_list");?>">帮助中心</a>寻求帮助</p>
				<p class="gray">3.去其他地方逛逛：<a href='javascript:void(0)' class='blue' onclick='window.history.go(-1);'>返回上一级操作</a>|<a class="blue" href="<?php echo IUrl::creatUrl("");?>">网站首页</a>|<a class="blue" href="<?php echo IUrl::creatUrl("/ucenter/order");?>">我的订单</a>|<a class="blue" href="<?php echo IUrl::creatUrl("/simple/cart");?>">我的购物车</a></p>
			</td>
		</tr>
	</table>
</div>



	<!--底部选项卡-->
	<footer>
		<nav class="mui-bar mui-bar-tab">
			<div class="mui-content">
				<a id="defaultTab" class="mui-tab-item mui-active" href="index.html">
					<span class="mui-icon mui-icon-home"></span>
					<span class="mui-tab-label">首页</span>
				</a>
				<a class="mui-tab-item" href="#">
					<span class="mui-icon mui-icon-list"></span>
					<span class="mui-tab-label">分类</span>
				</a>
				<a class="mui-tab-item" href="shopcar.html">
						<span class="mui-icon mui-icon-extra mui-icon-extra-cart">
							<span class="mui-badge">0</span>
							</span>
					<span class="mui-tab-label">购物车</span>
				</a>
				<a class="mui-tab-item" href="#">
					<span class="mui-icon mui-icon-contact"></span>
					<span class="mui-tab-label">个人中心</span>
				</a>
			</div>
		</nav>
	</footer>
<script type='text/javascript' src='<?php echo $this->getWebViewPath()."javascript/time.js";?>'></script>
<script type='text/javascript' src='<?php echo $this->getWebViewPath()."javascript/lazyload.js";?>'></script>
</body>
</html>
