@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">					
					<h1>Edit Dealer</h1>					
				</div>
				<div class="Unavbg">
					<div class="row">
						@if(session()->has('message'))
						<div class="alert alert-success">
							{{ session()->get('message') }}
						</div>
						@endif
						<div class="col-md-12">
							<ul class="Ulinenav clearfix">
								<li class="active"><a href="#">Edit Contacts</a></li>
							</ul>
						</div>
					</div>
				</div>
				<form action="{{ url('dealer') }}" method="post" id="form">
					{{csrf_field()}}
					<input type="hidden" name="dealer_id" value="{{ $dealer->id }}">
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>First Name</i>
									<div class="input-group">
										<span class="input-group-addon" id="basic-addon1">
											<em class="fa fa-user-o"></em>
										</span>
										<input type="text" class="form-control" aria-describedby="basic-addon1" name="first_name" value="{{ $dealer->first_name}}">
									</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox"><i>Last Name</i><input type="text" name="last_name" value="{{ $dealer->last_name}}"></div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Phone No</i>
									<input type="text" name="telephone" value="{{ $dealer->telephone}}">
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Email</i>
									<input type="text" name="email" value="{{ $dealer->email}}">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Document Type</i>
									<input type="text" name="doc_type" value="{{ $dealer->doc_type}}" class="input_disabled" disabled>
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Document Number</i>
									<input type="text" name="doc_number" value="{{ $dealer->doc_number}}" class="input_disabled" disabled>
								</div>
							</div>
						</div>
						<input type="hidden" name="doc_type" value="{{ $dealer->doc_type}}">
						<input type="hidden" name="doc_number" value="{{ $dealer->doc_number}}">

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Postal Code</i>
									<input type="text" id="user_updt_postal_code" name="postal_code" value="{{ $dealer->postal_code}}">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>House No</i>
									<input type="text" id="user_updt_house_no" name="house_no" value="{{$dealer->house_no}}">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>.</i>
									<button type="button" id="user_updt_gt_adrs" class="greenbtn dull">Get Address</button>
								</div>
							</div>
						</div>
						
						<div class="displayNone" id="usr_updt_adrs_error">
							<div class="invalid form-error" id="usr_updt_adrs_error-msg"></div>
						</div>
						<div class="displayNone row" id="usr_updt_adrs_select_hldr">
							<div class="col-sm-12">
								<div class="popTbox">
									<select id="usr_updt_adrs_select_box"></select>
								</div>								
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Address</i>
									<input type="text" id="usr_updt_adrs" name="street" value="{{ $dealer->street}}">
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>City</i>
									<input type="text" id="usr_updt_city" name="city" value="{{ $dealer->city}}">
								</div>
							</div>	

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>State</i>
									<input type="text" id="usr_updt_state" name="state" value="{{ $dealer->state}}">
								</div>
							</div>	
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Comment</i>
									<input type="text" name="comments"  value="{{ $dealer->comments}}" >
								</div>
							</div>
							<!-- <div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Nationality</i>
									<input type="text" name="nationality" value="{{ $dealer->nationality}}" class="input_disabled">
								</div>
							</div> -->											
						</div>

						<!-- <div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Country ID</i>
									<input type="text" name="country_id"  value="{{ $dealer->country_id}}" class="input_disabled">
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Language</i>
									<input type="text" name="language" value="{{ $dealer->language}}"  class="input_disabled">
								</div>
							</div>													
						</div> -->
						<input type="hidden" name="customer_id" value="{{ $dealer->customer_id}}">
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="addbtnsbg">
								<a class="greenbtn dull" href="{{ url('dealers') }}">CANCEL</a>
								<input name="" type="submit" class="greenbtn" value="SUBMIT">
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

<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>

<script>

	$(document).ready(function () {

		$('#form').validate({
			errorClass: "my-error-class",
			rules: {
				first_name: 'required',
				last_name: 'required',
				email: {
					required: true,
					email: true
				},				
				telephone: {
					minlength:5,
					maxlength:11,
					required: true
				},
			},
			messages: {
				phone_number: "Enter valid phone number"
			}
		});      
	});

	$(document).on('click','#user_updt_gt_adrs',function (e) {
		var postal_code = $('#user_updt_postal_code').val();
		var house_no = $('#user_updt_house_no').val();
		if (postal_code == '') {
			$('#usr_updt_adrs_error-msg').text('Please enter postal code');
			$('#usr_updt_adrs_error').show();
			setTimeout(function(){
				$('#usr_updt_adrs_error').hide();
			}, 3000);
		} else {
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: "<?= url('get-address') ?>",
				data: {postal_code:postal_code,house_no:house_no},
				success:function(data){	
					if (data.error) {
						if (data.type == 1) {
							$('#usr_updt_adrs_select_hldr').show();
							$('#usr_updt_adrs_select_box').html(data.list);
							$('#usr_updt_adrs_error').show();
							$('#usr_updt_adrs_error-msg').text(data.message);
						} else if (data.type == 1) {
							$('#usr_updt_adrs_select_hldr').hide();
							$('#usr_updt_adrs_error').show();
							$('#usr_updt_adrs_error-msg').text(data.message);
						}
					} else {
						$('#usr_updt_adrs_select_hldr').hide();
						$('#usr_updt_adrs_error').hide();
						
						var value = data.address;
						var result = value.split(' , ');
						if (result.length == 4) {
							$('#usr_updt_adrs').val(result[0]);
							$('#usr_updt_city').val(result[1]);
							//$('#usr_updt_state').val(result[2]);
							$('#usr_updt_country').val(result[2]);
						}
					}
				}
			});
		}
	});

	$(document).on('click','#usr_updt_adrs_select_box',function () {
		var value = $(this).val();
		var result = value.split(' , ');
		if (result.length == 4) {
			$('#usr_updt_adrs').val(result[0]);
			$('#usr_updt_city').val(result[1]);
			//$('#usr_updt_state').val(result[2]);
			$('#usr_updt_country').val(result[2]);
		}
	})

</script>

@endsection