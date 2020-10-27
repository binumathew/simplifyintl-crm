@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Update Sim Plan</h1>
				</div>
				<form action="{{ url('save-plan') }}" method="post" id="form">					
					@csrf	
					<input type="hidden" name="id" value="{{ $plan->id }}">
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Plan Name</i>
									<input type="text" class="form-control" name="plan_name" value="{{ $plan->plan_name }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Description</i>
									<input type="text" name="description" value="{{ $plan->description }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Buy Price</i>
									<input type="text" name="buy_price" value="{{ $plan->buy_price }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Sell Price</i>
									<input type="text" name="sell_price" value="{{ $plan->sell_price }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Data Limit</i>
									<input type="text" name="data_limit" value="{{ $plan->data_limit }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Call Limit</i>
									<input type="text" name="call_limit" value="{{ $plan->call_limit }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Int. Call Limit</i>
									<input type="text" name="in_call_limit" value="{{ $plan->in_call_limit }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Message Limit</i>
									<input type="text" name="msg_limit" value="{{ $plan->msg_limit }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Switch Plan ID</i>
									<input type="text" name="switch_billing_plan" value="{{ $plan->switch_billing_plan }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Sim Plan ID </i>
									<input type="text" name="sim_billing_plan" value="{{ $plan->sim_billing_plan }}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Plan Duration</i>
									<input type="text" name="period" value="{{ $plan->period }}">
								</div>
							</div>
							
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Status</i>
									<select name="status">
										<option value="1" {{ ($plan->status ==1)?'selected':'' }}>Active</option>
										<option value="0" {{ ($plan->status ==0)?'selected':'' }}>InActive</option>
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
									<a class="cancel_btn" href="{{ url('plan-management') }}">CANCEL</a>
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