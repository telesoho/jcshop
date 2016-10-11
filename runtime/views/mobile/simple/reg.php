<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <title><?php echo $this->_siteConfig->name;?></title>
    <link type="image/x-icon" href="<?php echo IUrl::creatUrl("")."favicon.ico";?>" rel="icon">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="apple-touch-icon-precomposed" href="<?php echo $this->getWebSkinPath()."image/logo.gif";?>">
    <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script> <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script> <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" /> <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" /> <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
    <script src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
    <script src='<?php echo $this->getWebViewPath()."javascript/site.js";?>'></script>
    <script src='<?php echo $this->getWebViewPath()."javascript/mobile.js";?>'></script>
    <link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."style/style.css";?>">
</head>

<body>
    <!-- 顶部通栏 -->
    <header class="header_box">
        <div class="header">
            <?php if(IWeb::$app->getController()->getId() == 'site' && IWeb::$app->getController()->getAction()->getId() == 'index'){?>
            <div class="header_home"><i class="icon-home"></i></div>
            <?php }else{?>
            <div class="header_back" onclick="window.history.back();"><i class="icon-chevron-left"></i></div>
            <?php }?>
            <h1 id="page_title" class="page_title"><?php echo $this->_siteConfig->name;?></h1>
            <div class="header_so_btn" onclick="$('.header_search').toggle();"><i class="icon-search"></i></div>
        </div>
    </header>
    <!-- 顶部搜索 -->
    <section class="header_search">
        <form method='get' action="<?php echo IUrl::creatUrl("/");?>">
            <input type='hidden' name='controller' value='site'>
            <input type='hidden' name='action' value='search_list'>
            <input class="keywords" type="text" name='word' autocomplete="off" placeholder="请输入关键词...">
            <input class="submit" type="submit" value="搜索">
        </form>
    </section>
    <!-- 引入内页 -->
    <div id="pageInfo" data-title="用户注册"></div>
<section class="reg input_li">
	<div class="form">
		<form action='<?php echo IUrl::creatUrl("/simple/reg_act");?>' method='post'>
			<input type="hidden" name='callback' value="<?php echo isset($callback)?$callback:"";?>" />
			<ul>
				
				<?php if($this->_siteConfig->reg_option == 1){?>
				<li>
					<input class='input_text' placeholder="请输入您的邮箱" initmsg="" type="text" name='email' pattern="email" alt="填写正确格式" />
				</li>
				<?php }?>
				<?php if($this->_siteConfig->reg_option == 3){?>
				<li>
					<input class="input_text" type="text" placeholder="请输入您的手机" initmsg="" name='mobile' pattern="mobi" alt="手机格式不正确" />
				</li>
				<li>
					<input class="input_text" type="text" placeholder="请输入验证码" initmsg="" name='mobile_code' pattern="^\w{4,6}$" alt="验证码不正确" />
					<input class="input_button" type="button" onclick="_sendMobileCode(this);" value="获取验证码">
				</li>
				<?php }?>
				<li>
					<input class='input_text' placeholder="请输入您的用户名" initmsg="" type="text" name='username' pattern="^[\w\u0391-\uFFE5]{2,20}$" alt="填写2-20个字符" />
				</li>
				<li>
					<input class='input_text' placeholder="请输入您的密码" type="password" name='password' pattern="^\S{6,32}$" bind='repassword' alt='填写6-32个字符' />
				</li>
				<li>
					<input type="password" class='input_text' placeholder="确认密码" name='repassword' pattern="^\S{6,32}$" bind='password' alt='重复上面密码' />
				</li>
				<li>
					<input type='text' class='input_text input_captcha' placeholder="请输入验证码" name='captcha' pattern='^\w{5,10}$' alt='填写验证码' />
					<img src='<?php echo IUrl::creatUrl("/simple/getCaptcha");?>' class="captchaImg" id="captchaImg" onclick="changeCaptcha()" />
				</li>
				<li>
					<input type="submit" class="input_submit" value="立即注册">
				</li>
			</ul>
		</form>
	</div>
</section>

<script type='text/javascript'>
$(function(){
	// 给底部导航加上当前样式
	$(".nav_user").addClass('on');
	$(".footer_login").hide();
});
</SCRIPT>
    <!-- 会员登陆与否显示不同内容 -->
    <?php if($this->user){?>
    <?php }else{?>
    <section class="footer_login">
        <a class="login" href="<?php echo IUrl::creatUrl("simple/login");?>">登录</a>
        <a class="reg" href="<?php echo IUrl::creatUrl("simple/reg");?>">注册</a>
    </section>
    <?php }?>
    <!--底部菜单-->
    <nav class="footer_nav">
        <ul>
            <li class="nav_home"><a href="<?php echo IUrl::creatUrl("/");?>"><i class="icon-home"></i><span>首页</span></a></li>
            <li class="nav_cart"><a href="<?php echo IUrl::creatUrl("simple/cart");?>"><i class="icon-shopping-cart"></i><span>购物车</span></a></li>
            <li class="nav_user"><a href="<?php echo IUrl::creatUrl("/ucenter/index");?>"><i class="icon-user"></i><span>个人中心</span></a></li>
            <li class="nav_map"><a href="<?php echo IUrl::creatUrl("site/sitemap");?>"><i class="icon-reorder"></i><span>分类</span></a></li>
        </ul>
    </nav>
</body>

</html>
