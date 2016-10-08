<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>使用代金券</title>
	<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/form/form.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/jquery/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script>
	<script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/autovalidate/validate.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/autovalidate/style.css" />
</head>

<body>
<div class="container-fluid">
	<form action="#" method="post" name="ticketForm" class="form-horizontal">
		<div class="form-group form-group-sm text-danger">
			注：代金券仅能抵扣一个商家的商品金额
		</div>

		<div class="form-group form-group-sm">
			<input type="button" onclick="cancel_ticket();" class="btn btn-danger btn-sm" value="不用代金券" />
		</div>

		<div class="form-group form-group-sm" id="ticket_show_box"></div>
		<hr />
		<div class="form-group form-group-sm text-success">有实体代金券？</div>

		<div class="form-group form-group-sm">
			<label for="ticket_pwd" class="control-label col-xs-4">卡号：</label>
			<div class="col-xs-8">
				<input type='text' class='form-control' id='ticket_num' placeholder="卡号" />
			</div>
		</div>

		<div class="form-group form-group-sm">
			<label for="ticket_pwd" class="control-label col-xs-4">密码：</label>
			<div class="col-xs-8">
				<input type='password' class='form-control' id='ticket_pwd' placeholder="密码" />
			</div>
		</div>

		<div class="form-group form-group-sm">
			<div class="col-xs-offset-4 col-xs-8">
				<input type="button" class="btn btn-success btn-sm" onclick="add_ticket();" value="添加代金券" />
			</div>
		</div>
		<hr />
	</form>
</div>
</body>

<!--代金券模板-->
<script type='text/html' id='ticketTrTemplate'>
<div class="radio">
	<label>
		<input name="ticket_id" onclick="userTicket(<%=item.seller_id%>);" type="radio" value="<%=item.id%>" price="<%=item.value%>" seller="<%=item.seller_id%>" />
		<%=item.name%>
		【<%=item.card_name%>】
		【￥<%=item.value%>】
	</label>
</div>
</script>

<script type='text/javascript'>
jQuery(function()
{
	<?php if($this->prop){?>
	<?php foreach($this->prop as $key => $item){?>
	var propItem = template.render("ticketTrTemplate",{"item":<?php echo JSON::encode($item);?>});
	$('#ticket_show_box').prepend(propItem);
	<?php }?>
	$('#ticket_show_box').show();
	<?php }?>
})

/**
 * @brief 选择代金券
 * @param seller_id int 商家ID
 */
function userTicket(seller_id)
{
	var sellerInfo = "<?php echo $this->sellerInfo;?>";
	var sellerArray= sellerInfo.split(",");

	if(jQuery.inArray(seller_id,sellerArray) !== -1 || seller_id == 0)
	{
		return true;
	}

	alert("该代金券不能用于此商家");
	$('[name="ticket_id"]').prop('checked',false);
	return false;
}

//取消红包
function cancel_ticket()
{
	$('[name="ticket_id"]').prop('checked',false);
}

//添加代金券
function add_ticket()
{
	var ticket_num = $('#ticket_num').val();
	var ticket_pwd = $('#ticket_pwd').val();

	if(ticket_num == '' || ticket_pwd == '')
	{
		alert('请填写卡号和密码');
		return '';
	}

	$.getJSON("<?php echo IUrl::creatUrl("/block/add_download_ticket");?>",{"ticket_num":ticket_num,"ticket_pwd":ticket_pwd},function(content){
		if(content.isError == false)
		{
			if($('[name="ticket_id"][value="'+content.data.id+'"]').length > 0)
			{
				alert('代金券已经存在，不要重复添加');
				return;
			}

			var ticketHtml = template.render('ticketTrTemplate',{item:content.data});
			$('#ticket_show_box').prepend(ticketHtml);

			$('#ticket_show_box').show();
			$('[name="ticket_id"]').prop('checked',true);
			$('[name="ticket_id"]:first').trigger('click');

			$('#ticket_num').val('');
			$('#ticket_pwd').val('');
		}
		else
		{
			alert(content.message);
		}
	});
}
</script>
</html>