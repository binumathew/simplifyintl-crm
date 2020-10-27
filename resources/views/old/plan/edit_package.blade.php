@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Update Sim Plan</h1>
				</div>
				<form action="{{ url('save-package') }}" method="post" id="form">					
					@csrf	
					<input type="hidden" name="id" value="{{ $package->id }}">
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Plan Name</i>
									<input type="text" class="form-control" name="plan_name" required value="{{ $package->plan_name }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Description</i>
									<input type="text" name="description" value="{{ $package->description }}" required>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Buy Price</i>
									<input type="text" name="buy_price" value="{{ $package->buy_price }}" required>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Sell Price</i>
									<input type="text" name="sell_price" value="{{ $package->sell_price }}" required>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Data Limit</i>
									<input type="text" name="data_limit" value="{{ $package->data_limit }}" required>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Call Limit</i>
									<input type="text" name="call_limit" value="{{ $package->call_limit }}" required>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Int. Call Limit</i>
									<input type="text" name="in_call_limit" value="{{ $package->in_call_limit }}" required>
								</div>
							</div>
							<!-- <div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Message Limit</i>
									<input type="text" name="msg_limit" required>
								</div>
							</div> -->							
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>No Of Sim</i>
									<input type="text" name="sim_count" value="{{ $package->sim_count }}" required>
								</div>
							</div>
							
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Plan Duration</i>
									<input type="text" name="period" value="{{ $package->period }}" required>
								</div>
							</div>
							
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Plan ID</i>
									<select name="plan_id">
										@foreach($plans as $plan)
										<option value="{{$plan->id}}" {{ ($plan->id == $package->plan_id)? 'selected': '' }}>{{ $plan->plan_name }}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Featured</i>									
									<select name="featured">
										<option value="1" {{ ($package->featured ==1)?'selected':'' }}>Featured</option>
										<option value="0" {{ ($package->featured ==0)?'selected':'' }}>Standard</option>
									</select>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Status</i>
									<select name="status">
										<option value="1" {{ ($package->status ==1)?'selected':'' }}>Enabled</option>
										<option value="0" {{ ($package->status ==0)?'selected':'' }}>Disabled</option>
									</select>
								</div>
							</div>
						</div>
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

						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<a class="cancel_btn" href="{{ url('package-management') }}">CANCEL</a>
									<input name="" type="submit" class="greenbtn" value="SUBMIT">
								</div>
							</div>
						</div>									
					</div>
				</form>
			</div>
			<div class="col-md-4">
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		$('#form').validate({
			errorClass: "form-error",
			rules: {
				first_name: 'required',
				email: {
					required: true,
					email: true
				},
				last_name: 'required',
			},
		});      
	});
</script>

@endsection