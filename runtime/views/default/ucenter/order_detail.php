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
	<div class="uc_title m_10">
		<label class="current"><span>订单详情</span></label>
	</div>

	<div class="prompt_2 m_10">
		<div class="t_part">
			<?php $orderStep = Order_Class::orderStep($this->order_info)?>
			<?php foreach($orderStep as $eventTime => $stepData){?>
			<p><?php echo isset($eventTime)?$eventTime:"";?>&nbsp;&nbsp;<span class="black"><?php echo isset($stepData)?$stepData:"";?></span></p>
			<?php }?>
		</div>
		<p>
			<b>订单号：</b><?php echo isset($this->order_info['order_no'])?$this->order_info['order_no']:"";?>
			<b>下单日期：</b><?php echo isset($this->order_info['create_time'])?$this->order_info['create_time']:"";?>
			<b>状态：</b>
			<span class="red2">
				<b class="orange"><?php echo Order_Class::orderStatusText($orderStatus);?></b>
	        </span>
        </p>

        <form action='<?php echo IUrl::creatUrl("/ucenter/order_status");?>' method='post'>
        <p>
	        <input type="hidden" name="order_id" value="<?php echo isset($this->order_info['order_id'])?$this->order_info['order_id']:"";?>" />
	    	<?php if(in_array($orderStatus,array(1,2))){?>
	        <label class="btn_orange">
	        	<input type="hidden" name='op' value='cancel' />
	        	<input type="submit" value="取消订单" />
	        </label>
	        <?php }?>

			<?php if($orderStatus == 2){?>
			<label class="btn_green">
				<input type="button" value="立即付款" onclick="window.location.href='<?php echo IUrl::creatUrl("/block/doPay/order_id/".$this->order_info['order_id']."");?>'" />
			</label>
			<?php }?>

			<?php if(in_array($orderStatus,array(11,3))){?>
	        <label class="btn_green">
	        	<input type="hidden" name='op' value='confirm' />
	        	<input type="submit" value="确认收货" />
	        </label>
			<?php }?>

	        <?php if(Order_Class::isRefundmentApply($this->order_info)){?>
	        <label class="btn_orange">
	        	<input type="button" value="申请退款" onclick='javascript:window.location.href="<?php echo IUrl::creatUrl("/ucenter/refunds_edit/order_id/".$this->order_info['order_id']."");?>"' />
	        </label>
	    	<?php }?>
	    </p>
        </form>
	</div>

	<div class="box m_10">
		<div class="title">
			<h2><span class="orange">收件人信息</span></h2>
		</div>

		<!--收获信息展示-->
		<div class="cont clearfix" id="acceptShow">
			<table class="dotted_table f_l" width="100%" cellpadding="0" cellspacing="0">
				<col width="130px" />
				<col />
				<tr>
					<th>收货人：</th>
					<td><?php echo isset($this->order_info['accept_name'])?$this->order_info['accept_name']:"";?></td>
				</tr>
				<tr>
					<th>地址：</th>
					<td><?php echo isset($this->order_info['province_str'])?$this->order_info['province_str']:"";?> <?php echo isset($this->order_info['city_str'])?$this->order_info['city_str']:"";?> <?php echo isset($this->order_info['area_str'])?$this->order_info['area_str']:"";?> <?php echo isset($this->order_info['address'])?$this->order_info['address']:"";?></td>
				</tr>
				<tr>
					<th>邮编：</th>
					<td><?php echo isset($this->order_info['postcode'])?$this->order_info['postcode']:"";?></td>
				</tr>
				<tr>
					<th>固定电话：</th>
					<td><?php echo isset($this->order_info['telphone'])?$this->order_info['telphone']:"";?></td>
				</tr>
				<tr>
					<th>手机号码：</th>
					<td><?php echo isset($this->order_info['mobile'])?$this->order_info['mobile']:"";?></td>
				</tr>
			</table>
		</div>
	</div>

	<!--支付和配送-->
	<div class="box m_10">
		<div class="title"><h2><span class="orange">支付及配送方式</span></h2></div>
		<div class="cont clearfix">
			<table class="dotted_table f_l" width="100%" cellpadding="0" cellspacing="0">
				<col width="130px" />
				<col />
				<tr>
					<th>配送方式：</th>
					<td><?php echo isset($this->order_info['delivery'])?$this->order_info['delivery']:"";?></td>
				</tr>

				<?php if($this->order_info['takeself']){?>
				<tr>
					<th>自提地址：</th>
					<td>
						<?php echo isset($this->order_info['takeself']['province_str'])?$this->order_info['takeself']['province_str']:"";?>
						<?php echo isset($this->order_info['takeself']['city_str'])?$this->order_info['takeself']['city_str']:"";?>
						<?php echo isset($this->order_info['takeself']['area_str'])?$this->order_info['takeself']['area_str']:"";?>
						<?php echo isset($this->order_info['takeself']['address'])?$this->order_info['takeself']['address']:"";?>
					</td>
				</tr>
				<tr>
					<th>自提联系方式：</th>
					<td>
						座机：<?php echo isset($this->order_info['takeself']['phone'])?$this->order_info['takeself']['phone']:"";?> &nbsp;&nbsp;
						手机：<?php echo isset($this->order_info['takeself']['mobile'])?$this->order_info['takeself']['mobile']:"";?>
					</td>
				</tr>
				<?php }?>

				<tr>
					<th>支付方式：</th>
					<td><?php echo isset($this->order_info['payment'])?$this->order_info['payment']:"";?></td>
				</tr>

				<?php if($this->order_info['paynote']){?>
				<tr>
					<th>支付说明：</th>
					<td><?php echo isset($this->order_info['paynote'])?$this->order_info['paynote']:"";?></td>
				</tr>
				<?php }?>

				<tr>
					<th>运费：</th>
					<td><?php echo isset($this->order_info['real_freight'])?$this->order_info['real_freight']:"";?></td>
				</tr>
				<tr>
					<th>物流公司：</th>
					<td><?php echo isset($this->order_info['freight']['freight_name'])?$this->order_info['freight']['freight_name']:"";?></td>
				</tr>
				<tr>
					<th>快递单号：</th>
					<td><?php echo isset($this->order_info['freight']['delivery_code'])?$this->order_info['freight']['delivery_code']:"";?></td>
				</tr>
			</table>
		</div>
	</div>

    <!--发票信息-->
    <?php if($this->order_info['invoice']==1){?>
	<div class="box m_10">
		<div class="title"><h2><span class="orange">发票信息</span></h2></div>
		<div class="cont clearfix">
			<table class="dotted_table f_l" width="100%" cellpadding="0" cellspacing="0">
				<col width="129px" />
				<col />
				<tr>
					<th>所需税金：</th>
					<td><?php echo isset($this->order_info['taxes'])?$this->order_info['taxes']:"";?></td>
				</tr>
				<tr>
					<th>发票抬头：</th>
					<td><?php echo isset($this->order_info['invoice_title'])?$this->order_info['invoice_title']:"";?></td>
				</tr>
			</table>
		</div>
	</div>
    <?php }?>

	<!--物品清单-->
	<div class="box m_10">
		<div class="title"><h2><span class="orange">商品清单</span></h2></div>
		<div class="cont clearfix">
			<table class="list_table f_l" width="100%" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
						<th>图片</th>
						<th>商品名称</th>
						<th>赠送积分</th>
						<th>商品价格</th>
						<th>优惠金额</th>
						<th>商品数量</th>
						<th>小计</th>
						<th>配送</th>
					</tr>
                    <?php foreach(Api::run('getOrderGoodsListByGoodsid',array('#order_id#',$this->order_info['order_id'])) as $key => $good){?>
                    <?php $good_info = JSON::decode($good['goods_array'])?>
					<tr>
						<td><img class="pro_pic" src="<?php echo IUrl::creatUrl("")."".$good['img']."";?>" width="50px" height="50px" onerror='this.src="<?php echo $this->getWebSkinPath()."images/front/nopic_100_100.gif";?>"' /></td>
						<td class="t_l">
							<a class="blue" href="<?php echo IUrl::creatUrl("/site/products/id/".$good['goods_id']."");?>" target='_blank'><?php echo isset($good_info['name'])?$good_info['name']:"";?></a>
							<?php if($good_info['value']!=''){?><p><?php echo isset($good_info['value'])?$good_info['value']:"";?></p><?php }?>
						</td>
						<td><?php echo $good['point']*$good['goods_nums'];?></td>
						<td class="red2">￥<?php echo isset($good['goods_price'])?$good['goods_price']:"";?></td>
						<td class="red2">￥<?php echo $good['goods_price']-$good['real_price'];?></td>
						<td>x <?php echo isset($good['goods_nums'])?$good['goods_nums']:"";?></td>
						<td class="red2 bold">￥<?php echo $good['goods_nums']*$good['real_price'];?></td>
						<td>
							<?php echo Order_Class::goodsSendStatus($good['is_send']);?>
							<?php if($good['delivery_id']){?>
							<input type='button' class='sbtn' value='物流' onclick='freightLine(<?php echo isset($good['delivery_id'])?$good['delivery_id']:"";?>);' />
							<?php }?>
						</td>
					</tr>
                    <?php }?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="gray_box">
		<div class="t_part">
			<p>商品总金额：￥<?php echo isset($this->order_info['payable_amount'])?$this->order_info['payable_amount']:"";?></p>
			<p>+ 运费：￥<label id="freightFee"><?php echo isset($this->order_info['real_freight'])?$this->order_info['real_freight']:"";?></label></p>

            <?php if($this->order_info['taxes'] > 0){?>
            <p>+ 税金：￥<?php echo isset($this->order_info['taxes'])?$this->order_info['taxes']:"";?></p>
            <?php }?>

            <?php if($this->order_info['pay_fee'] > 0){?>
            <p>+ 支付手续费：￥<?php echo isset($this->order_info['pay_fee'])?$this->order_info['pay_fee']:"";?></p>
            <?php }?>

            <?php if($this->order_info['insured'] > 0){?>
            <p>+ 保价：￥<?php echo isset($this->order_info['insured'])?$this->order_info['insured']:"";?></p>
            <?php }?>

            <p>订单折扣或涨价：￥<?php echo isset($this->order_info['discount'])?$this->order_info['discount']:"";?></p>

            <?php if($this->order_info['promotions'] > 0){?>
            <p>- 促销优惠金额：￥<?php echo isset($this->order_info['promotions'])?$this->order_info['promotions']:"";?></p>
            <?php }?>
		</div>

		<div class="b_part">
			<p>订单支付金额：<span class="red2">￥<label><?php echo isset($this->order_info['order_amount'])?$this->order_info['order_amount']:"";?></label></span></p>
		</div>
	</div>
</div>

<script type="text/javascript">
//快递跟踪
function freightLine(doc_id)
{
	var urlVal = "<?php echo IUrl::creatUrl("/block/freight/id/@id@");?>";
	urlVal = urlVal.replace("@id@",doc_id);
	art.dialog.open(urlVal,{'title':'轨迹查询',width:'600px',height:'500px'});
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
