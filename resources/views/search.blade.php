@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-5 col-md-offset-4 well">
        <form class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2">姓名</label>
                <div class="col-sm-8" id="fetchMember">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="输入首字母检索" aria-describedby="basic-addon2">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2">项目名称</label>
                <div class="col-sm-8" id="fetchProject">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="输入首字母检索" aria-describedby="basic-addon2">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2">登记时间</label>
                <div class="col-sm-3">
                    <input class="form-control" type="text" id="time1" value="{{ date("Y-m-d") }}"/>
                </div>
                <div class="col-sm-3">
                    <input class="form-control" type="text" id="time2" value="{{ date("Y-m-d") }}"/>
                </div>
                <div class="col-sm-4">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" onclick="if($(this).prop('checked')) {$('#time1').attr('disabled', true);$('#time2').attr('disabled', true); }else {$('#time1').attr('disabled', false); $('#time2').attr('disabled', false);}"> 查询所有时间段
                        </label>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary" type="button" id="search">搜索</button>
            <button style="float: right" class="btn btn-default" type="button" id="export">导出当前搜索结果</button>
        </form>
    </div>
</div>
<hr/>
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <h3>搜索结果，共计<span id="resLen">0</span>条</h3>
        <nav aria-label="Page navigation" class="text-right">
            <ul class="pagination">
            </ul>
        </nav>
        <table class="table table-bordered" id="table">
            <tr>
                <th style="width:5%">编号</th>
                <th style="width:10%">项目名称</th>
                <th style="width:10%">参与人员名称</th>
                <th style="width:10%">综合总工</th>
                <th style="width:10%">计量总工</th>
                <th style="width:30%">完成工作</th>
                <th style="width:10%">登记时间</th>
                <th style="width:15%">操作</th>
            </tr>
        </table>
    </div>
</div>
<script>
    var memberData = new Array();
    var projectData = new Array();
    var res = new Array();
    var page = 1;
    var pms = 15;

    var member_id = 0;
    var project_id = 0;
    var stime = 0;
    var etime = 0;

    $(document).ready(function(){
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
        $("#time1").datepicker({
            format : 'yyyy-mm-dd',
            language : 'zh-CN',
            autoclose : true,
            todayBtn : "linked",
            todayHighlight : true,
        })
        $("#time2").datepicker({
            format : 'yyyy-mm-dd',
            language : 'zh-CN',
            autoclose : true,
            todayBtn : "linked",
            todayHighlight : true,
        })

        $.get("{{ URL('people/getList') }}", function(res){
            if(res.status == true) {
                memberData = res.data;
            }
        })

        $.get("{{ URL("project/getAllList") }}", function(res){
            if(res.status == true) {
                projectData = res.data;
            }
        })

        if($(":checkbox").prop('checked')) {
            $('#time1').attr('disabled', true);
            $('#time2').attr('disabled', true);
        } else {
            $('#time1').attr('disabled', false);
            $('#time2').attr('disabled', false);
        }

        $("#fetchMember").on("click",".input-group-addon", function () {
            if($(this).children().eq(0).hasClass('glyphicon-search')) {
                var key = $(this).prev().val();
                key = key.toUpperCase();
                str = '<select class="form-control">';
                for (var i = 0; i < memberData.length; i++) {
                    if (memberData[i][2].indexOf(key) != -1 || key.length == 0) {
                        str += '<option value="' + memberData[i][1] + '">' + memberData[i][0] + '</option>';
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

        $("#fetchProject").on("click",".input-group-addon", function () {
            if($(this).children().eq(0).hasClass('glyphicon-search')) {
                var key = $(this).prev().val();
                key = key.toUpperCase();
                str = '<select class="form-control">';
                for (var i = 0; i < projectData.length; i++) {
                    if (projectData[i][2].indexOf(key) != -1 || key.length == 0) {
                        str += '<option value="' + projectData[i][1] + '">' + projectData[i][0] + '</option>';
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

        $("#search").click(function(){
            $("form").find("p.text-success").remove();
            $("form").find("p.text-danger").remove();
            $("form").find(".has-error").removeClass("has-error");

            member_id = 0;
            if($("#fetchMember").children().eq(0).children().eq(0).attr("type") == "text") {
                $("#fetchMember").append('<p class="text-success">姓名未选择</p>')
            } else {
                member_id = $("#fetchMember").find("select").val();
            }

            project_id = 0;
            if($("#fetchProject").children().eq(0).children().eq(0).attr("type") == "text") {
                $("#fetchProject").append('<p class="text-success">项目未选择</p>')
            } else {
                project_id = $("#fetchProject").find("select").val();
            }

            stime = 0;
            etime = 0;
            if($(":checkbox").prop("checked") == false) {
                stime = $("#time1").val();
                etime = $("#time2").val();
                if (/^\d{4}-\d{2}-\d{2}$/.test(stime) == false) {
                    $("#time1").parent().addClass('has-error');
                }
                if (/^\d{4}-\d{2}-\d{2}$/.test(etime) == false) {
                    $("#time2").parent().addClass('has-error');
                }

                stime = Date.parse(new Date(stime + " 00:00:00")) / 1000;
                etime = Date.parse(new Date(etime + " 00:00:00")) / 1000;

                if (etime < stime) {
                    $("#time2").parent().append('<p class="text-danger">结束时间不能超过开始时间</p>')
                }
            }
            if($("form").find("p.text-danger").length == 0 && $("form").find(".has-error").length == 0) {
                $("#search").html("搜索中请稍后...")

                $.get("{{ URL('record/search?')  }}" + "member_id=" + member_id + "&project_id=" + project_id + "&stime=" + stime + "&etime=" + etime,
                    function (response) {
                        if(response.status == true) {
                            res = response.res;
                            page = 1;
                            update_table();
                            $("#search").html("搜索")
                        } else {
                            $("#search").html(response.info);
                        }
                    })
            }
        })

        function update_table()
        {
            var len = res.length;
            $("#resLen").html(len);
            //pagination
            if(len > pms) {
                $(".pagination").html("");
                for(var i = 1; i <= (len - 1) / pms + 1; i++){
                    if(page == i)
                        $(".pagination").append('<li class="active"><a href="javascript:;">'+i+'</a></li>');
                    else
                        $(".pagination").append('<li><a href="javascript:;">'+i+'</a></li>');
                }
                $(".pagination").on('click', 'a', function(){
                    if(!$(this).parent().hasClass('active')) {
                        $(this).parent().addClass('active');
                        page = $(this).html();
                        update_table();
                    }
                })
            } else {
                $(".pagination").html("");
            }

            $("#table").children().eq(0).children(":gt(0)").remove();
            var date = new Date()

            for(var i = (page - 1) * pms; i < page * pms && i < len; i++) {

                date.setTime(res[i].record_time * 1000);

                $("#table").children().eq(0).append('<tr>' +
                    '<td>'+res[i].record_id+'</td>' +
                    '<td>'+res[i].project_name+'</td>' +
                    '<td>'+res[i].member_name+'</td>' +
                    '<td>'+res[i].project_total1+'</td>' +
                    '<td>'+res[i].project_total2+'</td>' +
                    '<td>'+res[i].content+'</td>' +
                    '<td>'+date.getFullYear() + '-' + ( date.getMonth() + 1) + '-' + date.getDate()+'</td>' +
                    '<td></td>' +
                    '</tr>')
            }
        }
    })
</script>
@endsection