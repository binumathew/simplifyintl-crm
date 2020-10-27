@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="Cleftpart">
						<h1>Settings</h1>
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
									<li class="active"><a href="">Settings</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
							<div class="col-md-4 col-sm-5">
							<ul class="EAbtnbg">
								<!-- <li class="expo">
									<a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a>
								</li> -->
								<li><a href="addsettings">ADD&nbsp;SETTINGS</a></li>
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
											<th>Name</th>											
											<th>Value</th>
											<th>Auto Load</th>												
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i=1; ?>
										@foreach ($options as $option)
										<tr id="{{$i}}">
										<td>{{$option->name}}</td>							
										<td>{{$option->value}}</td>
										<td>{{$option->auto_load}}</td>										
										<td>
										
										
										@php
										$parameter= Crypt::encrypt($option->id);
										@endphp
										
										<a data-toggle="tooltip" title="Edit"  href="addsettings/{{$parameter}}" class="fa fa-pencil-square-o editbtn"></a>
										<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_settings" list-id={{$i}} data-id="{{$parameter}}" ></a>
										<input type="hidden" id="test" value="">
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

		$(document).on("click", ".delete_settings", function () {
			var id = $(this).attr('data-id');
			if(confirm("Do you really want to delete this settings ?")){
				$.ajax({
				type:"POST",
				url:'delete-settings',
				headers: { 'X-CSRF-TOKEN': $('input[name=_token]').val()  },
				data:{id:id},
				success:function(data){
					alert(data['message']);
					window.location.reload();
				}
				})
			}
		});
	});
</script>
@endsection