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


	<!--头部-->
<header class="homeHeader mui-content">
    <div class="nav-header ">
        <div class="logo"><img dataimg="img/blm/bolome_logo1.png" alt="该图片无法显示" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif"> </div>
        <div class="mui-input-row mui-search">
            <input type="search" class="mui-input-clear" placeholder="搜索品牌">
        </div>
        <div class="modal"><span class="mui-icon mui-icon-bars"></span></div>
    </div>
    <div class="nav-body">
        <ul class="nav-menu">
            <li class="nev-menu-item"><a href="#" class="nav-body-active">首页</a></li>
            <li class="nev-menu-item"><a href="#">新人享</a></li>
            <li class="nev-menu-item"><a href="#">热销榜</a></li>
            <li class="nev-menu-item"><a href="#">新品购</a></li>
        </ul>
    </div>
</header>
<!--下拉刷新容器-->
<div id="pullrefresh" class="mui-content mui-scroll-wrapper" style="margin-top:8rem;margin-bottom:30px">
    <div class="mui-scroll">
        <!--数据列表-->
        <ul class="mui-table-view mui-table-view-chevron" style="background:#eee">
            <!--轮播图开始-->
            <div id="slider" class="mui-slider" >
                <div class="mui-slider-group mui-slider-loop">
                    <!-- 额外增加的一个节点(循环轮播：第一个节点是最后一张轮播) -->
                    <div class="mui-slider-item mui-slider-item-duplicate">
                        <a href="#">
                            <img dataimg="skin/default/images/blm/lunbo2.jpg" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </a>
                    </div>
                    <!-- 第一张 -->
                    <div class="mui-slider-item">
                        <a href="#">
                            <img dataimg="skin/default/images/blm/lunbo1.jpg" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </a>
                    </div>
                    <!-- 第二张 -->
                    <div class="mui-slider-item">
                        <a href="#">
                            <img dataimg="skin/default/images/blm/lunbo2.jpg" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </a>
                    </div>
                    <!-- 第三张 -->
                    <div class="mui-slider-item">
                        <a href="#">
                            <img dataimg="skin/default/images/blm/lunbo1.jpg" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </a>
                    </div>
                    <!-- 第四张 -->
                    <div class="mui-slider-item">
                        <a href="#">
                            <img dataimg="skin/default/images/blm/lunbo2.jpg" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </a>
                    </div>
                    <!-- 额外增加的一个节点(循环轮播：最后一个节点是第一张轮播) -->
                    <div class="mui-slider-item mui-slider-item-duplicate">
                        <a href="#">
                            <img dataimg="skin/default/images/blm/lunbo2.jpg" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </a>
                    </div>
                </div>
                <div class="mui-slider-indicator">
                    <div class="mui-indicator mui-active"></div>
                    <div class="mui-indicator"></div>
                    <div class="mui-indicator"></div>
                    <div class="mui-indicator"></div>
                </div>
            </div>
            <!--轮播图结束-->
            <!--第一轮商品-->
            <div id="wareItem1">
                <div class="wareItem1-title">
                    <div class="wareItem1-title-1">
                        <span class="mui-icon-extra mui-icon-extra-outline" style="color:red;font-size:1.5rem;margin-left:0.5rem"></span>
                        <span>限时购</span>
                    </div>
                    <div class="wareItem1-title-panel">
                        <div id="timer" data-timer="20161028140203" style="font-size:1rem">
                            <span style="color:#999">距离本场结束还有</span>
                            <span id="timer_h">0</span>:
                            <span id="timer_m">0</span>:
                            <span id="timer_s">0</span>
                        </div>
                    </div>
                    <div class="wareItem1-title-2">
                        <a href="#"><span style="color:#999">更多</span><span style="color:red">></span></a>
                    </div>
                </div>
                <div class="wareItem1-body">
                    <div class="wareItem1-body-item wareItem1-body-1">
                        <div class="wareItem1-body-item-img">
                            <img dataimg="img/blm/w1-1.jpg" alt="" width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </div>
                        <div class="wareItem1-body-item-c-1">@SHISEIDO资生堂</div>
                        <div class="wareItem1-body-item-c-2">
                            <span class="item1">限时价</span>
                            <span class="item2">￥48</span>
                        </div>
                        <div class="wareItem1-body-item-c-3"><s>￥105</s></div>
                    </div>
                    <div class="wareItem1-body-item wareItem1-body-2">
                        <div class="wareItem1-body-item-img">
                            <img dataimg="img/blm/w1-2.jpg" alt="" width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif"/>
                        </div>
                        <div class="wareItem1-body-item-c-1">@SHISEIDO资生堂</div>
                        <div class="wareItem1-body-item-c-2">
                            <span class="item1">限时价</span>
                            <span class="item2">￥48</span>
                        </div>
                        <div class="wareItem1-body-item-c-3"><s>￥105</s></div>
                    </div>
                    <div class="wareItem1-body-item wareItem1-body-3">
                        <div class="wareItem1-body-item-img">
                            <img dataimg="img/blm/w1-3.jpg" alt="" width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif"/>
                        </div>
                        <div class="wareItem1-body-item-c-1">@SHISEIDO资生堂</div>
                        <div class="wareItem1-body-item-c-2">
                            <span class="item1">限时价</span>
                            <span class="item2">￥48</span>
                        </div>
                        <div class="wareItem1-body-item-c-3"><s>￥105</s></div>
                    </div>
                </div>
            </div>
            <!--第二轮商品-->
            <div id="wareItem1">
                <div class="wareItem1-title">
                    <div class="wareItem1-title-1">
                        <span class="mui-icon-extra mui-icon-extra mui-icon-extra-like" style="color:red;font-size:1.5rem;margin-left:0.5rem;line-height:1.5rem;"></span>
                        <span>畅销商品</span>
                    </div>
                    <div class="wareItem1-title-2">
                        <a href="#"><span style="color:#999">更多</span><span style="color:red">></span></a>
                    </div>
                </div>
                <div class="wareItem1-body">
                    <div class="wareItem1-body-item wareItem1-body-1">
                        <div class="wareItem1-body-item-img">
                            <img dataimg="img/blm/w2-1.jpg" alt="" width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif"/>
                        </div>
                        <div class="wareItem1-body-item-c-1">收缩毛孔能手</div>
                        <div class="wareItem1-body-item-c-2">

                            <span class="item2">￥48</span>
                        </div>
                        <div class="wareItem1-body-item-c-3">国内参考价：￥105</div>
                    </div>
                    <div class="wareItem1-body-item wareItem1-body-2">
                        <div class="wareItem1-body-item-img">
                            <img dataimg="img/blm/w2-2.jpg" alt="" width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif"/>
                        </div>
                        <div class="wareItem1-body-item-c-1">吃再多也不会胖</div>
                        <div class="wareItem1-body-item-c-2">

                            <span class="item2">￥48</span>
                        </div>
                        <div class="wareItem1-body-item-c-3">国内参考价：￥105</div>
                    </div>
                    <div class="wareItem1-body-item wareItem1-body-3">
                        <div class="wareItem1-body-item-img">
                            <img dataimg="img/blm/w2-2.jpg" alt="" width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif"/>
                        </div>
                        <div class="wareItem1-body-item-c-1">吃太多也不会胖</div>
                        <div class="wareItem1-body-item-c-2">
                            <span class="item2">￥48</span>
                        </div>
                        <div class="wareItem1-body-item-c-3">国内参考价：￥105</div>
                    </div>
                </div>
            </div>
            <!--第三轮商品-->
            <div class="wareItem3">
                <div class="title-img">
                    <img dataimg="img/blm/w3-1.jpg" alt=""  width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                </div>
                <div class="ware">
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w3-2.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥1</div>
                    </div>
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w3-3.jpg" alt=""  width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                            <span class="buget">库存紧张</span>
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥202</div>
                    </div>
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w3-4.jpg" alt="" width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥19</div>
                    </div>

                </div>
            </div>
            <!--第三轮商品-->
            <div class="wareItem3">
                <div class="title-img">
                    <img dataimg="img/blm/w4-1.jpg" alt=""  width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                </div>
                <div class="ware">
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w4-2.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                            <span class="buget">库存紧张</span>
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥1</div>
                    </div>
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w4-3.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                            <span class="buget">库存紧张</span>
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥202</div>
                    </div>
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w4-4.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥19</div>
                    </div>

                </div>
            </div>
            <!--第三轮商品-->
            <div class="wareItem3">
                <div class="title-img">
                    <img dataimg="img/blm/w5-1.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                </div>
                <div class="ware">
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w5-2.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥1</div>
                    </div>
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w5-3.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                            <span class="buget">库存紧张</span>
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥202</div>
                    </div>
                    <div class="ware-item">
                        <div class="ware-img">
                            <img dataimg="img/blm/w5-4.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                        </div>
                        <div class="ware-name">AQUAAQUA 有机散粉迷你装＆唇蜜套装【一元换购】</div>
                        <div class="ware-price">￥19</div>
                    </div>

                </div>
            </div>
            <!--图文商品	-->
            <div class="wareItem4">
                <div class="title-img">
                    <img dataimg="img/blm/w6-1.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                </div>
                <div class="ware-content">
                    <div class="ware-content-head1">
								<span><img src="img/blm/japen.png" alt="" />
								<img src="img/blm/ic_directpost_oversea.png" alt="" / ></span>
                        <span>124.08元</span>
                    </div>
                    <div class="ware-content-head2">
                        <span>保湿效果超赞</span>
                        <span><s>69元</s></span>
                    </div>
                    <div class="ware-content-body">
                        <span>ETUDE HOUSE 小甜心透嫩唇蜜粉嫩唇彩 4.5g 多种色号可选</span>
                        <span>让吃进肚子里的一切都消失！茶包式油脂分解茶，薏仁、黑豆等10种天然材料混合茶。 适合饮食不规律，爱吃零食，想要瘦身或是一般上班族加班晚上吃东西的人群。不含咖啡因，不会影响睡眠。</span>
                    </div>
                </div>
            </div>
            <!--图文商品	-->
            <div class="wareItem4">
                <div class="title-img">
                    <img dataimg="img/blm/w7-1.jpg" alt="" / width="100%" class="samLazyImg" src="http://a.tbcdn.cn/mw/webapp/fav/img/grey.gif">
                    <div class="title-budget">
                        <img src="img/blm/middlePngLQ.png" alt="" />
                    </div>
                </div>
                <div class="ware-content">
                    <div class="ware-content-head1">
								<span><img src="img/blm/korea.png" alt="" />
								<img src="img/blm/ic_directpost_oversea.png" alt="" / ></span>
                        <span>139.78元</span>
                    </div>
                    <div class="ware-content-head2">
                        <span>保湿效果超赞</span>
                        <span><s>195元</s></span>
                    </div>
                    <div class="ware-content-body">
                        <span>Stylenanda 3CE GLOSSING FOUNDATION 保湿贴合提亮粉底霜液 35g 多种色号可选</span>
                        <span >又是一款在韩国很火的粉底液，有一个很特别的设计，搭配了专门取粉底液的滴管，妥妥地控制用量。粉底液的质地蛮轻薄的，涂抹在脸上感觉很水润，清爽不粘稠！
								冷色调（粉色）可将脸部发黄和暗黑的皮肤变得靓丽，自然的象牙白色和舒服的米色同样的色系只是亮度不一样。
								#NATURAL IVORY：象牙白，亮色的肤色选用；
								#SOFT BEIGE:米色，普通及偏暗的肤色。
								暖色调（黄色）修整红润的皮肤，并将缺少红润的肌肤变得更加富有活力，牛奶白和裸米色两种颜色是同个色系只是亮度不同，亮色肤色选用象牙白，普通肤色选用米色。
								#MILK IVORY：牛奶白，亮色肤色选用；
								#NUDE BEIGE:裸米色，普通及偏暗的肤色。</span>
                    </div>
                </div>
            </div>
        </ul>
    </div>
</div>
<!--top tip-->
<span class="mui-icon-extra mui-icon-extra-top" id="pagetop"></span>
<script type="text/javascript">
    //页面加载动画的调用
    $(window).load(function(){
        $("#loading").fadeOut(2000);
        timer('timer');
    })
    //解决tab选项卡a标签无法跳转的问题
    mui('body').on('tap','.mui-tab-item',function(){document.location.href=this.href;});
    //图片轮播功能
    var gallery = mui('.mui-slider');
    gallery.slider({
        interval:4000//自动轮播周期，若为0则不自动播放，默认为0；
    });
    mui.init({
        pullRefresh: {
            container: '#pullrefresh',
//				取消下拉刷新
            down: {
                callback: pulldownRefresh
            },
            up: {
                contentrefresh: '正在加载...',
                callback: pullupRefresh
            }
        }
    });
    /**
     * 下拉刷新具体业务实现
     */
    function pulldownRefresh() {
        setTimeout(function() {
            mui('#pullrefresh').pullRefresh().endPulldownToRefresh(); //refresh completed
        }, 1500);
    }
    var count = 0;
    /**
     * 上拉加载具体业务实现
     */
    function pullupRefresh() {
        setTimeout(function() {
            mui('#pullrefresh').pullRefresh().endPullupToRefresh(true); //参数为true代表没有更多数据了。

        }, 1500);
    }
    if (mui.os.plus) {
        mui.plusReady(function() {
            setTimeout(function() {
                mui('#pullrefresh').pullRefresh().pullupLoading();
            }, 1000);

        });
    } else {
        mui.ready(function() {
//					mui('#pullrefresh').pullRefresh().pullupLoading();
        });
    }
    //点击事件直达顶部
    document.getElementById("pagetop").addEventListener("tap",function () {

        mui('.mui-scroll-wrapper').pullRefresh().scrollTo(0,0,100);//100毫秒滚动到顶

    },false);
</script>


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
