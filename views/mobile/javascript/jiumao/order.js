var stop=true;
var vm = new Vue({
    el: '#orderList',
    data: {
        // 头部数据处理
        orderClass:getItem("status"),
        headData:['全部','待支付','待发货','待收货','已完成'],
        divC:'color:#bbb',
        divA:'color:#ff2f5c',
        spanC:'display:none',
        spanA:'display:block',
        showMessage:false,
        // 获取主入口数据
        page:1,
        orderInfo:[],
        //下面的小猫不让显示
        infoState:false,
        //折叠效果
        img1:'/views/mobile/skin/default/image/jmj/order/up.png',
        img2:'/views/mobile/skin/default/image/jmj/order/down.png',
        showImg:false,
        //物流效果
        showContainer:false,
        leftClass:'showWrapper',
        rightClass:'hideWrapper',
        show_ping:false,
        orderStatusText:[]
    },
    computed: {
        // 读取和设置
        orderInfo_new: function() {
            this.orderInfo.map(function(item){
                //下面是折叠的处理逻辑
                  item.firstList=[];
                  item.lastList=[];
                if(item.goodslist.length<3){
                    item.firstList=item.goodslist;
                }else{
                    item.lastNum=item.goodslist.length-2;
                    for(var i=0;i<2;i++){
                        item.firstList.push(item.goodslist[i])
                    }
                    for(var j=2;j<item.goodslist.length;j++){
                        item.lastList.push(item.goodslist[j])
                    }
                }
                //下面是对不同订单状态进行处理
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
                	 var t = parseInt(item.comment_id);
                     console.log(t)
                	if(t > 0){
                		item.button1='去评价';
                    	item.button1Url='/ucenter/comment?id='+t;
                    	vm.show_ping=false;
                	}else{
                    	item.button1='已评价';
                    	vm.show_ping=true;
                   } 
                };
            });
            return  this.orderInfo;
        }
    },
    mounted:function() {
        var self=this;
        // 如果用户从其他入口进入
        self.orderClass=getItem('status')?getItem('status'):0;
        getOrder(self)

    },
    methods: {
        changeStatus: function (num) {
            var self=this;
            //点击重置状态
            self.orderInfo=[];
            self.page=1;
            self.showMessage=false;
            setItem('status',num);
            self.orderClass=num;
            getOrder(self);
        },
        sho: function(){
            this.showImg=!this.showImg
        },
        getDelivery: function (eid) {
            console.log(eid);
            this.showContainer = true;
            Delivery(eid);
        },
        cancelOrder: function (url) {
            var btnArray = ['取消', '确认'];
            mui.confirm('您确定要删除订单吗？', '取消订单', btnArray, function (e) {
                if (e.index == 1) {
                    window.location.href = url
                } else {

                }
            })
        },
        comment_ed:function(url){
        	window.location.href = url;
        },
        shop_shop:function(url,key){
        	if(vm.orderStatusText[key] == "已取消"){
        	}else{
        		window.location.href = url;
        	}
        }
    }
})
$(window).load(function(){
    $("#loading").fadeOut(300);
    pushHistory();
    // 让order页面只能回到个人中心
    window.addEventListener("popstate", function(e) {
            window.location.href='/ucenter/index';
    }, false);
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
        document.getElementById("div_text").innerHTML=responseHtml;
    })
}
function getOrder(self){
    mui.ajax('/apic/order_list',{
        data:{
          class:self.orderClass,
          page:self.page
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            self.showMessage=true;
            data.data.map(function(item){
                self.orderInfo.push(item);
            })
            self.orderInfo.map(function(item){
            	self.orderStatusText.push(item.orderStatusText)
            })
            if(data.data==''){
                self.infoState=true;
                stop=false;
            }else{
                stop=true;
                self.infoState=false;
            }
            self.page++;
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
//引导用户按下返回键
function pushHistory(statusOrder) {
    var state = {
        title: "title",
        url: "#"
    };
    window.history.pushState(state, 'title', "#");
}
// 滚动函数
$(window).bind('scroll', function() {
    if ($(window).scrollTop() + $(window).height() +1000 >= $(document).height() && $(window).scrollTop() > 50) {
        if(stop==true){
            stop=false;
            getOrder(vm);
        }
    }
});