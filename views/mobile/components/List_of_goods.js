function active(filecss){
	var date ="?v=" + Date.parse(new Date());
	var head = document.getElementsByTagName("head")[0];
	var filelink = document.createElement("link");
	filelink.setAttribute("rel","stylesheet");
	filelink.setAttribute("type","text/css");
	filelink.setAttribute("href",filecss+date);
	head.appendChild(filelink);
}
function creatScript(fileJs){
	var date ="?v=" + Date.parse(new Date());
	var head = document.getElementsByTagName("head")[0];
	var fileSrc = document.createElement("script");
	fileSrc.setAttribute("type","text/javascript");
	fileSrc.setAttribute("src",fileJs+date);
	head.appendChild(fileSrc);
}
active("/views/mobile/componentsCss/List_of_goods.css");
creatScript("/views/mobile/javascript/lazyload.js");
Vue.component("goods_list",{
	template:'<div class="content">\
					<a :href="item.url" class="item locationA" v-for="item in msg">\
						<div class="img">\
							<img :dataimg="item.img" src="/views/mobile/skin/default/image/jmj/icon/bg_lazy.jpg" class="samLazyImg" alt="" />\
						</div>\
						<div class="con">\
							<div class="name hidewrap" style="text-align: center">\
								{{item.name}}\
							</div>\
							<div class="price" style="text-align: center">\
								<span style="font-size:0.22rem;color:#ff2f5c">¥ </span>{{item.sell_price}}\
							</div>\
							<div class="relate_price" style="text-align: center">\
								日本价：<span style="font-family:HelveticaNeue;font-size:0.26rem;color:#b8b8b8;">{{item.jp_price}}円</span>\
							</div>\
						</div>\
					</a>\
				</div>',
	props:["msg"]
})
