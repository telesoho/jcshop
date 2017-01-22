/**
 * Created by yb on 2017/1/22.
 */
Vue.component('cat-refresh', {
    template: '<div class="catContainer flexbox" v-if="!mes">\
    <img src="/views/mobile/skin/default/image/jmj/icon/cat_car.png" alt="" class="ball" id="cball" />\
    <div class="shadow"></div>\
    </div>',
    props:['mes']
});