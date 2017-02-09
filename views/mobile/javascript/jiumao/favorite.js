/**
 * Created by yb on 2016/11/29.
 */
var vm = new Vue({
    el: '#favorite',
    data: {
        infoMessage:'',
        state:getSession('favoriteState'),
        head_state: {
            left_red:'/views/mobile/skin/default/image/jmj/favorite/ware_red.png',
            left_black:'/views/mobile/skin/default/image/jmj/favorite/ware_black.png',
            right_red:'/views/mobile/skin/default/image/jmj/favorite/cate_red.png',
            right_black:'/views/mobile/skin/default/image/jmj/favorite/cate_black.png',
            bg1:'color:#3d4225',
            bg2:'color:#ff2f5c',
            img:'width:0.31rem;height:0.31rem;margin-right:0.22rem;'
        }
    },
    computed: {
        changeBgcolor: function(){
            if(this.state==0&&this.infoMessage.goods_data!=''){
                return "background:#f5f5f5";
            }
            else if(this.state==1&&this.infoMessage.article_data!=''){
                return "background:#f5f5f5";
            }else{
                return "background:#f5f5f5";
            }
        },
        // 读取和设置
        new_info_goods: function() {
            var self=this.infoMessage.goods_data;
            if(this.infoMessage.goods_data){
                this.infoMessage.goods_data.map(function(item){
                    item.url="/site/products?id="+item.id;
                    item.cls="item favoriteProduct"+item.id;
                });
            }
            return  this.infoMessage.goods_data;
        },
        new_article_goods: function() {
            var self=this.infoMessage.article_data;
            if(this.infoMessage.goods_data){
                this.infoMessage.article_data.map(function(item){
                    item.url="/site/article_detail?id="+item.id;
                    item.cls="item box favoriteArticle"+item.id;
                });
            }
            return  this.infoMessage.article_data;
        }
    },
    mounted: function(){
    	clear_pull()
    },
    methods: {
        getData: function () {
            var self = this;
            mui.ajax('/apic/favorite_list', {
                dataType: 'json',
                type: 'get',
                timeout: 10000,
                success: function (data) {
                    console.log(data);
                    self.infoMessage = data;
                },
                error: function (xhr, type, errorThrown) {
                    //异常处理；
                    console.log(type);
                }
            });
        },
        changeFavorite: function (sta) {
            this.state = sta;
            pushSession("favoriteState", sta);
        },
        delFavoriteProduct: function (item) {
            var self=this;
            var btnArray = ['取消', '确认'];
            mui.confirm('您确定要删除本商品吗？', '删除商品', btnArray, function(e) {
                if (e.index == 1) {
                    mui.ajax('/simple/favorite_add/_paramKey_/_paramVal_', {
                        data: {
                            goods_id: item.id,
                            random: Math.random()
                        },
                        dataType: 'json',//服务器返回json格式数据
                        type: 'get',//HTTP请求类型
                        timeout: 10000,//超时时间设置为10秒；
                        success: function (data) {
                            var index=self.infoMessage.goods_data.indexOf(item);
                            self.infoMessage.goods_data.splice(index,1);
                        },
                        error: function (xhr, type, errorThrown) {
                            //异常处理；
                            console.log(type);
                        }
                    });
                } else {

                }
            })
        },
        delFavoriteArticle: function (item) {
        	clear_pull();
            var self=this;
            var btnArray = ['取消', '确认'];
            mui.confirm('您确定要删除本专辑吗？', '删除专辑', btnArray, function(e) {
                if (e.index == 1) {
                    mui.ajax('index.php?controller=apic&action=favorite_article_add',{
                        data:{
                            id:item.id
                        },
                        dataType:'json',//服务器返回json格式数据
                        type:'get',//HTTP请求类型
                        timeout:10000,//超时时间设置为10秒；
                        success:function(data){
                            var index=self.infoMessage.article_data.indexOf(item);
                            self.infoMessage.article_data.splice(index,1);
                            //                $(".favoriteArticle"+eid).remove();
                            console.log("删除成功");
                        }
                    });
                } else {

                }
            })

        }
    }
})
vm.getData();
//页面加载动画的调用
$(window).load(function(){
    $("#loading").fadeOut(300);
    //解决tab选项卡a标签无法跳转的问题
    mui('body').on('tap','.mui-tab-item',function(){
        if(!$(this).hasClass("mui-active")){
            $(this).find(".mui-tab-label").addClass("tabBar_color");
            document.location.href=this.href;
        }
    });
    mui('body').on('tap','a',function(){document.location.href=this.href;});
})
function pushSession(key,value){
    if(window.sessionStorage){
        sessionStorage.setItem(key,value);
    }else{
        console.log("无法使用缓存");
        return "";
    }
}
function clear_pull(){
	removeSessionItem("state");
	removeSessionItem("state2");
	removeSessionItem("keys");
	removeSessionItem("self_info");
	removeSessionItem("key2");
	removeSessionItem("s_pull");
}
function getSession(key){
    if(window.sessionStorage){
        var state=sessionStorage.getItem(key)?sessionStorage.getItem(key):0;
        return state;
    }else{
        console.log("无法使用缓存");
        return "";
    }
}
function removeSessionItem(key){
        window.sessionStorage.removeItem(key);
    }