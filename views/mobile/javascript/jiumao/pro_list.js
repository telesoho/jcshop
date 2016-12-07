/**
 * Created by yb on 2016/11/29.
 */
//获取url的商品id
var Request = new Object();
Request = GetRequest();
var getId=Request["cat"];
$(".mui-control-item"+getId).addClass("mui-active");
var pageData = {
    page:1
};
//页面加载完成后调用的功能
window.onload=function(){
    $("#loading").fadeOut(300);
    var temp=getItem("placeHolder");
    document.getElementById("search").placeholder=temp+"件商品等你来搜"
    getIndex();
    hotSearth1();
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
    lazyload.init({
        anim:false,
        selectorName:".samLazyImg"
    });
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
            <!--var html3=template('albumPage',pageAlbum);-->
            <!--var div = document.createElement('div');-->
            <!--div.innerHTML=html3;-->
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
function getIndex(){
    mui.ajax('index.php?controller=apic&action=banner_list',{
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒
        success:function(data){
//				console.log(data);

        },
        error:function(xhr,type,errorThrown){
            //异常处理；
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
}
