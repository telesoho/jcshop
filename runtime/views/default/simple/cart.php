<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<title>九猫首页</title>
	<link rel="stylesheet" href="/views/default/skin/default/css/mui.css" />
	<link rel="stylesheet" href="/views/default/skin/default/css/common.css" />
	<link rel="stylesheet" href="/views/default/skin/default/css/icons-extra.css" />
	<link rel="stylesheet" href="/views/default/skin/default/css/app/shopcar.css" />

</head>
<body>
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
<!--购物车界面-->
<header class="mui-bar mui-bar-nav">
	<a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
	<h1 class="mui-title">购物车</h1>
</header>
<div class="mui-content shopcar-content" style="padding-bottom:61px">
	<!--购物车空的状态-->
	<!--<div class="empty empty-state">-->
		<!--<img src="/views/default/skin/default/images/blm/empty.jpg">-->
		<!--<p>亲、购物车空空</p>-->
		<!--<a href="index.html" class="goshopping mui-btn mui-btn-danger">前去逛逛</a>-->
	<!--</div>-->
	<!--购物车存在商品的状态-->
	<div id="shopcar">
		<form class="mui-input-group"></form>
	</div>
	<!--统计项-->
	<div id="shopcar-cal">
		<ul class="mui-table-view">
			<li class="mui-table-view-cell">
				<div>商品金额(不含税)</div>
				<div>
					<p>￥0元</p>
					<p><a href="#" >还差0元免邮费,去凑单吧</a></p>
				</div>
			</li>
			<li class="mui-table-view-cell">
				<div>综合税总额</div>
				<div>
					<p>￥0元</p>
				</div>
			</li>
			<li class="mui-table-view-cell">
				<div>总金额</div>
				<div>
					<p class="totalPrice">￥18.72元</p>
				</div>
			</li>
			<li class="mui-table-view-cell">
				<a href="cart2.html" type="button" class="mui-btn mui-btn-danger calTatal totalCal">结算（2）</a>
			</li>
		</ul>
	</div>
</div>
<footer>
	<nav class="mui-bar mui-bar-tab">
		<div class="mui-content">
			<a id="defaultTab" class="mui-tab-item " href="index.html">
				<span class="mui-icon mui-icon-home"></span>
				<span class="mui-tab-label">首页</span>
			</a>
			<a class="mui-tab-item" href="#">
				<span class="mui-icon mui-icon-list"></span>
				<span class="mui-tab-label">分类</span>
			</a>
			<a class="mui-tab-item mui-active" href="shopcar.html">
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
<script src="/views/default/javascript/jquery.min.js"></script>
<script src="/views/default/javascript/mui.js"></script>
<script src="/views/default/javascript/lazyload.js"></script>
<script>
	var str="";

	mui.init();
	mui.ajax('index.php?controller=apic&action=cart',{
		dataType:'json',//服务器返回json格式数据
		type:'get',//HTTP请求类型
		timeout:10000,//超时时间设置为10秒；
		headers:{'Content-Type':'application/json'},
		success:function(data){
			var str='';
			if(data.goodsList.length==''){
				$('.shopcar-content').html('<div class="empty">'+
						'<img src="/views/default/skin/default/images/blm/empty.jpg">'+
						'<p>亲、购物车空空</p>'+
						'<a href="index.html" class="goshopping mui-btn mui-btn-danger">前去逛逛</a>'+
						'</div>');
			}else{
				for(var i=0;i<data.goodsList.length;i++){
					var objJson=JSON.stringify(data.goodsList[i]);
					console.log(objJson);
				str+='<div class=" mui-checkbox shopcar-list">'+
						'<div class="ware-list">'+
							'<div class="ware-list-title">'+
							data.goodsList[i].name+
							'</div>'+
						'<div class="ware-list-body">'+
							'<div class="ware-img">'+
							'<img src="'+data.goodsList[i].img+'" alt=""  / >'+
							'</div>'+
							'<div class="ware-content">'+
							'<span class="price">￥'+data.goodsList[i].sell_price+'</span>'+
					'<span class="num" >数量: '+data.goodsList[i].count+'枚</span>'+
					'<span class="other">综合税: 0</span>'+
					'</div>'+
					'</div>'+
					'</div>'+
					'<div class="shop-action">'+
							'<span class="mui-icon mui-icon-trash" data1="'+data.goodsList[i].goods_id+'" data2="'+data.goodsList[i].product_id+'"></span>'+
							'</div>'+
							'<div class="mui-numbox">'+
							'<button class="mui-btn mui-btn-numbox-minus" type="button"  onclick=' + "'cart_reduce(" + objJson + ")'" + ' >-</button>'+
							'<input id="count_'+data.goodsList[i].goods_id+'_'+data.goodsList[i].product_id+'" class="mui-input-numbox" type="number" value="'+data.goodsList[i].count+'"  onchange=' + "'cartCount(" + objJson + ")'" + '  />'+
							'<button class="mui-btn mui-btn-numbox-plus" type="button"  onclick=' + "'cart_increase(" + objJson + ")'" + ' >+</button>'+
							'</div>'+
							'</div>';
				}
				$('.mui-input-group').html(str);
				$('.totalPrice').html(data.final_sum+'元');
				$('.totalCal').html('结算（'+data.count+'）');
			}

		},
		error:function(xhr,type,errorThrown){
			console.log(type);
		}
	});
	//页面加载动画的调用
	$(window).load(function(){
		$("#loading").fadeOut(300);

		//删除商品
		mui('body').on('tap', '.mui-icon-trash', function(index) {
			var elem = this;
			var btnArray = ['否', '是'];
			mui.confirm('' ,'是否删除该商品', btnArray, function(e) {
				if (e.index == 1) {
					var goods_id   = elem.getAttribute("data2") > 0 ? elem.getAttribute("data2") : elem.getAttribute("data1");
					var goods_type = elem.getAttribute("data2") > 0 ? "product"      : "goods";
					$.getJSON("<?php echo IUrl::creatUrl("/simple/removeCart");?>",{"goods_id":goods_id,"type":goods_type,"random":Math.random()},function()
					{
						window.location.reload();
					});
				}
			})

		});

	})
	//解决tab选项卡a标签无法跳转的问题
	mui('body').on('tap','.mui-tab-item',function(){document.location.href=this.href;});
	mui('body').on('tap','.calTatal',function(){document.location.href=this.href;});
	mui('body').on('tap','.goshopping',function(){document.location.href=this.href;});
	//购物车数量改动计算
	function cartCount(obj)
	{   var countInput = $('#count_'+obj.goods_id+'_'+obj.product_id);
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
							$('#cart_prompt .indent').remove();

							for(var i = 0;i < content.promotion.length; i++)
							{
								$('#cart_prompt').append( template.render('promotionTemplate',{"item":content.promotion[i]}) );
							}
							$('#cart_prompt').show();
						}
						else
						{
							$('#cart_prompt .indent').remove();
							$('#cart_prompt').hide();
						}
						/*开始更新数据*/
						$('.totalCal').html('结算（'+content.count+'）');
						$('.totalPrice').html(content.final_sum+'元');
						for(var i=0;i<content.goodsList.length;i++){
							console.log(content);
							$('.num').eq(i).html('数量'+content.goodsList[i].count+'枚');
						}
					});
				}
			});
		}
	}

	//增加商品数量
	function cart_increase(obj)
	{   //库存超量检查
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
	function cart_reduce(obj)
	{
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

</script>
</body>
</html>