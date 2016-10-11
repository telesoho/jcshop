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
    <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script> <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script> <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" /> <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" /> <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/template-native.js"></script>
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
    <?php $breadGuide = goods_class::catRecursion($this->catId)?>
<?php $goodsObj = search_goods::find(array('category_extend' => $this->childId),20);$resultData = $goodsObj->find()?>
<div id="pageInfo" data-title="<?php echo isset($this->catRow['name'])?$this->catRow['name']:"";?>"></div>

<?php if($resultData){?>
<aside class="goods_sort">
	<?php foreach(search_goods::getOrderType() as $key => $item){?>
	<span class="sort_btn" onclick="gourl('<?php echo search_goods::searchUrl(array('order','by'),array($key,search_goods::getOrderBy($key)));?>')"><?php echo isset($item)?$item:"";?></span>
	<?php }?>
</aside>
<section class="goods_list">
	<ul>
		<?php foreach($resultData as $key => $item){?>
		<li>
			<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>">
				<i class="photo"><img class="goods_photo" src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/200/h/200");?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>"></i>
				<h3 class="name"><strong><?php echo isset($item['name'])?$item['name']:"";?></strong></h3>
				<del class="old_price">￥<?php echo isset($item['market_price'])?$item['market_price']:"";?></del>
				<em class="price">￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></em>
			</a>
		</li>
		<?php }?>
	</ul>
</section>
<?php echo $goodsObj->getPageBar();?>
<?php }else{?>
<section class="nodata">商品进货中，请稍后关注上架情况</section>
<?php }?>

<script>
$(function(){
	// 隐藏底部导航
	hideNav();
	// 切换导航样式
	var order = getUrlParam("order");
	var by = getUrlParam("by");
	console.log(by)
	var sortBtn = $(".goods_sort").children('.sort_btn');
	// sortBtn.eq(2).append('<i>');
	if(order=="sale"){
		if (by=="desc") {sortBtn.eq(0).addClass('on').append('<i class="icon-angle-down"></i>')}
		else {sortBtn.eq(0).addClass('on').append('<i class="icon-angle-up"></i>');}

	}else if (order=="cpoint"){
		if (by=="desc") {sortBtn.eq(1).addClass('on').append('<i class="icon-angle-down"></i>')}
		else {sortBtn.eq(1).addClass('on').append('<i class="icon-angle-up"></i>');}
	}else if(order=="price"){
		if (by=="desc") {sortBtn.eq(2).addClass('on').append('<i class="icon-angle-down"></i>')}
		else {sortBtn.eq(2).addClass('on').append('<i class="icon-angle-up"></i>');}
	}else if(order=="new"){
		if (by=="desc") {sortBtn.eq(3).addClass('on').append('<i class="icon-angle-down"></i>')}
		else {sortBtn.eq(3).addClass('on').append('<i class="icon-angle-up"></i>');}
	}else{
		sortBtn.eq(0).addClass('on');
	};
});
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
