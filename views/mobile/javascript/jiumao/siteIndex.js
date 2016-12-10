/**
 * Created by yb on 2016/11/29.
 */
var Request = new Object();
	Request = GetRequest();
	var statusOrder=Request["id"];
	tanchaun(statusOrder);
var vm = new Vue({
    el: '#indexInfo',
    data: {
        showMessage:false,
        page:1,
        search:[],
        indexInfo:{
            banner: [
                {
                    url:'',
                    img:''
                }
            ],
            article_category_list:[],
            articleDetail:[]
        },
        placeHolder:getItem('placeHolder'),
        changeState:true,
        img1:"/views/mobile/skin/default/image/jmj/icon/like.png",
        img2:"/views/mobile/skin/default/image/jmj/icon/like-ed.png"
    },
    computed: {
        searth_pla: function (){
            return this.placeHolder+"件商品等你来搜";
        },
        new_data: function(){
            this.indexInfo.articleDetail.map(function(item){
                item.eid=item.id;
                item.url="/site/article_detail?id="+item.id;
                item.product_id="product_item"+item.id;
                if(item.visit_num>=1000000){
                    item.visit_num=parseInt(item.visit_num/1000000)+"万";
                    item.favorite_num=parseInt(item.favorite_num/1000000)+"万";
                }
                if(item.visit_num>=100000){
                    item.visit_num=(item.visit_num/100000).toFixed(1)+"万";
                    item.favorite_num=(item.favorite_num/100000).toFixed(1)+"万";
                }
                if(item.visit_num>=10000){
                    item.visit_num=(item.visit_num/10000).toFixed(2)+"万";
                    item.favorite_num=(item.favorite_num/100000).toFixed(1)+"万";
                }
                // item.cls="item box favoriteArticle"+item.id;
                item.list.map(function(itemList){
                    itemList.eid=item.id;
                    itemList.page=item.page;
                    itemList.url="/site/products?id="+itemList.id;
                })
            });
            return this.indexInfo.articleDetail;
        }
    },
    mounted: function(){
        var self=this;
        hotSearth(self);
        if(getSession('banner')&&getSession("articleDetail")&&getSession("article_category_list")){
            self.placeHolder=getItem('placeHolder');
            self.showMessage=true;
            self.indexInfo.banner=getSession("banner");
            self.indexInfo.article_category_list=getSession("article_category_list");
            self.indexInfo.articleDetail=getSession("articleDetail");
            self.page=getSession("indexPage");
        }else{
            getBanner(self);
            getArticle_category_list(self);
            pullupInfoRefresh(self);
        }
    },
    updated:function() {
        // 页面加载完成执行的函数;
        lazyload.init({
            anim:false,
            selectorName:".samLazyImg"
        });
        var gallery = mui('#slider1');
        gallery.slider({
            interval:3000//自动轮播周期，若为0则不自动播放，默认为0；
        });
        
    },
    methods: {
        toArticle: function(item){
            window.location.href='/site/article_detail?id='+item.id;
        },
        toArticle_list: function(item){
            // 保存分类的名字和id
            setItem("artileName",item.name);
            setItem("articleId",item.id);
            setItem("articlePage",1);
            window.location.href='/site/article_list'
        },
        store: function(item){
            pushSession("product1",item.eid);
            window.location.href=item.url;
        },
        fixToTop: function(){
            $("html,body").animate({scrollTop:0},0);
            return false;
        },
        collection:function(item){
            var self=this;
            if(this.changeState){this.changeState=false;
                collection(item,self);
            }
        },
        showSearth: function(){
            getSearth();
        }
    }
})

$(document).ready(function(){
//	tanchaun(statusOrder);
    var ua = navigator.userAgent.toLowerCase();
    if (/iphone|ipad|ipod/.test(ua)) {
        getScrollTop1();
    } else if (/android/.test(ua)) {

    }
    //解决tab选项卡a标签无法跳转的问题
    mui('body').on('tap','.mui-tab-item',function(){
        if(!$(this).hasClass("mui-active")){
            $(this).find(".mui-tab-label").addClass("tabBar_color");
            document.location.href=this.href;
        }
    });
    mui('body').on('tap','.locationA',function(){document.location.href=this.href;});
})
function getBanner(self){
    mui.ajax('/apic/banner_list',{
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            self.showMessage=true;
            self.placeHolder=data.goods_nums;
            // console.log(data.banner);
            setItem("placeHolder",data.goods_nums);
            self.indexInfo.banner=data.banner;
            pushSession("banner",self.indexInfo.banner);
        }
    });
}
function getArticle_category_list(self){
    mui.ajax('/apic/article_category_list', {
        dataType: 'json',
        type: 'get',
        timeout: 10000,
        success: function (data) {
            console.log(data);
            self.indexInfo.article_category_list=data;
            pushSession("article_category_list",self.indexInfo.article_category_list);
        },
        error: function(type){
        }
    });
}
function pullupInfoRefresh(self){
    mui.ajax('/apic/article_list', {
        data:{
            page:self.page
        },
        dataType: 'json',
        type: 'get',
        timeout: 10000,
        success: function (data) {
            data.map(function(item){
                item.page=self.page;
                self.indexInfo.articleDetail.push(item);
            });
            console.log(self.indexInfo.articleDetail);
            pushSession("articleDetail",self.indexInfo.articleDetail);
            if(data.length==0){
                stop=false;
            }else{
                stop=true;
            }
            self.page++;
            pushSession("indexPage",self.page);
        }
    });
};
//上拉加载
var stop=true;
$(window).bind('scroll', function() {
    if ($(window).scrollTop() + $(window).height() +1000 >= $(document).height() && $(window).scrollTop() > 50) {
        if(stop==true){
            stop=false;
            pullupInfoRefresh(vm);
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
function checkPause(obj){
    var self=obj;
    $(self).next().next().removeClass("hide");
}
function checkPlay(obj){
    var self=obj;
    $(self).next().next().addClass("hide");
}
// 收藏接口
function collection(item,self){
    mui.ajax('/apic/favorite_article_add',{
        data:{
            id:item.id
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            console.log(data);
            self.changeState=true;
            if(data.message=="请先登录"){
                alert("请先登录");
                return false;
            }
            if(data.message=="收藏成功"){
                item.is_favorite=1;
                item.favorite_num=parseInt(item.favorite_num)+1;
            }else{
                item.is_favorite=0;
                item.favorite_num=parseInt(item.favorite_num)-1
            }
            //处理完还要保存在本地
            pushSession("articleDetail",self.indexInfo.articleDetail)

        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
}
function hotSearth(self){
    mui.ajax('/apic/search_words',{
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            self.search=data;
        }
    });
}
function test(e){
    alert(111);
    alert(e.keyCode);
}
// 百度统计
var _hmt = _hmt || [];
(function() {
    var hm = document.createElement("script");
    hm.src = "https://hm.baidu.com/hm.js?d2ad3676e7aee829748ccde95d3e4d1a";
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(hm, s);
})();
// 					弹	窗	处	理
	function tanchaun(statusOrder){
		if(statusOrder == 1){
//			document.body.style.overflow="hidden";
			$("#bgg").css({
				"display":"block",
				"overflow":"hidden"
			})
			$("#PopupWindow").css({
				"display":"block",
			});
			$(document).on('touchmove',function(event) { event.preventDefault(); }, false);
			$(".fix-nav").css({
				"display":"none"
			})
		}
		$("#guan").click(function(){
            window.history.pushState({}, "九猫大百货", "/site/index");
//			document.body.style.overflow=""
			$("#bgg").css({
				"display":"none",
				"overflow":"hidden"
			})
			$("#PopupWindow").css({
				"display":"none",
			});
			$(document).unbind('touchmove');
			
		})
		//   点击去看看
		$("#sess").click(function(){
			window.location.href = "/site/ticket_list";
		})
	}

////	获取url传递过来的参数
//function GetRequest() {
//  var url = location.search; //获取url中"?"符后的字串
//  var theRequest = new Object();
//  if (url.indexOf("?") != -1) {
//      var str = url.substr(1);
//      strs = str.split("&");
//      for(var i = 0; i < strs.length; i ++) {
//          theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
//      }
//  }
//  return theRequest;
//}