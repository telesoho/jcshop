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
	    <div class="wrapper clearfix">
	<div class="wrap_box">
		<h3 class="notice">已注册用户，请登录</h3>
		<p class="tips">欢迎来到我们的网站，如果您已是本站会员请登录</p>
		<div class="box login_box clearfix">
			<form action='<?php echo IUrl::creatUrl("/simple/login_act");?>' method='post'>
				<table width="515" class="form_table f_l">
					<col width="160px" />
					<col />

					<?php if($this->getError()){?>
					<tr><td colspan="2">
						<div class="prompt"><img src="<?php echo $this->getWebSkinPath()."images/front/error_s.gif";?>" width="16" height="15" /><?php echo $this->getError();?></div>
					</td></tr>
					<?php }?>

					<tr><th>用户名/邮箱/手机：</th><td><input class="gray" type="text" name="login_info" value="<?php echo ICookie::get('loginName');?>" pattern='required' alt='填写用户名，邮箱，手机号' /></td></tr>
					<tr><th>密码：</th><td><input class="gray" type="password" name="password" pattern='^\S{6,32}$' alt='填写密码' /></td></tr>
					<tr class="low"><td></td>
						<td>
							<label class="attr"><input class="radio" type="checkbox" name="remember" value='1' />记住登录名</label>
							<label class="attr"><a class="link pwd" href="<?php echo IUrl::creatUrl("/simple/find_password");?>">忘记密码</a></label>
						</td>
					</tr>
					<tr class="low">
						<td></td>
						<td>
							<?php foreach(Api::run('getOauthList') as $key => $item){?>
							<a href="javascript:oauthlogin('<?php echo isset($item['id'])?$item['id']:"";?>');"><img src='<?php echo IUrl::creatUrl("")."".$item['logo']."";?>' /></a>
							<?php }?>
						</td>
					</tr>
					<tr><td></td><td><input class="submit_login" type="submit" value="登录" /></td></tr>
				</table>
			</form>

			<?php if(IReq::get('tourist') === null){?>
			<!--正常登录时-->
			<table width="360px" class="form_table prompt_3 f_l">
				<col width="75px" />
				<col />
				<tr>
					<th></th>
					<td>
						<p class="mt_10"><strong class="f14">您还不是<span class="orange"><?php echo $this->_siteConfig->name;?></span>用户</strong></p>
						<p>现在免费注册成为<?php echo $this->_siteConfig->name;?>商城用户，便能立即享受便宜又放心的购物乐趣。<a class="blue" href="<?php echo IUrl::creatUrl("");?>">网站首页>></a></p>
						<p class="mt_10"><a class="reg_btn" href="<?php echo IUrl::creatUrl("/simple/reg");?>">注册新用户</a></p>
					</td>
				</tr>
			</table>

			<?php }else{?>
			<!--访客购买时跳转-->
			<table id="reg_table" width="360px" class="form_table prompt_3 f_l">
				<col width="75px" />
				<col />
				<tr>
					<th></th>
					<td>
						<p class="mt_10 m_15">
							<strong class="f14">您还不是<span class="orange"><?php echo $this->_siteConfig->name;?></span>用户</strong><br />
							<label>使用游客身份结账或注册</label>
						</p>
						<p><label class="attr"><input class="radio" type='radio' name='next_step' value='acount' />使用游客身份结账</label></p>
						<p><label class="attr"><input class="radio" type='radio' name='next_step' value='reg' checked='checked' />注册新用户</label></p>
						<p class="mt_10 indentation">现在免费注册成为<?php echo $this->_siteConfig->name;?>商城用户，便能立即享受便宜又放心的购物乐趣。<a class="blue" href="<?php echo IUrl::creatUrl("");?>">网站首页>></a></p>
						<p class="mt_40 f_r"><a class="next_step" href="javascript:next_step();">下一步</a></p>
					</td>
				</tr>
			</table>
			<?php }?>

		</div>
	</div>
</div>

<script type='text/javascript'>
//DOM加载结束
$(function(){
	$(".form_table input").focus(function(){$(this).addClass('current');}).blur(function(){$(this).removeClass('current');})
});

//多平台登录
function oauthlogin(oauth_id)
{
	$.getJSON('<?php echo IUrl::creatUrl("/simple/oauth_login");?>',{"id":oauth_id},function(content){
		if(content.isError == false)
		{
			window.location.href = content.url;
		}
		else
		{
			alert(content.message);
		}
	});
}

//下一步操作
function next_step()
{
	var step_val = $('[name="next_step"]:checked').val();
	if(step_val == 'acount')
	{
		<?php $url = plugin::trigger('getCallback')."/tourist/yes"?>
		window.location.href = '<?php echo IUrl::creatUrl("".$url."");?>';
	}
	else if(step_val == 'reg')
	{
		window.location.href = '<?php echo IUrl::creatUrl("/simple/reg");?>';
	}
}
</script>

		<?php echo IFilter::stripSlash($this->_siteConfig->site_footer_code);?>
	</div>
</body>
</html>
