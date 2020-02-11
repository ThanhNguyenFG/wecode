@php($selected = 'notifications')
@extends('layouts.app')

@section('icon', 'fas fa-bell')

@section('title', 'Notifications')

@section('title_menu')
@if ( in_array( Auth::user()->role->name, ['admin', 'head_instructor']) )
    <span class="title_menu_item"><a href="{{ route('notifications.create') }}"><i class="fa fa-plus color10"></i> New</a></span>
@endif
@endsection

@section('body_end')
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
  tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
});
</script>
<script type="text/javascript" async
  src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML">
</script>
<div class="modal fade" id="notification_delete" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Are you sure you want to delete this notification?</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <div class="modal-footer">
        <button type="button" class="btn btn-danger confirm-notifycation-delete">yes, DELETE it</button>
		<button type="button" class="btn btn-primary" data-dismiss="modal">NO, DON'T delete</button>
      </div>
    </div>
  </div>
</div>

<script>
/**
 * Notifications
 */
$(document).ready(function () {
	$('.del_n').click(function () {
        jQuery.noConflict();
		var notif = $(this).parents('.notif');
		var id = $(notif).data('id');

		$(".confirm-notifycation-delete").off();
		$(".confirm-notifycation-delete").click(function(){
			$.ajax({
				type: 'DELETE',
				url: 'notifications/'+id,
				data: {
                    '_token': "{{ csrf_token() }}",
				},
				error: shj.loading_error,
				success: function (response) {
					if (response.done) {
						notif.animate({backgroundColor: '#FF7676'}, 1000, function () {
							notif.remove();
						});
						$.notify('Notification deleted'	, {position: 'bottom right', className: 'success', autoHideDelay: 5900});
                        $("#notification_delete").modal("hide");
					}
					else
						shj.loading_failed(response.message);
				}
			});
		});
		$("#notification_delete").modal("show");
	});


});

</script>
@endsection

@section('content')
@if ($notifications->all()==[])
<p style="text-align: center;">Nothing yet...</p>
@endif
@foreach ($notifications as $notification)
	<div class="col-md-6 col-xl-4">
		<div class="notif" id="number{{ $notification->id }}" data-id="{{ $notification->id }}"> 
			<div class="notif_title">
				<a href="notifications#number{{ $notification->id }}">{{ $notification->title }}</a>
				<div class="notif_meta">
					{{ $notification->created_at }}
					@if ( in_array( Auth::user()->role->name, ['admin', 'head_instructor']) )
						<a href="notifications/{{ $notification->id }}/edit">Edit</a>
						<span class="anchor del_n">Delete</span>
					@endif
				</div>
			</div>
			<div class="notif_text">
				{!! $notification->text !!}
			</div>
		</div>
	</div>
@endforeach
@endsection