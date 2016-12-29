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
});
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
            ticket:{}
        },
        error:'',
        showButton:true,
        img1:'/views/mobile/skin/default/image/jmj/cart/red.png',
        img2:'/views/mobile/skin/default/image/jmj/cart/uncho.png',
        bg:'background:rgba(255,68,160,0.5)',
        bg1:'background:rgba(255,68,160,1)',
        state:true,
        promo:{
            val:'',
            buttonBg:'opacity:1',
            buttonBg1:'opacity:0.5'
        },
        // 优惠券页面交互
        showCodeState:false,
        code:{
            style1:'-webkit-transform:rotate(90deg)',
            style2:'-webkit-transform:rotate(0deg)'
        },
        codeMessage:[],
        showCodeMessage:true

    },
    computed: {
        // 读取和设置
        youbian: function(){
            if(this.infoMessage.addressList[0].zip){
                return ";邮编:"+this.infoMessage.addressList[0].zip;
            }else{
                return "";
            }
        },
        sell: function(){
            this.infoMessage.goodsList.map(function(item){
                item.sell_pri=(item.sell_price-item.reduce).toFixed(2)
            })
            return this.infoMessage.goodsList;
        },
        lastPay: function(){
            var tt=(parseFloat(this.infoMessage.sum)+parseFloat(this.infoMessage.delivery_money)).toFixed(2)
            return tt;
        }
    },
    mounted: function(){
        var self=this;
        getTicketInfo1(self,1)
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
            console.log(this.showButton);
        },
        formSubmit: function(obj){
            var self=this;
            if(self.showButton&&self.state){
                self.state=false;
                checkSubmit(obj);
                console.log(self.state);

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
        },
        showCode: function(){
            var self=this;
            this.showCodeState=!this.showCodeState;
            console.log(self);
        },
        //往后台传递优惠码
        subMessCode: function(item){
            var self=this;
            if(self.showCodeMessage){
                self.showCodeMessage=false;
                this.codeMessage.map(function(it){
                    it.cho=false;
                })
                choCouponInfo(self,item);
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
            id:Request["id"]?Request["id"]:'',
            num:Request["num"]?Request["num"]:'',
            type:Request["type"]?Request["type"]:'',
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            //服务器返回响应，根据响应结果，分析是否登录成功；
            console.log(data);
            self.infoMessage=data.data;
            self.showMessage=true;
            if(data.payment==''){
                self.infoMessage.payment[0].id=1;
            }
            document.body.style.overflow = 'auto';
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
//获取用户绑定的code
function getTicketInfo1(self,type){
    mui.ajax('/apic/ticket_list_my',{
        data:{
            type:type
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            console.log(data);
            data.data.map(function(item){
                item.cho=false;
            });
            self.codeMessage=data.data;
        }
    });
}
//	获取用户输入激活码的信息
function getCouponInfo(obj,val) {
    var this_val=val;
    var this_=obj;
    mui.ajax('/apic/cart2',{
        data:{
            id:Request["id"]?Request["id"]:'',
            num:Request["num"]?Request["num"]:'',
            type:Request["type"]?Request["type"]:'',
            code:this_val
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            this_.promo.val='';
            this_.state=true;
            console.log(data);
            if(data.code!=0){
                this_.error=data.msg;
                setTimeout(function(){
                    mui("#sheet3").popover('show');
                    setTimeout(function(){
                        mui("#sheet3").popover('hide');
                    },2000)
                },500);

            }else{
                this_.infoMessage=data.data;
                //这里面让用户输入的选取的优惠券无效
                this_.codeMessage.map(function(item){
                    item.cho=false;
                });
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
//	获取用户选择优惠券的信息
function choCouponInfo(self,item) {
    mui.ajax('/apic/cart2',{
        data:{
            id:Request["id"]?Request["id"]:'',
            num:Request["num"]?Request["num"]:'',
            type:Request["type"]?Request["type"]:'',
            ticket_aid:item.id
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            self.showCodeMessage=true;
            if(data.code==0){
                item.cho=true;
                self.infoMessage=data.data;
                console.log(self.infoMessage.ticket.aid)
                // self.infoMessage.aid=item.id;
            }else{
                self.error=data.msg;
                setTimeout(function(){
                    mui("#sheet3").popover('show');
                    setTimeout(function(){
                        mui("#sheet3").popover('hide');
                    },2000)
                },500);
            }

            console.log(data);
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
