<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Str;

/**
 * Class ProjectCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ProjectCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\Models\Project');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/project');
        $this->crud->setEntityNameStrings('project', 'projects');
        $this->crud->addClause("orderBy","created_at","desc");
        $this->crud->setShowView("vendor.backpack.crud.showProject");
    }

    protected function setupListOperation()
    {
        $this->addColumns();
    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(ProjectRequest::class);
        $this->addFields();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    /**
     * Store a newly created resource in the database.
     *
     * @return RedirectResponse
     */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');
        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        $data = $this->crud->getStrippedSaveRequest();
        $data["slug"] = Str::slug($data["name"],"-") . "-" . time();
        $data["created_by"] = backpack_user()->id;
        $data["document"] = array_filter($data["document"]);

        // insert item in the db
        $item = Project::create($data);
        $this->data['entry'] = $this->crud->entry = $item;
        $this->uploadDocument($item,$data["document"]);
        $this->addMember($item,$data["user_id"] ?? []);

        // show a success message
        Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
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
        $data = $request->except(["_token","_method","http_referrer","save_action"]);
        $data["document"] = array_filter($data["document"]);
        // update the row in the db
        $item = Project::find($request->get($this->crud->model->getKeyName()));
        $item->update($data);
        $this->data['entry'] = $this->crud->entry = $item;

        $this->deleteDocument($item, isset($data["old-documents"]) ? $data["old-documents"] : null);
        $this->deleteUser($item, isset($data["user_id"]) ? array_unique($data["user_id"]) : null);

        $this->uploadDocument($item,$data["document"]);
        if(isset($data["user_id"])){
            $data["user_id"] = array_unique($data["user_id"]);
            $this->addMember($item,$data["user_id"]);
        }

        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($item->getKey());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return string
     */
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $item = Project::findOrFail($id);
        $this->deleteDocument($item);
        $this->deleteUser($item);
        return $item->delete();
    }

    private function addMember($item, $users){
        if(!empty($users)){
            foreach($users as $user_id){
                $item->users()->attach($user_id);
            }
        }
    }

    private function deleteDocument($item, $oldDocuments = null){
        foreach($item->documents as $document){
            if (isset($oldDocuments)){
                if (!in_array($document->id, $oldDocuments)){
                    Storage::delete($document->path);
                    $document->delete();
                }
            }else{
                $document->delete();
            }
        }
    }

    private function deleteUser($item, $user_id = null){
        foreach ($item->users as $user){
            if (isset($user_id)){
                if (!in_array($user->id,$user_id)){
                    $item->users()->detach($user->id);
                }
            }
            else{
                $item->users()->detach($user->id);
            }
        }
    }

    private function uploadDocument($item, $documents){
        if (!empty($documents)){
            $folder = now()->format("Y") ."/". now()->format("m") ."/". now()->format("d");
            foreach($documents as $key => $document){
                $path = Storage::put("public/documents/" . $folder, $document);
                $item->documents()->create([
                    "name" => $document->getClientOriginalName(),
                    "uploaded_by" => backpack_user()->id,
                    "path" => $path
                ]);
            }
        }
    }

    private function addFields(){
        $this->crud->addField([
            "label" => "Name",
            "type" => "text",
            "name" => "name"
        ]);
        $this->crud->addField([
            "label" => "Description",
            "type" => "ckeditor",
            "name" => "description",
            'options' => [
                'height' => 400,
                'removePlugins' => 'resize,maximize',
            ]
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
            "label" => "Documents",
            "type" => "DocumentUpload",
            "name" => "document",
            'upload' => true,
            'disk' => 'uploads',
            "hint" => "You can upload images (.jpg, .png) or documents (.doc, .pdf, .docx) with max size about 5MB"
        ]);
        $this->crud->addField([
            'label' => "Teams",
            'type' => "UserSelect",
            'name' => 'user_id',
            'entity' => 'users',
            'attribute' => "name",
            'model' => User::class,
            'data_source' => url("api/users"),
            'placeholder' => "Select member ",
            'minimum_input_length' => 2,
            'pivot' => true,
            'hint' => "you can search members to join this project by their email now or latter"
        ]);
    }

    private function addColumns(){
        $this->crud->addColumn([
            "name" => "id",
            "type" => "number"
        ]);
        $this->crud->addColumn([
            "name" => "name",
            "type" => "text",
            "limit" => 200
        ]);
        $this->crud->addColumn([
            "name" => "created_by",
            "type" => "closure",
            "function" => function ($entry){
                return $entry->user->name;
            }
        ]);
        $this->crud->addColumn([
            "lable" => "started at",
            "name" => "started_at",
            "type" => "dateTime"
        ]);
        $this->crud->addColumn([
            "name" => "end_at",
            "type" => "closure",
            "function" => function ($entry){
                return $entry->end_at->diffForHumans();
            }
        ]);
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
        $this->data["entry"]->created_by = $this->data["entry"]->user->name;
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
}
