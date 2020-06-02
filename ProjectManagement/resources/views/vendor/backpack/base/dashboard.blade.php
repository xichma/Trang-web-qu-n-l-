@extends(backpack_view('blank'))
@push("after_styles")
    <style>
        .container-fluid{
            display: flex;
            flex-wrap: wrap;
        }
        table{
            width: 100%;
        }
        table thead{
            text-align: center;
        }
        table tbody{
            text-align: center;
        }
        h3{
            text-align: center;
            font-weight: bold;
            width: 100%;
        }
        h4{
            width: 100%;
            text-align: center;
        }
        .label {
            display: inline;
            padding: .2em .6em .3em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25em;
        }
    </style>
@endpush
@section('content')
@endsection
@section('after_content_widgets')
    <h3>To does list</h3>
    @if(empty($todoes))
        <h4>To does not found</h4>
    @else
        <table class="table table-striped table-bordered">
            <thead>
                <th></th>
                <th>Content</th>
                <th>Priority</th>
                <th>EndTask</th>
            </thead>
            <tbody>
                @foreach($todoes as $todo)
                <tr>
                    <td>
                        <a href="{{route("api.todo.updateStatus",$todo->id)}}" class="btn btn-sm btn-default"><span class="la la-square-o "></span> </a>
                    </td>
                    <td>{{$todo->content}}</td>
                    <td>{!! $todo->priority !!}</td>
                    <td>{{$todo->task->end_at->diffForHumans()}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
