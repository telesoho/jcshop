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
			<div class="headbar clearfix">
	<div class="position">订单<span>></span><span>订单管理</span><span>></span><span>打印模板</span></div>
	<ul class="tab" name="menu">
		<li name="selec" class="selected" id="selec1"><a href="javascript:selectTab('1');" hidefocus="true">购物清单模板</a></li>
		<li name="selec" id="selec2"><a href="javascript:selectTab('2');" hidefocus="true">配货单模板</a></li>
		<li name="selec" id="selec3"><a href="javascript:selectTab('3');" hidefocus="true">快递单模板</a></li>
	</ul>
</div>

<div class="content_box">
	<div class="content">

		<form name="ModelForm" action="<?php echo IUrl::creatUrl("/order/print_template_update");?>" method="post">
			<table name="form_table" id="tab-1" class="form_table">
				<tr>
					<td><textarea class="big" name="con_shop" id="con_shop" style='width:95%'><?php echo isset($ifile_shop)?$ifile_shop:"";?></textarea></td>
				</tr>
				<tr class='td_c'>
					<td>
						<button class='submit' type="submit"  onclick="return checkForm();"><span>保 存</span></button>
						<button class='submit' type="reset"><span>恢复默认</span></button>
					</td>
				</tr>
			</table>

			<table name="form_table" id="tab-2" style="display:none" class="form_table">
				<tr>
					<td><textarea class="big" name="con_pick" id="con_pick" style='width:95%'><?php echo isset($ifile_pick)?$ifile_pick:"";?></textarea></td>
				</tr>
				<tr class='td_c'>
					<td>
						<button class='submit' type="submit" onclick="return checkForm();"><span>保 存</span></button>
						<button class='submit' type="reset"><span>恢复默认</span></button>
					</td>
				</tr>
			</table>
		</form>

		<table name="form_table" id="tab-3" style="display:none;" class="list_table">
			<thead>
			<tr>
				<th style='height:25px'>名称</th>
				<th>状态</th>
				<th>操作</th>
			</tr>
			</thead>

			<tbody>
				<?php $query = new IQuery("expresswaybill");$items = $query->find(); foreach($items as $key => $item){?>
				<tr>
					<td><?php echo isset($item['name'])?$item['name']:"";?></td>
					<td><?php echo $item['is_close']==0 ? '开启' : '关闭';?></td>
					<td>
						<a href='<?php echo IUrl::creatUrl("/order/expresswaybill_edit/id/".$item['id']."");?>'><img class="operator" alt="编辑" src="<?php echo $this->getWebSkinPath()."images/admin/icon_edit.gif";?>"></a>
						<a href='javascript:void(0);' onclick="delModel({link:'<?php echo IUrl::creatUrl("/order/expresswaybill_del/id/".$item['id']."");?>'})"><img class="operator" alt="删除" src="<?php echo $this->getWebSkinPath()."images/admin/icon_del.gif";?>"></a>
					</td>
				</tr>
				<?php }?>
			</tbody>

			<tfoot>
				<tr class='td_c'>
					<td colspan='3'>
						<button type='button' class='submit' onclick='window.location.href="<?php echo IUrl::creatUrl("/order/expresswaybill_edit");?>";'><span>添加模板</span></button>
					</td>
				</tr>
			</tfoot>
		</table>

	</div>
</div>

<script type='text/javascript'>
	//选择当前Tab
	function selectTab(curr_tab)
	{
		$("table[name=form_table]").hide();
		$("#tab-"+curr_tab).show();
		$("li[name='selec']").removeClass("selected");
		$("#selec"+curr_tab).addClass("selected");
	}

	//模型属性表单验证
	function checkForm()
	{
		var con1=$("#con_shop").val();
		var con2=$("#con_pick").val();

		if($.trim(con1)==""){
			selectTab('1');
			alert("购物清单模板不能为空!");
			return false;
		}
		if($.trim(con2)==""){
			selectTab('2');
			alert("配货单模板不能为空!");
			return false;
		}
		return true;
	}

	//渲染视图直接切换tab滑动门
	<?php if(IReq::get('tab_index')){?>
		jQuery(function(){
			selectTab("<?php echo IReq::get('tab_index');?>");
		});
	<?php }?>

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
