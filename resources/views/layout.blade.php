<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>工程项目管理系统</title>
    <link href="{{ URL::asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet">
    <script src="{{ URL::asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ URL::asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ URL::asset('js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ URL::asset('js/dynamicTables.js') }}"></script>
    <script src="{{ URL::asset('js/formCheck.js') }}"></script>
    <script src="{{ URL::asset('js/AjaxFileUpload.js') }}"></script>

</head>
<body>
<nav class="navbar navbar-inverse">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">工程项目管理系统</a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">

                <li @if(isset($menu) && $menu == 'index')class="active"@endif><a href="{{ URL('/') }}">首页</a></li>
                @if(Session::get('privilege') == 1)
                    <li @if(isset($menu) && $menu == 'projectManager')class="active"@endif><a href="{{ URL('project') }}">项目管理</a></li>
                    <li @if(isset($menu) && $menu == 'peopleManager')class="active"@endif><a href="{{ URL('people') }}">员工管理</a></li>
                @endif
                <li @if(isset($menu) && $menu == 'search')class="active"@endif><a href="{{ URL('search') }}">查询</a></li>
                <li @if(isset($menu) && $menu == 'insert')class="active"@endif><a href="{{  URL('insert') }}">进度录入</a></li>
                <li @if(isset($menu) && $menu == 'import')class="active"@endif><a href="{{ URL('import') }}">导入导出</a></li>
                <li @if(isset($menu) && $menu == 'userManager')class="active"@endif><a href="{{ URL('user') }}">用户管理</a></li>
                @if(Session::get('privilege') == 1)
                    <li @if(isset($menu) && $menu == 'logManager')class="active"@endif><a href="{{ URL('log') }}">操作日志</a></li>
                @endif
                <li><a href="{{ URL('file/工程项目管理软件.pdf') }}">操作手册</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{ URL('logout') }}">{{ Session::get('username') }} / 退出登录</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
@yield('main')
<hr/>
<nav class="navbar" style="border:0px solid white" role="navigation">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 text-center">

                <h3>工程项目管理系统</h3>
                <table class="table">
                    <tr>
                        <td colspan="3">仅限内部使用</td>
                    </tr>
                    <tr>
                        <td colspan="3">Version 1.0.1 || Developed & Design By gscsdlz</td>

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