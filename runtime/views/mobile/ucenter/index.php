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
    <div id="pageInfo" data-title="个人中心"></div>
<section class="user_main_info">
    <ol>
        <li>用户名：<?php echo isset($this->user['username'])?$this->user['username']:"";?></li>
        <li>余额：<em>￥<?php echo isset($user['balance'])?$user['balance']:"";?></em></li>
        <li>积分：<em><?php echo isset($user['point'])?$user['point']:"";?></em></li>
        <li>代金券：<em><?php echo isset($propData['prop_num'])?$propData['prop_num']:"";?> 张</em></li>
    </ol>
</section>
<?php $icoConfig = array(
    "我的订单"   => "icon-pencil",
    "我的积分"   => "icon-github-alt",
    "我的代金券" => "icon-tag",
    "退款申请"   => "icon-file",
    "站点建议"   => "icon-book",
    "商品咨询"   => "icon-comment",
    "商品评价"   => "icon-thumbs-up",
    "短信息"     => "icon-file",
    "收藏夹"     => "icon-flag",
    "帐户余额"   => "icon-jpy",
    "在线充值"   => "icon-money",
    "地址管理"   => "icon-map-marker",
    "个人资料"   => "icon-file-alt",
    "修改密码"   => "icon-lock",
)?>
<nav class="user_main_nav">
    <h3>
        <i class="icon-user"></i>
        个人中心
    </h3>
    <ul>
        <?php foreach(menuUcenter::init() as $key => $item){?>
        <?php foreach($item as $moreKey => $moreValue){?>
        <li><a href="<?php echo IUrl::creatUrl("".$moreKey."");?>"><i class="<?php echo $icoConfig[$moreValue];?>"></i><?php echo isset($moreValue)?$moreValue:"";?><u class="icon-chevron-right"></u></a></li>
        <?php }?>
        <?php }?>
    </ul>
</nav>
<a href="<?php echo IUrl::creatUrl("/simple/logout");?>" class="home_main_logout">退出登录</a>
<script>
$(function() {
    // 设置首页导航为当前
    $(".nav_user").addClass('on');
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
