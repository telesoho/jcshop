<?php $menuData=menu::init($this->admin['role_id']);?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>后台管理</title>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/admin.css";?>" />
	<meta name="robots" content="noindex,nofollow">
	<link rel="shortcut icon" href="<?php echo IUrl::creatUrl("")."favicon.ico";?>" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
	<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
	<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/admin.js";?>"></script>
</head>
<body>
	<div class="container">
		<div id="header">
			<div class="logo">
				<a href="<?php echo IUrl::creatUrl("/system/default");?>"><img src="<?php echo $this->getWebSkinPath()."images/admin/logo.png";?>" width="303" height="43" /></a>
			</div>
			<div id="menu">
				<ul name="topMenu">
					<?php foreach(menu::getTopMenu($menuData) as $key => $item){?>
					<li>
						<a hidefocus="true" href="<?php echo IUrl::creatUrl("".$item."");?>"><?php echo isset($key)?$key:"";?></a>
					</li>
					<?php }?>
				</ul>
			</div>
			<p><a href="<?php echo IUrl::creatUrl("/systemadmin/logout");?>">退出管理</a> <a href="<?php echo IUrl::creatUrl("/system/admin_repwd");?>">修改密码</a> <a href="<?php echo IUrl::creatUrl("/system/default");?>">后台首页</a> <a href="<?php echo IUrl::creatUrl("");?>" target='_blank'>商城首页</a> <span>您好 <label class='bold'><?php echo isset($this->admin['admin_name'])?$this->admin['admin_name']:"";?></label>，当前身份 <label class='bold'><?php echo isset($this->admin['admin_role_name'])?$this->admin['admin_role_name']:"";?></label></span></p>
		</div>
		<div id="info_bar">
			<label class="navindex"><a href="<?php echo IUrl::creatUrl("/system/navigation");?>">快速导航管理</a></label>
			<span class="nav_sec">
			<?php $adminId = $this->admin['admin_id']?>
			<?php $query = new IQuery("quick_naviga");$query->where = "admin_id = $adminId and is_del = 0";$items = $query->find(); foreach($items as $key => $item){?>
			<a href="<?php echo isset($item['url'])?$item['url']:"";?>" class="selected"><?php echo isset($item['naviga_name'])?$item['naviga_name']:"";?></a>
			<?php }?>
			</span>
		</div>

		<div id="admin_left">
			<ul class="submenu">
				<?php $leftMenu=menu::get($menuData,IWeb::$app->getController()->getId().'/'.IWeb::$app->getController()->getAction()->getId())?>
				<?php foreach(current($leftMenu) as $key => $item){?>
				<li>
					<span><?php echo isset($key)?$key:"";?></span>
					<ul name="leftMenu">
						<?php foreach($item as $leftKey => $leftValue){?>
						<li><a href="<?php echo IUrl::creatUrl("".$leftKey."");?>"><?php echo isset($leftValue)?$leftValue:"";?></a></li>
						<?php }?>
					</ul>
				</li>
				<?php }?>
			</ul>
			<div id="copyright"></div>
		</div>

		<div id="admin_right">
			<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/my97date/wdatepicker.js"></script>
<div class="headbar">
	<div class="position">订单<span>></span><span>订单管理</span><span>></span><span>订单列表</span></div>
	<div class="operating">
		<a href="javascript:void(0);"><button class="operating_btn" type="button" onclick="window.location='<?php echo IUrl::creatUrl("/order/order_edit");?>'"><span class="addition">添加订单</span></button></a>
		<a href="javascript:void(0);" onclick="selectAll('id[]')"><button class="operating_btn" type="button"><span class="sel_all">全选</span></button></a>
		<a href="javascript:void(0);" onclick="delModel({form:'orderForm',name:'id[]'})"><button class="operating_btn" type="button"><span class="delete">批量删除</span></button></a>
		<a href="javascript:void(0);" onclick="$('#orderForm').attr('action','<?php echo IUrl::creatUrl("/order/expresswaybill_template");?>');$('#orderForm').submit();"><button class="operating_btn"><span class="export">批量打印快递单</span></button></a>
		<a href="javascript:void(0);"><button class="operating_btn" onclick="location.href='<?php echo IUrl::creatUrl("/order/print_template");?>'"><span class="export">单据模板</span></button></a>
		<a href="javascript:void(0);"><button class="operating_btn" type="button" onclick="location.href='<?php echo IUrl::creatUrl("/order/order_recycle_list");?>'"><span class="recycle">回收站</span></button></a>
	</div>
	<div class="searchbar">
		<form action="<?php echo IUrl::creatUrl("/");?>" method="get" name="order_list">
			<input type='hidden' name='controller' value='order' />
			<input type='hidden' name='action' value='order_list' />
			<select name="search[is_seller]" class="auto">
				<option value="">选择类型</option>
				<option value="self">平台自营</option>
				<option value="seller">商家订单</option>
			</select>

			<select name="search[pay_status]" class="auto">
				<option value="">选择支付状态</option>
				<option value="0">未支付</option>
				<option value="1">已支付</option>
			</select>

			<select name="search[distribution_status]" class="auto">
				<option value="">选择发货状态</option>
				<option value="0">未发货</option>
				<option value="1">已发货</option>
				<option value="2">部分发货</option>
			</select>

			<select name="search[status]" class="auto">
				<option value="">选择订单状态</option>
				<option value="1">新订单</option>
				<option value="2">确认订单</option>
				<option value="3">取消订单</option>
				<option value="4">作废订单</option>
				<option value="5">完成订单</option>
				<option value="6">退款</option>
				<option value="7">部分退款</option>
			</select>

			<select class="auto" name="search[name]">
				<option value="">选择订单条件</option>
				<option value="accept_name">收件人姓名</option>
				<option value="order_no">订单号</option>
				<option value="seller_name">商户真实名称</option>
			</select>
			<input class="small" name="search[keywords]" type="text" value="" />
			<button class="btn" type="button" onclick='initSearchbar(1)'><span class="add">更 多</span></button>
			<button class="btn" type="submit"  onclick='changeAction(false)'><span class="sel">筛 选</span></button>
			<button class="btn" onclick='changeAction(true)'><span class="sel">导出Excel</span></button>
			<input type="hidden" name="search[adv_search]" value="" />
			<input type="hidden" name="search[send_time]" value="" />
			<input type="hidden" name="search[create_time]" value="" />
			<input type="hidden" name="search[completion_time]" value="" />
			<input type="hidden" name="search[order_amount]" value="" />
		</form>
	</div>
	<div class="searchbar" id="adv_searchbar" style="display:none;">
		订单总额：<input type="text" class="tiny" name="order_amount_start" id="order_amount_start" pattern="float" value="" />-
		<input type="text" class="tiny" name="order_amount_end" id="order_amount_end" pattern="float" value="" />
		下单：<input class="small" type="text" name="create_time_start" id="create_time_start" onfocus="WdatePicker()" value="" />-
		<input class="small" type="text" name="create_time_end" id="create_time_end" onfocus="WdatePicker()" value="" />
		发货：<input class="small" type="text" name="send_time_start" id="send_time_start" onfocus="WdatePicker()" value="" />-
		<input class="small" type="text" name="send_time_end" id="send_time_end" onfocus="WdatePicker()" value="" />
		完成：<input class="small" type="text" name="completion_time_start" id="completion_time_start" onfocus="WdatePicker()" value="" />-
		<input class="small" type="text" name="completion_time_end" id="completion_time_end" onfocus="WdatePicker()" value="" />
	</div>
</div>

<form name="orderForm" id="orderForm" action="<?php echo IUrl::creatUrl("/order/order_del");?>" method="post">
	<div class="content">
		<table class="list_table">
			<colgroup>
				<col width="30px" />
				<col width="130px" />
				<col width="70px" />
				<col width="75px" />
				<col width="75px" />
				<col width="75px" />
				<col width="115px" />
				<col width="70px" />
				<col width="70px" />
				<col width="115px" />
				<col width="110px" />
			</colgroup>

			<thead>
				<tr>
					<th>选择</th>
					<th>订单号</th>
					<th>收货人</th>
					<th>支付状态</th>
					<th>发货状态</th>
					<th>配送方式</th>
					<th>打印</th>
					<th>支付方式</th>
					<th>用户名</th>
					<th>下单时间</th>
					<th>操作</th>
				</tr>
			</thead>

			<tbody>
				<?php foreach($this->orderHandle->find() as $key => $item){?>
				<tr>
					<td><input name="id[]" type="checkbox" value="<?php echo isset($item['id'])?$item['id']:"";?>" /></td>
					<td title="<?php echo isset($item['order_no'])?$item['order_no']:"";?>" name="orderStatusColor<?php echo isset($item['status'])?$item['status']:"";?>"><?php echo isset($item['order_no'])?$item['order_no']:"";?></td>
					<td title="<?php echo isset($item['accept_name'])?$item['accept_name']:"";?>"><?php echo isset($item['accept_name'])?$item['accept_name']:"";?></td>
					<td name="payStatusColor<?php echo isset($item['pay_status'])?$item['pay_status']:"";?>"><?php echo Order_Class::getOrderPayStatusText($item);?></td>
					<td name="disStatusColor<?php echo isset($item['distribution_status'])?$item['distribution_status']:"";?>"><?php echo Order_Class::getOrderDistributionStatusText($item);?></td>
					<td title="<?php echo isset($item['distribute_name'])?$item['distribute_name']:"";?>"><?php echo isset($item['distribute_name'])?$item['distribute_name']:"";?></td>
					<td>
						<span class="prt" title="购物清单打印" onclick="window.open('<?php echo IUrl::creatUrl("/order/shop_template/id/".$item['id']."");?>');">购</span>
						<span class="prt" title="配货单打印" onclick="window.open('<?php echo IUrl::creatUrl("/order/pick_template/id/".$item['id']."");?>');">配</span>
						<span class="prt" title="联合打印" onclick="window.open('<?php echo IUrl::creatUrl("/order/merge_template/id/".$item['id']."");?>');">合</span>
						<span class="prt" title="快递单打印" onclick="window.open('<?php echo IUrl::creatUrl("/order/expresswaybill_template/id/".$item['id']."");?>');">递</span>
					</td>
					<td><?php echo isset($item['payment_name'])?$item['payment_name']:"";?></td>
					<td>
						<?php if($item['user_id'] == 0){?>
						游客
						<?php }else{?>
						<?php $query = new IQuery("user");$query->where = "id = $item[user_id]";$items = $query->find(); foreach($items as $key => $user){?>
						<?php echo isset($user['username'])?$user['username']:"";?>
						<?php }?>
						<?php }?>
					</td>
					<td title="<?php echo isset($item['create_time'])?$item['create_time']:"";?>"><?php echo isset($item['create_time'])?$item['create_time']:"";?></td>
					<td>
						<a href="<?php echo IUrl::creatUrl("/order/order_show/id/".$item['id']."");?>"><img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_check.gif";?>" title="查看" /></a>
						<?php if(Order_class::getOrderStatus($item) < 3){?>
						<a href="<?php echo IUrl::creatUrl("/order/order_edit/id/".$item['id']."");?>"><img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_edit.gif";?>" title="编辑"/></a>
						<?php }?>
						<a href="javascript:void(0)" onclick="delModel({link:'<?php echo IUrl::creatUrl("/order/order_del/id/".$item['id']."");?>'})" ><img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_del.gif";?>" title="删除"/></a>

						<?php if($item['seller_id']){?>
						<a href="<?php echo IUrl::creatUrl("/site/home/id/".$item['seller_id']."");?>" target="_blank"><img src="<?php echo $this->getWebSkinPath()."images/admin/seller_ico.png";?>" /></a>
						<?php }?>
					</td>
				</tr>
				<?php }?>
			</tbody>
		</table>
	</div>
	<?php echo $this->orderHandle->getPageBar();?>
</form>

<script type='text/javascript'>
//DOM加载结束
$(function(){
	<?php if($this->search){?>
	var searchData = <?php echo JSON::encode($this->search);?>;
	for(var index in searchData)
	{
		$('[name="search['+index+']"]').val(searchData[index]);
	}
	<?php }?>
	$("#order_amount_start").blur(function(){
		setOrderAmountVal();
	});
	$("#order_amount_end").blur(function(){
		setOrderAmountVal();
	});
	initSearchbar(0);

	//高亮色彩
	$('[name="payStatusColor1"]').addClass('green');
	$('[name="disStatusColor1"]').addClass('green');
	$('[name="orderStatusColor3"]').addClass('red');
	$('[name="orderStatusColor4"]').addClass('red');
	$('[name="orderStatusColor5"]').addClass('green');
});
function changeAction(excel)
{
	setDatetimeVal('send_time');
	setDatetimeVal('create_time');
	setDatetimeVal('completion_time');
	if (excel){
		$("input[name=\"action\"]").val("order_report");
		$("form[name=\"order_list\"]").attr("target", "_blank");
	}else{
		$("input[name=\"action\"]").val("order_list");
		$("form[name=\"order_list\"]").attr("target", "_self");
	}
}

// 设置订单总额的值
function setOrderAmountVal()
{
	var order_amount_start = $('#order_amount_start').val();
	var order_amount_end = $('#order_amount_end').val();
	var order_amount = '';
	order_amount_start = parseFloat(order_amount_start);
	order_amount_end = parseFloat(order_amount_end);
	if(isNaN(order_amount_start))
	{
		order_amount_start = 0;
	}
	if(isNaN(order_amount_end))
	{
		order_amount_end = 0;
	}
	if(order_amount_start!=0 || order_amount_end!=0)
	{
		if(order_amount_start > order_amount_end)
		{
			order_amount = order_amount_end + ',' + order_amount_start;
		}
		else
		{
			order_amount = order_amount_start + ',' + order_amount_end;
		}
	}
	$('input[name="search[order_amount]"]').val(order_amount);
	return true;
}

// 设置日期的值
function setDatetimeVal(name)
{
	var date_start = $('#'+ name +'_start').val();
	var date_end = $('#'+ name +'_end').val();
	var date_val = '';
	if('' != date_start && '' != date_end)
	{
		var start_time = Date.parse(date_start);
		var end_time = Date.parse(date_end);
		if(start_time > end_time)
		{
			date_val = date_end + ',' + date_start;
		}
		else
		{
			date_val = date_start + ',' + date_end;
		}
	}
	else if ('' != date_start)
	{
		date_val = date_start;
	}
	else if ('' != date_end)
	{
		date_val = date_end;
	}
	$('input[name="search['+ name +']"]').val(date_val);
	return true;
}

// 初始化高级筛选
function initSearchbar(from)
{
	var adv_search = $('input[name="search[adv_search]"]').val();
	if(1 == from)
	{
		// 更多按钮
		adv_search = '1'==adv_search ? '' : '1';
		$('input[name="search[adv_search]"]').val(adv_search);
	}
	if('1' == adv_search)
	{
		$('#adv_searchbar').show();
		var order_amount = $('input[name="search[order_amount]"]').val();
		if('' != order_amount)
		{
			var order_amount_arr = order_amount.split(",");
			$('#order_amount_start').val(order_amount_arr[0]);
			$('#order_amount_end').val(order_amount_arr[1]);
		}
		convertDatetimeVal('send_time');
		convertDatetimeVal('create_time');
		convertDatetimeVal('completion_time');
	}
	else
	{
		$('#adv_searchbar').hide();
	}
}

// 转换日期的值，设置高级筛选的初始值
function convertDatetimeVal(name)
{
	var date_val = $('input[name="search['+ name +']"]').val();
	if('' != date_val)
	{
		var date_arr = date_val.split(",");
		var len = date_arr.length;
		switch(len)
		{
			case 1:
				$('#'+ name +'_start').val(date_arr[0]);
				break;
			case 2:
				$('#'+ name +'_start').val(date_arr[0]);
				$('#'+ name +'_end').val(date_arr[1]);
				break;
		}
	}
	return true;
}
</script>
		</div>
	</div>

	<script type='text/javascript'>
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

	//按钮高亮
	var topItem  = "<?php echo key($leftMenu);?>";
	$("ul[name='topMenu']>li:contains('"+topItem+"')").addClass("selected");

	var leftItem = "<?php echo IUrl::getUri();?>";
	$("ul[name='leftMenu']>li a[href^='"+leftItem+"']").parent().addClass("selected");
	</script>
</body>
</html>
