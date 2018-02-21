@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <h1>导入导出</h1>
        <hr/>
        <button class="btn btn-primary" type="button">导入员工数据</button>
        <button class="btn btn-primary" type="button">导入项目数据</button>
        <button class="btn btn-primary" type="button">导入进度数据</button>
        <hr/>
        <button class="btn btn-default" type="button" onclick="window.location.href='{{ URL("export/people") }}'">下载员工数据</button>
        <button class="btn btn-default" type="button">下载项目数据</button>
        <button class="btn btn-default" type="button" onclick="window.location.href='{{ URL("export/search?project_id=0&member_id=0&stime=0&etime=0") }}'">下载进度数据</button>
        <hr/>
    </div>
</div>
@endsection