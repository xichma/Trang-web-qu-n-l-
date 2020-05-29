@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
<p class="error"></p>
@include('crud::fields.inc.translatable_icon')
@push("after_styles")
    <style>
        .more-feature{
            margin-top: 1vh;
        }
        .error{
            color: red;
        }
    </style>
@endpush
@push('crud_fields_scripts')
    <script>
        $(document).ready(function () {
            $(document).on("click","#more-task",function (e) {
                e.preventDefault();
                $(".error").empty();
                if( $("#task-name").val() === ""){
                    $(".error").text("this field is required");
                    return false;
                }
                $(this).closest(".input-group").after("<div class=\"input-group\">\n" +
                    "    <input class='form-control' name='task[]' type=\"text\" value='" + $("#task-name").val() + "'>\n" +
                    "    <div class=\"input-group-append\"><span class=\"input-group-text\"><button class=\"btn btn-sm remove\" title=\"remove this task\"><span class=\"la la-trash\"></span> </button></span></div>\n" +
                    "</div>");
                $("#task-name").val('');
            })
            $(document).on("click",".remove",function (e) {
                e.preventDefault();
               $(this).closest(".input-group").remove();
            })
            // $(document).on("click",".check",function (e) {
            //     e.preventDefault();
            //     if($(this).find("span").hasClass("la-circle")){
            //         $(this).find(".la-circle").removeClass("la-circle").addClass("la-check-circle");
            //     }else{
            //         $(this).find(".la-check-circle").removeClass("la-check-circle").addClass("la-circle");
            //     }
            // })
        })
    </script>
@endpush
@if(isset($field['prefix']) || isset($field['suffix']))
    <div class="input-group">
@endif
@if(isset($field['prefix']))
    <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div>
@endif
    <input
        id="task-name"
        type="text"
        name="{{ $field['name'] }}"
        value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
        @include('crud::fields.inc.attributes')
    >
@if(isset($field['suffix']))
    <div class="input-group-append"><span class="input-group-text">{!! $field['suffix'] !!}</span></div>
@endif
@if(isset($field['prefix']) || isset($field['suffix']))
    </div>
@endif
@if(isset($entry))
    @foreach($entry->tasks as $task)
        <div class="input-group">
            <input type="hidden" name="old-task-id[]" value="{{$task->id}}">
            <input class='form-control' name="old-task-content[]" type="text" value='{{$task->content}}'>
            <div class="input-group-append"><span class="input-group-text"><button class="btn btn-sm remove" title="remove this task"><span class="la la-trash"></span> </button></span></div>
        </div>
    @endforeach
@endif
{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
</div>
