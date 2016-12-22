/**
 * Created by yb on 2016/11/29.
 */
//获取url的商品id
var Request = new Object();
Request = GetRequest();
var getId=Request["cat"];
$(".mui-control-item"+getId).addClass("mui-active");
var sub_data={
    tid:getId
};
var pageData = {
    page:1
};
var vm= new Vue({
    el:"#prolistInfo",
    data:{
        placeHolder:'圣诞神秘好礼等你来拿',
        info:{
            category_list:[],
            goods_list1:[],
            goods_list2:[],
            goods_list3:[],
            article_list:[{url:'', len:'', goods_list:[]},
                {url:'', goods_list:[]}],
            brand_list:[]
        },
        searchWord:[],
      

    },
    computed:{
//  	
        hotCat: function(){
            this.info.category_list.map(function(item){
                item.url="/site/category_third/id/"+item.id+"/title/"+item.name;
            })
            return this.info.category_list;
        },
        goods1: function(){
            this.info.goods_list1.map(function(item){
                item.url="/site/products?id="+item.id;
            })
            return this.info.goods_list1;
        },
        goods2: function(){
            this.info.goods_list2.map(function(item){
                item.url="/site/products?id="+item.id;
            })
            return this.info.goods_list2;
        },
        goods3: function(){
            this.info.goods_list3.map(function(item){
                item.url="/site/products?id="+item.id;
            })
            return this.info.goods_list3;
        },
        // 相关品牌
        brand: function(){
            this.info.brand_list.map(function(item){
                item.url="/site/brand_detail?id="+item.id;
            })
            return this.info.brand_list;
        },
        // 随机文章
        article: function(){
            this.info.article_list.map(function(item){
                item.url="/site/article_detail?id="+item.id;
                item.len=item.goods_list.length;
                item.goods_list.map(function(item1){
                    item1.url="/site/products?id="+item1.id;
                })
            })
            return this.info.article_list;
        }
    },
    mounted: function(){
        var self=this;
        getPro_list(self,sub_data);
        hotSearth1(self);
    },
    updated:function(){
        lazyload.init({
            anim:false,
            selectorName:".samLazyImg"
        });
    },
    methods:{
    	toGoodsMore:function(mid){
    		removeSessionItem("goodsinfo");
    		removeSessionItem("goodspage");
    		window.location.href="/site/goods_more?tid="+sub_data.tid+"&mid="+mid;
    	}
    }
})
//页面加载完成后调用的功能
window.onload=function(){
    $("#loading").fadeOut(300);
    var temp=getItem("placeHolder");
    // document.getElementById("search").placeholder=temp+"件商品等你来搜"
    //解决tab选项卡a标签无法跳转的问题
    mui('body').on('tap','.mui-tab-item',function(){
        if(!$(this).hasClass("mui-active")){
            $(this).find(".mui-tab-label").addClass("tabBar_color");
            document.location.href=this.href;
        }
    });
    mui('body').on('tap','.locationA',function(){document.location.href=this.href;});
//		mui('body').on('tap','a',function(){
//			document.location.href=this.href;
//		});

    //点击直达顶部
//		mui("body").on("tap",".fix-toTop",function(){
//			$("html,body").animate({scrollTop:0},0);
//			return false;
//		});
}

//上拉加载
var stop=true;
$(window).bind('scroll', function() {
    if ($(window).scrollTop() + $(window).height() +600 >= $(document).height() && $(window).scrollTop() > 50) {
        if(stop==true){
            stop=false;
            pullupRefresh()
        }
    }
    if($(window).scrollTop()>100){
        $(".fix-toTop").show();
        $(".fix-toTop").css("position","fixed");
    }else{
        $(".fix-toTop").hide();
        $(".fix-toTop").css("position","fixed");
    }
});
function pullupRefresh() {
    mui.ajax('index.php?controller=apic&action=article_list',{
        data:pageData,
        dataType:'json',	// 服务器返回json格式数据
        type:'post',		// HTTP请求类型
        timeout:10000,		// 超时时间设置为10秒；
        success:function(data){
            console.log(data);
            var pageAlbum={};
            pageAlbum.data=data;
           
//				document.getElementById("pullContainer").appendChild(div);
//               mui('#pullrefresh').pullRefresh().endPullupToRefresh(pageData.page==data[0].totalpage);
            if(data[0].totalpage>pageData.page){
                stop = true;
            }else{
                stop = false;
            }
            pageData.page++;
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);

        }
    });
}
function getPro_list(self,sub_data){
    mui.ajax('/apic/pro_list',{
        data:sub_data,
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒
        success:function(data){
            self.info=data.data;
        }
    });
}
function hotSearth1(self){
    mui.ajax('/apic/search_words',{
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            self.searchWord=data;
        }
    });
}
//	获取url传递过来的参数
function GetRequest() {
    var url = location.search; //获取url中"?"符后的字串
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for(var i = 0; i < strs.length; i ++) {
            theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
        }
    }
    return theRequest;
};
    function removeSessionItem(key){
        window.sessionStorage.removeItem(key);
    };