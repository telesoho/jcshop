/**
 * Created by yb on 2016/12/26.
 */
// 专辑列表组件 用到的地方有三个  首页专辑  article-list  pro_list(字段不对暂时未列入) 以及今日好货推荐
function active(classname){
	var date ="?v=" + Date.parse(new Date())
	var head = document.getElementsByTagName("head")[0];
	var art_file = document.createElement("link");
	art_file.setAttribute("rel","stylesheet");
	art_file.setAttribute("type","text/css");
	art_file.setAttribute("href",classname+date);
	head.appendChild(art_file);
	console.log(art_file)
}
active("/views/mobile/componentsCss/article_list_component.css")
Vue.component('article-list', {
    template:'<div>\
    <div class="title" v-if="state==0">\
        <span class="logo" >\
        <img :src="item.icon" alt="" class="img-hot" />\
        </span>\
        <span class="name">\
        {{item.category_name}}\
        </span>\
        <span class="like mui-pull-right" style="margin-left:4.4rem">\
        <img :src="item.is_favorite==0?img1:img2"  alt="" class="img-like" @click="collection(item)" />\
        </span>\
        <span class="num mui-pull-right favorite" >\
            {{item.favorite_num}}\
        </span>\
    </div>\
    <a href="#" @click="store(item)" :id="item.product_id">\
    <div class="content">\
    <div class="img flexbox" style="background:#fff">\
    <img :dataimg="item.image"   style="width:7.1rem;height:3.6rem;border-radius:0.1rem" src="/views/mobile/skin/default/image/jmj/icon/big_lazy.png"  class="samLazyImg"  />\
    <img class="vedio_show" src="/views/mobile/skin/default/image/jmj/home_redesign/play_index.png"/>\
    </div>\
</a>\
<div class="single"></div>\
    <div class="mark">\
    <div class="product">\
    <div class="slider">\
    <div class="item" v-for="itemList in item.list" >\
    <a href="#" @click="store(itemList)" >\
    <div class="img"><img :dataimg="itemList.img" alt="" style="width:1.8rem;height:1.8rem;" src="/views/mobile/skin/default/image/jmj/product/ware_lazy.png" alt="" class="samLazyImg" /></div>\
    <div class="name hidewrap">{{itemList.name}}</div>\
<div class="sellprice" style="font-size:0.26rem;text-align: center">\
    <span style="font-size:0.2rem;color:#ff4aa0;margin-right:-0.08rem;">￥</span> {{itemList.sell_price}} </div>\
</a>\
<div class="singleline"></div>\
    </div>\
    </div>\
    </div>\
    </div></div>',
    data:function(){
        return {

        }
    },
    props:['item','state','img1','img2'],
    methods:{
        store:function(item){
            pushSession("product1",item.eid);
            window.location.href=item.url;
        },
        collection: function(item){
            // 向父组件传递参数
            this.$emit('col', item)
        }
    }
});