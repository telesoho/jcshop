function active(fileName){
	var date = "?v="+Date.parse(new Date());
	var head = document.getElementsByTagName("head")[0];
	var whether_file = document.createElement("link");
	whether_file.setAttribute("rel","stylesheet");
	whether_file.setAttribute("type","text/css");
	whether_file.setAttribute("href",fileName+date);
	head.appendChild(whether_file);
}
active("/views/mobile/componentsCss/Whether.css")
Vue.component("whether",{
	template:'<div class="flexbox empty_product" style="" v-if="state">\
            <div class="img">\
                <img src="/views/mobile/skin/default/image/jmj/icon/nomore.png" alt=""/>\
            </div>\
            <div class="content contents">\
                <span style="color:#bbb">~</span>\
                <span style="color:#bbb">憋拉了，到底了</span>\
                <span style="color:#bbb">~</span>\
            </div>\
       </div>',
	props:["state"]
})
