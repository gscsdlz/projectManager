;(function($) {
    $.fn.dynamicTables = function(options) {
        var defaults = {
            'class' : 'table table-bordered table-condensed ',
            'title' : [],
            'data' : [],
            'delsURL' : '',
            'saveURL' : '',
            'addURL' : '',
            'fetchURL' : '',
            'fetch' : '',
            'paginationURL': '',
            'typeConfig' : [],
            'titleConfig' : [],
            'noSave' : false,
            'noDel' : false,
            'noAdd' : false,
            'noOperator' : true,
            'operatorHTML' : '',
            'currentPage':-1,
            'totalPage':-1,
        };
        var settings = $.extend({}, defaults, options);
        var b = new Base64();
        var target = $(this);
        /**
         * 初始化表格 样式表 和表头
         */
        $(this).append("<tbody></tbody>");
        $(this).addClass(settings.class);
        function initTableHeader() {
            var str = '<tr>';
            if (!(settings.noAdd && settings.noDel && settings.noSave))
                str += '<th width="40px"  class="text-center"><input type="checkbox" name="ids" /></th>';
            for (var i = 0; i < settings.title.length; i++) {
                var find = false;
                for (var j = 0; j < settings.titleConfig.length; ++j) {
                    if (settings.titleConfig[j].title == settings.title[i]) {
                        find = true;
                        str += '<th><a tabindex="0" data-trigger="focus" class="btn btn-link" datadata-placement="bottom"  role="button" data-toggle="popover" title="筛选" data-content="';
                        if (settings.titleConfig[j].type == 'select') {
                            for (var k = 0; k < settings.titleConfig[j].value.length; ++k) {
                                str += '<label><input type=\'radio\'  name=\'fetch\' value=\'' + settings.titleConfig[j].value[k] + '\'> ' + settings.titleConfig[j].options[k] + '</label><br/>';
                            }
                            str += '">' + settings.title[i] + '</a></th>';
                        }
                    }
                }
                if (!find) {
                    if(settings.typeConfig[i].edit === false)
                        str += '<th>' + settings.title[i] + '</th>'
                    else
                        str += '<th class="text-danger">' + settings.title[i] + '</th>'
                }
            }
            if (settings.noOperator == false) {
                str += "<th>操作</th>";
            }
            str += '</tr>';
            $(target).children().eq(0).append(str);
        }
        initTableHeader();

        $(this).on("click", ":radio[name='fetch']", function(){
            var val = $(this).val();
            settings.fetch = val;
            $.get(settings.fetchURL + "?fetch=" + val,  function(response){
                if(response.status == true) {
                    settings.data = response.data;
                    settings.currentPage = response.currentPage;
                    settings.totalPage = response.totalPage;
                    initTableData();
                    initPagination();
                } else {
                    $("#alertInfo").html("请求失败!!!");
                    $("#alertModal").modal();
                }
            })
        })

        /**
         * 表格内容填充
         */

        function initTableData() {

            $(target).children().eq(0).children("tr:gt(0)").remove();
            for (var i = 0; i < settings.data.length; i++) {
                var str = '<tr>'
                if(!(settings.noAdd && settings.noDel && settings.noSave))
                    str += '<th width="40px" class="text-center"><input type="checkbox" name="id" /></th>';
                for (var j = 0; j < settings.data[i].length; j++) {
                    if (settings.typeConfig[j].type == 'textarea')
                        str += '<td style="cursor: pointer">' + settings.data[i][j].substr(0, 10) + '......<input type="hidden" value="' + b.encode(settings.data[i][j]) + '" /></td>';
                    else if(settings.typeConfig[j].type == 'select') {
                        for(var k = 0; k < settings.typeConfig[j].options.length; k++)
                            if(settings.typeConfig[j].options[k][0] == settings.data[i][j]) {
                                str += '<td   style="cursor: pointer">' + settings.typeConfig[j].options[k][1] + '</td>';
                                break;
                            }
                    } else {
                        str += '<td   style="cursor: pointer">' + settings.data[i][j] + '</td>';
                    }
                }
                if(settings.noOperator == false) {
                    str += settings.operatorHTML;
                }
                str += '</tr>';
                $(target).children().eq(0).append(str);
                $('[data-toggle="tooltip"]').tooltip()
            }
        }
        initTableData();
        /**
         * 表格单元被双击的事件
         */
        $(this).on("dblclick", "td", function(){

            var cel = $(this).parent().find("td").index($(this)[0]);
            var row = $(this).parent().parent().find("tr").index($(this).parent()[0]);

            if(settings.typeConfig[cel].edit == false) {
                ;
            } else {
                if(settings.typeConfig[cel].type == 'text') {
                    if ($(this).html().indexOf("input") == -1) {
                        $(this).html('<input type="text" onkeypress="if(event.which == 13)$(this).parent().html($(this).val());" value="' + $(this).html() + '" class="form-control"/>')
                        $(":text").focus();
                    }

                } else if(settings.typeConfig[cel].type == 'select') {
                    if ($(this).html().indexOf("<select") == -1) {
                        var str = $(this).html();
                        $(this).html('<select class="form-control" onchange="$(this).parent().html($(this).find(\'option:selected\').text());"></select>');
                        for(var i = 0; i < settings.typeConfig[cel].options.length; i++) {
                            if(settings.typeConfig[cel].options[i][1] == str)
                                $(this).children().eq(0).append('<option selected value="' + settings.typeConfig[cel].options[i][0] + '">' + settings.typeConfig[cel].options[i][1] + '</option>')
                            else
                                $(this).children().eq(0).append('<option value="' + settings.typeConfig[cel].options[i][0] + '">' + settings.typeConfig[cel].options[i][1] + '</option>')
                        }
                    }
                } else if(settings.typeConfig[cel].type == 'textarea') {
                    var str = b.decode($(this).children().eq(0).val());
                    $("#longTextRow").val(row);
                    $("#longTextCel").val(cel);
                    $("#longTextModal textarea").val(str);
                    $("#longTextModal").modal();
                } else if(settings.typeConfig[cel].type == 'date') {

                    $.fn.datepicker.dates['zh-CN'] = {
                        days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"],
                        daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"],
                        daysMin:  ["日", "一", "二", "三", "四", "五", "六"],
                        months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                        monthsShort: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
                        today: "今日",
                        clear: "清除",
                        format: "yyyy年mm月dd日",
                        titleFormat: "yyyy年mm月",
                        weekStart: 1
                    };

                    if ($(this).html().indexOf("input") == -1) {
                        $(this).html('<input type="text" date-type onkeypress="if(event.which == 13)$(this).parent().html($(this).val());" value="' + $(this).html() + '" class="form-control"/>')
                        $(":text").focus();
                        $(this).datepicker({
                            format : 'yyyy-mm-dd',
                            language : 'zh-CN',
                            autoclose : true,
                            todayBtn : "linked",
                            todayHighlight : true,
                        }).on('hide', function(ev){
                            $(this).html(ev.format());
                        });
                    }
                }
            }

        })

        /**
         * 正在编辑中的表单失去焦点
         */
        $(this).on("blur", ":text", function(){
            if(typeof $(this).attr("date-type") != 'undefined')
                ;
            else
                $(this).parent().html($(this).val());
        })
        /**
         * 一行数据被选中
         */
        $(this).on("click", ":checkbox[name='id']", function(){
            if($(this).prop("checked") == true) {
                $(this).parent().parent().addClass("active");
            } else {
                $(this).parent().parent().removeClass("active");
            }
        })

        $(this).on("click", ":checkbox[name='ids']", function(){
            if($(this).prop("checked") == true) {
                $(":checkbox[name='id']").prop('checked', true);
                $(target).children().eq(0).children("tr:gt(0)").addClass('active');
            } else {
                $(":checkbox[name='id']").prop('checked', false);
                $(target).children().eq(0).children("tr:gt(0)").removeClass('active');

            }
        })

        /**
         * tooltip初始化
         */
        $(function () {

            $('[data-toggle="popover"]').popover({
                "html" : true,
            });
        })

        /**
         * 添加按钮
         */
        $(this).after('<hr />');
        if(!settings.noAdd)
            $(this).after(' <button class="btn btn-primary add" type="button"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 新增数据</button>')
        if(!settings.noDel)
            $(this).after('<button class="btn btn-danger dels"  type="button"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> 删除选中数据</button> ')
        if(!settings.noSave)
            $(this).after('<button class="btn btn-default saves"  type="button"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"> 保存选中数据</span></button> ')

        /**
         * 添加分页
         */

        $(this).after('<nav aria-label="Page navigation" class="text-right" id="pagination"></nav>');
        function initPagination() {
            $("#pagination").html("");
            if (settings.totalPage > 1) {
                var str = '<ul class="pagination">';
                for (var i = 1; i <= settings.totalPage; i++) {
                    if (i == settings.currentPage || (settings.currentPage == -1 && i == 1))
                        str += '<li class="disabled"><a href="javascript:;">' + i + '</a></li>';
                    else
                        str += '<li><a href="javascript:;">' + i + '</a></li>';
                }
                str += '</ul>';
                $("#pagination").html(str);
            }
        }
        initPagination();

        $("#pagination").on('click', 'li', function(){
            if(!$(this).hasClass('disabled')) {
                var page = $(this).children().eq(0).html();
                $(this).html('<span class="glyphicon glyphicon-refresh"></span>')
                $.get(settings.paginationURL + "?currentPage=" + page + "&fetch=" + settings.fetch,  function(response){
                    if(response.status == true) {
                        settings.data = response.data;
                        settings.currentPage = response.currentPage;
                        settings.totalPage = response.totalPage;
                        initTableData();
                        initPagination();
                    } else {
                        $("#alertInfo").html("请求失败!!!");
                        $("#alertInfo").modal();
                    }
                })
            }
        })
        /**
         * 删除多行数据
         */
        $(".dels").click(function(){
            var ids = new Array();

            $(":checkbox[name='id']").each(function(){
                if($(this).prop('checked')) {
                    ids.push($(this).parent().next().html());
                }
            })
            if(ids.length != 0) {
                $.post(settings.delsURL, {ids:ids}, function(response){
                    if(response.status == true) {
                        for(var i = 0; i < response.ids.length; i++) {
                            $(target).children().eq(0).children().each(function(){
                                if($(this).children().eq(1).html() == response.ids[i])
                                    $(this).remove();
                            })
                        }
                        $("#alertInfo").html("删除成功");
                        $("#alertModal").modal();
                        window.setTimeout('$("#alertModal").modal("hide")', 2000);
                    } else {
                        $("#alertInfo").html("删除失败");
                        $("#alertModal").modal();
                    }
                });
            }
        })

        /**
         * 保存多行数据
         */
        $(".saves").click(function(){
            var infos = new Array();
            $(":checkbox[name='id']").each(function(){
                if($(this).prop('checked')) {
                    var target = $(this).parent().parent();
                    var ele = new Array();
                    var len = $(target).children().length;
                    if(settings.noOperator == false)
                        len--;
                    for(var i = 1; i < len; i++) {
                        if(settings.typeConfig[i-1].edit == false)
                            ele.push($(target).children().eq(i).html())
                        else if(settings.typeConfig[i - 1].type == 'text' || settings.typeConfig[i - 1].type == 'date')
                            ele.push($(target).children().eq(i).html())
                        else if (settings.typeConfig[i - 1].type == 'select') {
                            for(var k = 0; k < settings.typeConfig[i-1].options.length; k++) {
                                if($(target).children().eq(i).html() == settings.typeConfig[i-1].options[k][1])
                                    ele.push(settings.typeConfig[i-1].options[k][0])
                            }
                        } else if(settings.typeConfig[i-1].type == 'textarea') {
                            ele.push(b.decode($(target).children().eq(i).children().eq(0).val()));
                        }
                    }
                    infos.push(ele);
                }
            })
            if(infos.length != 0) {
                $.post(settings.saveURL, {infos: infos}, function (response) {
                    if(response.status == true)
                        $("#alertInfo").html("更新成功!");
                    else
                        $("#alertInfo").html("更新失败!");
                    $("#alertModal").modal();
                    window.setTimeout(function () {
                        $("#alertModal").modal('hide');
                        $.get(settings.paginationURL + "?currentPage=" + settings.currentPage,  function(response){
                            if(response.status == true) {
                                settings.data = response.data;
                                settings.currentPage = response.currentPage;
                                settings.totalPage = response.totalPage;
                                initTableData();
                                initPagination();
                            }
                        })
                    }, 2000);
                })
            }
        })

        /**
         * 添加数据
         */
        $(".add").click(function(){
            $("#addForm").html("");
            for(var i = 1; i < settings.title.length; i++) {
                if(settings.typeConfig[i].type == 'text') {
                    $("#addForm").append('' +
                        '<div class="form-group">' +
                        '<label class="col-sm-4 control-label">' + settings.title[i] + '</label>' +
                        '<div class="col-sm-6">' +
                        '<input type="text" value="" class="form-control"/>' +
                        '</div>' +
                        '</div>')
                } else if(settings.typeConfig[i].type == 'select') {
                    $("#addForm").append('' +
                        '<div class="form-group">' +
                        '<label class="col-sm-4 control-label">' + settings.title[i] + '</label>' +
                        '<div class="col-sm-6">' +
                        '<select class="form-control"></select>'+
                        '</div>' +
                        '</div>');
                    for(var k = 0; k < settings.typeConfig[i].options.length; k++)
                        $("#addForm").children().last().children().eq(1).children().eq(0).append('<option selected value="' + settings.typeConfig[i].options[k][0] + '">' + settings.typeConfig[i].options[k][1] + '</option>');
                } else if(settings.typeConfig[i].type == 'textarea') {
                    $("#addForm").append('' +
                        '<div class="form-group">' +
                        '<label class="col-sm-4 control-label">' + settings.title[i] + '</label>' +
                        '<div class="col-sm-6">' +
                        '<textarea class="form-control" rows="10"></textarea>'+
                        '</div>' +
                        '</div>');
                } else if(settings.typeConfig[i].type == 'date') {
                    $("#addForm").append('' +
                        '<div class="form-group">' +
                        '<label class="col-sm-4 control-label">' + settings.title[i] + '</label>' +
                        '<div class="col-sm-6">' +
                        '<input type="date" value="" class="form-control"/>' +
                        '</div>' +
                        '</div>');
                }
            }
            $("#addModal").modal();
        })

        /**
         * 添加Modal
         */
        $("body").append('' +
            '<div id="alertModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel">' +
            '  <div class="modal-dialog modal-sm" role="document">' +
            '    <div class="modal-content">' +
            '     <div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
            '   提醒'+
            '     </div>' +
            '     <div class="modal-body" >' +
                '<p id="alertInfo" class="text-center text-danger"></p>' +
            '     </div>  '+
            '    </div>' +
            '  </div>' +
            '</div>'+
            '');

        $("body").append('<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel">\n' +
            '    <div class="modal-dialog" role="document">\n' +
            '        <div class="modal-content">\n' +
            '            <div class="modal-header">\n' +
            '                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n' +
            '                <h4 class="modal-title" id="addModalLabel">新增一组数据</h4>\n' +
            '            </div>\n' +
            '            <div class="modal-body">\n' +
            '                <form class="form-horizontal" id="addForm">\n' +
            '\n' +
            '                </form>\n' +
            '            </div>\n' +
            '            <div class="modal-footer">\n' +
            '                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>\n' +
            '                <button type="button" class="btn btn-primary" id="save">保存</button>\n' +
            '                <p class="text-success"></p>'+
            '            </div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '</div>');

        $("body").append('<div class="modal fade" id="longTextModal" tabindex="-1" role="dialog" aria-labelledby="longTextModalLabel">\n' +
            '    <div class="modal-dialog" role="document">\n' +
            '        <div class="modal-content">\n' +
            '            <div class="modal-header">\n' +
            '                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n' +
            '                <h4 class="modal-title">编辑长文本</h4>\n' +
            '            </div>\n' +
            '            <div class="modal-body">\n' +
            '<textarea class="form-control" cols="10" rows="20"></textarea>'+
            '<input type="hidden" id="longTextRow" />' +
            '<input type="hidden" id="longTextCel" />' +
            '            </div>\n' +
            '            <div class="modal-footer">\n' +
            '                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>\n' +
            '                <button type="button" class="btn btn-primary" data-dismiss="modal" id="saveLongText">保存</button>\n' +
            '            </div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '</div>');
        /**
         * addModal的新增按钮
         */
        $("#save").click(function(){
            var info = new Array();
            $("#addForm .text-danger").remove();
            for(var i = 0; i < $("#addForm").children().length; i++) {
                var target = $("#addForm").children().eq(i).children().eq(1).children().eq(0);
                info[i] = $(target).val();
            }
            $.post(settings.addURL, {info:info}, function(response){
                if(response.status == true) {
                    $("#save").next().html("保存成功！")
                    window.setTimeout('$("#addModal").modal("hide"); $("#save").next().html("")', 2000);
                } else {
                    for(var i = 0; i < response.errors.length; i++) {
                        $("#addForm").children().eq(response.errors[i][0]).children().eq(1).append('<p class="text-danger">' + response.errors[i][1] + '</p>');
                    }
                }
            })
        })

        var target = $(this);
        $("#saveLongText").click(function(){
            var str = $("#longTextModal textarea").val().substr(0, 10) + '......<input type="hidden" value="' + b.encode($("#longTextModal textarea").val())+'"/>';
            $(target).children().eq(0).children().eq($("#longTextRow").val()).children().eq(parseInt($("#longTextCel").val()) + 1).html(str);
        })
    };


    function Base64() {

        // private property
        _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

        // public method for encoding
        this.encode = function (input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;
            input = _utf8_encode(input);
            while (i < input.length) {
                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);
                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;
                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }
                output = output +
                    _keyStr.charAt(enc1) + _keyStr.charAt(enc2) +
                    _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
            }
            return output;
        }

        // public method for decoding
        this.decode = function (input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;
            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
            while (i < input.length) {
                enc1 = _keyStr.indexOf(input.charAt(i++));
                enc2 = _keyStr.indexOf(input.charAt(i++));
                enc3 = _keyStr.indexOf(input.charAt(i++));
                enc4 = _keyStr.indexOf(input.charAt(i++));
                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;
                output = output + String.fromCharCode(chr1);
                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }
            }
            output = _utf8_decode(output);
            return output;
        }

        // private method for UTF-8 encoding
        _utf8_encode = function (string) {
            string = string.replace(/\r\n/g,"\n");
            var utftext = "";
            for (var n = 0; n < string.length; n++) {
                var c = string.charCodeAt(n);
                if (c < 128) {
                    utftext += String.fromCharCode(c);
                } else if((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                } else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }
            return utftext;
        }

        // private method for UTF-8 decoding
        _utf8_decode = function (utftext) {
            var string = "";
            var i = 0;
            var c = c1 = c2 = 0;
            while ( i < utftext.length ) {
                c = utftext.charCodeAt(i);
                if (c < 128) {
                    string += String.fromCharCode(c);
                    i++;
                } else if((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i+1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
                } else {
                    c2 = utftext.charCodeAt(i+1);
                    c3 = utftext.charCodeAt(i+2);
                    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }
            }
            return string;
        }
    }
})(jQuery);