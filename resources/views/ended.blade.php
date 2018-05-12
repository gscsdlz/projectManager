@extends('layout')
@section('main')
<div class="row">
    <h1 class="text-center">存档项目进度录入</h1>
    <hr/>
    <div class="col-md-2 col-md-offset-1">
        <div class="list-group">
            <a href="#" class="list-group-item disabled active" id="projectList">项目列表</a>
            <a href="javascript:;" class="list-group-item">
                <button id="prevPage" class="btn btn-primary" type="button"><span class="glyphicon glyphicon-arrow-left"></span></button>
                <button id="nextPage" style="float:right" class="btn btn-primary" type="button"><span class="glyphicon glyphicon-arrow-right"></span></button>
            </a>

        </div>
    </div>
    <div class="col-md-8">
        <div class="panel panel-default">
            @if(isset($project_id))
            <div class="panel-heading">
                <p>项目名称：{{ $project_name }}</p>
            </div>
            <div class="panel-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-2 col-sm-offset-2">时间</label>
                        <div class="col-sm-3">
                            <input class="form-control" type="text" value="{{ date('Y-m-d') }}" id="datetime"/>
                        </div>
                    </div>
                    <hr/>
                    <div class="form-group">
                        <label class="control-label col-sm-1">员工1</label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="输入首字母检索" aria-describedby="basic-addon2">
                                <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" placeholder="计量总工">
                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" placeholder="综合总工">
                        </div>
                        <div class="col-sm-4">
                            <textarea class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="panel-footer text-right">
                <button class="btn btn-default" type="button" id="add">添加一组记录</button>
                <button class="btn btn-primary" type="button" id="save">保存</button>
            </div>
            @else
                <div class="panel-heading">
                    <h3 class="text-center">请先从左侧选择项目</h3>
                </div>
            @endif

        </div>
    </div>
</div>
<script>
    var data = new Array();
    var page = 1;
    $(document).ready(function(){
        $("#add").click(function () {
            var id = $(this).parent().prev().children().eq(0).children().last().children().eq(0).html();
            id = "员工" + (parseInt(id.substr(2, id.length - 2)) + 1);
            $(".form-horizontal").append('<div class="form-group">\n' +
                '                        <label class="control-label col-sm-1">'+id+'</label>\n' +
                '                        <div class="col-sm-3">\n' +
                '                            <div class="input-group">\n' +
                '                                <input type="text" class="form-control" placeholder="输入首字母检索" aria-describedby="basic-addon2">\n' +
                '                                <span class="input-group-addon"><span class="glyphicon glyphicon-menu-down"></span></span>\n' +
                '                            </div>\n' +
                '                        </div>\n' +
                '                        <div class="col-sm-2">\n' +
                '                            <input type="text" class="form-control" placeholder="计量总工">\n' +
                '                        </div>\n' +
                '                        <div class="col-sm-2">\n' +
                '                            <input type="text" class="form-control" placeholder="综合总工">\n' +
                '                        </div>\n' +
                '                        <div class="col-sm-4">\n' +
                '                            <textarea class="form-control" rows="2"></textarea>\n' +
                '                        </div>\n' +
                '                    </div>')
        })
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
        $("#datetime").datepicker({
            format : 'yyyy-mm-dd',
            language : 'zh-CN',
            autoclose : true,
            todayBtn : "linked",
            todayHighlight : true,
        })

        $(".form-horizontal").on("click",".input-group-addon", function () {
            if($(this).children().eq(0).hasClass('glyphicon-search')) {
                var key = $(this).prev().val();
                key = key.toUpperCase();
                str = '<select class="form-control">';
                for (var i = 0; i < data.length; i++) {
                    if (data[i][2].indexOf(key) != -1 || key.length == 0) {
                        str += '<option value="' + data[i][1] + '">' + data[i][0] + '</option>';
                    }
                }
                str += '</select>'
                str += '<span class="input-group-addon"><span class="glyphicon glyphicon-trash text-danger"></span></span>'
                str += '<input type="hidden" value="' + key + '"/>'
                $(this).parent().html(str);
            } else {
                var key = $(this).next().val();
                str = '<input type="text" value="'+key+'" class="form-control" placeholder="输入首字母检索" aria-describedby="basic-addon2">\n' +
                    ' <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>\n'

                $(this).parent().html(str);
            }
        })

        getList()
        $.get("{{ URL('people/getList') }}", function(res){
            if(res.status == true) {
                data = res.data;
            }
        })

        $("#prevPage").click(function(){
            page = page - 1;
            page = page < 1 ? 1 : page;
            getList();
        })

        $("#nextPage").click(function(){
            page = parseInt(page) + 1;
            getList();
        })

        $("#projectList").parent().on("click", "[data-id]", function(){
            var id = $(this).attr("data-id");
            window.location.href="{{ URL('ended') }}" + "/" + id;
        })
        @if(isset($project_id))
        $("#save").click(function(){

            if($(this).html() == "保存") {
                $("form").find(".has-error").removeClass('has-error');
                $("form").find("p.text-danger").remove();

                var date = $("#datetime").val();
                if (date.length == 0) {
                    $(this).parent().parent().addClass('has-error');
                }
                var data = new Array();
                $("form").children(".form-group").each(function (index, target) {
                    if (index > 0) { //避开时间日期
                        var tmp = new Array();
                        var t = $(this).children().eq(1).children().eq(0).children().eq(0);
                        var total1 = $(this).children().eq(2).children().eq(0).val();
                        var total2 = $(this).children().eq(3).children().eq(0).val();
                        var content = $(this).children().eq(4).children().eq(0).val();

                        {

                            if ($(t).attr("type") == "text" || $(t).children().length == 0) {
                                $(t).parent().after('<p class="text-danger">还未选择用户</p>')
                            } else {
                                tmp.push($(t).val());
                            }
                            if (total1 == 0 && total2 == 0) {
                                $(this).children().eq(2).addClass('has-error');
                                $(this).children().eq(3).addClass('has-error');
                            } else if(total1 != 0 && total2 != 0) {
                                $(this).children().eq(2).addClass('has-error');
                                $(this).children().eq(3).addClass('has-error');
                                $(this).children().eq(2).append("<p class='text-danger'>计量总工和综合总工只能填写一个</p>")
                            } else {
                                tmp.push(total1);
                                tmp.push(total2);
                            }
                            if (content.length == 0) {
                                $(this).children().eq(4).addClass('has-error');
                            } else {
                                tmp.push(content);
                            }
                            if (tmp.length == 4)
                                data.push(tmp);
                        }
                    }
                })

                if (data.length == $("form").children(".form-group").length - 1 && $("form").find(".has-error").length == 0 && $("form").find("p.text-danger").length == 0) {
                    $("#save").html("保存中...请稍后");
                    $.post("{{ URL('record/insert') }}", {
                        project_id: "{{ $project_id }}",
                        data: data,
                        date: date
                    }, function (res) {
                        if(res.status == true) {
                            alert("保存成功");
                            window.location.reload();
                        }
                    })
                }
            }
        })
        @endif
    })

    function getList() {
        $("[data-id]").remove();
        $.get("{{ URL('project/getEndList?page=') }}" + page, function (res) {
            if(res.status == true) {
                var str = '';
                for(var i = 0; i < res.data.length; i++) {
                    str += '<a href="#" class="list-group-item" data-id="'+res.data[i]['project_id']+'">'+res.data[i]['project_name']+'</a>'
                }
                $("#projectList").after(str);
                page = res.page;
            }
        })
    }

</script>
@endsection
