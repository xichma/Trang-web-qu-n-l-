<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TaskCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TaskCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
//    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Task');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/task');
        $this->crud->setEntityNameStrings('task', 'tasks');
        CRUD::operation('list', function() {
            CRUD::removeButton('create');
        });
    }

    protected function setupListOperation()
    {
        // TODO: remove setFromDb() and manually define Columns, maybe Filters
        $this->addColumns();
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(TaskRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->addFields();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    private function addColumns(){
        $this->crud->addColumn([
            "name" => "content",
            "label" => "Content",
            "type" => "text"
        ]);
        $this->crud->addColumn([
            "name" => "priority",
            "label" => "Priority",
            "type" => "closure",
            "function" => function($entry){
                return $this->getLabel($entry);
            }
        ]);
        $this->crud->addColumn([
            "name" => "user",
            "label" => "Assignments",
            "type" => "closure",
            "function" => function($entry){
                $name = "";
                if ($entry->users->count() > 0){
                    foreach ($entry->users as $user){
                        $name .= $user->name . ",";
                    }
                    $name = substr($name, 0, -1);
                }else{
                    $name = "";
                }
                return $name;
            },
            "limit" => 200
        ]);
        $this->crud->addColumn([
            "name" => "started_at",
            "label" => "Started at",
            "type" => "dateTime"
        ]);
        $this->crud->addColumn([
            "name" => "end_at",
            "label" => "End at",
            "type" => "dateTime"
        ]);
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

    private function addFields(){
        $this->crud->addField([
            "label" => "Content",
            "name" => "content",
            "type" => "text"
        ]);
        $this->crud->addField([
            "label" => "Priority",
            "name" => "priority",
            'type'        => 'select_from_array',
            'options'     => [
                config("prioritize.urgent_and_important") => "urgent and important",
                config("prioritize.important_not_urgent") => "important not urgent",
                config("prioritize.urgent_not_important") => "urgent not important",
                config("prioritize.not_important_not_urgent") => "not important not urgent"
            ],
            'allows_null' => false,
            'default'     => 0,
        ]);
        $this->crud->addField([
            "label" => "Start at",
            "type" => "datetime_picker",
            "name" => "started_at"
        ]);
        $this->crud->addField([
            "label" => "End at",
            "type" => "datetime_picker",
            "name" => "end_at"
        ]);
        $this->crud->addField([
            "label" => "Assigned to",
            'type' => "UserSelect",
            'name' => 'user_id',
            'entity' => 'users',
            'attribute' => "name",
            'model' => User::class,
            'data_source' => url("api/users/assigned-to"),
            'placeholder' => "Select member ",
            'minimum_input_length' => 2,
            'pivot' => true,
            'hint' => "you just choose user who was added to this project"
        ]);
        $this->crud->addField([
            "label" => "To do",
            "type" => "AddTodo",
            'name' => 'task[]',
            'suffix' => "<button class=\"btn btn-primary btn-sm\" id='more-task' title=\"add task\"><span class=\"la la-plus\"></span> </button>",
            'hint' => "you can add Task for this project or later"
        ]);
    }

    /**
     * Update the specified resource in the database.
     *
     * @return Response
     */
    public function update()
    {
        $this->crud->hasAccessOrFail('update');

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();
        $data =  $data = $request->except(["_token","_method","http_referrer","save_action"]);
        // update the row in the db
        $item = Task::find($request->get($this->crud->model->getKeyName()));
        $item->update($data);
        $this->data['entry'] = $this->crud->entry = $item;
        $this->addMember($item, $data["user_id"]);

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    private function addMember($item, $users)
    {
        foreach ($item->users as $user) {
            $item->users()->detach($user->id);
        }
        if (!empty($users)) {
            foreach ($users as $user_id) {
                $item->users()->attach($user_id);
            }
        }
    }
}
