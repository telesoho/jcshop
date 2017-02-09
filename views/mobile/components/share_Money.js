Vue.component("sharemoney",{
	template:'<header id="header">\
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
		props:["share"]
})