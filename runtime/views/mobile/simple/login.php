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
    
<div id="pageInfo" data-title="用户登录"></div>
<section class="login input_li">
	<form action='<?php echo IUrl::creatUrl("/simple/login_act");?>' method='post'>
		<input type="hidden" name='callback' value="<?php echo isset($callback)?$callback:"";?>" />
		<ul>
			<li>
				<input type="text" class="input_text" placeholder="请输入用户名" initmsg="验证通过" name="login_info" id="login_info" value="<?php echo ICookie::get('loginName');?>" pattern='required' alt='填写用户名或邮箱' />
			</li>
			<li>
				<input type="password" class="input_text" placeholder="密码" name="password" pattern='^\S{6,32}$' alt='填写密码' />
			</li>
			<li>
				<input type="submit" class="input_submit" value="立即登录">
			</li>
			<li>
				<a href="<?php echo IUrl::creatUrl("simple/reg");?>" class="link fl">快速注册</a>
				<a href="<?php echo IUrl::creatUrl("simple/find_password");?>" class="link fr">找回密码</a>
			</li>
			<?php $items=Api::run('getOauthList')?>
			<?php if($items){?>
			<li>
				<div class="other_login">
					<h3>第三方快捷登陆</h3>
					<?php foreach($items as $key => $item){?>
					<a href="javascript:oauthlogin('<?php echo isset($item['id'])?$item['id']:"";?>');"><img src='<?php echo IUrl::creatUrl("")."".$item['logo']."";?>' /></a>
					<?php }?>
				</div>
			</li>
			<?php }?>
		</ul>
	</form>
</section>
<script>
//DOM加载结束
$(function(){
	// 给底部导航加上当前样式
	$(".nav_user").addClass('on');
	$(".footer_login").hide();
});

//多平台登录
function oauthlogin(oauth_id){
	$.getJSON('<?php echo IUrl::creatUrl("/simple/oauth_login");?>',{"id":oauth_id,"callback":"<?php echo isset($callback)?$callback:"";?>"},function(content){
		if(content.isError == false)
		{
			window.location.href = content.url;
		}
		else
		{
			alert(content.message);
		}
	});
}

//下一步操作
function next_step(){
	var step_val = $('[name="next_step"]:checked').val();
	if(step_val == 'acount')
	{
		<?php $url = plugin::trigger('getCallback')."/tourist/yes"?>
		window.location.href = '<?php echo IUrl::creatUrl("".$url."");?>';
	}
	else if(step_val == 'reg')
	{
		window.location.href = '<?php echo IUrl::creatUrl("/simple/reg");?>';
	}
}
</script>

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
