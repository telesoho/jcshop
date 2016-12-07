var oPhpnum = $("#phpnum");
var oLogo = $("#logo");
var oName = $("#name");
var oTop = $("#top");
var oUl = $("#list");
var oStrong1 = $("#strong1");
var oStrong2 = $("#strong2");
var oArticle_top = $("#article_top");
var oArticle_top1 = $("#article_top1");
var oSlider = $(".slider");
var stop=true;
var y = 0
var str="",str1="",str2="",str3="",oLii,page=1;
//oPhpnum.val()res.goods_list.length
	// ajax
	(function(page){
		showajax(page);
	})(page)
	
function showajax(pagenum){
	$.ajax({
		type:"get",
		dataType:"json",
		url:"/apic/brand",
		data:{
			"id":oPhpnum.val(),
			"page": pagenum
		},
		success:function(res){
			var length = res.goods_list.length;
			if(length != 0){
				
				console.log(res.goods_list.length)
				if(res.logo){
					str = "<img src="+res.logo+" />";//logo
				}
				if(res.banner){
					str1 = "<img src="+res.banner+" />"//logo大图;
				}
				for(var i = 0;i<length;i++){
					str2 += '<li><a href="/site/products?id='+res.goods_list[i].id+'">\
								<img src='+ res.goods_list[i].img +'/>\
								<div class="right-list">\
									<p class="p1">'+res.goods_list[i].name+'</p>\
									<p class="p2">'+res.goods_list[i].description+'</p>\
									<span class="span1"><span class="span3">¥</span>'+res.goods_list[i].sell_price+'</span>\
									<span class="span2">日本价：' + res.goods_list[i].jp_price+ '円</span>\
								</div>\
								<t></t>\
							</a></li>'
				}
				
				if(res.article_list){
					for(var j = 0;j<res.article_list.length;j++){
						str3 += '<li>\
									<a href="/site/article_detail/id/'+res.article_list[j].id+'">\
										<img src='+res.article_list[j].image+' />\
									</a>\
								</li>'
					}
				}
				oUl.append(str2);         //商品列表
				oLogo.append(str);       //logo图标
				oName.html(res.name);      //品牌名
				oTop.append(str1);  //头部大图
				oArticle_top.html(res.description)  //介绍收起
				oArticle_top1.html(res.description) //介绍展开
				oStrong1.html(res.goods_sum);//商品款数
				oStrong2.html(res.article_sum);//专辑数量
				oSlider.append(str3);  //相关专辑
	//			oLii = $("#list li")[0].offsetHeight;
					
			}else if(length == 0){
					console.log(res.goods_list.length)
					var oEmpty_product = $(".empty_product");
					oEmpty_product.css("display","block");
				}
			
		}
	});
};
	//懒加载
	$(window).bind('scroll', function() {
		console.log($(window).scrollTop() + $(window).height(),$(document).height())
	    if ($(window).scrollTop() + $(window).height()>= $(document).height()) {
           	if( stop) {
           		
           		page++
            	showajax(page);		
           	}
           			
	    }
	    
	})