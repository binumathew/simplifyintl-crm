@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="Cleftpart">
					<h1>Roles</h1>
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
								<li class="active"><a href="">Role Management</a></li>
								<!--<li><a href="#">Contact Groups</a></li>-->
							</ul>
						</div>
						<div class="col-md-4 col-sm-5">
							<ul class="EAbtnbg">
								<li class="expo">
									<!-- <a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a> -->
								</li>
								<li><button type="button" class="btn btn-success" id="addRole_popup_btn" data-toggle="modal" data-target="#addRoleModal">Add Role</button></li>
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
										<th>Sl No</th>											
										<th>Role Name</th>
										<th>Short Code</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i=1; ?>
									@foreach ($roles as $role)
									<tr id="{{$i}}">
										<td>{{$i}}</td>							
										<td>{{$role->name}}</td>
										<td>{{$role->short_code}}</td>
										<td>
											@php
											$parameter= Crypt::encrypt($role->id);
											@endphp

											<a data-toggle="tooltip" title="Edit"  href="edit-permission/{{$parameter}}" class="fa fa-pencil-square-o editbtn"></a>
											<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_role" data-id={{$parameter}}></a>
										</td>
									</tr>
									<?php 
									$i++; ?>
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

<div id="addRoleModal" class="modal fade avoopopup" role="dialog">
	<div class="modal-dialog mt-7p">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h3 class="modal-title" id="addRoleModalTitle">Add Role</h3>
			</div>
			<div class="modal-body">
				
				<div class="form-out-group">
					<label>Role Name</label>
					<input type="text" name="role_name" id="role_name" class="form-input" placeholder="Role Name">
				</div>
				<div class="form-out-group">
					<label>Short Code</label>
					<input type="text" name="short_code" id="short_code" class="form-input" placeholder="Short Code">
				</div>

				<div id="addRole_Error" class="alert alert-danger displayNone"></div>
				<button type="button" id="add_role_btn" class="btn btn-success">Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>
		</div>

	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('#contacttable').DataTable({responsive: true,"bSort" : false,language: { search: "" },});
		$('.dataTables_filter input').attr("placeholder", "Search");

		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);

		$(document).on('click','#add_role_btn',function (e) {
			var role_name = $('#role_name').val();
			var short_code = $('#short_code').val();
			if (role_name == '') {
				$('#addRole_Error').text('Please enter a role name');
				$('#addRole_Error').show();
				setTimeout(function(){
					$('#addRole_Error').hide();
				}, 3000);
			} 
			else if (short_code == '') {
				$('#addRole_Error').text('Please enter short code for the role');
				$('#addRole_Error').show();
				setTimeout(function(){
					$('#addRole_Error').hide();
				}, 3000);
			}
			else {
				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					type: 'POST',                                                
					url: 'add-role',
					data: {role_name:role_name, short_code: short_code},
					success:function(data){	
						if (data.error) {
							$('#addRole_Error').text(data.message);
							$('#addRole_Error').show();
							setTimeout(function(){
								$('#addRole_Error').hide();
							}, 3000);
						} else {
							location.reload();
						}
					}
				});
			}
			
		});

	});
</script>
@endsection