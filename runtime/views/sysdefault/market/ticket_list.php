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
			<div class="headbar">
	<div class="position"><span>营销</span><span>></span><span>代金券管理</span><span>></span><span>代金券列表</span></div>
	<div class="operating">
		<a href="javascript:;" onclick="event_link('<?php echo IUrl::creatUrl("/market/ticket_edit");?>')"><button class="operating_btn" type="button"><span class="addition">添加代金券</span></button></a>
		<a href="javascript:void(0)" onclick="selectAll('id[]');"><button class="operating_btn" type="button"><span class="sel_all">全选</span></button></a>
		<a href="javascript:void(0)" onclick="document.forms[0].action='<?php echo IUrl::creatUrl("/market/ticket_excel");?>';delModel({msg:'是否要生成excel表格'});"><button class="operating_btn" type="button"><span class="export">生成EXCEL</span></button></a>
	</div>
</div>
<div class="content">
	<form method='post' action='#'>
		<table class="list_table">
			<colgroup>
				<col width="40px" />
				<col width="120px" />
				<col width="70px" />
				<col width="70px" />
				<col width="70px" />
				<col width="180px" />
				<col width="140px" />
			</colgroup>

			<thead>
				<tr>
					<th>选择</th>
					<th>名称</th>
					<th>面值</th>
					<th>数量</th>
					<th>兑换积分</th>
					<th>有效期</th>
					<th>操作</th>
				</tr>
			</thead>

			<tbody>
				<?php $propObj = new IModel('prop')?>
				<?php $query = new IQuery("ticket");$items = $query->find(); foreach($items as $key => $item){?>
				<?php $ticket_num = statistics::getTicketCount($item['id'])?>
				<tr>
					<td><input type='checkbox' name='id[]' value='<?php echo isset($item['id'])?$item['id']:"";?>' /></td>
					<td><?php echo isset($item['name'])?$item['name']:"";?></td>
					<td><?php echo isset($item['value'])?$item['value']:"";?> 元</td>
					<td><?php echo isset($ticket_num)?$ticket_num:"";?> 张</td>
					<td><?php echo ($item['point']==0) ? '不可兑换':$item['point'];?></td>
					<td><?php echo isset($item['start_time'])?$item['start_time']:"";?> ～ <?php echo isset($item['end_time'])?$item['end_time']:"";?></td>
					<td>
						<a href='<?php echo IUrl::creatUrl("/market/ticket_edit/id/".$item['id']."");?>'>
							<img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_edit.gif";?>" alt="修改" title="修改" />
						</a>

						<a href='<?php echo IUrl::creatUrl("/market/ticket_more_list/ticket_id/".$item['id']."");?>'>
							<img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_check.gif";?>" alt="查看详情" title="查看详情" />
						</a>

						<a href='javascript:create_dialog("<?php echo isset($item['id'])?$item['id']:"";?>");'>
							<img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_add.gif";?>" alt="生成实体代金券" title="生成实体优惠券" />
						</a>

						<?php if($ticket_num > 0){?>
						<a href='javascript:void(0)' onclick="delModel({msg:'是否要生成excel表格？',link:'<?php echo IUrl::creatUrl("/market/ticket_excel/id/".$item['id']."");?>'});">
							<img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_down.gif";?>" alt="生成EXCEL" title="生成EXCEL" />
						</a>
						<?php }?>

						<a href='javascript:void(0)' onclick="delModel({link:'<?php echo IUrl::creatUrl("/market/ticket_del/id/".$item['id']."");?>'});">
							<img class="operator" src="<?php echo $this->getWebSkinPath()."images/admin/icon_del.gif";?>" alt="删除" title="删除" />
						</a>
					</td>
				</tr>
				<?php }?>
			</tbody>
		</table>
	</form>
</div>

<script type='text/javascript'>
	//创建优惠券
	function create_dialog(ticket_id)
	{
		art.dialog.prompt('请输入生成线下实体代金券数量：',function(num)
		{
			var num = parseInt(num);
			if(isNaN(num) || num <= 0)
			{
				alert('请填写正确的数量');
				return false;
			}

			var url = '<?php echo IUrl::creatUrl("/market/ticket_create/ticket_id/@ticket_id@/num/@num@");?>';
			    url = url.replace('@ticket_id@',ticket_id).replace('@num@',num);
			window.location.href = url;
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
