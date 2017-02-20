
var Request = new Object();
Request = GetRequest();
var statusOrder=Request["id"];
var vm = new Vue({
    el: '#order_detail',
    data: {
        showMessage:false,
        order_detailInfo:{
            order_info:{},
            order_goods:[],
            is_refunds:true,
        },
        order_class:{
            hot_state:'state_item hot',
            state:'state_item',
            img_little:'/views/mobile/skin/default/image/jmj/order/cat_small.png',
            img_big:'/views/mobile/skin/default/image/jmj/order/catbig.png'
        },
        showContainer:false,
        leftClass:'showWrapper1',
        rightClass:'hideWrapper1',
        tuikuans:false,
        stylealreadyComment:"border:none;color:#fff;background:rgb(102,102,102)" , //用户以已经评价的样式
        showstylealreadyComment:false
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
//              this.order_detailInfo.button3='退款申请';
//              this.order_detailInfo.button2Url='';
            };
            if(this.order_detailInfo.orderStatus==3){
                var t = parseInt(this.order_detailInfo.comment_id);
                this.order_detailInfo.button1='删除订单';
                this.order_detailInfo.button1Url='/site/error';
                if(t>0){
                    this.order_detailInfo.button2='评价';
                    this.order_detailInfo.button2Url='/ucenter/comment?id='+this.order_detailInfo.comment_id;
                }else{
                    this.showstylealreadyComment=true;
                    this.order_detailInfo.button2='已评价';
                    this.order_detailInfo.button2Url="#";
                }
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
            window.location.href = "/ucenter/delivery/order_no/"+eid;
        },
        tuikuan:function(){
        	window.location.href="/ucenter/order_refunds?id="+statusOrder;
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
        	console.log(data.data.orderStatus);
        	if(data.data.is_refunds == "1"){
        		self.tuikuans = true;
        		
            }else{
            	self.tuikuans = false
            }
            self.order_detailInfo=data.data;
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
