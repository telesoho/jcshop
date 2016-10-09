<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $this->_siteConfig->name;?></title>
	<link type="image/x-icon" href="<?php echo IUrl::creatUrl("")."favicon.ico";?>" rel="icon">
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/index.css";?>" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
	<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
	<script type='text/javascript' src='<?php echo $this->getWebViewPath()."javascript/site.js";?>'></script>
</head>
<body class="index">
<div class="container">
	<div class="header">
		<h1 class="logo"><a title="<?php echo $this->_siteConfig->name;?>" style="background:url(<?php if($this->_siteConfig->logo){?><?php echo IUrl::creatUrl("")."".$this->_siteConfig->logo."";?><?php }else{?><?php echo $this->getWebSkinPath()."images/front/logo.gif";?><?php }?>) center no-repeat;background-size:contain;" href="<?php echo IUrl::creatUrl("");?>"><?php echo $this->_siteConfig->name;?></a></h1>
		<ul class="shortcut">
			<li class="first"><a href="<?php echo IUrl::creatUrl("/ucenter/index");?>">我的账户</a></li>
			<li><a href="<?php echo IUrl::creatUrl("/ucenter/order");?>">我的订单</a></li>
			<li><a href="<?php echo IUrl::creatUrl("/simple/seller");?>">申请开店</a></li>
			<li><a href="<?php echo IUrl::creatUrl("/seller/index");?>">商家管理</a></li>
			<li class='last'><a href="<?php echo IUrl::creatUrl("/site/help_list");?>">使用帮助</a></li>
		</ul>
		<p class="loginfo">
			<?php if($this->user){?>
			<?php echo $this->user['username'];?>您好，欢迎您来到<?php echo $this->_siteConfig->name;?>购物！[<a href="<?php echo IUrl::creatUrl("/simple/logout");?>" class="reg">安全退出</a>]
			<?php }else{?>
			[<a href="<?php echo IUrl::creatUrl("/simple/login");?>">登录</a><a class="reg" href="<?php echo IUrl::creatUrl("/simple/reg");?>">免费注册</a>]
			<?php }?>
		</p>
	</div>
	<div class="navbar">
		<ul>
			<li><a href="<?php echo IUrl::creatUrl("/site/index");?>">首页</a></li>
			<?php foreach(Api::run('getGuideList') as $key => $item){?>
			<li><a href="<?php echo IUrl::creatUrl("".$item['link']."");?>"><?php echo isset($item['name'])?$item['name']:"";?><span> </span></a></li>
			<?php }?>
		</ul>

		<div class="mycart" name="mycart">
			<dl>
				<dt><a href="<?php echo IUrl::creatUrl("/simple/cart");?>">购物车<b name="mycart_count">0</b>件</a></dt>
				<dd><a href="<?php echo IUrl::creatUrl("/simple/cart");?>">去结算</a></dd>
			</dl>

			<!--购物车浮动div 开始-->
			<div class="shopping" id='div_mycart' style='display:none;'></div>
			<!--购物车浮动div 结束-->

			<!--购物车模板 开始-->
			<script type='text/html' id='cartTemplete'>
			<dl class="cartlist">
				<%for(var item in goodsData){%>
				<%var data = goodsData[item]%>
				<dd id="site_cart_dd_<%=item%>">
					<div class="pic f_l"><img width="55px" height="55px" src="<?php echo IUrl::creatUrl("")."<%=data['img']%>";?>"></div>
					<h3 class="title f_l"><a href="<?php echo IUrl::creatUrl("/site/products/id/<%=data['goods_id']%>");?>"><%=data['name']%></a></h3>
					<div class="price f_r t_r">
						<b class="block">￥<%=data['sell_price']%> x <%=data['count']%></b>
						<input class="del" type="button" value="删除" onclick="removeCart('<%=data['id']%>','<%=data['type']%>');$('#site_cart_dd_<%=item%>').hide('slow');" />
					</div>
				</dd>
				<%}%>

				<dd class="static"><span>共<b name="mycart_count"><%=goodsCount%></b>件商品</span>金额总计：<b name="mycart_sum">￥<%=goodsSum%></b></dd>

				<%if(goodsData){%>
				<dd class="static">
					<label class="btn_orange"><input type="button" value="去购物车结算" onclick="window.location.href='<?php echo IUrl::creatUrl("/simple/cart");?>';" /></label>
				</dd>
				<%}%>
			</dl>
			</script>
			<!--购物车模板 结束-->
		</div>
	</div>

	<div class="searchbar">
		<div class="allsort">
			<a href="javascript:void(0);">全部商品分类</a>

			<!--总的商品分类-开始-->
			<ul class="sortlist" id='div_allsort' style='display:none'>
				<?php foreach(Api::run('getCategoryListTop') as $key => $first){?>
				<li>
					<h2><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$first['id']."");?>"><?php echo isset($first['name'])?$first['name']:"";?></a></h2>

					<!--商品分类 浮动div 开始-->
					<div class="sublist" style='display:none'>
						<div class="items">
							<strong>选择分类</strong>
							<?php foreach(Api::run('getCategoryByParentid',array('#parent_id#',$first['id'])) as $key => $second){?>
							<dl class="category selected">
								<dt>
									<a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$second['id']."");?>"><?php echo isset($second['name'])?$second['name']:"";?></a>
								</dt>

								<dd>
									<?php foreach(Api::run('getCategoryByParentid',array('#parent_id#',$second['id'])) as $key => $third){?>
									<a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$third['id']."");?>"><?php echo isset($third['name'])?$third['name']:"";?></a>|
									<?php }?>
								</dd>
							</dl>
							<?php }?>
						</div>
					</div>
					<!--商品分类 浮动div 结束-->
				</li>
				<?php }?>
			</ul>
			<!--总的商品分类-结束-->
		</div>

		<div class="searchbox">
			<form method='get' action='<?php echo IUrl::creatUrl("/");?>'>
				<input type='hidden' name='controller' value='site' />
				<input type='hidden' name='action' value='search_list' />
				<input class="text" type="text" name='word' autocomplete="off" value="" placeholder="请输入关键词..."  />
				<input class="btn" type="submit" value="商品搜索" />
			</form>
		</div>

		<div class="hotwords">热门搜索：
			<?php foreach(Api::run('getKeywordList') as $key => $item){?>
			<?php $tmpWord = urlencode($item['word']);?>
			<a href="<?php echo IUrl::creatUrl("/site/search_list/word/".$tmpWord."");?>"><?php echo isset($item['word'])?$item['word']:"";?></a>
			<?php }?>
		</div>
	</div>
	<?php echo Ad::show(1);?>

	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquerySlider/jquery.bxslider.min.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/jquerySlider/jquery.bxslider.css" />
<div class="wrapper clearfix">
	<div class="sidebar f_r">

		<!--cms新闻展示-->
		<div class="box m_10">
			<div class="title"><h2>Shop资讯sadas</h2><a class="more" href="<?php echo IUrl::creatUrl("/site/article");?>">更多...</a></div>
			<div class="cont">
				<ul class="list">
				<?php foreach(Api::run('getArtList',5) as $key => $item){?>
				<?php $tmpId=$item['id'];?>
				<li><a href="<?php echo IUrl::creatUrl("/site/article_detail/id/".$tmpId."");?>"><?php echo Article::showTitle($item['title'],$item['color'],$item['style']);?></a></li>
				<?php }?>
				</ul>
			</div>
		</div>
		<!--cms新闻展示-->
		<?php echo Ad::show(7);?>
	</div>

	<!--幻灯片 开始-->
	<div class="main f_l">
		<?php if($this->index_slide){?>
		<ul class="bxslider">
			<?php foreach($this->index_slide as $key => $item){?>
			<li title="<?php echo isset($item['name'])?$item['name']:"";?>"><a href="<?php echo IUrl::creatUrl("".$item['url']."");?>" target="_blank"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."");?>" width="750px" title="<?php echo isset($item['name'])?$item['name']:"";?>" /></a></li>
			<?php }?>
		</ul>
		<?php }?>
	</div>
	<!--幻灯片 结束-->
</div>

<?php echo Ad::show(6);?>

<div class="wrapper clearfix">
	<div class="sidebar f_r">

		<!--团购-->
		<div class="group_on box m_10">
			<div class="title"><h2>团购商品</h2><a class="more" href="<?php echo IUrl::creatUrl("/site/groupon");?>">更多...</a></div>
			<div class="cont">
				<ul class="ranklist">

					<?php foreach(Api::run('getRegimentList',5) as $key => $item){?>
					<li class="current">
						<?php $tmpId=$item['id'];?>
						<a href="<?php echo IUrl::creatUrl("/site/groupon/id/".$tmpId."");?>"><img width="60px" height="60px" alt="<?php echo isset($item['title'])?$item['title']:"";?>" src="<?php echo IUrl::creatUrl("")."".$item['img']."";?>"></a>
						<a class="p_name" title="<?php echo isset($item['title'])?$item['title']:"";?>" href="<?php echo IUrl::creatUrl("/site/groupon/id/".$tmpId."");?>"><?php echo isset($item['title'])?$item['title']:"";?></a><p class="light_gray">团购价：<em>￥<?php echo isset($item['regiment_price'])?$item['regiment_price']:"";?></em></p>
					</li>
					<?php }?>

				</ul>
			</div>
		</div>
		<!--团购-->

		<!--限时抢购-->
		<div class="buying box m_10">
			<div class="title"><h2>限时抢购</h2></div>
			<div class="cont clearfix">
				<ul class="prolist">
					<?php foreach(Api::run('getPromotionList',5) as $key => $item){?>
					<?php $free_time = ITime::getDiffSec($item['end_time'])?>
					<?php $countNumsItem[] = $item['p_id'];?>
					<li>
						<p class="countdown">倒计时:<br /><b id='cd_hour_<?php echo isset($item['p_id'])?$item['p_id']:"";?>'><?php echo floor($free_time/3600);?></b>时<b id='cd_minute_<?php echo isset($item['p_id'])?$item['p_id']:"";?>'><?php echo floor(($free_time%3600)/60);?></b>分<b id='cd_second_<?php echo isset($item['p_id'])?$item['p_id']:"";?>'><?php echo $free_time%60;?></b>秒</p>
						<?php $tmpGoodsId=$item['goods_id'];$tmpPId=$item['p_id'];?>
						<a href="<?php echo IUrl::creatUrl("/site/products/id/".$tmpGoodsId."/promo/time/active_id/".$tmpPId."");?>"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/175/h/175");?>" width="175" height="175" alt="<?php echo isset($item['name'])?$item['name']:"";?>" title="<?php echo isset($item['name'])?$item['name']:"";?>" /></a>
						<p class="pro_title"><a href="<?php echo IUrl::creatUrl("/site/products/id/".$tmpGoodsId."/promo/time/active_id/".$tmpPId."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a></p>
						<p class="light_gray">抢购价：<b>￥<?php echo isset($item['award_value'])?$item['award_value']:"";?></b></p>
						<div></div>
					</li>
					<?php }?>
				</ul>
			</div>
		</div>
		<!--限时抢购-->

		<!--热卖商品-->
		<div class="hot box m_10">
			<div class="title"><h2>热卖商品</h2></div>
			<div class="cont clearfix">
				<ul class="prolist">
					<?php foreach(Api::run('getCommendHot',8) as $key => $item){?>
					<?php $tmpId=$item['id']?>
					<li>
						<a href="<?php echo IUrl::creatUrl("/site/products/id/".$tmpId."");?>"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/85/h/85");?>" width="85" height="85" alt="<?php echo isset($item['name'])?$item['name']:"";?>" /></a>
						<p class="pro_title"><a href="<?php echo IUrl::creatUrl("/site/products/id/".$tmpId."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a></p>
						<p class="brown"><b>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></b></p>
					</li>
					<?php }?>
				</ul>
			</div>
		</div>
		<!--热卖商品-->

		<!--公告通知-->
		<div class="box m_10">
			<div class="title"><h2>公告通知</h2><a class="more" href="<?php echo IUrl::creatUrl("/site/notice");?>">更多...</a></div>
			<div class="cont">
				<ul class="list">
					<?php foreach(Api::run('getAnnouncementList',5) as $key => $item){?>
					<?php $tmpId=$item['id'];?>
					<li><a href="<?php echo IUrl::creatUrl("/site/notice_detail/id/".$tmpId."");?>"><?php echo isset($item['title'])?$item['title']:"";?></a></li>
					<?php }?>
				</ul>
			</div>
		</div>
		<!--公告通知-->

		<!--促销规则-->
		<div class="box m_10">
			<div class="title"><h2>促销活动</h2></div>
			<div class="cont">
				<ul class="list">
				<?php foreach(Api::run('getProrule') as $key => $item){?>
				<li><?php echo isset($item['info'])?$item['info']:"";?></li>
				<?php }?>
				</ul>
			</div>
		</div>
		<!--促销规则-->

		<!--关键词-->
		<div class="box m_10">
			<div class="title"><h2>关键词</h2><a class="more" href="<?php echo IUrl::creatUrl("/site/tags");?>">更多...</a></div>
			<div class="tag cont t_l">
				<?php foreach(Api::run('getKeywordList',5) as $key => $item){?>
				<?php $searchWord =urlencode($item['word']);?>
				<a href="<?php echo IUrl::creatUrl("/site/search_list/word/".$searchWord."");?>" class="orange"><?php echo isset($item['word'])?$item['word']:"";?></a>
				<?php }?>
			</div>
		</div>
		<!--关键词-->

		<!--电子订阅-->
		<div class="book box m_10">
			<div class="title"><h2>电子订阅</h2></div>
			<div class="cont">
				<p>我们会将最新的资讯发到您的Email</p>
				<input type="text" class="gray_m light_gray f_l" name='orderinfo' placeholder="输入您的电子邮箱地址" />
				<label class="btn_orange"><input type="button" onclick="orderinfo();" value="订阅" /></label>
			</div>
		</div>
		<!--电子订阅-->
	</div>

	<div class="main f_l">
		<!--商品分类展示-->
		<div class="category box">
			<div class="title2">
				<h2><img src="<?php echo $this->getWebSkinPath()."images/front/category.gif";?>" alt="商品分类" width="155" height="36" /></h2>
				<a class="more" href="<?php echo IUrl::creatUrl("/site/sitemap");?>">全部商品分类</a>
			</div>
		</div>

		<table id="index_category" class="sort_table m_10" width="100%">
			<col width="100px" />
			<col />
			<?php foreach(Api::run('getCategoryListTop') as $key => $first){?>
			<tr>
				<th><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$first['id']."");?>"><?php echo isset($first['name'])?$first['name']:"";?></a></th>
				<td>
					<?php foreach(Api::run('getCategoryByParentid',array('#parent_id#',$first['id'])) as $key => $second){?>
					<a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$second['id']."");?>"><?php echo isset($second['name'])?$second['name']:"";?></a>
					<?php }?>
				</td>
			</tr>
			<?php }?>
		</table>
		<!--商品分类展示-->

		<!--最新商品-->
		<div class="box yellow m_10">
			<div class="title2">
				<h2><img src="<?php echo $this->getWebSkinPath()."images/front/new_product.gif";?>" alt="最新商品" width="160" height="36" /></h2>
			</div>
			<div class="cont clearfix">
				<ul class="prolist">
					<?php foreach(Api::run('getCommendNew',8) as $key => $item){?>
					<?php $tmpId=$item['id'];?>
					<li style="overflow:hidden">
						<a href="<?php echo IUrl::creatUrl("/site/products/id/".$tmpId."");?>"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/175/h/175");?>" width="175" height="175" alt="<?php echo isset($item['name'])?$item['name']:"";?>" /></a>
						<p class="pro_title"><a title="<?php echo isset($item['name'])?$item['name']:"";?>" href="<?php echo IUrl::creatUrl("/site/products/id/".$tmpId."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a></p>
						<p class="brown">惊喜价：<b>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></b></p>
						<p class="light_gray">市场价：<s>￥<?php echo isset($item['market_price'])?$item['market_price']:"";?></s></p>
					</li>
					<?php }?>
				</ul>
			</div>
		</div>
		<!--最新商品-->

		<!--首页推荐商品-->
		<?php foreach(Api::run('getCategoryListTop') as $key => $first){?>
		<div class="box m_10" name="showGoods">
			<div class="title title3">
				<h2><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$first['id']."");?>"><strong><?php echo isset($first['name'])?$first['name']:"";?></strong></a></h2>
				<a class="more" href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$first['id']."");?>">更多商品...</a>
				<ul class="category">
					<?php foreach(Api::run('getCategoryByParentid',array('#parent_id#',$first['id'])) as $key => $second){?>
					<li><a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$second['id']."");?>"><?php echo isset($second['name'])?$second['name']:"";?></a><span></span></li>
					<?php }?>
				</ul>
			</div>

			<div class="cont clearfix">
				<ul class="prolist">
					<?php foreach(Api::run('getCategoryExtendList',array('#categroy_id#',$first['id']),8) as $key => $item){?>
					<li style="overflow:hidden">
						<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/175/h/175");?>" width="175" height="175" alt="<?php echo isset($item['name'])?$item['name']:"";?>" title="<?php echo isset($item['name'])?$item['name']:"";?>" /></a>
						<p class="pro_title"><a title="<?php echo isset($item['name'])?$item['name']:"";?>" href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a></p>
						<p class="brown">惊喜价：<b>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></b></p>
						<p class="light_gray">市场价：<s>￥<?php echo isset($item['market_price'])?$item['market_price']:"";?></s></p>
					</li>
					<?php }?>
				</ul>
			</div>
		</div>
		<?php }?>

		<!--品牌列表-->
		<div class="brand box m_10">
			<div class="title2"><h2><img src="<?php echo $this->getWebSkinPath()."images/front/brand.gif";?>" alt="品牌列表" width="155" height="36" /></h2><a class="more" href="<?php echo IUrl::creatUrl("/site/brand");?>">&lt;<span>全部品牌</span>&gt;</a></div>
			<div class="cont clearfix">
				<ul>
					<?php foreach(Api::run('getBrandList',6) as $key => $item){?>
					<?php $tmpId=$item['id'];?>
					<li><a href="<?php echo IUrl::creatUrl("/site/brand_zone/id/".$tmpId."");?>"><img src="<?php echo IUrl::creatUrl("")."".$item['logo']."";?>"  width="110" height="50"/><?php echo isset($item['name'])?$item['name']:"";?></a></li>
					<?php }?>
				</ul>
			</div>
		</div>
		<!--品牌列表-->

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

	<div class="help m_10">
		<div class="cont clearfix">
			<?php foreach(Api::run('getHelpCategoryFoot') as $key => $helpCat){?>
			<dl>
     			<dt><a href="<?php echo IUrl::creatUrl("/site/help_list/id/".$helpCat['id']."");?>"><?php echo isset($helpCat['name'])?$helpCat['name']:"";?></a></dt>
				<?php foreach(Api::run('getHelpListByCatidAll',array('#cat_id#',$helpCat['id'])) as $key => $item){?>
					<dd><a href="<?php echo IUrl::creatUrl("/site/help/id/".$item['id']."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a></dd>
				<?php }?>
      		</dl>
      		<?php }?>
		</div>
	</div>
	<?php echo IFilter::stripSlash($this->_siteConfig->site_footer_code);?>
</div>

<script type='text/javascript'>
$(function()
{
	//搜索框填充默认数据
	$('input:text[name="word"]').val("<?php echo $this->word;?>");

	var allsortLateCall = new lateCall(200,function(){$('#div_allsort').show();});

	//商品分类
	$('.allsort').hover(
		function(){
			allsortLateCall.start();
		},
		function(){
			allsortLateCall.stop();
			$('#div_allsort').hide();
		}
	);
	$('.sortlist li').each(
		function(i)
		{
			$(this).hover(
				function(){
					$(this).addClass('hover');
					$('.sublist:eq('+i+')').show();
				},
				function(){
					$(this).removeClass('hover');
					$('.sublist:eq('+i+')').hide();
				}
			);
		}
	);

	//排行,浏览记录的图片
	$('#ranklist li').hover(
		function(){
			$(this).addClass('current');
		},
		function(){
			$(this).removeClass('current');
		}
	);
});
</script>
</body>
</html>
