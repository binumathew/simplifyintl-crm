@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<!-- <div class="Cleftpart">
						<h1>Plan Purchase</h1>
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
									<li class="active"><a href="">Plan Purchase</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
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
											<th>Plan Name</th>
											<th>Provider</th>											
											<th>Price</th>
											<th>Data Limit</th>
											<th>Call Limit</th>	
											<th>In. Call Limit</th>							
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										@php 
										$i = 1;
										$payment = json_decode(Auth::user()->payment_mode);
										@endphp
										@if($parking && in_array('cash',$payment))
										<!-- <tr id="1">
											<td>{{$parking->plan_name}}</td>							
											<td>{{$parking->sell_price}}</td>
											<td>{{$parking->data_limit}}</td>
											<td>{{$parking->call_limit}}</td>
											<td>{{$parking->in_call_limit}}</td>								
											<td>
											<a data-toggle="tooltip" title="Buy"  href="{{ url('/billing',Crypt::encrypt('plan:'.$parking->id.'~'.$user)) }}" class="fa">Buy</a>									
											</td>
										</tr> -->
										@php $i++; @endphp
										@endif										
										@foreach ($plans as $plan)
										<tr id="{{$i}}">
											<td>{{$plan->plan_name}}</td>
											<td>{{$plan->provider}}</td>						
											<td>{{$plan->sell_price}}</td>
											<td>{{$plan->data_limit}}</td>
											<td>{{$plan->call_limit}}</td>
											<td>{{$plan->in_call_limit}}</td>									
											<td>
											<a data-toggle="tooltip" title="Buy"  href="{{ url('/billing',Crypt::encrypt('plan:'.$plan->id.'~'.$user)) }}" class="fa">Buy</a>									
											</td>
										</tr>
										<?php 
										$i++; ?>
										@endforeach
										@foreach ($packages as $package)
										<tr id="{{$i}}">
										<td>{{$package->plan_name.'-'.$package->sim_count}}</td>
										<td>EE</td>					
										<td>{{$package->sell_price}}</td>
										<td>{{$package->data_limit}}</td>
										<td>{{$package->call_limit}}</td>
										<td>{{$package->in_call_limit}}</td>										
										<td>

										<a data-toggle="tooltip" title="Buy"  href="{{ url('/billing',Crypt::encrypt('bundle:'.$package->id.'~'.$user)) }}" class="fa">Buy</a>
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