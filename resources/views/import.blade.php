@extends('layout')
@section('main')
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <h1>导入导出</h1>
        <hr/>
        @if(Session::get('privilege') == 1)
        <button class="btn btn-primary" type="button" onclick="$('form').hide(); $('form').eq(0).show()">导入员工数据</button>
        <button class="btn btn-primary" type="button" onclick="$('form').hide(); $('form').eq(1).show()">导入项目数据</button>
        @endif
        <button class="btn btn-primary" type="button" onclick="$('form').hide(); $('form').eq(2).show()">导入进度数据</button>
        <hr/>
        <form class="form-horizontal well" role="form" method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
                <div class="col-sm-12">
                    <ul>
                        <li>此处上传员工数据</li>
                        <li>仅能使用xls或者xlsx，由WPS或者Excel生成</li>
                        <li>文件第一行即为数据，不能包含标题和表格头</li>
                        <li>进度数据只有3列，不能多，也不能少，所有列不能为空白</li>
                        <li><table class="table table-bordered"><tr>
                                    <th>编号</th>
                                    <th>姓名</th>
                                    <th>部门名称</th>
                                </tr></table>
                        </li>
                        <li>编号并不写入数据库，可以自定义，但是不能缺少这一列</li>
                        <li>员工姓名不能重复，如果有同名可以使用数字1、2区别或者手机尾号等方式，如果同名将会更新其部门名称</li>
                        <li>范例：</li>
                        <li><table class="table table-bordered"><tr>
                                    <td>1</td>
                                    <td>张三</td>
                                    <td>建筑</td>
                                </tr></table>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                    <label>请选择xls或者xlsx文件：<input class="form-control" type="file" name="file" id="uploadPeople" /></label>
                </div>
            </div>
        </form>
        <form class="form-horizontal well" role="form" method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
                <div class="col-sm-12">
                    <ul class="text-danger">
                        <li>此处上传项目数据</li>
                        <li>仅能使用xls或者xlsx，由WPS或者Excel生成</li>
                        <li>文件第一行即为数据，不能包含标题和表格头</li>
                        <li>进度数据只有7列，不能多，也不能少，所有列不能为空白</li>
                        <li><table class="table table-bordered"><tr>
                                    <th>编号</th>
                                    <th>项目名称</th>
                                    <th>建筑面积</th>
                                    <th>层数</th>
                                    <th>檐高</th>
                                    <th>总造价</th>
                                    <th>开始时间</th>
                                </tr></table>
                        </li>
                        <li>编号并不写入数据库，可以自定义，但是不能缺少这一列</li>
                        <li>以上数据顺序不可以交换, 开始时间格式为2018-02-21或者2018/02/21或者2018.02.21</li>
                        <li>项目名称在没有的情况下会重建，如果有会使用文件中的数据更新</li>
                        <li>范例：</li>
                        <li><table class="table table-bordered"><tr>
                                    <td>1</td>
                                    <td>鸟巢</td>
                                    <td>1000平方</td>
                                    <td>5</td>
                                    <td>无</td>
                                    <td>100000000</td>
                                    <td>2018-01-23</td>
                                </tr></table>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                    <label>请选择xls或者xlsx文件：<input class="form-control" type="file" name="file" id="uploadProject" /></label>
                </div>
            </div>
        </form>
        <form class="form-horizontal well" role="form" method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
                <div class="col-sm-12">
                    <ul class="text-danger">
                        <li>此处上传进度数据</li>
                        <li>仅能使用xls或者xlsx，由WPS或者Excel生成</li>
                        <li>文件第一行即为数据，不能包含标题和表格头</li>
                        <li>进度数据只有7列，不能多，也不能少，所有列不能为空白</li>
                        <li><table class="table table-bordered"><tr>
                                    <th>编号</th>
                                    <th>登记时间</th>
                                    <th>员工名称</th>
                                    <th>项目名称</th>
                                    <th>工作内容</th>
                                    <th>计量总工</th>
                                    <th>综合总工</th>
                            </tr></table>
                        </li>
                        <li>编号并不写入数据库，可以自定义，但是不能缺少这一列</li>
                        <li>以上数据顺序不可以交换, 登记时间格式为2018-02-21或者2018/02/21或者2018.02.21</li>
                        <li>员工名称和项目名称在没有的情况下会重建</li>
                        <li>计量总工和综合总工，不能同时填写，填写其中一个时，另一个置0</li>
                        <li>范例：</li>
                        <li><table class="table table-bordered"><tr>
                                    <td>1</td>
                                    <td>2018-01-23</td>
                                    <td>张三</td>
                                    <td>李四的项目</td>
                                    <td>样例内容</td>
                                    <td>1.0</td>
                                    <td>0.0</td>
                                </tr></table>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                    <label>请选择xls或者xlsx文件：<input class="form-control" type="file" name="file" id="uploadRecord" /></label>
                </div>
            </div>
        </form>
        <div id="alert" class="alert alert-danger alert-dismissible fade in" role="alert">
            <h4 id="info"></h4>
        </div>
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
        $("form").hide()
        $("#alert").hide();

        $("#uploadRecord").AjaxFileUpload({
            action : '{{ URL('upload/record') }}',
            onComplete : function(filename, response) {
                response = eval("("+ /\{.*\}/.exec(response) + ")");
                if(response.status == true) {
                    $("#info").html(response.info);
                    $("#alert").show();
                } else {
                    $("#info").html(response.info);
                    $("#alert").show();
                }
            }
        })
        $("#uploadPeople").AjaxFileUpload({
            action : '{{ URL('upload/people') }}',
            onComplete : function(filename, response) {
                response = eval("("+ /\{.*\}/.exec(response) + ")");
                if(response.status == true) {
                    $("#info").html(response.info);
                    $("#alert").show();
                } else {
                    $("#info").html(response.info);
                    $("#alert").show();
                }
            }
        })
        $("#uploadProject").AjaxFileUpload({
            action : '{{ URL('upload/project') }}',
            onComplete : function(filename, response) {
                response = eval("("+ /\{.*\}/.exec(response) + ")");
                if(response.status == true) {
                    $("#info").html(response.info);
                    $("#alert").show();
                } else {
                    $("#info").html(response.info);
                    $("#alert").show();
                }
            }
        })
    })
</script>
@endsection