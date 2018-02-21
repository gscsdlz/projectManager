@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <h1>项目管理</h1>
        <form class="form-horizontal">
        <div class="form-group">
            <div class="col-sm-2">
                <input type="text" id="str" value="@if(isset($name)){{ $name }}@endif" class="form-control" placeholder="搜索">
            </div>
            <div class="col-ms-2">
                <button class="btn btn-success" type="button" id="search"><span class="glyphicon glyphicon-search"></span></button>
            </div>
        </div>
        </form>
        <hr/>
        <table id="projects">
        </table>
    </div>
</div>
<script>
    $(document).ready(function(){
        @if(isset($name))
            $.post('{{ URL('project/search')}}',{name: "{{ $name }}"}, function(res){
        @else
            $.get('{{ URL('project/get').'?currentPage=1' }}', function(res){
        @endif
                $("#projects").dynamicTables({
            'title' : [
                "编号", "项目名称", "建筑面积", "层数", "檐高", "总造价", "计量总工", "综合总工", "工日合计", "开始", "完成"
            ],
            'data' : res.data,
            'currentPage' : res.currentPage,
            'totalPage' : res.totalPage,
            'saveURL' : '{{ URL('project/update') }}',
            'delsURL' : '{{ URL('project/dels') }}',
            'addURL' : '{{ URL('project/add') }}',
            'paginationURL': '{{ URL('project/get') }}',
            'typeConfig' : [
                {'edit' : false},
                {'type' : 'text'},
                {'type' : 'text'},
                {'type' : 'text'},
                {'type' : 'text'},
                {'type' : 'text'},
                {'type' : 'text'},
                {'type' : 'text'},
                {'type' : 'text'},
                {'type' : 'date'},
                {'type' : 'date'},
            ]
        });
            })

        $("#search").click(function(){
            var name = $("#str").val();
            if(name.length == 0) {
                window.location.href = "{{ URL('project') }}"
            } else {
                window.location.href="{{ URL('project/search?name=') }}"+name
            }
        })
    })
</script>
@endsection