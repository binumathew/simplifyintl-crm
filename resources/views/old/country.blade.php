@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Edit Country</h1>
				</div>
				@if ($errors->any())
				    <div class="alert alert-danger">
				        <ul>
				            <li>{{$errors->first()}}</li>
				        </ul>
				    </div>
				@endif
				<form action="{{ url('update-country', [$country->id]) }}" method="post" id="form">
					{{csrf_field()}}
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Country Name</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="country_name" value="{{ $country->country_name}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Country Code</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="country_code" value="{{ $country->country_code}}">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Short Code</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="short_code" value="{{ $country->short_code}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Dial Code</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="dial_code" value="{{ $country->dial_code}}">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Send OTP through</i>
									<select name="otp_type" id="otp_type">
										<option value="1" @php if($country->otp_type == 1) echo 'selected'; @endphp>SMS</option>
										<option value="0" @php if($country->otp_type == 0) echo 'selected'; @endphp>Call</option>
									</select>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Switch</i>
									<select name="switch" id="switch">
										@foreach ($switch_template as $switch)
											<option value="{{$switch->id}}" @php if($country->switch_id == $switch->id) echo 'selected'; @endphp>{{$switch->currency}}</option>
										@endforeach
									</select>
								</div>
							</div>							
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Access Number</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="access_number" value="{{ $country->access_number}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Has Access Number Support?</i>
									<select name="access_number_support" id="access_number_support">
										<option value="1" @php if($country->accessnumber_support == 1) echo 'selected'; @endphp>Yes</option>
										<option value="0" @php if($country->accessnumber_support == 0) echo 'selected'; @endphp>No</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Has Callback Support?</i>
									<select name="callback_support" id="callback_support">
										<option value="1" @php if($country->callback_support == 1) echo 'selected'; @endphp>Yes</option>
										<option value="0" @php if($country->callback_support == 0) echo 'selected'; @endphp>No</option>
									</select>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Has Wifi Support?</i>
									<select name="wifi_support" id="wifi_support">
										<option value="1" @php if($country->wifi_support == 1) echo 'selected'; @endphp>Yes</option>
										<option value="0" @php if($country->wifi_support == 0) echo 'selected'; @endphp>No</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Has Conference Support?</i>
									<select name="conference_support" id="conference_support">
										<option value="1" @php if($country->conference_support == 1) echo 'selected'; @endphp>Yes</option>
										<option value="0" @php if($country->conference_support == 0) echo 'selected'; @endphp>No</option>
									</select>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Is popular?</i>
									<select name="popular" id="is_popular">
										<option value="1" @php if($country->popular == 1) echo 'selected'; @endphp>Yes</option>
										<option value="0" @php if($country->popular == 0) echo 'selected'; @endphp>No</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Landline Price</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="land_price" value="{{ $country->land_price}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Mobile Price</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="mob_price" value="{{ $country->mob_price}}">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Currency</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="currency" value="{{ $country->currency}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Currency Symbol</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="currency_symbol" value="{{ $country->currency_symbol}}">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Tax</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="tax" value="{{ $country->tax}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Status</i>
									<select name="status" id="country_status">
										<option value="1" @php if($country->status == 1) echo 'selected'; @endphp>Active</option>
										<option value="0" @php if($country->status == 0) echo 'selected'; @endphp>Inactive</option>
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<a class="cancel_btn" href="{{ url('/countries') }}">CANCEL</a>
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

			errorClass: "my-error-class",

			rules: {

				country_name: 'required',

				country_code: 'required',

				short_code: 'required',
				dial_code: 'required',
				currency: 'required',
				currency_symbol: 'required',
				
			},

			
		});      

	});


</script>

@endsection