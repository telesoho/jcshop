var oInput = document.getElementById("inputs");
var	oInputval = oInput.value
var pages = 0;
var em = new Vue({
	el:"#PopupWindow",
	data:{
		aid:"3",
		pid:oInputval,
		info:[],
		p:"",
		info1:""
	},
	computed:{
		newactivity:function(){
			return this.info	
		},
		aaa:function(){
			console.log(typeof this.info1)
			if(this.info1 == "0"){
				this.p="喵酱提醒"
			}else{
				this.p=""
			}
			return this.p
		}
	},
	mounted: function(){
		var self = this;
		activity(self);
	},
	methods:{
		tiao:function(){
			window.location.href="/site/ticket_list"
		}
	}
})
function activity(self){
	mui.ajax('/apic/get_ticket_activity', {
		data:{
			aid:self.aid,
			pid:self.pid ? self.pid : ""
		},
		dataType: 'json',
		type: 'get',
		timeout: 10000,
		success: function (data) {
			console.log(2);
			self.info = data.msg;
			self.info1 = data.code
		console.log(data)
		}
	});
}
// 					弹	窗	处	理
		 
			$("#btn_img").click(function(){
				pages++
				tanchaun();
				if(pages>=2){
					activity(em)
				}
			})
	function tanchaun(){
//			document.body.style.overflow="hidden";
			$("#bgg").css({
				"display":"block",
				"overflow":"hidden"
			})
		
			$("#PopupWindow").css({
				"display":"block",
			}).animate({
				"top": "1.2rem",
			},500);
			
			$(document).on('touchmove',function(event) { event.preventDefault(); }, false);
			$(".fix-nav").css({
				"display":"none"
			})
		$("#guan").click(function(){
           
//			document.body.style.overflow=""
			$("#bgg").css({
				"display":"none",
				"overflow":"hidden"
			})
			$("#PopupWindow").css({
				"display":"none",
				"top": "-6.78rem"
			});
			$(document).unbind('touchmove');
			
		})
		
	}