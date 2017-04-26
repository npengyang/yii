var qlk = function(){};

qlk.prototype.selector=function(name){
    return $('[data-selector="'+name+'"]');
};

//表单提交
qlk.prototype.submit=function(form,btn){
    var btntxt=btn.html();
    btn.html('处理中...').attr('disabled',true);
    var data = form.serialize();
    console.log(data);
    $.ajax({
        url:form.attr('action'),
        type:form.attr('method'),
        data:form.serialize(),
        dataType:'json',
        success:function(d){
            btn.html(btntxt).attr('disabled',false);
            if(d["flag"]==1 || d["status"]==1){
                setTimeout(function(){
                    layer.msg(d.msg);
                },200);
                if(form.data('jump')){
                    setTimeout(function(){
                        top.window.location.href=form.data('jump');
                    },1000);
                }else if(d["redirectUrl"]){
                    setTimeout(function(){
                        top.window.location.href=d["redirectUrl"];
                    },1000);
                }else{
                    setTimeout(function(){
                        top.location.href=top.window.location.href;
                    },1000);
                }
            }else{
                setTimeout(function(){
                    layer.msg(d["msg"], {icon: 5});
                },500);
            }
        }
    });
}

//confirm
qlk.prototype.confirm=function(msg,callback1,callback2){
    layer.confirm(msg, {
        title:'确认提醒',
        btn: ['确定','取消'] //按钮
    }, function(){
        (!!callback1)&&callback1();
    },function(){
        (!!callback2)&&callback2();
    });
};

//error
qlk.prototype.error=function(msg){
    layer.msg(msg, {icon: 2});
};
//success
qlk.prototype.success=function(msg){
    layer.msg(msg, {icon: 1});
};

var _qlk = new qlk();

