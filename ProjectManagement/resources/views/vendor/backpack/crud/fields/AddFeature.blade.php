@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!} <button class="btn btn-primary btn-sm more-feature-btn" title="more features"><span class="la la-plus"></span> </button></label>
@include('crud::fields.inc.translatable_icon')
@push("after_styles")
    <style>
        .features{
            cursor: pointer;
        }
        .more-feature{
            margin-top: 1vh;
        }
    </style>
@endpush
@push('crud_fields_scripts')
    <script>
        $(document).ready(function () {
            $(document).on("click",".more-feature-btn",function (e) {
                e.preventDefault();
                $(".features").append('<input type="text" name="feature[]" value="" class="form-control more-feature">');
            })
        })
    </script>
@endpush
@if(isset($field['prefix']) || isset($field['suffix']))
    <div class="input-group">
@endif
@if(isset($field['prefix']))
    <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div>
@endif
<div class="features">
    <input
        type="text"
        name="{{ $field['name'] }}"
        value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
        @include('crud::fields.inc.attributes')
    >
</div>
@if(isset($field['suffix']))
    <div class="input-group-append"><span class="input-group-text">{!! $field['suffix'] !!}</span></div>
@endif
@if(isset($field['prefix']) || isset($field['suffix']))
    </div>
@endif
{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
</div>
