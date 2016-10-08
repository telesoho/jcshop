<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>管理后台</title>
<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/admin.css";?>" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/admin.js";?>"></script>
</head>
<body style="width:450px;min-height:200px;">
<div class="pop_win">
	<form name="balanceForm" callback="submitCallback();" action="#">
		<table class="form_table" style="width:95%">
			<col width="120px" />
			<col />
			<tr>
				<td class="t_r">请选择：</td>
				<td>
					<select name="type" class="auto" pattern='required'>
						<option value="">请选择操作类型</option>
						<option value="recharge">充值账户余额</option>
						<option value="withdraw">账户余额提现</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="t_r">请输入金额：</td>
				<td><input name="balance" class="small" type="text" maxlength="8" pattern='float' alt='必须要填写金额数字' /></td>
			</tr>
		</table>
	</form>
</div>

<script type='text/javascript'>
//提交回调函数
function submitCallback()
{
	return false;
}
</script>
</body>
</html>