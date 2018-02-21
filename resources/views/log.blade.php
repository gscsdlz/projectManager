@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <h1>操作日志</h1>
        <hr/>
        <table id="logs">
        </table>
    </div>
</div>
<script>
    $(document).ready(function () {

        $.get('{{ URL('log/show?currentPage=1') }}', function (res) {
            if(res.status) {
                $("#logs").dynamicTables({
                    'title': [
                        '编号', '用户名', '调用接口', '请求方式', '发起时间', '发起IP', '用户标识'
                    ],
                    'data': res.data,
                    'noAdd': true,
                    'noSave': true,
                    'noDel': true,
                    'paginationURL': '{{ URL('log/show') }}',
                    'currentPage': res.currentPage,
                    'totalPage': res.totalPage,
                    'typeConfig' : [
                        {'edit' : false},
                        {'edit' : false},
                        {'edit' : false},
                        {'edit' : false},
                        {'edit' : false},
                        {'edit' : false},
                        {'edit' : false},
                        {'edit' : false},
                    ]
                })
            }
        })

    })
</script>
@endsection