
var Request = new Object();
Request = GetRequest();
var statusOrder=Request["id"];
console.log(statusOrder)
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
        },
        changeState:true,
        img1:"/views/mobile/skin/default/image/jmj/home_redesign/collection.png",
        img2:"/views/mobile/skin/default/image/jmj/home_redesign/collection_ed.png",
        cid:"",
    },
    computed: {
        new_data: function(){
            this.articleDetail.map(function(item){
                item.eid=item.id;
                item.url="/site/article_detail?id="+item.id;
                item.product_id="product_item"+item.id;
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
        // if(nowPage!=1){
        //     self.page=nowPage;
        //     self.articleDetail=getItem("articleData");
        //     console.log(self.articleDetail.length);
        // }else{
            setItem("articleData",[]);
            setItem("articlePage",nowPage);
            if(statusOrder){
            	 pullupArticleRefresh(self,statusOrder);
            }else{
            	 pullupArticleRefresh(self);
            }
        // }
    },
    updated:function() {
        // getScrollTop1();
        lazyload.init({
            anim:false,
            selectorName:".samLazyImg"
        });
        if(statusOrder){
        	$(".vedio_show").css({
        		"display":"block"
        	})
        }else{
        	$(".vedio_show").css({
        		"display":"none"
        	})
        }
    },
    methods: {
        store: function(item){
            setItem("product1",item.eid)
            window.location.href=item.url;
        },
        fixToTop: function(){
            $("html,body").animate({scrollTop:0},0);
            return false;
        },
        collect:function(item){
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
           if(statusOrder){
            	 pullupArticleRefresh(vm,statusOrder);
            }else{
            	 pullupArticleRefresh(vm);
            }
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
function pullupArticleRefresh(self,cid){
    mui.ajax('/apic/article_list', {
        data:{
            page:self.page,
            cid:cid ? cid:''
        },
        dataType: 'json',
        type: 'get',
        timeout: 10000,
        success: function (data) {
            data.data.map(function(item){
                self.articleDetail.push(item);
            });
            self.showMessage=true;
           if(data.data.length==0){
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
            setItem('articleData',self.articleDetail);
        }
    });
}

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
