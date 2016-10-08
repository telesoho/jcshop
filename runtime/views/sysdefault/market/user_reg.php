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
			<?php 
	$start = IFilter::act(IReq::get('start'));
	$end   = IFilter::act(IReq::get('end'));
	$countData = statistics::userReg($start,$end);
?>

<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/my97date/wdatepicker.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/highcharts/highcharts.js"></script>

<div class="headbar">
	<div class="position"><span>统计</span><span>></span><span>基础数据统计</span><span>></span><span>注册用户统计</span></div>
	<form action='<?php echo IUrl::creatUrl("/market/user_reg");?>' method='get'>
		<input type='hidden' name='controller' value='market' />
		<input type='hidden' name='action' value='user_reg' />
		<div class="operating">
			<div class="search f_l">
				<input type="text" name='start' class="Wdate" id="date_start" pattern='date' value='<?php echo isset($start)?$start:"";?>' alt='' onFocus="WdatePicker()" empty /> —— <input type="text" value="<?php echo isset($end)?$end:"";?>" name='end' pattern='date' class="Wdate" id="date_end" onFocus="WdatePicker()" empty />
				<button class="btn"><span>查 询</span></button>
				<button class="btn" onclick="userReport()"><span>导出报表</span></button>
			</div>
		</div>
    </form>
</div>

<div class="content_box">
	<h3>用户注册统计：</h3>
	<div class='cont'>
		<ul>
			<li>用户注册统计，可以帮助更好的了解你的shop用户的注册情况，为你下一步的营销计划做出更好的判定！</li>
		</ul>
	</div>
</div>

<div class='content_box'>
	<div id="myChart" style="min-height:350px;"></div>
</div>

<script type='text/javascript'>
//图表生成
$(function()
{
	//图标模板
	userHighChart = $('#myChart').highcharts(
	{
		title:
		{
			text:'注册用户'
		},
		xAxis:
		{
			title:
			{
				text:'时间'
			},
			categories:<?php echo JSON::encode(array_keys($countData));?>,
		},
		yAxis:
		{
			title:
			{
				text:'人数(人)'
			},
		},
		series:
		[
			{
				name:'注册人数',
				data:<?php echo JSON::encode(array_values($countData));?>
			}
		],
		tooltip:
		{
			valueSuffix:'人'
		}
	});
});

//生成 Excel并下载
function userReport()
{
	var url   = '<?php echo IUrl::creatUrl("/market/user_report/start/@start@/end/@end@");?>';
	var start = $('#date_start').val();
	var end   = $('#date_end').val();
	url = url.replace("@start@",start).replace("@end@",end);
	window.open(url);
	return false;
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
