<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Models\Todo;
use App\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TaskCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class TaskCrudController extends CrudController
{
    use ListOperation;
//    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Task');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/task');
        $this->crud->setEntityNameStrings('task', 'tasks');
        $this->crud->setShowView("vendor.backpack.crud.showTask");
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
            "type" => "text",
            "limit" => 200
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
        $this->crud->addColumn([
            "name" => "todoes",
            "label" => "To does",
            "type" => "closure",
            "function" => function($entry){
                return $entry->todoes()->where("status", Todo::DONE)->count() . "/" .$entry->todoes()->count();
            }
        ]);
        $this->crud->addColumn([
            "name" => "status",
            "label" => "Status",
            "type" => "closure",
            "function" => function($entry){
                return '<a href="'.route("api.task.updateStatus", $entry->id).'" title="'.($entry->status ? "Done" : "Doing").'">'.($entry->status ? '<span class="la la-2x la-toggle-on"></span>' : '<span class="la la-2x la-toggle-off"></span>').'</a>';
            }
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
            "label" => "To does",
            "type" => "AddTodo",
            'name' => 'todoes[]',
            'suffix' => "<button class=\"btn btn-primary btn-sm\" id='more-todo' title=\"add task\"><span class=\"la la-plus\"></span> </button>",
            'hint' => "you can add to does for this project or later"
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
        $data["todoes"] = array_filter($data["todoes"]);
        // update the row in the db
        $item = Task::find($request->get($this->crud->model->getKeyName()));
        $item->update($data);
        $this->data['entry'] = $this->crud->entry = $item;
        $this->addMember($item, $data["user_id"]);
        if (isset($data["old-todo-id"])){
            $data["old-todo"] = array_combine($data["old-todo-id"], $data["old-todo-content"]);
            $this->updateOldTodo($item,$data["old-todo-id"],$data["old-todo"]);
        }else{
            $this->updateOldTodo($item);
        }
        $this->addTodo($item,$data["todoes"]);

        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $setFromDb = $this->crud->get('show.setFromDb');

        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data["entry"]->priority = $this->getLabel($this->data["entry"]);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // set columns from db
        if ($setFromDb) {
            $this->crud->setFromDb();
        }

        // cycle through columns
        foreach ($this->crud->columns() as $key => $column) {

            // remove any autoset relationship columns
            if (array_key_exists('model', $column) && array_key_exists('autoset', $column) && $column['autoset']) {
                $this->crud->removeColumn($column['key']);
            }

            // remove any autoset table columns
            if ($column['type'] == 'table' && array_key_exists('autoset', $column) && $column['autoset']) {
                $this->crud->removeColumn($column['key']);
            }

            // remove the row_number column, since it doesn't make sense in this context
            if ($column['type'] == 'row_number') {
                $this->crud->removeColumn($column['key']);
            }

            // remove columns that have visibleInShow set as false
            if (isset($column['visibleInShow']) && $column['visibleInShow'] == false) {
                $this->crud->removeColumn($column['key']);
            }

            // remove the character limit on columns that take it into account
            if (in_array($column['type'], ['text', 'email', 'model_function', 'model_function_attribute', 'phone', 'row_number', 'select'])) {
                $this->crud->modifyColumn($column['key'], ['limit' => 999]);
            }
        }

        // remove preview button from stack:line
        $this->crud->removeButton('show');

        // remove bulk actions colums
        $this->crud->removeColumns(['blank_first_column', 'bulk_actions']);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getShowView(), $this->data);
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

    private function addTodo($item, $todoes){
        if(!empty($todoes)){
            foreach($todoes as $todo){
                $item->todoes()->create([
                    "content" => $todo
                ]);
            }
        }
    }

    private function updateOldTodo($item,$todo_ids = [],$todoes = []){
        $item->todoes()->whereNotIn("id",$todo_ids)->delete();
        if (!empty($todoes)){
            foreach ($todoes as $key => $todo){
                $record = Todo::find($key);
                if ($record->content != $todo){
                    $record->content = $todo;
                }
                $record->save();
            }
        }
    }
}
