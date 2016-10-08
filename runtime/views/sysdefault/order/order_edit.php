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
			<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/areaSelect/areaSelect.js"></script>
<div class="headbar clearfix">
	<div class="position">订单<span>></span><span>订单管理</span><span>></span><span><?php if(isset($this->orderRow)){?>订单修改<?php }else{?>订单添加<?php }?></span></div>

	<div class="operating">
		<a href="javascript:void(0);"><button class="operating_btn" type="button" onclick="searchGoods('<?php echo IUrl::creatUrl("/block/search_goods/is_products/1");?>',searchGoodsCallback);"><span class="addition">添加商品</span></button></a>
	</div>

	<ul class="tab" name="menu">
		<li id="button_1" class="selected"><a href="javascript:selectTab('1');" hidefocus="true">商品信息</a></li>
		<li id="button_2"><a href="javascript:selectTab('2');" hidefocus="true">订单配置</a></li>
		<li id="button_3"><a href="javascript:selectTab('3');" hidefocus="true">收货人信息</a></li>
	</ul>

	<!--列表头-->
	<div class="field">
		<table class="list_table">
			<colgroup>
				<col />
				<col width="120px" />
				<col width="120px" />
				<col width="120px" />
			</colgroup>

			<thead>
				<tr>
					<th>商品名称</th>
					<th>商品价格</th>
					<th>商品数量</th>
					<th>操作</th>
				</tr>
			</thead>
		</table>
	</div>
</div>

<form name="ModelForm" action="<?php echo IUrl::creatUrl("/order/order_update");?>" method="post">
	<input type='hidden' name='id' value='' />
	<input type='hidden' name='takeself' value='' />

	<!--商品信息-->
	<div id="tab-1">
		<div class="content">
			<table class="list_table">
				<col />
				<col width="120px" />
				<col width="120px" />
				<col width="120px" />

				<tbody id="goodsBox"></tbody>

				<!--商品模板-->
				<script type='text/html' id='goodsTrTemplate'>
				<tr>
					<input type='hidden' name='goods_id[]' value='<%=item.id%>' />
					<input type='hidden' name='product_id[]' value='<%=item.product_id%>' />
					<td>
						<%=item.name%>
						<%if(item.spec_array){%>
							<label class="attr">
							<%var spec_array = parseJSON(item.spec_array)%>
							<%for(var index in spec_array){%>
								<%var value = spec_array[index]%>
								<%=value['name']%>:
								<%if(value['type'] == 1){%>
									<%=value['value']%>
								<%}else{%>
									<img src="<?php echo IUrl::creatUrl();?><%=value['value']%>" width="15px" height="15px" class="spec_photo" />
								<%}%>
							<%}%>
							</label>
						<%}%>
						【
						<%if(item.products_no){%>
							<%=item.products_no%>
						<%}else{%>
							<%=item.goods_no%>
						<%}%>
						】
					</td>
					<td><%=item.real_price%></td>
					<td><input class="tiny" name="goods_nums[]" value="<%=item.goods_nums ? item.goods_nums : 1%>" /></td>
					<td>
						<a href="javascript:void(0)" onclick="$(this).parent().parent().remove();">
							<img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_del.gif";?>" alt="删除" />
						</a>
					</td>
				</tr>
				</script>
			</table>
		</div>
	</div>

	<!--订单配置-->
	<div id="tab-2" class="content_box" style="display:none">
		<div class="content form_content">
			<table class="form_table">
				<col width="150px" />
				<col />

				<tbody>
					<tr>
						<th>配送方式：</th>
						<td>
							<select name="distribution" alt="请选择配送方式" class="normal" onchange="selectTakeself(this);">
								<?php $query = new IQuery("delivery");$query->where = "is_delete = 0";$items = $query->find(); foreach($items as $key => $item){?>
								<option value="<?php echo isset($item['id'])?$item['id']:"";?>" datatype="<?php echo isset($item['type'])?$item['type']:"";?>"><?php echo isset($item['name'])?$item['name']:"";?></option>
								<?php }?>
							</select>
							<p id="takeself"></p>

							<!--自提点模板-->
							<script type='text/html' id='takeselfTemplate'>
								<%=item['province_str']%> <%=item['city_str']%> <%=item['area_str']%> <%=item['address']%> <%=item['name']%> <%=item['phone']%> <%=item['mobile']%>
							</script>
						</td>
					</tr>
					<tr>
						<th>配送运费：</th>
						<td>
							<input class="small" type="text" name="real_freight" value="" pattern="float" empty />￥
							<label>不填写运费则系统自动计算</label>
						</td>
					</tr>
					<tr>
						<th>支付方式：</th>
						<td>
							<select name="pay_type" pattern="required" alt="请选择配送方式" class="normal">
								<option value="">请选择支付</option>
								<?php $query = new IQuery("payment");$query->where = "status = 0";$items = $query->find(); foreach($items as $key => $item){?>
								<option value="<?php echo isset($item['id'])?$item['id']:"";?>"><?php echo isset($item['name'])?$item['name']:"";?></option>
								<?php }?>
							</select>
						</td>
					</tr>
					<tr>
						<th>是否要发票：</th>
						<td>
							<label class='attr'><input type="checkbox" name="invoice" value="1" />发票</label>
						</td>
					</tr>
					<tr>
						<th>发票抬头：</th>
						<td><input class="normal" type="text" name="invoice_title" value="" /></td>
					</tr>
					<tr>
						<th>订单备注：</th>
						<td><input class="normal" type="text" name="note" value="" /></td>
					</tr>
					<tr>
						<th>指定送货时间：</th>
						<td>
							<label class='attr'><input type='radio' name='accept_time' checked="checked" value='任意' />任意</label>
							<label class='attr'><input type='radio' name='accept_time' value='周一到周五' />周一到周五</label>
							<label class='attr'><input type='radio' name='accept_time' value='周末' />周末</label>
						</td>
					</tr>
					<?php if(isset($this->orderRow)){?>
					<tr>
						<th>订单折扣或涨价：</th>
						<td>
							<input class="small" type="text" name="discount" value="" />￥
							<label>折扣用" - ",涨价用" + "</label>
						</td>
					</tr>
					<?php }?>
				</tbody>
			</table>
		</div>
	</div>

	<!--收货人信息-->
	<div id="tab-3" class="content_box" style="display:none">
		<div class="content form_content">
			<table class="form_table">
				<col width="150px" />
				<col />

				<tbody>
					<tr>
						<th>所属用户名:</th>
						<td>
							<input type='text' name='username' value='<?php echo $this->username;?>' class='normal' />
							<label>订单所属于的用户，直接填写用户名，订单创建后与该用户绑定在一起，如果为空则为游客订单或者线下订单</label>
						</td>
					</tr>
					<tr>
						<th>收货人姓名:</th>
						<td><input class="normal" type="text" name="accept_name" value="" pattern="required" alt="请填写收货人姓名" /></td>
					</tr>
					<tr>
						<th>收货地区:</th>
						<td>
							<select name="province" child="city,area"></select>
							<select name="city" child="area"></select>
							<select name="area" pattern="required"></select>
						</td>
					</tr>
					<tr>
						<th>收货地址:</th>
						<td><input class="normal" type="text" name="address" pattern="required" value="" alt="请填写收货地址" /></td>
					</tr>
					<tr>
						<th>联系手机:</th><td><input class="normal" type="text" name="mobile" value="" pattern="mobi" alt="手机号码错误"/></td>
					</tr>
					<tr>
						<th>联系电话:</th><td><input class="normal" type="text" name="telphone" value="" empty pattern="phone" alt="请输入正确的固定电话" /></td>
					</tr>
					<tr>
						<th>邮编:</th><td><input class="normal" type="text" name="postcode" value="" empty pattern="zip" alt="请输入正确的邮编" /></td>
					</tr>
					<tr>
						<th>用户附言:</th>
						<td><textarea rows="5" cols="15" name="postscript"></textarea></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="pages_bar"><div class="t_c"><button type="submit" class="submit"><span>保 存</span></button></div></div>
</form>

<script type="text/javascript">
//DOM加载完毕
$(function(){
	//表单回填
	var formInstance = new Form();
	formInstance.init(<?php echo JSON::encode($this->orderRow);?>);

	//动态数据回填
	<?php if($this->orderRow){?>
		var goodsList = <?php echo JSON::encode($this->orderGoods);?>;
		for(var index in goodsList)
		{
			insertGoods(goodsList[index]);
		}

		<?php if($this->takeself){?>
		var takeselfJSON = <?php echo JSON::encode($this->takeself);?>;
		$('#takeself').html(template.render('takeselfTemplate',{"item":takeselfJSON}));
		<?php }?>
	<?php }?>

	//地区联动插件
	var areaInstance = new areaSelect('province');
	areaInstance.init(<?php echo JSON::encode($this->orderRow);?>);
});

/**
 * 筛选商品回调
 * @param goodsList JQ选中的商品列表节点
 */
function searchGoodsCallback(goodsList)
{
	//循环插入DOM节点
	goodsList.each(function()
	{
		var temp = $.parseJSON($(this).attr('data'));
		var insertObject = {"id":temp.goods_id,"name":temp.name,"real_price":temp.sell_price,"product_id":temp.product_id,"spec_array":temp.spec_array ? $.parseJSON(temp.spec_array) : "","goods_no":temp.goods_no};
		insertGoods(insertObject);
	});
}

/**
 * 生成商品信息
 */
function insertGoods(goodsRow)
{
	var goodsRow = goodsRow ? goodsRow : {};
	var goodsTrHtml = template.render('goodsTrTemplate',{item:goodsRow});
	$('#goodsBox').append(goodsTrHtml,goodsRow);
}

//选择当前Tab
function selectTab(curr_tab)
{
	if(curr_tab == 1)
	{
		$('.field').show();
	}
	else
	{
		$('.field').hide();
	}

	$("div[id^=tab-]").hide();
	$("#tab-"+curr_tab).show();

	$("li[id^='button-']").removeClass('selected');
	$("#button-"+curr_tab).addClass('selected');
}

//选择自提点
function selectTakeself(obj)
{
	var type = $(obj).find("option:selected").attr("datatype");
	if(type != 2)
	{
		$('[name="takeself"]').val(0);
		$('#takeself').empty();
		return;
	}

	art.dialog.open("<?php echo IUrl::creatUrl("/block/takeself");?>",{
		title:'选择自提点',
		okVal:'选择',
		ok:function(iframeWin, topWin)
		{
			var takeselfJson = $(iframeWin.document).find('[name="takeselfItem"]:checked').val();

			if(!takeselfJson)
			{
				alert('请选择自提点');
				return false;
			}
			var json = $.parseJSON(takeselfJson);
			$('#takeself').empty();
			$('[name="takeself"]').val(json.id);
			$('#takeself').html(template.render('takeselfTemplate',{"item":json}));
			return true;
		}
	});
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
