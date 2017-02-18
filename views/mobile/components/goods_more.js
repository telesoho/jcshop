function active(filename){
	var date = "?v="+Date.parse(new Date());
	var head = document.getElementsByTagName("head")[0];
	var goodsfile = document.createElement("link");
	goodsfile.setAttribute("rel","stylesheet");
	goodsfile.setAttribute("type","text/css");
	goodsfile.setAttribute("href",filename+date);
	head.appendChild(goodsfile);
}
active("/views/mobile/componentsCss/goods_more.css");
Vue.component('goods_list',{
	template:'<a :href="item.url">\
                <div class="img">\
                    <img src="/views/mobile/skin/default/image/jmj/product/icon-third.png" alt="" class="img-logo"  />\
                    <img :dataimg="item.img" src="/views/mobile/skin/default/image/jmj/product/ware_lazy.png" alt="" class="samLazyImg img-ware" />\
                    <img src="" alt="" class="" />\
                </div>\
                <div class="content">\
                    <div class="name" style="text-align: center">{{item.name}}</div>\
                    <div class="price" style="text-align: center">\
                        <span class="sellprice">¥{{item.sell_price}}</span>\
                        <span class="japanprice">日本价：{{item.jp_price}}円</span>\
                        <span class="openprice">国内价：¥{{item.market_price}}</span>\
                    </div>\
                </div>\
            </a>',
	props:["item"],
	computed:{},
	mounted:function(){
	}
})
