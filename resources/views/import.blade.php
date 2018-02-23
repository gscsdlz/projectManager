@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <h1>导入导出</h1>
        <hr/>
        @if(Session::get('privilege') == 1)
        <button class="btn btn-primary" type="button">导入员工数据</button>
        <button class="btn btn-primary" type="button">导入项目数据</button>
        @endif
        <button class="btn btn-primary" type="button">导入进度数据</button>
        <hr/>
        <form class="form-horizontal well" role="form" method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
                <div class="col-sm-12">
                    <ul>
                        <li>此处上传进度数据</li>
                        <li>仅能使用xls或者xlsx，由WPS或者Excel生成</li>
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                    <label>请选择xls或者xlsx文件：<input class="form-control" type="file" name="file" id="uploadFile" /></label>
                </div>
            </div>
            <div id="alert" class="alert alert-danger alert-dismissible fade in" role="alert">
                <h4 id="info"></h4>
            </div>
        </form>
        <hr/>

        @if(Session::get('privilege') == 1)
        <button class="btn btn-default" type="button" onclick="window.location.href='{{ URL("export/people") }}'">下载员工数据</button>
        <button class="btn btn-default" type="button" onclick="window.location.href='{{ URL("export/project") }}'">下载项目数据</button>
        @endif
        <button class="btn btn-default" type="button" onclick="window.location.href='{{ URL("export/search?project_id=0&member_id=0&stime=0&etime=0") }}'">下载进度数据</button>
        <hr/>
    </div>
</div>

<script>
    $(document).ready(function () {
        $("#alert").hide();

        $("#uploadFile").AjaxFileUpload({
            action : '{{ URL('upload/record') }}',
            onComplete : function(filename, response) {
                response = eval("("+ /\{.*\}/.exec(response) + ")");
                if(response.status == true) {
                    window.location.href = '{{ URL('import/record?_path=') }}' + response.path;
                } else {
                    $("#info").html(response.info);
                    $("#alert").show();
                }
            }
        })
    })
</script>
@endsection