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
			<script type="text/javascript" src="<?php echo IUrl::creatUrl("")."plugins/expresswaybill/history/history.js";?>"></script>
<script type="text/javascript" src="<?php echo IUrl::creatUrl("")."plugins/expresswaybill/print_express.js";?>"></script>
<script type="text/javascript" src="<?php echo IUrl::creatUrl("")."plugins/expresswaybill/swfobject.js";?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo IUrl::creatUrl("")."plugins/expresswaybill/history/history.css";?>" />
<style type='text/css'>
	.operaFlash{text-align:left;margin:5px 0px 7px 15px}
	.operaFlash button{margin-right:15px}
	.operaFlash div{margin-top:5px}
	#flashBox{width:900px;height:500px;margin:10px 10px 10px 10px;border:1px solid gray;}
</style>
<script type="text/javascript">
    var swfVersionStr = "10.0.0";
    var xiSwfUrlStr = "<?php echo IUrl::creatUrl("")."plugins/expresswaybill/playerProductInstall.swf";?>";
    var flashvars = {};
    var params = {};
    params.quality = "high";
    params.bgcolor = "#ffffff";
    params.allowscriptaccess = "sameDomain";
    params.allowfullscreen = "true";
    params.wmode = "Opaque";
    var attributes = {};
    attributes.id = "printExpress";
    attributes.name = "printExpress";
    attributes.align = "left";

    swfobject.embedSWF(
        "<?php echo IUrl::creatUrl("")."plugins/expresswaybill/main.swf";?>", "flashContent",
        "100%", "100%",
        swfVersionStr, xiSwfUrlStr,
        flashvars, params, attributes);

	swfobject.createCSS("#flashContent", "display:block;text-align:left;");
</script>

<div class='operaFlash'>
	<div>
		名称：<input type='text' name='express_name' class='small' value='<?php echo isset($this->expressRow['name'])?$this->expressRow['name']:"";?>' />
		宽度：<input type='text' id='app_width' onchange="resize();" class='tiny' name='width' value='<?php echo isset($this->expressRow['width']) ? $this->expressRow['width']:900;?>' />
		高度：<input type='text' id='app_height' onchange="resize();" class='tiny' name='height' value='<?php echo isset($this->expressRow['height']) ? $this->expressRow['height'] : 500;?>' />
		<button type='button' class='btn' onclick="uploadPhoto();"><span>添加背景图</span></button>
		<button type='button' class='btn' onclick="save();"><span>保存</span></button>
		<button type='button' class='btn' onclick="window.location.href='<?php echo IUrl::creatUrl("/order/print_template/tab_index/3");?>';"><span>返回列表</span></button>
	</div>

	<div>
		<select onchange='addPrint(this)' id='addElementCon' class='auto'></select>
		<button type='button' class='btn' onclick="printObj.delTextarea();"><span>移除元素</span></button>

		字号：
		<select id='fontSize_options' onchange="printObj.editStyle({fontSize:this.value});" class='auto'>
			<option value='8'>8px</option>
			<option value='10'>10px</option>
			<option value='12' selected=selected>12px</option>
			<option value='15'>15px</option>
			<option value='20'>20px</option>
		</select>

		文字位置：
		<select id='textAlign_options' onchange="printObj.editStyle({textAlign:this.value});" class='auto'>
			<option value='left' selected=selected>居左</option>
			<option value='center'>居中</option>
			<option value='right'>居右</option>
		</select>

		字体：
		<select id='fontFamily_options' onchange="printObj.editStyle({fontFamily:this.value});" class='auto'>
			<option selected=selected value='宋体'>宋体</option>
			<option value='隶书'>隶书</option>
			<option value='黑体'>黑体</option>
		</select>

		字间距：
		<select id='trackingLeft_options' onchange="printObj.editStyle({trackingLeft:this.value});" class='auto'>
			<option value='-5'>-5</option>
			<option value='-2'>-2</option>
			<option value='0' selected=selected>0</option>
			<option value='2'>2</option>
			<option value='5'>5</option>
			<option value='9'>9</option>
			<option value='12'>12</option>
			<option value='15'>15</option>
			<option value='20'>20</option>
		</select>

		<input type='hidden' id='fontWeight_options' value='normal' />
		<input type='hidden' id='fontStyle_options' value='normal' />
		<label class='attr'><input type='checkbox' id='fontWeight_checkbox' value='bold' onclick="editFont(this,'fontWeight');" />加粗</label> &nbsp;&nbsp;
		<label class='attr'><input type='checkbox' id='fontStyle_checkbox' value='italic' onclick="editFont(this,'fontStyle');" />斜体</label>
	</div>
</div>

<input type='hidden' value='<?php echo IReq::get("id");?>' name='id' />
<!--兼容IE flash居左对齐-->
<div style="text-align:left;overflow:auto;height:500px">
	<div id='flashBox'>
		<div id='flashContent'></div>
	</div>
</div>
<script type='text/javascript'>

	printObj = null;

	//保存快递单
	function save()
	{
		//拼接用户自定义的post数据
		var postData          = {};
		postData.express_name = $('[name="express_name"]').val();
		postData.id           = $('[name="id"]').val();
		postData.width        = $('[name="width"]').val();
		postData.height       = $('[name="height"]').val();

		//调用flex发送接口
		printObj.saveExpress('<?php echo IUrl::creatUrl("/order/expresswaybill_edit_act");?>',postData);
	}

	//初始化
	function init()
	{
		printObj = new printExpress();
		printObj.setModeByJS('edit');

		<?php $configArray = isset($this->expressRow['config']) ? unserialize($this->expressRow['config']) : array();?>

		var elementObj = new Array(<?php echo join(',',$configArray);?>);
		for(elementPro in elementObj)
		{
			printObj.createTextarea(elementObj[elementPro]);
		}

		var backgroundPic = "<?php echo isset($this->expressRow['background'])?$this->expressRow['background']:"";?>";

		if(backgroundPic != '')
		{
			printObj.backgroundPic("<?php echo IUrl::creatUrl("")."";?>"+backgroundPic);
		}

		resize();
	}

	//字体样式更新【针对粗体，斜体】
	function editFont(obj,styleType)
	{
		var setVal = (obj.checked == true) ? obj.value : 'normal';
		$('#'+styleType+'_options').val(setVal);

		switch(styleType)
		{
			case "fontWeight":
				printObj.editStyle({fontWeight:setVal});
			break;

			case "fontStyle":
				printObj.editStyle({fontStyle:setVal});
			break;
		}
	}

	//字体样式还原复选框【针对粗体，斜体】
	function editFontRestore(styleType)
	{
		var is_checked = ($('#'+styleType+'_options').val() == 'normal') ? false : true;
		$('#'+styleType+'_checkbox').prop('checked',is_checked);
	}

	//根据宽高值自动改变大小
	function resize()
	{
		var w = $('#app_width').val();
		var h = $('#app_height').val();

		printObj.flashObj.width  = w;
		printObj.flashObj.height = h;

		$('#flashBox').css('width' ,w);
		$('#flashBox').css('height',h);

		printObj.setAppRange(w,h);
	}

	//添加元素
	function addPrint(obj)
	{
		var styleSheet = {};
		styleSheet.fontStyle    = $('#fontStyle_options').val();
		styleSheet.fontFamily   = $('#fontFamily_options').val();
		styleSheet.fontWeight   = $('#fontWeight_options').val();
		styleSheet.fontSize     = $('#fontSize_options').val();
		styleSheet.textAlign    = $('#textAlign_options').val();
		styleSheet.trackingLeft = $('#trackingLeft_options').val();

		if(obj.value)
		{
			printObj.createTextarea({typeId:obj.value,typeText:obj.options[obj.selectedIndex].text,styleSheet:styleSheet});
		}
	}

	//重置style按钮
	function resetTextareaStyle(styleObj)
	{
		for(pro in styleObj[0])
		{
			$('#'+pro+'_options').val(styleObj[0][pro]);
		}

		//根据值影响checkbox的效果【针对粗体，斜体】
		editFontRestore('fontWeight');
		editFontRestore('fontStyle');
	}

	//保存后回调函数
	function saveCallback(result)
	{
		if($.trim(result) == 'success')
		{
			window.location.href = '<?php echo IUrl::creatUrl("/order/print_template/tab_index/3");?>';
		}
		else
		{
			realAlert(result);
		}
	}

	//上传背景图片
	function uploadPhoto()
	{
		<?php $callback = urlencode(IUrl::creatUrl('/order/expresswaybill_upload'))?>
		art.dialog.open('<?php echo IUrl::creatUrl("/block/photo_upload?callback=".$callback."");?>',
		{
			'id':'uploadPhoto',
			'title':'背景图片上传',
			'top':250,
			'ok':function(iframeWin, topWin)
			{
				iframeWin.document.forms[0].submit();
				return false;
			}
		});
	}

	//背景图片上传回调
	function photoUpload_callback(dataObj)
	{
		if(dataObj.isError == false)
		{
			printObj.backgroundPic("<?php echo IUrl::creatUrl("")."";?>"+dataObj.data);
		}
		else
		{
			alert(dataObj.message);
		}

		art.dialog({id:'uploadPhoto'}).close();
	}

	//页面加载完毕后执行的任务
	jQuery(function(){
		<?php foreach(Expresswaybill::$itemData as $key => $item){?>
		<?php $dataJS[] = "'".$key."':'".$item."'"?>
		<?php }?>
		var elementDataObj = {
			<?php echo join(',',$dataJS);?>
		}
		var selectStr = "<option value=''>添加元素</option>";
		for(e in elementDataObj)
		{
			selectStr += "<option value='"+e+"'>"+elementDataObj[e]+"</option>";
		}
		$('#addElementCon').html(selectStr);
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
