@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Scheduled Tasks</h1>
						<!--<span>Home  |  My Avoo  | Contacts </span> -->
					</div>
					<div class="Unavbg">
						<div class="row">
							<div class="alert-status"> 
								@if(session()->has('message'))
								<div class="alert alert-success" id="success">
									{{ session()->get('message') }}
								</div>
								@endif
								@if(Session()->has('error'))
								<div class="alert alert-danger">
									{{ Session()->get('error') }}
								</div>
								@endif
							</div>
							<div class="col-md-10 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Scheduled Tasks</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
							<div class="col-md-2 col-sm-5">
							<ul class="EAbtnbg">
								<!-- <li class="expo">
									<a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a>
								</li> -->
								<li><a href="#" data-task_id="" id="add_task">ADD&nbsp;TASK</a></li>
							</ul>
							</div>
						</div>
					</div>   
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-12">
								<table id="contacttable" class="display responsive no-wrap" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Description</th>
											<th>Last Run</th>
											<th>Next Run</th>
											<th>Time Taken</th>
											<th>Status</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($tasks as $task)
										<tr id="task_{{$task->id}}">
											<td>{{$task->description}}</td>
											<td>{{$task->updated_at}}</td>
											<td>{{$task->next_run}}</td>
											<td>{{$task->run_time}}</td>
											<td>{{($task->status)?'Enabled':'Disabled'}}</td>
											<td>
												@if($task->status)
												<a data-toggle="tooltip" title="Execute" href="#" data-task_id="{{$task->id}}" class="fa fa-play execute_task"></a>
												@endif
												<a data-toggle="tooltip" title="Edit" href="#" data-task_id="{{$task->id}}" class="fa fa-pencil-square-o editbtn edit_task"></a>
												<a data-toggle="tooltip" title="Delete" href="#" data-task_id="{{$task->id}}" class="fa fa-trash-o cancelbtn delete_task" ></a>
										
											</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="popup_wrapp"></div>
	<script type="text/javascript">
	$(document).ready(function(){
		$('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		$('.dataTables_filter input').attr("placeholder", "Search");

		$(document).on("click", '.edit_task, #add_task', function () {
			var task_id = $(this).data('task_id');
			$.ajax({
				type:"GET",
				url:base_url+'/manage-task/'+task_id,
				success:function(data){
					$('#popup_wrapp').html(data);
					$('#scheduledTask').modal('show');
				}
			})
		});

		$(document).on("click", '.delete_task', function () {
			if(confirm("Do you really want to delete this Scheduled Task?")){
				var task_id = $(this).data('task_id');
				$.ajax({
					type:"POST",
					url:base_url+'/delete-task',
					headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
					data:{task_id:task_id},
					success:function(){
						$('#task_'+task_id).fadeOut(1000);
						$('#alert-status').html('<div class="alert alert-success" id="success">Deleted successfully</div>');
					}
				});
			}
		});

		$(document).on("click", '.execute_task', function () {
			if(confirm("Do you really want to execute this task?")){
				var task_id = $(this).data('task_id');
				$.ajax({
					type:"POST",
					url:base_url+'/execute-task',
					headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
					data:{task_id:task_id},
					success:function(){						
						location.reload();
					}
				});
			}
		});

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>
@endsection