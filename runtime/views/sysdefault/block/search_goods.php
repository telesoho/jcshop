<?php $isProducts= IFilter::act(IReq::get('is_products'),'int');?>
<?php $tmpType   = IFilter::act(IReq::get('type'));?>
<?php $seller_id = IFilter::act(IReq::get('seller_id'),'int');?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>管理后台</title>
<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/admin.css";?>" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/admin.js";?>"></script>
<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
</head>
<body style="width:600px;">
<div class="pop_win">
	<form action='<?php echo IUrl::creatUrl("/block/goods_list/type/".$tmpType."");?>' method='post'>
		<input type='hidden' name='is_products' value='<?php echo isset($isProducts)?$isProducts:"";?>' />
		<input type='hidden' name='seller_id' value='<?php echo isset($seller_id)?$seller_id:"";?>' />
		<table class='form_table'>
			<colgroup>
				<col width="150px" />
				<col />
			</colgroup>

			<tbody>
				<tr>
					<td class="t_r">商品名称：</td>
					<td><input type='text' class='normal' name='keywords' /></td>
				</tr>
				<tr>
					<td class="t_r">商品货号：</td>
					<td><input type='text' class='normal' name='goods_no' /></td>
				</tr>
				<tr>
					<td class="t_r">商品分类：</td>
					<td>
						<span id="__categoryBox" style="margin-bottom:8px"></span>
						<button class="btn" type="button" name="_goodsCategoryButton"><span class="add">设置分类</span></button>
						<?php plugin::trigger('goodsCategoryWidget',array("name" => "category_id"))?>
					</td>
				</tr>
				<tr>
					<td class="t_r">商品价格：</td>
					<td>
						<input type='text' class='small' name='min_price' pattern='float' empty /> ～
						<input type='text' class='small' name='max_price' pattern='float' empty />
					</td>
				</tr>
				<tr>
					<td class="t_r">显示数量：</td>
					<td>
						<select class='small' name='show_num'>
							<option value='10' selected='selected'>10</option>
							<option value='20'>20</option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
</body>
</html>
