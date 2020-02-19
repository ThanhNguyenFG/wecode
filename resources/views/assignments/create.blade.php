@extends('layouts.app')

@section('icon', 'fas fa-plus-square')

@section('title', 'Add assignments')

@section('other_assets')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/select2/select2.min.css') }}">
<style>
	.select2-selection__choice{
		display:none !important;
	}
	.problem-score{
		width:8em!important;
	}
</style>
@endsection

@section('title_menu')
<span class="title_menu_item">
	<a href="https://github.com/truongan/wecode-judge/blob/docs/v1.4/add_assignment.md" target="_blank"><i class="fa fa-question-circle color1"></i> Help</a>
</span>
@endsection

@section('body_end')
<script type="text/javascript" src="{{ asset('assets/js/Sortable.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/select2/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/add_assignments.js') }}"></script>
<script type='text/javascript' src="{{ asset('assets/js/taboverride.min.js') }}"></script>
<script>
	$(document).ready(function(){
		tabOverride.set(document.getElementsByTagName('textarea'));
	});
</script>
{{-- <script type="text/javascript">shj.num_of_problems={{ $problems.count() }};</script> --}}
@endsection

@section('content')
{{-- <form method="POST"  
	@if (Route::currentRouteName() == 'assignments.edit')
		action="{!! route('assignments.update', $assignment) !!}"
	@else  
		action="{!! route('assignments.store') !!}"
	@endif
enctype="multipart/form-data">
@if (Route::currentRouteName() == 'assignments.edit')
	@method("PUT")
@endif
<input type="hidden"  name ="_token" value="{!! csrf_token() !!}"/>
<table>
	<tr>
			<td>Assignment Name:</td>
			<td><input type="text" name="name" value="{{$assignment->name ?? ''}}"></td>
	</tr>
	<tr>
			<td>Description:</td>
			<td><input type="text" name="description" value="{{$assignment->description ?? ''}}"></td>
	</tr>
	<tr>
			<td>Start time:</td>
			<td>
				<input type="date" name="start_time_date" value="{{empty($assignment) ? '' : date('Y-m-d', strtotime($assignment->start_time))}}"> 
				<input type="time" name="start_time_time" value="{{empty($assignment) ? '' : date('H:i', strtotime($assignment->start_time))}}">
			</td>
	</tr>
	<tr>
			<td>Finish time:</td>
			<td>
				<input type="date" name="finish_time_date" value="{{empty($assignment) ? '' : date('Y-m-d', strtotime($assignment->finish_time))}}"> 
				<input type="time" name="finish_time_time" value="{{empty($assignment) ? '' : date('H:i', strtotime($assignment->start_time))}}">
			</td>
	</tr>
	<tr>
			<td>Extra time:</td>
			<td><input type="number" name="extra_time" value="{{$assignment->extra_time ?? ''}}"></td>
	</tr>
	<tr>
			<td>Participants:</td>
			<td><input type="text" name="participants" value="{{$assignment->participants ?? ''}}"></td>
	</tr>
	<tr>
			<td>PDF File:</td>
			<td><input type="file" name="pdf_file" value="Choose.pdf"></td>
	</tr>
	<tr>
			<td>Open:</td>
		 	<td><input type="checkbox" name="open" 
		 		@if (!empty($assignment))
			 		@if ($assignment->open)
			 			checked
			 		@endif
			 	@endif
		 	></td>
	</tr>
	<tr>
		 	<td>Scoreboard:</td>
		 	<td><input type="checkbox" name="score_board" 
		 		@if (!empty($assignment))
			 		@if ($assignment->score_board)
			 			checked
			 		@endif
			 	@endif
		 	></td>
	</tr>
	<tr>
			<td>Coefficient rule:</td>
			<td><input type="text" name="late_rule" value="{{$assignment->late_rule ?? ''}}"></td>
	</tr>

</table>
<button class="btn btn-primary"   type="submit" > OK </button>
</form> --}}



















<div class="col">
	{{-- {% set msgclasses = {'text-success': 'text-success', 'text-info': 'text-warning', 'text-danger': 'text-danger'} %} --}}
	{{-- {% for message in messages %}
		<p class="{{ msgclasses[message.type] }}">{{ message.text }}</p>
	{% endfor %} --}}
	
	{{-- {% if edit %}
	<p>
		<i class="fa fa-info-circle fa-lg color8"></i> If you don't want to change tests or pdf file, just do not upload its file.
	</p>
	{% endif %} --}}
	@php($edit = Route::currentRouteName() == 'assignments.edit')
	<form method="POST"
		@if (Route::currentRouteName() == 'assignments.edit')
			action="{!! route('assignments.update', $assignment) !!}"
		@else  
			action="{!! route('assignments.store') !!}"
		@endif
		enctype="multipart/form-data">
		@if (Route::currentRouteName() == 'assignments.edit')
			@method("PUT")
		@endif
	
		{{-- <input type="hidden" name="number_of_problems" id="nop" value="{{ $edit ? $assignment->problems->count() : $problems->count() }}"/> --}}
		<div class="row">
			<div class="col-sm-6">
				<fieldset class="form-group">
					<div class="form-row">
						<div class="col-sm-4">
							<label for="form_a_name">Assignment Name</label>
						</div>
						<div class="col-sm-8">
							<input id="form_a_name" type="text" name="assignment_name" class="form-control col-xs-7" value="{{ $edit ? $assignment->name : old('assignment_name') }}"/>
							{{-- {{ form_error('assignment_name', '<div class="alert alert-danger">', '</div>') }} --}}
						</div>
					</div>
				</fieldset>
				<fieldset class="form-group">
					<div class="form-row">
						<div class="col-sm-4">
							<label for="start_time">Start Time</label>
						</div>
						<div class="col-sm-8">
							<input id="start_time" type="hidden" name="start_time" class="form-control" value="" />
							<div class="form-row">
								<div class="col-xl-7">
									<input id="start_date" type="date" name="start_date" class="form-control" value="{{ $edit ? $assignment->start_time|date('Y-m-d') : old('start_date') }}" />
								</div>
								<div class="col-xl-5">
									<input id="start__time" type="time" name="start__time" class="form-control" value="{{ $edit ? $assignment->start_time|date('H:i') : old('start__time',0) }}" />
								</div>
							</div>
							{{-- {{ form_error('start_time', '<div class="alert alert-danger">', '</div>') }} --}}
						</div>
					</div>
				</fieldset>
	
				<fieldset class="form-group">
					<div class="form-row">
						<div class="col-sm-4">
							<label for="finish_time">Finish Time
							<small class="form-text text-muted">Set finish time before start time will set the deadline to <strong>FOREVER</strong></small>
							</label>
						</div>
						<div class="col-sm-8">
							<input id="finish_time" type="hidden" name="finish_time" class="form-control" value="" />
							<div class="form-row">
								<div class="col-xl-7">
									<input id="finish_date" type="date" name="finish_date" class="form-control" value="{{ $edit ? $assignment->finish_time|date('Y-m-d') : old('finish_date') }}" />
								</div>
								<div class="col-xl-5">
									<input id="finish__time" type="time" name="finish__time" class="form-control" value="{{ $edit ? $assignment->finish_time|date('H:i') : old('finish__time') }}" />
								</div>
							</div>
							{{-- {{ form_error('finish_time', '<div class="alert alert-danger">', '</div>') }} --}}
						</div>
					</div>
				</fieldset>
	
				<fieldset class="form-group">
					<div class="form-row">
						<div class="col-sm-4">
							<label for="form_extra_time">
							Extra Time (minutes)
							<small class="form-text text-muted">Extra time for late submissions.</small>
							</label>
						</div>
						<div class="col-sm-8">
							<input id="form_extra_time" type="text" name="extra_time" id="extra_time" class="form-control" value="{{ $edit ? $assignment->extra_time : old('extra_time', 0) }}" />
							{{-- {{ form_error('extra_time', '<div class="alert alert-danger">', '</div>') }} --}}
						</div>
					</div>
				</fieldset>
				<fieldset class="form-group">
					<div class="form-row">
						<div class="col-sm-4">
							<label for="form_participants">Participants<br>
								<small class="form-text text-muted">Only the users in this list (comma separated) are able to submit.
									You can use keyword <code>ALL</code>.</small>
							</label>
						</div>
						<div class="col-sm-8">
					<textarea id="form_participants" name="participants" rows="2" class="form-control">{{ $edit ? $assignment->participants : old('participants', 'ALL') }}</textarea>
						</div>
					</div>
				</fieldset>
	
				<fieldset class="form-group">
					<div class="form-row">
						<div class="col-sm-4">
							<label for="form_pdf">PDF File<br>
								<small class="form-text text-muted">PDF File of Assignment</small>
							</label>
						</div>
						<div class="col-sm-8">
							<div class="custom-file">
								<input id="form_pdf" type="file" name="pdf" class="custom-file-input"/>
								<label class="custom-file-label"><small>Choose .pdf</small></label>
							</div>
						 </div>
					</div>
				</fieldset>
			</div>
	
			<div class="col-sm-6">
				<fieldset class="form-group">
					<div class="form-row">
							<label for="form_late_rule">Description
							<small class="form-text text-muted medium clear" style="display: block;"></small>
							</label>
					</div>
					<textarea id="form_late_rule" name="description" rows="3" class="form-control add_text">{{ $edit ? $assignment->description : old('description', '') }}</textarea>
					{{-- {{ form_error('late_rule', '<div class="alert alert-danger">', '</div>') }} --}}
				</fieldset>
				<fieldset class="form-group">
					<div class="custom-control custom-switch">
						<input id="form_a_open" class="custom-control-input" type="checkbox" name="open" value="1" {!! $edit ? ($assignment->open ? 'checked' : '') :'' !!} />
						<label for="form_a_open" class="custom-control-label">Open</label>
					</div>
					<small class="form-text text-muted ">Open or close this assignment for submission</small>
					{{-- {{ form_error('open', '<div class="alert alert-danger">', '</div>') }} --}}
				</fieldset>
	
				<fieldset class="form-group">
					<label class="custom-control custom-switch">
						<input id="form_a_scoreboard" class="custom-control-input" type="checkbox" name="scoreboard" value="1" {!! $edit ? ($assignment->scoreboard ? 'checked' : '') : '' !!} />
						<span {#for="form_a_scoreboard"#} class="custom-control-label">Scoreboard</span>
					</label>
					<small class="form-text text-muted ">Check this to publish scoreboard to student, lecturer can always see scoreboard</small>
					{{-- {{ form_error('scoreboard', '<div class="alert alert-danger">', '</div>') }} --}}
				</fieldset>
	
				<fieldset class="form-group">
					<div class="form-row">
							<label for="form_late_rule">Coefficient rule (<a target="_blank" href="https://github.com/truongan/wecode-judge/blob/docs/v1.4/add_assignment.md#coefficient-rule">?</a>)
							<small class="form-text text-muted medium clear">PHP script without &lt;?php ?&gt; tags. You can use 3 variables: <code>$extra_time</code>, <code>$delay</code>, <code>$submit_time</code></small>
							</label>
					<textarea id="form_late_rule" name="late_rule" rows="8" class="form-control add_text">{{ $edit ? $assignment->late_rule : old('late_rule', $settings['default_late_rule'], false) }}</textarea>
					{{-- {{ form_error('late_rule', '<div class="alert alert-danger">', '</div>') }} --}}
				</fieldset>
			</div>
		</div>
	
	<div class="row">
		<div class="col-sm-8">
			<fieldset class="form-group">
				<label> Select problem(s) for this assignment
					<small class="form-text text-muted">You can type in the box below to search for problems
					</small>
				</label>
				<select class="all_problems form-control" multiple="multiple">
					@foreach( $all_problems as $p)
					<option value="{{ $p->id }}" data-name="{{$p->name}}" data-id="{{$p->id}}" data-note="{{ $p->admin_note }}" data-no_of_assignment="{{ $p->no_of_assignment }}" 
						{{-- {{ $problems[$p->id] ?  'selected="selected"' : ''  }} --}}
						>
					 {{$p->id}} - {{$p->name}} ({{ $p->admin_note }}) </option>
					@endforeach
				</select>
			</fieldset>
		</div>
		<div class="col-sm-4">
			<div class="form-group">
			  <label for="min_assignment_to_select">Select all problems</label>
	
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="input-group-text" >used in less than</span>
					</div>
					<input type="number" class="form-control" value="2" id="multiple_problems_min" >
					<div class="input-group-append">
						<span class="input-group-text" >assignments</span>
						<button class="btn btn-outline-primary" type="button" id="select_multiple_problems">Add</button>
					</div>
				</div>
	
			  <small id="helpId" class="text-muted">Use this to select every problems that have been used in no more than a specific number of assignments. You can still fine tuning the selection afterward </small>
			</div>
		</div>
	</div>
	
	<fieldset class="form-group">
	<label> Set alias, score and order for problems in this assignment  <small class="form-text text-muted">Problem's alias will be displayed when student view this assignment instead of the problem's original name<br/>
	You can drag the handle to re-order the problems.<br/>
	Remove one problem from assignment won't remove the submissions of that problem but will reset its alias and score to default if you re-add it later.
	<br/>Currently: <span class="badge badge-info count_problems">0</span> problems with a total score of <span class="badge badge-info sum_score">0</span>
	</small></label>
	<ul id="problem_list" class="list-group">
		@php($i = 0)
		@foreach($problems as $problem)
		<li   class="list-group-item {{$problem['id'] == -1 ? 'd-none' : ''}} "><div class="row align-items-center">
			<div class="col-auto list_handle pointer">
				<span><i class="fa fa-grip-vertical fa-lg fa-fw"></i></span>
			</div>
			<div class="col">
				<div class="row  align-items-center" >			
					<div class="form-inline">
						<input type="hidden" name="problem_id[]" value="{{$problem['id']}}"/>
						<span class="lead mr-2">
							<span class="badge badge-light">{{ $problem['id'] }}</span>
							{{ $problem['name'] }}
						</span>
						<div class="input-group input-group-sm mr-2">
							<div class="input-group-prepend">
								<label class="input-group-text">Alias</label>
							</div>
							<input type="text" name="problem_name[]" class="form-control form-control-sm " value="{{ $problem['problem_name'] }}"/>
						</div>
						
						<div class="input-group input-group-sm mr-2">
							<div class="input-group-prepend">
							<label class="input-group-text">Score</label>
							</div>
							<input type="number" name="problem_score[]" class="form-control form-control-sm problem-score" value="{{ $problem['score'] }}"/>
						</div>
						<span class="text-muted admin_note">{{ $problem['admin_note'] }}</span>
					</div>
				</div>
			</div>
			<div class="col-auto">
					<button class="btn btn-danger list_remover"><span><i class="fa fa-times-circle fa-lg fa-fw pointer"></i></span></button>
			</div>
			@php($i = $i+1)
		</div></li>
		@endforeach
	<ul class="list-group">
	</fieldset>
	
	<fieldset class="form-group mt-2">
		<input type="submit" value="{{ $edit ? "Edit" : "Add" }} Assignment" class="sharif_input btn btn-primary"/>
	</fieldset>
	</form>
</div>
@endsection

