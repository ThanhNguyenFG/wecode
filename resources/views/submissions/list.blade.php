@extends('layouts.app')
@php($selected = 'all_submissions')
@section('other_assets')
	<link rel='stylesheet' type='text/css' href='https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css'/>
	<link rel='stylesheet' type='text/css' href='{{ asset("assets/prismjs/prism.css") }}'/>
	<style type="text/css">
	.status_button{
		white-space: normal;
	}
	.wcj_log{
		font-size: 0.70em;
	}
	</style>
@endsection
@section('icon')
	fas {{$choose =='all' ? 'fa-bars' : 'fa-map-marker'}}
@endsection
@section('title')
	{{$choose =='all' ? 'All submissions' : 'Final submissions'}}
@endsection
@section('title_menu')
@if ($user_id != 'all' and !in_array( Auth::user()->role->name, ['student'])) 
	<a href="{{route('submissions.index', [$assignment->id, 'all', $problem_id, 'all'])}}">Remove filter user</a>
@endif
@if ($problem_id != 'all')
	<a href="{{route('submissions.index', [$assignment->id, $user_id, 'all', 'all'])}}">Remove filter problem</a>
@endif
@endsection
@section('body_end')
<div class="modal fade" id="submission_modal" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h6 class="modal-title" id="exampleModalLongTitle">Modal title</h6>
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button>
		</div>
		<div class="modal-body">
  <div class="text-center">
	<div class="spinner-border" role="status">
	  <span class="sr-only">Loading...</span>
	</div>
  </div>
		</div>
  
	  	</div>
	</div>
  </div>
  
	  <script type='text/javascript' src="{{ asset("assets/prismjs/prism.js") }}"></script>
	  <script type='text/javascript' src="{{ asset("assets/js/shj_submissions.js") }}"></script>
	  <script type='text/javascript' src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
	  <script type='text/javascript' src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
  
	  <script type="text/javascript">$("nav  > ul.pagination a").addClass("page-link");</script>
@endsection
@section('content')
@if($choose == 'all')
<a href="{{route('submissions.index', [$assignment->id, $user_id, $problem_id, 'all'])}}" class="btn btn-primary active" role="button">All <i class="fas fa-chevron-down"></i></a>
<a style="opacity: 0.3;" href="{{route('submissions.index', [$assignment->id, $user_id, $problem_id, 'final'])}}" class="btn btn-light active" role="button">Final <i class="fas fa-chevron-right"></i></a>
@else
<a style="opacity: 0.3;" href="{{route('submissions.index', [$assignment->id, $user_id, $problem_id, 'all'])}}" class="btn btn-light active" role="button">All <i class="fas fa-chevron-right"></i></a>
<a href="{{route('submissions.index', [$assignment->id, $user_id, $problem_id, 'final'])}}" class="btn btn-primary active" role="button">Final <i class="fas fa-chevron-down"></i></a>
@endif
<hr>
@if ($choose == 'all')
<p><i class="fa fa-warning color3"></i> You cannot change your final submissions after assignment finishes.</p>
@endif
<div class="row">
    <div class="col">
        <div class="table-responsive">
			<table class="wecode_table table table-bordered {{$choose == 'all' ? 'table-striped' : 'data-table'}}">
				<thead class="thead-dark">
					<tr>
						@if ($choose == 'all')
							<th width="1%" rowspan="1">Final</th>
						@endif
						@if ($choose == 'final')
							<th width="1%" rowspan="1">#</th>
						@endif
							<th width="2%" rowspan="1">Submit ID</th>
						@if (in_array( Auth::user()->role->name, ['student']))
							<th width="20%">Problem</th>
							<th width="10%">Submit Time</th>
							<th width="7%">Score</th>
							<th width="7%">Delay (%)</th>
							<th width="1%">Language</th>
							<th width="30%">Status</th>
							<th width="15%">Code</th>
						@else
							<th width="5%">Username</th>
							<th width="20%">Name</th>
							<th width="20%">Problem</th>
							<th width="10%">Submit Time</th>
							<th width="1%">Score</th>
							<th width="1%">Delay %</th>
							<th width="1%">Lang</th>
							<th width="6%">Status</th>
							<th width="6%">Code</th>
							<th width="6%">Log</th>
							<th width="1%">Rejudge</th>
						@endif
					</tr>
				</thead>
				@foreach ($submissions as $submission)
				<tr data-id="{{$submission->id}}">
					@if ($choose == 'all')
						<td>
							<i class="pointer set_final far {{ $submission->is_final ? 'fa-check-circle color11' : 'fa-circle' }} fa-2x"></i>
						</td>
					@endif
					
					@if ($choose == 'final')
						<td>{{$loop->iteration}} </td>
					@endif
					<td>{{$submission->id}}</td>
					@if (!in_array( Auth::user()->role->name, ['student']))
					<td>
						<a href="{{route('submissions.index', [$assignment->id, strval($submission->user_id), $problem_id, 'all'])}}">
							{{$submission->user->username}}<br><i class="fas fa-filter"></i>
						</a>
					</td>
					<td>{{$submission->user->display_name}}</td>
					@endif
					<td>
						<a href="{{route('problems.show', $submission->problem_id)}}">
							@if ($assignment->id == 0)
								{{$submission->problem->name}}
							@else
								{{$submission->problem->name}}
								{{-- {{$assignment->problems[$submission->problem->id]->pivot->problem_name}} --}}
							@endif
						</a><br>
						<a href="{{route('submissions.index', [$assignment->id, $user_id, strval($submission->problem_id), 'all'])}}"><span class="btn btn-info btn-sm"><i class="fas fa-filter"></i></span></a>
					</td>
					<td>{{$submission->time}}</small></td>
					<td>{{$submission->score}}</td>
					<td>Delay</td>
					<td>{{$submission->language->name}}</td>
					<td>Status</td>
					<td>Code</td>
					<td>Log</td>
					<td>Rejudge</td>
				</tr>
				@endforeach
			</table>
		</div>
	</div>
</div>
@endsection

@section('body_end')
<script type='text/javascript' src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type='text/javascript' src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function () {
    $("table").DataTable({
		"pageLength": 10,
		"lengthMenu": [ [10, 20, 30, 50, -1], [10, 20, 30, 50, "All"] ]
	});
});
</script>
@endsection