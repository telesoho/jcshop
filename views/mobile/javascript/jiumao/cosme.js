var oLastWeek = $("#lastWeek");
var oLastWeek2 = $("#lastWeek2");
var oLastWeek3 = $("#lastWeek3");
var more = $(".more");
console.log(1)
var str1 = "",str2="",str3="";
$.ajax({
	type:"get",
	url:"/apic/cosme/3",
	dataType:"json",
	success:function(res){
		var res1 = res.cosme1.list;
		var res2 = res.cosme2.list;
		var res3 = res.cosme3.list;
		console.log(res3);
		//str1 上周热销榜
		for(var i =0;i<res1.length;i++){
			str1 +='<li><a href ="/site/products?id='+res1[i].id+'">\
					<img src="/views/mobile/skin/default/image/jmj/cosme/0'+(i+1)+'.png" class="logo1"/>\
					<img src='+res1[i].img+' class="logo2" style="width: 1.8rem; height: 1.8rem;"/>\
					<p>'+res1[i].name+'</p>\
					<span class="span2"><span class="span1">¥</span>'+res1[i].sell_price+'</span>\
					</a></li>'
		}
		console.log(str1);
		oLastWeek.html(str1);


		//  str2	"美容热销榜"
		for(var j =0;j<res2.length;j++){
			str2 +='<li><a href ="/site/products?id='+res2[j].id+'">\
					<img src="/views/mobile/skin/default/image/jmj/cosme/0'+(j+1)+'.png" class="logo1"/>\
					<img src='+res2[j].img+' class="logo2" style="width: 1.8rem; height: 1.8rem;"/>\
					<p>'+res2[j].name+'</p>\
					<span class="span2"><span class="span1">¥</span>'+res2[j].sell_price+'</span>\
				</a></li>';
		};
		oLastWeek2.html(str2);
		
		
		// str3		美容护理榜
		for(var k =0;k<res3.length;k++){
			str3 +='<li><a href ="/site/products?id='+res3[k].id+'">\
					<img src="/views/mobile/skin/default/image/jmj/cosme/0'+(k+1)+'.png" class="logo1"/>\
					<img src='+res3[k].img+' class="logo2" style="width: 1.8rem; height: 1.8rem;"/>\
					<p>'+res3[k].name+'</p>\
					<span class="span2"><span class="span1">¥</span>'+res3[k].sell_price+'</span>\
				</a></li>'
		}
		oLastWeek3.html(str3);
		
		
		//点击更多跳转
		var name = "";
		more.click(function(){
			var type = $(this).attr("num");
			if(parseInt(type) == 1){
				name="上周热销榜";
			}else if(parseInt(type) == 2){
				name="美容热销榜";
			}else if(parseInt(type) == 3){
				name="美容护理榜";
			}
			console.log(name)
			window.location.href = '/site/category_third/id/'+type+'/title/'+name+'/cosme/'+type+''
		})	
	}
});
