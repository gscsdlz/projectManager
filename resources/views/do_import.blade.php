@extends('layout')
@section('main')
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <h1>导入进度记录</h1>
            <hr/>
           @if($status === false)
                <div id="alert" class="alert alert-danger alert-dismissible fade in" role="alert">
                    <h4>{{ $info }}</h4>
                </div>
           @else
               <form class="form-horizontal">
                   <div class="form-group">
                    <div class="col-sm-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" onclick="if($(this).prop('checked')) $('#table').children().eq(0).children().eq(1).hide(); else $('#table').children().eq(0).children().eq(1).show()" value=""/> 数据包含标题
                            </label>
                        </div>
                    </div>
                   </div>
                   <button class="btn btn-danger" type="button">保存</button>
               </form>
                <hr/>
                <table class="table table-bordered" id="table">
                    <tr>
                        <th style="width:10%">编号</th>
                        <th style="width:10%">登记时间</th>
                        <th style="width:10%">员工名称</th>
                        <th style="width:15%">项目名称</th>
                        <th style="width:35%">完成工作</th>
                        <th style="width:10%">计量总工</th>
                        <th style="width:10%">综合总工</th>
                    </tr>
                    @foreach($data as $row)
                        <tr>
                            <td>{{ $row[0] }}</td>
                            <td>{{ $row[1] }}</td>
                            <td>{{ $row[2] }}</td>
                            <td>{{ $row[3] }}</td>
                            <td>{{ $row[4] }}</td>
                            <td>{{ $row[5] }}</td>
                            <td>{{ $row[6] }}</td>
                        </tr>
                    @endforeach
                </table>
           @endif
        </div>
    </div>
@endsection