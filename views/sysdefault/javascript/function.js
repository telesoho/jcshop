/* 自定义方法 */

//消息提示
var informMove = false ;
function _inform(text,fn,delay,speed1,speed2){
    if(informMove == true){ return false; } informMove = true;
    var informDiv = $('<div style="padding:20px 30px; background:rgba(0,0,0,0.5); color:#fff; font-size:20px; line-height:20px; position:fixed; left:50%; top:0%; z-index:9999; border-radius:10px; opacity:0; -webkit-transform:translateX(-50%); -moz-transform:translateX(-50%); -ms-transform:translateX(-50%); transform:translateX(-50%);">操作成功</div>');
    if(text)informDiv.text(text);
    if(!delay)delay     = 1000;  //停留时间
    if(!speed1)speed1   = 200;  //出现时间
    if(!speed2)speed2   = 300;  //消失时间
    informDiv.appendTo($('body')).animate({"top":"20%","opacity":"1"},speed1).delay(delay).animate({"top":"0%","opacity":"0"},speed2,function(){
        informDiv.remove();
        if(fn)fn();
        informMove = false;
    });
}
//操作确认框
function _confirm(text,fn,obj){
    var speed1  = 300;  //出现时间
    var speed2  = 400;  //消失时间
    var html    = '<div style="display:none; width:100%; height:100%; position:fixed; left:0; top:0; z-index:8888;">';
    html        += '<div style="position:absolute; width:100%; height:100%; left:0; top:0; background:rgba(0,0,0,0.3);"></div>';
    html        += '<div class="hint_contain" style="color:#fff; font-size:16px; opacity:0; min-width:200px; height:100px; background:rgba(0,0,0,0.6); position:absolute; left:50%; top:0%; margin-left:-125px; margin-top:-50px; text-align:center; border-radius:10px; -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none;">';
    html        += '<p style="font-size:16px; line-height:20px; padding:20px 20px; border-bottom:solid 1px #000; overflow:hidden;">确定删除?</p>';
    html        += '<span class="hint_true" style="display:block; float:left; line-height:39px; box-sizing:border-box; cursor:pointer; width:50%; height:39px; border-bottom-left-radius:10px; border-right:solid 1px #000;" >确定</span>';
    html        += '<span class="hint_false" style="display:block; float:left; line-height:39px; box-sizing:border-box; cursor:pointer; width:50%; height:39px; border-bottom-right-radius:10px;">取消</span></div></div>';
    var thisDiv = $(html);
    if(text)thisDiv.find('p').text(text);
    thisDiv.appendTo($('body')).show().find('.hint_contain').animate({'top':'20%','opacity':'1'},speed1,function(){
        thisDiv.find('.hint_true').on({
            'click'         : function(){
                thisDiv.find('.hint_contain').animate({'top':'0%','opacity':'0'},speed2,function(){
                    thisDiv.remove();
                    if(fn){fn(obj);}
                });
            },
            'mousedown'     : function(){ thisDiv.find('.hint_true').css("background","rgba(0,0,0,0.8)"); },
            'mouseover'     : function(){ thisDiv.find('.hint_true').css("background","rgba(0,0,0,0.3)"); },
            'mouseout'      : function(){ thisDiv.find('.hint_true').css("background","none"); },
        });
        thisDiv.find('.hint_false').on({
            'click'         : function(){
                thisDiv.find('.hint_contain').animate({'top':'0%','opacity':'0'},speed2,function(){
                    thisDiv.remove();
                });
            },
            'mousedown'     : function(){ thisDiv.find('.hint_false').css("background","rgba(0,0,0,0.8)"); },
            'mouseover'     : function(){ thisDiv.find('.hint_false').css("background","rgba(0,0,0,0.3)"); },
            'mouseout'      : function(){ thisDiv.find('.hint_false').css("background","none"); },
        });
    });
}

//获取地区信息
function _getArea(obj){
    var speed1  = 300;  //出现时间
    var speed2  = 400;  //消失时间
    var html    = '<div id="areainfo" style="display:none; width:100%; height:100%; position:fixed; left:0; top:0; z-index:200;">';
    html        += '<div style="position:absolute; width:100%; height:100%; left:0; top:0; background:rgba(0,0,0,0.3);"></div>';
    html        += '<div class="hint_contain" style="color:#fff; font-size:16px; opacity:0; width:350px; height:150px; background:rgba(0,0,0,0.6); position:absolute; left:50%; top:0%; margin-left:-175px; margin-top:-50px; text-align:center; border-radius:10px; -webkit-user-select:none; -moz-user-select:none; -ms-user-select:none; user-select:none;">';
    html        += '<div style="height:70px; padding:20px 0; border-bottom:solid 1px #000;"><p style="font-size:16px; margin-bottom:10px">请选择地址</p>';
    html        += '<select class="area_text" style="width:100px; color:#fff; background-color:transparent; border:solid 1px #000;" onchange="myTool.getAreaList(this);"></select>';
    html        += '<select class="area_text" style="width:100px; color:#fff; background-color:transparent; border:solid 1px #000;" onchange="myTool.getAreaList(this);"></select>';
    html        += '<select class="area_text" style="width:100px; color:#fff; background-color:transparent; border:solid 1px #000;"></select><br/>';
    html        += '<input class="area_text" type="text" size="30" value="" class="textInput" minlength="1" maxlength="30" placeholder="详细地址" style="width:290px; color:#fff; background:rgba(0,0,0,0); border:solid 1px #000; margin-top:5px;"/></div>';
    html        += '<span class="hint_true" style="display:block; float:left; line-height:39px; box-sizing:border-box; cursor:pointer; width:50%; height:39px; border-bottom-left-radius:10px; border-right:solid 1px #000;">确定</span>';
    html        += '<span class="hint_false" style="display:block; float:left; line-height:39px; box-sizing:border-box; cursor:pointer; width:50%; height:39px; border-bottom-right-radius:10px;">取消</span></div></div>';
    var thisDiv = $(html);
    var thisObj = $(obj);
    if(thisObj.val() == true){
        alert();
    }
    myTool.getAreaList(thisDiv.find('select').first(),0);
    thisDiv.appendTo($('body')).show().find('.hint_contain').animate({'top':'20%','opacity':'1'},speed1,function(){
        thisDiv.find('.hint_true').on({
            'click'         : function(){
                var text    = '';
                $.each(thisDiv.find('.area_text'),function(k,v){
                    if( $(v).val() != null && $(v).val() != ''){
                        text += $(v).val()+' ';
                    }
                });
                thisDiv.find('.hint_contain').animate({'top':'0%','opacity':'0'},speed2,function(){
                    thisDiv.remove(); $(obj).val(text.substring(0,text.length-1));
                });
            },
            'mousedown'     : function(){ thisDiv.find('.hint_true').css("background","rgba(0,0,0,0.8)"); },
            'mouseover'     : function(){ thisDiv.find('.hint_true').css("background","rgba(0,0,0,0.3)"); },
            'mouseout'      : function(){ thisDiv.find('.hint_true').css("background","none"); },
        });
        thisDiv.find('.hint_false').on({
            'click'         : function(){
                thisDiv.find('.hint_contain').animate({'top':'0%','opacity':'0'},speed2,function(){
                    thisDiv.remove();
                });
            },
            'mousedown'     : function(){ thisDiv.find('.hint_false').css("background","rgba(0,0,0,0.8)"); },
            'mouseover'     : function(){ thisDiv.find('.hint_false').css("background","rgba(0,0,0,0.3)"); },
            'mouseout'      : function(){ thisDiv.find('.hint_false').css("background","none"); },
        });
    });
}

//验证表单
function _validate(obj){
    //验证规则
    var thisRule = {
        "idcard"        : { "class":"idcard", "error":"身份证号有误" },
    };
    var idcardReg       = /^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/;
    var thisObj         = $(obj);   //当前表单对象
    var thisFlag        = true;     //验证标记
    $.each(thisRule,function(k1,v1){
        var thisDiv     = thisObj.find("."+v1.class);
        $.each(thisDiv,function(k2,v2){
            var thisInput = $(v2);
            if(thisInput.val() == ''){ return true ; }  //已经填写时才验证
            //验证操作
            switch(v1.class){
                case 'idcard' :
                    if(thisInput.val().search(idcardReg) == -1){
                        thisInput.nextAll('.error').remove();
                        thisInput.addClass('error').after('<span for="'+thisInput.attr('name')+'" class="error">'+v1.error+'</span>');
                        thisFlag = false;
                    }
                    break;
            }
        });
    });
    if(thisFlag == false){ return false; }
    return validateCallback(obj, dialogAjaxDone);
}

/* 自定义工具函数 */
myTool = {
    loading         : false,
    getAreaList      : function(obj,pid){       //获取地区列表
        var thisObj     = $(obj);
        var thisId      = $(obj).find(':selected').attr('data-id');
        if(pid == 0){ thisId=0; }
        if(thisId == null){ thisObj.nextAll('select').html(''); return false; }
        var html        = '<option value="" style="background-color:#474747;">请选择';
        $.each(db_area,function(k,v){
            if(v.pid == thisId){
                html    += '<option value="'+v.area+'" data-id="'+v.id+'" style="background-color:#474747;">'+v.area;
            }
        });
        if(pid == 0){
            thisObj.html(html);
        }else{
            thisObj.nextAll('select').html('');
            thisObj.next('select').html(html);
        }
    },
};

//主页自定义函数
index = {
    loading             : false,
    logoutStart         : function(){       //退出登录
        if(index.loading == true){ return false; } index.loading = true;
        $.post("/Admin/Public/logout",{},function(data,status){
            if(status == 'success'){
                if(data.status == 1){
                    _inform(data.info,function(){
                        window.location.href= data.url;
                    });
                }else{
                    _inform(data.info);
                }
            }
            index.loading = false;
        },'json');
    },
};

page = {
    loading             : false,    //是否AJAX请求中
};