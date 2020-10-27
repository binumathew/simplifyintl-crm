@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<!-- <div class="Cleftpart">
						<h1>Port Request</h1>
						<span>Home  |  My Avoo  | Contacts </span> 
					</div> -->
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
							<div class="col-md-12 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Porting Request</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
							<!-- <div class="col-md-4 col-sm-5">
							<ul class="EAbtnbg">
								<li class="expo">
									<a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a>
								</li>
								<li><a href="{{ url('create-plan') }}">ADD&nbsp;PLAN</a></li>
							</ul>
							</div> -->
						</div>
					</div>   
					@csrf
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-12">
								<table id="contacttable" class="display responsive no-wrap" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Email</th>
											<th>Temporary No</th>
											<th>Reference ID</th>
											<th>PAC Number</th>
											<th>No To Keep</th>
											<th>Expected Date</th>
											<th>Status</th>	
											<th>Requested On</th>	
											<th>Action</th>
										</tr>
									</thead>
									<tbody>										
										@foreach ($data as $port)
										@php  
										$avoo_num = ($port->status==4) ? $port->stock->temp_number:$port->stock->phone_number;
										@endphp
										<tr>
											<td>{{ $port->list->sim_request->user->email }}</td>
											<td>{{'0'.ltrim($avoo_num, '44')}}</td>
											<td>{{$port->reference_id}}</td>
											<td>{{$port->pac_number}}</td>
											<td>{{$port->porting_to}}</td>
											<td>{{$port->expected_date}}</td>
											<td>
												@switch($port->status)
													@case(0)
														Request Received
													@break
													@case(1)
														Request Initiated
													@break
													@case(2)
														Request Confirmed
													@break
													@case(3)
														Processed
													@break
													@case(4)
														Process Completed
													@break
													@case(5)
														Request Canceled
													@break
												@endswitch												
											</td>
											<td>{{date('d-m-Y', strtotime($port->created_at))}}</td>
											<td>
												@if($port->list->reg_status != 0 && $port->status != 4)
												<a data-toggle="tooltip" title="Edit"  href="{{url('/edit-port-request',$port->id)}}" class="fa fa-pencil-square-o editbtn"></a>
												@endif
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