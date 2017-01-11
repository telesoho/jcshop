/**
 * Created by yb on 2017/1/11.
 */
// 注册一个头部导航
var HEADERINFOR={
    class1:"mui-control-item mui-active locationA",
    class2:'mui-control-item locationA',
    nav:[{
        name:'推荐',
        img1:'/views/mobile/skin/default/image/jmj/new_active/index/01.png',
        img2:'/views/mobile/skin/default/image/jmj/new_active/index/00.png',
        url:'/site/index'
    },
        {
            name:'品牌',
            img1:'/views/mobile/skin/default/image/jmj/new_active/index/11.png',
            img2:'/views/mobile/skin/default/image/jmj/new_active/index/10.png',
            url:'/site/brandlist'
        },
        {
            name:'爱逛',
            img1:'/views/mobile/skin/default/image/jmj/new_active/index/21.png',
            img2:'/views/mobile/skin/default/image/jmj/new_active/index/20.png',
            url:'/site/article_list'
        },
        {
            name:'药妆',
            img1:'/views/mobile/skin/default/image/jmj/new_active/index/31.png',
            img2:'/views/mobile/skin/default/image/jmj/new_active/index/30.png',
            url:'/site/pro_list?cat=1'
        },
        {
            name:'各护',
            img1:'/views/mobile/skin/default/image/jmj/new_active/index/41.png',
            img2:'/views/mobile/skin/default/image/jmj/new_active/index/40.png',
            url:'/site/pro_list?cat=2'
        },
        {
            name:'宠物',
            img1:'/views/mobile/skin/default/image/jmj/new_active/index/51.png',
            img2:'/views/mobile/skin/default/image/jmj/new_active/index/50.png',
            url:'/site/pro_list?cat=3'
        },
        {
            name:'健康',
            img1:'/views/mobile/skin/default/image/jmj/new_active/index/61.png',
            img2:'/views/mobile/skin/default/image/jmj/new_active/index/60.png',
            url:'/site/pro_list?cat=4'
        },
        {
            name:'零食',
            img1:'/views/mobile/skin/default/image/jmj/new_active/index/71.png',
            img2:'/views/mobile/skin/default/image/jmj/new_active/index/70.png',
            url:'/site/pro_list?cat=5'
        },
    ],
}
Vue.component('head-nav', {
    data:function(){
        return HEADERINFOR
    },
    props:['state'],
    template: '<section id="nav-slider">\
    <div  class="mui-slider">\
        <div id="Control" class="mui-scroll-wrapper mui-slider-indicator mui-segmented-control mui-segmented-control-inverted">\
            <div class="mui-scroll">\
                <a :class="state==key?class1:class2" :href="item.url" v-for="(item,key) in nav">\
                    <img :src="state==key?item.img1:item.img2" alt=""  />\
                    <span class="text">{{item.name}}</span>\
                    <span class="bg"></span>\
                </a>\
            </div>\
        </div>\
    </div>\
    </section>'
});