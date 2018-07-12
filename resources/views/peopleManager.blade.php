@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <h1>员工管理</h1>
        <form class="form-horizontal">
            <div class="form-group">
                <div class="col-sm-3">
                    <input type="text" id="str" value="@if(isset($name)){{ $name }}@endif" class="form-control" placeholder="输入首字母进行搜索">
                </div>
                <div class="col-ms-2">
                    <button class="btn btn-success" type="button" id="search"><span class="glyphicon glyphicon-search"></span></button>
                </div>
            </div>
        </form>
        <hr/>

        <table id="people">
        </table>
    </div>
</div>
<script>
    $(document).ready(function(){
        @if(isset($name))
            $.post('{{ URL('people/search')}}',{name: "{{ $name }}"}, function(res){
        @else
            $.get('{{ URL('people/get').'?currentPage=1' }}', function(res) {
            @endif
            $("#people").dynamicTables({
                'title': [
                    "编号", "员工姓名", "所属部门", "创建时间", "上次修改时间", "是否离职"
                ],
                'data': res.data,
                'currentPage': res.currentPage,
                'totalPage': res.totalPage,
                'saveURL': '{{ URL('people/update') }}',
                'delsURL': '{{ URL('people/dels') }}',
                'addURL': '{{ URL('people/add') }}',
                'paginationURL': '{{ URL('people/get') }}',
                'typeConfig': [
                    {'edit': false},
                    {'type': 'text'},
                    {'type': 'text'},
                    {'edit': false},
                    {'edit': false},
                    {
                        'type' : 'select',
                        'options' : [
                            ['1', '已离职'],
                            ['0', '未离职'],
                        ]
                    }
                ]
            });
        })

        $("#search").click(function(){
            var name = $("#str").val();
            if(name.length == 0) {
                window.location.href = "{{ URL('people') }}"
            } else {
                window.location.href="{{ URL('people/search?name=') }}"+name
            }
        })
    })
</script>
@endsection