<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>修改收货地址</title>
	<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/areaSelect/areaSelect.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style type="text/css">
		.valid-msg,.invalid-msg,.form-group label{display:none;line-height:0px}
	</style>
</head>

<body style="background-color:#f3f3f3">
<div class="container-fluid" style="margin-top:15px">
	<form action="<?php echo IUrl::creatUrl("/simple/address_add");?>" method="post" name="addressForm" class="form-horizontal">
	<input type="hidden" name="id" />

	<div class="form-group">
		<span class="col-xs-3 text-danger">姓名</span>
		<div class="col-xs-9">
			<input class="form-control" type="text" name="accept_name" pattern='required' alt='姓名不能为空' />
		</div>
	</div>

	<div class="form-group">
		<span class="col-xs-3 text-danger">省份</span>
		<div class="col-xs-9">
			<select class="form-control" name="province" child="city,area"></select>
			<select class="form-control" name="city" child="area"></select>
			<select class="form-control" name="area" pattern="required" alt="请选择收货地区"></select>
		</div>
	</div>

	<div class="form-group">
		<span class="col-xs-3 text-danger">地址</span>
		<div class="col-xs-9">
			<input class="form-control" name='address' type="text" alt='地址不能为空' pattern='required' />
		</div>
	</div>

	<div class="form-group">
		<span class="col-xs-3 text-danger">手机</span>
		<div class="col-xs-9">
			<input class="form-control" name='mobile' type="text" pattern='mobi' alt='格式不正确' />
		</div>
	</div>

	<div class="form-group">
		<span class="col-xs-3">固话</span>
		<div class="col-xs-9">
			<input class="form-control" type="text" pattern='phone' name='telphone' empty alt='格式不正确' />
		</div>
	</div>

	<div class="form-group">
		<span class="col-xs-3">邮编</span>
		<div class="col-xs-9">
			<input class="form-control" name='zip' empty type="text" pattern='zip' alt='格式不正确' />
		</div>
	</div>

	</form>
</div>
</body>

<script type='text/javascript'>
jQuery(function()
{
	var areaInstance = new areaSelect('province');
	areaInstance.init(<?php echo JSON::encode($this->addressRow);?>);

	<?php if($this->addressRow){?>
		var formObj = new Form('addressForm');
		formObj.init(<?php echo JSON::encode($this->addressRow);?>);
	<?php }?>
})
</script>
</html>