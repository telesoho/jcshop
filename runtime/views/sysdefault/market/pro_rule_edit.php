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
	<div class="position"><span>营销</span><span>></span><span>促销活动管理</span><span>></span><span><?php if(isset($this->promotionRow['id'])){?>编辑<?php }else{?>添加<?php }?>促销活动</span></div>
	<ul class='tab' name='conf_menu'>
		<li class='selected'><a href="javascript:select_tab(0);">活动信息</a></li>
		<li><a href="javascript:select_tab(1);">活动规则</a></li>
	</ul>
</div>
<div class="content_box">
	<div class="content form_content">
		<form action="<?php echo IUrl::creatUrl("/market/pro_rule_edit_act");?>" method="post" name='pro_rule_edit' callback="invalid_callback('rule_table');">
			<input type='hidden' name='id' />
			<table class="form_table" name="rule_table">
				<col width="150px" />
				<col />
				<tr>
					<th>活动名称：</th>
					<td><input type='text' class='normal' name='name' pattern='required' alt='请填写活动名称' /><label>* 填写活动名称</label></td>
				</tr>
				<tr>
					<th>活动时间：</th>
					<td>
						<input type='text' name='start_time' class='Wdate' pattern='datetime' onFocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})"  alt='请填写一个日期' /> ～
						<input type='text' name='end_time' class='Wdate' pattern='datetime' onFocus="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})" alt='请填写一个日期' />
						<label>* 此活动的使用时间段</label>
					</td>
				</tr>
				<tr>
					<th>允许参与人群：</th>
					<td>
						<ul class='attr_list clearfix'>
							<li><label class='attr'><input type='checkbox' name='user_group' value='all' onclick='select_all();' />全部</label></li>
							<?php $query = new IQuery("user_group");$items = $query->find(); foreach($items as $key => $item){?>
							<li><label class='attr'><input type='checkbox' <?php if(in_array($item['id'],explode(',',$this->promotionRow['user_group']))){?>checked=checked<?php }?> name='user_group[]' value='<?php echo isset($item['id'])?$item['id']:"";?>' /><?php echo isset($item['group_name'])?$item['group_name']:"";?></label></li>
							<?php }?>
						</ul>
						<label>* 此活动允许参加的用户组</label>
					</td>
				</tr>
				<tr>
					<th>是否开启：</th>
					<td>
						<label class='attr'><input type='radio' name='is_close' value='0' checked=checked />是</label>
						<label class='attr'><input type='radio' name='is_close' value='1' />否</label>
					</td>
				</tr>
				<tr>
					<th>活动介绍：</th>
					<td><textarea name='intro' class='textarea'><?php echo isset($this->promotionRow['intro'])?$this->promotionRow['intro']:"";?></textarea></td>
				</tr>
			</table>
			<div class='clear'></div>
			<table class="form_table" name="rule_table" style='display:none'>
				<col width="150px" />
				<col />
				<tr>
					<th>购物车总金额条件：</th>
					<td><input type='text' name='condition' class='small' pattern='float' alt='请填写一个金额数字' />元 <label>* 当购物车总金额达到所填写的现金额度时规则生效</label></td>
				</tr>
				<tr>
					<th>活动规则：</th>
					<td>
						<select class='auto' name='award_type' pattern='required' alt='请选择一种规则' onchange="change_rule(this.value);">
							<option value=''>请选择</option>
							<option value='1'>当购物车金额满 M 元时,优惠 N 元</option>
							<option value='2'>当购物车金额满 M 元时,优惠 N% </option>
							<option value='3'>当购物车金额满 M 元时,赠送 N 个积分</option>
							<option value='4'>当购物车金额满 M 元时,赠送一张面值 N 元的代金券</option>
							<option value='6'>当购物车金额满 M 元时,免运费</option>
							<option value='7'>当购物车金额满 M 元时,赠送 N 经验值</option>
						</select>
						<label>* 选择一种活动规则</label>
					</td>
				</tr>
				<tr id='rule_box'>
				</tr>
			</table>
			<button class="submit" type='submit'><span>确 定</span></button>
		</form>
	</div>
</div>

<script type='text/javascript'>

	//校验回调
	function invalid_callback(nameVal)
	{
		if($('.invalid-text').length > 0)
		{
			var parentObj = $('.invalid-text:eq(0)').parents('[name="'+nameVal+'"]');
			$('[name="'+nameVal+'"]').index(parentObj);
			select_tab($('[name="'+nameVal+'"]').index(parentObj));
			return false;
		}
		return true;
	}

	//修改规则
	function change_rule(selectVal)
	{
		//判断是否为真正的onchange事件
		if(selectVal != $('#rule_box').data('index'))
		{
			$('#rule_box').data('index',selectVal);
		}
		else
		{
			return;
		}

		var html = '';
		switch(selectVal)
		{
			case "1":
			{
				html = "<th>优惠金额：</th>"
						+"<td><input type='text' name='award_value' class='small' pattern='float' alt='请填写一个金额数字' />元"
						+"<label>* 优惠的金额，从购物车总金额中减掉此部分金额</label></td>";
			}
			break;

			case "2":
			{
				html = "<th>优惠百分比：</th>"
						+"<td><input type='text' name='award_value' class='small' pattern='float' alt='请填写一个数字' />%"
						+"<label>* 优惠的百分比，从购物车总金额中的折扣百分比，如输入10则表示减免10%金额</label></td>";
			}
			break;

			case "3":
			{
				html = "<th>赠送积分：</th>"
						+"<td><input type='text' name='award_value' class='small' pattern='int' alt='请填写一个数字' />"
						+"<label>* 赠送的积分</label></td>";
			}
			break;

			case "4":
			{
				html = "<th>设置代金券：</th>"
						+"<td><select class='auto' name='award_value' pattern='required'><option value=''>请选择</option></select>"
						+"<label>* 选择一个代金券</label></td>";

				//异步获取代金券
				$.getJSON('<?php echo IUrl::creatUrl("/market/getTicketList");?>',{'random':Math.random()},function(content){
					for(pro in content)
					{
						$('select[name="award_value"]').append('<option value="'+content[pro]['id']+'">'+content[pro]['name']+'   面值:'+content[pro]['value']+'元</option>');
					}
					//获取后设置默认的代金券选择
					formObj.setValue('award_value',"<?php echo isset($this->promotionRow['award_value'])?$this->promotionRow['award_value']:"";?>");
				});
			}
			break;

			case "5":
			{
				html = "<th>选择赠品：</th>"
						+"<td>暂无此功能"
						+"<label>* 购物车总额</label></td>";
			}
			break;

			case "7":
			{
				html = "<th>赠送经验：</th>"
						+"<td><input type='text' name='award_value' class='small' pattern='int' alt='请填写一个数字' />"
						+"<label>* 赠送的经验</label></td>";
			}
			break;
		}
		$('#rule_box').html(html);
		formObj.setValue('award_value',"<?php echo isset($this->promotionRow['award_value'])?$this->promotionRow['award_value']:"";?>");
	}

	//选择参与人群
	function select_all()
	{
		var is_checked = $('[name="user_group"]').prop('checked');
		if(is_checked ==  "checked")
		{
			var checkedVal  = true;
			var disabledVal = true;
		}
		else
		{
			var checkedVal  = false;
			var disabledVal = false;
		}

		$('input:checkbox[name="user_group[]"]').each(
			function(i)
			{
				$(this).prop('checked',checkedVal);
				$(this).prop('disabled',disabledVal);
			}
		);
	}

	//表单回填
	var formObj = new Form('pro_rule_edit');
	formObj.init(<?php echo JSON::encode($this->promotionRow);?>);

	change_rule("<?php echo isset($this->promotionRow['award_type'])?$this->promotionRow['award_type']:"";?>");

	//滑动门
	function select_tab(indexVal)
	{
		indexVal = (parseInt(indexVal)>0) ? indexVal : 0;
		$('table[name="rule_table"]').hide();
		$('table[name="rule_table"]:eq('+indexVal+')').show();

		//切换tab样式
		$('ul[name="conf_menu"] li').removeClass('selected');
		$('ul[name="conf_menu"] li:eq('+indexVal+')').addClass('selected');
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
