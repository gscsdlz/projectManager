@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <h1>系统用户管理</h1>
        <hr/>
        <p class="text-danger">新增用户的密码将会被默认修改为123456，新增用户后请重新修改</p>
        <hr/>
        <table id="users">
        </table>
    </div>
</div>
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-labelledby="passwordModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">修改用户密码</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-4">新密码</label>
                        <div class="col-sm-6">
                            <input type="password" id="pass1" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-4">确认密码</label>
                        <div class="col-sm-6">
                            <input type="password" id="pass2" class="form-control" />
                        </div>
                    </div>
                </form>
                </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="change">修改密码</button>
                <p class="text-danger" id="info"></p>
            </div>
        </div>
    </div>
</div>
<script>
    var id;
    $(document).ready(function(){
        $.get("{{ URL('user/show') }}", function(res){
            if(res.status == true) {
                $("#users").dynamicTables({
                    'title': [
                        '编号', '用户名', '密码', '权限', '创建时间', '上次登录时间', '上次登录IP'
                    ],
                    'typeConfig': [
                        {'edit': false},
                        {'type': 'text'},
                        {'edit': false},
                        {
                            'type': 'select',
                            'options': [
                                ['0', '普通用户'],
                                ['1', '管理员'],
                            ]
                        },
                        {'edit': false},
                        {'edit': false},
                        {'edit': false},
                    ],
                    'data': res.data,
                    'saveURL' : "{{ URL('user/update') }}",
                    'delsURL' : "{{ URL('user/dels') }}",
                    'addURL' : "{{ URL('user/add') }}",
                    'noOperator' : false,
                    'operatorHTML' : '<td class="text-danger">' +
                        '<button onclick="changePassword($(this).parent().parent())" class="btn btn-danger" type="button"><span class="glyphicon glyphicon-edit"></span> 修改密码</button>'+
                    '</td>'
                })
            }
        })

        $("#change").click(function(){
            var p1 = $("#pass1").val();
            var p2 = $("#pass2").val();

            $('.form-horizontal').find('.has-error').removeClass('has-error');
            $(".form-horizontal").find('p.text-danger').remove();
            if(p1.length == 0 || p1 != p2) {
                $("#pass1").parent().addClass('has-error');
                $("#pass2").parent().addClass('has-error');
                $("#pass2").after("<p class='text-danger'>两次密码不匹配</p>")
            } else {
                $.post("{{ URL('user/changePass') }}", {pass1:p1, pass2:p2, user_id:id}, function(res){
                    if(res.status == true) {
                        $("#info").html("修改成功!");
                    } else {
                        $("#info").html("修改失败!");
                    }
                })
            }
        })
    })

    function changePassword(target) {
        id = $(target).children().eq(1).html();
        $("#passwordModal").modal();

    }
</script>
@endsection