<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
     * @return \Illuminate\Http\Response
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
                'value'       => '11.456',
                'description' => 'Registered users.',
                'progress'    => 57, // integer
                'hint'        => '8544 more until next milestone.',
                'wrapperClass' => 'col-sm-3 col-md-3',
            ],
            [
                'type'        => 'progress',
                'class'       => ' text-white bg-info mb-2',
                'value'       => '11.456',
                'description' => 'Registered users.',
                'progress'    => 57, // integer
                'hint'        => '8544 more until next milestone.',
                'wrapperClass' => 'col-sm-3 col-md-3',
            ],
            [
                'type'        => 'progress',
                'class'       => 'card text-white bg-primary mb-2',
                'value'       => '11.456',
                'description' => 'Registered users.',
                'progress'    => 57, // integer
                'hint'        => '8544 more until next milestone.',
                'wrapperClass' => 'col-sm-3 col-md-3',
            ],
            [
                'type'        => 'progress',
                'class'       => ' text-white bg-success mb-2',
                'value'       => '11.456',
                'description' => 'Registered users.',
                'progress'    => 57, // integer
                'hint'        => '8544 more until next milestone.',
                'wrapperClass' => 'col-sm-3 col-md-3',
            ],
        ];

        return view(backpack_view('dashboard'), $this->data);
    }
}
