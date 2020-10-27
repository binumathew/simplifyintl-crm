@extends('layouts.home')

@section('content')

<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">
					<h1>Add User</h1>
				</div>
				<form action="{{ url('save-member') }}" method="post" id="form">
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
										<input type="text" class="form-control" aria-describedby="basic-addon1" name="first_name">
									</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox"><i>Last Name</i><input type="text" name="last_name"></div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Phone No</i>
									<input type="text" name="phone">
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Email</i>
									<input type="text" name="email">
								</div>
							</div>
						</div>
<!-- 						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Postal Code</i>
									<input type="text" id="user_updt_postal_code" name="postal_code">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>House No</i>
									<input type="text" id="user_updt_house_no" name="house_no">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>.</i>
									<button type="button" id="user_updt_gt_adrs" class="greenbtn">Get Address</button>
								</div>
							</div>
						</div> -->
						
						<!-- <div class="displayNone" id="usr_updt_adrs_error">
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
									<input type="text" id="usr_updt_adrs" name="street">
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>City</i>
									<input type="text" id="usr_updt_city" name="city">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Country</i>
									<input type="text" name="country" id="usr_updt_country">
								</div>
							</div>							
						</div> -->
						<div class="row">
							<div class="col-md-12">
								<div class="addbtnsbg">
									<input type="hidden" name="parent_id" value="{{ $parent_id }}">
									<a class="cancel_btn" href="{{ url('/show-user') }}">CANCEL</a>
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
		$.validator.addMethod(
	        "regex",
	        function(value, element, regexp) {
	            var re = new RegExp(regexp);
	            return this.optional(element) || re.test(value);
	        },
	        "Please check your input."
		);

        $.validator.setDefaults({
            ignore: []
        });

		$('#form').validate({
			errorClass: "invalid form-error",
			rules: {                            
                    first_name:'required',
                    last_name:'required',
                    email: {
                    	required: true, 
                    	email: true
                    },
                    phone: {
                        number: true,
                        required: true, 
                        minlength:8,
                        maxlength:11,
                        regex:/^(((0))[0-9]{10})$/,
                    },                        
                    // postal_code: {                    	
                    // 	regex:/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})$/
                    // },
                    // street: 'required',
                    // city: 'required',
                    // country: 'required',                  
                },
                messages: {
                    first_name: "Enter your first name!",
                    last_name: "Enter your last name!",
                    contact_number: {
                    	required:"Enter valid phone number!",
                    	regex:"Enter phone number leading with 0",
                    },
                    // postal_code: {
                    // 	// required:"Enter your postal code!",
                    // 	regex:"Invalid Postalcode",               
                    // }
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