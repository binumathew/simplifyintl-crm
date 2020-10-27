@extends('layouts.home')
@section('content')
<div class="Cwrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="Cleftpart">					
					<h1>Billing Information</h1>					
				</div>
				<form action="{{ url('/billing',$payload) }}" method="post" id="billing-form">
					@csrf					
					<div class="Paymentsbg ctablebg clearfix">
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>First Name</i>
									<div class="input-group">
										<span class="input-group-addon" id="basic-addon1">
											<em class="fa fa-user-o"></em>
										</span>
										<input type="text" class="form-control" aria-describedby="basic-addon1" name="first_name" value="{{($user)?$user->first_name:''}}">
									</div>
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox"><i>Last Name</i><input type="text" name="last_name" value="{{($user)?$user->last_name:''}}"></div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Email</i>
									<input type="text" name="user_email" value="{{($user)?$user->email:''}}">
								</div>
							</div>
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Phone No</i>
									<input type="text" name="contact_number" value="{{($user)? str_replace('+44','0',$user->phone):''}}">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>House No</i>
									<input type="text" id="bill_house_no" name="house_no" value="{{($user)?$user->userDetail->house_no:''}}">
								</div>
							</div>
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>Postal Code</i>
									<input type="text" id="bill_postal_code" name="postal_code" value="{{($user)?$user->userDetail->postal_code:''}}">
									<div id="bill_postalcode_error"></div>
								</div>
								
							</div>
							
							<div class="col-md-3 col-sm-3">
								<div class="popTbox">
									<i>.</i>
									<button type="button" id="user_updt_gt_adrs" class="greenbtn dull find_address bill">Get Address</button>
								</div>
							</div>
						</div>
						<p class="bill_postcode_list hidden"></p>
<!-- 						<div class="displayNone" id="usr_updt_adrs_error">
							<div class="invalid form-error" id="usr_updt_adrs_error-msg"></div>
						</div>
						<div class="displayNone row" id="usr_updt_adrs_select_hldr">
							<div class="col-sm-12">
								<div class="popTbox">
									<select id="usr_updt_adrs_select_box"></select>
								</div>								
							</div>
						</div> -->
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Address</i>
									<input type="text" id="bill_street" name="street" value="{{($user)?$user->userDetail->address:''}}">
								</div>
							</div>

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>City</i>
									<input type="text" id="bill_city" name="city" value="{{($user)?$user->userDetail->city:''}}">
								</div>
							</div>	

							<div class="col-md-6 col-sm-6">
								<div class="popTbox">
									<i>Country</i>
									<input type="text" id="bill_country" name="country" value="{{($user)?$user->userDetail->state:''}}">
								</div>
							</div>				
					</div>
					<br>
					<div class="row">
					<div class="col-md-12">
							<input type="checkbox" id="shipping_address" name="shipping_address" value="1" checked>
						My shipping address is same as billing address
					</div>
					</div>
					<div class="shipping hidden" >
					<h1>Shipping Address</h1>
					<div class="row">
						<div class="col-md-6 col-sm-6">
							<div class="popTbox">
								<i>House No</i>
								<input type="text" id="ship_house_no" name="ship_house_no">
							</div>
						</div>
						<div class="col-md-3 col-sm-3">
							<div class="popTbox">
								<i>Postal Code</i>
								<input type="text" id="ship_postal_code" name="ship_postal_code">
								<div id="ship_postalcode_error"></div>
							</div>
							
						</div>
						
						<div class="col-md-3 col-sm-3">
							<div class="popTbox">
								<i>.</i>
								<button type="button" id="user_updt_gt_adrs" class="greenbtn dull find_address ship">Get Address</button>
							</div>
						</div>
					</div>
					<p class="ship_postcode_list hidden"></p>
					<div class="row">
						<div class="col-md-6 col-sm-6">
							<div class="popTbox">
								<i>Address</i>
								<input type="text" id="usr_updt_adrs" name="ship_street">
							</div>
						</div>

						<div class="col-md-6 col-sm-6">
							<div class="popTbox">
								<i>City</i>
								<input type="text" id="usr_updt_city" name="ship_city">
							</div>
						</div>	

						<div class="col-md-6 col-sm-6">
							<div class="popTbox">
								<i>Country</i>
								<input type="text" id="usr_updt_country" name="ship_country">
							</div>
						</div>				
					</div>
				</div>
				<input type="hidden" id="cart_id" name="cart_id" value="{{$cart_id}}">
				<input type="hidden" id="user_id" name="user_id" value="{{($user)?$user->id:''}}">				
					<div class="row">
						<div class="col-md-12">
							<div class="addbtnsbg">
								<input name="" type="button" class="greenbtn activatenow" value="Continue">
							</div>
						</div>
					</div>									
				</form>
			</div>
		</div>
		<div class="col-md-4">
			<div class="Cleftpart">					
				<h1>Purchase Details</h1>
				<div class="Paymentsbg ctablebg clearfix">
					<div class="buybox">
						<div class="row">
							<div class="col-md-8 col-xs-7">
								<b>{{ $product->product_name }} </b>
								<i>{{ $product->period }} Days</i>										
							</div>
							<div class="col-md-4 col-xs-5 text-right">
								<h4><b>{{ $currency }}{{ $product->sell_price }}</b></h4>
							</div>
						</div>								
					</div>
					<div class="buybox">
							<div class="row">
								<div class="col-md-8 col-xs-7">
									<b>{{ $product->in_call_limit }} International Mins</b>
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
								<h4>{{ $currency }}{{ $product->sell_price }}</h4>
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
            $(document).on( 'click', '#shipping_address', function(){
                if($(this).prop("checked") == true){
                	$('.shipping').addClass('hidden');
                }else{
                	$('.shipping').removeClass('hidden');
                }                
            });

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

		$('.find_address').on('click', function() {
		var $this =  $(this);
		var pc_type = ($this.hasClass('bill'))?'bill':'ship';
		var post_code = $('#'+ pc_type +'_postal_code').val();
		var house_no = $('#'+ pc_type +'_house_no').val();						
		if(post_code.length > 3){
			$.ajax({
				headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
				type: 'POST',
				data: {post_code:post_code,house_no:house_no},
				url: base_url+'/get_postcode',
				success: function(response){
					if(response.error){								
						$('#'+ pc_type +'_postalcode_error').html('<div class="invalid form-error">'+ response.message +'</div>');
					}else if(response.length == 1){
						var category = (pc_type == 'ship')?'ship_':'bill_';
						var address = response[0].split(',');
						var street = address[0]+', '+address[1]+((address[2] != '')?', '+address[2]:'');
						$('#'+category+'street').val(street);
						$('#'+category+'city').val($.trim(address[3])+', '+address[4]);  
						$('#'+category+'country').val(address[5]);
						$('#'+ pc_type +'_postal_code').val(address[6]);  
						$('#'+ pc_type +'_postalcode_error').html('');
						$('.'+ pc_type +'_postcode_list').addClass('hidden');  
					}else{
						var postcode_select = '<select id="postcode_option" class="'+pc_type+'"><option value="">Choose Address</option>';
						$.each(response, function( index, value) {		
						  	postcode_select += '<option value="'+value+'">'+value+'</option>';
						}); 
						postcode_select += '</select>';   
						$('#'+ pc_type +'_postalcode_error').html('');   
						$('.'+ pc_type +'_postcode_list').html(postcode_select);   
						$('.'+ pc_type +'_postcode_list').removeClass('hidden');  
					}							        
				}
			});
		} else {
			$('#'+ pc_type +'_postal_code').focus();
		}
		});

		$(document).on('change','#postcode_option', function() {
			var type = ($(this).hasClass('ship'))?'ship_':'bill_';
			var category = (type == 'ship_')?'ship':'bill';
			var postcode = $(this).val();  
			console.log(postcode);
			$('.'+category+'_postcode_list').addClass('hidden');            
			var address = postcode.split(',');
			console.log(address);
			var street = address[0]+', '+address[1]+((address[2] != '')?', '+address[2]:'');
			$('#'+type+'street').val(street);
			$('#'+type+'city').val($.trim(address[3])+', '+address[4]);  
			$('#'+type+'country').val(address[5]);
			$('#'+ category +'_postal_code').val(address[6]);              
		}); 

		$(document).on( 'click', '.activatenow', function(){
            if($("#billing-form").valid()){
            	var url = $("#billing-form").attr('action');
            	var formData = new FormData($('#billing-form')[0]);
            	$.ajax({
					headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
					type: 'POST',                                                
					url: url,
					data: formData,
			        processData: false,
			        contentType: false,
			        dataType: 'json',
					success:function(data){								
						// if(data.success.user_exist){
						// 	$('#login').modal();								
						// }else{
							window.location.href = base_url+'/payment';	
						//}
					}
				});
            }
        });

	   	$("#billing-form").validate({
            errorClass: "invalid form-error",
            errorElement: 'div',
            errorPlacement: function(error, element) {                       
                error.appendTo(element.parent());
            },
            rules: {                            
                first_name:'required',
                last_name:'required',
                user_email: {
                	required: true, 
                	email: true
                },
                contact_number: {
                    number: true,
                    required: true, 
                    minlength:8,
                    maxlength:11,
                    regex:/^(((0))[0-9]{10})$/,
                },                        
        //         postal_code: {
        //         	required:true,
        //         	// regex:/^([A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}|GIR ?0A{2})$/,
        //         	regex:/^([Gg][Ii][Rr] 0[Aa]{2})|((([A-Za-z][0-9]{1,2})|(([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})|(([A-Za-z][0-9][A-Za-z])|([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?))))\s?[0-9][A-Za-z]{2})$/
        //         },
        //         street: 'required',
        //         city: 'required',
        //         country: 'required',
        //         ship_postal_code: {
        //       		required: {
				    //     depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
				    // }
        // 		},
        //         ship_street: {
        //       		required: {
				    //     depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
				    // }
        // 		},
        //         ship_street: {
        //       		required: {
				    //     depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
				    // }
        // 		},
        //         ship_city: {
        //       		required: {
				    //     depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
				    // }
        // 		},
        //         ship_country: {
        //       		required: {
				    //     depends: function () { return (!$("#shipping_address").is(":checked"))?1:0 }
				    // }
        // 		},
            },
            messages: {
                first_name: "Enter your first name!",
                last_name: "Enter your last name!",
                contact_number: {
                	required:"Enter valid phone number!",
                	regex:"Enter phone number leading with 0",
                },
                // postal_code: {
                // 	required:"Enter your postal code!",
                // 	regex:"Invalid Postalcode",               
                // }
            }
        });
    });
</script>
@endsection