/**
 * Created by yb on 2017/1/22.
 */
function active(classname){
	var date = "?v=" + Date.parse(new Date())
	var head = document.getElementsByTagName("head")[0];
	var loadding_file = document.createElement("link");
	loadding_file.setAttribute("rel","stylesheet");
	loadding_file.setAttribute("type","text/css");
	loadding_file.setAttribute("href",classname+date);
	head.appendChild(loadding_file);
}
active("/views/mobile/componentsCss/loadding_component.css")
Vue.component('loadding', {
    template: '<div id="loading" v-if="mes">\
    <div class="spinner">\
    <div class="spinner-container container1">\
    <div class="circle1"></div>\
    <div class="circle2"></div>\
    <div class="circle3"></div>\
    <div class="circle4"></div>\
    </div>\
    <div class="spinner-container container2">\
    <div class="circle1"></div>\
    <div class="circle2"></div>\
    <div class="circle3"></div>\
    <div class="circle4"></div>\
    </div>\
    <div class="spinner-container container3">\
    <div class="circle1"></div>\
    <div class="circle2"></div>\
    <div class="circle3"></div>\
    <div class="circle4"></div>\
    </div>\
    </div>\
    </div>',
    props:['mes']
});