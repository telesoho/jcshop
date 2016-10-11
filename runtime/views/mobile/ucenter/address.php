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
    <div id="pageInfo" data-title="地址管理"></div>
<div class="address_edit_btn" id="address_edit_btn">编辑</div>
<section id="address_list" class="address_list">
	<ul>
		<!-- 这里两个class default 代表是否是默认地址 on 代表当前是否选中 -->
		<?php foreach($this->address as $key => $item){?>
		<li data-value="1" <?php if($item['is_default']==1){?>class="default"<?php }?>>
			<header class="address_list_header">
				<strong class="name"><?php echo isset($item['accept_name'])?$item['accept_name']:"";?></strong>
				<span class="phone"><?php echo isset($item['mobile'])?$item['mobile']:"";?></span>
			</header>
			<section class="address_list_section">
				<?php echo isset($this->areas[$item['province']])?$this->areas[$item['province']]:"";?> <?php echo isset($this->areas[$item['city']])?$this->areas[$item['city']]:"";?> <?php echo isset($this->areas[$item['area']])?$this->areas[$item['area']]:"";?> <?php echo isset($item['address'])?$item['address']:"";?>
			</section>
			<footer class="address_list_footer">
				<span class="address_btn" onclick='editAddress(<?php echo isset($item['id'])?$item['id']:"";?>)'>修改</span>
				<span class="address_btn" onclick="delModel({link:'<?php echo IUrl::creatUrl("/ucenter/address_del/id/".$item['id']."");?>'});">删除</span>
				<?php if($item['is_default']==1){?>
				<a class="address_btn" href="<?php echo IUrl::creatUrl("/ucenter/address_default/id/".$item['id']."/is_default/0");?>">取消默认</a>
				<?php }else{?>
				<a class="address_btn pink" href="<?php echo IUrl::creatUrl("/ucenter/address_default/id/".$item['id']."/is_default/1");?>">设为默认</a>
				<?php }?>
			</footer>
		</li>
		<?php }?>
	</ul>
</section>

<div class="btn_bottom">
	<input type="button" value="添加新地址" class="btn_submit pink" onclick="editAddress();" />
</div>

<script>
$(function(){
	// 内页隐藏页底导航
	hideNav();
	// 打开关闭编辑模式
	var btn = $("#address_edit_btn"),
		list = $("#address_list").find('li');
	btn.on('click',function(){
		var t = $(this),
			v = t.data('v');
		if(v==1){
			t.html("编辑").data('v', '0');
			list.children('footer').hide();
			list.find('.select').show();
		}else{
			t.html("取消").data('v', '1');
			// 这里把原来的 show()改成了 css控制
			list.children('footer').css('display','table');
			list.find('.select').hide();
		}
	});
});

//地址修改
function editAddress(addressId)
{
	art.dialog.open(creatUrl("block/address/id/"+addressId),
	{
		"id":"addressWindow",
		"title":"收货地址",
		"ok":function(iframeWin, topWin){
			var formObject = iframeWin.document.forms[0];
			if(formObject.onsubmit() === false)
			{
				alert("请正确填写各项信息");
				return false;
			}
			$.getJSON(formObject.action,$(formObject).serialize(),function(content){
				if(content.result == false)
				{
					alert(content.msg);
					return;
				}
				window.location.reload();
			});
			return false;
		},
		"okVal":"提交",
		"cancel":true,
		"cancelVal":"取消",
	});
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
