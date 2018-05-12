<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'normalUser'], function() {
    Route::get('/', 'IndexController@index');

    /**
     * 查询记录，新增记录
     */
    Route::get('insert/{project_id?}', 'RecordController@index');
    Route::get('ended/{project_id?}', 'RecordController@endedIndex');
    Route::post('record/insert', 'RecordController@insert');
    Route::get('record/search', 'RecordController@search');
    Route::post('record/update', 'RecordController@update');
    Route::post('record/del', 'RecordController@del');
    Route::post('upload/record', 'RecordController@import');
    Route::get('search', 'RecordController@searchPage');
    Route::get('export/search', 'RecordController@export');
    /**
     * 项目管理 仅允许查看列表
     */
    Route::group(['middleware' => 'adminUser'], function(){
        Route::get('project', 'ProjectController@index');
        Route::get('project/get', 'ProjectController@get');
        Route::post('project/dels', 'ProjectController@dels');
        Route::post('project/update', 'ProjectController@save');
        Route::post('project/add', 'ProjectController@add');
        Route::get('project/search', 'ProjectController@search');
        Route::post('project/search', 'ProjectController@search');
        Route::get('export/project', 'ProjectController@export');
        Route::post('upload/project', 'ProjectController@import');

    });
    Route::get('project/getList', 'ProjectController@getList');
    Route::get('project/getEndList', 'ProjectController@getEndList');
    Route::get('project/getAllList', 'ProjectController@getAllList');

    /**
     * 员工管理 仅允许查看列表
     */
    Route::group(['middleware' => 'adminUser'], function(){
        Route::get('people', 'PeopleController@index');
        Route::get('people/get', 'PeopleController@get');
        Route::post('people/dels', 'PeopleController@dels');
        Route::post('people/update', 'PeopleController@save');
        Route::post('people/add', 'PeopleController@add');
        Route::get('people/search', 'PeopleController@search');
        Route::post('people/search', 'PeopleController@search');
        Route::get('export/people', 'PeopleController@export');
        Route::post('upload/people', 'PeopleController@import');
    });
    Route::get('people/getList', 'PeopleController@getList');

    Route::get('import', 'IndexController@import');

    /**
     * 用户管理 普通用户仅允许查看自己 修改自己的密码
     */
    Route::get('user', 'UserController@index');
    Route::get('user/show', 'UserController@show');
    Route::post('user/changePass', 'UserController@changePass');

    Route::group(['middleware' => 'adminUser'], function (){
        Route::post('user/update', 'UserController@save');
        Route::post('user/dels', 'UserController@dels');
        Route::post('user/add', 'UserController@add');
    });

    /**
     * 日志 普通用户不查看日志
     */
    Route::group(['middleware' => 'adminUser'], function () {
        Route::get('log', 'LogController@index');
        Route::get('log/show', 'LogController@show');
    });

});
Route::any('logout', 'UserController@logout');
Route::any('login', 'UserController@login');
