 //时分秒倒计时方法  
        function timer(eleId){  
            var element=document.getElementById(eleId);  
            if(element){  
                endTimer=element.getAttribute('data-timer');  
                var endTime=new Date(parseInt(endTimer.substr(0,4)),parseInt(endTimer.substr(4,2)),parseInt(endTimer.substr(6,2)),parseInt(endTimer.substr(8,2)),parseInt(endTimer.substr(10,2)),parseInt(endTimer.substr(12,2)));  
                var endTimeMonth=endTime.getMonth()-1;  
                endTime.setMonth(endTimeMonth);  
                var ts = endTime.getTime() - new Date().getTime();  
                if(ts>0){  
                    var dd = parseInt(ts / 1000 / 60 / 60 / 24, 10);  
                    var hh = parseInt(ts / 1000 / 60 / 60 % 24, 10);  
                    var mm = parseInt(ts / 1000 / 60 % 60, 10);  
                    var ss = parseInt(ts / 1000 % 60, 10);  
                    dd = dd<10?("0" + dd):dd;   //天  
                    hh = hh<10?("0" + hh):hh;   //时  
                    mm = mm<10?("0" + mm):mm;   //分  
                    ss = ss<10?("0" + ss):ss;   //秒   
                    document.getElementById("timer_h").innerHTML=hh;  
                    document.getElementById("timer_m").innerHTML=mm;  
                    document.getElementById("timer_s").innerHTML=ss;  
                    setTimeout(function(){timer(eleId);},1000);  
                }else{  
                    document.getElementById("timer_h").innerHTML=0;  
                    document.getElementById("timer_m").innerHTML=0;  
                    document.getElementById("timer_s").innerHTML=0;  
                }  
            }  
        }  