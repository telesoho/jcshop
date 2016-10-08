<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $this->_siteConfig->name;?></title>
	<link type="image/x-icon" href="<?php echo IUrl::creatUrl("")."favicon.ico";?>" rel="icon">
	<link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/index.css";?>" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
	<script type='text/javascript' src="<?php echo $this->getWebViewPath()."javascript/common.js";?>"></script>
	<script type='text/javascript' src='<?php echo $this->getWebViewPath()."javascript/site.js";?>'></script>
</head>
<body class="second">
	<div class="brand_list container_2">
		<div class="header">
			<h1 class="logo"><a title="<?php echo $this->_siteConfig->name;?>" style="background:url(<?php if($this->_siteConfig->logo){?><?php echo IUrl::creatUrl("")."".$this->_siteConfig->logo."";?><?php }else{?><?php echo $this->getWebSkinPath()."images/front/logo.gif";?><?php }?>) center no-repeat;background-size:contain;" href="<?php echo IUrl::creatUrl("");?>"><?php echo $this->_siteConfig->name;?></a></h1>
			<ul class="shortcut">
				<li class="first"><a href="<?php echo IUrl::creatUrl("/ucenter/index");?>">我的账户</a></li>
				<li><a href="<?php echo IUrl::creatUrl("/ucenter/order");?>">我的订单</a></li>
				<li><a href="<?php echo IUrl::creatUrl("/simple/seller");?>">申请开店</a></li>
				<li><a href="<?php echo IUrl::creatUrl("/seller/index");?>">商家管理</a></li>
		   		<li class='last'><a href="<?php echo IUrl::creatUrl("/site/help_list");?>">使用帮助</a></li>
			</ul>

			<p class="loginfo">
			<?php if($this->user){?>
			<?php echo isset($this->user['username'])?$this->user['username']:"";?>您好，欢迎您来到<?php echo $this->_siteConfig->name;?>购物！[<a href="<?php echo IUrl::creatUrl("/simple/logout");?>" class="reg">安全退出</a>]
			<?php }else{?>
			[<a href="<?php echo IUrl::creatUrl("/simple/login");?>">登录</a><a class="reg" href="<?php echo IUrl::creatUrl("/simple/reg");?>">免费注册</a>]
			<?php }?>
			</p>
		</div>
	    <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/areaSelect/areaSelect.js"></script>

<div class="wrapper clearfix">
	<div class="wrap_box">
		<div id="fp_form">
			<h3 class="notice">申请加盟商户</h3>
			<p class="tips">加入我们的电商平台，成为我们的供应商，一起共创美好未来</p>
			<div class="box">
				<form action="<?php echo IUrl::creatUrl("/simple/seller_reg");?>" method="post" enctype='multipart/form-data' name="sellerForm">
					<table class="form_table">
						<colgroup>
							<col width="300px" />
							<col />
						</colgroup>

						<tbody>
							<tr>
								<th>登陆用户名：</th>
								<td><input class="normal" name="seller_name" type="text" value="" pattern="required" alt="用户名不能为空" /><label>* 用户名称（必填）</label></td>
							</tr>
							<tr>
								<th>密码：</th><td><input class="normal" name="password" type="password" bind='repassword' pattern="required" /><label>* 登录密码</label></td>
							</tr>
							<tr>
								<th>确认密码：</th><td><input class="normal" name="repassword" type="password" bind='password' pattern="required" /><label>* 重复确认密码</label></td>
							</tr>
							<tr>
								<th>商户真实全称：</th>
								<td><input class="normal" name="true_name" type="text" value="" pattern="required" /></td>
							</tr>
							<tr>
								<th>商户资质材料：</th>
								<td>
									<input type='file' name='paper_img' />
								</td>
							</tr>
							<tr>
								<th>固定电话：</th>
								<td><input type="text" class="normal" name="phone" pattern="phone" /><label>* 固定电话联系方式，如：010-88888888</label></td>
							</tr>
							<tr>
								<th>手机号码：</th>
								<td><input type="text" class="normal" name="mobile" pattern="mobi" /><label>* 移动电话联系方式：如：13000000000</label></td>
							</tr>
							<tr>
								<th>邮箱：</th>
								<td><input type="text" class="normal" name="email" pattern="email" /><label>* 电子邮箱联系方式：如：aircheng@163.com</label></td>
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
								<th>详细地址：</th><td><input class="normal" name="address" type="text" empty value="" /></td>
							</tr>
							<tr>
								<th>企业官网：</th>
								<td><input class="normal" name="home_url" type="text" pattern="url" empty value="" /><label>填写完整的网址，如：http://www.aircheng.com</label></td>
							</tr>
							<tr>
								<td></td>
								<td>
									<input class="submit" type="submit" value="申请加盟" />
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var areaInstance = new areaSelect('province');
areaInstance.init();
</script>
		<?php echo IFilter::stripSlash($this->_siteConfig->site_footer_code);?>
	</div>
</body>
</html>
