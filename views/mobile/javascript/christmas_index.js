var em = new Vue({
	el:"#wrap",
	data:{
		cat:[{
			src:''
		}],
		img:["/views/mobile/skin/default/image/jmj/new_active/gehu.png","/views/mobile/skin/default/image/jmj/new_active/yaozhuang.png",
		"/views/mobile/skin/default/image/jmj/new_active/chongwu.png",
		"/views/mobile/skin/default/image/jmj/new_active/jiankang.png",
		"/views/mobile/skin/default/image/jmj/new_active/lingshi.png"],
		list1:[],
		list:[],
		style2:"padding-bottom:0.3rem",
		style1:"padding-bottom:0.3rem",
		banner:[
					"",
					"",
					"/views/mobile/skin/default/image/jmj/new_active/banner/8.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/9.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/10.png",
					"/views/mobile/skin/default/image/jmj/new_active/banner/11.png",
//					"/views/mobile/skin/default/image/jmj/new_active/banner/12.png"
				]
//		banner图
		
	},
	computed:{
		newcat:function(){
			this.cat.map(function(item){				
				item.url="/activity/christmas_list?id="+item.id+"&cid="+item.cid;
				
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
			this.list.map(function(item){
				item.map(function(itemList){
					itemList.url="/site/products?id="+itemList.id;
				})
//				
				
			})
			return this.list;
		}
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
			for(var i=0;i<data.data.cat.length;i++){
				data.data.cat[i].src=self.img[i];
				
			}
			self.cat=data.data.cat;
//			data.data.map(function(val){
//				console.log(val);
//			})
	
          for( var item in data.data){
          	if(item!="cat"&&item!="list1"){
          		self.list.push(data.data[item]);
          	}
          	
          };
          for(var j =0;j<self.banner.length;j++){
          		console.log(self.banner.length);
          		self.list[j].bannerimg = self.banner[j];
          	}
          
//        console.log(self.list);
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
