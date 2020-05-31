<?php

namespace App\Http\Controllers\Api;

use Alert;
use App\Http\Controllers\Controller;
use App\Models\Task;

class TaskController extends Controller
{
    function updateStatus($id){
        $task = Task::findOrFail($id);
        $task->status = !$task->status;
        $task->save();
        Alert::success(trans('backpack::crud.update_success'))->flash();
        return redirect()->back();
    }
}
