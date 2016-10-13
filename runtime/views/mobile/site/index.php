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
    <script src="<?php echo $this->getWebViewPath()."javascript/mui.js";?>"></script>
    <script src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
    <script src='<?php echo $this->getWebViewPath()."javascript/site.js";?>'></script>
    <script src='<?php echo $this->getWebViewPath()."javascript/mobile.js";?>'></script>
    <link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."style/style.css";?>">
    <!--<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/mui.css";?>">-->
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
    <script src="<?php echo $this->getWebViewPath()."javascript/jquery.slider.js";?>"></script>
<!--幻灯片 开始-->
<div class="home_banner">
    <?php if($this->index_slide){?>
    <ul>
        <?php foreach($this->index_slide as $key => $item){?>
        <li>
            <a href="<?php echo isset($item['url'])?$item['url']:"";?>"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."");?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>"></a>
        </li>
        <?php }?>
    </ul>
    <?php }?>
</div>
<!-- 首页菜单 -->
<nav class="home_nav">
    <ul>
        <li class="map"><a href="<?php echo IUrl::creatUrl("site/sitemap");?>"><i class="icon-th-list"></i><span>全部分类</span></a></li>
        <li class="cart"><a href="<?php echo IUrl::creatUrl("simple/cart");?>"><i class="icon-shopping-cart"></i><span>购物车</span></a></li>
        <li class="groupon"><a href="<?php echo IUrl::creatUrl("site/groupon");?>"><i class="icon-legal"></i><span>今日团购</span></a></li>
        <li class="favorite"><a href="<?php echo IUrl::creatUrl("ucenter/favorite");?>"><i class="icon-star"></i><span>我的收藏</span></a></li>
    </ul>
</nav>
<?php echo Ad::show("首页顶部通栏100%*120(mobile)");?>
<!-- 首页推荐商品列表 -->
<h2 class="home_title"><i class="icon-gift"></i><strong>商品推荐</strong></h2>
<section class="home_goods">
    <ul>
        <?php foreach(Api::run('getCommendRecom') as $key => $item){?>
        <li>
            <a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>">
                <img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/300/h/300");?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>">
                <span><?php echo isset($item['name'])?$item['name']:"";?></span>
            </a>
        </li>
        <?php }?>
    </ul>
</section>

<?php echo Ad::show("首页中部通栏100%*200(mobile)");?>

<!-- 首页顶级栏目分类 -->
<h2 class="home_title"><i class="icon-list"></i><strong>畅销商品分类</strong></h2>
<nav class="home_goods_nav">
    <ul>
        <?php foreach(Api::run('getCategoryListTop') as $key => $first){?>
        <li><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$first['id']."");?>"><?php echo isset($first['name'])?$first['name']:"";?></a><i class="icon-chevron-right"></i></li>
        <?php }?>
    </ul>
</nav>

<section class="home_slogan">
    <ol>
        <li>
            <i class="icon-truck"></i>
            <p>
                <strong>免费送货与退货</strong>
                <br>
                <span>所有订单超过200免费送货。</span>
            </p>
        </li>
        <li>
            <i class="icon-jpy"></i>
            <p>
                <strong>退款保证</strong>
                <br>
                <span>100%退款保证。</span>
            </p>
        </li>
        <li>
            <i class="icon-phone"></i>
            <p>
                <strong>在线支持 24/7</strong>
                <br>
                <span>客服7*24小时在线。</span>
            </p>
        </li>
    </ol>
</section>

<script>
$(function() {
    // 设置首页导航为当前
    $(".nav_home").addClass('on');
    // 设置焦点图
    $(".home_banner").MobileSlider({
        width: 720,
        height: 360
    });
})
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
