/**
 * Created by yb on 2016/11/14.
 */
// 模板引擎函数
//处理分享数据
template.helper('cal', function(obj){
    if(obj>=1000000){
        return parseInt(obj/1000000)+"万";
    }
    if(obj>=100000){
        return (obj/100000).toFixed(1)+"万";
    }
    if(obj>=10000){
        return (obj/10000).toFixed(2)+"万";
    }
    if(obj<10000){
        return obj;
    }
    return JSON.stringify(obj);
});
template.helper('Base64', function(obj){
    return Base64.encode(obj);
})
template.helper('JSONsplit', function(obj){
    return obj.split(",")
});
template.helper('JSONreduce', function(obj){
    var arr=[];
    for(var m=0;m<obj.length;m++){
        arr.push(obj[m].image);
    }
    return JSON.stringify(arr)
});
template.helper('JSONstringfly', function(obj){
    return JSON.stringify(obj)
});
template.helper('JSONparse', function(obj){
    return JSON.parse(obj);
});
template.helper('JSONarray', function(obj){
    var arr=[];
    for(var i in obj){
        arr.push(obj[i])
    }
    return arr;
});
template.helper('parseInt', function(obj){
   return parseInt(obj);
});
//本地缓存函数
function setItem(key,value){
    var val=JSON.stringify(value)?JSON.stringify(value):[];
    window.localStorage.setItem(key,val);
}
function getItem(key){
    var getter= window.localStorage.getItem(key);
    return JSON.parse(getter);
}
function removeItem(key){
    window.localStorage.removeItem(key);
}
function pushSession(key,value){
    var val=JSON.stringify(value)?JSON.stringify(value):[];
    if(window.sessionStorage){
        sessionStorage.setItem(key,val);
    }else{
        console.log("无法使用缓存");
        return "";
    }
}
function getSession(key){
    if(window.sessionStorage){
        var state=sessionStorage.getItem(key)?sessionStorage.getItem(key):0;
        return JSON.parse(state);
    }else{
        console.log("无法使用缓存");
        return "";
    }
}
function removeSessionItem(key){
    window.sessionStorage.removeItem(key);
}
//功能函数
//搜索处理函数
function handler() {
    event.preventDefault();
}
function getSearth(){
    document.getElementById("search").value="";
    document.getElementById("search").placeholder="";
    document.getElementById("modalid-search").className="show";
    document.getElementById("cancle").className="show cancel";
    document.getElementById("homeHeader").style.cssText="position:absolute;width:100%;z-index:88;top:0;left:0";
    document.body.style.overflow = 'hidden';
    document.body.addEventListener('touchmove', handler, false)
}
function searthCancel(){
    document.getElementById("search").placeholder=getItem("placeHolder")+"件商品等你来搜";
    document.getElementById("modalid-search").className="hide";
    document.getElementById("cancle").className="hide cancel";
    document.body.style.overflow = 'auto';
    document.getElementById("homeHeader").style.position="static";
    document.body.removeEventListener('touchmove', handler, false)
}
function ToSearthPage(item){
    setItem("searth_word",item);
    removeSessionItem("searth_answer");
    window.location.href="/site/search_list";
}
function searthSubmit(){
    var subContent=document.getElementById("search").value;
    setItem("searth_word",subContent);
    removeSessionItem("searth_answer");
    if(subContent==""){
        searthCancel();
    }else{
        window.location.href="/site/search_list";
    }
    return false;
}
//分类页面处理函数
function toPageThird(obj){
    setItem("siteMap",obj)
    window.location.href="/site/category_third";
}
//记录三级页面位置函数
function getPosition(){
    var pid=getItem('product');
    var eid=document.getElementById("product_item"+pid);
    var scroll=eid?eid:"";
    console.log(scroll.offsetTop);
    return scroll.offsetTop;

}
function getScrollTop()
{
    var position=getPosition();
    if(document.documentElement&&document.documentElement.scrollTop)
    {
        document.documentElement.scrollTop=position-9;
    }
    else if(document.body)
    {
        document.body.scrollTop=position-9;
    }
    removeItem('product');
//        console.log(position);
}
function getPosition1(){
    var pid=getSession('product1');
    var eid=document.getElementById("product_item"+pid);
    var scroll=eid?eid:"";
    console.log(scroll.offsetTop);
    return scroll.offsetTop;


}
function getScrollTop1()
{
    var position=getPosition1();
    if(document.documentElement&&document.documentElement.scrollTop)
    {
        document.documentElement.scrollTop=position-9;
    }
    else if(document.body)
    {
        document.body.scrollTop=position-9;
        console.log(document.body.scrollTop);
    }
    removeSessionItem('product1');
//        console.log(position);
}
// api接口请求
//首页限时购接口
//    function getTimePurchase(){
//        var url1='index.php?controller=apic&action=pro_speed_list';
//        mui.ajax(url1,{
//            dataType:'json',//服务器返回json格式数据
//            type:'get',//HTTP请求类型
//            timeout:10000,//超时时间设置为10秒；
//            success:function(data1){
//                //服务器返回响应，根据响应结果，分析是否登录成功；
//                document.getElementById("timer").setAttribute("data_timer",data1.end_time);
//                var html2=template('timeWare',data1);
//                document.getElementById("timePurchase_body").innerHTML = html2;
//                timer('timer');
//            },
//            error:function(xhr,type,errorThrown){
//                //异常处理；
//                console.log(type);
//            }
//        });
//    }
//获得分类三级商品
function getcategory_thirdInfo(id){
    mui.ajax('/apic/category_child',{
        data:{id:id},
        dataType:'json',
        type:'get',
        timeout:10000,
        success:function(data){
            var dat={};
            dat.data=data;
            console.log(data);
            var html = template('category_third_temp',dat);
            document.getElementById("category").innerHTML=html;
            lazyload.init({
                anim:false,
                selectorName:".samLazyImg"
            });
            getScrollTop();
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
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