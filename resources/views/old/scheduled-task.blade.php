<div id="scheduledTask" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title">{{ isset($task)?'Edit':'Add'}} Scheduled Task</h3>
			</div>
			<div class="modal-body">	
			<form action="{{ url('/save-task')}}" method="post" id="task-form">
				@csrf
				<div class="form-group">
					<label>Description</label>
					@if(isset($task))
					<input type="hidden" id="task_id" name="id" value="{{$task->id}}">
					@endif
					<input type="text" id="description" class="form-control" name="description" value="{{isset($task)?$task->description:''}}" required>
				</div>
				<div class="form-group">
					<label>Command</label>									
					<input type="text" id="command" class="form-control" name="command" value="{{isset($task)?$task->command:''}}" required>
				</div>	
				<div class="form-group">
					<label>Status</label>									
					<select name="status" id="status" class="form-control">
						<option value="1" @php if(!isset($task) || $task->status == 1) echo 'selected'; @endphp>Enabled</option>
						<option value="0" @php if(isset($task) && $task->status == 0) echo 'selected'; @endphp>Disabled</option>
					</select>
				</div>	

				<div id="task_error" class="form-error"></div>									
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-success">Submit</button>
			</form>			
			</div>
		</div>
	</div>
</div>
