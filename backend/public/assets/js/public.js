
$(function(){

    //公共表单处理方法
    _qlk.selector('common-form').submit(function() {
        var self = $(this), btn = self.find('[type=submit]');
        _qlk.submit(self, btn);
        return false;
    });

    _qlk.selector('calllayer-btn').click(function(){
        var self=$(this),href=self.attr('href'),layertitle=self.data('layertitle');
        var layersize=['800px','60%'];
        if(self.data('layerwidth')){
            layersize[0]=self.data('layerwidth');
        }
        if(self.data('layerheight')){
            layersize[1]=self.data('layerheight');
        }
        layer.open({
            type: 2,//0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
            title: layertitle,
            shadeClose: true,//是否点击遮罩关闭
            shade: 0.8,//即弹层外区域。默认是0.3透明度的黑色背景（'#000'）
            area: layersize,//大小
            scrollbar: false,//是否允许浏览器出现滚动条
            content: href //iframe的url
        });
        return false;
    });
});

