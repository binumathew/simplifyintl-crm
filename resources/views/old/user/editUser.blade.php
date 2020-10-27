@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Edit User</h1>
				</div>
				<form action="{{ url('update-user', [$user->id]) }}" method="post" id="form">
					{{csrf_field()}}
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>First Name</i>
									<div class="input-group">
										<span class="input-group-addon" id="basic-addon1">
											<em class="fa fa-user-o"></em>
										</span>
										<input type="text" class="form-control" aria-describedby="basic-addon1" name="first_name" value="{{ $user->first_name}}">
									</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox"><i>Last Name</i><input type="text" name="last_name" value="{{ $user->last_name}}"></div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Phone No</i>
									<input type="text" name="phone_number" value="{{ $user->phone}}" class="input_disabled" disabled>
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Email</i>
									<input type="text" name="email" value="{{ $user->email}}">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Postal Code</i>
									<input type="text" id="user_updt_postal_code" name="postal_code" value="{{ $userData->postal_code}}">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>House No</i>
									<input type="text" id="user_updt_house_no" name="house_no" value="">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>.</i>
									<button type="button" id="user_updt_gt_adrs" class="greenbtn">Get Address</button>
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
									<input type="text" id="usr_updt_adrs" name="address" value="{{ $userData->address}}">
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>City</i>
									<input type="text" id="usr_updt_city" name="city" value="{{ $userData->city}}">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Country</i>
									<input type="text" name="country" id="usr_updt_country" value="{{ $user->country->country_name}}" class="input_disabled" disabled>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>User Status</i>
									<select name="user_status" id="user_status">
										<option value="1" @php if($user->status == 1) echo 'selected'; @endphp>Active</option>
										<option value="0" @php if($user->status == 0) echo 'selected'; @endphp>Inactive</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<input type="hidden" name="from_user_detail" value="0">
									<a class="cancel_btn" href="{{ url('\users') }}">CANCEL</a>
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

				first_name: 'required',

				email: {

					required: true,

					email: true

				},

				last_name: 'required',


				phone_number: {

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
			$('#loadingsign').show();
			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: 'POST',                                                
				url: "<?= url('get-address') ?>",
				data: {postal_code:postal_code,house_no:house_no},
				success:function(data){	
					$('#loadingsign').hide();
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