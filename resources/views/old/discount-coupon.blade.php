@extends('layouts.home')
@section('content')
	<div class="Cwrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<!-- <div class="Cleftpart">
						<h1>Discount Coupons</h1>						
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
							<div class="col-md-9 col-sm-7">
								<ul class="Ulinenav clearfix">
									<li class="active"><a href="">Discount Coupons</a></li>
									<!--<li><a href="#">Contact Groups</a></li>-->
								</ul>
							</div>
							<div class="col-md-3 col-sm-5">
								<ul class="EAbtnbg">
									<!-- <li class="expo">
										<a href="importcontactview">IMPORT<i class=" fa fa-file-excel-o"></i></a>
									</li> -->
									<li><a href="add-discount-coupon">ADD&nbsp;DISCOUNT COUPON</a></li>
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
											<th>Coupon Code</th>											
											<th>Type</th>	
											<th>Value</th>
											<th>Expires On</th>	
											<th>Status</th>													
											<th>Action</th>				
										</tr>
									</thead>
									<tbody>
										<?php
										$i=1; ?>
										@foreach ($discount_coupon as $coupon)
										<tr id="{{$i}}">
											<td>{{$coupon->coupon_code}}</td>						
											<td>@php echo $coupon->is_fixed == 1 ? 'Fixed Amount' : 'Percentage' @endphp</td>
											<td>{{ $coupon->discount_value }}</td>
											<td>{{ $coupon->expiry_date }}</td>
											<td>@php echo $coupon->status == 1 ? 'Active' : 'Inactive' @endphp</td>
											<td>																				
												@php
												$parameter= Crypt::encrypt($coupon->id);
												@endphp
												
												<a data-toggle="tooltip" title="Edit"  href="edit-discount-coupon/{{$parameter}}" class="fa fa-pencil-square-o editbtn"></a>
												<a data-toggle="tooltip" title="Delete" href="#" class="fa fa-trash-o cancelbtn delete_contact" list-id={{$i}} user-id="{{$parameter}}" ></a>
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