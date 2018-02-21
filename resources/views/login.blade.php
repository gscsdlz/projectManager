<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>工程项目管理系统</title>
    <link href="{{ URL::asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="{{ URL::asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ URL::asset('js/formCheck.js') }}"></script>
</head>
<body>
<div class="row" style="background: url('{{ URL('image/loginbg.jpg') }}') 0px 0px no-repeat; height:1080px">
    <div class="col-md-4 col-md-offset-4 well" style="margin-top: 400px">
        <form class="form-horizontal">
            <h1 class="text-center">登录</h1>
            <hr/>
            <div class="form-group">
                <label class="control-label col-sm-2">用户名</label>
                <div class="col-sm-8">
                    <input type="text" formCheck-noEmpty formCheck-info="用户名不能为空" value="" class="form-control" placeholder="请输入用户名" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2">密码</label>
                <div class="col-sm-8">
                    <input type="password"  formCheck-noEmpty formCheck-info="密码不能为空" value="" class="form-control" placeholder="请输入密码" />
                </div>
            </div>
        </form>
        <p class="text-danger" id="info"></p>
        <button class="btn btn-primary" style="float:right" type="button" id="go">登录</button>
    </div>
</div>
<script>
    $(document).ready(function () {
        $("#go").click(function(){
            if($.formCheck($("form"))) {
                var username = $(":text").val();
                var password = $(":password").val();
                $.post("{{ URL('login') }}", {username:username, password:password}, function(res){
                    if(res.status == true) {
                        window.location.reload();
                    } else {
                        $("#info").html("用户名或者密码错误");
                    }
                })
            }
        })
    })
</script>
<nav class="navbar navbar-inverse" style="background:rgb(248,248,248); border:0px solid white" role="navigation">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 text-center">
                <h3>工程项目管理系统</h3>
                <table class="table">
                    <tr>
                        <td colspan="3">仅限内部使用</td>
                    </tr>
                    <tr>
                        <td colspan="3">Version 1.0.0 || Developed & Design By gscsdlz</td>

                    </tr>
                </table>
                <h4>执行时间：{{ sprintf("%0.3f", microtime(true) - LARAVEL_START) }} &nbsp;
                    服务器时间: {{ date('Y-m-d H:i:s', time()) }}
                </h4>
            </div>
        </div>
    </div>
</nav>
</body>
</html>