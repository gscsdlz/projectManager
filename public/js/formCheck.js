jQuery.extend({
    formCheck : function(target){
        var passCheck = true;

        $(target).find(".has-error").removeClass('has-error');
        $(target).find(".fcInfo").remove();

        //文本域表单
        $("[formCheck-noEmpty]").each(function() {
            if($(this).val().length == 0) {
                display($(this));
            }
        })

        //下拉框
        $("[formCheck-notSelect]").each(function(){
            var value = $(this).attr('formCheck-notSelect');
            if($(this).val() == value) {
               display($(this))
            }
        })

        //正则匹配
        $("[formCheck-regex]").each(function(){
            var p = $(this).attr('formCheck-regex');
            var patt = new RegExp(p);
            if(!patt.test($(this).val())) {
                display($(this))
            }
        })

        //常用测试慢慢补充
        $("[formCheck-email]").each(function(){
            var patt = new RegExp('^\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*$');
            if(!patt.test($(this).val())) {
                display($(this))
            }
        })
        function display(that){
            $(that).parent().addClass('has-error');
            if(typeof $(that).attr("formCheck-info") != "undefined")
                $(that).after('<p class="text-danger fcInfo">'+$(that).attr("formCheck-info")+'</p>');
            passCheck = false;
        }
        return passCheck;
    }
})