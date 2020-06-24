@extends('layouts.app')

@section('content')
<v-app institutionform>

	<div class="page-header">
	    <h1>{{ $institution->name }}</h1>
	</div>
    <institution-form :institution="{{ json_encode($institution) }}"
                      :types="{{ json_encode($types) }}"
                      :all_groups="{{ json_encode($all_groups) }}"
    ></institution-form>

    @if ( auth()->user()->hasAnyRole(['Admin','Manager']) )
    <div class="users">
	<h2 class="section-title">Users</h2>
    <users-by-inst :users="{{ json_encode($users) }}"
				   :inst_id="{{ json_encode($institution->id) }}"
				   :all_roles="{{ json_encode($all_roles) }}"
	></users-by-inst>
	</div>
    @endif

    <div class="related-list">
	  <hr>
	  <h2 class="section-title">Providers</h2>
	  <all-sushi-by-inst :settings="{{ json_encode($institution->sushiSettings->toArray()) }}"
		  				 :inst_id="{{ json_encode($institution->id) }}"
		  				 :unset="{{ json_encode($unset_providers) }}"
	  ></all-sushi-by-inst>
    </div>

</v-app>
@endsection
