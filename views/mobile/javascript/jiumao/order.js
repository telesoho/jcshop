var vm = new Vue({
    el: '#order_Total',
    data: {
        showMessage:false,
        orderInfo:{
            state0:[],
            state1:[],
            state2:[],
            state3:[],
            state4:[]
        },
        contentClass:'mui-slider-item mui-control-content'
    },
    computed: {
        nowStatus: function (){
            return getItem('status')?getItem('status'):0;
        },
        // 读取和设置
        orderInfo_new: function() {
            this.orderInfo.state0.map(function(item){
                item.url="/ucenter/order_detail?id="+item.id;
                if(item.orderStatusVal==2){
                    item.button1='取消订单';
                    item.button1Url='/ucenter/order_status/order_id/'+item.id+'/op/cancel';
                    item.button2='去付款';
                    item.button2Url='/block/doPay/order_id/'+item.id;
                };
                if(item.orderStatusVal==3){
                    item.button1='查看物流';
                    item.button2='确认收货';
                    item.button2Url='/ucenter/order_status/order_id/'+item.id+'/op/confirm';
                };
                if(item.orderStatusVal==6){
                    item.button1='去评价';
                    item.button1Url='/site/error';
                };
            });
            this.orderInfo.state1.map(function(item){
                item.url="/ucenter/order_detail?id="+item.id;
                item.button1='取消订单';
                item.button1Url='/ucenter/order_status/order_id/'+item.id+'/op/cancel';
                item.button2='去付款';
                item.button2Url='/block/doPay/order_id/'+item.id;
            });
            this.orderInfo.state2.map(function(item){
                item.url="/ucenter/order_detail?id="+item.id;
            });
            this.orderInfo.state3.map(function(item){
                item.url="/ucenter/order_detail?id="+item.id;
                item.button1='查看物流';
                item.button2='确认收货';
                item.button2Url='/ucenter/order_status/order_id/'+item.id+'/op/confirm';
            });
            this.orderInfo.state4.map(function(item){
                item.url="/ucenter/order_detail?id="+item.id;
                item.button1='去评价';
                item.button1Url='/site/error';
            });
            return  this.orderInfo;
        }
    },
    mounted:function() {
        $(".mui-control-item").removeClass('mui-active');
        $(".mui-control-item"+this.nowStatus).addClass('mui-active');
        $(".mui-slider-item").removeClass('mui-active');
        $("#order_state"+this.nowStatus).addClass('mui-active');
    },
    methods: {
        getData: function () {
            var self = this;
            getOrder(self);
        },
        getDelivery: function(eid){
            console.log(eid);
            Delivery(eid);
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
    mui('body').on('tap','.mui-control-item',function(){
        this.click();
    });
});
//快递跟踪
function freightLine(doc_id){
    mui('.mui-popover').popover('toggle',document.getElementById("logistics_all"));
    var urlVal = "url:/block/freight/id/"+doc_id;
    // urlVal = urlVal.replace("@id@", doc_id);
//		window.location.href=urlVal;
}
function Delivery(id){
    //获取物流信息
    console.log(id);
    var urlVal = "/block/freight/id/"+id;
    // urlVal = urlVal.replace("@id@", id);
    $.get(urlVal,function(response){
        var responseHtml=response.substring(response.indexOf('<div class="container">'),response.indexOf("</body>"));
        console.log(responseHtml);
        document.getElementById("container").innerHTML=responseHtml;
    })
}
function getOrder(self){
    $.ajax('/apic/order_list',{
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            self.showMessage=true;
            self.orderInfo=data;
            console.log(data);
        },
        error:function(xhr,type,errorThrown){
            //异常处理；
            console.log(type);
        }
    });
}
//本地缓存函数
function setItem(key,value){
    var val=JSON.stringify(value)?JSON.stringify(value):[];
    window.localStorage.setItem(key,val);
}
function getItem(key){
    var getter= window.localStorage.getItem(key);
    return JSON.parse(getter);
}
function saveStatus(num){
    setItem('status',num);
//		$(".mui-slider-item").removeClass('mui-active');
//		$("#order_state"+this.nowStatus).addClass('mui-active');
}