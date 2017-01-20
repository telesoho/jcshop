/**
 * Created by yb on 2016/11/14.
 */
//本地缓存函数
function setItem(key,value){
    var val=JSON.stringify(value)?JSON.stringify(value):[];
    window.localStorage.setItem(key,val);
}
function getItem(key){
    var getter= window.localStorage.getItem(key);
    return JSON.parse(getter);
}
function removeItem(key){
    window.localStorage.removeItem(key);
}
function pushSession(key,value){
    var val=JSON.stringify(value)?JSON.stringify(value):[];
    if(window.sessionStorage){
        sessionStorage.setItem(key,val);
    }else{
        console.log("无法使用缓存");
        return "";
    }
}
function getSession(key){
    if(window.sessionStorage){
        var state=sessionStorage.getItem(key)?sessionStorage.getItem(key):0;
        return JSON.parse(state);
    }else{
        console.log("无法使用缓存");
        return "";
    }
}
function removeSessionItem(key){
    window.sessionStorage.removeItem(key);
}
//功能函数
//搜索处理函数
function handler() {
    event.preventDefault();
}
function getSearth(){
	document.body.scrollTop =0;
	$('window').scroll().Top = 0;
	setTimeout(function(){
	    document.getElementById("search").value="";
	    document.getElementById("search").placeholder="";
	    document.getElementById("modalid-search").className="show";
	    document.getElementById("cancle").className="show cancel";
		
	//	console.log(h)
	    document.getElementById("homeHeader").style.cssText="position:fixed;width:100%;z-index:1111;top:0px;left:0";
	    document.body.style.overflow = 'hidden';
	    
	    document.body.addEventListener('touchmove', handler, false);
    },1000)
}
function searthCancel(){
    document.getElementById("search").placeholder=getItem("placeHolder")+"件商品等你来搜";
    document.getElementById("modalid-search").className="hide";
    document.getElementById("cancle").className="hide cancel";
    document.body.style.overflow = 'auto';
    document.getElementById("homeHeader").style.position="static";
    document.body.removeEventListener('touchmove', handler, false);
    time_xian();
}
function ToSearthPage(item){
    setItem("searth_word",item);
    removeSessionItem("searth_answer");
    window.location.href="/site/search_list";
}
function searthSubmit(){
    var subContent=document.getElementById("search").value;
    setItem("searth_word",subContent);
    removeSessionItem("searth_answer");
    if(subContent==""){
        searthCancel();
    }else{
        window.location.href="/site/search_list";
    }
    return false;
}
//分类页面处理函数
function toPageThird(obj){
    setItem("siteMap",obj)
    window.location.href="/site/category_third";
}
//记录三级页面位置函数
function getPosition(){
    var pid=getItem("product");
    console.log(pid);
    var eid=document.getElementById("product_item"+pid);
    var scroll=eid?eid:"";
    console.log(scroll.offsetTop);
    return scroll.offsetTop;

}
function getScrollTop()
{
    var position=getPosition();
    if(document.documentElement&&document.documentElement.scrollTop)
    {
        document.documentElement.scrollTop=position-9;
    }
    else if(document.body)
    {
        document.body.scrollTop=position-9;
    }
    removeItem('product');
//        console.log(position);
}
function getPosition1(){
    var pid=getSession('product1');
    var eid=document.getElementById("product_item"+pid);
    var scroll=eid?eid:"";
    console.log(scroll.offsetTop);
    return scroll.offsetTop;


}
function getScrollTop1()
{
    var position=getPosition1();
    if(document.documentElement&&document.documentElement.scrollTop)
    {
        document.documentElement.scrollTop=position-9;
    }
    else if(document.body)
    {
        document.body.scrollTop=position-9;
        console.log(document.body.scrollTop);
    }
    removeSessionItem('product1');
//        console.log(position);
}
//	获取url传递过来的参数
function GetRequest() {
    var url = location.search; //获取url中"?"符后的字串
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(1);
        strs = str.split("&");
        for(var i = 0; i < strs.length; i ++) {
            theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
        }
    }
    return theRequest;
}
