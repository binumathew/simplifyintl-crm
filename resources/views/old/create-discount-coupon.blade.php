@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Add Discount Coupon</h1>
				</div>
				@if ($errors->any())
				    <div class="alert alert-danger">
				        <ul>
				            <li>{{$errors->first()}}</li>
				        </ul>
				    </div>
				@endif
				<form action="{{ url('save-discount-coupon') }}" method="post" id="form">
					{{csrf_field()}}
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Coupon Code</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="coupon_code" value="">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Coupon Type</i>
									<select name="coupon_type" id="coupon_type">
										<option value="1" >Fixed Amount</option>
										<option value="0" >Percentage</option>
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Discount Value</i>
									<input type="text" class="form-control" aria-describedby="basic-addon1" name="discount_value" value="">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Expires On</i>
									<input type="text" class="form-control customdate" name="expiry_date" id="expiry_date" placeholder="Expires On" value="">
								</div>
							</div>
							
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Status</i>
									<select name="coupon_status" id="coupon_status">
										<option value="1" >Active</option>
										<option value="0" >Inactive</option>
									</select>
								</div>
							</div>
							
						</div>

						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<a class="cancel_btn" href="{{ url('/discount-coupon') }}">CANCEL</a>
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

				coupon_code: 'required',

				discount_value: 'required',

				expiry_date: "required"

			},

		});  

		$('.customdate').datetimepicker({
	        format: 'YYYY-MM-DD'
	    });    

	});

	

</script>

@endsection