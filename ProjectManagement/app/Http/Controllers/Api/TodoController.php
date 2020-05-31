<?php

namespace App\Http\Controllers\Api;

use Alert;
use App\Http\Controllers\Controller;
use App\Models\Todo;

class TodoController extends Controller
{
    function updateStatus($id){
        $todo = Todo::findOrFail($id);
        $todo->status = !$todo->status;
        $todo->save();
        Alert::success(trans('backpack::crud.update_success'))->flash();
        return redirect()->back();
    }
}
