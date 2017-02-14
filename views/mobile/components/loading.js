Vue.component("loadings",{
	template:'<div id="loading" position: absolute;left: 50%;top: 50%;margin-top: -0.5rem;margin-left: -0.5rem" v-if="load">\
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
            props:['load']
})
