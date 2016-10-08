<!--商品条件检索-->
<div class="box m_10">
	<div class="cont">
		<!--品牌展示-->
		<?php $brandList = search_goods::$brandSearch?>
		<?php if($brandList){?>
		<dl class="sorting">
			<dt>品牌：</dt>
			<dd id='brand_dd'>
				<a class="nolimit current" href="<?php echo search_goods::searchUrl('brand','');?>">不限</a>
				<?php foreach($brandList as $key => $item){?>
				<a href="<?php echo search_goods::searchUrl('brand',$item['id']);?>" id='brand_<?php echo isset($item['id'])?$item['id']:"";?>'><?php echo isset($item['name'])?$item['name']:"";?></a>
				<?php }?>
			</dd>
		</dl>
		<?php }?>
		<!--品牌展示-->

		<!--商品属性-->
		<?php foreach(search_goods::$attrSearch as $key => $item){?>
		<dl class="sorting">
			<dt><?php echo isset($item['name'])?$item['name']:"";?>：</dt>
			<dd id='attr_dd_<?php echo isset($item['id'])?$item['id']:"";?>'>
				<a class="nolimit current" href="<?php echo search_goods::searchUrl('attr['.$item["id"].']','');?>">不限</a>
				<?php foreach($item['value'] as $key => $attr){?>
				<a href="<?php echo search_goods::searchUrl('attr['.$item["id"].']',$attr);?>" id="attr_<?php echo isset($item['id'])?$item['id']:"";?>_<?php echo md5($attr);?>"><?php echo isset($attr)?$attr:"";?></a>
				<?php }?>
			</dd>
		</dl>
		<?php }?>
		<!--商品属性-->

		<!--商品价格-->
		<dl class="sorting">
			<dt>价格：</dt>
			<dd id='price_dd'>
				<p class="f_r"><input type="text" class="mini" name="min_price" value="" /> 至 <input type="text" class="mini" name="max_price" value="" /> 元
				<label class="btn_gray_s"><input type="button" onclick="priceLink();" value="确定"></label></p>
				<a class="nolimit current" href="<?php echo search_goods::searchUrl(array('min_price','max_price'),'');?>">不限</a>
				<?php foreach(search_goods::$priceSearch as $key => $item){?>
				<?php $priceZone = explode('-',$item)?>
				<a href="<?php echo search_goods::searchUrl(array('min_price','max_price'),array($priceZone[0],$priceZone[1]));?>" id="<?php echo isset($item)?$item:"";?>"><?php echo isset($item)?$item:"";?></a>
				<?php }?>
			</dd>
		</dl>
		<!--商品价格-->
	</div>
</div>
<!--商品条件检索-->

<!--商品排序展示-->
<div class="display_title">
	<span class="l"></span>
	<span class="r"></span>
	<span class="f_l">排序：</span>
	<ul>
		<?php foreach(search_goods::getOrderType() as $key => $item){?>
		<li id="order_<?php echo isset($key)?$key:"";?>">
			<span class="l"></span><span class="r"></span>
			<a href="<?php echo search_goods::searchUrl(array('order','by'),array($key,search_goods::getOrderBy($key)));?>"><?php echo isset($item)?$item:"";?><span id="by_<?php echo isset($key)?$key:"";?>">&nbsp;</span></a>
		</li>
		<?php }?>
	</ul>
	<span class="f_l">显示方式：</span>
	<a class="show_b" href="<?php echo search_goods::searchUrl('show_type','win');?>" title='橱窗展示' alt='橱窗展示'><span id="winButton"></span></a>
	<a class="show_s" href="<?php echo search_goods::searchUrl('show_type','list');?>" title='列表展示' alt='列表展示'><span id="listButton"></span></a>
</div>
<!--商品排序展示-->

<script type='text/javascript'>
//价格跳转
function priceLink()
{
	var minVal = $('input[name="min_price"]').val();
	var maxVal = $('input[name="max_price"]').val();
	if(isNaN(minVal) || isNaN(maxVal))
	{
		alert('价格填写不正确');
		return '';
	}
	var searchUrl = "<?php echo search_goods::searchUrl(array('min_price','max_price'),array('__min_price__','__max_price__'));?>";
	searchUrl     = searchUrl.replace("__min_price__",minVal).replace("__max_price__",maxVal);
	window.location.href = searchUrl;
}

//筛选条件按钮高亮
jQuery(function(){
	//品牌模块高亮和预填充
	<?php $brand = IFilter::act(IReq::get('brand'),'int');?>
	<?php if($brand){?>
	$('#brand_dd>*').removeClass('current');
	$('#brand_<?php echo isset($brand)?$brand:"";?>').addClass('current');
	<?php }?>

	//属性模块高亮和预填充
	<?php $tempArray = IFilter::act(IReq::get('attr'))?>
	<?php if($tempArray){?>
		<?php $json = JSON::encode(array_map('md5',$tempArray))?>
		var attrArray = <?php echo isset($json)?$json:"";?>;
		for(val in attrArray)
		{
			if(attrArray[val])
			{
				$('#attr_dd_'+val+'>*').removeClass('current');
				$('#attr_'+val+'_'+attrArray[val]).addClass('current');
			}
		}
	<?php }?>

	//价格模块高亮和预填充
	<?php if(IReq::get('min_price') || IReq::get('max_price')){?>
	<?php $priceId = IFilter::act(IReq::get('min_price'))."-".IFilter::act(IReq::get('max_price'))?>
	$('#price_dd>*').removeClass('current');
	$('#<?php echo isset($priceId)?$priceId:"";?>').addClass('current');
	$('input[name="min_price"]').val("<?php echo IFilter::act(IReq::get('min_price'));?>");
	$('input[name="max_price"]').val("<?php echo IFilter::act(IReq::get('max_price'));?>");
	<?php }?>

	//排序字段
	<?php $orderValue = IFilter::act(IReq::get('order'))?>
	<?php if($orderValue){?>
	$('#order_<?php echo isset($orderValue)?$orderValue:"";?>').addClass('current');
	<?php }?>

	//顺序
	<?php $byValue = IReq::get('by')?>
	<?php if($byValue == "desc"){?>
	$('#by_<?php echo isset($orderValue)?$orderValue:"";?>').addClass('desc');
	<?php }else{?>
	$('#by_<?php echo isset($orderValue)?$orderValue:"";?>').addClass('asc');
	<?php }?>

	//显示方式
	<?php $showType = IReq::get('show_type');?>
	<?php if($showType == "win"){?>
	$('[name="goodsItems"]').attr({"class":"clearfix win"});
	$('[name="goodsImage"]').css({"width":200,"height":200});
	$('#winButton').addClass('current');
	<?php }elseif($showType == "list"){?>
	$('[name="goodsItems"]').attr({"class":"clearfix list"});
	$('[name="goodsImage"]').css({"width":115,"height":115});
	$('#listButton').addClass('current');
	<?php }?>
});
</script>