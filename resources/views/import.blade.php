@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <h1>数据导入与导出</h1>
        <hr/>
        <button class="btn btn-primary" type="button">导入参与人员数据</button>
        <button class="btn btn-primary" type="button">导入项目数据</button>
        <button class="btn btn-primary" type="button">导入进度数据</button>
        <hr/>
        <button class="btn btn-default" type="button" onclick="window.location.href='{{ URL("export/people") }}'">下载参与人员数据</button>
        <button class="btn btn-default" type="button">下载项目数据</button>
        <button class="btn btn-default" type="button">下载进度数据</button>
        <hr/>
    </div>
</div>
@endsection