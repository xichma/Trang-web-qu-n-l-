<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request){
        $search_term = $request->input('q');
        $page = $request->input('page');

        if ($search_term)
        {
            $results = User::where('email', '=', $search_term)->paginate(10);
        }
        else
        {
            $results = User::paginate(10);
        }

        return $results;
    }

    public function show($id){
        return User::find($id);
    }

    public function assignedTo(Request $request, $id){
        $search_term = $request->input('q');
        $page = $request->input('page');
        $results = Project::find($id)->users()->where('name', 'like', "%".$search_term."%")->paginate(10);
        return $results;
    }
}
