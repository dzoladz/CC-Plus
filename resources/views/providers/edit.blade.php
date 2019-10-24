@extends('layouts.app')

@section('content')
<script type="text/javascript" src="{{ URL::asset('js/providers.js') }}"></script>
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Editting settings for : {{ $provider->name }}</h2>
            <h3>(ID: {{ $provider->id }})</h3>
        </div>
        <div class="pull-right">
          @if ( auth()->user()->hasRole('Admin') )
            <a class="btn btn-primary" href="{{ route('providers.index') }}"> Back</a>
          @else
            <a class="btn btn-primary" href="{{ route('admin') }}"> Back</a>
          @endif
        </div>
    </div>
</div>

@if (count($errors) > 0)
  <div class="alert alert-danger">
    <strong>Whoops!</strong> There were some problems with your input.<br><br>
    <ul>
       @foreach ($errors->all() as $error)
         <li>{{ $error }}</li>
       @endforeach
    </ul>
  </div>
@endif

<table>
  <tr>
    <th width="5%">&nbsp;</th>
    <th width="40%"><h3>Editting settings for : {{ $provider->name }} (id: {{ $provider->id }})</h3></th>
    <th width="10%">&nbsp;</th>
    <td width="40%"><h3>Update Sushi settings by Institution</h3></th>
    <th width="5%">&nbsp;</th>
  </tr>
  <tr>
    <td colspan="3" align="center">&nbsp;</td>
    <td colspan="2" align="center"><div style="display:none; color:red;" id="notice"></div></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top">
    {!! Form::model($provider, ['method' => 'PATCH','route' => ['providers.update', $provider->id]]) !!}
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Status:</strong>
                {!! Form::select('is_active', ['1'=>'Active', '0'=>'Inactive'],
                                 array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Serves:</strong>
                @if ( auth()->user()->hasRole('Admin') )
                    {!! Form::select('inst_id', $institutions, $provider->inst_id,
                                     array('class' => 'form-control')) !!}
                @else
                  <span style="padding-left: 20px; display:inline-block">
                      <input type='hidden' name='inst_id' value='{{ auth()->user()->institution->id }}' />
                      <strong>{{ auth()->user()->institution->name }}</strong>
                  </span>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Service (SUSHI) URL:</strong>
                {!! Form::text('server_url_r5', null, array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Ingest COUNTER-5 Reports:</strong>
                {!! Form::select('master_reports[]', $master_reports, $ProvReports,
                                 array('class' => 'form-control','multiple')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Security:</strong>
                {!! Form::select('security', ['None'=>'None', 'HTTP' => 'HTTP', 'WSSE' => 'WSSE'],
                                 array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Username:</strong>
                {!! Form::text('auth_username', null, array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Password:</strong>
                {!! Form::text('auth_password', null, array('class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Harvest Monthly on Day:</strong>
                {!! Form::number('day_of_month', 15, array('min' => 1, 'max' => 28,
                                 'class' => 'form-control')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <button type="submit" class="btn btn-primary">Update Provider</button>
        </div>
    </div>
    {!! Form::close() !!}
    </td>
    <td>&nbsp;</td>
    <td valign="top">
      <form method="post" id="sushi_settings">
      @csrf
      <input type="hidden" value="{{ $provider->id }}" name="prov_id" id="PROV">
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Institution:</strong>
                @if ( auth()->user()->hasRole('Admin') && $provider->inst_id == 1)
                    {!! Form::select('inst_id', $sushi_insts, $provider->inst_id,
                                      array('class' => 'form-control', 'id' =>'Inst')) !!}
                @else
                  <span style="padding-left: 20px; display:inline-block">
                      <input type='hidden' name='inst_id' value='{{ $provider->inst_id }}' />
                      <strong>{{ $provider->institution->name }}</strong>
                  </span>
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Customer ID:</strong>
                {!! Form::text('customer_id', null, array('placeholder' => 'Customer ID',
                               'class' => 'form-control', 'id' => 'Sushi_CustID')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>RequestorID:</strong>
                {!! Form::text('requestor_id', null, array('placeholder' => 'Requestor ID',
                               'class' => 'form-control', 'id' => 'Sushi_ReqID')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>API Key:</strong>
                {!! Form::text('API_key', null, array('placeholder' => 'API Key',
                               'class' => 'form-control', 'id' => 'Sushi_APIkey')) !!}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
          <button type="button" id="SaveSushi" class="btn btn-primary">Update Sushi Settings</button>
        </div>
      </div>
      {!! Form::close() !!}
    </td>
    <td>&nbsp;</td>
  </tr>
</table>
@endsection
