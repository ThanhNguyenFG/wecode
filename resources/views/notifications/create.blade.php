@php($selected = 'notifications')
@extends('layouts.app')

@section('icon', 'fas fa-plus')

@section('title', 'New Notification')

@section('body_end')
<script src="{{ asset('assets/ckeditor/ckeditor.js') }}" charset="utf-8"></script>
<script>
$(document).ready(function(){
	CKEDITOR.replace("notif_text");
});
</script>
@endsection

@section('content')
<form method="POST"  action="{!! route('notifications.store') !!}">
	<input type="hidden"  name ="_token" value="{!! csrf_token() !!}"/>
	<p class="input_p">
		<label for="form_title" class="tiny">Title:</label>
		<input id="form_title" name="title" type="text" class="sharif_input"/>
	</p>
	<p class="input_p">
		<label for="notif_text" class="tiny">Text:</label><br><br>
		<textarea id="notif_text" name="text"></textarea>
	</p>
	<p class="input_p">
		<input type="submit" value="Add" class="sharif_input"/>
	</p>
</form>	
@endsection