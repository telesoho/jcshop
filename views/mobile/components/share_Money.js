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
					window.location.href = "/ucenter/shareMoneyDetail"
				}
			}
		}
})