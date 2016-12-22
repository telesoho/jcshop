var stop=true;
var vm = new Vue({
    el: '#articleList',
    data: {
        // tab数据
        tab:[
            {
                img_hot:'/views/mobile/skin/default/image/jmj/article/logo1ed.png',
                img:'/views/mobile/skin/default/image/jmj/article/logo1.png',
                name:'狗子推荐'
            },
            {
                img_hot:'/views/mobile/skin/default/image/jmj/article/logo2ed.png',
                img:'/views/mobile/skin/default/image/jmj/article/logo2.png',
                name:'昔君推荐'
            },
            {
                img_hot:'/views/mobile/skin/default/image/jmj/article/logo3ed.png',
                img:'/views/mobile/skin/default/image/jmj/article/logo3.png',
                name:'一哥推荐'
            },
            {
                img_hot:'/views/mobile/skin/default/image/jmj/article/logo4ed.png',
                img:'/views/mobile/skin/default/image/jmj/article/logo4.png',
                name:'奶糖推荐'
            },
            {
                img_hot:'/views/mobile/skin/default/image/jmj/article/logo5ed.png',
                img:'/views/mobile/skin/default/image/jmj/article/logo5.png',
                name:'腿毛推荐'
            }
        ],
        style1:'color:#3d4245',
        style2:'color:#bbb',
        tabState:getSession('tabState')?getSession('tabState'):0,
        tid:getSession('tabState')?getSession('tabState')+1:1,
        // tab数据结束
        page:1,
        articleDetail:[],
        state:getSession('favoriteState'),
        img:{},
        changeState:true,
        img1:"/views/mobile/skin/default/image/jmj/article/grass.png",
        img2:"/views/mobile/skin/default/image/jmj/article/grassed.png"
    },
    computed: {
        new_data: function(){
            this.articleDetail.map(function(item){
                item.eid=item.id;
                item.url="/site/article_detail?id="+item.id;
                item.product_id="product_item"+item.id;
                if(item.visit_num>=1000000){
                    item.visit_num=parseInt(item.visit_num/1000000)+"万";
                    item.favorite_num=parseInt(item.favorite_num/1000000)+"万";
                }
                if(item.visit_num>=100000){
                    item.visit_num=(item.visit_num/100000).toFixed(1)+"万";
                    item.favorite_num=(item.favorite_num/100000).toFixed(1)+"万";
                }
                if(item.visit_num>=10000){
                    item.visit_num=(item.visit_num/10000).toFixed(2)+"万";
                    item.favorite_num=(item.favorite_num/100000).toFixed(1)+"万";
                }
                // item.cls="item box favoriteArticle"+item.id;
                item.list.map(function(itemList){
                    itemList.eid=item.id;
                    itemList.page=item.page;
                    itemList.url="/site/products?id="+itemList.id;
                })
            });
            return this.articleDetail;
        }
    },
    mounted: function(){
        var self=this;
        //如果数据来自首页重新请求
        //如果数据来自详情，从storePage开始加载
        if(getSession("articleGoodData")){
            self.page=getSession("articleGoodPage");
            self.articleDetail=getSession("articleGoodData");
            console.log(self.articleDetail.length);
        }else{
            pullupArticleRefresh(self);
            console.log(self.articleDetail.length);
        }
    },
    updated:function() {
        // getScrollTop1();
        lazyload.init({
            anim:false,
            selectorName:".samLazyImg"
        });
    },
    methods: {
        // 改变状态并且清除每次进来的缓存
        changeTabState: function(num){
            var self=this;
            //主函数 记录用户进来的的位置  点击删除原有的状态,重置函数
          this.tabState=num;
            pushSession('tabState',num);
            removeSessionItem('articleGoodData');
            removeSessionItem('articleGoodPage');
            self.page=1;
            self.articleDetail=[];
            this.tid=num+1;
            pullupArticleRefresh(self)

        },
        store: function(item){
            setItem("product1",item.eid)
            window.location.href=item.url;
        },
        fixToTop: function(){
            $("html,body").animate({scrollTop:0},0);
            return false;
        },
        collection:function(item){
            var self=this;
            if(this.changeState){
                this.changeState=false;
                collection(item,self)
            }
        }
    }
})
//页面加载动画的调用
$(window).load(function(){
    $("#loading").fadeOut(300);

    mui('body').on('tap','.locationA',function(){
        document.location.href=this.href;
    })
})
//上拉加载
$(window).bind('scroll', function() {
    if ($(window).scrollTop() + $(window).height() +2000 >= $(document).height() && $(window).scrollTop() > 50) {
        if(stop==true){
            stop=false;
            pullupArticleRefresh(vm);
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
function pullupArticleRefresh(self){
    mui.ajax('/apic/article_good', {
        data:{
            tid:self.tid,
            page:self.page
        },
        dataType: 'json',
        type: 'get',
        timeout: 10000,
        success: function (data) {
            data.map(function(item){
                self.articleDetail.push(item);
            });
            self.showMessage=true;
           if(data.length==0){
               stop=false;
           }else{
               stop=true;
           }
           // console.log(self.page);
            pushSession("articleGoodData",self.articleDetail);
            self.page++;
            pushSession("articleGoodPage",self.page);
        }
    });
};
// 收藏接口
function collection(item,self){
    mui.ajax('/apic/favorite_article_add',{
        data:{
            id:item.id
        },
        dataType:'json',//服务器返回json格式数据
        type:'get',//HTTP请求类型
        timeout:10000,//超时时间设置为10秒；
        success:function(data){
            console.log(data);
            self.changeState=true;
            if(data.message=="请先登录"){
                alert("请先登录");
                return false;
            }
            if(data.message=="收藏成功"){
                item.is_favorite=1;
                item.favorite_num=parseInt(item.favorite_num)+1;
            }else{
                item.is_favorite=0;
                item.favorite_num=parseInt(item.favorite_num)-1
            }
            pushSession('articleGoodData',self.articleDetail);
        }
    });
}

