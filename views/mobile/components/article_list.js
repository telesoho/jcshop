/**
 * Created by yb on 2016/12/26.
 */
// 专辑列表组件 用到的地方有四个  首页专辑  article-list  pro_list 以及今日好货推荐
Vue.component('article-list', {
    template:'<div class="title">\
    <span class="logo" >\
    <img :src="item.icon" alt="" class="img-hot">\
    </span>\
    <span class="name">\
    {{item.category_name}}\
</span>\
<span class="num mui-pull-right favorite<%=dat.id %>">\
    {{item.favorite_num}}\
</span>\
<span class="like mui-pull-right">\
    <img :src="item.is_favorite==0?img1:img2"  alt="" class="img-like" @click="collection(item)" />\
    </span>\
    </div>\
    <a href="#" @click="store(item)" :id="item.product_id">\
    <div class="content">\
    <div class="img">\
    <img :dataimg="item.image" alt=""  style="height:3.79rem;" src="/views/mobile/skin/default/image/jmj/icon/big_lazy.png"  class="samLazyImg"  />\
    </div>\
    <div class="mark1">{{item.goods_num}}件商品\
</div>\
<div class="mark2">\
    <img src="/views/mobile/skin/default/image/jmj/icon/read.png" alt="">\
    {{item.visit_num}}</div>\
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
<div class="sellprice" style="font-size:0.26rem;">\
    <span style="font-size:0.2rem;color:#ff4aa0;margin-right:-0.08rem;">￥</span> {{itemList.sell_price}} </div>\
</a>\
<div class="singleline"></div>\
    </div>\
    </div>\
    </div>\
    </div>',
    data:function(){
        return {
            searth_pla:'圣诞神秘大礼等你来拿'
        }
    },
    props:['item','state'],
    methods:{
        showSearth:function(){
            getSearth()
        }
    }
});