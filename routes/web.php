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
    Route::get('insert/{project_id?}', 'RecordController@index');
    Route::post('record/insert', 'RecordController@insert');
    Route::get('record/search', 'RecordController@search');

    Route::get('project', 'ProjectController@index');
    Route::get('project/get', 'ProjectController@get');
    Route::post('project/dels', 'ProjectController@dels');
    Route::post('project/update', 'ProjectController@save');
    Route::post('project/add', 'ProjectController@add');
    Route::get('project/search', 'ProjectController@search');
    Route::post('project/search', 'ProjectController@search');

    Route::get('project/getList', 'ProjectController@getList');
    Route::get('project/getAllList', 'ProjectController@getAllList');

    Route::get('people', 'PeopleController@index');
    Route::get('people/get', 'PeopleController@get');
    Route::post('people/dels', 'PeopleController@dels');
    Route::post('people/update', 'PeopleController@save');
    Route::post('people/add', 'PeopleController@add');
    Route::get('people/search', 'PeopleController@search');
    Route::post('people/search', 'PeopleController@search');
    Route::get('people/getList', 'PeopleController@getList');

    Route::get('search', 'RecordController@searchPage');
    Route::get('import', 'IndexController@import');

    Route::get('export/people', 'PeopleController@export');

    Route::get('user', 'UserController@index');
    Route::get('user/show', 'UserController@show');
    Route::post('user/dels', 'UserController@dels');
    Route::post('user/update', 'UserController@save');
    Route::post('user/add', 'UserController@add');
    Route::post('user/changePass', 'UserController@changePass');

    Route::get('log', 'LogController@index');
    Route::get('log/show', 'LogController@show');

});
Route::any('logout', 'UserController@logout');
Route::any('login', 'UserController@login');
