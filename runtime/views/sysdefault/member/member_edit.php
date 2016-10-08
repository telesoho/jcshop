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
			<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/editor/kindeditor-min.js"></script><script type="text/javascript">window.KindEditor.options.uploadJson = "/index.php?controller=pic&action=upload_json";window.KindEditor.options.fileManagerJson = "/index.php?controller=pic&action=file_manager_json";</script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/areaSelect/areaSelect.js"></script>

<div class="headbar">
	<div class="position"><span>会员</span><span>></span><span>会员管理</span><span>></span><span>编辑会员</span></div>
</div>
<div class="content_box">
	<div class="content form_content">
		<form action="<?php echo IUrl::creatUrl("/member/member_save");?>" method="post" name="memberForm">
			<input name="user_id" value="" type="hidden" />
			<table class="form_table">
				<col width="150px" />
				<col />
				<tbody>
					<tr>
						<th>用户名：</th>
						<td><input class="normal" name="username" type="text" value="" pattern="required" alt="用户名不能为空" /><label>* 用户名称（必填）</label></td>
					</tr>
					<tr>
						<th>邮箱：</th>
						<td><input type="text" class="normal" name="email" pattern="email" alt="邮箱错误" empty /><label>邮箱</label></td>
					</tr>
					<tr>
						<th>密码：</th><td><input class="normal" name="password" type="password" /><label>登录密码</label></td>
					</tr>
					<tr>
						<th>确认密码：</th><td><input class="normal" name="repassword" type="password" /><label>确认密码</label></td>
					</tr>
					<tr>
						<th>会员组：</th>
						<td>
							<select class="normal" name="group_id">
								<option value=''>请选择</option>
								<?php $query = new IQuery("user_group");$items = $query->find(); foreach($items as $key => $item){?>
								<option value="<?php echo isset($item['id'])?$item['id']:"";?>"><?php echo isset($item['group_name'])?$item['group_name']:"";?></option>
								<?php }?>
							</select>
						</td>
					</tr>
					<tr>
						<th>姓名：</th>
						<td><input class="normal" name="true_name" type="text" value="" /><label>真实姓名</label></td>
					</tr>
					<tr>
						<th>性别：</th>
						<td>
							<label><input name="sex" type="radio" value="1" checked="checked" />男</label>
							<label><input name="sex" type="radio" value="2" />女</label>
						</td>
					</tr>
					<tr>
						<th>电话：</th><td><input class="normal" name="telephone" type="text" value="" empty pattern="phone" alt="格式不正确 格式：（地区号-）用户号（-分机号）如010-66668888-123" /><label>格式：（地区号-）用户号（-分机号）如010-66668888-123</label></td>
					</tr>
					<tr>
						<th>手机：</th><td><input class="normal" name="mobile" type="text" value="" empty pattern="mobi" alt="格式不正确" /><label>手机号码</label></td>
					</tr>
					<tr>
						<th>地区：</th>
						<td>
							<select name="province" child="city,area"></select>
							<select name="city" child="area"></select>
							<select name="area"></select>
						</td>
					</tr>
					<tr>
						<th>地址：</th><td><input class="normal" name="contact_addr" type="text" value="" /><label>联系地址</label></td>
					</tr>
					<tr>
						<th>邮编：</th><td><input class="normal" name="zip" type="text" value="" empty pattern="zip" alt="格式不正确（6位数字）" /><label>邮政编码</label></td>
					</tr>
					<tr>
						<th>QQ：</th><td><input class="normal" name="qq" type="text" value="" empty pattern="qq" alt="格式不正确" /><label>QQ号码</label></td>
					</tr>
					<tr>
						<th>经验值：</th><td><input class="normal" name="exp" type="text" value="" /></td>
					</tr>
					<tr>
						<th>积分：</th><td><input class="normal" name="point" type="text" value="" /></td>
					</tr>
					<tr>
						<th>状态：</th>
						<td>
							<select name="status">
								<option value="1">正常</option>
								<option value="2">删除</option>
								<option value="3">锁定</option>
							</select>
						</td>
					</tr>
					<tr>
						<td></td><td><button class="submit" type="submit"><span>确 定</span></button></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<script language="javascript">
//DOM加载完毕
$(function(){
	var areaInstance = new areaSelect('province');

	//修改模式
	<?php if($this->userInfo){?>
		var formObj = new Form('memberForm');
		formObj.init(<?php echo JSON::encode($this->userInfo);?>);

		<?php if($this->userInfo['area']){?>
		<?php $area = explode(',',trim($this->userInfo['area'],','))?>
		areaInstance.init({"province":"<?php echo isset($area[0])?$area[0]:"";?>","city":"<?php echo isset($area[1])?$area[1]:"";?>","area":"<?php echo isset($area[2])?$area[2]:"";?>"});
		<?php }?>
	<?php }else{?>
		areaInstance.init();
	<?php }?>
});
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
