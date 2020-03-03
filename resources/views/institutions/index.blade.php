@extends('layouts.app')

@section('content')
<div class="col-lg-12 margin-tb">
  <a href="{{ route('admin') }}"><< Back</a>
</div>
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Institution Management</h2>
        </div>
        @if (auth()->user()->hasRole("Admin"))
        <div class="pull-right">
            <a class="btn btn-success" href="{{ route('institutions.create') }}"> Create New Institution</a>
        </div>
        @endif
    </div>
</div>

@if ($message = Session::get('success'))
    <flash class="alert-flash" message="{{ $message }}"></flash>
@endif

<table class="table table-bordered">
 <tr>
   <th>Institution</th>
   <th>Type</th>
   <th>Status</th>
   <th>Group(s)</th>
   <th width="280px">Action</th>
 </tr>

 @foreach ($data as $key => $institution)
  <tr>
    <td>{{ $institution->name }}</td>
    <td>{{ $institution->institutiontype->name }}</td>
    <td>{{ $institution->is_active ? 'Active' : 'Inactive' }}</td>
    <td>
       @foreach($groups as $group_id => $group_name)
          @if($institution->isAMemberof($group_id))
             <label class="badge badge-success">{{ $group_name }} </label>
          @endif
       @endforeach
    </td>
    <td>
       <a class="btn btn-info" href="{{ route('institutions.show',$institution->id) }}">Show</a>
       <a class="btn btn-primary" href="{{ route('institutions.edit',$institution->id) }}">Edit</a>
        {!! Form::open(['method' => 'DELETE','route' => ['institutions.destroy', $institution->id],
                                    'style'=>'display:inline']) !!}
            {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
        {!! Form::close() !!}
    </td>
  </tr>
 @endforeach
</table>

{!! $data->render() !!}

@endsection
