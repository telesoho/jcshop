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
<div class="headbar">
	<div class="position"><span>工具</span><span>></span><span>文章管理</span><span>></span><span><?php if(isset($this->articleRow['id'])){?>编辑<?php }else{?>添加<?php }?>文章</span></div>
</div>
<div class="content_box">
	<div class="content form_content">
		<form action='<?php echo IUrl::creatUrl("/tools/article_edit_act");?>' method='post' name='article'>
			<input type='hidden' name='id' value="" />
			<table class="form_table">
				<colgroup>
					<col width="150px" />
					<col />
				</colgroup>
				<tr>
					<th>分类：</th>
					<td>
						<select class="auto" name="category_id" pattern="required" alt="请选择分类值">
							<option value=''>选择文章分类</option>
							<?php $query = new IQuery("article_category");$query->order = "path asc";$items = $query->find(); foreach($items as $key => $item){?>
							<option value='<?php echo isset($item['id'])?$item['id']:"";?>'><?php echo str_repeat('&nbsp;&nbsp;&nbsp;',substr_count($item['path'],',')-2);?><?php echo isset($item['name'])?$item['name']:"";?></option>
							<?php }?>
						</select>
						<label>*选择文章所属分类（必填）</label>
					</td>
				</tr>
				<tr>
					<th>标题：</th>
					<td><input type='text' name='title' class='normal' value='' pattern='required' alt='标题不能为空' /></td>
				</tr>
				<tr>
					<th>是否发布：</th>
					<td>
						<label class='attr'><input type='radio' name='visibility' value='1' checked=checked />是</label>
						<label class='attr'><input type='radio' name='visibility' value='0' />否</label>
					</td>
				</tr>
				<tr>
					<th>首页推荐：</th>
					<td>
						<label class='attr'><input type='radio' name='top' value='1' checked=checked />是</label>
						<label class='attr'><input type='radio' name='top' value='0' />否</label>
					</td>
				</tr>
				<tr>
					<th>标题字体：</th>
					<td>
						<label class='attr'><input type='radio' name='style' value='0' checked=checked />正常</label>
						<label class='attr'><input type='radio' name='style' value='1' /><b>粗体</b></label>
						<label class='attr'><input type='radio' name='style' value='2' /><span style="font-style:oblique;">斜体</span></label>
					</td>
				</tr>
				<tr>
					<th>标题颜色：</th>
					<td>
						<div class="color_sel">
							<?php $color = ($this->articleRow['color']===null) ? '#000000' : $this->articleRow['color']?>
							<input type='hidden' name='color' value='' />
							<a class="color_current" style='color:<?php echo isset($color)?$color:"";?>;background-color:<?php echo isset($color)?$color:"";?>;' href='javascript:void(0)' onclick='showColorBox();' id='titleColor'><?php echo isset($color)?$color:"";?></a>
							<div id='colorBox' class="color_box" style='display:none'></div>
						</div>
					</td>
				</tr>
				<tr>
					<th>排序：</th><td><input type='text' class='small' name='sort' value='' /></td>
				</tr>
				<tr>
					<th>关联相关商品：</th>
					<td>
						<table class='border_table' style='width:70%;margin-bottom:10px;'>
							<thead><tr><th>商品名称</th><th>操作</th></tr></thead>
							<tbody id="goodsListBox"></tbody>
						</table>
						<button class='btn' type='button' onclick="searchGoods('<?php echo IUrl::creatUrl("/block/search_goods/type/checkbox");?>',searchGoodsCallback);"><span>选择商品</span></button>
						<label>文章所要关联的商品（可选）</label>
					</td>
				</tr>

				<tr>
					<th valign="top">内容：</th><td><textarea id="content" name='content' style='width:700px;height:300px' pattern='required' alt='内容不能为空'><?php echo htmlspecialchars($this->articleRow['content']);?></textarea></td>
				</tr>
				<tr>
					<th>关键词(SEO)：</th><td><input type='text' class='normal' name='keywords' value='' /></td>
				</tr>
				<tr>
					<th>描述简要(SEO)：</th><td><input type='text' class='normal' name='description' value='' /></td>
				</tr>
				<tr>
					<th></th><td><button class='submit' type='submit'><span>确 定</span></button></td>
				</tr>
			</table>
		</form>
	</div>
</div>

<!--商品模板-->
<script type="text/html" id="goodsItemTemplate">
<tr>
	<td>
		<input type='hidden' name='goods_id[]' value='<%=templateData['goods_id']%>' />
		<img src="<?php echo IUrl::creatUrl("")."<%=templateData['img']%>";?>" style="width:80px;" />
		<%=templateData['name']%>
	</td>
	<td style="text-align:center"><img src="<?php echo $this->getWebSkinPath()."images/admin/icon_del.gif";?>" alt="删除" title="删除" onclick="$(this).parent().parent().remove();" /></td>
</tr>
</script>

<script type='text/javascript'>
jQuery(function(){
	//调色板颜色
	var colorBox = new Array('#000','#930','#330','#030','#036','#930','#000080','#339','#333','#800000','#f60','#808000','#808080','#008080','#00f','#669','#f00','#f90','#9c0','#396','#3cc','#36f','#800080','#999','#f0f','#fc0','#ff0','#0f0','#0ff','#0cf','#936','#c0c0c0','#f9c','#fc9','#ff9','#cfc','#cff','#9cf','#c9f','#fff');
	for(color in colorBox)
	{
		var aHTML = '<a href="javascript:void(0)" onclick="changeColor(this);" style="background-color:'+colorBox[color]+';color:'+colorBox[color]+'">'+colorBox[color]+'</a> ';
		$('#colorBox').html($('#colorBox').html() + aHTML);
	}

	var FromObj = new Form('article');
	FromObj.init(<?php echo JSON::encode($this->articleRow);?>);

	KindEditor.ready(function(K){
		K.create('#content');
	});

	<?php if($this->articleRow){?>
	<?php $goodsList = Api::run("getArticleGoods",array("#article_id#",$this->articleRow['id']))?>
	createGoodsList(<?php echo JSON::encode($goodsList);?>);
	<?php }?>
});

//弹出调色板
function showColorBox()
{
	var layer = document.createElement('div');
	layer.className = "poplayer";
	$(document.body).append(layer);
	var poplay = $('#colorBox');
	$('.poplayer').bind("click",function(){if(poplay.css('display')=='block') poplay.fadeOut();$("div").remove('.poplayer');})
	poplay.fadeIn();
}

//选择颜色
function changeColor(obj)
{
	var color = $(obj).html();
	$('#titleColor').css({color:color,'background-color':color});
	$('input[type=hidden][name="color"]').val(color);
	$('#colorBox').fadeOut();
	$("div").remove('.poplayer');
}

//输入筛选商品的条件
function searchGoodsCallback(goodsList)
{
	var result = [];
	goodsList.each(function()
	{
		var temp = $.parseJSON($(this).attr('data'));
		result.push(temp);
	});
	createGoodsList(result);
}

//创建商品数据
function createGoodsList(goodsList)
{
	for(var i in goodsList)
	{
		var templateHTML = template.render('goodsItemTemplate',{"templateData":goodsList[i]});
		$('#goodsListBox').append(templateHTML);
	}
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
