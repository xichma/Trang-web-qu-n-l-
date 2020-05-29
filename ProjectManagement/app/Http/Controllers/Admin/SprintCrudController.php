<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SprintRequest;
use App\Models\Feature;
use App\Models\Sprint;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SprintCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SprintCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Sprint');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/sprint');
        $this->crud->setEntityNameStrings('sprint', 'sprints');
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->crud->setFromDb();
//        $this->addColumns();
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(SprintRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->addFields();
        $this->crud->setListView("vendor.crud.list");
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Store a newly created resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        $data = $request->all();
        $data["slug"] = \Str::slug($data["name"]) .'-'. time();
        // insert item in the db
        $item = Sprint::create($data);
        $this->data['entry'] = $this->crud->entry = $item;
        foreach($data["feature"] as $feature){
            $fe = Feature::create([
                "name" => $feature,
                "slug" => \Str::slug($feature) ."-". time(),
                "project_id" => $data["project_id"]
            ]);
            $item->features()->attach($fe);
        }
        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    private function addFields(){
        $this->crud->addField([
            "label" => "Project",
            "type" => "select2_from_array",
            "name" => "project_id",
            "placeholder" => "choose project",
            'options' => $this->getOptionsProject(),
            'allows_null' => false,
            'hint' => "Chose your own project"
        ]);
        $this->crud->addField([
            "label" => "Name",
            "type" => "text",
            "name" => "name",
        ]);
        $this->crud->addField([
            "label" => "Purposes",
            "type" => "ckeditor",
            "name" => "purposes",
            'options' => [
                'height' => 200,
                'removePlugins' => 'resize,maximize',
            ]
        ]);
        $this->crud->addField([
            "label" => "Features",
            "type" => "AddFeature",
            "name" =>"feature[]"
        ]);
    }

    private function getOptionsProject(){
        $data = [];
        $projects = backpack_user()->projectMadeByMe()->select(["id","name"])->get()->toArray();
        foreach ($projects as $project){
            $data[$project["id"]] = $project["name"];
        }
        return $data;
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

    private function addColumns(){
        $this->crud->addColumn([
            "name" => "name",
            "label" => "Name",
            "limit" => 200
        ]);
        $this->crud->addColumn([
            "name" => "project",
            "type" => "closure",
            "function" => function($entry){
                return $entry->project->name;
            },
            "limit" => 200
        ]);
        $this->crud->addColumn([
            "name" => "feature",
            "label" => "features",
            "type" => "closure",
            "function" => function($entry){
                return number_format($entry->features()->count());
            },
            "limit" => 200
        ]);
        $this->crud->addColumn([
            "name" => "task",
            "label" => "tasks",
            "type" => "closure",
            "function" => function($entry){
                $sum = 0;
                foreach ($entry->features as $feature){
                    $sum += $feature->tasks()->count();
                }
                return number_format($sum);
            },
            "limit" => 200
        ]);
    }
}
