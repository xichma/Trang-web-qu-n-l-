<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $search_term = $request->input('q');

        if ($search_term)
        {
            $results = Project::where('name', 'LIKE', '%'.$search_term.'%')->where("created_by",Auth::id())->paginate(10);
        }
        else
        {
            $results = Project::where("created_by",Auth::id())->paginate(10);
        }

        return $results;
    }
}
