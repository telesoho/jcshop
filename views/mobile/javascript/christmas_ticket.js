var oInput = document.getElementById("inputs");
var	oInputval = oInput.value

var em = new Vue({
	el:"#PopupWindow",
	data:{
		aid:"2",
		pid:oInputval,
		info:[]
	},
	computed:{
		newactivity:function(){
			return this.info
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
			self.info = data.msg
		console.log(data)
		}
	});
}
