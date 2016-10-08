<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/admin.css";?>" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
</head>
<?php $type = IReq::get('type') == 'checkbox' ? 'checkbox' : 'radio'?>
<body style="width:650px;min-height:250px;">
	<div class="pop_win">

		<table class="form_table" style="margin-top: 0px;">
			<colgroup>
				<col width="85px" />
				<col />
			</colgroup>
			<th>所属分类：</th>
			<td id="categoryBox"></td>
		</table>

		<!--分类列表-->
		<script id="categoryListTemplate" type="text/html">
		<ul class="select">
			<%for(var item in templateData){%>
			<%item = templateData[item]%>
			<li onmouseover="showCategory(<%=item['id']%>,<%=level%>);"><label><input name="categoryVal" type="<?php echo isset($type)?$type:"";?>" value="<%=item['id']%>" onchange="selectCategory(this);" <%if( jQuery().inArray(item['id'],checkedCategory) != -1 ){%>checked="checked"<%}%> /><%=item['name']%></label></li>
			<%}%>
		</ul>
		</script>
	</div>
</body>

<script type="text/javascript">
//分类层次数据结构
categoryParentData = art.dialog.data('categoryParentData');

//初始化被选中的分类ID
checkedCategory = art.dialog.data('categoryValue') ? art.dialog.data('categoryValue') : [];

$(function()
{
	//生成顶级分类信息
	var templateHtml = template.render('categoryListTemplate',{'templateData':categoryParentData[0],'level':0,'checkedCategory':checkedCategory});
	$('#categoryBox').append(templateHtml);
})

//显示分类数据信息
function showCategory(categoryId,level)
{
	$('ul.select:gt('+level+')').remove();
	var childCategory = categoryParentData[categoryId];
	if(childCategory)
	{
		var templateHtml = template.render('categoryListTemplate',{'templateData':childCategory,'level':level+1,'checkedCategory':checkedCategory});
		$('#categoryBox').append(templateHtml);
	}
}

//选择分类数据
function selectCategory(_self)
{
	var categoryId = $(_self).val();
	var valueIndex  = jQuery.inArray(categoryId,checkedCategory);

	if($(_self).prop('checked'))
	{
		(valueIndex == -1) ? checkedCategory.push(categoryId) : "";
	}
	else
	{
		(valueIndex == -1) ? "" : checkedCategory.splice(valueIndex,1);
	}
	//更新分类数据值
	<?php if($type == 'checkbox'){?>
	art.dialog.data('categoryValue',checkedCategory);
	<?php }else{?>
	var result = checkedCategory.pop();
	art.dialog.data('categoryValue',Array(result));
	<?php }?>
}
</script>
</html>