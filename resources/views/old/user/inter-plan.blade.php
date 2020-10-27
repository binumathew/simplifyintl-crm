@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="Cleftpart">
					<h1>Purchase International Plan</h1>
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
					</div>
				</div>   
				@csrf
				<div class="Paymentsbg ctablebg clearfix">
					<div class="row">
						<div class="col-md-8">
							<div class="col-md-12">
							<table id="userBasicDetail" class="table table-stripped">
									<tr>
										<th>Name</th>
										<td>{{ $user->name }}</td>
										<th>Email</th>
										<td>{{ $user->email }}</td>
									</tr>
									<tr>
										<th>Phone</th>
										<td>{{ $user->phone }}</td>
										<th>Address</th>
										<td>{{ $user->userDetail->address }}</td>
									</tr>
									<tr>
										<th>City</th>
										<td>{{ $user->userDetail->city }}</td>
										<th>Country</th>
										<td>{{ $user->country->country_name }}</td>
									</tr>
								</table>
							</div>
							<div class="col-md-12">
							<form action="{{ url('save-interplan') }}" method="post" id="interplan-form">					
							@csrf
							<input type="hidden" name="plan_id" value="{{ Crypt::encrypt($plan->id)

						}}">
						<input type="hidden" name="user_id" value="{{ Crypt::encrypt($user->id)

						}}">
							<div class="col-md-6">
							<div class="popTbox">
								<label>Choose Credit Card</label>
								<select id="credit_card" class="form-control" name="credit_card" required>
									<option value="" selected>choose</option>
									@foreach($credit_cards as $cards)
										<option value="{{ $cards->id }}" {{($cards->is_default)?'selected':''}}>{{ $cards->card_type }}</option>
									@endforeach
								</select>
							</div>
							</div>
							<br>
							<div class="col-md-2">
								<input name="" type="submit" class="greenbtn" value="Buy">
							</div>
							</form>
						</div>
						</div>
						<div class="col-md-4">
							<div class="Cleftpart">					
								<h4>Purchase Details</h4>
								<div class="Paymentsbg ctablebg clearfix">
									<div class="buybox">
										<div class="row">
											<div class="col-md-8 col-xs-7">
												<b>{{ $plan->product_name }} </b>
												<i>{{ $plan->period }} Days</i>										
											</div>
											<div class="col-md-4 col-xs-5 text-right">
												<h4><b>{{ $currency }}{{ $plan->sell_price }}</b></h4>
											</div>
										</div>								
									</div>
									<div class="buybox">
											<div class="row">
												<div class="col-md-8 col-xs-7">
													<b>{{ $plan->in_call_limit }} International Mins</b>
												</div>
												<div class="col-md-4 col-xs-5 text-right">
													<h4>Free</h4>
												</div>
											</div>
									</div>
									<div class="buybox">
										<div class="row">
											<div class="col-md-8 col-xs-7">
												<b>Grand Total</b>
											</div>
											<div class="col-md-4 col-xs-5 text-right">
												<h4>{{ $currency }}{{ $plan->sell_price }}</h4>
											</div>
										</div>
									</div>
								</div>					
							</div>
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

		$(document).on('click','.delete_user',function(){
          var id = $(this).attr('user-id'); 
          if(confirm("Do you really want to delete this contact ?")){
            $('#delete_user_'+id).submit();
          }
        });


		setTimeout(function() {
			$('#success').fadeOut('fast');
		}, 5000);
	});
</script>
@endsection