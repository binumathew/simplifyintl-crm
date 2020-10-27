@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Role Permission</h1>
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
							<div class="col-md-8 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Permission Management</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
							<div class="col-md-4 col-sm-5">
							<ul class="EAbtnbg">
								<li class="expo">
									<!-- <a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a> -->
								</li>
								<li><!-- <a href="addcontact">ADD ROLE</a> --></li>
							</ul>
							</div>
						</div>
					</div>   

					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-12">
								<form action="{{ url('/update-permission') }}" method="post">
                                    @csrf
                                    <div class="col-md-6">
	                                    <div class="form-group">
	                                    	<label for="name" class="control-label">Role Name</label>
	                                    	<input type="hidden" name="role_id" value="{{$role->id}}">
	                                    	<input type="text" id="name" name="name" class="form-control" autofocus="1" value="{{$role->name}}">
	                                    </div>               
                                	</div>
                                	<div class="col-md-6">
	                                    <div class="form-group">
	                                    	<label for="name" class="control-label">Short Code</label>
	                                    	<input type="text" id="short_code" name="short_code" class="form-control" autofocus="1" value="{{$role->short_code}}">
	                                    </div>               
                                	</div>

									<table class="table">
				                     	<thead>
					                        <tr>
					                           <th class="bold">Permission</th>
					                           <th class="text-center bold">View</th>				
					                           <th class="text-center bold">Create</th>
					                           <th class="text-center bold">Edit</th>
					                           <th class="text-center text-danger bold">Delete</th>
					                        </tr>
				                     	</thead>
				                     	<tbody>
				                     	@foreach($permissions as $permission)	
				                     		@php 				                     		
				                     			$view_checked = ($permission->can_view)?'checked':'';
				                     			$create_checked = ($permission->can_create)?'checked':'';
				                     			$edit_checked = ($permission->can_edit)?'checked':'';
				                     			$delete_checked = ($permission->can_delete)?'checked':'';
				                     		@endphp
				                     		<tr>				                     			
				                           		<td> 
				                           			{{ $permission->name }} 				
				                           			<input type="hidden" name="permission_id[]" value="{{$permission->permission_id}}">
				                           		</td>
				                           		<td class="text-center">
                                                  	<div class="checkbox">
					                                 	<input type="checkbox" name="permission_view[{{$permission->permission_id}}]" value="1" {{$view_checked}}>
					                                 	<label></label>
				                              		</div>
                                             	</td>
				                           		<td class="text-center">
                                                    <div class="checkbox">
				                                 		<input type="checkbox" name="permission_create[{{$permission->permission_id}}]" value="1" {{$create_checked}}>
				                                 		<label></label>
				                              		</div>
                                             	</td>
				                           		<td class="text-center">
				                                    <div class="checkbox">
				                                 		<input type="checkbox" name="permission_edit[{{$permission->permission_id}}]" value="1" {{$edit_checked}}>
				                                 		<label></label>
				                              		</div>
                                             	</td>
				                           		<td class="text-center">
                                                    <div class="checkbox checkbox-danger">
				                                 		<input type="checkbox" name="permission_delete[{{$permission->permission_id}}]" value="1" {{$delete_checked}}>
				                                 		<label></label>
				                              		</div>
                                             	</td>
				                        	</tr>
				                        @endforeach
				                        </tbody>
				                  	</table>
				                  	<button type="submit" class="btn btn-info pull-right">Save</button>
			                  	</form>  
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal hide" id="addBookDialog">
			<div class="modal-header">
				<button class="close" data-dismiss="modal">×</button>
				<h3>Modal header</h3>
			</div>
			<div class="modal-body">
				<p>some content</p>
				<input type="text" name="bookId" id="bookId" value=""/>
			</div>
		</div>
	</div>

	<script type="text/javascript">
	$(document).ready(function(){
		$('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		$('.dataTables_filter input').attr("placeholder", "Search");

		// $(document).on("click", ".open-AddBookDialog", function () {
		// 	var myBookId = $(this).data('id');
		// 	$(".modal-body #bookId").val( myBookId );         
		// 	// $('#addBookDialog').modal('show');
		// });
		// if(confirm("Do you really want to delete this contact ?")){
		// 	$.ajax({
		// 		type:"POST",
		// 		url:'deletecontact',
		// 		headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
		// 		data:{id:id},
		// 		success:function(){
		// 			$('#'+list_id).fadeOut(1000);
		// 		}
		// 	})
		// }

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>
@endsection