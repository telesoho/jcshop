var oInput = document.getElementById("inputs");
var	oInputval = oInput.value

var em = new Vue({
	el:"#PopupWindow",
	data:{
		aid:"2",
		pid:oInputval,
		info:[],
		p:"",
		info1:""
	},
	computed:{
		newactivity:function(){
			console.log(this.info)
			
			return this.info	
		},
		aaa:function(){
			console.log(typeof this.info1)
			if(this.info1 == "0"){
				this.p="恭喜您"
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
			self.info = data.msg;
			self.info1 = data.code
		console.log(data)
		}
	});
}
