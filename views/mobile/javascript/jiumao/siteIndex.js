/**
 * Created by yb on 2016/11/29.
 */
var Request = new Object();
	Request = GetRequest();
	var statusOrder=Request["id"];
	tanchaun(statusOrder);
	var times;
	if($("#modalid-search").attr("class") == "false"){
			time_xian();
		}
//时间  倒计时	
var hours = 0;
var minutes = 0;
var seconds = 0;
var _time = 0;
var data = 0;
var vm = new Vue({
    el: '#indexInfo',
    data: {
        // components:{"Nav": Nav},
        showMessage:false,
        page:1,
        speed:[],
        search_top:false,
        search_top_small:true,
        search:[],
        indexInfo:{
            banner: [
                {
                    url:'',
                    // img:''
                }
            ],
            article_category_list:[],
            articleDetail:[]
        },
        placeHolder:getItem('placeHolder'),
        changeState:false,
        img1:"/views/mobile/skin/default/image/jmj/home_redesign/collection.png",
        img2:"/views/mobile/skin/default/image/jmj/home_redesign/collection_ed.png",
        showCat:false,
        //限时购
        seconds:seconds,
        minutes:minutes,
        hours:hours,
        info_time:[],
        shop_time:true,
//      shop_,
        // 榜单
        cid:"",
        did:"",
        //图文专辑
        article_:[],
        //专区
        zhuan_index:[]
    },
    computed: {
        searth_pla: function (){
        	return "圣诞神秘大礼等你来拿";
//          return this.placeHolder+"件商品等你来搜";
        },
        new_data: function(){
            this.indexInfo.articleDetail.map(function(item){
                item.eid=item.id;
                item.url="/site/article_detail?id="+item.id;
                item.product_id="product_item"+item.id;
                item.list.map(function(itemList){
                    itemList.eid=item.id;
                    itemList.page=item.page;
                    itemList.url="/site/products?id="+itemList.id;
                })
            });
            return this.indexInfo.articleDetail;
        },
        shop_info_time:function(){
        	this.info_time.map(function(item){
        		
        	})
        	return this.info_time
        },
        article_list:function(){
        	this.article_.map(function(item){
        		
        	})
        	return this.article_
        }
    },
    mounted: function(){
        var self=this;
        hotSearth(self);
         index_home(self);
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
//          pullupInfoRefresh(self);
           index_home(self);
        }
        time_xian();
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
        $("#search_").click(function(){
        	clearInterval(times);
        	$("#modalid-search").attr("class", "show"); 
        });
        $("#search").focus(function(){
          		$("#nav-slider").hide();
				$("#slider1").hide();
				$("#Timed_to_rob").hide();
				$("#Video_special").hide();
				$("#article_list").hide();
				$("#the_zone").hide();
				$("#footer_index").hide();
				$(".footer").hide();
				$(".recommended ").hide();
				$("#z_special").hide();
				$("#aaaa").hide();
				clearInterval(times);
        })
        $("#search").blur(function(){
			$("#nav-slider").show();
			$("#slider1").show();
			$("#Timed_to_rob").show();
			$("#Video_special").show();
			$("#article_list").show();
			$("#the_zone").show();
			$("#footer_index").show();
			$(".footer").show();
			$(".recommended ").show();
			$("#z_special").show();
			$("#aaaa").show();
		});
		
    },
    methods: {
    	zhuan_shop:function(item){
    		console.log(item);
    		window.location.href = "/site/products?id="+item;
    	},
        toArticle_list: function(item){
            // 保存分类的名字和id
            setItem("artileName",item.name);
            setItem("articleId",item.id);
            setItem("articlePage",1);
            window.location.href='/site/article_list'
        },
//      fixToTop: function(){
//          $("html,body").animate({scrollTop:0},0);
//          return false;
//      },
        collect:function(item,id_this){
            var self=this;
           collection(item,self,id_this);
        },
//      showSearth: function(){
//      	alert(1)
//          getSearth();
//      },
        // 跳转活动页面
        toActive: function(){
            window.location.href="/activity/christmas_grow";
        },
        list_index:function(switch_list){
        	removeSessionItem("list_info");
        	removeSessionItem("list_page");
        	window.location.href = "/redesign/list?id="+switch_list;
        },
        search_tops:function(){
    		this.search_top = true;
    		this.search_top_small = false;	
    		clearInterval(times);
    	},
    	time_shop:function(id){
    		window.location.href="/site/products?id="+id;
    	},
    	tuwen:function(ids){
    		window.location.href="/site/products?id="+ids;
    	},
    	zhuan_pro:function(item){
    		console.log(item.title)
    		if(item.title == "个护"){
    			window.location.href = "/site/pro_list?cat=2"
    		}else if(item.title == "美妆"){
    			window.location.href = "/site/pro_list?cat=1"
    		}else if(item.title == "健康"){
    			window.location.href = "/site/pro_list?cat=4"
    		}
    	},
    	Video_pro:function(){
    		window.location.href = "/site/article_list?id=3"
    	},
    	Brand_pavilion:function(){
    		window.location.href = "/site/sitemap?id=3"
    	},
    	newproduct:function(){
    		removeSessionItem("week_new_page");
			removeSessionItem("week_new_info");
    		window.location.href = "/redesign/week_new"
    	},
    	scene_pavilion:function(){
//  		window.location.href = "/redesign/scenepavilion"
    	},
    	guan:function(){
    		window.location.href = "/site/article_list"
    	},
    	wenzhang_pro:function(id){
    		console.log(id)
    		window.location.href = "/site/article_detail?id="+id;
    	},
    	zheng:function(){
    		window.location.href = "http://mp.weixin.qq.com/s?__biz=MzI3MzQ3NTc1Ng==&mid=100000241&idx=1&sn=f41ecf07dadbc93a1a10d917eb06f2e9&chksm=6b23f0325c5479242b6b80d338dba45d9bf59def5eed366848e9cc3a9d7b4e7429f5e3d9f022&scene=0#rd"
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
    mui('body').on('tap','.locationA',function(){document.location.href=this.href;});
})
 mui('body').on('tap','.locationA',function(){document.location.href=this.href;});
function getBanner(self){
    mui.ajax('/apic/banner_list',{
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒
        success:function(data){
            self.showMessage=true;
            self.placeHolder=data.goods_nums;
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

function index_home(self){
	mui.ajax('/apic/index', {
        dataType: 'json',
        type: 'get',
        timeout: 10000,
        success: function (data) {
        	console.log(data.data);
        	self.zhuan_index = data.data.pro_list;
        	//商品
        	self.info_time = data.data.speed.list;
        	self.article_ = data.data.article_list;
        	//时间
        	var myDate = new Date();
        	var data_time = myDate.getTime();
            
			self.speed = data.data.speed;
			if(self.speed == ""){
				self.shop_time = false;
			}
			_time = self.speed.end_time
			all_time = self.speed.end_time-parseInt(data_time/1000);
//			if(all_time<=0){
//				shop_time = false;
//			}else{
//				shop_time = true;
//			}
			// alert(theTime);
			if(all_time > 60) { 
				minutes = parseInt(all_time/60); 
				seconds = parseInt(all_time%60); 
				// alert(theTime1+"-"+theTime); 
				if(minutes > 60) { 
				hours = parseInt(minutes/60); 
				minutes = parseInt(minutes%60); 
				} 
				if(hours > 24) {
					data = parseInt(hours/24);
					hours = parseInt(hours%24)
				}
				self.hours = hours;
				self.minutes = minutes;
				self.seconds = seconds;
       		}
        },
    });
}
//


//定时器  限时购
function time_xian(){
	times = setInterval(function(){
		if( all_time1<0 ){
			clearInterval(times);
		}
		var myDate = new Date();
		var data_time = myDate.getTime();
		var all_time1 = _time-parseInt(data_time/1000);
		if(all_time1<=0){
			shop_time = false;
		}else{
			shop_time = true;
		}
		if(all_time1 > 60) { 
			minutes = parseInt(all_time1/60); 
			seconds = parseInt(all_time1%60); 
			// alert(theTime1+"-"+theTime); 
			if(minutes > 60) { 
			hours = parseInt(minutes/60); 
			minutes = parseInt(minutes%60); 
			if(hours>24) {
				data = parseInt(hours/24);
				hours = parseInt(hours%24);
			}
			
			if(seconds<10){
				seconds = "0"+seconds;
			}if(minutes<10){
				minutes = "0"+minutes;
			}if(hours<10){
				hours = "0"+hours;
			}
			vm.hours = hours;
			vm.minutes = minutes;
			vm.seconds = seconds;
			} 
			
		}
	},1000)
}

var t = ""
document.addEventListener("touchstart",function(ev){
	t = ev.touches[0].pageY;
})
document.addEventListener("touchmove",function(ev){
	var scroll_s = ev.touches[0].pageY - t;
	if(scroll_s>-20){
		vm.search_top = false;
    	vm.search_top_small = true;
	}
	if(scroll_s <= -20){
		vm.search_top = true;
		vm.search_top_small = false;
	}
})
//document.addEventListener("touchend",function(ev){
//	var scroll_s = ev.changedTouches[0].pageY;
//	
//	if(scroll_s -t>-20){
//		vm.search_top = false;
//  	vm.search_top_small = true;
//	}
//	if(scroll_s-t <= -20){
//		vm.search_top = true;
//		vm.search_top_small = false;
//	}
//})
//上拉加载
var stop=true;
$(window).bind('scroll', function() {
	
    if ($(window).scrollTop() + $(window).height() +1000 >= $(document).height() && $(window).scrollTop() > 50) {
        if(stop==true){
            stop=false;
            vm.showCat=true;
//          pullupInfoRefresh(vm);
        }
    }
//  if($(window).scrollTop()>100){
//      $(".fix-toTop").show();
//      $(".fix-toTop").css("position","fixed");
//  }else{
//      $(".fix-toTop").hide();
//      $(".fix-toTop").css("position","fixed");
//  }
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
function collection(item,self,id_this){
    mui.ajax('/apic/favorite_article_add',{
        data:{
            id:item
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            console.log(data);
			console.log()
			
            if(data.message=="请先登录"){
                alert("请先登录");
                return false;
            }
            if(data.message=="收藏成功"){
                id_this.is_favorite=1;
//              self.changeState=false;
				id_this.favorite_num = parseInt(id_this.favorite_num)+1;
            }else{
                id_this.is_favorite=0;
//              self.changeState=true;
				id_this.favorite_num = parseInt(id_this.favorite_num)-1;
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
    function removeSessionItem(key){
        window.sessionStorage.removeItem(key);
    }
     function removeSessionItem(key){
        window.sessionStorage.removeItem(key);
    }

