@extends(backpack_view('blank'))

@php
    $defaultBreadcrumbs = [
      trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
      $crud->entity_name_plural => url($crud->route),
      trans('backpack::crud.preview') => false,
    ];

    // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
    $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs
@endphp

@section('header')
    <section class="container-fluid d-print-none">
        <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
        <h2>
            <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
            <small>{!! $crud->getSubheading() ?? mb_ucfirst(trans('backpack::crud.preview')).' '.$crud->entity_name !!}.</small>
            @if ($crud->hasAccess('list'))
                <small class=""><a href="{{ url($crud->route) }}" class="font-sm"><i class="la la-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
            @endif
        </h2>
    </section>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            <!-- Default box -->
            <div class="">
                @if ($crud->model->translationEnabled())
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <!-- Change translation button group -->
                            <div class="btn-group float-right">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[request()->input('locale')?request()->input('locale'):App::getLocale()] }} &nbsp; <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach ($crud->model->getAvailableLocales() as $key => $locale)
                                        <a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}?locale={{ $key }}">{{ $locale }}</a>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @else
                @endif
                <div class="card no-padding no-border">
                    <table class="table table-striped mb-0">
                        <tbody>
                            <tr>
                                <td>
                                    <div style="text-align: left"> <strong>Content:</strong></div>
                                    <div style="text-align: justify">
                                        {!! $entry->content !!}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: left"> <strong>Priority:</strong></div>
                                    <div style="text-align: justify">
                                        {!! $entry->priority !!}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: left"> <strong>Started at:</strong></div>
                                    <div style="text-align: justify">
                                        {!! $entry->started_at !!}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: left"> <strong>End at:</strong></div>
                                    <div style="text-align: justify">
                                        {!! $entry->end_at !!}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: left"> <strong>Status:</strong></div>
                                    <div style="text-align: justify">
                                        {{ $entry->status ? "Done" : "Doing" }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: left"> <strong>Project:</strong></div>
                                    <div style="text-align: justify">
                                        {{ $entry->project->name }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: left"> <strong>To does:</strong></div>
                                    @foreach($entry->todoes as $todo)
                                        <div style="text-align: justify; margin: 1vh 1vw">
                                            <span><a href="{{route("api.todo.updateStatus",$todo->id)}}" class="btn btn-sm @if($todo->status) btn-success @else btn-default @endif"><span class="la @if($todo->status) la-check-square @else la-square-o @endif"></span> </a></span>
                                            <span>{{ $todo->content }}</span>
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="fb-comments" data-href="{{request()->url()}}"
                         data-width="100%" data-numposts="5"></div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->

        </div>
    </div>
@endsection


@section('after_styles')
    <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css') }}">
    <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css') }}">
@endsection

@section('after_scripts')
    <script src="{{ asset('packages/backpack/crud/js/crud.js') }}"></script>
    <script src="{{ asset('packages/backpack/crud/js/show.js') }}"></script>
@endsection
