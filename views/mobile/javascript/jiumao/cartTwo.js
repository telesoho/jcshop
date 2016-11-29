/**
 * Created by yb on 2016/11/29.
 */
setItem("cart2url",location.href);
var addr=true;//判断用户是否提供地址
var Request = new Object();
Request = GetRequest();
//页面加载动画的调用
$(window).load(function(){
    $("#loading").fadeOut(300);
    mui('body').on('tap','a',function(){
        document.location.href=this.href;
    });
})
var vm = new Vue({
    el: '#wareListShop',
    data: {
        showMessage:false,
        infoMessage: {
            addressList: [
                {accept_name:'',id:''}
            ],
            delivery:[
                {first_price:'',id:''}
            ],
            payment:[
                {id:''}
            ],
            kicket:{}
        },
        error:'',
        showButton:true,
        img1:'/views/mobile/skin/default/image/jmj/cart/red.png',
        img2:'/views/mobile/skin/default/image/jmj/cart/uncho.png',
        bg:'background:rgba(255,68,160,0.5)',
        bg1:'background:rgba(255,68,160)',
        state:true,
        promo:{
            val:'',
            buttonBg:'opacity:1',
            buttonBg1:'opacity:0.5'
        }

    },
    computed: {
        // 读取和设置
        youbian: function(){
            if(this.infoMessage.addressList[0].zip){
                return ";邮编:"+this.infoMessage.addressList[0].zip;
            }else{
                return "";
            }
        }
    },
    updated:function() {
    },
    methods: {
        getData: function () {
            var self = this;
            getCart2Info(self);
        },
        changeBg: function(){
            this.showButton=this.showButton?false:true;
        },
        formSubmit: function(obj){
            var self=this;
            if(self.showButton){
                checkSubmit(obj)
            }
        },
        promoCho: function(){
            mui("#sheet1").popover('show');
            document.getElementById("promoInput").focus();
        },
        proSub: function(){
            var self=this;
            if(this.promo.val&&this.state){
                this.state=false;
                mui("#sheet1").popover('toggle');
                getCouponInfo(self,this.promo.val);
                mui('body').on('tap','.mui-backdrop',function(){
                    return false;
                })
            }
        }
    }
})
vm.getData();
var turn=true;
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
function getCart2Info(self){
    mui.ajax('/apic/cart2',{
        data:{
            id:Request["id"],
            num:Request["num"],
            type:Request["type"]
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            //服务器返回响应，根据响应结果，分析是否登录成功；
            console.log(data);
            self.infoMessage=data;
            self.showMessage=true;
            if(data.payment==''){
                self.infoMessage.payment[0]={id:1}
            }
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
}
function setItem(key,value){
    var val=JSON.stringify(value)?JSON.stringify(value):[];
    window.localStorage.setItem(key,val);
}
function getItem(key){
    var getter= window.localStorage.getItem(key);
    return JSON.parse(getter);
}
//	获取用户输入激活码的信息
function getCouponInfo(obj,val) {
    var this_val=val;
    var this_=obj;
    mui.ajax('/apic/cart2',{
        data:{
            id:Request["id"],
            num:Request["num"],
            type:Request["type"],
            code:this_val
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            //服务器返回响应，根据响应结果，分析是否登录成功；
            this_.promo.val='';
            this_.state=true;
            if(data.error){
                this_.error=data.error;
                setTimeout(function(){
                    mui("#sheet3").popover('show');
                    setTimeout(function(){
                        mui("#sheet3").popover('hide');
                    },2000)
                },500);

            }else{
                this_.infoMessage=data;
                setTimeout(function(){
                    mui("#sheet2").popover('show');
                    setTimeout(function(){
                        mui("#sheet2").popover('hide');
                    },2000)
                },500);
            }
        }
    });
}
//检查是否有地址
function checkSubmit(obj) {
    console.log(obj);
    if (obj.length!=0) {
        document.getElementById("testForm").submit();
    } else {
        var btnArray = ['取消', '确定'];
        mui.confirm('是否需要新增收货地址', '无收货地址', btnArray, function (e) {
            if (e.index == 1) {
                window.location.href = "/simple/addaddress?from=cart2";
            } else {

            }

        })
    }
}
$('#promoInput').bind('focus',function(){
    $('#deliveryTest').css('display','none');
    $('#testForm').css({'position':'static','display':'none'});
}).bind('blur',function(){
    $('#deliveryTest').css('display','block');
    $('#testForm').css({'position':'fixed','bottom':'0','left':0,'display':'block'});
});
