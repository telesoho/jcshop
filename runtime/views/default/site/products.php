<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>{hilujhklj</title>
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

	
<script type="text/javascript" src="<?php echo $this->getWebViewPath()."javascript/products.js";?>"></script>
<link rel="stylesheet" href="/views/default/skin/default/css/www.css">
<?php $breadGuide = goods_class::catRecursion($category);?>
<div class="position"><span>您当前的位置111：</span><a href="<?php echo IUrl::creatUrl("");?>">首页</a><?php foreach($breadGuide as $key => $item){?> » <a href="<?php echo IUrl::creatUrl("/site/pro_list/cat/".$item['id']."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a><?php }?> » <?php echo isset($name)?$name:"";?></div>
<div class="wrapper clearfix">
	<div class="summary">
		<h2><?php echo isset($name)?$name:"";?></h2>

		<!--基本信息区域-->
		<ul>
			<li>
				<span class="f_r light_gray">商品编号1111：<label id="data_goodsNo"><?php echo $goods_no?$goods_no:$id;?></label></span>
				<?php if(isset($brand)){?>品牌111：<?php echo isset($brand)?$brand:"";?><?php }?>
			</li>

			<!--抢购活动,引入 "_products_time"模板-->
			<?php if($promo == 'time' && isset($time)){?>
			<?php require(ITag::createRuntime("_products_time"));?>
			<?php }?>

			<!--团购活动,引入 "_products_groupon"模板-->
			<?php if($promo == 'groupon' && isset($groupon)){?>
			<?php require(ITag::createRuntime("_products_groupon"));?>
			<?php }?>

			<!--普通商品购买-->
			<?php if($promo == ''){?>
				<?php if($group_price){?>
				<!--当前用户有会员价-->
				<li>
					会员价：<b class="price red2">￥<span class="f30" id="data_groupPrice"><?php echo isset($group_price)?$group_price:"";?></span></b>
				</li>
				<li>
					原售价：￥<s id="data_sellPrice"><?php echo isset($sell_price)?$sell_price:"";?></s>
				</li>
				<?php }else{?>
				<!--当前用户普通价格-->
				<li>
					销售价：<b class="price red2">￥<span class="f30" id="data_sellPrice"><?php echo isset($sell_price)?$sell_price:"";?></span></b>
				</li>
				<?php }?>
			<?php }?>

			<li>
				市场价：￥<s id="data_marketPrice"><?php echo isset($market_price)?$market_price:"";?></s>
			</li>

			<li>
				库存：现货<span>(<label id="data_storeNums"><?php echo isset($store_nums)?$store_nums:"";?></label>)</span>
				<a class="favorite" onclick="favorite_add_ajax(<?php echo isset($id)?$id:"";?>,this);" href="javascript:void(0)">收藏此商品</a>
			</li>

			<li>顾客评分：<span class="grade-star g-star<?php echo Common::gradeWidth($grade,$comments);?>"></span> (已有<?php echo isset($comments)?$comments:"";?>人评价)</li>

			<?php if($point > 0){?>
			<li>送积分：单件送<?php echo isset($point)?$point:"";?>分</li>
			<?php }?>

			<!--物流配送运费显示-->
			<li class="relative" style="z-index:2">至
				<a class="sel_area blue" href="javascript:void(0)" name="localArea">当前地区</a>：
				<span id="deliveInfo"></span>
				<div class="area_box" style="display:none;">
					<ul>
						<li><a data-code="1" href="#J_PostageTableCont"><strong>全部</strong></a></li>
						<?php foreach(Api::run('getAreasListTop') as $key => $item){?>
						<li><a href="javascript:void(0);" name="areaSelectButton" value="<?php echo isset($item['area_id'])?$item['area_id']:"";?>"><?php echo isset($item['area_name'])?$item['area_name']:"";?></a></li>
						<?php }?>
					</ul>
				</div>
			</li>

			<!--商家信息 开始-->
			<?php if(isset($seller)){?>
			<li>商家：<a class="orange" href="<?php echo IUrl::creatUrl("/site/home/id/".$seller_id."");?>"><?php echo isset($seller['true_name'])?$seller['true_name']:"";?></a></li>
			<li>联系电话：<?php echo isset($seller['phone'])?$seller['phone']:"";?></li>
			<li>所在地：<?php echo join(' ',area::name($seller['province'],$seller['city'],$seller['area']));?></li>
			<li><?php plugin::trigger("onServiceButton",$seller['id'])?></li>
			<?php }?>
			<!--商家信息 结束-->
		</ul>

		<!--购买区域-->
		<div class="current">
		<?php if($store_nums <= 0){?>
			该商品已售完，不能购买，您可以看看其它商品！(<a href="<?php echo IUrl::creatUrl("/simple/arrival/goods_id/".$id."");?>" class="orange">到货通知</a>)
		<?php }else{?>
			<?php if($spec_array){?>
			<!--商品规格选择 开始-->
			<?php foreach(JSON::decode($spec_array) as $key => $item){?>
			<dl class="m_10 clearfix">
				<dt><?php echo isset($item['name'])?$item['name']:"";?>：</dt>
				<dd class="w_45">
					<?php foreach(explode(',',$item['value']) as $key => $spec_value){?>
					<?php if($item['type'] == 1){?>

					<!--文字规格 开始-->
					<div class="item w_27">
						<a href="javascript:void(0);" specName="<?php echo isset($item['name'])?$item['name']:"";?>" specId="<?php echo isset($item['id'])?$item['id']:"";?>" specData="<?php echo isset($spec_value)?$spec_value:"";?>"><?php echo isset($spec_value)?$spec_value:"";?><span></span></a>
					</div>
					<!--文字规格 结束-->

					<?php }else{?>

					<!--图片规格 开始-->
					<div class="item">
						<a href="javascript:void(0);" specName="<?php echo isset($item['name'])?$item['name']:"";?>" specId="<?php echo isset($item['id'])?$item['id']:"";?>" specData="<?php echo isset($spec_value)?$spec_value:"";?>" style="background:url(<?php echo IUrl::creatUrl("")."".$spec_value."";?>) center no-repeat;background-size:contain;height:40px;display:inline-block;"><span></span></a>
					</div>
					<!--图片规格 结束-->

					<?php }?>
					<?php }?>
				</dd>
			</dl>
			<?php }?>
			<!--商品规格选择 结束-->
			<?php }?>

			<dl class="m_10 clearfix">
				<dt>购买数量：</dt>
				<dd>
					<input class="gray_t f_l" type="text" id="buyNums" value="1" maxlength="5" />
					<div class="resize">
						<a class="add" id="buyAddButton" href="javascript:void(0);"></a>
						<a class="reduce" id="buyReduceButton" href="javascript:void(0);"></a>
					</div>
				</dd>
			</dl>

			<input class="submit_buy" type="button" id="buyNowButton" value="立即购买" />
			<div class="shop_cart">
				<input class="submit_join" type="button" id="joinCarButton" value="加入购物车" />
			</div>
		<?php }?>
		</div>
	</div>

	<!--图片放大镜-->
	<div class="preview">
		<div class="pic_show" style="width:435px;height:435px;position:relative;z-index:5;padding-bottom:5px;">
			<img id="picShow" rel="" src="" />
		</div>

		<ul id="goodsPhotoList" class="pic_thumb">
			<?php foreach($photo as $key => $item){?>
			<li>
				<a href="javascript:void(0);" thumbimg="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/435/h/435");?>" sourceimg="<?php echo IUrl::creatUrl("")."".$item['img']."";?>">
					<img src='<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/60/h/60");?>' width="60px" height="60px" />
				</a>
			</li>
			<?php }?>
		</ul>
	</div>
</div>

<div class="t_l">
	<a class="zoom blue" href="<?php echo IUrl::creatUrl("/site/pic_show/id/".$id."");?>">点击看大图</a>
</div>

<div class="wrapper clearfix container_2">

	<!--左边栏-->
	<div class="sidebar f_l">

		<!--促销规则-->
		<div class="box m_10">
			<div class="title">促销活动</div>
			<div class="cont">
				<ul class="list">
				<?php foreach(Api::run('getProrule',$seller_id) as $key => $item){?>
					<li><?php echo isset($item['info'])?$item['info']:"";?></li>
				<?php }?>
				</ul>
			</div>
		</div>
		<!--促销规则-->

		<!--热卖商品-->
		<div class="box m_10">
			<div class="title">热卖商品</div>
			<div class="content">
				<ul class="ranklist">
				<?php foreach(Api::run('getCommendHot') as $key => $item){?>
					<li class="current">
						<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>"><img width="58px" height="58px" alt="<?php echo isset($item['name'])?$item['name']:"";?>" src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/58/h/58");?>" /></a>
						<a title="<?php echo isset($item['name'])?$item['name']:"";?>" class="p_name" href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>"><?php echo isset($item['name'])?$item['name']:"";?></a>
						<b>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></b>
					</li>
				<?php }?>
				</ul>
			</div>
		</div>
		<!--热卖商品-->
	</div>

	<!--滑动面tab标签-->
	<div class="main f_r" style="overflow:hidden">

		<div class="uc_title" name="showButton">
			<label class="current"><span>商品详情</span></label>
			<label><span>顾客评价(<?php echo isset($comments)?$comments:"";?>)</span></label>
			<label><span>购买记录(<?php echo isset($buy_num)?$buy_num:"";?>)</span></label>
			<label><span>购买前咨询(<?php echo isset($refer)?$refer:"";?>)</span></label>
			<label><span>网友讨论圈(<?php echo isset($discussion)?$discussion:"";?>)</span></label>
		</div>

		<div name="showBox">
			<!-- 商品详情 start -->
			<div>
				<ul class="saleinfos m_10 clearfix">
					<li>商品名称：<?php echo isset($name)?$name:"";?></li>

					<?php if(isset($brand) && $brand){?>
					<li>品牌：<?php echo isset($brand)?$brand:"";?></li>
					<?php }?>

					<?php if(isset($weight) && $weight){?>
					<li>商品毛重：<label id="data_weight"><?php echo isset($weight)?$weight:"";?></label></li>
					<?php }?>

					<?php if(isset($unit) && $unit){?>
					<li>单位：<?php echo isset($unit)?$unit:"";?></li>
					<?php }?>

					<?php if(isset($up_time) && $up_time){?>
					<li>上架时间：<?php echo isset($up_time)?$up_time:"";?></li>
					<?php }?>

					<?php if(($attribute)){?>
					<?php foreach($attribute as $key => $item){?>
					<li><?php echo isset($item['name'])?$item['name']:"";?>：<?php echo isset($item['attribute_value'])?$item['attribute_value']:"";?></li>
					<?php }?>
					<?php }?>
				</ul>
				<?php if(isset($content) && $content){?>
				<div class="salebox">
					<strong class="saletitle block">产品描述：</strong>
					<p class="saledesc"><?php echo isset($content)?$content:"";?></p>
				</div>
				<?php }?>
			</div>
			<!-- 商品详情 end -->

			<!-- 顾客评论 start -->
			<div class="hidden comment_list box">
				<div class="title3">
					<img src="<?php echo $this->getWebSkinPath()."images/front/comm.gif";?>" width="16px" height="16px" />
					商品评论<span class="f12 normal">（已有<b class="red2"><?php echo isset($comments)?$comments:"";?></b>条）</span>
				</div>

				<div id='commentBox'></div>

				<!--评论JS模板-->
				<script type='text/html' id='commentRowTemplate'>
				<div class="item">
					<div class="user">
						<div class="ico">
							<a href="javascript:void(0)">
								<img src="<?php echo IUrl::creatUrl("")."<%=head_ico%>";?>" width="70px" height="70px" onerror="this.src='<?php echo $this->getWebSkinPath()."images/front/user_ico.gif";?>'" />
							</a>
						</div>
						<span class="blue"><%=username%></span>
					</div>
					<dl class="desc">
						<p class="clearfix">
							<b>评分：</b>
							<span class="grade-star g-star<%=point%>"></span>
							<span class="light_gray"><%=comment_time%></span><label></label>
						</p>
						<hr />
						<p><b>评价：</b><span class="gray"><%=contents%></span></p>
						<%if(recontents){%>
						<p><b>回复：</b><span class="red"><%=recontents%></span></p>
						<%}%>
					</dl>
					<div class="corner b"></div>
				</div>
				<hr />
				</script>
			</div>
			<!-- 顾客评论 end -->

			<!-- 购买记录 start -->
			<div class="hidden box">
				<div class="title3">
					<img src="<?php echo $this->getWebSkinPath()."images/front/cart.gif";?>" width="16" height="16" alt="" />
					购买记录<span class="f12 normal">（已有<b class="red2"><?php echo isset($buy_num)?$buy_num:"";?></b>购买）</span>
				</div>

				<table width="100%" class="list_table m_10 mt_10">
					<colgroup>
						<col width="150" />
						<col width="120" />
						<col width="120" />
						<col width="150" />
						<col />
					</colgroup>

					<thead class="thead">
						<tr>
							<th>购买人</th>
							<th>出价</th>
							<th>数量</th>
							<th>购买时间</th>
							<th>状态</th>
						</tr>
					</thead>

					<tbody class="dashed" id="historyBox"></tbody>
				</table>

				<!--购买历史js模板-->
				<script type='text/html' id='historyRowTemplate'>
				<tr>
					<td><%=username?username:'游客'%></td>
					<td><%=goods_price%></td>
					<td class="bold orange"><%=goods_nums%></td>
					<td class="light_gray"><%=completion_time%></td>
					<td class="bold blue">成交</td>
				</tr>
				</script>
			</div>
			<!-- 购买记录 end -->

			<!-- 购买前咨询 start -->
			<div class="hidden comment_list box">
				<div class="title3">
					<span class="f_r f12 normal"><a class="comm_btn" href="<?php echo IUrl::creatUrl("/site/consult/id/".$id."");?>">我要咨询</a></span>
					<img src="<?php echo $this->getWebSkinPath()."images/front/cart.gif";?>" width="16" height="16" />购买前咨询<span class="f12 normal">（共<b class="red2"><?php echo isset($refer)?$refer:"";?></b>记录）</span>
				</div>

				<div id='referBox'></div>

				<!--购买咨询JS模板-->
				<script type='text/html' id='referRowTemplate'>
				<div class="item">
					<div class="user">
						<div class="ico"><img src="<?php echo IUrl::creatUrl("")."<%=head_ico%>";?>" width="70px" height="70px" onerror="this.src='<?php echo $this->getWebSkinPath()."images/front/user_ico.gif";?>'" /></div>
						<span class="blue"><%=username%></span>
					</div>
					<dl class="desc gray">
						<p>
							<img src="<?php echo $this->getWebSkinPath()."images/front/ask.gif";?>" width="16px" height="17px" />
							<b>咨询内容：</b><span class="f_r"><%=time%></span>
						</p>
						<p class="indent"><%=question%></p>
						<hr />
						<%if(answer){%>
						<p class="bg_gray"><img src="<?php echo $this->getWebSkinPath()."images/front/answer.gif";?>" width="16px" height="17px" />
						<b class="orange">商家回复：</b><span class="f_r"><%=reply_time%></span></p>
						<p class="indent bg_gray"><%=answer%></p>
						<%}%>
					</dl>
					<div class="corner b"></div>
					<div class="corner tl"></div>
				</div>
				<hr />
				</script>
			</div>
			<!-- 购买前咨询 end -->

			<!-- 网友讨论圈 start -->
			<div class="hidden box">
				<div class="title3">
					<span class="f_r f12 normal"><a class="comm_btn" name="discussButton">发表话题</a></span>
					<img src="<?php echo $this->getWebSkinPath()."images/front/discuss.gif";?>" width="18px" height="19px" />
					网友讨论圈<span class="f12 normal">（共<b class="red2"><?php echo isset($discussion)?$discussion:"";?></b>记录）</span>
				</div>
				<div class="wrap_box no_wrap">
					<!--讨论内容列表-->
					<table width="100%" class="list_table">
						<colgroup>
							<col />
							<col width="150">
						</colgroup>

						<tbody id='discussBox'></tbody>
					</table>

					<!--讨论JS模板-->
					<script type='text/html' id='discussRowTemplate'>
					<tr>
						<td class="t_l discussion_td" style="border:none;">
							<span class="blue"><%=username%></span>
						</td>
						<td style="border:none;" class="t_r gray discussion_td"><%=time%></td>
					</tr>
					<tr><td class="t_l" colspan="2" style="padding:0 7px 0 13px;"><%=contents%></td></tr>
					</script>

					<!--讨论内容输入框-->
					<table class="form_table" style="display:none;" id="discussTable">
						<colgroup>
							<col width="80px">
							<col />
						</colgroup>

						<tbody>
							<tr>
								<th>讨论内容：</th>
								<td valign="top"><textarea id="discussContent" pattern="required" alt="请填写内容"></textarea></td>
							</tr>
							<tr>
								<th>验证码：</th>
								<td><input type='text' class='gray_s' name='captcha' pattern='^\w{5}$' alt='填写下面图片所示的字符' /><label>填写下面图片所示的字符</label></td>
							</tr>
							<tr class="low">
								<th></th>
								<td><img src='<?php echo IUrl::creatUrl("/site/getCaptcha");?>' id='captchaImg' /><span class="light_gray">看不清？<a class="link" href="javascript:changeCaptcha();">换一张</a></span></td>
							</tr>
							<tr>
								<td></td>
								<td><label class="btn"><input type="submit" value="发表" name="sendDiscussButton" /></label></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<!-- 网友讨论圈 end -->
		</div>
	</div>
</div>

<script type="text/javascript">
//DOM加载结束后
$(function(){
	//初始化商品详情对象
	var productInstance = new productClass("<?php echo isset($id)?$id:"";?>","<?php echo isset($this->user['user_id'])?$this->user['user_id']:"";?>","<?php echo isset($promo)?$promo:"";?>","<?php echo isset($active_id)?$active_id:"";?>");

	//初始化商品轮换图
	$('#goodsPhotoList').bxSlider({
		infiniteLoop:false,
		hideControlOnEnd:true,
		controls:true,
		pager:false,
		minSlides: 5,
		maxSlides: 5,
		slideWidth: 72,
		slideMargin: 15,
		onSliderLoad:function(currentIndex){
			//默认初始化显示第一张
			$('[thumbimg]:eq('+currentIndex+')').trigger('click');

			//放大镜
			$("#picShow").imagezoom();
		}
	});

	//城市地域选择按钮事件
	$('.sel_area').hover(
		function(){
			$('.area_box').show();
		},function(){
			$('.area_box').hide();
		}
	);
	$('.area_box').hover(
		function(){
			$('.area_box').show();
		},function(){
			$('.area_box').hide();
		}
	);

	//详情滑动门按钮绑定
	$('[name="showButton"]>label').click(function()
	{
		//滑动按钮高亮
		$(this).siblings().removeClass('current');
		$(this).addClass('current');

		//滑动DIV显示
		$('[name="showBox"]>div').hide();
		$('[name="showBox"]>div:eq('+$(this).index()+')').show();

		//滑动按钮绑定事件
		switch($(this).index())
		{
			case 1:
			{
				productInstance.comment_ajax();
			}
			break;

			case 2:
			{
				productInstance.history_ajax();
			}
			break;

			case 3:
			{
				productInstance.refer_ajax();
			}
			break;

			case 4:
			{
				productInstance.discuss_ajax();
			}
			break;
		}
	});
});
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
