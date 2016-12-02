/**
 * Created by yb on 2016/11/29.
 */
var pageData = {
    page:1
};
//页面加载完成后调用的功能
window.onload=function(){
    $("#loading").fadeOut(300);
    getIndex();
    var vm = new Vue({
        el: '#indexInfo',
        data: {
            showMessage:false,
            article_category_list:[]
        },
        computed: {
        },
        mounted: function(){
            var self=this;
            getArticle_category_list(self);
        },
        updated:function() {
            // getScrollTop1();
            lazyload.init({
                anim:false,
                selectorName:".samLazyImg"
            });
        },
        methods: {
            toArticle: function(item){
                window.location.href='/site/article_detail?id='+item.id;
            },
            toArticle_list: function(item){
                // 保存分类的名字和id
                setItem("artileName",item.name);
                setItem("articleId",item.id);
                setItem("articlePage",1);
                window.location.href='/site/article_list'
            }
        }
    })
    pullupIndexRefresh();
    hotSearth();
    //解决tab选项卡a标签无法跳转的问题
    mui('body').on('tap','.mui-tab-item',function(){
        var srcimg= $(this).find('img').attr("data-img");
        $(this).find('img').attr("src","/views/mobile/skin/default/image/jmj/icon/"+srcimg);
        document.location.href=this.href;
    });
    mui('body').on('tap','.locationA',function(){document.location.href=this.href;});
    mui('body').on('tap','video',function(){
        var self=this;
        if(this.paused){
            self.setAttribute("controls",'controls');
            $(self).next().addClass('hide');
            self.play();
        }else{
            self.setAttribute("controls",false);
            self.pause();
        }
    });
    mui('body').on('tap','.img-click',function(){
        var self=this;
        var self_Aid=self.parentNode;
//        self_Aid.previousSiblingNode.play();
        $(self_Aid).prev().addClass("hide");
        $(self_Aid).prev().prev()[0].play();
    });
    //点击直达顶部
    mui("body").on("tap",".fix-toTop",function(){
        $("html,body").animate({scrollTop:0},0);
        return false;
    });
}
function getArticle_category_list(self){
    mui.ajax('/apic/article_category_list', {
        dataType: 'json',
        type: 'get',
        timeout: 10000,
        success: function (data) {
            console.log(data);
            self.showMessage=true;
            self.article_category_list=data;
        }
    });
}
//上拉加载
var stop=true;
$(window).bind('scroll', function() {
    if ($(window).scrollTop() + $(window).height() +2000 >= $(document).height() && $(window).scrollTop() > 50) {
        if(stop==true){
            stop=false;
            pullupIndexRefresh()
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
function checkPause(obj){
    var self=obj;
    $(self).next().next().removeClass("hide");
}
function checkPlay(obj){
    var self=obj;
    $(self).next().next().addClass("hide");
}

// 百度统计
var _hmt = _hmt || [];
(function() {
    var hm = document.createElement("script");
    hm.src = "https://hm.baidu.com/hm.js?d2ad3676e7aee829748ccde95d3e4d1a";
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(hm, s);
})();
