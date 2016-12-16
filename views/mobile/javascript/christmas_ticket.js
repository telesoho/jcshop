var oInput = document.getElementById("inputs");
var	oInputval = oInput.value

var em = new Vue({
	el:"#PopupWindow",
	data:{
		aid:"2",
		pid:oInputval
	},
	computed:{
		newactivity:function(){
			
		}
	},
	mounted: function(){
		var self = this;
		activity(self);
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
//			data.data.map(function(item){
//				self.info.push(item);
//			})
		console.log(data)
		}
	});
}
