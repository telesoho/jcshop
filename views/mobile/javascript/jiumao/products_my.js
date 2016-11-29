/**
 * Created by yb on 2016/11/29.
 */
//	接收页面传过来的状态值
var Request = new Object();
Request = GetRequest();
var statusOrder=Request["id"];
//记录访问位置的id
setItem("product",statusOrder);
$(window).load(function(){
    $("#loading").fadeOut(300);
    getProductDetail(statusOrder);
    mui('body').on("tap","#joinButton",function(){
        $("#joinCarButton").show();
        $("#buyNowButton").hide();
    })
    mui('body').on("tap","#buyBottom",function(){
        $("#joinCarButton").hide();
        $("#buyNowButton").show();
    })
    mui('body').on("tap",".test11",function(){
        window.location.href=this.href;
    })
    mui('.mui-scroll-wrapper').scroll({
        deceleration: 0.0005
    });
//
});
function getProductDetail(obj){
    var product_detail=obj;
    mui.ajax('index.php?controller=apic&action=products_details',{
        data:{id:product_detail},
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            //轮播图展示页面
            console.log(data);
            setItem("collection",data);
            var html=template('product_carousel',data);
            document.getElementById('carousel').innerHTML=html;
            var carousel  = mui("#carousel");
            carousel.slider({
                interval: 2000
            });
            var html2=template('product_goodsDetail',data);
            document.getElementById('goods_detaile').innerHTML=html2;
            document.getElementById('goodsContent').innerHTML=data.content;
            var html3=template('evaluate',data);
            document.getElementById('evaluateBox').innerHTML=html3;
            var html4=template('joinCarTemplate',data);
            document.getElementById('select_product').innerHTML=html4;
            document.getElementById('collection_state').innerHTML=template('collectionTemplate',data);
            var productInstance = new productClass("{$id}","{$this->user['user_id']}","{$promo}","{$active_id}");
//					getRelateDetail(product_detail);
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
}
function getRelateDetail(obj){
    mui.ajax('index.php?controller=apic&action=products_details_other',{
        data:{id:obj},
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            console.log(data);
            console.log(data.article_data.length);
//					if(data.article_data.length==0){
//						$("#showVedio").hide();
//					}
//					var relateV=template('vedioTemp',data);
//					document.getElementById('relateVedio').innerHTML=relateV;
//					var relateW=template('relateTemp',data);
//					document.getElementById('getDetailR').innerHTML=relateW;
//					var relateB=template('relateBannelTemp',data);
//					document.getElementById('bannel').innerHTML=relateB;
            document.getElementById('sumRelative').innerHTML=data.brand_data.nums;
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
}
function Collection(){
    var data=getItem("collection")
    mui.ajax('/simple/favorite_add/_paramKey_/_paramVal_',{
        data:{
            goods_id:data.id,
            random:Math.random()
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            if(data.message==""){
                console.log("取消收藏")
                $(".collection_img").attr("src","/views/mobile/skin/default/image/jmj/product/remove.png");
                $(".collection_text").html("种草").css("color","#979797");
            }else{
                $(".collection_img").attr("src","/views/mobile/skin/default/image/jmj/product/already.png");
                $(".collection_text").html("已种草").css("color","#ff4aa0");
            }
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
}
function getActionSheet(){
    mui("#sheet").popover('toggle');
    translate.start();
}
//在线翻译
var translate 	= {
    loading 	 		: false,
    start 				: function(){
        var thisContentObj 		= $('#goodsContent');
        var text 				= thisContentObj.text(); //原内容
        translate.getTranslateApi(text);
    },
    getTranslateApi 	: function(text){
        var url 			= '{url:site/translate}';
        if(translate.loading == true) return false;
        translate.loading = true;
        $.post(url,{text:text},function(data,status){
            if(status == 'success'){
                translate.end(data);
            }
            translate.loading = false;
        },'json');
    },
    end 				: function(data){
        $('#sheet').find('.text').text(data);
    }
}

// 商品分享

