function active(classname){
	var date = "?v="+Date.parse(new Date());
	var head = document.getElementsByTagName("head")[0];
	var share_file = document.createElement("link");
	share_file.setAttribute("rel","stylesheet");
	share_file.setAttribute("type","text/css");
	share_file.setAttribute("href",classname+date);
	head.appendChild(share_file);
}
active("/views/mobile/componentsCss/share_Money_component.css")
Vue.component("sharemoney",{
	template:'<header id="header" @click="share_detail(share.key)">\
			   <div class="img_top">\
			   		<img :src="share.img"/>\
			   </div>\
			   <div class="left_youbalance">\
			   		<p>{{share.youbalance}}</p>\
			   		<span>账户余额(元)</span>\
			   </div>\
			   <div class="right_nobalance">\
			   		<p>{{share.nobalance}}</p>\
			   		<span>未到账(元)</span>\
			   </div>\
		</header>',
		props:["share"],
		methods:{
			share_detail:function(key){
				if(key == 1){
					window.location.href = "/ucenter/share_money_detail"
				}
			}
		}
})