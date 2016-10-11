$(function(){
	
	
	var slideMenu=function(o){
		var f=$("."+o.f),s=f.children("."+o.s),h=s.outerHeight();
		f.css({position:"relative"});
		s.css({height:0,opacity:0});
		f.hover(function(){
		s.show().stop(true,false).animate({height:h,opacity:1},350,function(){
			s.css({overflow:"visible"});
		});
		},function(){
			s.stop(true,false).animate({height:0,opacity:0},350,function(){
				s.hide();
			});
		});
		
	}
	// 导航左侧所有商品下拉框
	slideMenu({
		f:"nav-menus",
		s:"j-categorys"
	});
	// 导航右侧的购物车弹出框
	//slideMenu({
	//	f:"cart",
	//	s:"car_ul"
    //
	//});
	//登录过后的弹出框
	slideMenu({
		f:"j-user-img",
		s:"j-logined"
	});
	//教程js弹出框
//	slideMenu({
//		f:"j-user-teach",
//		s:"j-teach"
//	    });



});