/**
 * Created by yb on 2016/11/30.
 */
var stop=true;
var vm = new Vue({
    el: '#articleList',
    data: {
        page:1,
        storePage:getItem("storePage"),
        articleDetail:[],
        state:getSession('favoriteState'),
        style:{
            firstArticle:'margin-top:0',
            ortherArticle:'margin-top:0.22rem'
        }
    },
    computed: {
        new_data: function(){
            this.articleDetail.map(function(item){
                item.eid=item.id;
                item.url="/site/article_detail?id="+item.id;
                item.product_id="product_item"+item.id;
                if(item.visit_num>=1000000){
                    item.visit_num=parseInt(item.visit_num/1000000)+"万";
                }
                if(item.visit_num>=100000){
                    item.visit_num=(item.visit_num/100000).toFixed(1)+"万";
                }
                if(item.visit_num>=10000){
                    item.visit_num=(item.visit_num/10000).toFixed(2)+"万";
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
        var nowPage=getItem("articlePage")?getItem("articlePage"):1;
        console.log(nowPage);
        //如果数据来自首页重新请求
        //如果数据来自详情，从storePage开始加载
        if(nowPage!=1){
            self.page=nowPage;
            self.articleDetail=getItem("articleData");
            console.log(self.articleDetail.length);
        }else{
            setItem("articleData",[]);
            setItem("articlePage",nowPage);
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
        store: function(item){
            setItem("product1",item.eid)
            window.location.href=item.url;
        },
        fixToTop: function(){
            $("html,body").animate({scrollTop:0},0);
            return false;
        }
    }
})
//页面加载动画的调用
$(window).load(function(){
    $("#loading").fadeOut(300);
    document.title=getItem("artileName");
    mui('body').on('tap','.mui-tab-item',function(){
        var srcimg= $(this).find('img').attr("data-img");
        $(this).find('img').attr("src","/views/mobile/skin/default/image/jmj/icon/"+srcimg);
        document.location.href=this.href;
    });
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
    mui.ajax('/apic/article_lists', {
        data:{
            cid:getItem("articleId"),
            page:self.page
        },
        dataType: 'json',
        type: 'get',
        timeout: 10000,
        success: function (data) {
            data.map(function(item){
                item.page=self.page;
                self.articleDetail.push(item);
            });
            self.showMessage=true;
           if(data.length==0){
               stop=false;
           }else{
               stop=true;
           }
           // console.log(self.page);
            setItem('articleData',self.articleDetail);
            self.page++;
            setItem('articlePage',self.page);
        }
    });
};

