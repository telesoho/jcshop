/**
 * Created by yb on 2016/11/29.
 */
var Request = new Object();
Request = GetRequest();
var statusOrder=Request["id"];
var vm = new Vue({
    el: '#order_detail',
    data: {
        showMessage:false,
        order_detailInfo:{
            order_info:{},
            order_goods:[]
        },
        order_class:{
            hot_state:'state_item hot',
            state:'state_item',
            img_little:'/views/mobile/skin/default/image/jmj/order/cat_small.png',
            img_big:'/views/mobile/skin/default/image/jmj/order/catbig.png'
        },
        showContainer:false,
        leftClass:'showWrapper1',
        rightClass:'hideWrapper1'
    },
    computed: {
        goods_new: function (){
            this.order_detailInfo.order_goods.map(function(item){
                item.url="/site/products?id="+item.goods_id;
            });
            return this.order_detailInfo.order_goods;
        },
        buttonurl: function (){
            if(this.order_detailInfo.orderStatus==0){
                this.order_detailInfo.button1='取消订单';
                this.order_detailInfo.button1Url='/ucenter/order_status/order_id/'+this.order_detailInfo.order_info.id+'/op/cancel';
                this.order_detailInfo.button2='去付款';
                this.order_detailInfo.button2Url='/block/doPay/order_id/'+this.order_detailInfo.order_info.id;
            };
            if(this.order_detailInfo.orderStatus==1){
                this.order_detailInfo.button1=false;
            };
            if(this.order_detailInfo.orderStatus==2){
                this.order_detailInfo.button1='查看物流';
                this.order_detailInfo.button2='确认收货';
                this.order_detailInfo.button2Url='/ucenter/order_status/order_id/'+this.order_detailInfo.order_info.id+'/op/confirm';
            };
            if(this.order_detailInfo.orderStatus==3){
                this.order_detailInfo.button1='删除订单';
                this.order_detailInfo.button1Url='/site/error';
                this.order_detailInfo.button2='评价';
                this.order_detailInfo.button2Url='/site/error';
            }
            return this.order_detailInfo;
        }
    },
    mounted:function() {
    },
    methods: {
        getData: function () {
            var self = this;
            getOrderDetail(self);
        },
        getDelivery: function(eid){
            Delivery(eid);
            this.showContainer=true;
            mui("#logistics").popover('show');
            $('.mui-backdrop').hide();
        }
    }
})
vm.getData();





$(window).load(function(){
    $("#loading").fadeOut(300);
    mui('body').on('tap','.locationA',function(){
        document.location.href=this.href;
    });
});
function getOrderDetail(self){
    mui.ajax('/apic/order_detail',{
        data:{id:statusOrder},
        dataType:'json',	// 服务器返回json格式数据
        type:'get',		// HTTP请求类型
        timeout:10000,		// 超时时间设置为10秒；
        success:function(data){
            self.order_detailInfo=data;
            self.showMessage=true;
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
function Delivery(id){
    //获取物流信息
    var urlVal = "/block/freight/id/"+id;
    // urlVal = urlVal.replace("@id@", id);
    $.get(urlVal,function(response){
        var responseHtml=response.substring(response.indexOf('<div class="container">'),response.indexOf("</body>"));
        console.log(responseHtml);
        document.getElementById("div_text").innerHTML=responseHtml;
    })
}