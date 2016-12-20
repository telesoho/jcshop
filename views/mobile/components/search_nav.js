/**
 * Created by yb on 2016/12/20.
 */

// 创建一个搜索的组件
Vue.component('search-nav', {
    template:'<section class="search-header" id="homeHeader">\
    <div class="inputContent">\
    <form onsubmit="searthSubmit()" action="/site/search_list">\
    <input type="search" :placeholder="searth_pla" id="search" @focus="showSearth" v-model="val"/>\
    <img class="search_ico" src="/views/mobile/skin/default/image/jmj/new_active/index/search.png" alt="" />\
    </form>\
    <img src="/views/mobile/skin/default/image/jmj/new_active/index/leftear.png" alt="" class="left-icon" />\
    <img src="/views/mobile/skin/default/image/jmj/new_active/index/rightear.png" alt="" class="right-icon" />\
    <img src="/views/mobile/skin/default/image/jmj/new_active/index/flower.png" alt="" class="img-flower" />\
    <div class="cancel hide" id="cancle" onclick="searthCancel();">取消</div>\
    </div>\
    <div id="modalid-search" class="hide">\
    <div class="title">\
    猫猫都在搜\
    </div>\
    <div class="search-content">\
    <a href="#"  @click="ToSearthPage(item.word)" v-for="(item,key) in search" v-if="key<12">{{item.word}}</a>\
</div>\
<button type="button" id="button-submit"  onclick="searthSubmit();">确定</button>\
    <button type="button" id="button-cancel"  onclick="searthCancel();">取消</button>\
    </div>\
    </section>',
    data:function(){
         return {
             searth_pla:'圣诞神秘大礼等你来拿'
         }
    },
    props:['search','val'],
    methods:{
        showSearth:function(){
            getSearth()
        }
    }
});