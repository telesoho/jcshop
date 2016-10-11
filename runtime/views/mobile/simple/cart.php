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
    
<div id="pageInfo" data-title="购物车"></div>
<?php if($this->count == 0){?>
<!-- 无商品显示 -->
<section class="nodata">购物车中空空如也哦~</section>
<?php }else{?>
<!-- 优惠信息 -->
<section class="cart_prompt" style="display:none" id="cart_prompt">
	<h4>恭喜，您的订单已经满足了以下优惠活动！</h4>
	<ol></ol>
</section>

<!--促销模板-->
<script type="text/html" id="promotionTemplate">
	<li><%=item['plan']%>，<%=item['info']%></li>
</script>

<!-- 商品列表 -->
<section class="cart_list">
	<ul>
		<?php foreach($this->goodsList as $key => $item){?>
		<li>
			<div class="cart_list_goods">
				<div class="cart_list_photo" onclick="gourl('<?php echo IUrl::creatUrl("/site/products/id/".$item['goods_id']."");?>')">
					<img src="<?php echo IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/80/h/80");?>" alt="<?php echo isset($item['name'])?$item['name']:"";?>">
				</div>
				<div class="cart_list_info">
					<h3 class="cart_list_info_title"><?php echo isset($item['name'])?$item['name']:"";?></h3>
					<p class="cart_list_info_info">
						<?php if(isset($item['spec_array'])){?> <?php $spec_array=Block::show_spec($item['spec_array']);?>
						<?php foreach($spec_array as $specName => $specValue){?>
						<?php echo isset($specName)?$specName:"";?>：<?php echo isset($specValue)?$specValue:"";?>&nbsp;
						<?php }?>
						<?php }else{?>
						祝您购物愉快!
						<?php }?>
					</p>
					<em class="cart_list_info_price">单价：￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></em>
				</div>
			</div>
			<?php $item_json = JSON::encode($item)?>
			<del class="del" onclick='javascript:removeCartByJSON(<?php echo isset($item_json)?$item_json:"";?>);'>删除</del>
			<div class="goods_num_adjust">
				<span onclick='cart_reduce(<?php echo isset($item_json)?$item_json:"";?>);'>-</span>
				<input type='text' onchange='cartCount(<?php echo isset($item_json)?$item_json:"";?>);' id="count_<?php echo isset($item['goods_id'])?$item['goods_id']:"";?>_<?php echo isset($item['product_id'])?$item['product_id']:"";?>" value="<?php echo isset($item['count'])?$item['count']:"";?>">
				<span onclick='cart_increase(<?php echo isset($item_json)?$item_json:"";?>);'>+</span>
			</div>
			<div class="count">小结：￥<span id="sum_<?php echo isset($item['goods_id'])?$item['goods_id']:"";?>_<?php echo isset($item['product_id'])?$item['product_id']:"";?>"><?php echo isset($item['sum'])?$item['sum']:"";?></span></div>
		</li>
		<?php }?>
	</ul>
</section>
<!-- 统计信息 -->
<section class="cart_count">
	<h4>购物车统计</h4>
	<table>
		<tr>
			<th>总重量</th>
			<th>总金额</th>
			<th>商品优惠</th>
			<th>促销优惠</th>
		</tr>
		<tr>
			<td><span id='weight'><?php echo $this->weight;?></span>g</td>
			<td>￥<span id='origin_price'><?php echo $this->sum;?></span></td>
			<td>￥<span id='discount_price'><?php echo $this->reduce;?></span></td>
			<td>￥<span id='promotion_price'><?php echo $this->proReduce;?></span></td>
		</tr>
	</table>
</section>

<footer class="cart_footer">
	<div class="cart_footer_fixed">
		<div class="buy btn_pay" id="btn_pay" onclick="gourl('<?php echo IUrl::creatUrl("/simple/cart2");?>')">
			去结算
		</div>
		<div class="count">
			<span>合计:</span>
			<em>￥<i id='sum_price'><?php echo $this->final_sum;?></i></em>
			<u>不含运费</u>
		</div>
	</div>
</footer>
<?php }?>
<script>
$(function(){
	// 隐藏底部导航
	hideNav();

	<?php if($this->promotion){?>
	<?php foreach($this->promotion as $key => $item){?>
	$('#cart_prompt ol').append( template.render('promotionTemplate',{"item":<?php echo JSON::encode($item);?>}) );
	<?php }?>
	$('#cart_prompt').show();
	<?php }?>

})
//购物车数量改动计算
function cartCount(obj)
{
	var countInput = $('#count_'+obj.goods_id+'_'+obj.product_id);
	var countInputVal = parseInt(countInput.val());
	var oldNum = countInput.data('oldNum') ? countInput.data('oldNum') : obj.count;

	//商品数量大于1件
	if(isNaN(countInputVal) || (countInputVal <= 0))
	{
		alert('购买的数量必须大于1件');
		countInput.val(1);
		countInput.change();
	}
	//商品数量小于库存量
	else if(countInputVal > parseInt(obj.store_nums))
	{
		alert('购买的数量不能大于此商品的库存量');
		countInput.val(parseInt(obj.store_nums));
		countInput.change();
	}
	else
	{
		var diff = parseInt(countInputVal) - parseInt(oldNum);
		if(diff == 0)
		{
			return;
		}

		var goods_id   = obj.product_id > 0 ? obj.product_id : obj.goods_id;
		var goods_type = obj.product_id > 0 ? "product"      : "goods";

		//更新购物车中此商品的数量
		$.getJSON("<?php echo IUrl::creatUrl("/simple/joinCart");?>",{"goods_id":goods_id,"type":goods_type,"goods_num":diff,"random":Math.random()},function(content){
			if(content.isError == true)
			{
				alert(content.message);
				countInput.val(1);
				countInput.change();
			}
			else
			{
				var goodsId   = [];
				var productId = [];
				var num       = [];
				$('[id^="count_"]').each(function(i)
				{
					var idValue = $(this).attr('id');
					var dataArray = idValue.split("_");

					goodsId.push(dataArray[1]);
					productId.push(dataArray[2]);
					num.push(this.value);
				});
				countInput.data('oldNum',countInputVal);
				$.getJSON("<?php echo IUrl::creatUrl("/simple/promotionRuleAjax");?>",{"goodsId":goodsId,"productId":productId,"num":num,"random":Math.random()},function(content){
					if(content.promotion.length > 0)
					{
						$('#cart_prompt li').remove();

						for(var i = 0;i < content.promotion.length; i++)
						{
							$('#cart_prompt ol').append( template.render('promotionTemplate',{"item":content.promotion[i]}) );
						}
						$('#cart_prompt').show();
					}
					else
					{
						$('#cart_prompt li').remove();
						$('#cart_prompt').hide();
					}

					/*开始更新数据*/
					$('#weight').html(content.weight);
					$('#origin_price').html(content.sum);
					$('#discount_price').html(content.reduce);
					$('#promotion_price').html(content.proReduce);
					$('#sum_price').html(content.final_sum);
					$('#sum_'+obj.goods_id+'_'+obj.product_id).html((obj.sell_price * countInputVal).toFixed(2));
				});
			}
		});
	}
}

//增加商品数量
function cart_increase(obj){
	//库存超量检查
	var countInput = $('#count_'+obj.goods_id+'_'+obj.product_id);
	if(parseInt(countInput.val()) + 1 > parseInt(obj.store_nums))
	{
		alert('购买的数量大于此商品的库存量');
	}
	else
	{
		countInput.val(parseInt(countInput.val()) + 1);
		countInput.change();
	}
}

//减少商品数量
function cart_reduce(obj){
	//库存超量检查
	var countInput = $('#count_'+obj.goods_id+'_'+obj.product_id);
	if(parseInt(countInput.val()) - 1 <= 0)
	{
		alert('购买的数量必须大于1件');
	}
	else
	{
		countInput.val(parseInt(countInput.val()) - 1);
		countInput.change();
	}
}

//移除购物车
function removeCartByJSON(obj)
{
	var goods_id   = obj.product_id > 0 ? obj.product_id : obj.goods_id;
	var goods_type = obj.product_id > 0 ? "product"      : "goods";
	$.getJSON("<?php echo IUrl::creatUrl("/simple/removeCart");?>",{"goods_id":goods_id,"type":goods_type,"random":Math.random()},function()
	{
		window.location.reload();
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
