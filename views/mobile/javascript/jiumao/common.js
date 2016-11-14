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
//搜索处理函数
function getSearth(){
    document.getElementById("searth").value="";
    document.getElementById("modalid-searth").className="show";
    document.body.style.overflow = 'hidden';
    document.getElementById("homeHeader").style.cssText="position:fixed;width:100%;z-index:88;";
}
function searthCancel(){
    document.getElementById("modalid-searth").className="hide";
    document.body.style.overflow = 'auto';
    document.getElementById("homeHeader").style.position="static";
}
function ToSearthPage(item){
    setItem("word",item);
    window.location.href="/site/search_list";
}
function searthSubmit(){
    var subContent=document.getElementById("searth").value;
    setItem("word",subContent);
    if(subContent==""){
        searthCancel();
    }else{
        window.location.href="/site/search_list";
    }
}
// api接口请求
// 首页请求
function getIndex(){
    mui.ajax('index.php?controller=apic&action=banner_list',{
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            console.log(data);
            setItem("placeHolder",data.goods_nums);
            $(".mui-placeholder span").eq(1).html(data.goods_nums+"件商品等你来搜");
            var html = template('C_slider',data);
            document.getElementById("slider1").innerHTML = html;
            var gallery = mui('#slider1');
            gallery.slider({
                interval:3000//自动轮播周期，若为0则不自动播放，默认为0；
            });
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
        }
    });
}
// 下拉刷新
function pullupIndexRefresh() {
    mui.ajax('index.php?controller=apic&action=article_list',{
        data:pageData,
        dataType:'json',	// 服务器返回json格式数据
        type:'post',		// HTTP请求类型
        timeout:10000,		// 超时时间设置为10秒；
        success:function(data){
            console.log(data);
            var pageAlbum={};
            pageAlbum.data=data;
            var html3=template('albumPage',pageAlbum);
            var div = document.createElement('div');
            div.innerHTML=html3;
            document.getElementById("pullContainer").appendChild(div);
//               mui('#pullrefresh').pullRefresh().endPullupToRefresh(pageData.page==data[0].totalpage);
            stop = true;

            pageData.page++;
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
}
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
// 首页分享接口
function collection(id){
    mui.ajax('index.php?controller=apic&action=favorite_article_add',{
        data:{
            id:id
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            console.log(data);
            var num=$(".favorite"+id);
            var share=$(".shareContent"+id);
            if(data.message=="收藏成功"){
//                        num.innerHTML=parseInt(num.innerHTML)+1;
                num.html(parseInt(num.html())+1);
                share.attr("src","/views/mobile/skin/default/image/jmj/icon/like-ed.png");
            }else{
//                   num.innerHTML=parseInt(num.innerHTML)-1;
                num.html(parseInt(num.html())-1);
                share.attr("src","/views/mobile/skin/default/image/jmj/icon/like.png");
            }
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
}