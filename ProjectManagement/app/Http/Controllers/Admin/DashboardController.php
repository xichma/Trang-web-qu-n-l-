<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Todo;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    protected $data = []; // the information we send to the view

    /**
     * Create a new controller instance.
     */
    function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    /**
     * Show the admin dashboard.
     *
     * @return Response
     */
    function index()
    {
        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title
        $this->data['breadcrumbs'] = [
            trans('backpack::crud.admin')     => backpack_url('dashboard'),
            trans('backpack::base.dashboard') => false,
        ];
        $this->data['widgets']['before_content'] = [
            [
                'type'        => 'progress',
                'class'       => 'card text-white bg-primary mb-2',
                'value'       => number_format(backpack_user()->projectMadeByMe()->count() + backpack_user()->projectMadeByMe()->count()),
                'description' => 'Projects',
                'progress'    => 0,
                'hint'        => 'All of projects you joined and made by you',
                'wrapperClass' => 'col-sm-4 col-md-4',
            ],
            [
                'type'        => 'progress',
                'class'       => 'text-white bg-info mb-2',
                'value'       => number_format(backpack_user()->tasks()->count()),
                'description' => 'Tasks',
                'progress'    => number_format(backpack_user()->tasks()->where("status",Task::DONE)->count()),
                'hint'        => 'All tasks you were assigned',
                'wrapperClass' => 'col-sm-4 col-md-4',
            ],
            [
                'type'        => 'progress',
                'class'       => ' text-white bg-success mb-2',
                'value'       => number_format($this->getTodoes()),
                'description' => 'Todoes',
                'progress'    => number_format($this->getTodoesDone()),
                'hint'        => 'All todoes you need to complete',
                'wrapperClass' => 'col-sm-4 col-md-4',
            ],
        ];

        $todoes = $this->getTodoesDoing();
        $this->data["todoes"] = $todoes;

        return view(backpack_view('dashboard'), $this->data);
    }

    private function getTodoes(){
        $num = 0;
        foreach (backpack_user()->tasks as $task){
            $num += $task->todoes()->count();
        }
        return $num;
    }

    private function getTodoesDone(){
        $num = 0;
        foreach (backpack_user()->tasks as $task){
            $num += $task->todoes()->where("status", Todo::DONE)->count();
        }
        return $num;
    }

    private function getTodoesDoing(){
        $todoes = [];
        foreach (backpack_user()->tasks as $task){
            foreach($task->todoes()->where("status", !Todo::DONE)->get() as $todo){
                $todo->priority = $this->getLabel($todo->task);
                $todoes[] = $todo;
            }
        }
        return $todoes;
    }

    private  function getLabel($entry)
    {
        $label = "";
        switch ($entry->priority) {
            case config("prioritize.urgent_and_important"):
                $label = "<span class='label' style='background-color: " . config("prioritize.urgent_and_important_color") . "'>Urgent and Important</span>";
                break;
            case config("prioritize.important_not_urgent"):
                $label = "<span class='label' style='background-color: " . config("prioritize.important_not_urgent_color") . "'>Important not Urgent</span>";
                break;
            case config("prioritize.urgent_not_important"):
                $label = "<span class='label' style='background-color: " . config("prioritize.urgent_not_important_color") . "'>Urgent not Important</span>";
                break;
            case config("prioritize.not_important_not_urgent"):
                $label = "<span class='label' style='background-color: " . config("prioritize.not_important_not_urgent_color") . "'>not Important not Urgent</span>";
                break;
        }
        return $label;
    }
}
