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
	<h1 class="mui-title">确认订单</h1>
</header>
<div class="mui-content" style="padding-bottom:61px">
	<div id="address">
	</div>
	<div id="wareListShop" style="margin-top:1rem">
	</div>
</div>
<footer>
		<div id="footer-fixed">
		</div>
</footer>
<script src="/views/default/javascript/jquery.min.js"></script>
<script src="/views/default/javascript/template-native.js"></script>
<script src="/views/default/javascript/mui.js"></script>
<script src="/views/default/javascript/lazyload.js"></script>
<script id="test" type="text/html">
	<%if(addressList.length==0){%>
	<!--如果没有收货地址-->
	<a href="<?php echo IUrl::creatUrl("/simple/addaddress");?>" class="addAddr">添加收货地址</a>
	<%}else{%>
	<a href="<?php echo IUrl::creatUrl("/simple/addresslist");?>" class="editAddr">
	<span class="name"><%=addressList[0].accept_name%></span>
	<span class="phone"><%=addressList[0].mobile%></span>
	<span class="addr"><%=addressList[0].address%></span>
	</a>
	<%}%>
</script>
<script id="wareList" type="text/html">
	<div class="warelist">
		<ul class="mui-table-view">
			<li class="mui-table-view-cell">
				<div class="title">日本直邮！<span class="activity">新用户首单满188包邮</span></div>
			</li>
			<li class="mui-table-view-cell mui-media">
				<a href="javascript:;">
					<img class=" mui-pull-left" src="<%=goodsList[0].img%>">
					<div class="mui-media-body">
						<span class="title-list"><%=goodsList[0].name%></span>
						<p class='mui-ellipsis'>数量：<%=goodsList[0].count%></p>
						<p class='mui-ellipsis' >
							<span style="color:black;">￥<%=goodsList[0].sell_price%></span>
						<span>×<%=goodsList[0].count%>件</span></p>
					</div>
				</a>
			</li>
			<%if(goodsList.length>1){%>
				<div id="collapse-wareList-item" style="display:none">
				<% for(var i=1; i<goodsList.length; i++){%>
					<li class="mui-table-view-cell mui-media">
						<a href="javascript:;">
							<img class=" mui-pull-left" src="<%=goodsList[i].img%>">
							<div class="mui-media-body">
								<span class="title-list"><%=goodsList[i].name%></span>
								<p class='mui-ellipsis'>数量：<%=goodsList[i].count%></p>
								<p class='mui-ellipsis' >
									<span style="color:black;">￥<%=goodsList[i].sell_price%></span>
									<span>×<%=goodsList[0].count%>件</span></p>
							</div>
						</a>
					</li>
				<%}%>
				</div>
					<li class="mui-table-view-cell" id="collapse-wareList" >
						<span class="collapse" >另外显示<%=goodsList.length-1%>(共<%=goodsList.length%>件)
							<span class="mui-icon  mui-icon-arrowdown icon-sign"></span>
						</span>
					</li>
			<%}else{%>
			<%}%>
			<li class="mui-table-view-cell">
				<span>商品金额</span>
				<span class="mui-pull-right" style="color:#e53e42">￥<%=sum%></span>
			</li>
			<li class="mui-table-view-cell">
				<span>订单邮费</span>
				<span class="mui-pull-right" style="color:#e53e42">￥<%=proReduce%> (免邮费)
					</span>
			</li>
			<li class="mui-table-view-cell">
				<span>优惠券</span>
				<span class="mui-pull-right" style="color:#e53e42">
					暂无优惠券</span>
			</li>
			<li class="mui-table-view-cell">
				<span>订单金额</span>
				<span class="mui-pull-right" style="color:#e53e42">￥
					<%=final_sum%></span>
			</li>
			<li class="mui-table-view-cell">
				<span style="color:rgb(218, 57, 57)">安全提醒：</span>
				<span style="color:#959595">付款成功后，波罗蜜不会以付款异常、卡单、系统升级为由联系您。请勿泄露银行卡号、手机验证码，否则会造成钱款的损失。如有疑问咨询客服，谨防电话诈骗！</span>
			</li>
		</ul>
		<ul class="mui-table-view" style="margin-top:1rem;margin-bottom:6rem;">
			<li class="mui-table-view-cell">
				<span>留言备注</span>
				<span class="mui-pull-right user-book">
						<textarea rows="2" placeholder="" maxlength="28"></textarea>
					</span>
			</li>
		</ul>
	</div>
</script>
<script id="footer-content" type="text/html">
	<ul class="mui-table-view">
		<li class="mui-table-view-cell">
			共<span style="color:#e53e42"><%=count%></span>件商品
			<span class="mui-pull-right">应付金额：<span style="color:#e53e42">￥<%=final_sum%></span></span>
		</li>
		<%if(addressList.length==0){%>
		<!--如果没有收货地址-->
		<li class="mui-table-view-cell payware">
			<a href="<?php echo IUrl::creatUrl("/simple/addaddress");?>" type="button" class="mui-btn mui-btn-danger payWare">添加收货地址</a>
		</li>
		<%}else{%>
		<li class="mui-table-view-cell payware">
			<!--要跳到支付页面-->
			<a href="" type="button" class="mui-btn mui-btn-danger payWare">确认支付</a>
		</li>
		<%}%>
	</ul>
</script>
<script>
	var addr=true;//判断用户是否提供地址
	mui.ajax('index.php?controller=apic&action=cart2',{
		dataType:'json',//服务器返回json格式数据
		type:'get',//HTTP请求类型
		timeout:10000,//超时时间设置为10秒；
		headers:{'Content-Type':'application/json'},
		success:function(data){
			//服务器返回响应，根据响应结果，分析是否登录成功；
            console.log(data);
			console.log(data.addressList.accept_name)
			var data=data;
			if(data.addressList.length==0){
				addr=false;
			}
			data.addressList[0].telphone=data.addressList[0].telphone?addressList[0].telphone:'1768978455';
			var html1 = template('test',data);
			document.getElementById("address").innerHTML = html1;
			var html2 = template('wareList',data);
			document.getElementById("wareListShop").innerHTML = html2;
			var html3 = template('footer-content',data);
			document.getElementById("footer-fixed").innerHTML = html3;

		},
		error:function(xhr,type,errorThrown){
			//异常处理；
			console.log(type);
		}
	});
	//页面加载动画的调用
	$(window).load(function(){
		$("#loading").fadeOut(300);
	})
	var turn=true;
	//解决tab选项卡a标签无法跳转的问题
	mui('body').on('tap','.payWare',function(){
			document.location.href=this.href;
	});
	mui('body').on('tap','.addAddr',function(){
		document.location.href=this.href;
	});
	mui('body').on('tap','.editAddr',function(){
		document.location.href=this.href;
	});
	mui('body').on('tap','#collapse-wareList',function(){
		if(turn){
			turn=false;
			document.querySelector('.icon-sign').className="mui-icon  mui-icon-arrowup icon-sign";
			document.getElementById("collapse-wareList-item").style.display='block';
		}else{
			turn=true;
			document.querySelector('.icon-sign').className="mui-icon  mui-icon-arrowdown icon-sign";
			document.getElementById("collapse-wareList-item").style.display='none';
		}
	});

</script>
</body>
</html>