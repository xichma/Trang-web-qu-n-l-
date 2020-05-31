<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return redirect('/admin');
});

Route::group([
    "prefix" => "api",
    "namespace" => "Api",
    "as" => "api."
],function(){
    Route::get("users",[
        "uses" => "UserController@index",
        "as"   => "user.index"
    ]);
    Route::get("users/assigned-to/{id}",[
        "uses" => "UserController@assignedTo",
        "as"   => "user.assignedTo"
    ]);
    Route::get("users/id",[
        "uses" => "UserController@show",
        "as"   => "user.show"
    ]);
    Route::get('project', [
        "uses" => "ProjectController@index",
        "as"   => "project.index"
    ]);
    Route::get('task/{id}', [
        "uses" => "TaskController@updateStatus",
        "as"   => "task.updateStatus"
    ]);
    Route::get('todo/{id}', [
        "uses" => "TodoController@updateStatus",
        "as"   => "todo.updateStatus"
    ]);
});

