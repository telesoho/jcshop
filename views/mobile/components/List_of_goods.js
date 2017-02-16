function active(filecss){
	var date ="?v=" + Date.parse(new Date());
	var head = document.getElementsByTagName("head")[0];
	var filelink = document.createElement("link");
	filelink.setAttribute("rel","stylesheet");
	filelink.setAttribute("type","text/css");
	filelink.setAttribute("href",filecss+date);
	head.appendChild(filelink);
}
active("/views/mobile/componentsCss/List_of_goods.css")
Vue.component("goods_list",{
	template:'<div class="content">\
					<a :href="item.url" class="item locationA" v-for="item in msg">\
						<div class="img">\
							<img :src="item.img" alt="" />\
						</div>\
						<div class="con">\
							<div class="name hidewrap">\
								{{item.name}}\
							</div>\
							<div class="price">\
								<span style="font-size:0.22rem;color:#ff2f5c">¥ </span>{{item.sell_price}}\
							</div>\
							<div class="relate_price">\
								日本价：<span style="font-family:HelveticaNeue;font-size:0.26rem;color:#b8b8b8;">{{item.jp_price}}</span>\
							</div>\
						</div>\
					</a>\
				</div>',
	props:["msg"],
	mounted:function(){
		console.log(this.msg)
	}
})
