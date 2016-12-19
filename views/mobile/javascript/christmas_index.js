var em = new Vue({
	el:"#wrap",
	data:{
		cat:[{
			src:''
		}],
		img:["/views/mobile/skin/default/image/jmj/new_active/yaozhuang.png","/views/mobile/skin/default/image/jmj/new_active/gehu.png",
		"/views/mobile/skin/default/image/jmj/new_active/chongwu.png",
		"/views/mobile/skin/default/image/jmj/new_active/jiankang.png",
		"/views/mobile/skin/default/image/jmj/new_active/lingshi.png"],
		list1:[],
		list:[],
		style2:"padding-bottom:0.1rem",
		style1:"padding-bottom:0.1rem",
		banner:[
					"/views/mobile/skin/default/image/jmj/new_active/banner/6.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/7.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/8.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/9.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/10.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/11.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/12.png"
				]
//		banner图为专场
	},
	computed:{
		newcat:function(){
			this.cat.map(function(item){				
				item.url="/activity/christmas_list?id="+item.id;
				
			})
			return this.cat;
		},
		newlist1:function(){
			this.list1.map(function(item){
				item.url="/site/products?id="+item.id;
				
			})
			return this.list1;
		},
		newlist:function(){
			var t = 6
			this.list.map(function(item){
				item.map(function(itemList){
					itemList.url="/site/products?id="+itemList.id;
					
				})	
				item.url = "/activity/christmas_list?id="+t;
				if(t==6){
					item.url = "/activity/christmas_brand_list";
				}
				t++
			})
			return this.list;
		},
		
	},
	
	mounted:function(){
		var self = this;
		getActiveInfo(self);
	},
	updated:function() {
		// 页面加载完成执行的函数;
		lazyload.init({
			anim:false,
			selectorName:".samLazyImg"
		});
	},
	methods:{
		getTicket:function(tid){
//			console.log(tid)
			youhuijian(tid)
		}
	}
})
function getActiveInfo(self){
	mui.ajax('/apic/christmas_index', {
		data:{
			
		},
		dataType: 'json',
		type: 'get',
		timeout: 10000,
		success: function (data){
			//获取专区的图片(img自己定义的)
			for(var i=0;i<data.data.cat.length;i++){
				data.data.cat[i].src=self.img[i];
				
			}
			
//			data.data.map(function(val){
//				console.log(val);
//			})
			//循环list2-list7的object 推入数组
          for( var item in data.data){
          	if(item!="cat"&&item!="list1"){
          		self.list.push(data.data[item]);
          	}
          	
          };
          //获取专场的图片(banner图自己定义的)
          for(var j =0;j<self.banner.length;j++){
          		self.list[j].bannerimg = self.banner[j];
          }
          	//获取专区(5个专区)
          	self.cat=data.data.cat;
			//打折的商品分类
			self.list1=data.data.list1;
//			self.list2=data.data.list2;
//			self.list3=data.data.list3;
//			self.list4=data.data.list4;
//			self.list5=data.data.list5;
//			self.list6=data.data.list6;
//			self.list7=data.data.list7;
////			data.data.map(function(item){
////				self.info.push(item);
////			})
		console.log(data.data)
		},

	});
}
//优惠劵  领取
function youhuijian(tid){
	mui.ajax('/apic/get_ticket', {
		data:{
			tid:tid
		},
		dataType: 'json',
		type: 'get',
		timeout: 10000,
		success: function (data){
			if(data.code==0){
				var btnArray = ['取消', '查看'];
                    mui.confirm('领取成功，查看优惠券？', '领取成功', btnArray, function(e) {
                        if (e.index == 1) {
                         
                            window.location.href="/site/ticket_list"
                        } else {
                            
                        }
                    })
			}else{
				alert(data.msg);
			}
			

		}
	})
}
