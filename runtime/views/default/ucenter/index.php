<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $this->_siteConfig->name;?></title>
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/index.css";?>" />
	<link rel="shortcut icon" href="<?php echo IUrl::creatUrl("")."favicon.ico";?>" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
	<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
	<script type='text/javascript' src='<?php echo $this->getWebViewPath()."javascript/site.js";?>'></script>
</head>
<body class="index">
<div class="ucenter container">
	<div class="header">
		<h1 class="logo"><a title="<?php echo $this->_siteConfig->name;?>" style="background:url(<?php if($this->_siteConfig->logo){?><?php echo IUrl::creatUrl("")."".$this->_siteConfig->logo."";?><?php }else{?><?php echo $this->getWebSkinPath()."images/front/logo.gif";?><?php }?>) center no-repeat;background-size:contain;" href="<?php echo IUrl::creatUrl("");?>"><?php echo $this->_siteConfig->name;?></a></h1>
		<ul class="shortcut">
			<li class="first"><a href="<?php echo IUrl::creatUrl("/ucenter/index");?>">我的账户</a></li><li><a href="<?php echo IUrl::creatUrl("/ucenter/order");?>">我的订单</a></li><li class='last'><a href="<?php echo IUrl::creatUrl("/site/help_list");?>">使用帮助</a></li>
		</ul>
		<p class="loginfo"><?php echo isset($this->user['username'])?$this->user['username']:"";?>您好，欢迎您来到<?php echo $this->_siteConfig->name;?>购物！[<a class='reg' href="<?php echo IUrl::creatUrl("/simple/logout");?>">安全退出</a>]</p>
	</div>
	<div class="navbar">
		<ul>
			<li><a href="<?php echo IUrl::creatUrl("");?>">首页</a></li>
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
			<div class="shopping" id='div_mycart' style='display:none;'>
			</div>
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
			<a href="javascript:void();">全部商品分类</a>

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

	<div class="position">
		您当前的位置： <a href="<?php echo IUrl::creatUrl("");?>">首页</a> » <a href="<?php echo IUrl::creatUrl("/ucenter/index");?>">我的账户</a>
	</div>
	<div class="wrapper clearfix">
		<div class="sidebar f_l">
			<img src="<?php echo $this->getWebSkinPath()."images/front/ucenter/ucenter.gif";?>" width="180" height="40" />

			<?php $index=0;?>
			<?php foreach(menuUcenter::init() as $key => $item){?>
			<?php $index++;?>
			<div class="box">
				<div class="title"><h2 class='bg<?php echo isset($index)?$index:"";?>'><?php echo isset($key)?$key:"";?></h2></div>
				<div class="cont">
					<ul class="list">
						<?php foreach($item as $moreKey => $moreValue){?>
						<li><a href="<?php echo IUrl::creatUrl("".$moreKey."");?>"><?php echo isset($moreValue)?$moreValue:"";?></a></li>
						<?php }?>
					</ul>
				</div>
			</div>
			<?php }?>
		</div>
		<div class="main f_r">
    <?php if($msgNum>0){?>
    <div class="prompt m_10">
        <b>温馨提示：</b>您有<span class="red"><?php echo isset($msgNum)?$msgNum:"";?></span> 条站内未读短信息，<a class="blue" href="<?php echo IUrl::creatUrl("/ucenter/message");?>">现在去看看</a>
    </div>
    <?php }?>

	<div class="userinfo_bar"><span class="f_r">上一次登录时间：<?php echo ISafe::get('last_login');?></span><b class="f14">您好，<?php echo isset($this->user['username'])?$this->user['username']:"";?> 欢迎回来!</b></div>
	<div class="box clearfix">
		<h3>用户信息</h3>
		<dl class="userinfo_box">
			<dt>
			<?php $user_ico = $this->user['head_ico']?>
			<a class="ico"><img id="user_ico_img" src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$user_ico."/w/100/h/100");?>" width="100" height="100" alt="" onerror="this.src='<?php echo $this->getWebSkinPath()."images/front/user_ico.gif";?>'" /></a>
			<a class="blue" href="javascript:select_ico();">修改头像</a>
			</dt>
			<dd>
			<table width="100%" cellpadding="0" cellspacing="0">
				<col width="350px" />
				<col />
				<tr>
					<td>你的账户目前总积分：<b class="red2"><?php echo isset($user['point'])?$user['point']:"";?> 分</b>&nbsp;&nbsp;&nbsp;<a class="blue" href="<?php echo IUrl::creatUrl("/ucenter/integral");?>">查看积分历史</a></td>
					<td>你的订单交易总数量：<b class="red2"><?php echo isset($statistics['num'])?$statistics['num']:"";?> 笔</b>&nbsp;&nbsp;&nbsp;<a class="blue" href="<?php echo IUrl::creatUrl("/ucenter/order");?>">进入订单列表</a></td>
				</tr>
				<tr>
					<td>总消费额：<b class="red2">￥<?php echo isset($statistics['amount'])?$statistics['amount']:"";?></b></td>
					<td>预存款余额：<b class="red2">￥<?php echo isset($user['balance'])?$user['balance']:"";?></b></td>
				</tr>
				<tr>
					<td>代金券：<b class="red2"><?php echo isset($propData['prop_num'])?$propData['prop_num']:"";?> 张</b></td>
					<td></td>
				</tr>
			</table>

			<div class="stat">
				<span>待评价商品：<label>(<b><a class="red2" href="<?php echo IUrl::creatUrl("/ucenter/evaluation");?>"><?php echo statistics::countUserWaitComment($this->user['user_id']);?></b>)</a></label></span>
				<span>待付款订单：<label>(<b><a class="red2" href="<?php echo IUrl::creatUrl("/ucenter/order");?>"><?php echo statistics::countUserWaitPay($this->user['user_id']);?></b>)</a></label></span>
				<span>待确认收货：<label>(<b><a class="red2" href="<?php echo IUrl::creatUrl("/ucenter/order");?>"><?php echo statistics::countUserWaitCommit($this->user['user_id']);?></b>)</a></label></span>
			</div>
			</dd>
		</dl>
	</div>
	<h3 class="bg">我的订单</h3>
	<div class="box m_10">
		<table class="list_table" width="100%" cellpadding="0" cellspacing="0">
			<col width="140px" />
			<tr>
				<th>订单编号</th><th>下单日期</th><th>收货人</th><th>支付方式</th><th>总金额</th><th>订单状态</th>
			</tr>
			<?php foreach(Api::run('getOrderListByUserid',array('#user_id#',$user['user_id'])) as $key => $item){?>
			<tr>
				<td><a href="<?php echo IUrl::creatUrl("/ucenter/order_detail/id/".$item['id']."");?>"><?php echo isset($item['order_no'])?$item['order_no']:"";?></a></td>
				<td><?php echo isset($item['create_time'])?$item['create_time']:"";?></td>
				<td><?php echo isset($item['accept_name'])?$item['accept_name']:"";?></td>
				<td><?php echo isset($this->payments[$item['pay_type']]['name'])?$this->payments[$item['pay_type']]['name']:"";?></td>
				<td>￥<?php echo ($item['order_amount']);?></td>
				<td>
					<?php $orderStatus = Order_Class::getOrderStatus($item)?>
					<b class="<?php if($orderStatus >= 6){?>green<?php }else{?>orange<?php }?>"><?php echo Order_Class::orderStatusText($orderStatus);?></b>
				</td>
			</tr>
			<?php }?>

			<tfoot><tr><td colspan="6" class="t_r"><a class="blue" href="<?php echo IUrl::creatUrl("/ucenter/order");?>">更多订单&gt;&gt;</a></td></tr></tfoot>
		</table>
	</div>
	<div class="box">
		<div class="title"><h2>也许你会对下列商品感兴趣</h2></div>
		<div class="cont clearfix">
			<ul class="prolist f_l">
				<?php foreach(Api::run('getGoodsByCommendgoods') as $key => $item){?>
				<li>
				<a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>" target="_black"><img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/100/h/100");?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>" height="100" width="100"></a>
				<p class="pro_title"><a href="<?php echo IUrl::creatUrl("/site/products/id/".$item['id']."");?>" target='_black'><?php echo isset($item['name'])?$item['name']:"";?></a></p>
				<p class="price_new"><b>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></b></p>
				</li>
				<?php }?>
			</ul>
		</div>
	</div>
</div>
<script type='text/javascript'>
//选择头像
function select_ico()
{
	<?php $callback = urlencode(IUrl::creatUrl('/ucenter/user_ico_upload'))?>
	art.dialog.open('<?php echo IUrl::creatUrl("/block/photo_upload?callback=".$callback."");?>',
	{
		'id':'user_ico',
		'title':'设置头像',
		'ok':function(iframeWin, topWin)
		{
			iframeWin.document.forms[0].submit();
			return false;
		}
	});
}

//头像上传回调函数
function callback_user_ico(content)
{
	var content = eval(content);
	if(content.isError == true)
	{
		alert(content.message);
	}
	else
	{
		$('#user_ico_img').prop('src',content.data);
	}
	art.dialog({id:'user_ico'}).close();
}
</script>

	</div>

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
//DOM加载完毕后运行
$(function()
{
	//隔行换色
	$(".list_table tr:nth-child(even)").addClass('even');
	$(".list_table tr").hover(
		function () {
			$(this).addClass("sel");
		},
		function () {
			$(this).removeClass("sel");
		}
	);

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

	//按钮高亮
	<?php $localUrl = IWeb::$app->getController()->getId().'/'.IWeb::$app->getController()->getAction()->getId()?>
	$('a[href*="<?php echo isset($localUrl)?$localUrl:"";?>"]').parent().addClass('current');
});
</script>
</body>
</html>
