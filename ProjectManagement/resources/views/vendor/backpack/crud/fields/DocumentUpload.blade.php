@php
    if (!isset($field['wrapperAttributes']) || !isset($field['wrapperAttributes']['data-init-function'])){
        $field['wrapperAttributes']['data-init-function'] = 'bpFieldInitUploadMultipleElement';
    }

    if (!isset($field['wrapperAttributes']) || !isset($field['wrapperAttributes']['data-field-name'])) {
        $field['wrapperAttributes']['data-field-name'] = $field['name'];
    }
@endphp
@push("after_styles")
    <style>
        .documents{
            border: 1px solid rgba(0,40,100,.12);
            padding: 5px;
        }
        .action{
            float: right;
        }
    </style>
@endpush
<!-- upload multiple input -->
@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

{{-- Show the file name and a "Clear" button on EDIT form. --}}
@if (isset($field['value']))
    @php
        if (is_string($field['value'])) {
            $values = json_decode($field['value'], true) ?? [];
        } else {
            $values = $field['value'];
        }
    @endphp
    @if (count($values))
        <div class="well well-sm existing-file">
            @foreach($values as $key => $file_path)
                <div class="file-preview">
                    @if (isset($field['temporary']))
                        <a target="_blank" href="{{ isset($field['disk'])?asset(\Storage::disk($field['disk'])->temporaryUrl($file_path, Carbon\Carbon::now()->addMinutes($field['temporary']))):asset($file_path) }}">{{ $file_path }}</a>
                    @else
                        <a target="_blank" href="{{ isset($field['disk'])?asset(\Storage::disk($field['disk'])->url($file_path)):asset($file_path) }}">{{ $file_path }}</a>
                    @endif
                    <a href="#" class="btn btn-light btn-sm float-right file-clear-button" title="Clear file" data-filename="{{ $file_path }}"><i class="la la-remove"></i></a>
                    <div class="clearfix"></div>
                </div>
            @endforeach
        </div>
    @endif
@endif
{{-- Show the file picker on CREATE form. --}}
@if(isset($entry))
    @foreach($entry->documents as $document)
        <div class="backstrap-file mt-2 documents">
            <span><a href="{{\Illuminate\Support\Facades\Storage::url($document->path)}}" target="_blank">{{$document->name}}</a></span>
            <input type="hidden" name="old-documents[]" value="{{$document->id}}">
            <span class="action"><button type="button" class="btn btn-delete btn-danger btn-sm"><i class="la la-trash" aria-hidden="true"></i></button></span>
        </div>
    @endforeach
@endif
<input name="{{ $field['name'] }}[]" type="hidden" value="">
<div class="backstrap-file mt-2">
    <input
        type="file"
        name="{{ $field['name'] }}[]"
        value="@if (old(square_brackets_to_dots($field['name']))) old(square_brackets_to_dots($field['name'])) @elseif (isset($field['default'])) $field['default'] @endif"
        @include('crud::fields.inc.attributes', ['default_class' =>  isset($field['value']) && $field['value']!=null?'file_input backstrap-file-input':'file_input backstrap-file-input'])
        multiple
    >
    <label class="backstrap-file-label" for="customFile"></label>
</div>

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    @push('crud_fields_scripts')
        <!-- no scripts -->
        <script>
            function bpFieldInitUploadMultipleElement(element) {
                var fieldName = element.attr('data-field-name');
                var clearFileButton = element.find(".file-clear-button");
                var fileInput = element.find("input[type=file]");
                var inputLabel = element.find("label.backstrap-file-label");

                clearFileButton.click(function(e) {
                    e.preventDefault();
                    var container = $(this).parent().parent();
                    var parent = $(this).parent();
                    // remove the filename and button
                    parent.remove();
                    // if the file container is empty, remove it
                    if ($.trim(container.html())=='') {
                        container.remove();
                    }
                    $("<input type='hidden' name='clear_"+fieldName+"[]' value='"+$(this).data('filename')+"'>").insertAfter(fileInput);
                });

                fileInput.change(function() {
                    inputLabel.html("Files selected. After save, they will show up above.");
                    // remove the hidden input, so that the setXAttribute method is no longer triggered
                    $(this).next("input[type=hidden]").remove();
                });
            }
        </script>
        <script>
            $(document).ready(function () {
                $(document).on("click",".btn-delete",function () {
                    $(this).closest(".documents").remove();
                })
            })
        </script>
    @endpush
@endif
